document.addEventListener('DOMContentLoaded', () => {
	const search = document.querySelector('#site-search');
	const profileTrigger = document.querySelector('.profile-trigger');
	const profileMenu = document.querySelector('#profile-menu');
	document.querySelector('[data-notification-action]')?.addEventListener('click', () => {
		document.dispatchEvent(new CustomEvent('layout:toast', { detail: { message: 'Notifications are coming soon.' } }));
	});
	const closeProfileMenu = () => {
		if (!profileMenu || !profileTrigger) return;
		profileMenu.hidden = true;
		profileTrigger.setAttribute('aria-expanded', 'false');
	};
	profileTrigger?.addEventListener('click', (event) => {
		event.stopPropagation();
		const isOpen = !profileMenu.hidden;
		profileMenu.hidden = isOpen;
		profileTrigger.setAttribute('aria-expanded', String(!isOpen));
	});
	document.querySelectorAll('[data-profile-action]').forEach((option) => option.addEventListener('click', () => {
		document.dispatchEvent(new CustomEvent('layout:toast', { detail: { message: `${option.dataset.profileAction} is coming soon.` } }));
		closeProfileMenu();
	}));
	document.addEventListener('click', closeProfileMenu);
	document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeProfileMenu(); });
	document.addEventListener('keydown', (event) => {
		if (event.key === '/' && document.activeElement !== search) {
			event.preventDefault();
			search?.focus();
		}
	});
});
