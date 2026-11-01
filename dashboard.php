<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/policy_access.php';

if (!policyAccessGranted()) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('location: ../../../index.php');
    exit;
}

require_once __DIR__ . '/policy_data.php';
$isGuest = policyGuestAccess();
$displayNameTh = htmlspecialchars((string) ($_SESSION['User_name'] ?? 'ผู้มาเยี่ยมชม'), ENT_QUOTES, 'UTF-8');
$displayNameEn = htmlspecialchars((string) ($_SESSION['User_name'] ?? 'Visitor'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TCS Policy Center | คู่มือและข้อบังคับ</title>
  <link rel="shortcut icon" type="image/png" href="../../assets/images/logos/logo_new.png">
  <link rel="stylesheet" href="../../assets/css/styles.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/policy.css?v=20261020-language-all1">
</head>
<body class="policy-dashboard">
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

  <div class="app-layout">
    <aside class="sidebar" aria-label="เมนู Policy Center">
      <nav class="side-nav">
        <a class="side-link active" href="#home"><i class="fa-solid fa-house" aria-hidden="true"></i><span data-th="หน้าหลัก" data-en="Home">หน้าหลัก</span></a>
        <a class="side-link" href="assistant.php"><i class="fa-solid fa-robot" aria-hidden="true"></i><span data-th="สอบถาม AI" data-en="Ask AI">สอบถาม AI</span></a>
        <a class="side-link" id="focusSearch" href="#home"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span data-th="ค้นหาข้อบังคับ" data-en="Search Policies">ค้นหาข้อบังคับ</span></a>
        <a class="side-link" href="#categories"><i class="fa-solid fa-table-cells-large" aria-hidden="true"></i><span data-th="หมวดหมู่ทั้งหมด" data-en="Categories">หมวดหมู่ทั้งหมด</span></a>
        <a class="side-link" href="#allChapters"><i class="fa-solid fa-list" aria-hidden="true"></i><span data-th="รายการข้อบังคับ" data-en="All Policies">รายการข้อบังคับ</span></a>
        <a class="side-link portal-link" href="../dashboard.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span data-th="กลับหน้าระบบ" data-en="Back to Portal">กลับหน้าระบบ</span></a>
      </nav>
    </aside>

    <main class="main-content" id="home">
      <div class="content-shell">
        <section class="hero" aria-labelledby="heroTitle">
          <div class="hero-content">
            <div class="hero-kicker">
              <span><i class="fa-solid fa-book-open" aria-hidden="true"></i> <span data-th="ข้อมูลสำหรับพนักงาน" data-en="Employee Information">ข้อมูลสำหรับพนักงาน</span></span>
              <a class="hero-ai-button" href="assistant.php"><i class="fa-solid fa-robot" aria-hidden="true"></i> <span data-th="สอบถาม AI" data-en="Ask AI">สอบถาม AI</span></a>
            </div>
            <h1 id="heroTitle" data-th="คู่มือและข้อบังคับ" data-en="Policies and Guidelines">คู่มือและข้อบังคับ</h1>
            <p class="hero-lead" data-th="ค้นหาสิทธิ หลักเกณฑ์ และแนวปฏิบัติของพนักงาน" data-en="Find employee rights, rules and workplace procedures">ค้นหาสิทธิ หลักเกณฑ์ และแนวปฏิบัติของพนักงาน</p>
            <form class="policy-search" id="policySearch" role="search">
              <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
              <input id="searchInput" type="search" autocomplete="off" placeholder="ค้นหาหัวข้อ เช่น ลากิจ ลาป่วย OT วันหยุด หรือวินัย" data-placeholder-th="ค้นหาหัวข้อ เช่น ลากิจ ลาป่วย OT วันหยุด หรือวินัย" data-placeholder-en="Search personal leave, sick leave, OT, holidays or discipline">
              <button type="submit" data-th="ค้นหา" data-en="Search">ค้นหา</button>
            </form>
            <div class="quick-search" aria-label="คำค้นหายอดนิยม">
              <span data-th="หัวข้อแนะนำ" data-en="Suggested topics">หัวข้อแนะนำ</span>
              <button class="quick-tag" type="button" data-query-th="การลา" data-query-en="leave" data-th="การลา" data-en="Leave">การลา</button>
              <button class="quick-tag" type="button" data-query-th="ค่าล่วงเวลา" data-query-en="overtime" data-th="ค่าล่วงเวลา" data-en="Overtime">ค่าล่วงเวลา</button>
              <button class="quick-tag" type="button" data-query-th="วันหยุด" data-query-en="holidays" data-th="วันหยุด" data-en="Holidays">วันหยุด</button>
              <button class="quick-tag" type="button" data-query-th="วินัย" data-query-en="discipline" data-th="วินัย" data-en="Discipline">วินัย</button>
              <button class="quick-tag" type="button" data-query-th="ร้องทุกข์" data-query-en="grievance" data-th="ร้องทุกข์" data-en="Grievance">ร้องทุกข์</button>
            </div>
          </div>
        </section>

        <section class="section-block" id="categories" aria-labelledby="categoryTitle">
          <div class="section-heading">
            <div>
              <h2 id="categoryTitle" data-th="เลือกหมวดข้อบังคับ" data-en="Browse Policy Categories">เลือกหมวดข้อบังคับ</h2>
              <p data-th="เปิดอ่านเนื้อหาแบบข้อความ พร้อมตรวจเอกสารต้นฉบับ" data-en="Read text online and compare it with the official source document">เปิดอ่านเนื้อหาแบบข้อความ พร้อมตรวจเอกสารต้นฉบับ</p>
            </div>
            <span class="section-count" id="resultCount">8 หมวด</span>
          </div>
          <div class="category-grid" id="categoryGrid">
            <?php foreach ($policyChapters as $number => $chapter): ?>
              <a class="category-card tone-<?= htmlspecialchars($chapter['tone']) ?>"
                 href="view.php?chapter=<?= $number ?>"
                 data-policy-item
                 data-search="<?= htmlspecialchars($chapter['title'] . ' ' . $chapter['summary'] . ' ' . $chapter['keywords'] . ' ' . $chapter['title_en'] . ' ' . $chapter['summary_en'] . ' ' . $chapter['keywords_en'], ENT_QUOTES, 'UTF-8') ?>">
                <span class="category-icon"><i class="fa-solid <?= htmlspecialchars($chapter['icon']) ?>" aria-hidden="true"></i></span>
                <span class="category-copy">
                  <strong data-th="<?= htmlspecialchars($chapter['title'], ENT_QUOTES, 'UTF-8') ?>" data-en="<?= htmlspecialchars($chapter['title_en'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($chapter['title']) ?></strong>
                  <span data-th="<?= htmlspecialchars($chapter['summary'], ENT_QUOTES, 'UTF-8') ?>" data-en="<?= htmlspecialchars($chapter['summary_en'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($chapter['summary']) ?></span>
                </span>
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
              </a>
            <?php endforeach; ?>
          </div>
          <div class="search-empty" id="searchEmpty">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <span data-th="ไม่พบหมวดที่ตรงกับคำค้นหา ลองใช้คำที่สั้นลงหรือเลือกจากหัวข้อแนะนำ" data-en="No matching category was found. Try a shorter keyword or select a suggested topic.">ไม่พบหมวดที่ตรงกับคำค้นหา ลองใช้คำที่สั้นลงหรือเลือกจากหัวข้อแนะนำ</span>
          </div>
        </section>

        <div class="dashboard-lower">
          <section class="section-block" id="allChapters" aria-labelledby="chapterListTitle">
            <div class="section-heading">
              <div>
                <h2 id="chapterListTitle" data-th="ข้อบังคับทั้งหมด" data-en="All Policy Chapters">ข้อบังคับทั้งหมด</h2>
                <p data-th="เรียงตามหมวดและหน้าของเอกสาร" data-en="Ordered by chapter and source document page">เรียงตามหมวดและหน้าของเอกสาร</p>
              </div>
            </div>
            <div class="chapter-rows">
              <?php foreach ($policyChapters as $number => $chapter): ?>
                <a class="chapter-row"
                   href="view.php?chapter=<?= $number ?>"
                   data-policy-row
                   data-search="<?= htmlspecialchars($chapter['title'] . ' ' . $chapter['summary'] . ' ' . $chapter['keywords'] . ' ' . $chapter['title_en'] . ' ' . $chapter['summary_en'] . ' ' . $chapter['keywords_en'], ENT_QUOTES, 'UTF-8') ?>">
                  <span class="chapter-row-number"><?= $number ?></span>
                  <span class="chapter-row-copy">
                    <span class="chapter-row-title" data-th="<?= htmlspecialchars($chapter['title'], ENT_QUOTES, 'UTF-8') ?>" data-en="<?= htmlspecialchars($chapter['title_en'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($chapter['title']) ?></span>
                    <small data-th="<?= htmlspecialchars($chapter['summary'], ENT_QUOTES, 'UTF-8') ?>" data-en="<?= htmlspecialchars($chapter['summary_en'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($chapter['summary']) ?></small>
                  </span>
                  <span class="chapter-row-page" data-th="<?= htmlspecialchars(policyPageLabel($chapter), ENT_QUOTES, 'UTF-8') ?>" data-en="<?= $chapter['start'] === $chapter['end'] ? 'Page ' . $chapter['start'] : 'Pages ' . $chapter['start'] . '-' . $chapter['end'] ?>"><?= htmlspecialchars(policyPageLabel($chapter)) ?></span>
                  <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </a>
              <?php endforeach; ?>
            </div>
          </section>

          <section class="section-block" id="documentInfo" aria-labelledby="documentTitle">
            <div class="document-overview">
              <span class="document-badge"><i class="fa-solid fa-file-shield" aria-hidden="true"></i></span>
              <h3 id="documentTitle" data-th="ข้อบังคับเกี่ยวกับการทำงาน" data-en="Work Regulations">ข้อบังคับเกี่ยวกับการทำงาน</h3>
              <p data-th="บริษัท ทานตะวัน คอนสตรัคชั่น แอนด์ เซอร์วิส จำกัด" data-en="Tantawan Construction and Service Co., Ltd.">บริษัท ทานตะวัน คอนสตรัคชั่น แอนด์ เซอร์วิส จำกัด</p>
              <p class="document-language-note" data-th="มีคำแปลภาษาอังกฤษสำหรับใช้อ่านประกอบ โดยยึดข้อความภาษาไทยเป็นต้นฉบับ" data-en="An English translation is available for convenience; the Thai text remains the authoritative source.">มีคำแปลภาษาอังกฤษสำหรับใช้อ่านประกอบ โดยยึดข้อความภาษาไทยเป็นต้นฉบับ</p>
              <div class="document-stats">
                <div class="document-stat"><span data-th="วันที่มีผลบังคับใช้" data-en="Effective date">วันที่มีผลบังคับใช้</span><strong data-th="1 มิ.ย. 2569" data-en="1 Jun 2026">1 มิ.ย. 2569</strong></div>
                <div class="document-stat"><span data-th="จำนวนหมวด" data-en="Chapters">จำนวนหมวด</span><strong data-th="8 หมวด" data-en="8 chapters">8 หมวด</strong></div>
                <div class="document-stat"><span data-th="จำนวนหน้า" data-en="Document length">จำนวนหน้า</span><strong data-th="27 หน้า" data-en="27 pages">27 หน้า</strong></div>
                <div class="document-stat"><span data-th="รูปแบบการอ่าน" data-en="Reading format">รูปแบบการอ่าน</span><strong data-th="อ่านในระบบ" data-en="Read online">อ่านในระบบ</strong></div>
              </div>
            </div>
          </section>
        </div>
      </div>
    </main>
  </div>

  <script>
    (() => {
      const form = document.getElementById('policySearch');
      const input = document.getElementById('searchInput');
      const cards = [...document.querySelectorAll('[data-policy-item]')];
      const rows = [...document.querySelectorAll('[data-policy-row]')];
      const empty = document.getElementById('searchEmpty');
      const count = document.getElementById('resultCount');
      const languageButtons = [...document.querySelectorAll('[data-language]')];
      let currentLanguage = 'th';

      function updateResultCount(visibleCards) {
        count.textContent = currentLanguage === 'en'
          ? `${visibleCards} categories`
          : `${visibleCards} หมวด`;
      }

      function applyLanguage(language) {
        currentLanguage = language === 'en' ? 'en' : 'th';
        document.documentElement.lang = currentLanguage;
        document.title = currentLanguage === 'en'
          ? 'TCS Policy Center | Policies and Guidelines'
          : 'TCS Policy Center | คู่มือและข้อบังคับ';

        document.querySelectorAll('[data-th][data-en]').forEach((element) => {
          element.textContent = element.dataset[currentLanguage];
        });
        input.placeholder = currentLanguage === 'en'
          ? input.dataset.placeholderEn
          : input.dataset.placeholderTh;

        languageButtons.forEach((button) => {
          const active = button.dataset.language === currentLanguage;
          button.classList.toggle('active', active);
          button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        input.value = '';
        filterPolicies('');
        try {
          window.localStorage.setItem('tcsPolicyLanguage', currentLanguage);
        } catch (error) {
          // Language still works when browser storage is unavailable.
        }
      }

      function filterPolicies(rawQuery, scrollToResults = false) {
        const query = rawQuery.trim().toLocaleLowerCase(currentLanguage);
        let visibleCards = 0;

        cards.forEach((card) => {
          const matched = !query || card.dataset.search.toLocaleLowerCase(currentLanguage).includes(query);
          card.hidden = !matched;
          if (matched) visibleCards += 1;
        });

        rows.forEach((row) => {
          row.hidden = !!query && !row.dataset.search.toLocaleLowerCase(currentLanguage).includes(query);
        });

        updateResultCount(visibleCards);
        empty.classList.toggle('show', visibleCards === 0);
        if (scrollToResults) {
          document.getElementById('categories').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }

      form.addEventListener('submit', (event) => {
        event.preventDefault();
        filterPolicies(input.value, true);
      });

      input.addEventListener('input', () => filterPolicies(input.value));

      document.querySelectorAll('[data-query-th][data-query-en]').forEach((button) => {
        button.addEventListener('click', () => {
          input.value = currentLanguage === 'en' ? button.dataset.queryEn : button.dataset.queryTh;
          filterPolicies(input.value, true);
        });
      });

      languageButtons.forEach((button) => {
        button.addEventListener('click', () => applyLanguage(button.dataset.language));
      });

      document.getElementById('focusSearch').addEventListener('click', () => {
        window.setTimeout(() => input.focus(), 250);
      });

      let savedLanguage = 'th';
      try {
        savedLanguage = window.localStorage.getItem('tcsPolicyLanguage') || 'th';
      } catch (error) {
        savedLanguage = 'th';
      }
      applyLanguage(savedLanguage);
    })();
  </script>
</body>
</html>

