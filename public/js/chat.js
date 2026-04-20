// public/js/chat.js

(function () {
  'use strict';

  const container = document.getElementById('messagesContainer');
  const input     = document.getElementById('messageInput');
  const sendBtn   = document.getElementById('sendBtn');
  const typing    = document.getElementById('typingIndicator');
  const moodEl    = document.getElementById('moodIndicator');
  const subtitle  = document.getElementById('chatSubtitle');

  let isSending = false;

  // ─── Auto-resize textarea ──────────────────────────────────────────────
  input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 140) + 'px';
  });

  // ─── Enter to send (Shift+Enter = newline) ─────────────────────────────
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  sendBtn.addEventListener('click', sendMessage);

  // ─── Send ──────────────────────────────────────────────────────────────
  async function sendMessage() {
    const text = input.value.trim();
    if (!text || isSending) return;

    isSending = true;
    sendBtn.disabled = true;

    // Remove empty state placeholder
    const emptyState = container.querySelector('.empty-state');
    if (emptyState) emptyState.remove();

    appendMessage('user', text, formatTime(new Date()));
    input.value = '';
    input.style.height = 'auto';
    showTyping();

    try {
      const res = await fetch(window.CHAT_CONFIG.sendUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': window.CHAT_CONFIG.csrfToken,
        },
        body: JSON.stringify({ message: text }),
      });

      const data = await res.json();
      hideTyping();

      if (res.ok && data.reply) {
        // sender_type 'ai' → CSS class 'message--ai'
        appendMessage('ai', data.reply, data.timestamp || formatTime(new Date()));
        updateMood(data.sentiment);
        updateSubtitle();
      } else {
        appendMessage('ai', "I'm having a moment — could you try again?", formatTime(new Date()));
      }

    } catch {
      hideTyping();
      appendMessage('ai', "Something went wrong. Please try again.", formatTime(new Date()));
    } finally {
      isSending = false;
      sendBtn.disabled = false;
      input.focus();
    }
  }

  // ─── DOM helpers ──────────────────────────────────────────────────────
  function appendMessage(senderType, content, time) {
    const div = document.createElement('div');
    // senderType is 'user' or 'ai' — matches CSS .message--user / .message--ai
    div.className = `message message--${senderType}`;
    div.innerHTML = `
      <div class="message-bubble">${escapeHtml(content)}</div>
      <div class="message-time">${time}</div>
    `;
    container.insertBefore(div, typing);
    scrollToBottom();
  }

  function showTyping() {
    typing.style.display = 'flex';
    scrollToBottom();
  }

  function hideTyping() {
    typing.style.display = 'none';
  }

  function scrollToBottom() {
    requestAnimationFrame(() => {
      container.scrollTop = container.scrollHeight;
    });
  }

  function updateMood(sentiment) {
    if (!sentiment) return;
    moodEl.className = `mood-indicator mood--${sentiment}`;
  }

  function updateSubtitle() {
    if (!subtitle) return;
    const count = container.querySelectorAll('.message--user').length;
    subtitle.textContent = `${count} message${count !== 1 ? 's' : ''} today`;
  }

  function formatTime(d) {
    let h = d.getHours(), m = d.getMinutes();
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${String(m).padStart(2, '0')} ${ampm}`;
  }

  function escapeHtml(str) {
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/\n/g, '<br>');
  }

  // ─── Initial scroll ───────────────────────────────────────────────────
  scrollToBottom();
  input.focus();

})();
