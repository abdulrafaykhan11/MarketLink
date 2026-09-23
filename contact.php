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
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/contact.css">

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
            <div class="contact-info-icon">📧</div>
            <div>
              <div class="contact-info-label">Email Us</div>
              <div class="contact-info-value">support@marketlink.pk</div>
            </div>
          </div>
          <div class="contact-info-card">
            <div class="contact-info-icon">📞</div>
            <div>
              <div class="contact-info-label">Call or WhatsApp</div>
              <div class="contact-info-value">+92 300 123 4567</div>
            </div>
          </div>
          <div class="contact-info-card">
            <div class="contact-info-icon">📍</div>
            <div>
              <div class="contact-info-label">Head Office</div>
              <div class="contact-info-value">Model Town, Lahore, Pakistan</div>
            </div>
          </div>
          <div class="contact-info-card">
            <div class="contact-info-icon">🕐</div>
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

      <!-- Right: Contact Form with Live Validation -->
      <div class="contact-form-col">
        <div class="contact-form-card">
          <h2 class="contact-form-heading">Send Us a Message</h2>
          <p class="contact-form-sub">Fill in the form below and we will get back to you shortly.</p>

          <form class="contact-form" id="contactForm" novalidate>

            <!-- Full Name -->
            <div class="cf-field" id="field-name">
              <label class="cf-label" for="contactName">
                Full Name <span class="cf-required">*</span>
              </label>
              <div class="cf-input-wrap">
                <span class="cf-icon">👤</span>
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
                <span class="cf-icon">✉️</span>
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
                <span class="cf-icon">📱</span>
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
                <span class="cf-icon">📋</span>
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
              <span class="cf-btn-icon">→</span>
            </button>

            <!-- Success state -->
            <div class="cf-success-banner" id="contactSuccess" style="display:none;">
              <span>✅</span>
              <div>
                <strong>Message Sent!</strong>
                <div>We will get back to you within 24 hours.</div>
              </div>
            </div>

          </form>
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
        <div class="abt-principle-icon">🌾</div>
        <h3 class="abt-principle-title">How do I register as a farmer?</h3>
        <p class="abt-principle-desc">Create a free account, select "Farmer" as your role, and complete your stall profile. Our team reviews and approves within 48 hours.</p>
      </div>
      <div class="abt-principle-card">
        <div class="abt-principle-icon">🛒</div>
        <h3 class="abt-principle-title">When can I pick up my basket?</h3>
        <p class="abt-principle-desc">Pickup windows are set by each farmer for their market day. You choose your slot when reserving — usually Saturday or Sunday mornings.</p>
      </div>
      <div class="abt-principle-card">
        <div class="abt-principle-icon">💳</div>
        <h3 class="abt-principle-title">How do payments work?</h3>
        <p class="abt-principle-desc">Payment is settled directly at the stall via cash, Easypaisa, or JazzCash. MarketLink charges the farmer zero commission.</p>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>

<script src="<?= BASE_URL ?>/assets/js/contact-validation.js"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
