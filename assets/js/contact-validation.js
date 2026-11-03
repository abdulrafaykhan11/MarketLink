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
            const detailEl = document.getElementById('contactSuccessDetail');
            if (detailEl) {
              detailEl.innerHTML = `Your inquiry <strong>#${result.ticket_number}</strong> was recorded in our system. A confirmation email has been dispatched to <strong>${payload.email}</strong>.<br><span style="display:block; margin-top:0.35rem; font-size:0.82rem; color:var(--text-secondary);">You can track administrator review status and replies using the <strong>Check Application / Ticket</strong> tab.</span>`;
            }

            const trackBtn = document.getElementById('btnTrackSubmittedTicket');
            if (trackBtn) {
              trackBtn.style.display = 'inline-block';
              trackBtn.onclick = function() {
                window.switchContactTab('track');
                const tInput = document.getElementById('trackTicketInput');
                if (tInput) tInput.value = result.ticket_number;
                const eInput = document.getElementById('trackEmailInput');
                if (eInput) eInput.value = payload.email;
                window.handleTrackInquiry(new Event('submit'));
              };
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

  // -----------------------------------------------------------------------
  // Mode Tab Switcher: Send Message vs Track Inquiry
  // -----------------------------------------------------------------------
  window.switchContactTab = function (tab) {
    const btnSend   = document.getElementById('tabBtnSend');
    const btnTrack  = document.getElementById('tabBtnTrack');
    const panelSend = document.getElementById('panelSendInquiry');
    const panelTrack= document.getElementById('panelTrackInquiry');

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

      const tInput = document.getElementById('trackTicketInput');
      if (tInput && !tInput.value) {
        setTimeout(function() { tInput.focus(); }, 60);
      }
    }
  };

  // -----------------------------------------------------------------------
  // Track Form Live Validation
  // -----------------------------------------------------------------------
  const trackFields = {
    trackTicketInput: {
      wrapperId: 'field-track-ticket',
      feedbackId: 'feedback-track-ticket',
      validate(val) {
        const raw = val.trim().toUpperCase().replace(/^#/, '');
        if (!raw) {
          return { ok: false, msg: 'Please enter your ticket number (e.g. INQ-00012 or 12).' };
        }
        if (/^INQ-\d+$/.test(raw)) {
          return { ok: true, msg: '✓ Looks great! Valid ticket format.' };
        }
        if (/^\d+$/.test(raw)) {
          return { ok: true, msg: '✓ Valid numeric ticket code (will match INQ-' + raw.padStart(5, '0') + ').' };
        }
        return { ok: false, msg: 'Ticket format should be like INQ-00012 or digits (e.g. 15).' };
      }
    },
    trackEmailInput: {
      wrapperId: 'field-track-email',
      feedbackId: 'feedback-track-email',
      validate(val) {
        const raw = val.trim();
        if (!raw) {
          return { ok: true, msg: '✓ Optional: Leave blank or enter email used in your inquiry.' };
        }
        if (!EMAIL_REGEX.test(raw)) {
          return { ok: false, msg: 'Please enter a valid email format (e.g. you@example.com).' };
        }
        return { ok: true, msg: '✓ Email format valid for security match.' };
      }
    }
  };

  function applyTrackState(inputId, res) {
    const def = trackFields[inputId];
    if (!def) return;
    const wrap = document.getElementById(def.wrapperId);
    const feed = document.getElementById(def.feedbackId);
    if (!wrap) return;

    wrap.classList.remove('is-valid', 'is-invalid');
    if (res === null) {
      if (feed) feed.textContent = '';
      return;
    }
    wrap.classList.add(res.ok ? 'is-valid' : 'is-invalid');
    if (feed) feed.textContent = res.msg;
  }

  // Attach live event listeners to track fields
  const trackTicketEl = document.getElementById('trackTicketInput');
  const trackEmailEl  = document.getElementById('trackEmailInput');

  if (trackTicketEl) {
    trackTicketEl.addEventListener('input', function () {
      // Auto uppercase as typed
      const pos = this.selectionStart;
      this.value = this.value.toUpperCase();
      this.setSelectionRange(pos, pos);

      const res = trackFields.trackTicketInput.validate(this.value);
      applyTrackState('trackTicketInput', res);
    });

    trackTicketEl.addEventListener('blur', function () {
      if (!this.value.trim()) return;
      const res = trackFields.trackTicketInput.validate(this.value);
      applyTrackState('trackTicketInput', res);
    });
  }

  if (trackEmailEl) {
    trackEmailEl.addEventListener('input', function () {
      const res = trackFields.trackEmailInput.validate(this.value);
      applyTrackState('trackEmailInput', res);
    });

    trackEmailEl.addEventListener('blur', function () {
      if (!this.value.trim()) return;
      const res = trackFields.trackEmailInput.validate(this.value);
      applyTrackState('trackEmailInput', res);
    });
  }

  // -----------------------------------------------------------------------
  // Track Inquiry Handler
  // -----------------------------------------------------------------------
  window.handleTrackInquiry = async function (e) {
    if (e && e.preventDefault) e.preventDefault();

    const ticketInput = document.getElementById('trackTicketInput');
    const emailInput  = document.getElementById('trackEmailInput');
    const submitBtn   = document.getElementById('trackSubmitBtn');
    const loadingEl   = document.getElementById('trackLoading');
    const errorBox    = document.getElementById('trackErrorBox');
    const resultBox   = document.getElementById('trackResultBox');

    if (!ticketInput) return;

    // Run live validations
    const ticketRes = trackFields.trackTicketInput.validate(ticketInput.value);
    applyTrackState('trackTicketInput', ticketRes);

    let emailRes = { ok: true };
    if (emailInput && emailInput.value.trim()) {
      emailRes = trackFields.trackEmailInput.validate(emailInput.value);
      applyTrackState('trackEmailInput', emailRes);
    }

    if (!ticketRes.ok || !emailRes.ok) {
      if (!ticketRes.ok) ticketInput.focus();
      else if (emailInput) emailInput.focus();
      return;
    }

    const ticket = ticketInput.value.trim();
    const email  = emailInput ? emailInput.value.trim() : '';

    if (errorBox)  errorBox.style.display  = 'none';
    if (resultBox) resultBox.style.display = 'none';
    if (loadingEl) loadingEl.style.display = 'block';
    if (submitBtn) submitBtn.disabled = true;

    try {
      const endpoint = (window.BASE_URL ? window.BASE_URL : '') + '/api/contact_status.php';
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ticket: ticket, email: email })
      });

      const res = await response.json();
      if (loadingEl) loadingEl.style.display = 'none';
      if (submitBtn) submitBtn.disabled = false;

      if (!res.success) {
        if (errorBox) {
          errorBox.textContent = '⚠️ ' + (res.error || 'Inquiry not found.');
          errorBox.style.display = 'block';
        }
        return;
      }

      // Populate Result Card
      const inq = res.data;
      if (resultBox) {
        document.getElementById('resTicketNumber').textContent = '#' + inq.ticket_number;
        document.getElementById('resSubject').textContent = inq.subject;
        document.getElementById('resCreatedAt').textContent = inq.created_at_human;
        document.getElementById('resOriginalMessage').textContent = inq.message;
        document.getElementById('resStatusDescBox').textContent = inq.status_desc;

        // Status Badge
        const badgeEl = document.getElementById('resStatusBadge');
        if (badgeEl) {
          if (inq.status === 'replied') {
            badgeEl.innerHTML = '<span style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.35rem 0.85rem; border-radius:999px; font-size:0.8rem; font-weight:700; background:rgba(34,197,94,0.15); color:#22c55e; border:1.5px solid rgba(34,197,94,0.3);">✓ Replied &amp; Resolved</span>';
          } else if (inq.status === 'read') {
            badgeEl.innerHTML = '<span style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.35rem 0.85rem; border-radius:999px; font-size:0.8rem; font-weight:700; background:rgba(14,165,233,0.15); color:#38bdf8; border:1.5px solid rgba(56,189,248,0.3);">● Seen &amp; Under Review</span>';
          } else {
            badgeEl.innerHTML = '<span style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.35rem 0.85rem; border-radius:999px; font-size:0.8rem; font-weight:700; background:rgba(251,191,36,0.15); color:#fbbf24; border:1.5px solid rgba(251,191,36,0.3);">⏳ Received &amp; In Queue</span>';
          }
        }

        // Stepper updates
        const s1 = document.getElementById('step1');
        const s2 = document.getElementById('step2');
        const s3 = document.getElementById('step3');
        if (s1 && s2 && s3) {
          s1.className = 'track-step completed';
          s2.className = 'track-step' + (inq.status_step >= 2 ? (inq.status_step > 2 ? ' completed' : ' active') : '');
          s3.className = 'track-step' + (inq.status_step === 3 ? ' completed' : '');
        }

        // Admin Reply Box
        const replyCard = document.getElementById('resAdminReplyCard');
        if (replyCard) {
          if (inq.admin_reply) {
            replyCard.style.display = 'block';
            document.getElementById('resAdminReplyText').textContent = inq.admin_reply;
            document.getElementById('resRepliedAt').textContent = inq.replied_at_human ? inq.replied_at_human : '';
            document.getElementById('resRepliedBy').textContent = inq.replied_by || 'MarketLink Admin Team';
          } else {
            replyCard.style.display = 'none';
          }
        }

        resultBox.style.display = 'block';
        resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }

    } catch (err) {
      if (loadingEl) loadingEl.style.display = 'none';
      if (submitBtn) submitBtn.disabled = false;
      if (errorBox) {
        errorBox.textContent = '⚠️ Network error while fetching status. Please try again.';
        errorBox.style.display = 'block';
      }
    }
  };

})();
