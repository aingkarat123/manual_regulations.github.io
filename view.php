<?php
session_start();
require_once __DIR__ . '/policy_access.php';

if (!policyAccessGranted()) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('location: ../../../index.php');
    exit;
}

require_once __DIR__ . '/policy_data.php';

$chapterNumber = filter_input(INPUT_GET, 'chapter', FILTER_VALIDATE_INT);
if (!$chapterNumber || !isset($policyChapters[$chapterNumber])) {
    header('location: dashboard.php');
    exit;
}

$chapter = $policyChapters[$chapterNumber];
$previous = $chapterNumber > 1 ? $policyChapters[$chapterNumber - 1] : null;
$next = $chapterNumber < count($policyChapters) ? $policyChapters[$chapterNumber + 1] : null;
$isGuest = policyGuestAccess();
$displayNameTh = htmlspecialchars((string) ($_SESSION['User_name'] ?? 'ผู้มาเยี่ยมชม'), ENT_QUOTES, 'UTF-8');
$displayNameEn = htmlspecialchars((string) ($_SESSION['User_name'] ?? 'Visitor'), ENT_QUOTES, 'UTF-8');

$policyTextFile = __DIR__ . '/policy_text.php';
$policyText = is_file($policyTextFile) ? require $policyTextFile : [];
if (!is_array($policyText)) {
    $policyText = [];
}
$policyEnglishFile = __DIR__ . '/policy_text_en.php';
$policyEnglishPages = is_file($policyEnglishFile) ? require $policyEnglishFile : [];
if (!is_array($policyEnglishPages)) {
    $policyEnglishPages = [];
}

$chapterPages = [];
$chapterSectionCount = 0;
foreach (range($chapter['start'], $chapter['end']) as $pageNumber) {
    $pageContent = $policyText['pages'][(string) $pageNumber] ?? [
        'page' => $pageNumber,
        'signed_copy' => false,
        'sections' => [],
    ];
    $chapterPages[] = $pageContent;
    $chapterSectionCount += count($pageContent['sections'] ?? []);
}

$policyNotice = $policyText['meta']['notice']
    ?? 'ข้อความจัดทำจากเอกสารสแกน โปรดตรวจเทียบภาพต้นฉบับเมื่อใช้อ้างอิง';
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>หมวดที่ <?= $chapterNumber ?> <?= htmlspecialchars($chapter['title']) ?> | TCS Policy Center</title>
  <link rel="shortcut icon" type="image/png" href="../../assets/images/logos/logo_new.png">
  <link rel="stylesheet" href="../../assets/css/styles.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/policy.css?v=20261020-language-all1">
</head>
<body class="policy-reader">
  <header class="topbar">
    <a class="brand-block" href="dashboard.php" aria-label="หน้าหลัก Policy Center">
      <img class="brand-logo" src="../../assets/images/logos/logo_new.png" alt="Tantawan">
      <span class="brand-divider" aria-hidden="true"></span>
      <span class="brand-copy">
        <strong>TCS POLICY CENTER</strong>
        <span data-th="คู่มือและข้อบังคับของบริษัท" data-en="Company Policies and Guidelines">คู่มือและข้อบังคับของบริษัท</span>
      </span>
    </a>
    <div class="topbar-actions">
      <div class="language-switch" role="group" aria-label="Language">
        <button type="button" data-language="th" aria-pressed="true">TH</button>
        <button type="button" data-language="en" aria-pressed="false">EN</button>
      </div>
      <div class="user-block">
        <img class="user-avatar" src="../../assets/images/profile/user-4.jpg" alt="">
        <span class="user-copy">
          <strong data-th="<?= $displayNameTh ?>" data-en="<?= $displayNameEn ?>"><?= $displayNameTh ?></strong>
          <span data-th="<?= $isGuest ? 'ผู้เข้าชม' : 'พนักงาน' ?>" data-en="<?= $isGuest ? 'Guest' : 'Employee' ?>"><?= $isGuest ? 'ผู้เข้าชม' : 'พนักงาน' ?></span>
        </span>
      </div>
    </div>
  </header>

  <div class="reader-layout">
    <aside class="reader-sidebar" aria-label="สารบัญข้อบังคับ">
      <a class="reader-back" href="dashboard.php">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        <span data-th="กลับหน้าหลัก Policy Center" data-en="Back to Policy Center">กลับหน้าหลัก Policy Center</span>
      </a>
      <a class="reader-ai-link" href="assistant.php">
        <i class="fa-solid fa-robot" aria-hidden="true"></i>
        <span data-th="สอบถาม AI จากข้อบังคับ" data-en="Ask AI about policies">สอบถาม AI จากข้อบังคับ</span>
      </a>
      <p class="toc-label" data-th="สารบัญข้อบังคับ" data-en="Policy contents">สารบัญข้อบังคับ</p>
      <nav class="toc-list">
        <?php foreach ($policyChapters as $number => $tocChapter): ?>
          <a class="toc-link<?= $number === $chapterNumber ? ' active' : '' ?>" href="view.php?chapter=<?= $number ?>">
            <span class="toc-number"><?= $number ?></span>
            <span data-th="<?= htmlspecialchars($tocChapter['title'], ENT_QUOTES, 'UTF-8') ?>" data-en="<?= htmlspecialchars($tocChapter['title_en'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($tocChapter['title']) ?></span>
          </a>
        <?php endforeach; ?>
      </nav>
    </aside>

    <main class="reader-main">
      <div class="reader-shell">
        <nav class="breadcrumb" aria-label="เส้นทางนำทาง">
          <a href="dashboard.php">Policy Center</a>
          <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
          <span data-th="หมวดที่ <?= $chapterNumber ?>" data-en="Chapter <?= $chapterNumber ?>">หมวดที่ <?= $chapterNumber ?></span>
        </nav>

        <section class="reader-hero">
          <div>
            <span class="reader-chapter-label" data-th="หมวดที่ <?= $chapterNumber ?>" data-en="Chapter <?= $chapterNumber ?>">หมวดที่ <?= $chapterNumber ?></span>
            <h1 data-th="<?= htmlspecialchars($chapter['title'], ENT_QUOTES, 'UTF-8') ?>" data-en="<?= htmlspecialchars($chapter['title_en'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($chapter['title']) ?></h1>
            <p data-th="<?= htmlspecialchars($chapter['summary'] . ' · ' . policyPageLabel($chapter), ENT_QUOTES, 'UTF-8') ?>" data-en="<?= htmlspecialchars($chapter['summary_en'] . ' · ' . ($chapter['start'] === $chapter['end'] ? 'Page ' . $chapter['start'] : 'Pages ' . $chapter['start'] . '-' . $chapter['end']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($chapter['summary']) ?> · <?= htmlspecialchars(policyPageLabel($chapter)) ?></p>
          </div>
          <div class="reader-navigation" aria-label="เปลี่ยนหมวด">
            <a class="icon-button<?= $previous ? '' : ' disabled' ?>"
               href="<?= $previous ? 'view.php?chapter=' . ($chapterNumber - 1) : '#' ?>"
               title="หมวดก่อนหน้า" aria-label="หมวดก่อนหน้า" data-title-th="หมวดก่อนหน้า" data-title-en="Previous chapter" data-aria-th="หมวดก่อนหน้า" data-aria-en="Previous chapter">
              <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </a>
            <a class="icon-button<?= $next ? '' : ' disabled' ?>"
               href="<?= $next ? 'view.php?chapter=' . ($chapterNumber + 1) : '#' ?>"
               title="หมวดถัดไป" aria-label="หมวดถัดไป" data-title-th="หมวดถัดไป" data-title-en="Next chapter" data-aria-th="หมวดถัดไป" data-aria-en="Next chapter">
              <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </a>
          </div>
        </section>

        <div class="reader-toolbar">
          <div class="toolbar-title">
            <i class="fa-solid fa-book-open" aria-hidden="true"></i>
            <span data-th="เนื้อหาหมวดที่ <?= $chapterNumber ?>" data-en="Chapter <?= $chapterNumber ?> content">เนื้อหาหมวดที่ <?= $chapterNumber ?></span>
            <small data-th="<?= $chapterSectionCount ?> หัวข้อ · <?= policyPageCount($chapter) ?> หน้า" data-en="<?= $chapterSectionCount ?> sections · <?= policyPageCount($chapter) ?> pages"><?= $chapterSectionCount ?> หัวข้อ · <?= policyPageCount($chapter) ?> หน้า</small>
          </div>
          <div class="toolbar-actions">
            <button class="icon-button" id="textDecrease" type="button" title="ลดขนาดตัวอักษร" aria-label="ลดขนาดตัวอักษร" data-title-th="ลดขนาดตัวอักษร" data-title-en="Decrease text size" data-aria-th="ลดขนาดตัวอักษร" data-aria-en="Decrease text size">
              <i class="fa-solid fa-minus" aria-hidden="true"></i>
            </button>
            <button class="icon-button text-reset" id="textReset" type="button" title="ขนาดตัวอักษรปกติ" aria-label="ใช้ขนาดตัวอักษรปกติ" data-title-th="ขนาดตัวอักษรปกติ" data-title-en="Default text size" data-aria-th="ใช้ขนาดตัวอักษรปกติ" data-aria-en="Use default text size">
              <span aria-hidden="true" data-th="ก" data-en="A">ก</span>
            </button>
            <button class="icon-button" id="textIncrease" type="button" title="เพิ่มขนาดตัวอักษร" aria-label="เพิ่มขนาดตัวอักษร" data-title-th="เพิ่มขนาดตัวอักษร" data-title-en="Increase text size" data-aria-th="เพิ่มขนาดตัวอักษร" data-aria-en="Increase text size">
              <i class="fa-solid fa-plus" aria-hidden="true"></i>
            </button>
            <button class="icon-button" type="button" onclick="window.print()" title="พิมพ์" aria-label="พิมพ์เนื้อหา" data-title-th="พิมพ์" data-title-en="Print" data-aria-th="พิมพ์เนื้อหา" data-aria-en="Print content">
              <i class="fa-solid fa-print" aria-hidden="true"></i>
            </button>
          </div>
        </div>

        <aside class="ocr-notice" role="note">
          <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
          <div>
            <strong data-th="เนื้อหาแบบข้อความเพื่อให้อ่านและค้นหาได้สะดวก" data-en="Searchable text for easier reading">เนื้อหาแบบข้อความเพื่อให้อ่านและค้นหาได้สะดวก</strong>
            <span data-th="<?= htmlspecialchars($policyNotice, ENT_QUOTES, 'UTF-8') ?>" data-en="An English translation is provided for convenience. Check the Thai source image when an exact or legal reference is required."><?= htmlspecialchars($policyNotice) ?></span>
          </div>
        </aside>

        <section class="reader-pages" id="policyReader" aria-label="เนื้อหาข้อบังคับหมวดที่ <?= $chapterNumber ?>">
          <?php foreach ($chapterPages as $pageIndex => $pageContent): ?>
            <?php $page = (int) ($pageContent['page'] ?? 0); ?>
            <article class="policy-text-page" id="page-<?= $page ?>">
              <header class="policy-text-page-header">
                <div>
                  <span data-th="เอกสารต้นฉบับ" data-en="Source document">เอกสารต้นฉบับ</span>
                  <strong data-th="หน้า <?= $page ?>" data-en="Page <?= $page ?>">หน้า <?= $page ?></strong>
                </div>
                <a href="#page-<?= $page ?>" aria-label="ลิงก์ไปยังหน้า <?= $page ?>">#<?= $page ?></a>
              </header>

              <div class="policy-clauses">
                <?php if (empty($pageContent['sections'])): ?>
                  <p class="policy-empty" data-th="ยังไม่มีข้อความที่อ่านได้จากหน้านี้ กรุณาเปิดภาพต้นฉบับเพื่อตรวจสอบ" data-en="No readable text is available for this page. Please check the source image.">ยังไม่มีข้อความที่อ่านได้จากหน้านี้ กรุณาเปิดภาพต้นฉบับเพื่อตรวจสอบ</p>
                <?php else: ?>
                  <?php foreach ($pageContent['sections'] as $sectionIndex => $section): ?>
                    <?php
                      $sectionNumber = trim((string) ($section['number'] ?? ''));
                      $sectionKind = preg_replace('/[^a-z-]/', '', (string) ($section['kind'] ?? 'clause'));
                      $paragraphs = is_array($section['paragraphs'] ?? null) ? $section['paragraphs'] : [];
                      $englishParagraphs = $policyEnglishPages[(string) $page][$sectionIndex] ?? [];
                      $englishParagraphs = is_array($englishParagraphs) ? $englishParagraphs : [];
                    ?>
                    <section class="policy-clause kind-<?= htmlspecialchars($sectionKind) ?>">
                      <div class="clause-number<?= $sectionNumber === '' ? ' is-empty' : '' ?>">
                        <?= $sectionNumber !== '' ? htmlspecialchars($sectionNumber) : '<i class="fa-solid fa-file-signature" aria-hidden="true"></i>' ?>
                      </div>
                      <div class="clause-content" data-policy-language="th">
                        <?php foreach ($paragraphs as $paragraphIndex => $paragraph): ?>
                          <?php
                            $paragraphText = (string) $paragraph;
                            $paragraphLength = preg_match_all('/./u', $paragraphText, $unusedMatches);
                            $isShortLead = $paragraphIndex === 0 && $paragraphLength !== false && $paragraphLength <= 110;
                          ?>
                          <p<?= $isShortLead ? ' class="clause-lead"' : '' ?>><?= htmlspecialchars($paragraphText) ?></p>
                        <?php endforeach; ?>
                      </div>
                      <div class="clause-content" data-policy-language="en" hidden>
                        <?php foreach ($englishParagraphs as $paragraphIndex => $paragraph): ?>
                          <?php
                            $paragraphText = (string) $paragraph;
                            $paragraphLength = strlen($paragraphText);
                            $isShortLead = $paragraphIndex === 0 && $paragraphLength <= 110;
                          ?>
                          <p<?= $isShortLead ? ' class="clause-lead"' : '' ?>><?= htmlspecialchars($paragraphText) ?></p>
                        <?php endforeach; ?>
                      </div>
                    </section>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>

              <details class="source-check">
                <summary>
                  <span><i class="fa-regular fa-image" aria-hidden="true"></i> <span data-th="ตรวจภาพต้นฉบับ หน้า <?= $page ?>" data-en="View source image, page <?= $page ?>">ตรวจภาพต้นฉบับ หน้า <?= $page ?></span></span>
                  <i class="fa-solid fa-chevron-down summary-chevron" aria-hidden="true"></i>
                </summary>
                <div class="source-image-wrap">
                  <img src="documents/work-regulations-2026-06-01/pages/page-<?= str_pad((string) $page, 2, '0', STR_PAD_LEFT) ?>.jpg"
                       alt="ข้อบังคับต้นฉบับหมวดที่ <?= $chapterNumber ?> หน้า <?= $page ?>"
                       loading="lazy"
                       decoding="async">
                </div>
              </details>
            </article>
          <?php endforeach; ?>
        </section>

        <nav class="reader-footer-nav" aria-label="หมวดก่อนหน้าและถัดไป">
          <a class="chapter-nav-card<?= $previous ? '' : ' disabled' ?>" href="<?= $previous ? 'view.php?chapter=' . ($chapterNumber - 1) : '#' ?>">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>
              <span data-th="หมวดก่อนหน้า" data-en="Previous chapter">หมวดก่อนหน้า</span>
              <strong data-th="<?= $previous ? htmlspecialchars($previous['title'], ENT_QUOTES, 'UTF-8') : '' ?>" data-en="<?= $previous ? htmlspecialchars($previous['title_en'], ENT_QUOTES, 'UTF-8') : '' ?>"><?= $previous ? htmlspecialchars($previous['title']) : '' ?></strong>
            </span>
          </a>
          <a class="chapter-nav-card next<?= $next ? '' : ' disabled' ?>" href="<?= $next ? 'view.php?chapter=' . ($chapterNumber + 1) : '#' ?>">
            <span>
              <span data-th="หมวดถัดไป" data-en="Next chapter">หมวดถัดไป</span>
              <strong data-th="<?= $next ? htmlspecialchars($next['title'], ENT_QUOTES, 'UTF-8') : '' ?>" data-en="<?= $next ? htmlspecialchars($next['title_en'], ENT_QUOTES, 'UTF-8') : '' ?>"><?= $next ? htmlspecialchars($next['title']) : '' ?></strong>
            </span>
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
          </a>
        </nav>
      </div>
    </main>
  </div>

  <script>
    (() => {
      const reader = document.getElementById('policyReader');
      const languageButtons = [...document.querySelectorAll('[data-language]')];
      const sizes = [15.5, 17, 18.5, 20];
      let current = 1;

      function applyLanguage(language) {
        const currentLanguage = language === 'en' ? 'en' : 'th';
        document.documentElement.lang = currentLanguage;
        document.title = currentLanguage === 'en'
          ? 'Chapter <?= $chapterNumber ?> <?= htmlspecialchars($chapter['title_en'], ENT_QUOTES, 'UTF-8') ?> | TCS Policy Center'
          : 'หมวดที่ <?= $chapterNumber ?> <?= htmlspecialchars($chapter['title'], ENT_QUOTES, 'UTF-8') ?> | TCS Policy Center';

        document.querySelectorAll('[data-th][data-en]').forEach((element) => {
          element.textContent = element.dataset[currentLanguage];
        });
        document.querySelectorAll('[data-title-th][data-title-en]').forEach((element) => {
          element.title = currentLanguage === 'en' ? element.dataset.titleEn : element.dataset.titleTh;
        });
        document.querySelectorAll('[data-aria-th][data-aria-en]').forEach((element) => {
          element.setAttribute('aria-label', currentLanguage === 'en' ? element.dataset.ariaEn : element.dataset.ariaTh);
        });
        document.querySelectorAll('[data-policy-language]').forEach((element) => {
          element.hidden = element.dataset.policyLanguage !== currentLanguage;
        });
        languageButtons.forEach((button) => {
          const active = button.dataset.language === currentLanguage;
          button.classList.toggle('active', active);
          button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        try {
          window.localStorage.setItem('tcsPolicyLanguage', currentLanguage);
        } catch (error) {
          // Language still works when browser storage is unavailable.
        }
      }

      function applySize() {
        reader.style.setProperty('--reader-font-size', `${sizes[current]}px`);
        document.getElementById('textDecrease').disabled = current === 0;
        document.getElementById('textIncrease').disabled = current === sizes.length - 1;
      }

      document.getElementById('textDecrease').addEventListener('click', () => {
        current = Math.max(0, current - 1);
        applySize();
      });

      document.getElementById('textIncrease').addEventListener('click', () => {
        current = Math.min(sizes.length - 1, current + 1);
        applySize();
      });

      document.getElementById('textReset').addEventListener('click', () => {
        current = 1;
        applySize();
      });

      languageButtons.forEach((button) => {
        button.addEventListener('click', () => applyLanguage(button.dataset.language));
      });

      let savedLanguage = 'th';
      try {
        savedLanguage = window.localStorage.getItem('tcsPolicyLanguage') || 'th';
      } catch (error) {
        savedLanguage = 'th';
      }

      applySize();
      applyLanguage(savedLanguage);
    })();
  </script>
</body>
</html>

