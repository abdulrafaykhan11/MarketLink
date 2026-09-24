    </main><!-- /.customer-content -->
  </div><!-- /.customer-main -->
</div><!-- /.customer-layout -->

<!-- AI Chatbot Assistant Floating Launcher -->
<button type="button" class="ai-chatbot-toggle" id="aiChatbotToggle" title="Chat with MarketLink Farm Assistant" aria-label="Open AI Assistant">
  🤖
</button>

<!-- AI Chatbot Assistant Window -->
<div class="ai-chatbot-window" id="aiChatbotWindow" role="dialog" aria-labelledby="chatbotHeading">
  <div class="chatbot-header">
    <div class="chatbot-header-info">
      <div class="chatbot-avatar">🌱</div>
      <div>
        <h4 class="chatbot-title" id="chatbotHeading">FarmLink AI Assistant</h4>
        <span class="chatbot-status">● Live • Market FAQs & Stock</span>
      </div>
    </div>
    <button type="button" class="chatbot-close-btn" id="aiChatbotClose" aria-label="Close Assistant">✕</button>
  </div>

  <div class="chatbot-messages-body" id="aiChatbotMessages">
    <div class="chat-bubble bot">
      Hello <?= htmlspecialchars($displayName ?? 'there') ?>! 👋 I am your MarketLink AI Assistant. I can help you find fresh products, check market timings, discover pickup points, and understand pre-orders!
    </div>

    <!-- Quick Questions Suggestions -->
    <div class="chat-suggestion-chips">
      <span class="suggestion-chip">What markets are open Saturday?</span>
      <span class="suggestion-chip">How does pickup payment work?</span>
      <span class="suggestion-chip">Can I cancel my pre-order?</span>
      <span class="suggestion-chip">Where can I get fresh organic eggs?</span>
    </div>
  </div>

  <div class="chatbot-input-row">
    <input type="text" class="chatbot-input" id="aiChatbotInput" placeholder="Ask about markets, stalls, or products..." autocomplete="off">
    <button type="button" class="chatbot-send-btn" id="aiChatbotSend" aria-label="Send message">➔</button>
  </div>
</div>

<div id="toast-container"></div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="<?= BASE_URL ?>/assets/js/customer.js"></script>

</body>
</html>
<?php
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>
