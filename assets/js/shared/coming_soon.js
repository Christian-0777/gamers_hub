document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-coming-soon-modal]');

    if (!modal) {
        return;
    }

    const message = modal.querySelector('[data-coming-soon-message]');
    let lastTrigger = null;

    const closeModal = () => {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('coming-soon-open');
        lastTrigger?.focus();
    };

    const openModal = (trigger) => {
        lastTrigger = trigger;
        const feature = trigger.dataset.feature || 'This feature';
        message.textContent = `${feature} is being prepared for GamersHUB. We will have it ready soon.`;
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('coming-soon-open');
        modal.querySelector('[data-coming-soon-close]')?.focus();
    };

    document.querySelectorAll('[data-coming-soon]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            openModal(trigger);
        });
    });

    modal.querySelectorAll('[data-coming-soon-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
});
