document.addEventListener('DOMContentLoaded', () => {
  const page = document.querySelector('.friends-main');
  if (!page) return;

  const apiUrl = page.dataset.apiUrl;
  const list = document.querySelector('#friendsList');
  const search = document.querySelector('#friendsSearch');
  const globalSearch = document.querySelector('#globalFriendSearch');
  const title = document.querySelector('#friendsSectionTitle');
  const description = document.querySelector('#friendsSectionDescription');
  const sectionCount = document.querySelector('#friendsSectionCount');
  const state = { friends: [], requests: [], sent: [], gamers: [], tab: 'friends', query: '' };
  const labels = { friends: ['Your Friends', 'Players in your gaming network'], requests: ['Friend Requests', 'Players who want to join your network'], sent: ['Sent Requests', 'Friend requests waiting for a response'], gamers: ['Find Gamers', 'Discover players outside your network'] };

  const escapeText = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]);
  const showToast = (message) => { const toast = document.createElement('div'); toast.className = 'friends-toast'; toast.textContent = message; document.body.appendChild(toast); window.setTimeout(() => toast.remove(), 2800); };
  const request = async (action, userId = null) => {
    const body = new URLSearchParams({ action });
    if (userId) body.set('user_id', userId);
    const response = await fetch(apiUrl, { method: 'POST', body, headers: { Accept: 'application/json' } });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'Friend request failed.');
    return data;
  };

  function renderProfile(item, view = state.tab) {
    const avatar = item.avatar ? `<img src="${escapeText(item.avatar)}" alt="">` : escapeText(item.initials);
    const games = item.games.slice(0, 2).map((game) => escapeText(game)).join(' · ');
    const status = escapeText(item.online_status.replace('_', ' '));
    let actions = '';
    if (view === 'friends') actions = `<button class="friend-action primary" data-action="message" data-user="${item.id}"><span class="material-symbols-rounded" aria-hidden="true">chat</span>Message</button><button class="friend-action danger" data-action="unfriend" data-user="${item.id}">Unfriend</button>`;
    if (view === 'requests') actions = `<button class="friend-action accept" data-action="accept" data-user="${item.id}">Accept</button><button class="friend-action secondary" data-action="decline" data-user="${item.id}">Decline</button>`;
    if (view === 'sent') actions = `<button class="friend-action secondary" data-action="cancel" data-user="${item.id}">Cancel</button>`;
    if (view === 'gamers') actions = `<button class="friend-action primary" data-action="request" data-user="${item.id}"><span class="material-symbols-rounded" aria-hidden="true">person_add</span>Add friend</button>`;
    return `<article class="friend-row"><div class="friend-avatar">${avatar}</div><div class="friend-copy"><h3 class="friend-name">${escapeText(item.name)}</h3><p class="friend-username">@${escapeText(item.username)}</p><div class="friend-meta"><span class="friend-status ${escapeText(item.online_status)}">● ${status}</span>${games ? `<span>${games}</span>` : ''}</div></div><div class="friend-actions">${actions}</div></article>`;
  }

  function render() {
    const items = (state[state.tab] || []).filter((item) => `${item.name} ${item.username} ${item.games.join(' ')}`.toLowerCase().includes(state.query));
    const copy = labels[state.tab];
    title.textContent = copy[0]; description.textContent = copy[1]; sectionCount.textContent = `${items.length} ${state.tab === 'friends' ? 'friends' : 'people'}`;
    document.querySelectorAll('[data-count]').forEach((counter) => { counter.textContent = state[counter.dataset.count].length; });
    const requestPreview = state.tab === 'friends' && state.requests.length ? `<section class="friends-request-preview"><div><h3>Friend Requests</h3><p>Players who want to join your network</p></div><div class="friends-list">${state.requests.slice(0, 2).map((item) => renderProfile(item, 'requests')).join('')}</div></section>` : '';
    list.innerHTML = items.length || requestPreview ? `${requestPreview}${items.length ? `<div class="friends-list">${items.map((item) => renderProfile(item)).join('')}</div>` : ''}` : '<div class="friends-empty"><strong>No gamers here yet</strong><span>Try another view or search for a different player.</span></div>';
    list.querySelectorAll('[data-action]').forEach((button) => button.addEventListener('click', () => handleAction(button.dataset.action, Number(button.dataset.user), button)));
  }

  async function handleAction(action, userId, button) {
    if (action === 'message') {
      const messageUrl = new URL(document.querySelector('.friends-main').dataset.messageUrl, window.location.origin);
      messageUrl.searchParams.set('user_id', userId);
      window.location.href = messageUrl.toString();
      return;
    }
    button.disabled = true;
    try { await request(action, userId); await load(); showToast(action === 'request' ? 'Friend request sent.' : 'Network updated.'); } catch (error) { button.disabled = false; showToast(error.message); }
  }

  async function load() {
    try {
      const response = await fetch(`${apiUrl}?action=list`, { cache: 'no-store' });
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || 'Unable to load friends.');
      Object.keys(state).filter((key) => Array.isArray(state[key])).forEach((key) => { state[key] = data[key] || []; });
      render();
    } catch (error) { list.innerHTML = `<div class="friends-empty">${escapeText(error.message)}</div>`; }
  }

  document.querySelectorAll('.friends-tab').forEach((tab) => tab.addEventListener('click', () => { document.querySelectorAll('.friends-tab').forEach((item) => item.classList.remove('active')); tab.classList.add('active'); state.tab = tab.dataset.tab; state.query = ''; search.value = ''; render(); }));
  const updateSearch = (value) => { state.query = value.toLowerCase().trim(); search.value = value; render(); };
  search.addEventListener('input', () => updateSearch(search.value));
  globalSearch.addEventListener('input', () => updateSearch(globalSearch.value));
  load();
});