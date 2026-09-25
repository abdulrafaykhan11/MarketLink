<?php
/**
 * MarketLink - Reusable AI Chatbot Floating Widget
 * Powered by Google Gemini 3.8 Flash with live database context.
 */
?>
<!-- AI Chatbot Stylesheet -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/ai-chatbot.css">

<!-- Floating Launcher Toggle -->
<button type="button" class="ai-chatbot-toggle" id="aiChatbotToggle" title="Chat with MarketLink Farm AI Assistant" aria-label="Open Farm AI Assistant">
  <span class="toggle-pulse"></span>
  <img src="<?= BASE_URL ?>/assets/images/farm-ai-icon.svg" alt="Farm AI" class="ai-launcher-icon">
  <span class="ai-close-icon">✕</span>
  <span class="ai-launcher-badge">AI</span>
</button>

<!-- AI Chatbot Floating Window -->
<div class="ai-chatbot-window" id="aiChatbotWindow" role="dialog" aria-labelledby="chatbotHeading" aria-modal="false">
  <div class="ai-chatbot-header">
    <div class="ai-header-left">
      <div class="ai-avatar">
        <img src="<?= BASE_URL ?>/assets/images/farm-ai-icon.svg" alt="Farm AI" class="ai-header-icon-img">
      </div>
      <div class="ai-title-wrap">
        <h4 id="chatbotHeading">MarketLink Farm AI</h4>
        <div class="ai-status-badge">
          <span class="ai-status-dot"></span>
          <span>Gemini 3.8 Flash • Live Database</span>
        </div>
      </div>
    </div>
    <div class="ai-header-actions">
      <button type="button" class="ai-header-btn" id="aiChatbotReset" title="Restart Chat" aria-label="Restart Conversation">🔄</button>
      <button type="button" class="ai-header-btn" id="aiChatbotClose" title="Close Assistant" aria-label="Close Assistant">✕</button>
    </div>
  </div>

  <div class="ai-chatbot-messages" id="aiChatbotMessages"></div>

  <div class="ai-chatbot-input-wrap">
    <input type="text" class="ai-chatbot-input" id="aiChatbotInput" placeholder="Ask about harvest, stalls, rates, orders..." autocomplete="off">
    <button type="button" class="ai-chatbot-send" id="aiChatbotSend" aria-label="Send message">➔</button>
  </div>
</div>

<!-- Chatbot Engine Script -->
<script src="<?= BASE_URL ?>/assets/js/ai-chatbot.js"></script>
