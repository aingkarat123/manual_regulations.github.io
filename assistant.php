<?php
session_start();
require_once __DIR__ . '/policy_access.php';

if (!policyAccessGranted()) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบ!';
    header('location: ../../../index.php');
    exit;
}

if (empty($_SESSION['policy_ai_csrf'])) {
    $_SESSION['policy_ai_csrf'] = bin2hex(random_bytes(24));
}

$isGuest = policyGuestAccess();
$displayNameTh = htmlspecialchars((string) ($_SESSION['User_name'] ?? 'ผู้มาเยี่ยมชม'), ENT_QUOTES, 'UTF-8');
$displayNameEn = htmlspecialchars((string) ($_SESSION['User_name'] ?? 'Visitor'), ENT_QUOTES, 'UTF-8');
$csrfToken = htmlspecialchars((string) $_SESSION['policy_ai_csrf'], ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ผู้ช่วยค้นข้อมูล HR | TCS Policy Center</title>
  <link rel="shortcut icon" type="image/png" href="../../assets/images/logos/logo_new.png">
  <link rel="stylesheet" href="../../assets/css/styles.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/policy.css?v=20261020-language-all1">
</head>
<body class="policy-assistant">
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
        <a class="side-link" href="dashboard.php"><i class="fa-solid fa-house" aria-hidden="true"></i><span data-th="หน้าหลัก" data-en="Home">หน้าหลัก</span></a>
        <a class="side-link active" href="assistant.php"><i class="fa-solid fa-robot" aria-hidden="true"></i><span data-th="สอบถาม AI" data-en="Ask AI">สอบถาม AI</span></a>
        <a class="side-link" href="dashboard.php#categories"><i class="fa-solid fa-table-cells-large" aria-hidden="true"></i><span data-th="หมวดหมู่ทั้งหมด" data-en="Categories">หมวดหมู่ทั้งหมด</span></a>
        <a class="side-link portal-link" href="../dashboard.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span data-th="กลับหน้าระบบ" data-en="Back to Portal">กลับหน้าระบบ</span></a>
      </nav>
    </aside>

    <main class="assistant-main">
      <div class="assistant-shell">
        <section class="assistant-intro" aria-labelledby="assistantTitle">
          <span class="assistant-mark"><i class="fa-solid fa-robot" aria-hidden="true"></i></span>
          <div>
            <span class="assistant-eyebrow" data-th="ผู้ช่วยข้อมูลฝ่ายบุคคล" data-en="HR INFORMATION ASSISTANT">ผู้ช่วยข้อมูลฝ่ายบุคคล</span>
            <h1 id="assistantTitle" data-th="สอบถามข้อบังคับบริษัท" data-en="Ask about company policies">สอบถามข้อบังคับบริษัท</h1>
            <p data-th="พิมพ์คำถามได้ตามปกติ ระบบจะค้นคำตอบพร้อมเลขข้อและหน้าต้นฉบับให้ตรวจสอบ" data-en="Ask in plain language. The system will find relevant clauses and source pages for you to verify.">พิมพ์คำถามได้ตามปกติ ระบบจะค้นคำตอบพร้อมเลขข้อและหน้าต้นฉบับให้ตรวจสอบ</p>
          </div>
          <span class="assistant-status"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> <span data-th="อ้างอิงเอกสารภายใน" data-en="Internal document sources">อ้างอิงเอกสารภายใน</span></span>
        </section>

        <div class="assistant-layout">
          <section class="chat-panel" aria-label="สนทนากับผู้ช่วยค้นข้อมูล HR">
            <div class="chat-messages" id="chatMessages" aria-live="polite">
              <article class="chat-message is-assistant">
                <span class="message-avatar"><i class="fa-solid fa-robot" aria-hidden="true"></i></span>
                <div class="message-bubble">
                  <strong data-th="สวัสดีครับ ต้องการสอบถามเรื่องใด?" data-en="Hello. What would you like to ask?">สวัสดีครับ ต้องการสอบถามเรื่องใด?</strong>
                  <p data-th="ถามเรื่องวันลา เวลาทำงาน ค่าล่วงเวลา วินัย หรือข้อบังคับอื่น ๆ ได้เลยครับ" data-en="You can ask about leave, working hours, overtime, discipline or other company policies.">ถามเรื่องวันลา เวลาทำงาน ค่าล่วงเวลา วินัย หรือข้อบังคับอื่น ๆ ได้เลยครับ</p>
                </div>
              </article>
            </div>

            <div class="suggested-questions" aria-label="คำถามแนะนำ">
              <span data-th="คำถามที่พบบ่อย" data-en="Common questions">คำถามที่พบบ่อย</span>
              <button type="button" data-question-th="ลากิจได้กี่วัน และต้องแจ้งอย่างไร" data-question-en="How many personal leave days are allowed and how should I notify my supervisor?" data-th="ลากิจได้กี่วัน" data-en="Personal leave">ลากิจได้กี่วัน</button>
              <button type="button" data-question-th="การลาป่วยต้องใช้ใบรับรองแพทย์เมื่อใด" data-question-en="When is a medical certificate required for sick leave?" data-th="ใบรับรองแพทย์" data-en="Medical certificate">ใบรับรองแพทย์</button>
              <button type="button" data-question-th="การทำงานล่วงเวลามีหลักเกณฑ์อย่างไร" data-question-en="What are the rules for overtime work?" data-th="หลักเกณฑ์ OT" data-en="Overtime rules">หลักเกณฑ์ OT</button>
              <button type="button" data-question-th="พนักงานต้องยื่นลาออกล่วงหน้ากี่วัน" data-question-en="How many days in advance must an employee submit a resignation?" data-th="การลาออก" data-en="Resignation">การลาออก</button>
            </div>

            <form class="chat-composer" id="questionForm">
              <label class="sr-only" for="questionInput" data-th="พิมพ์คำถาม" data-en="Type your question">พิมพ์คำถาม</label>
              <textarea id="questionInput" rows="2" maxlength="500" placeholder="พิมพ์คำถามเกี่ยวกับการลา เวลาทำงาน ค่าล่วงเวลา วินัย หรือข้อบังคับอื่น ๆ" data-placeholder-th="พิมพ์คำถามเกี่ยวกับการลา เวลาทำงาน ค่าล่วงเวลา วินัย หรือข้อบังคับอื่น ๆ" data-placeholder-en="Ask about leave, working hours, overtime, discipline or another policy" required></textarea>
              <button id="sendQuestion" type="submit" aria-label="ส่งคำถาม" data-aria-th="ส่งคำถาม" data-aria-en="Send question">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                <span data-th="ส่งคำถาม" data-en="Send">ส่งคำถาม</span>
              </button>
            </form>
            <p class="composer-note" data-th="คำตอบนี้ใช้สำหรับค้นข้อมูลเบื้องต้น การอนุมัติสิทธิหรือกรณีเฉพาะบุคคลให้ติดต่อ HR" data-en="This assistant provides initial guidance. Contact HR for approvals or individual cases.">คำตอบนี้ใช้สำหรับค้นข้อมูลเบื้องต้น การอนุมัติสิทธิหรือกรณีเฉพาะบุคคลให้ติดต่อ HR</p>
          </section>

          <aside class="answer-guide" aria-label="ขอบเขตการตอบคำถาม">
            <h2><i class="fa-solid fa-circle-check" aria-hidden="true"></i> <span data-th="คำตอบที่ตรวจสอบได้" data-en="Verifiable answers">คำตอบที่ตรวจสอบได้</span></h2>
            <ul>
              <li data-th="ค้นสิทธิและหลักเกณฑ์จากข้อบังคับ" data-en="Find entitlements and rules in company policies">ค้นสิทธิและหลักเกณฑ์จากข้อบังคับ</li>
              <li data-th="แสดงเลขข้อ หมวด และหน้าต้นฉบับ" data-en="Show clause, chapter and source page references">แสดงเลขข้อ หมวด และหน้าต้นฉบับ</li>
              <li data-th="พาไปเปิดเนื้อหาฉบับเต็มได้ทันที" data-en="Open the full official text immediately">พาไปเปิดเนื้อหาฉบับเต็มได้ทันที</li>
            </ul>
            <div class="guide-warning">
              <i class="fa-solid fa-user-shield" aria-hidden="true"></i>
              <div>
                <strong data-th="ระบบไม่อนุมัติแทน HR" data-en="The assistant cannot approve on behalf of HR">ระบบไม่อนุมัติแทน HR</strong>
                <span data-th="หากข้อมูลไม่ชัดเจน ระบบจะแนะนำให้สอบถาม HR โดยตรงและจะไม่คาดเดาคำตอบ" data-en="If the policy is unclear, the assistant will direct you to HR instead of guessing.">หากข้อมูลไม่ชัดเจน ระบบจะแนะนำให้สอบถาม HR โดยตรงและจะไม่คาดเดาคำตอบ</span>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </main>
  </div>

  <script>
    (() => {
      const csrfToken = '<?= $csrfToken ?>';
      const form = document.getElementById('questionForm');
      const input = document.getElementById('questionInput');
      const submit = document.getElementById('sendQuestion');
      const messages = document.getElementById('chatMessages');
      const languageButtons = [...document.querySelectorAll('[data-language]')];
      let busy = false;
      let currentLanguage = 'th';

      function applyLanguage(language) {
        currentLanguage = language === 'en' ? 'en' : 'th';
        document.documentElement.lang = currentLanguage;
        document.title = currentLanguage === 'en'
          ? 'HR Policy Assistant | TCS Policy Center'
          : 'ผู้ช่วยค้นข้อมูล HR | TCS Policy Center';
        document.querySelectorAll('[data-th][data-en]').forEach((element) => {
          element.textContent = element.dataset[currentLanguage];
        });
        document.querySelectorAll('[data-placeholder-th][data-placeholder-en]').forEach((element) => {
          element.placeholder = currentLanguage === 'en' ? element.dataset.placeholderEn : element.dataset.placeholderTh;
        });
        document.querySelectorAll('[data-aria-th][data-aria-en]').forEach((element) => {
          element.setAttribute('aria-label', currentLanguage === 'en' ? element.dataset.ariaEn : element.dataset.ariaTh);
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

      function scrollToLatest() {
        messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });
      }

      function addUserMessage(text) {
        const article = document.createElement('article');
        article.className = 'chat-message is-user';
        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';
        const paragraph = document.createElement('p');
        paragraph.textContent = text;
        bubble.appendChild(paragraph);
        article.appendChild(bubble);
        messages.appendChild(article);
      }

      function addLoadingMessage(language) {
        const article = document.createElement('article');
        article.className = 'chat-message is-assistant is-loading';
        article.id = 'loadingMessage';
        const avatar = document.createElement('span');
        avatar.className = 'message-avatar';
        avatar.innerHTML = '<i class="fa-solid fa-robot" aria-hidden="true"></i>';
        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';
        const loadingLabel = language === 'en' ? 'Searching policy content' : 'กำลังค้นข้อมูล';
        bubble.innerHTML = `<span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span><span class="sr-only">${loadingLabel}</span>`;
        article.append(avatar, bubble);
        messages.appendChild(article);
      }

      function addAssistantMessage(answer, references = [], warning = '', language = currentLanguage) {
        document.getElementById('loadingMessage')?.remove();
        const article = document.createElement('article');
        article.className = 'chat-message is-assistant';

        const avatar = document.createElement('span');
        avatar.className = 'message-avatar';
        avatar.innerHTML = '<i class="fa-solid fa-robot" aria-hidden="true"></i>';

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';
        const paragraph = document.createElement('p');
        paragraph.className = 'answer-text';
        paragraph.textContent = answer;
        bubble.appendChild(paragraph);

        if (references.length) {
          const sourceTitle = document.createElement('strong');
          sourceTitle.className = 'source-title';
          sourceTitle.textContent = language === 'en' ? 'Policy references' : 'อ้างอิงจากข้อบังคับ';
          bubble.appendChild(sourceTitle);

          const sourceList = document.createElement('div');
          sourceList.className = 'answer-sources';
          references.forEach((source) => {
            const link = document.createElement('a');
            link.href = source.url;
            const label = document.createElement('span');
            label.textContent = language === 'en'
              ? `${source.section ? `Clause ${source.section}` : 'Content'} · Page ${source.page}`
              : `${source.section ? `ข้อ ${source.section}` : 'เนื้อหา'} · หน้า ${source.page}`;
            const title = document.createElement('small');
            title.textContent = source.chapter_title;
            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-arrow-up-right-from-square';
            icon.setAttribute('aria-hidden', 'true');
            link.append(label, title, icon);
            sourceList.appendChild(link);
          });
          bubble.appendChild(sourceList);
        }

        if (warning) {
          const note = document.createElement('span');
          note.className = 'answer-warning';
          note.textContent = warning;
          bubble.appendChild(note);
        }

        article.append(avatar, bubble);
        messages.appendChild(article);
        scrollToLatest();
      }

      async function ask(question) {
        if (busy || !question.trim()) return;
        const requestLanguage = currentLanguage;
        busy = true;
        submit.disabled = true;
        input.disabled = true;
        languageButtons.forEach((button) => { button.disabled = true; });
        messages.setAttribute('aria-busy', 'true');
        addUserMessage(question.trim());
        addLoadingMessage(requestLanguage);
        scrollToLatest();

        try {
          const response = await fetch('api/ask.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-Token': csrfToken,
            },
            body: JSON.stringify({ question: question.trim(), language: requestLanguage }),
          });
          const data = await response.json();
          if (!response.ok || !data.ok) {
            const requestError = new Error(data.message || (requestLanguage === 'en' ? 'Unable to search policy content' : 'ไม่สามารถค้นข้อมูลได้'));
            requestError.userMessage = requestError.message;
            throw requestError;
          }
          addAssistantMessage(data.answer, data.references || [], data.warning || '', requestLanguage);
        } catch (error) {
          const fallbackMessage = requestLanguage === 'en'
            ? 'Sorry, the policy search is temporarily unavailable. Please try again or contact HR.'
            : 'ขออภัย ระบบค้นข้อมูลขัดข้อง กรุณาลองใหม่อีกครั้งหรือติดต่อ HR';
          addAssistantMessage(
            typeof error?.userMessage === 'string' && error.userMessage.trim() !== ''
              ? error.userMessage
              : fallbackMessage,
            [],
            requestLanguage === 'en'
              ? 'Your question was not sent to an external service.'
              : 'ระบบไม่ได้ส่งคำถามออกไปภายนอก',
            requestLanguage
          );
        } finally {
          document.getElementById('loadingMessage')?.remove();
          busy = false;
          submit.disabled = false;
          input.disabled = false;
          languageButtons.forEach((button) => { button.disabled = false; });
          messages.removeAttribute('aria-busy');
          input.value = '';
          input.focus();
        }
      }

      form.addEventListener('submit', (event) => {
        event.preventDefault();
        ask(input.value);
      });

      input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
          event.preventDefault();
          form.requestSubmit();
        }
      });

      document.querySelectorAll('[data-question-th][data-question-en]').forEach((button) => {
        button.addEventListener('click', () => {
          ask(currentLanguage === 'en' ? button.dataset.questionEn : button.dataset.questionTh);
        });
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
      applyLanguage(savedLanguage);
    })();
  </script>
</body>
</html>
