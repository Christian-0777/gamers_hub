document.addEventListener('DOMContentLoaded', () => {
  const page = document.querySelector('.notification-main');
  if (!page) return;

  const apiUrl = page.dataset.apiUrl;
  const list = document.querySelector('#notificationList');
  const count = document.querySelector('#notificationCount');
  const filter = document.querySelector('#notificationFilter');
  const markAll = document.querySelector('#notificationMarkAll');
  const state = { notifications: [], unreadOnly: false };
  const icons = { follow: 'person_add', reaction: 'favorite', comment: 'chat', share: 'share', mention: 'alternate_email', system: 'shield' };
  const labels = { follow: 'Friend activity', reaction: 'Reactions', comment: 'Comments', share: 'Shares', mention: 'Mentions', system: 'System' };

  const escapeText = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]);
  const relativeTime = (value) => {
    const date = new Date(`${String(value).replace(' ', 'T')}Z`);
    const seconds = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} min ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} hr ago`;
    if (seconds < 172800) return 'Yesterday';
    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
  };
  const sectionName = (value) => {
    const date = new Date(`${String(value).replace(' ', 'T')}Z`);
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);
    if (date.toDateString() === today.toDateString()) return 'Today';
    if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';
    return date.toLocaleDateString([], { month: 'long', day: 'numeric', year: 'numeric' });
  };

  async function request(params = {}, options = {}) {
    const url = new URL(apiUrl, window.location.origin);
    Object.entries(params).forEach(([key, value]) => url.searchParams.set(key, value));
    const response = await fetch(url, options);
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'Notification request failed.');
    return data;
  }

  function updateControls(unreadCount) {
    count.textContent = unreadCount;
    count.hidden = unreadCount === 0;
    markAll.disabled = unreadCount === 0;
  }

  function render() {
    const visible = state.unreadOnly ? state.notifications.filter((item) => !item.is_read) : state.notifications;
    updateControls(state.notifications.filter((item) => !item.is_read).length);
    if (!visible.length) {
      list.innerHTML = '<div class="notification-empty"><span class="material-symbols-rounded" aria-hidden="true">notifications_off</span><strong>No notifications</strong><div>You\'re all caught up.</div></div>';
      return;
    }
    const groups = visible.reduce((result, item) => { const key = sectionName(item.created_at); (result[key] ||= []).push(item); return result; }, {});
    list.innerHTML = Object.entries(groups).map(([title, items]) => `<section class="notification-section"><div class="notification-section-title">${escapeText(title)}</div>${items.map(renderItem).join('')}</section>`).join('');
    list.querySelectorAll('[data-notification-id]').forEach((item) => item.addEventListener('click', async (event) => {
      if (event.target.closest('button')) return;
      await markRead(Number(item.dataset.notificationId));
    }));
    list.querySelectorAll('[data-mark-read]').forEach((button) => button.addEventListener('click', () => markRead(Number(button.dataset.markRead))));
  }

  function renderItem(item) {
    const actor = item.actor_name ? `<strong>${escapeText(item.actor_name)}</strong> ` : '';
    return `<article class="notification-item${item.is_read ? '' : ' unread'}" data-notification-id="${item.id}"><div class="notification-icon notification-icon-${escapeText(item.type)}"><span class="material-symbols-rounded" aria-hidden="true">${icons[item.type] || 'notifications'}</span></div><div class="notification-content"><div class="notification-line"><p class="notification-text">${actor}${escapeText(item.message)}</p><span class="notification-time">${escapeText(relativeTime(item.created_at))}</span></div>${item.post_id ? `<div class="notification-preview">Open the related post to see the full conversation.</div>` : ''}${!item.is_read ? `<div class="notification-actions-inline"><button class="notification-action secondary" type="button" data-mark-read="${item.id}">Mark as read</button></div>` : ''}</div><button class="notification-menu" type="button" data-mark-read="${item.id}" aria-label="Mark notification as read"><span class="material-symbols-rounded" aria-hidden="true">more_horiz</span></button></article>`;
  }

  async function markRead(id) {
    try { await request({ action: 'mark_read', id }); const item = state.notifications.find((row) => row.id === id); if (item) item.is_read = true; render(); } catch (error) { window.dispatchEvent(new CustomEvent('layout:toast', { detail: { message: error.message } })); }
  }

  markAll.addEventListener('click', async () => {
    try { await request({ action: 'mark_all_read' }, { method: 'POST' }); state.notifications.forEach((item) => { item.is_read = true; }); render(); } catch (error) { window.dispatchEvent(new CustomEvent('layout:toast', { detail: { message: error.message } })); }
  });
  filter.addEventListener('click', () => { state.unreadOnly = !state.unreadOnly; filter.setAttribute('aria-pressed', String(state.unreadOnly)); filter.querySelector('span:last-child').textContent = state.unreadOnly ? 'Unread' : 'All'; render(); });
  request({ action: 'list', limit: 50 }).then((data) => { state.notifications = data.notifications || []; render(); }).catch((error) => { list.innerHTML = `<div class="notification-empty">${escapeText(error.message)}</div>`; });
});
