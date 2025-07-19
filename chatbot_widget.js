document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('chatbot-toggle');
  const box = document.getElementById('chatbot-box');
  const closeBtn = document.getElementById('chat-close');
  const input = document.getElementById('chat-input');
  const send = document.getElementById('send-btn');
  const messages = document.getElementById('chat-messages');

  if (!toggle || !box || !input || !send || !messages) {
    console.warn("Chatbot elements not found on page.");
    return;
  }

  toggle.addEventListener('click', () => {
    box.classList.toggle('d-none');
    toggle.querySelector('.bi-chat-dots-fill')?.classList.toggle('d-none');
    toggle.querySelector('.bi-x-lg')?.classList.toggle('d-none');
  });

  closeBtn.addEventListener('click', () => {
    box.classList.add('d-none');
    toggle.querySelector('.bi-chat-dots-fill')?.classList.remove('d-none');
    toggle.querySelector('.bi-x-lg')?.classList.add('d-none');
  });

  function appendMessage(msg, type = 'incoming') {
    const li = document.createElement('li');
    li.className = `chat ${type}`;
    li.innerHTML = `
      <span><i class="bi ${type === 'incoming' ? 'bi-robot' : 'bi-person'}"></i></span>
      <p>${msg}</p>
    `;
    messages.appendChild(li);
    messages.scrollTop = messages.scrollHeight;
  }

  async function sendMessage() {
    const question = input.value.trim();
    if (!question) return;

    appendMessage(question, 'outgoing');
    input.value = '';

    try {
      const res = await fetch('/api/chatbot_proxy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prompt: question })
      });
      const data = await res.json();
      appendMessage(data.reply || "🤖 No reply available.", 'incoming');
    } catch (err) {
      appendMessage("⚠️ Unable to connect to the server.", 'incoming');
    }
  }

  send.addEventListener('click', sendMessage);
  input.addEventListener('keypress', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });
});
