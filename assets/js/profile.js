document.addEventListener('DOMContentLoaded', () => {
    const profileHero = document.querySelector('[data-profile-hero]');
    const stickyBar = document.querySelector('[data-profile-sticky]');
    const copyButtons = document.querySelectorAll('[data-copy-user]');

    if (profileHero && stickyBar) {
        const stickyObserver = new IntersectionObserver(
            ([entry]) => {
                const isVisible = entry.isIntersecting;
                stickyBar.classList.toggle('is-visible', !isVisible);
                stickyBar.setAttribute('aria-hidden', String(isVisible));
            },
            {
                rootMargin: '-68px 0px 0px 0px',
                threshold: 0,
            }
        );

        stickyObserver.observe(profileHero);

        stickyBar.querySelector('.profile-sticky-person')?.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

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

                button.innerHTML = '<span class="material-symbols-rounded" aria-hidden="true">check</span>';
            } catch (error) {
                button.innerHTML = '<span class="material-symbols-rounded" aria-hidden="true">close</span>';
            }

            window.setTimeout(() => {
                button.innerHTML = originalIcon;
            }, 1200);
        });
    });
});