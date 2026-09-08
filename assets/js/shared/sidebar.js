document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('.coming-soon').forEach((item) => item.setAttribute('aria-label', `${item.textContent.trim()} coming soon`));
});
