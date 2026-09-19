document.addEventListener('DOMContentLoaded', () => {
  const dashboardSidebar = document.querySelector('#dashboardSidebar');
  const dashboardMain = document.querySelector('#dashboardMain');
  const dashboardBackdrop = document.querySelector('#dashboardBackdrop');
  const dashboardToggle = document.querySelector('#dashboardSidebarToggle');
  const sidebar = document.querySelector('#sidebar');
  const menuTrigger = document.querySelector('.menu-trigger');
  const toastElement = document.querySelector('#appToast');
  const modal = document.querySelector('#postModal');
  const accountTrigger = document.querySelector('#dashboardAccountTrigger');
  const accountMenu = document.querySelector('#dashboardAccountMenu');
  const currentDateTime = document.querySelector('#currentDateTime');
  const currentPing = document.querySelector('#currentPing');
  const pingUrl = document.querySelector('[data-ping-url]')?.dataset.pingUrl;
  const notificationTrigger = document.querySelector('#dashboardNotificationTrigger');
  const notificationDropdown = document.querySelector('#dashboardNotificationDropdown');
  const notificationList = document.querySelector('#dashboardNotificationList');
  const notificationBadge = document.querySelector('#dashboardNotificationBadge');
  const notificationCount = document.querySelector('#dashboardNotificationCount');
  const notificationApiUrl = notificationTrigger?.dataset.notificationApiUrl || '';
  const rightSidebar = document.querySelector('#dashboardRightSidebar');
  const rightSidebarToggle = document.querySelector('#dashboardRightSidebarToggle');

  const showToast = (message) => {
    if (!toastElement) return;
    toastElement.querySelector('.toast-body').textContent = message;
    toastElement.style.display = 'block';
    window.setTimeout(() => { toastElement.style.display = ''; }, 2600);
  };

  const pad = (value, length = 2) => String(value).padStart(length, '0');
  const formatDateTime = (date, utc = false) => {
    const month = utc ? date.getUTCMonth() + 1 : date.getMonth() + 1;
    const day = utc ? date.getUTCDate() : date.getDate();
    const year = utc ? date.getUTCFullYear() : date.getFullYear();
    const hours = utc ? date.getUTCHours() : date.getHours();
    const minutes = utc ? date.getUTCMinutes() : date.getMinutes();
    const seconds = utc ? date.getUTCSeconds() : date.getSeconds();
    const milliseconds = utc ? date.getUTCMilliseconds() : date.getMilliseconds();
    return `${pad(month)}/${pad(day)}/${pad(year % 100)} - ${pad(hours)}:${pad(minutes)}:${pad(seconds)}:${pad(milliseconds, 3)}`;
  };
  const formatUtcOffset = (date) => {
    const offsetMinutes = -date.getTimezoneOffset();
    const sign = offsetMinutes >= 0 ? '+' : '-';
    const absoluteMinutes = Math.abs(offsetMinutes);
    return `${sign}${pad(Math.floor(absoluteMinutes / 60))}:${pad(absoluteMinutes % 60)}`;
  };
  const updateDateTime = () => {
    if (!currentDateTime) return;
    const now = new Date();
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'LOCAL';
    currentDateTime.textContent = `${timezone} (${formatDateTime(now)} - ${formatUtcOffset(now)}) | UTC (${formatDateTime(now, true)})`;
  };

  updateDateTime();
  window.setInterval(updateDateTime, 10);

  const updatePing = async () => {
    if (!currentPing || !pingUrl) return;
    const startedAt = performance.now();
    try {
      const response = await fetch(`${pingUrl}?action=ping`, { cache: 'no-store' });
      if (!response.ok) throw new Error('Ping failed');
      await response.json();
      currentPing.textContent = Math.max(0, Math.round(performance.now() - startedAt));
    } catch (error) {
      currentPing.textContent = '--';
    }
  };

  updatePing();
  window.setInterval(updatePing, 15000);

  document.addEventListener('layout:toast', (event) => showToast(event.detail.message));

  const closeAccountMenu = () => {
    if (!accountTrigger || !accountMenu) return;
    accountMenu.hidden = true;
    accountTrigger.setAttribute('aria-expanded', 'false');
  };

  accountTrigger?.addEventListener('click', () => {
    const isOpen = !accountMenu.hidden;
    accountMenu.hidden = isOpen;
    accountTrigger.setAttribute('aria-expanded', String(!isOpen));
  });
  document.addEventListener('click', (event) => {
    if (accountMenu && accountTrigger && !accountMenu.contains(event.target) && !accountTrigger.contains(event.target)) {
      closeAccountMenu();
    }
  });

  const escapeNotificationText = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]);
  const notificationIcon = { follow: 'person_add', reaction: 'favorite', comment: 'chat', share: 'share', mention: 'alternate_email', system: 'shield' };
  const loadHeaderNotifications = async () => {
    if (!notificationApiUrl || !notificationList) return;
    try {
      const response = await fetch(`${notificationApiUrl}?action=list&limit=4`, { cache: 'no-store' });
      const data = await response.json();
      if (!response.ok) throw new Error(data.error || 'Unable to load notifications.');
      const unread = Number(data.unread_count || 0);
      notificationBadge.textContent = unread > 99 ? '99+' : String(unread);
      notificationBadge.hidden = unread === 0;
      notificationCount.textContent = `${unread} unread`;
      notificationList.innerHTML = (data.notifications || []).length ? data.notifications.map((item) => `<a class="dashboard-notification-row${item.is_read ? '' : ' unread'}" href="${escapeNotificationText(document.querySelector('.dashboard-notification-more')?.href || '#')}" data-notification-id="${item.id}"><span class="material-symbols-rounded" aria-hidden="true">${notificationIcon[item.type] || 'notifications'}</span><span class="dashboard-notification-copy">${item.actor_name ? `<strong>${escapeNotificationText(item.actor_name)}</strong> ` : ''}${escapeNotificationText(item.message)}<small>${escapeNotificationText(item.created_at)}</small></span></a>`).join('') : '<div class="dashboard-notification-loading">You are all caught up.</div>';
    } catch (error) {
      notificationList.innerHTML = '<div class="dashboard-notification-loading">Notifications are unavailable right now.</div>';
    }
  };
  const closeNotificationMenu = () => {
    if (!notificationTrigger || !notificationDropdown) return;
    notificationDropdown.hidden = true;
    notificationTrigger.setAttribute('aria-expanded', 'false');
  };
  notificationTrigger?.addEventListener('click', async () => {
    const isOpen = !notificationDropdown.hidden;
    notificationDropdown.hidden = isOpen;
    notificationTrigger.setAttribute('aria-expanded', String(!isOpen));
    if (!isOpen) await loadHeaderNotifications();
  });
  document.addEventListener('click', (event) => {
    if (notificationDropdown && notificationTrigger && !notificationDropdown.contains(event.target) && !notificationTrigger.contains(event.target)) closeNotificationMenu();
  });
  loadHeaderNotifications();
  window.setInterval(loadHeaderNotifications, 30000);

  const isMobile = () => window.innerWidth < 992;
  const closeMobileRightSidebar = () => rightSidebar?.classList.remove('show');
  const applyRightSidebarState = () => {
    if (isMobile()) {
      closeMobileRightSidebar();
      return;
    }

    document.body.classList.add('right-sidebar-collapsed');
    rightSidebarToggle?.setAttribute('aria-expanded', 'false');
    rightSidebarToggle?.setAttribute('aria-label', 'Expand community sidebar');
    rightSidebarToggle?.querySelector('.material-symbols-rounded')?.replaceChildren('right_panel_open');
  };

  applyRightSidebarState();
  rightSidebarToggle?.addEventListener('click', () => {
    if (isMobile()) {
      const isOpen = rightSidebar?.classList.toggle('show') || false;
      rightSidebarToggle.setAttribute('aria-expanded', String(isOpen));
      rightSidebarToggle.setAttribute('aria-label', isOpen ? 'Close community sidebar' : 'Open community sidebar');
      const rightSidebarIcon = rightSidebarToggle.querySelector('.material-symbols-rounded');
      if (rightSidebarIcon) rightSidebarIcon.textContent = isOpen ? 'right_panel_close' : 'right_panel_open';
      return;
    }

    const collapsed = document.body.classList.toggle('right-sidebar-collapsed');
    rightSidebarToggle.setAttribute('aria-expanded', String(!collapsed));
    rightSidebarToggle.setAttribute('aria-label', collapsed ? 'Expand community sidebar' : 'Collapse community sidebar');
    const rightSidebarIcon = rightSidebarToggle.querySelector('.material-symbols-rounded');
    if (rightSidebarIcon) rightSidebarIcon.textContent = collapsed ? 'right_panel_open' : 'right_panel_close';
  });
  window.addEventListener('resize', () => {
    if (isMobile()) {
      closeMobileRightSidebar();
    }
  });
  accountTrigger?.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeAccountMenu();
      accountTrigger.focus();
    }
  });

  const closeModal = () => {
    modal?.classList.remove('show');
    if (modal) modal.style.display = 'none';
    document.body.classList.remove('modal-open');
    document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());
  };

  const openModal = () => {
    modal?.classList.add('show');
    if (modal) modal.style.display = 'block';
    document.body.classList.add('modal-open');
  };

  document.querySelectorAll('[data-bs-toggle="modal"]').forEach((button) => button.addEventListener('click', openModal));
  document.querySelectorAll('[data-bs-dismiss="modal"]').forEach((button) => button.addEventListener('click', closeModal));
  modal?.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });

  menuTrigger?.addEventListener('click', () => {
    const collapsed = document.body.classList.toggle('sidebar-collapsed');
    sidebar?.classList.toggle('open');
    menuTrigger.setAttribute('aria-expanded', String(!collapsed));
  });

  document.querySelectorAll('.coming-soon').forEach((button) => button.addEventListener('click', () => showToast('Coming soon. We are leveling this feature up.')));
  document.querySelectorAll('.post-tab').forEach((tab) => tab.addEventListener('click', () => {
    document.querySelectorAll('.post-tab').forEach((item) => item.classList.remove('active'));
    tab.classList.add('active');
    document.querySelector('#postModalLabel').textContent = `Create ${tab.dataset.postType.toLowerCase()} post`;
  }));
  document.querySelectorAll('.react-button').forEach((button) => button.addEventListener('click', () => {
    button.classList.toggle('reacted');
    button.querySelector('span').textContent = button.classList.contains('reacted') ? '♥' : '♡';
    button.lastChild.textContent = button.classList.contains('reacted') ? ' Liked' : ' Like';
  }));
  document.querySelectorAll('.follow-button').forEach((button) => button.addEventListener('click', () => {
    button.textContent = button.textContent === 'Follow' ? 'Following' : 'Follow';
    button.classList.toggle('following');
  }));
  document.querySelector('.publish-button')?.addEventListener('click', () => {
    const content = document.querySelector('#post-content');
    if (!content.value.trim()) { content.focus(); return; }
    closeModal();
    content.value = '';
    showToast('Your post is ready to share with the community.');
  });

  if (!dashboardSidebar || !dashboardMain || !dashboardBackdrop || !dashboardToggle) return;

  const closeMobileSidebar = () => {
    dashboardSidebar.classList.remove('show');
    dashboardBackdrop.classList.remove('show');
  };
  const applyDashboardState = () => {
    if (isMobile()) {
      dashboardSidebar.classList.remove('collapsed');
      dashboardMain.classList.remove('collapsed');
    } else {
      closeMobileSidebar();
    }
  };

  dashboardToggle.addEventListener('click', () => {
    if (isMobile()) {
      dashboardSidebar.classList.toggle('show');
      dashboardBackdrop.classList.toggle('show');
    } else {
      dashboardSidebar.classList.toggle('collapsed');
      dashboardMain.classList.toggle('collapsed');
    }
  });
  dashboardBackdrop.addEventListener('click', closeMobileSidebar);
  window.addEventListener('resize', applyDashboardState);
  applyDashboardState();

  const chart = document.querySelector('#dashboardRevenueChart');
  if (chart && window.Chart) {
    new Chart(chart, {
      type: 'line',
      data: {
        labels: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
        datasets: [{
          data: [31200, 34800, 33100, 39400, 42750, 48290],
          borderColor: '#6C55D9',
          backgroundColor: 'rgba(108,85,217,.08)',
          borderWidth: 2.5,
          fill: true,
          tension: .35,
          pointRadius: 0,
          pointHoverRadius: 5,
          pointBackgroundColor: '#6C55D9'
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { color: '#7B8193', font: { size: 11 } } },
          y: { grid: { color: '#EAECF2' }, ticks: { color: '#7B8193', font: { size: 11 }, callback: (value) => `$${value / 1000}k` } }
        }
      }
    });
  }
});
