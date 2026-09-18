document.addEventListener('DOMContentLoaded', () => {
  const messenger = document.querySelector('.messenger');
  if (!messenger) return;

  const apiUrl = messenger.dataset.apiUrl;
  const currentUser = Number(messenger.dataset.currentUser);
  const defaultAvatar = messenger.dataset.defaultAvatar;
  const list = document.querySelector('#conversationList');
  const search = document.querySelector('#conversationSearch');
  const chatArea = document.querySelector('#chatArea');
  const chatEmpty = document.querySelector('#chatEmpty');
  const chatContent = document.querySelector('#chatContent');
  const messages = document.querySelector('#messageContainer');
  const input = document.querySelector('#messageInput');
  const send = document.querySelector('#sendButton');
  const form = document.querySelector('#messageForm');
  const state = { conversations: [], activeId: 0, latestId: 0 };

  const escapeText = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]);
  const initials = (name) => escapeText(name).split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase() || 'G';
  const formatTime = (date) => new Date(`${date.replace(' ', 'T')}Z`).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
  const formatDate = (date) => new Date(`${date.replace(' ', 'T')}Z`).toLocaleDateString([], { month: 'long', day: 'numeric', year: 'numeric' });

  function avatarMarkup(conversation, className = 'conversation-avatar') {
    return `<img class="${className}" src="${escapeText(conversation.avatar || defaultAvatar)}" alt="">`;
  }

  function renderConversations() {
    const query = search.value.trim().toLowerCase();
    const visible = state.conversations.filter((conversation) => conversation.name.toLowerCase().includes(query));
    list.innerHTML = visible.length ? visible.map((conversation) => `
      <button class="conversation-item${conversation.id === state.activeId ? ' active' : ''}${conversation.unread ? ' unread' : ''}" data-conversation-id="${conversation.id}" type="button">
        <div class="conversation-avatar-wrap">${avatarMarkup(conversation)}<span class="online-dot${conversation.online ? ' online' : ''}"></span></div>
        <div class="conversation-copy"><div class="conversation-top"><p class="conversation-name">${escapeText(conversation.name)}</p><span class="conversation-time">${formatTime(conversation.updated_at)}</span></div><p class="conversation-preview">${escapeText(conversation.preview)}</p></div>
        ${conversation.unread ? `<span class="conversation-unread">${conversation.unread}</span>` : ''}
      </button>`).join('') : '<div class="message-loading">No conversations found.</div>';
    list.querySelectorAll('[data-conversation-id]').forEach((button) => button.addEventListener('click', () => selectConversation(Number(button.dataset.conversationId))));
  }

  async function request(params = {}, options = {}) {
    const url = new URL(apiUrl, window.location.origin);
    Object.entries(params).forEach(([key, value]) => url.searchParams.set(key, value));
    const response = await fetch(url, options);
    const responseText = await response.text();
    let data;
    try {
      data = JSON.parse(responseText);
    } catch (error) {
      throw new Error('The messaging service returned an invalid response. Check the PHP error log.');
    }
    if (!response.ok) throw new Error(data.error || 'Request failed');
    return data;
  }

  function renderMessages(rows, replace = true) {
    if (replace) messages.innerHTML = '';
    if (replace && rows.length) {
      const separator = document.createElement('div');
      separator.className = 'date-separator';
      separator.textContent = formatDate(rows[0].created_at);
      messages.appendChild(separator);
    }
    rows.forEach((row) => {
      state.latestId = Math.max(state.latestId, row.id);
      const rowElement = document.createElement('div');
      const outgoing = row.sender_id === currentUser;
      rowElement.className = `message-row ${outgoing ? 'outgoing' : 'incoming'}`;
      const bubble = document.createElement('div');
      bubble.className = 'message-bubble';
      bubble.textContent = row.body;
      rowElement.appendChild(bubble);
      messages.appendChild(rowElement);
      const time = document.createElement('div');
      time.className = `message-time${outgoing ? ' outgoing' : ''}`;
      time.textContent = `${outgoing ? 'You' : row.sender_name} · ${formatTime(row.created_at)}`;
      messages.appendChild(time);
    });
    messages.scrollTop = messages.scrollHeight;
  }

  async function loadMessages(replace = true) {
    if (!state.activeId) return;
    const data = await request({ action: 'messages', conversation_id: state.activeId, after_id: replace ? 0 : state.latestId });
    renderMessages(data.messages || [], replace);
  }

  async function selectConversation(id) {
    const conversation = state.conversations.find((item) => item.id === id);
    if (!conversation) return;
    state.activeId = id;
    state.latestId = 0;
    document.querySelector('#chatUserName').textContent = conversation.name;
    document.querySelector('#chatUserStatus').textContent = conversation.status;
    document.querySelector('#chatOnlineDot').classList.toggle('online', conversation.online);
    const avatar = document.querySelector('#chatAvatar');
    avatar.src = conversation.avatar || defaultAvatar;
    avatar.alt = conversation.name;
    chatEmpty.hidden = true;
    chatContent.hidden = false;
    chatArea.classList.add('mobile-visible');
    renderConversations();
    try { await loadMessages(true); } catch (error) { messages.innerHTML = `<div class="message-loading">${escapeText(error.message)}</div>`; }
  }

  async function loadConversations() {
    const data = await request({ action: 'conversations' });
    state.conversations = data.conversations || [];
    renderConversations();
    const requestedUserId = Number(new URLSearchParams(window.location.search).get('user_id'));
    if (requestedUserId && !state.activeId) {
      const opened = await request({ action: 'open', target_user_id: requestedUserId });
      history.replaceState({}, document.title, window.location.pathname);
      const refreshed = await request({ action: 'conversations' });
      state.conversations = refreshed.conversations || [];
      if (!state.conversations.some((conversation) => conversation.id === opened.conversation_id)) {
        state.conversations.unshift(opened.conversation);
      }
      renderConversations();
      await selectConversation(opened.conversation_id);
      return;
    }
    if (state.conversations.length && !state.activeId) await selectConversation(state.conversations[0].id);
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const body = input.value.trim();
    if (!body || !state.activeId) return;
    send.disabled = true;
    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('conversation_id', state.activeId);
    formData.append('body', body);
    try { await request({}, { method: 'POST', body: formData }); input.value = ''; input.style.height = ''; await loadMessages(true); await loadConversations(); } catch (error) { window.dispatchEvent(new CustomEvent('layout:toast', { detail: { message: error.message } })); } finally { send.disabled = input.value.trim() === ''; }
  });
  input.addEventListener('input', () => { input.style.height = 'auto'; input.style.height = `${Math.min(input.scrollHeight, 120)}px`; send.disabled = input.value.trim() === ''; });
  input.addEventListener('keydown', (event) => { if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); form.requestSubmit(); } });
  search.addEventListener('input', renderConversations);
  document.querySelector('#emojiButton').addEventListener('click', () => { input.value += '🙂'; input.dispatchEvent(new Event('input')); input.focus(); });
  document.querySelector('#mobileBack').addEventListener('click', () => chatArea.classList.remove('mobile-visible'));
  loadConversations().catch((error) => { list.innerHTML = `<div class="message-loading">${escapeText(error.message)}</div>`; });
  window.setInterval(async () => { try { await loadMessages(false); await loadConversations(); } catch (error) {} }, 4000);
});
