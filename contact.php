<?php
/**
 * MarketLink - Contact Us Page with Live Validation
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

$pageTitle = 'Contact Us • Get in Touch with MarketLink';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/about.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/contact.css?v=<?= filemtime(__DIR__ . '/assets/css/contact.css') ?>">
<script>window.BASE_URL = '<?= BASE_URL ?>';</script>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- ====== CONTACT HERO ====== -->
<section class="contact-hero">
  <div class="contact-hero-overlay"></div>
  <div class="contact-hero-content">
    <span class="abt-eyebrow">Get In Touch</span>
    <h1 class="about-hero-title">
      We Would <span class="abt-grad-green">Love to Hear</span><br>
      From <span class="abt-grad-sky">You.</span>
    </h1>
    <p class="about-hero-lead">
      Whether you are a farmer wanting to join, a family with a question, or a partner with an idea — our team is ready to listen.
    </p>
  </div>
</section>

<!-- ====== CONTACT BODY ====== -->
<section class="contact-section">
  <div class="section-container">
    <div class="contact-layout">

      <!-- Left: Info Cards -->
      <div class="contact-info-col">
        <h2 class="contact-info-heading">Contact Information</h2>
        <p class="contact-info-sub">Reach us through any of these channels and we will respond within 24 hours.</p>

        <div class="contact-info-cards">
          <div class="contact-info-card">
            <div class="contact-info-icon"><i class="fa-solid fa-envelope" aria-hidden="true"></i></div>
            <div>
              <div class="contact-info-label">Email Us</div>
              <div class="contact-info-value">support@marketlink.pk</div>
            </div>
          </div>
          <div class="contact-info-card">
            <div class="contact-info-icon"><i class="fa-solid fa-phone" aria-hidden="true"></i></div>
            <div>
              <div class="contact-info-label">Call or WhatsApp</div>
              <div class="contact-info-value">+92 300 123 4567</div>
            </div>
          </div>
          <div class="contact-info-card">
            <div class="contact-info-icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></div>
            <div>
              <div class="contact-info-label">Head Office</div>
              <div class="contact-info-value">Model Town, Lahore, Pakistan</div>
            </div>
          </div>
          <div class="contact-info-card">
            <div class="contact-info-icon"><i class="fa-solid fa-clock" aria-hidden="true"></i></div>
            <div>
              <div class="contact-info-label">Support Hours</div>
              <div class="contact-info-value">Mon – Sat, 9:00 AM – 6:00 PM</div>
            </div>
          </div>
        </div>

        <!-- Response time badge -->
        <div class="contact-response-badge">
          <span class="pulse-dot"></span>
          <span>Average response time: <strong>Under 4 hours</strong></span>
        </div>
      </div>

      <!-- Right: Contact Form with Live Validation & Inquiry Tracking -->
      <div class="contact-form-col">
        <div class="contact-form-card">

          <!-- 2-Feature Mode Switcher Tabs -->
          <div class="contact-tab-nav" role="tablist">
            <button type="button" id="tabBtnSend" class="contact-tab-btn active" role="tab" aria-selected="true" onclick="switchContactTab('send')">
              <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
              <span>Send a Message</span>
            </button>
            <button type="button" id="tabBtnTrack" class="contact-tab-btn" role="tab" aria-selected="false" onclick="switchContactTab('track')">
              <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
              <span>Check Application / Ticket</span>
            </button>
          </div>

          <script>
            function switchContactTab(tab) {
              try {
                var btnSend   = document.getElementById('tabBtnSend');
                var btnTrack  = document.getElementById('tabBtnTrack');
                var panelSend = document.getElementById('panelSendInquiry');
                var panelTrack= document.getElementById('panelTrackInquiry');

                if (!btnSend || !btnTrack || !panelSend || !panelTrack) return;

                if (tab === 'send') {
                  btnSend.classList.add('active');
                  btnTrack.classList.remove('active');
                  btnSend.setAttribute('aria-selected', 'true');
                  btnTrack.setAttribute('aria-selected', 'false');
                  panelSend.style.display = 'block';
                  panelTrack.style.display = 'none';
                } else {
                  btnTrack.classList.add('active');
                  btnSend.classList.remove('active');
                  btnTrack.setAttribute('aria-selected', 'true');
                  btnSend.setAttribute('aria-selected', 'false');
                  panelSend.style.display = 'none';
                  panelTrack.style.display = 'block';

                  var tInput = document.getElementById('trackTicketInput');
                  if (tInput && !tInput.value) {
                    setTimeout(function () { tInput.focus(); }, 50);
                  }
                }
              } catch (e) {
                console.error("Tab switch error:", e);
              }
            }
            window.switchContactTab = switchContactTab;

            document.addEventListener('DOMContentLoaded', function () {
              var btnSend  = document.getElementById('tabBtnSend');
              var btnTrack = document.getElementById('tabBtnTrack');
              if (btnSend)  btnSend.addEventListener('click', function(e) { e.preventDefault(); switchContactTab('send'); });
              if (btnTrack) btnTrack.addEventListener('click', function(e) { e.preventDefault(); switchContactTab('track'); });
            });
          </script>

          <!-- PANEL 1: Send Message Form -->
          <div id="panelSendInquiry">
            <h2 class="contact-form-heading">Send Us a Message</h2>
            <p class="contact-form-sub">Fill in the form below and our team will get back to you shortly.</p>

            <form class="contact-form" id="contactForm" novalidate>

            <!-- Full Name -->
            <div class="cf-field" id="field-name">
              <label class="cf-label" for="contactName">
                Full Name <span class="cf-required">*</span>
              </label>
              <div class="cf-input-wrap">
                <span class="cf-icon"><i class="fa-solid fa-user" aria-hidden="true"></i></span>
                <input
                  type="text"
                  id="contactName"
                  name="name"
                  class="cf-input"
                  placeholder="e.g. Muhammad Iqbal"
                  autocomplete="name"
                  maxlength="80"
                >
                <span class="cf-status-icon" id="status-name"></span>
              </div>
              <div class="cf-feedback" id="feedback-name"></div>
            </div>

            <!-- Email -->
            <div class="cf-field" id="field-email">
              <label class="cf-label" for="contactEmail">
                Email Address <span class="cf-required">*</span>
              </label>
              <div class="cf-input-wrap">
                <span class="cf-icon"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                <input
                  type="email"
                  id="contactEmail"
                  name="email"
                  class="cf-input"
                  placeholder="you@example.com"
                  autocomplete="email"
                >
                <span class="cf-status-icon" id="status-email"></span>
              </div>
              <div class="cf-feedback" id="feedback-email"></div>
            </div>

            <!-- Phone -->
            <div class="cf-field" id="field-phone">
              <label class="cf-label" for="contactPhone">
                Phone Number <span class="cf-required">*</span>
              </label>
              <div class="cf-input-wrap">
                <span class="cf-icon"><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i></span>
                <input
                  type="tel"
                  id="contactPhone"
                  name="phone"
                  class="cf-input"
                  placeholder="03XX-XXXXXXX"
                  autocomplete="tel"
                  maxlength="13"
                >
                <span class="cf-status-icon" id="status-phone"></span>
              </div>
              <div class="cf-feedback" id="feedback-phone"></div>
            </div>

            <!-- Subject -->
            <div class="cf-field" id="field-subject">
              <label class="cf-label" for="contactSubject">
                Subject <span class="cf-required">*</span>
              </label>
              <div class="cf-input-wrap">
                <span class="cf-icon"><i class="fa-solid fa-clipboard-list" aria-hidden="true"></i></span>
                <select id="contactSubject" name="subject" class="cf-input cf-select">
                  <option value="">Select a subject...</option>
                  <option value="farmer_registration">Farmer Registration Help</option>
                  <option value="order_issue">Order / Pickup Issue</option>
                  <option value="general">General Question</option>
                  <option value="partnership">Partnership / Media</option>
                  <option value="feedback">Feedback & Suggestions</option>
                </select>
                <span class="cf-status-icon" id="status-subject"></span>
              </div>
              <div class="cf-feedback" id="feedback-subject"></div>
            </div>

            <!-- Message -->
            <div class="cf-field" id="field-message">
              <label class="cf-label" for="contactMessage">
                Message <span class="cf-required">*</span>
              </label>
              <div class="cf-input-wrap cf-textarea-wrap">
                <textarea
                  id="contactMessage"
                  name="message"
                  class="cf-input cf-textarea"
                  placeholder="Tell us how we can help you..."
                  rows="5"
                  maxlength="1000"
                ></textarea>
                <span class="cf-status-icon cf-textarea-icon" id="status-message"></span>
              </div>
              <div class="cf-field-footer">
                <div class="cf-feedback" id="feedback-message"></div>
                <div class="cf-char-count" id="charCount">0 / 1000</div>
              </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="cf-submit-btn" id="contactSubmitBtn">
              <span class="cf-btn-text">Send Message</span>
              <span class="cf-btn-icon"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
            </button>

            <!-- Success state -->
            <div class="cf-success-banner" id="contactSuccess" style="display:none;">
              <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
              <div>
                <strong>Message Sent!</strong>
                <div id="contactSuccessDetail">We will get back to you within 24 hours.</div>
                <button type="button" id="btnTrackSubmittedTicket" class="cf-btn-text" style="background:none; border:none; color:var(--primary-400); font-weight:700; text-decoration:underline; cursor:pointer; padding:0; margin-top:0.4rem; display:none;">
                  Track This Ticket Now &rarr;
                </button>
              </div>
            </div>

          </form>
          </div>
          <!-- END PANEL 1 -->

          <!-- PANEL 2: Track Inquiry / Ticket Application Status -->
          <div id="panelTrackInquiry" style="display:none;">
            <h2 class="contact-form-heading">Track Inquiry Status</h2>
            <p class="contact-form-sub">Check live review progress, administrator replies, and status updates for your submitted inquiry.</p>

            <form class="contact-form" id="trackInquiryForm" novalidate onsubmit="handleTrackInquiry(event)">
              <div class="cf-field" id="field-track-ticket">
                <label class="cf-label" for="trackTicketInput">
                  Ticket Number <span class="cf-required">*</span>
                </label>
                <div class="cf-input-wrap">
                  <span class="cf-icon"><i class="fa-solid fa-hashtag" aria-hidden="true"></i></span>
                  <input
                    type="text"
                    id="trackTicketInput"
                    name="ticket"
                    class="cf-input"
                    placeholder="e.g. INQ-00012 or 12"
                    required
                    autocomplete="off"
                    style="text-transform:uppercase; font-weight:700; letter-spacing:0.04em;"
                  >
                  <span class="cf-status-icon" id="status-track-ticket"></span>
                </div>
                <div class="cf-feedback" id="feedback-track-ticket"></div>
              </div>

              <div class="cf-field" id="field-track-email">
                <label class="cf-label" for="trackEmailInput">
                  Your Email Address <span style="font-weight:400; color:var(--text-muted);">(Optional Security Match)</span>
                </label>
                <div class="cf-input-wrap">
                  <span class="cf-icon"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                  <input
                    type="email"
                    id="trackEmailInput"
                    name="email"
                    class="cf-input"
                    placeholder="you@example.com"
                    autocomplete="email"
                  >
                  <span class="cf-status-icon" id="status-track-email"></span>
                </div>
                <div class="cf-feedback" id="feedback-track-email"></div>
              </div>

              <button type="submit" class="cf-submit-btn" id="trackSubmitBtn">
                <span class="cf-btn-text">Check Application Status</span>
                <span class="cf-btn-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
              </button>
            </form>

            <!-- Loading Spinner -->
            <div id="trackLoading" style="display:none; text-align:center; padding:2rem 1rem; color:var(--text-secondary);">
              <i class="fa-solid fa-spinner fa-spin" style="font-size:1.8rem; color:var(--primary-400); margin-bottom:0.6rem; display:block;"></i>
              <span>Locating your inquiry in database...</span>
            </div>

            <!-- Error Notification -->
            <div id="trackErrorBox" style="display:none; background:rgba(239,68,68,0.12); border:1px solid #ef4444; color:#f87171; padding:1rem 1.25rem; border-radius:var(--radius-md); margin-top:1.25rem; font-size:0.88rem;"></div>

            <!-- Result Box -->
            <div id="trackResultBox" style="display:none; margin-top:1.75rem; background:var(--bg-surface-elevated, rgba(255,255,255,0.03)); border:1.5px solid var(--border-color); border-radius:var(--radius-lg); padding:1.5rem;">
              
              <!-- Result Header -->
              <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; border-bottom:1px solid var(--border-color); padding-bottom:1rem;">
                <div>
                  <span style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Inquiry Ticket</span>
                  <h3 id="resTicketNumber" style="font-family:monospace; font-size:1.25rem; font-weight:800; color:var(--primary-400); margin:0.15rem 0 0 0;">#INQ-00000</h3>
                </div>
                <div id="resStatusBadge">
                  <!-- Dynamic status badge -->
                </div>
              </div>

              <!-- 3-Step Live Progress Indicator -->
              <div class="track-stepper" id="resStepper">
                <div class="track-step" id="step1">
                  <div class="track-step-circle">1</div>
                  <span class="track-step-label">Received</span>
                </div>
                <div class="track-step" id="step2">
                  <div class="track-step-circle">2</div>
                  <span class="track-step-label">Seen by Admin</span>
                </div>
                <div class="track-step" id="step3">
                  <div class="track-step-circle">3</div>
                  <span class="track-step-label">Replied</span>
                </div>
              </div>

              <!-- Status Summary Note -->
              <div id="resStatusDescBox" style="background:rgba(255,255,255,0.04); border-radius:var(--radius-md); padding:0.85rem 1rem; margin-bottom:1.25rem; font-size:0.85rem; color:var(--text-secondary); line-height:1.5;">
              </div>

              <!-- Details Grid -->
              <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1.25rem; font-size:0.85rem;">
                <div>
                  <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase; font-weight:700;">Subject</span>
                  <strong id="resSubject" style="color:var(--text-primary);"></strong>
                </div>
                <div>
                  <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase; font-weight:700;">Submitted Time</span>
                  <span id="resCreatedAt" style="color:var(--text-primary); font-weight:600;"></span>
                </div>
              </div>

              <!-- User's Original Message Preview -->
              <div style="margin-bottom:1.25rem;">
                <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase; font-weight:700; margin-bottom:0.35rem;">Your Message</span>
                <div id="resOriginalMessage" style="background:var(--bg-surface, rgba(0,0,0,0.2)); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:0.85rem 1rem; color:var(--text-secondary); font-size:0.88rem; line-height:1.55; white-space:pre-wrap;"></div>
              </div>

              <!-- Official Admin Reply Card (If Available) -->
              <div id="resAdminReplyCard" style="display:none; background:rgba(34,197,94,0.08); border:1.5px solid rgba(34,197,94,0.35); border-radius:var(--radius-md); padding:1.25rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.6rem; flex-wrap:wrap; gap:0.5rem;">
                  <span style="font-size:0.82rem; font-weight:800; color:var(--primary-400); display:flex; align-items:center; gap:0.4rem;">
                    <i class="fa-solid fa-reply-all"></i> Official Response from Administration
                  </span>
                  <span id="resRepliedAt" style="font-size:0.75rem; color:var(--text-muted);"></span>
                </div>
                <div id="resAdminReplyText" style="color:var(--text-primary); font-size:0.92rem; line-height:1.65; white-space:pre-wrap; margin-bottom:0.75rem;"></div>
                <div style="font-size:0.78rem; color:var(--primary-400); font-weight:600;">
                  <i class="fa-solid fa-shield-check"></i> Dispatched by <span id="resRepliedBy">MarketLink Admin Team</span>
                </div>
              </div>

            </div>

          </div>
          <!-- END PANEL 2 -->
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ====== FAQ ROW ====== -->
<section class="abt-section" style="padding-top:2rem;">
  <div class="section-container">
    <div class="section-header">
      <span class="section-tag">Quick Answers</span>
      <h2 class="section-title">Common Questions</h2>
    </div>
    <div class="abt-principles-grid">
      <div class="abt-principle-card">
        <div class="abt-principle-icon"><i class="fa-solid fa-wheat-awn" aria-hidden="true"></i></div>
        <h3 class="abt-principle-title">How do I register as a farmer?</h3>
        <p class="abt-principle-desc">Create a free account, select "Farmer" as your role, and complete your stall profile. Our team reviews and approves within 48 hours.</p>
      </div>
      <div class="abt-principle-card">
        <div class="abt-principle-icon"><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i></div>
        <h3 class="abt-principle-title">When can I pick up my basket?</h3>
        <p class="abt-principle-desc">Pickup windows are set by each farmer for their market day. You choose your slot when reserving — usually Saturday or Sunday mornings.</p>
      </div>
      <div class="abt-principle-card">
        <div class="abt-principle-icon"><i class="fa-solid fa-credit-card" aria-hidden="true"></i></div>
        <h3 class="abt-principle-title">How do payments work?</h3>
        <p class="abt-principle-desc">Payment is settled directly at the stall via cash, Easypaisa, or JazzCash. MarketLink charges the farmer zero commission.</p>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>

<script src="<?= BASE_URL ?>/assets/js/contact-validation.js?v=<?= filemtime(__DIR__ . '/assets/js/contact-validation.js') ?>"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
