document.addEventListener('DOMContentLoaded', () => {
    const profileHero = document.querySelector('[data-profile-hero]');
    const copyButtons = document.querySelectorAll('[data-copy-user]');

    const toggleCollapsedHeader = () => {
        if (!profileHero) {
            return;
        }

        profileHero.classList.toggle('is-collapsed', window.scrollY > 180);
    };

    toggleCollapsedHeader();
    window.addEventListener('scroll', toggleCollapsedHeader, { passive: true });

    copyButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.dataset.copyUser || '';
            const originalIcon = button.innerHTML;

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(value);
                } else {
                    const temporaryInput = document.createElement('input');
                    temporaryInput.value = value;
                    document.body.appendChild(temporaryInput);
                    temporaryInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(temporaryInput);
                }

                button.innerHTML = '<i class="fas fa-check"></i>';
            } catch (error) {
                button.innerHTML = '<i class="fas fa-times"></i>';
            }

            window.setTimeout(() => {
                button.innerHTML = originalIcon;
            }, 1200);
        });
    });
});