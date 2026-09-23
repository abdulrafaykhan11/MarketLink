/**
 * MarketLink - Contact Form Live Validation
 * Real-time validation on every field with visual feedback
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
        const cleaned = val.replace(/[\s\-]/g, '');
        if (!val.trim())                      return { ok: false, msg: 'Please enter your phone number.' };
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
        if (!val.trim())         return { ok: false, msg: 'Please write your message.' };
        if (val.trim().length < 20) return { ok: false, msg: 'Message is too short — please add more detail (min 20 chars).' };
        if (val.length > 1000)   return { ok: false, msg: 'Message is too long (max 1000 characters).' };
        return { ok: true, msg: '✓ Great, message looks good.' };
      }
    }
  };

  // -----------------------------------------------------------------------
  // Helper: apply visual state to a field
  // -----------------------------------------------------------------------
  function applyState(fieldId, result) {
    const wrapper  = document.getElementById('field-' + fieldId.replace('contact', '').toLowerCase());
    const feedback = document.getElementById('feedback-' + fieldId.replace('contact', '').toLowerCase());

    if (!wrapper) return;

    wrapper.classList.remove('is-valid', 'is-invalid');
    if (result === null) { feedback && (feedback.textContent = ''); return; }

    wrapper.classList.add(result.ok ? 'is-valid' : 'is-invalid');
    if (feedback) feedback.textContent = result.msg;
  }

  // Mapping for field IDs to feedback/wrapper IDs
  const ID_MAP = {
    contactName:    'name',
    contactEmail:   'email',
    contactPhone:   'phone',
    contactSubject: 'subject',
    contactMessage: 'message'
  };

  function applyStateById(inputId, result) {
    const key     = ID_MAP[inputId];
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

    // Show validation on input / change
    el.addEventListener(eventType, function () {
      const result = def.validate(this.value);
      applyStateById(id, result);
    });

    // Also validate on blur (when user leaves field)
    el.addEventListener('blur', function () {
      if (!this.value.trim() && this.value === '') return; // skip empty on first blur
      const result = def.validate(this.value);
      applyStateById(id, result);
    });

    // Reset on focus if currently invalid (give user fresh start)
    el.addEventListener('focus', function () {
      const wrapper = document.getElementById('field-' + ID_MAP[id]);
      if (wrapper && wrapper.classList.contains('is-invalid')) {
        // keep showing error; do not reset
      }
    });
  });

  // -----------------------------------------------------------------------
  // Phone: auto-format as user types (0300-1234567)
  // -----------------------------------------------------------------------
  const phoneInput = document.getElementById('contactPhone');
  if (phoneInput) {
    phoneInput.addEventListener('input', function () {
      let val = this.value.replace(/[^\d+]/g, '');
      // Format: 03XX-XXXXXXX
      if (val.startsWith('0') && val.length > 4) {
        val = val.slice(0, 4) + '-' + val.slice(4, 11);
      }
      this.value = val;
    });
  }

  // -----------------------------------------------------------------------
  // Form Submit: validate all fields, show errors or success
  // -----------------------------------------------------------------------
  const form      = document.getElementById('contactForm');
  const submitBtn = document.getElementById('contactSubmitBtn');
  const successEl = document.getElementById('contactSuccess');

  if (form) {
    form.addEventListener('submit', function (e) {
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
        // Scroll to first error
        const firstErr = form.querySelector('.is-invalid .cf-input, .is-invalid .cf-select, .is-invalid .cf-textarea');
        if (firstErr) firstErr.focus();
        return;
      }

      // Simulate submission (loading state)
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.querySelector('.cf-btn-text').textContent = 'Sending…';
      }

      setTimeout(() => {
        if (submitBtn) submitBtn.style.display = 'none';
        if (successEl) successEl.style.display = 'flex';
        form.reset();

        // Reset all field states
        Object.keys(fields).forEach(id => applyStateById(id, null));
        if (charCount) charCount.textContent = '0 / 1000';
      }, 1200);
    });
  }

})();
