document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.querySelector('#sidebar');
  const menuTrigger = document.querySelector('.menu-trigger');
  const toastElement = document.querySelector('#appToast');
  const modal = document.querySelector('#postModal');
  const showToast = (message) => {
    if (!toastElement) return;
    toastElement.querySelector('.toast-body').textContent = message;
    toastElement.style.display = 'block';
    window.setTimeout(() => { toastElement.style.display = ''; }, 2600);
  };
  document.addEventListener('layout:toast', (event) => showToast(event.detail.message));
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
    sidebar.classList.toggle('open');
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
});