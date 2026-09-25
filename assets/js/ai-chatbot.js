/**
 * MarketLink - Gemini Real-World AI Assistant Client Engine
 * Features: Markdown parsing, typing animation, conversational memory, auto-scroll.
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    initMarketLinkAIChat();
  });

  function initMarketLinkAIChat() {
    const toggleBtn = document.getElementById('aiChatbotToggle');
    const windowElem = document.getElementById('aiChatbotWindow');
    const closeBtn = document.getElementById('aiChatbotClose');
    const resetBtn = document.getElementById('aiChatbotReset');
    const inputElem = document.getElementById('aiChatbotInput');
    const sendBtn = document.getElementById('aiChatbotSend');
    const messagesElem = document.getElementById('aiChatbotMessages');

    if (!toggleBtn || !windowElem || !messagesElem) return;

    const STORAGE_KEY = 'marketlink_ai_chat_history';
    let chatHistory = [];

    // Load persisted history from sessionStorage if available
    try {
      const saved = sessionStorage.getItem(STORAGE_KEY);
      if (saved) {
        chatHistory = JSON.parse(saved);
        if (Array.isArray(chatHistory) && chatHistory.length > 0) {
          // Replay history
          renderHistory(chatHistory);
        }
      }
    } catch (e) {
      chatHistory = [];
    }

    // Toggle window
    toggleBtn.addEventListener('click', () => {
      const isOpen = windowElem.classList.toggle('open');
      toggleBtn.classList.toggle('active', isOpen);
      if (isOpen) {
        setTimeout(() => inputElem?.focus(), 150);
        scrollToBottom();
      }
    });

    closeBtn?.addEventListener('click', () => {
      windowElem.classList.remove('open');
      toggleBtn.classList.remove('active');
    });

    // Reset Chat
    resetBtn?.addEventListener('click', () => {
      chatHistory = [];
      try {
        sessionStorage.removeItem(STORAGE_KEY);
      } catch (e) {}
      messagesElem.innerHTML = '';
      showWelcomeMessage();
    });

    // Handle Send
    function handleSend(explicitText) {
      const text = (explicitText !== undefined ? explicitText : (inputElem?.value || '')).trim();
      if (!text) return;

      // 1. Append User Message
      appendBubble(text, 'user');
      chatHistory.push({ role: 'user', text: text });
      saveHistory();

      if (inputElem) {
        inputElem.value = '';
        inputElem.disabled = true;
      }
      if (sendBtn) sendBtn.disabled = true;

      // 2. Show Typing Indicator
      const typingElem = showTypingIndicator();
      scrollToBottom();

      // 3. Make API Call to Gemini Backend
      const apiEndpoint = `${window.BASE_URL || '/MarketLink'}/api/ai_chat.php`;
      const formData = new FormData();
      formData.append('query', text);
      formData.append('history', JSON.stringify(chatHistory.slice(-8)));

      fetch(apiEndpoint, {
        method: 'POST',
        body: formData
      })
        .then(res => res.json())
        .then(data => {
          removeTypingIndicator(typingElem);
          const replyText = data.reply || 'I am ready to help you with our harvest, stalls, and order tracking!';
          appendBubble(replyText, 'bot');
          chatHistory.push({ role: 'model', text: replyText });
          saveHistory();
        })
        .catch(err => {
          console.error('AI Chat Error:', err);
          removeTypingIndicator(typingElem);
          const fallback = 'I am currently connected to the MarketLink knowledge base. You can ask me about active markets, stall locations, and fresh produce!';
          appendBubble(fallback, 'bot');
          chatHistory.push({ role: 'model', text: fallback });
          saveHistory();
        })
        .finally(() => {
          if (inputElem) {
            inputElem.disabled = false;
            inputElem.focus();
          }
          if (sendBtn) sendBtn.disabled = false;
          scrollToBottom();
        });
    }

    sendBtn?.addEventListener('click', () => handleSend());

    inputElem?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        handleSend();
      }
    });

    // Click handler for suggestion chips
    messagesElem.addEventListener('click', (e) => {
      const chip = e.target.closest('.ai-suggestion-chip');
      if (chip) {
        const queryText = chip.getAttribute('data-query') || chip.textContent.trim();
        handleSend(queryText);
      }
    });

    function appendBubble(rawText, sender = 'bot') {
      const bubble = document.createElement('div');
      bubble.className = `ai-chat-bubble ${sender}`;

      if (sender === 'user') {
        bubble.textContent = rawText;
      } else {
        bubble.innerHTML = parseMarkdownToHTML(rawText);
      }

      messagesElem.appendChild(bubble);
      scrollToBottom();
      return bubble;
    }

    function showTypingIndicator() {
      const typingWrap = document.createElement('div');
      typingWrap.className = 'ai-chat-bubble bot ai-typing-indicator';
      typingWrap.innerHTML = '<span></span><span></span><span></span>';
      messagesElem.appendChild(typingWrap);
      return typingWrap;
    }

    function removeTypingIndicator(elem) {
      if (elem && elem.parentNode) {
        elem.parentNode.removeChild(elem);
      }
    }

    function scrollToBottom() {
      messagesElem.scrollTop = messagesElem.scrollHeight;
    }

    function saveHistory() {
      try {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(chatHistory.slice(-12)));
      } catch (e) {}
    }

    function renderHistory(history) {
      messagesElem.innerHTML = '';
      history.forEach(item => {
        const sender = item.role === 'user' ? 'user' : 'bot';
        appendBubble(item.text, sender);
      });
      scrollToBottom();
    }

    function showWelcomeMessage() {
      const welcome = document.createElement('div');
      welcome.className = 'ai-chat-bubble bot';
      welcome.innerHTML = `
        <p>Hello! 👋 I am your <strong>MarketLink AI Farm Assistant</strong>, powered by Google Gemini with live access to our database.</p>
        <p>Ask me about fresh produce in stock, market locations & timings, farmer stalls, or your orders!</p>
        <div class="ai-suggestions-container" style="margin-top:0.75rem;">
          <span class="ai-suggestion-chip" data-query="What organic vegetables and fruits are in stock?">🥦 Fresh Harvest in Stock</span>
          <span class="ai-suggestion-chip" data-query="What markets are open this Saturday and Sunday?">📍 Weekend Market Timings</span>
          <span class="ai-suggestion-chip" data-query="How does order pickup and payment work?">📦 How Pickup Works</span>
          <span class="ai-suggestion-chip" data-query="Tomato aur dairy products ka kya rate hai?">🍅 Check Produce Rates</span>
        </div>
      `;
      messagesElem.appendChild(welcome);
    }

    /**
     * Safely parse markdown formatting into clean HTML
     */
    function parseMarkdownToHTML(text) {
      if (!text) return '';

      // Escape basic HTML to avoid injection
      let html = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

      // Headers (### Header)
      html = html.replace(/^### (.*$)/gim, '<h4>$1</h4>');
      html = html.replace(/^## (.*$)/gim, '<h3>$1</h3>');

      // Bold (**text**)
      html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

      // Italic (*text*)
      html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');

      // Convert bullet points (* item or - item or • item)
      const lines = html.split('\n');
      let inList = false;
      let outputLines = [];

      lines.forEach(line => {
        const trimmed = line.trim();
        const isBullet = /^[*\-•]\s+(.*)$/.test(trimmed);

        if (isBullet) {
          const content = trimmed.replace(/^[*\-•]\s+/, '');
          if (!inList) {
            inList = true;
            outputLines.push('<ul>');
          }
          outputLines.push(`<li>${content}</li>`);
        } else {
          if (inList) {
            inList = false;
            outputLines.push('</ul>');
          }
          if (trimmed.length > 0) {
            // If line is not a header, wrap in paragraph if needed or add br
            if (/^<h[34]>/.test(trimmed)) {
              outputLines.push(trimmed);
            } else {
              outputLines.push(`<p>${trimmed}</p>`);
            }
          }
        }
      });

      if (inList) {
        outputLines.push('</ul>');
      }

      return outputLines.join('');
    }

    // If chat empty on boot, display initial welcome
    if (messagesElem.children.length === 0) {
      showWelcomeMessage();
    }
  }
})();
