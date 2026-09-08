document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('.site-footer a[href="#"]').forEach((link) => link.addEventListener('click', (event) => event.preventDefault()));
});
