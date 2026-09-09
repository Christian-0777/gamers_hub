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
  const currentPing = document.querySelector('#currentPing');
  const pingUrl = document.querySelector('[data-ping-url]')?.dataset.pingUrl;

  const showToast = (message) => {
    if (!toastElement) return;
    toastElement.querySelector('.toast-body').textContent = message;
    toastElement.style.display = 'block';
    window.setTimeout(() => { toastElement.style.display = ''; }, 2600);
  };

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

  const isMobile = () => window.innerWidth < 992;
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
