/**
 * MarketLink - Contact Form Live Validation & Server Submission
 * Real-time validation on every field with visual feedback & real email dispatch
 */

(function () {
  'use strict';

  // Pakistani mobile number regex: 03XX-XXXXXXX or 03XXXXXXXXX
  const PK_PHONE_REGEX = /^((\+92|0092|03)\d{2}[-\s]?\d{7})$/;
  const EMAIL_REGEX    = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  // -----------------------------------------------------------------------
  // Field Definitions — name, validate fn, messages
  // -----------------------------------------------------------------------
  const fields = {
    contactName: {
      validate(val) {
        if (!val.trim())                    return { ok: false, msg: 'Please enter your full name.' };
        if (val.trim().length < 3)          return { ok: false, msg: 'Name must be at least 3 characters.' };
        if (val.trim().length > 80)         return { ok: false, msg: 'Name is too long (max 80 chars).' };
        if (/[^a-zA-Z\s\u0600-\u06FF]/.test(val)) return { ok: false, msg: 'Name can only contain letters.' };
        return { ok: true, msg: '✓ Looks good!' };
      }
    },
    contactEmail: {
      validate(val) {
        if (!val.trim())              return { ok: false, msg: 'Please enter your email address.' };
        if (!EMAIL_REGEX.test(val))   return { ok: false, msg: 'Please enter a valid email (e.g. you@example.com).' };
        return { ok: true, msg: '✓ Valid email address.' };
      }
    },
    contactPhone: {
      validate(val) {
        if (!val.trim())              return { ok: true, msg: '✓ Optional (recommended for WhatsApp updates).' };
        if (!PK_PHONE_REGEX.test(val.trim())) return { ok: false, msg: 'Enter a valid Pakistani number (e.g. 0300-1234567).' };
        return { ok: true, msg: '✓ Valid Pakistani number.' };
      }
    },
    contactSubject: {
      validate(val) {
        if (!val) return { ok: false, msg: 'Please select a subject.' };
        return { ok: true, msg: '✓ Subject selected.' };
      }
    },
    contactMessage: {
      validate(val) {
        if (!val.trim())            return { ok: false, msg: 'Please write your message.' };
        if (val.trim().length < 15) return { ok: false, msg: 'Message is too short — please add more detail (min 15 chars).' };
        if (val.length > 1000)      return { ok: false, msg: 'Message is too long (max 1000 characters).' };
        return { ok: true, msg: '✓ Great, message looks good.' };
      }
    }
  };

  // Mapping for field IDs to feedback/wrapper IDs
  const ID_MAP = {
    contactName:    'name',
    contactEmail:   'email',
    contactPhone:   'phone',
    contactSubject: 'subject',
    contactMessage: 'message'
  };

  function applyStateById(inputId, result) {
    const key      = ID_MAP[inputId];
    const wrapper  = document.getElementById('field-' + key);
    const feedback = document.getElementById('feedback-' + key);

    if (!wrapper) return;
    wrapper.classList.remove('is-valid', 'is-invalid');
    if (result === null) { if (feedback) feedback.textContent = ''; return; }
    wrapper.classList.add(result.ok ? 'is-valid' : 'is-invalid');
    if (feedback) feedback.textContent = result.msg;
  }

  // -----------------------------------------------------------------------
  // Char counter for textarea
  // -----------------------------------------------------------------------
  const msgInput   = document.getElementById('contactMessage');
  const charCount  = document.getElementById('charCount');

  if (msgInput && charCount) {
    msgInput.addEventListener('input', function () {
      const len = this.value.length;
      charCount.textContent = len + ' / 1000';
      charCount.classList.remove('near-limit', 'at-limit');
      if (len >= 1000) charCount.classList.add('at-limit');
      else if (len >= 850) charCount.classList.add('near-limit');
    });
  }

  // -----------------------------------------------------------------------
  // Attach live validation to each field
  // -----------------------------------------------------------------------
  Object.entries(fields).forEach(([id, def]) => {
    const el = document.getElementById(id);
    if (!el) return;

    const eventType = (el.tagName === 'SELECT') ? 'change' : 'input';

    el.addEventListener(eventType, function () {
      const result = def.validate(this.value);
      applyStateById(id, result);
    });

    el.addEventListener('blur', function () {
      if (!this.value.trim() && this.value === '') return;
      const result = def.validate(this.value);
      applyStateById(id, result);
    });
  });

  // -----------------------------------------------------------------------
  // Phone: auto-format as user types (0300-1234567)
  // -----------------------------------------------------------------------
  const phoneInput = document.getElementById('contactPhone');
  if (phoneInput) {
    phoneInput.addEventListener('input', function () {
      let val = this.value.replace(/[^\d+]/g, '');
      if (val.startsWith('0') && val.length > 4) {
        val = val.slice(0, 4) + '-' + val.slice(4, 11);
      }
      this.value = val;
    });
  }

  // -----------------------------------------------------------------------
  // Form Submit: validate all fields, send AJAX request, handle emails & response
  // -----------------------------------------------------------------------
  const form      = document.getElementById('contactForm');
  const submitBtn = document.getElementById('contactSubmitBtn');
  const successEl = document.getElementById('contactSuccess');

  if (form) {
    form.addEventListener('submit', async function (e) {
      e.preventDefault();

      let allValid = true;

      // Validate every field
      Object.entries(fields).forEach(([id, def]) => {
        const el = document.getElementById(id);
        if (!el) return;
        const result = def.validate(el.value);
        applyStateById(id, result);
        if (!result.ok) allValid = false;
      });

      if (!allValid) {
        const firstErr = form.querySelector('.is-invalid .cf-input, .is-invalid .cf-select, .is-invalid .cf-textarea');
        if (firstErr) firstErr.focus();
        return;
      }

      // Prepare payload
      const payload = {
        name:    document.getElementById('contactName').value.trim(),
        email:   document.getElementById('contactEmail').value.trim(),
        phone:   document.getElementById('contactPhone').value.trim(),
        subject: document.getElementById('contactSubject').value,
        message: document.getElementById('contactMessage').value.trim()
      };

      // Set Loading UI
      const btnText = submitBtn ? submitBtn.querySelector('.cf-btn-text') : null;
      const originalText = btnText ? btnText.textContent : 'Send Message';
      if (submitBtn) {
        submitBtn.disabled = true;
        if (btnText) btnText.textContent = 'Sending & Notifying Admin…';
      }

      // Remove any existing error banner
      const oldErr = document.getElementById('cfErrorBanner');
      if (oldErr) oldErr.remove();

      try {
        const endpoint = (window.BASE_URL ? window.BASE_URL : '') + '/api/contact_submit.php';
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.success) {
          // Success UI
          if (submitBtn) submitBtn.style.display = 'none';
          if (successEl) {
            successEl.style.display = 'flex';
            const sub = successEl.querySelector('div div');
            if (sub) {
              sub.innerHTML = `Your inquiry <strong>#${result.ticket_number}</strong> was logged and sent to platform administration. A confirmation email has been dispatched to <strong>${payload.email}</strong>.`;
            }
          }
          form.reset();
          Object.keys(fields).forEach(id => applyStateById(id, null));
          if (charCount) charCount.textContent = '0 / 1000';
        } else {
          throw new Error(result.error || 'Failed to submit inquiry.');
        }
      } catch (err) {
        console.error('Contact Form Error:', err);
        const errBanner = document.createElement('div');
        errBanner.id = 'cfErrorBanner';
        errBanner.style.cssText = 'background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 12px 16px; border-radius: 8px; margin-top: 14px; font-size: 0.88rem;';
        errBanner.textContent = '⚠️ ' + (err.message || 'Error sending message. Please verify your connection and try again.');
        form.appendChild(errBanner);

        if (submitBtn) {
          submitBtn.disabled = false;
          if (btnText) btnText.textContent = originalText;
        }
      }
    });
  }

})();
