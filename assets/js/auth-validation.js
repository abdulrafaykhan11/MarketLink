/**
 * MarketLink - Live Real-Time Form Validation & Modal System
 * Provides immediate feedback, AJAX availability checking, and password strength analysis
 */

document.addEventListener('DOMContentLoaded', function () {

  // ==========================================================================
  // 1. Terms & Conditions Modal Controller
  // ==========================================================================
  const modalBackdrop = document.getElementById('termsModal');
  const termsTriggerLinks = document.querySelectorAll('.terms-link, #openTermsModal');
  const modalCloseBtns = document.querySelectorAll('[data-close-modal]');
  const acceptTermsBtn = document.getElementById('acceptTermsModalBtn');
  const termsCheckbox = document.getElementById('terms_accepted');
  const termsCard = document.querySelector('.terms-agreement-card');

  function openTermsModal() {
    if (modalBackdrop) {
      modalBackdrop.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeTermsModal() {
    if (modalBackdrop) {
      modalBackdrop.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  termsTriggerLinks.forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      openTermsModal();
    });
  });

  modalCloseBtns.forEach(btn => {
    btn.addEventListener('click', closeTermsModal);
  });

  if (modalBackdrop) {
    modalBackdrop.addEventListener('click', function (e) {
      if (e.target === modalBackdrop) {
        closeTermsModal();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modalBackdrop.classList.contains('active')) {
        closeTermsModal();
      }
    });
  }

  // "Accept & Continue" button inside Modal automatically checks the checkbox
  if (acceptTermsBtn && termsCheckbox) {
    acceptTermsBtn.addEventListener('click', function () {
      termsCheckbox.checked = true;
      if (termsCard) {
        termsCard.classList.remove('has-error');
      }
      closeTermsModal();
      if (window.Toast) {
        Toast.success('Terms Accepted', 'You have accepted MarketLink terms & conditions.');
      }
    });
  }

  if (termsCheckbox) {
    termsCheckbox.addEventListener('change', function () {
      if (termsCard) {
        if (this.checked) {
          termsCard.classList.remove('has-error');
        }
      }
    });
  }

  // ==========================================================================
  // 2. Password Visibility Toggles
  // ==========================================================================
  const togglePasswordBtns = document.querySelectorAll('.password-toggle-btn');
  togglePasswordBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const targetInputId = this.getAttribute('data-target');
      const input = document.getElementById(targetInputId);
      if (!input) return;

      if (input.type === 'password') {
        input.type = 'text';
        this.innerHTML = `
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
          </svg>
        `;
      } else {
        input.type = 'password';
        this.innerHTML = `
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        `;
      }
    });
  });

  // ==========================================================================
  // 3. Role Selector Toggle (Customer vs Farmer)
  // ==========================================================================
  const roleRadios = document.querySelectorAll('input[name="role"]');
  const customerFields = document.getElementById('customer-specific-fields');
  const farmerFields = document.getElementById('farmer-specific-fields');

  roleRadios.forEach(radio => {
    radio.addEventListener('change', function () {
      document.querySelectorAll('.role-option').forEach(el => el.classList.remove('active'));
      const activeOption = this.closest('.role-option');
      if (activeOption) activeOption.classList.add('active');

      if (this.value === 'farmer') {
        if (customerFields) customerFields.style.display = 'none';
        if (farmerFields) farmerFields.style.display = 'block';
      } else {
        if (farmerFields) farmerFields.style.display = 'none';
        if (customerFields) customerFields.style.display = 'block';
      }
    });
  });

  // ==========================================================================
  // 4. Live Registration Form Validations
  // ==========================================================================
  const registerForm = document.getElementById('registrationForm');

  if (registerForm) {
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const fullNameInput = document.getElementById('full_name');
    const stallNameInput = document.getElementById('stall_name');
    const contactPersonInput = document.getElementById('contact_person');
    const phoneInput = document.getElementById('phone_number');
    const businessAddressInput = document.getElementById('business_address');

    let debounceTimer = null;

    // Helper: update field state
    function setFieldState(input, isValid, message = '') {
      if (!input) return;
      const group = input.closest('.form-group');
      if (!group) return;

      const hint = group.querySelector('.validation-hint');
      const icon = group.querySelector('.input-feedback-icon');

      if (isValid === true) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        if (hint) {
          hint.className = 'validation-hint hint-success';
          hint.textContent = message ? `✓ ${message}` : '✓ Looks good';
        }
        if (icon) {
          icon.className = 'input-feedback-icon visible valid';
          icon.innerHTML = '✓';
        }
      } else if (isValid === false) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        if (hint) {
          hint.className = 'validation-hint hint-error';
          hint.textContent = message ? `✕ ${message}` : '✕ Invalid input';
        }
        if (icon) {
          icon.className = 'input-feedback-icon visible invalid';
          icon.innerHTML = '✕';
        }
      } else {
        // Reset / Neutral
        input.classList.remove('is-valid', 'is-invalid');
        if (hint) {
          hint.className = 'validation-hint hint-neutral';
          hint.textContent = message || '';
        }
        if (icon) {
          icon.className = 'input-feedback-icon';
          icon.innerHTML = '';
        }
      }
    }

    // Helper: set field to loading
    function setFieldLoading(input, message = 'Checking...') {
      if (!input) return;
      const group = input.closest('.form-group');
      if (!group) return;

      const hint = group.querySelector('.validation-hint');
      const icon = group.querySelector('.input-feedback-icon');

      input.classList.remove('is-valid', 'is-invalid');
      if (hint) {
        hint.className = 'validation-hint hint-neutral';
        hint.textContent = message;
      }
      if (icon) {
        icon.className = 'input-feedback-icon visible loading';
        icon.innerHTML = '⟳';
      }
    }

    // 4.1 Live Username Validation (Format + AJAX Availability)
    if (usernameInput) {
      usernameInput.addEventListener('input', function () {
        const val = this.value.trim();
        clearTimeout(debounceTimer);

        if (val.length === 0) {
          setFieldState(this, null, 'Choose a unique username (3-30 characters)');
          return;
        }

        if (val.length < 3) {
          setFieldState(this, false, 'Username must be at least 3 characters');
          return;
        }

        if (val.length > 30) {
          setFieldState(this, false, 'Username cannot exceed 30 characters');
          return;
        }

        if (!/^[a-zA-Z0-9_]+$/.test(val)) {
          setFieldState(this, false, 'Only letters, numbers, and underscores allowed');
          return;
        }

        setFieldLoading(this, 'Checking availability...');

        debounceTimer = setTimeout(() => {
          fetch(`/MarketLink/api/check_availability.php?type=username&value=${encodeURIComponent(val)}`)
            .then(res => res.json())
            .then(data => {
              if (data.available) {
                setFieldState(usernameInput, true, 'Username is available');
              } else {
                setFieldState(usernameInput, false, data.message || 'Username already taken');
              }
            })
            .catch(() => {
              setFieldState(usernameInput, null, 'Could not verify username');
            });
        }, 350);
      });
    }

    // 4.1b Username Suggestions Generator
    const suggestionsWrap = document.getElementById('username-suggestions-wrap');
    const suggestionsList = document.getElementById('suggestions-chips-list');
    const btnTriggerSuggestions = document.getElementById('btn-trigger-suggestions');

    function fetchUsernameSuggestions(seedName) {
      if (!seedName || seedName.trim().length < 2) return;
      if (!suggestionsWrap || !suggestionsList) return;

      fetch(`/MarketLink/api/suggest_usernames.php?name=${encodeURIComponent(seedName.trim())}`)
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success' && data.suggestions && data.suggestions.length > 0) {
            suggestionsList.innerHTML = '';
            data.suggestions.forEach(cand => {
              const chip = document.createElement('button');
              chip.type = 'button';
              chip.className = 'suggestion-chip';
              chip.innerHTML = `<span>@${cand}</span> <small style="opacity:0.7;">+</small>`;
              chip.title = `Click to choose @${cand}`;
              chip.addEventListener('click', function () {
                if (usernameInput) {
                  usernameInput.value = cand;
                  usernameInput.dispatchEvent(new Event('input'));
                  if (window.Toast) {
                    Toast.success('Username Picked', `Set username to @${cand}`);
                  }
                }
              });
              suggestionsList.appendChild(chip);
            });
            suggestionsWrap.style.display = 'block';
          }
        })
        .catch(() => {});
    }

    if (btnTriggerSuggestions) {
      btnTriggerSuggestions.addEventListener('click', function () {
        const seed = (fullNameInput && fullNameInput.value.trim()) 
          || (contactPersonInput && contactPersonInput.value.trim()) 
          || (stallNameInput && stallNameInput.value.trim()) 
          || (emailInput && emailInput.value.split('@')[0]) 
          || 'fresh buyer';
        fetchUsernameSuggestions(seed);
      });
    }

    // Auto-suggest usernames when user enters their name
    if (fullNameInput) {
      fullNameInput.addEventListener('blur', function () {
        if (this.value.trim().length >= 2 && usernameInput && !usernameInput.value.trim()) {
          fetchUsernameSuggestions(this.value.trim());
        }
      });
    }

    if (contactPersonInput) {
      contactPersonInput.addEventListener('blur', function () {
        if (this.value.trim().length >= 2 && usernameInput && !usernameInput.value.trim()) {
          fetchUsernameSuggestions(this.value.trim());
        }
      });
    }

    // 4.2 Live Email Validation (RFC Regex + AJAX Availability)
    if (emailInput) {
      let emailTimer = null;
      emailInput.addEventListener('input', function () {
        const val = this.value.trim();
        clearTimeout(emailTimer);

        if (val.length === 0) {
          setFieldState(this, null, 'We will send order & account updates here');
          return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(val)) {
          setFieldState(this, false, 'Please enter a valid email address');
          return;
        }

        setFieldLoading(this, 'Verifying email...');

        emailTimer = setTimeout(() => {
          fetch(`/MarketLink/api/check_availability.php?type=email&value=${encodeURIComponent(val)}`)
            .then(res => res.json())
            .then(data => {
              if (data.available) {
                setFieldState(emailInput, true, 'Email address is valid & available');
              } else {
                setFieldState(emailInput, false, data.message || 'Email already registered');
              }
            })
            .catch(() => {
              setFieldState(emailInput, null, 'Could not verify email');
            });
        }, 350);
      });
    }

    // 4.3 Full Name Validation (For Customers)
    if (fullNameInput) {
      fullNameInput.addEventListener('input', function () {
        const val = this.value.trim();
        if (val.length === 0) {
          setFieldState(this, null);
        } else if (val.length < 2) {
          setFieldState(this, false, 'Name must be at least 2 characters');
        } else {
          setFieldState(this, true, 'Looks good');
        }
      });
    }

    // 4.4 Farmer Stall & Contact Name Validation
    if (stallNameInput) {
      stallNameInput.addEventListener('input', function () {
        const val = this.value.trim();
        if (val.length === 0) {
          setFieldState(this, null);
        } else if (val.length < 2) {
          setFieldState(this, false, 'Stall name must be at least 2 characters');
        } else {
          setFieldState(this, true, 'Stall name accepted');
        }
      });
    }

    if (contactPersonInput) {
      contactPersonInput.addEventListener('input', function () {
        const val = this.value.trim();
        if (val.length === 0) {
          setFieldState(this, null);
        } else if (val.length < 2) {
          setFieldState(this, false, 'Contact name must be at least 2 characters');
        } else {
          setFieldState(this, true, 'Looks good');
        }
      });
    }

    if (businessAddressInput) {
      businessAddressInput.addEventListener('input', function () {
        const val = this.value.trim();
        if (val.length === 0) {
          setFieldState(this, null);
        } else if (val.length < 5) {
          setFieldState(this, false, 'Please enter full farm or stall address');
        } else {
          setFieldState(this, true, 'Address looks valid');
        }
      });
    }

    // 4.5 Pakistani Phone Number Validation (03XX-XXXXXXX or +923XXXXXXXXX)
    if (phoneInput) {
      phoneInput.addEventListener('input', function () {
        const val = this.value.trim();
        if (val.length === 0) {
          setFieldState(this, null, 'Pakistani format: 03001234567 or +923001234567');
          return;
        }

        const clean = val.replace(/[-\s]/g, '');
        // Pakistani mobile regex: 03XX-XXXXXXX or +923XXXXXXXXX
        const pkPhoneRegex = /^((\+92)|(0092)|(92)|(0))?3[0-9]{9}$/;
        if (pkPhoneRegex.test(clean)) {
          setFieldState(this, true, 'Valid Pakistani mobile number');
        } else {
          setFieldState(this, false, 'Enter valid Pakistani number (e.g. 03001234567 or +923001234567)');
        }
      });
    }

    // 4.6 Password Strength Meter & Live Checklist
    if (passwordInput) {
      const bars = [
        document.getElementById('meterBar1'),
        document.getElementById('meterBar2'),
        document.getElementById('meterBar3'),
        document.getElementById('meterBar4')
      ];
      const strengthText = document.getElementById('strengthText');
      const reqLength = document.getElementById('req-length');
      const reqUpper = document.getElementById('req-upper');
      const reqLower = document.getElementById('req-lower');
      const reqNumber = document.getElementById('req-number');
      const reqSpecial = document.getElementById('req-special');

      passwordInput.addEventListener('input', function () {
        const val = this.value;

        // Reset check
        const hasLength = val.length >= 8;
        const hasUpper = /[A-Z]/.test(val);
        const hasLower = /[a-z]/.test(val);
        const hasNumber = /[0-9]/.test(val);
        const hasSpecial = /[\W_]/.test(val);

        // Update checklist UI
        updateReqItem(reqLength, hasLength);
        updateReqItem(reqUpper, hasUpper);
        updateReqItem(reqLower, hasLower);
        updateReqItem(reqNumber, hasNumber);
        updateReqItem(reqSpecial, hasSpecial);

        if (val.length === 0) {
          resetMeter(bars, strengthText);
          setFieldState(this, null);
          return;
        }

        // Calculate score
        let score = 0;
        if (hasLength) score++;
        if (hasUpper && hasLower) score++;
        if (hasNumber) score++;
        if (hasSpecial) score++;

        // Reset all bar classes
        bars.forEach(b => {
          if (b) b.className = 'meter-bar';
        });

        if (score === 1) {
          if (bars[0]) bars[0].classList.add('meter-weak');
          if (strengthText) {
            strengthText.textContent = 'Weak';
            strengthText.style.color = 'var(--error)';
          }
          setFieldState(this, false, 'Password is too weak');
        } else if (score === 2) {
          if (bars[0]) bars[0].classList.add('meter-fair');
          if (bars[1]) bars[1].classList.add('meter-fair');
          if (strengthText) {
            strengthText.textContent = 'Fair';
            strengthText.style.color = 'var(--warning)';
          }
          setFieldState(this, false, 'Add more character varieties');
        } else if (score === 3) {
          if (bars[0]) bars[0].classList.add('meter-good');
          if (bars[1]) bars[1].classList.add('meter-good');
          if (bars[2]) bars[2].classList.add('meter-good');
          if (strengthText) {
            strengthText.textContent = 'Good';
            strengthText.style.color = '#3b82f6';
          }
          setFieldState(this, true, 'Strong password');
        } else if (score >= 4 && hasLength) {
          bars.forEach(b => { if (b) b.classList.add('meter-strong'); });
          if (strengthText) {
            strengthText.textContent = 'Excellent';
            strengthText.style.color = 'var(--success)';
          }
          setFieldState(this, true, 'Super strong password');
        }

        // Trigger confirm password re-check if filled
        if (confirmPasswordInput && confirmPasswordInput.value.length > 0) {
          checkPasswordMatch();
        }
      });
    }

    function updateReqItem(el, passed) {
      if (!el) return;
      if (passed) {
        el.classList.add('passed');
        const icon = el.querySelector('.icon');
        if (icon) icon.textContent = '✓';
      } else {
        el.classList.remove('passed');
        const icon = el.querySelector('.icon');
        if (icon) icon.textContent = '○';
      }
    }

    function resetMeter(bars, strengthText) {
      bars.forEach(b => {
        if (b) b.className = 'meter-bar';
      });
      if (strengthText) {
        strengthText.textContent = 'Too Weak';
        strengthText.style.color = 'var(--slate-400)';
      }
    }

    // 4.7 Live Confirm Password Match Check
    function checkPasswordMatch() {
      if (!confirmPasswordInput || !passwordInput) return;
      const pass = passwordInput.value;
      const confirm = confirmPasswordInput.value;

      if (confirm.length === 0) {
        setFieldState(confirmPasswordInput, null);
        return;
      }

      if (pass === confirm) {
        setFieldState(confirmPasswordInput, true, 'Passwords match perfectly');
      } else {
        setFieldState(confirmPasswordInput, false, 'Passwords do not match');
      }
    }

    if (confirmPasswordInput) {
      confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }

    // 4.8 Registration Form Submission
    registerForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const submitBtn = document.getElementById('registerSubmitBtn');
      let isValid = true;
      let firstErrorField = null;

      // Validate Terms Agreement Checkbox (COMPULSORY)
      if (termsCheckbox && !termsCheckbox.checked) {
        isValid = false;
        if (termsCard) {
          termsCard.classList.add('has-error', 'shake');
          setTimeout(() => termsCard.classList.remove('shake'), 450);
        }
        if (!firstErrorField) firstErrorField = termsCheckbox;
        if (window.Toast) {
          Toast.error('Agreement Required', 'You must agree to MarketLink Terms & Conditions to proceed.');
        }
      }

      // Check for any input with .is-invalid or required fields empty
      const currentRole = document.querySelector('input[name="role"]:checked')?.value || 'customer';

      if (usernameInput && (!usernameInput.classList.contains('is-valid'))) {
        setFieldState(usernameInput, false, 'Please enter a valid, available username');
        isValid = false;
        if (!firstErrorField) firstErrorField = usernameInput;
      }

      if (emailInput && (!emailInput.classList.contains('is-valid'))) {
        setFieldState(emailInput, false, 'Please enter a valid, registered email');
        isValid = false;
        if (!firstErrorField) firstErrorField = emailInput;
      }

      if (currentRole === 'customer') {
        if (fullNameInput && fullNameInput.value.trim().length < 2) {
          setFieldState(fullNameInput, false, 'Full Name is required');
          isValid = false;
          if (!firstErrorField) firstErrorField = fullNameInput;
        }
      } else {
        if (stallNameInput && stallNameInput.value.trim().length < 2) {
          setFieldState(stallNameInput, false, 'Stall name is required');
          isValid = false;
          if (!firstErrorField) firstErrorField = stallNameInput;
        }
        if (contactPersonInput && contactPersonInput.value.trim().length < 2) {
          setFieldState(contactPersonInput, false, 'Contact person name is required');
          isValid = false;
          if (!firstErrorField) firstErrorField = contactPersonInput;
        }
        if (businessAddressInput && businessAddressInput.value.trim().length < 3) {
          setFieldState(businessAddressInput, false, 'Stall/Farm address is required');
          isValid = false;
          if (!firstErrorField) firstErrorField = businessAddressInput;
        }
      }

      if (passwordInput && (!passwordInput.classList.contains('is-valid'))) {
        setFieldState(passwordInput, false, 'Password does not meet required strength criteria');
        isValid = false;
        if (!firstErrorField) firstErrorField = passwordInput;
      }

      if (confirmPasswordInput && confirmPasswordInput.value !== passwordInput.value) {
        setFieldState(confirmPasswordInput, false, 'Passwords do not match');
        isValid = false;
        if (!firstErrorField) firstErrorField = confirmPasswordInput;
      }

      if (phoneInput && phoneInput.value.trim().length > 0) {
        const cleanPhone = phoneInput.value.trim().replace(/[-\s]/g, '');
        const pkPhoneRegex = /^((\+92)|(0092)|(92)|(0))?3[0-9]{9}$/;
        if (!pkPhoneRegex.test(cleanPhone)) {
          setFieldState(phoneInput, false, 'Enter valid Pakistani mobile number (e.g. 03001234567 or +923001234567)');
          isValid = false;
          if (!firstErrorField) firstErrorField = phoneInput;
        }
      }

      if (!isValid) {
        if (firstErrorField && typeof firstErrorField.focus === 'function') {
          firstErrorField.focus();
        }
        return;
      }

      // If valid, submit via AJAX
      if (submitBtn) {
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
      }

      const formData = new FormData(registerForm);

      fetch('/MarketLink/api/register_process.php', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        if (submitBtn) {
          submitBtn.classList.remove('loading');
          submitBtn.disabled = false;
        }

        if (res.status === 'success') {
          if (window.Toast) {
            Toast.success('Account Created!', res.message || 'Welcome to MarketLink.');
          }
          setTimeout(() => {
            window.location.href = res.redirect || '/MarketLink/login.php';
          }, 1200);
        } else {
          if (window.Toast) {
            Toast.error('Registration Error', res.message || 'Please check your information.');
          }
          // Highlight errors returned by server
          if (res.errors) {
            for (const [key, msg] of Object.entries(res.errors)) {
              const input = document.getElementById(key);
              if (input) setFieldState(input, false, msg);
            }
          }
        }
      })
      .catch(err => {
        if (submitBtn) {
          submitBtn.classList.remove('loading');
          submitBtn.disabled = false;
        }
        if (window.Toast) {
          Toast.error('Connection Error', 'Could not reach server. Please try again.');
        }
      });
    });
  }

  // ==========================================================================
  // 5. Live Login Form Validations
  // ==========================================================================
  const loginForm = document.getElementById('loginForm');

  if (loginForm) {
    const loginIdInput = document.getElementById('login_id');
    const loginPassInput = document.getElementById('password');
    const loginSubmitBtn = document.getElementById('loginSubmitBtn');

    function setLoginFieldState(input, isValid, message = '') {
      if (!input) return;
      const group = input.closest('.form-group');
      if (!group) return;

      const hint = group.querySelector('.validation-hint');
      const icon = group.querySelector('.input-feedback-icon');

      if (isValid === true) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        if (hint) {
          hint.className = 'validation-hint hint-success';
          hint.textContent = '✓ ' + message;
        }
        if (icon) {
          icon.className = 'input-feedback-icon visible valid';
          icon.innerHTML = '✓';
        }
      } else if (isValid === false) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        if (hint) {
          hint.className = 'validation-hint hint-error';
          hint.textContent = '✕ ' + message;
        }
        if (icon) {
          icon.className = 'input-feedback-icon visible invalid';
          icon.innerHTML = '✕';
        }
      } else {
        input.classList.remove('is-valid', 'is-invalid');
        if (hint) {
          hint.className = 'validation-hint hint-neutral';
          hint.textContent = message || '';
        }
        if (icon) {
          icon.className = 'input-feedback-icon';
          icon.innerHTML = '';
        }
      }
    }

    if (loginIdInput) {
      loginIdInput.addEventListener('input', function () {
        const val = this.value.trim();
        if (val.length === 0) {
          setLoginFieldState(this, null);
        } else if (val.length < 3) {
          setLoginFieldState(this, false, 'Enter your valid username or email');
        } else {
          setLoginFieldState(this, true, 'Valid format');
        }
      });
    }

    if (loginPassInput) {
      loginPassInput.addEventListener('input', function () {
        const val = this.value;
        if (val.length === 0) {
          setLoginFieldState(this, null);
        } else if (val.length < 6) {
          setLoginFieldState(this, false, 'Password must be at least 6 characters');
        } else {
          setLoginFieldState(this, true, 'Password entered');
        }
      });
    }

    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const valId = loginIdInput ? loginIdInput.value.trim() : '';
      const valPass = loginPassInput ? loginPassInput.value : '';

      let hasError = false;

      if (!valId) {
        setLoginFieldState(loginIdInput, false, 'Username or email is required');
        hasError = true;
      }

      if (!valPass) {
        setLoginFieldState(loginPassInput, false, 'Password is required');
        hasError = true;
      }

      if (hasError) {
        if (window.Toast) {
          Toast.error('Missing Credentials', 'Please enter your username/email and password.');
        }
        return;
      }

      if (loginSubmitBtn) {
        loginSubmitBtn.classList.add('loading');
        loginSubmitBtn.disabled = true;
      }

      const formData = new FormData(loginForm);

      fetch('/MarketLink/api/login_process.php', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        if (loginSubmitBtn) {
          loginSubmitBtn.classList.remove('loading');
          loginSubmitBtn.disabled = false;
        }

        if (res.status === 'success') {
          if (window.Toast) {
            Toast.success('Login Successful', res.message || 'Redirecting to your dashboard...');
          }
          setTimeout(() => {
            window.location.href = res.redirect || '/MarketLink/index.php';
          }, 1000);
        } else {
          if (window.Toast) {
            Toast.error('Login Failed', res.message || 'Invalid username or password.');
          }
          if (loginPassInput) {
            setLoginFieldState(loginPassInput, false, 'Authentication failed');
          }
        }
      })
      .catch(err => {
        if (loginSubmitBtn) {
          loginSubmitBtn.classList.remove('loading');
          loginSubmitBtn.disabled = false;
        }
        if (window.Toast) {
          Toast.error('Connection Error', 'Could not reach server. Please try again.');
        }
      });
    });
  }

});
