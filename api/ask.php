<?php
declare(strict_types=1);

session_start();
require_once dirname(__DIR__) . '/policy_access.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '', true);
$payload = is_array($payload) ? $payload : [];
$language = (($payload['language'] ?? 'th') === 'en') ? 'en' : 'th';
$isEnglish = $language === 'en';

if (!policyAccessGranted()) {
    respond(401, ['ok' => false, 'message' => $isEnglish ? 'Please sign in again.' : 'กรุณาเข้าสู่ระบบอีกครั้ง']);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(405, ['ok' => false, 'message' => 'Method not allowed']);
}

$csrfHeader = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$csrfSession = (string) ($_SESSION['policy_ai_csrf'] ?? '');
if ($csrfSession === '' || $csrfHeader === '' || !hash_equals($csrfSession, $csrfHeader)) {
    respond(403, ['ok' => false, 'message' => $isEnglish ? 'Your session has expired. Refresh the page and try again.' : 'เซสชันหมดอายุ กรุณารีเฟรชหน้าแล้วลองใหม่']);
}

$question = trim((string) ($payload['question'] ?? ''));

if ($question === '' || mb_strlen($question, 'UTF-8') < 3) {
    respond(422, ['ok' => false, 'message' => $isEnglish ? 'Please enter at least 3 characters.' : 'กรุณาพิมพ์คำถามอย่างน้อย 3 ตัวอักษร']);
}
if (mb_strlen($question, 'UTF-8') > 500) {
    respond(422, ['ok' => false, 'message' => $isEnglish ? 'The question must not exceed 500 characters.' : 'คำถามยาวเกิน 500 ตัวอักษร']);
}

$now = time();
$requestTimes = array_values(array_filter(
    is_array($_SESSION['policy_ai_requests'] ?? null) ? $_SESSION['policy_ai_requests'] : [],
    static fn ($time): bool => is_int($time) && $time > ($now - 300)
));
if (count($requestTimes) >= 20) {
    respond(429, ['ok' => false, 'message' => $isEnglish ? 'Too many questions were sent. Please wait a moment and try again.' : 'ส่งคำถามถี่เกินไป กรุณารอสักครู่แล้วลองใหม่']);
}
$requestTimes[] = $now;
$_SESSION['policy_ai_requests'] = $requestTimes;

$policyText = require dirname(__DIR__) . '/policy_text.php';
$policyEnglishPages = require dirname(__DIR__) . '/policy_text_en.php';
require dirname(__DIR__) . '/policy_data.php';
if (!is_array($policyText) || !isset($policyText['pages']) || !is_array($policyText['pages'])) {
    respond(503, ['ok' => false, 'message' => $isEnglish ? 'The policy database is currently unavailable.' : 'ยังไม่สามารถเปิดฐานข้อมูลข้อบังคับได้']);
}
if (!is_array($policyEnglishPages)) {
    $policyEnglishPages = [];
}

function normalizeThai(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, [
        'โอที' => 'ot',
        'โอ.ที.' => 'ot',
        'o.t.' => 'ot',
        'ค่าล่วงเวลา' => 'ค่าล่วงเวลา ot',
    ]);
    $text = preg_replace('/[^\p{L}\p{N}.\s]/u', ' ', $text) ?? $text;
    return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
}

function searchTerms(string $question): array
{
    $normalized = normalizeThai($question);
    $terms = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $groups = [
        ['การลา', 'วันลา', 'ลา', 'leave', 'leave entitlement'],
        ['ลากิจ', 'กิจธุระ', 'personal leave', 'business leave'],
        ['ลาป่วย', 'ป่วย', 'ใบรับรองแพทย์', 'sick leave', 'medical certificate'],
        ['ลาคลอด', 'คลอดบุตร', 'ตั้งครรภ์', 'maternity leave', 'pregnancy'],
        ['ลาพักผ่อน', 'พักร้อน', 'วันหยุดพักผ่อน', 'annual leave', 'vacation leave', 'vacation'],
        ['ค่าล่วงเวลา', 'ot', 'ทำงานล่วงเวลา', 'overtime', 'overtime work'],
        ['วันหยุด', 'หยุดงาน', 'วันหยุดประเพณี', 'holiday', 'holidays', 'day off', 'days off'],
        ['เวลาทำงาน', 'เวลาเข้างาน', 'เวลาพัก', 'working hours', 'work hours', 'rest period'],
        ['ค่าจ้าง', 'เงินเดือน', 'การจ่ายค่าจ้าง', 'wage', 'wages', 'salary', 'pay'],
        ['วินัย', 'บทลงโทษ', 'ลงโทษ', 'ความผิด', 'discipline', 'disciplinary action', 'penalty', 'violation'],
        ['ร้องทุกข์', 'ร้องเรียน', 'ข้อพิพาท', 'grievance', 'complaint', 'dispute'],
        ['ลาออก', 'พ้นสภาพ', 'เลิกจ้าง', 'resignation', 'termination', 'dismissal'],
        ['ทดลองงาน', 'พนักงานทดลองงาน', 'probation', 'probationary employee'],
        ['เกษียณ', 'เกษียณอายุ', 'retirement', 'retire'],
        ['ขาดงาน', 'มาสาย', 'ละทิ้งหน้าที่', 'absence', 'absent', 'late', 'abandonment of duty'],
    ];

    foreach ($groups as $group) {
        foreach ($group as $variant) {
            if (mb_strpos($normalized, normalizeThai($variant), 0, 'UTF-8') !== false) {
                $terms = array_merge($terms, $group);
                break;
            }
        }
    }

    $stopWords = ['อะไร', 'อย่างไร', 'ยังไง', 'เท่าไร', 'เท่าไหร่', 'กี่วัน', 'กี่ครั้ง', 'เมื่อใด', 'ตอนไหน', 'ได้ไหม', 'หรือไม่', 'ต้อง', 'มี', 'ขอ', 'ถาม', 'อยาก', 'ทราบ', 'เกี่ยวกับ', 'บริษัท', 'พนักงาน', 'และ', 'หรือ', 'ที่', 'ของ', 'ใน', 'เป็น', 'ให้', 'what', 'when', 'where', 'which', 'who', 'why', 'how', 'many', 'much', 'are', 'is', 'the', 'a', 'an', 'and', 'or', 'of', 'to', 'for', 'in', 'on', 'with', 'employee', 'company', 'policy', 'rules', 'rule', 'must', 'should', 'can', 'may', 'does', 'do'];
    $filtered = [];
    foreach ($terms as $term) {
        $term = normalizeThai((string) $term);
        if ($term === '' || in_array($term, $stopWords, true) || mb_strlen($term, 'UTF-8') < 2) {
            continue;
        }
        $filtered[$term] = true;
    }

    if ($normalized !== '' && mb_strlen($normalized, 'UTF-8') <= 100) {
        $filtered[$normalized] = true;
    }
    return array_keys($filtered);
}

function excerpt(array $paragraphs, int $limit = 700): string
{
    $text = trim(implode("\n", array_map('trim', array_map('strval', $paragraphs))));
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit, 'UTF-8')) . '...';
}

$normalizedQuestion = normalizeThai($question);
$terms = searchTerms($question);
$numberMatch = null;
if (preg_match('/(?:ข้อ\s*)?(\d+(?:\.\d+)+)/u', $normalizedQuestion, $matches)) {
    $numberMatch = $matches[1];
}

$ranked = [];
foreach ($policyText['pages'] as $pageKey => $pageData) {
    // The signed copy repeats the preceding page verbatim; exclude it from
    // search ranking so answers do not show duplicate references.
    if (!empty($pageData['signed_copy'])) {
        continue;
    }

    $page = (int) ($pageData['page'] ?? $pageKey);
    $chapterNumber = (int) ($pageData['chapter'] ?? 0);
    if ($chapterNumber < 1 || !isset($policyChapters[$chapterNumber])) {
        foreach ($policyChapters as $candidateNumber => $candidateChapter) {
            if ($page >= $candidateChapter['start'] && $page <= $candidateChapter['end']) {
                $chapterNumber = (int) $candidateNumber;
                break;
            }
        }
    }
    $chapter = $policyChapters[$chapterNumber] ?? [
        'title' => 'ข้อบังคับบริษัท',
        'keywords' => '',
        'title_en' => 'Company Policies',
        'summary_en' => '',
        'keywords_en' => '',
    ];

    foreach (($pageData['sections'] ?? []) as $sectionIndex => $section) {
        $sectionNumber = trim((string) ($section['number'] ?? ''));
        $paragraphs = is_array($section['paragraphs'] ?? null) ? $section['paragraphs'] : [];
        $englishParagraphs = $policyEnglishPages[(string) $page][$sectionIndex] ?? [];
        $englishParagraphs = is_array($englishParagraphs) ? $englishParagraphs : [];
        $sectionText = trim(implode(' ', array_map('strval', $paragraphs)));
        if ($sectionText === '') {
            continue;
        }

        $sectionHaystack = normalizeThai($sectionNumber . ' ' . $sectionText . ' ' . implode(' ', array_map('strval', $englishParagraphs)));
        $chapterHaystack = normalizeThai(
            $chapter['title'] . ' ' . ($chapter['keywords'] ?? '') . ' '
            . ($chapter['title_en'] ?? '') . ' ' . ($chapter['summary_en'] ?? '') . ' ' . ($chapter['keywords_en'] ?? '')
        );
        $score = 0;
        if ($numberMatch !== null && $sectionNumber === $numberMatch) {
            $score += 120;
        }
        if ($normalizedQuestion !== '' && mb_strlen($normalizedQuestion, 'UTF-8') >= 4 && mb_strpos($sectionHaystack, $normalizedQuestion, 0, 'UTF-8') !== false) {
            $score += 60;
        }
        foreach ($terms as $term) {
            if (mb_strpos($sectionHaystack, $term, 0, 'UTF-8') !== false) {
                $score += 12 + min(12, (int) floor(mb_strlen($term, 'UTF-8') / 2));
            } elseif (mb_strpos($chapterHaystack, $term, 0, 'UTF-8') !== false) {
                $score += 3 + min(4, (int) floor(mb_strlen($term, 'UTF-8') / 3));
            }
        }
        if ($score > 0) {
            $ranked[] = [
                'score' => $score,
                'page' => $page,
                'chapter' => $chapterNumber,
                'chapter_title' => (string) ($language === 'en' ? ($chapter['title_en'] ?? $chapter['title']) : $chapter['title']),
                'section' => $sectionNumber,
                'paragraphs' => $isEnglish && $englishParagraphs !== [] ? $englishParagraphs : $paragraphs,
            ];
        }
    }
}

usort($ranked, static function (array $a, array $b): int {
    if ($a['score'] === $b['score']) {
        return $a['page'] <=> $b['page'];
    }
    return $b['score'] <=> $a['score'];
});

$bestScore = (int) ($ranked[0]['score'] ?? 0);
if ($bestScore < 9) {
    respond(200, [
        'ok' => true,
        'answer' => $isEnglish
            ? 'No policy text clearly answers this question yet. Try a more specific topic such as sick leave, personal leave, overtime, working hours or discipline.'
            : 'ยังไม่พบข้อความในข้อบังคับที่ตอบคำถามนี้ได้อย่างชัดเจน กรุณาลองระบุหัวข้อให้เจาะจงขึ้น เช่น ลาป่วย ลากิจ ค่าล่วงเวลา เวลาทำงาน หรือวินัย',
        'references' => [],
        'warning' => $isEnglish
            ? 'Please ask HR directly for this case. The system will not guess an answer.'
            : 'กรณีนี้ควรสอบถาม HR โดยตรง ระบบจะไม่คาดเดาคำตอบ',
    ]);
}

$selected = [];
$seen = [];
foreach ($ranked as $item) {
    if (count($selected) >= 5) {
        break;
    }
    $key = $item['page'] . ':' . $item['section'];
    if (isset($seen[$key])) {
        continue;
    }
    if ($item['score'] < max(9, (int) floor($bestScore * 0.35))) {
        continue;
    }
    $seen[$key] = true;
    $selected[] = $item;
}

$primary = $selected[0];
$answerItems = [];
foreach ($selected as $item) {
    $itemText = trim(implode(' ', array_map('strval', $item['paragraphs'])));
    if (mb_strlen($itemText, 'UTF-8') >= 40) {
        $answerItems[] = $item;
    }
    if (count($answerItems) >= 4) {
        break;
    }
}
if ($answerItems === []) {
    $answerItems = [$primary];
}

$answerParts = [];
foreach ($answerItems as $index => $item) {
    $label = $isEnglish
        ? ($item['section'] !== '' ? 'Clause ' . $item['section'] : 'Page ' . $item['page'])
        : ($item['section'] !== '' ? 'ข้อ ' . $item['section'] : 'หน้า ' . $item['page']);
    $answerParts[] = ($index + 1) . '. ' . $label . "\n" . excerpt($item['paragraphs'], 680);
}
$answer = $isEnglish
    ? "The company regulations contain the following relevant details. This English translation is provided for convenience; the Thai source remains authoritative.\n\n" . implode("\n\n", $answerParts)
    : "จากข้อบังคับของบริษัท พบรายละเอียดที่เกี่ยวข้องดังนี้\n\n" . implode("\n\n", $answerParts);
if ($isEnglish && count($selected) > count($answerItems)) {
    $answer .= "\n\nOpen the references below to review all conditions, especially advance approval or supporting-document requirements.";
} elseif ($isEnglish) {
    $answer .= "\n\nOpen the references below to read the official full text and verify the details.";
} elseif (count($selected) > count($answerItems)) {
    $answer .= "\n\nควรเปิดข้ออ้างอิงด้านล่างเพื่อตรวจเงื่อนไขทั้งหมด โดยเฉพาะกรณีที่ต้องขออนุญาตล่วงหน้าหรือใช้เอกสารประกอบ";
} else {
    $answer .= "\n\nสามารถเปิดข้ออ้างอิงด้านล่างเพื่ออ่านเนื้อหาต้นฉบับและตรวจสอบรายละเอียดได้";
}

$references = array_map(static function (array $item): array {
    return [
        'section' => $item['section'],
        'page' => $item['page'],
        'chapter' => $item['chapter'],
        'chapter_title' => $item['chapter_title'],
        'url' => 'view.php?chapter=' . $item['chapter'] . '#page-' . $item['page'],
    ];
}, $selected);

respond(200, [
    'ok' => true,
    'answer' => $answer,
    'references' => $references,
    'warning' => $isEnglish
        ? 'This answer uses the English translation of the Company policy. Check the Thai source for exact or legal reference, and confirm approvals or individual cases with HR.'
        : 'คำตอบอ้างอิงจากข้อความในข้อบังคับ หากเป็นการอนุมัติหรือกรณีเฉพาะบุคคล กรุณายืนยันกับ HR',
]);
