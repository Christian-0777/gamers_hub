document.addEventListener('DOMContentLoaded', () => {
    const tabs = [...document.querySelectorAll('[data-settings-tab]')];
    const panels = [...document.querySelectorAll('[data-settings-panel]')];

    const syncVisibility = (activeTab) => {
        tabs.forEach((tab) => {
            const isActive = tab.dataset.settingsTab === activeTab;
            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach((panel) => {
            panel.classList.toggle('active', panel.dataset.settingsPanel === activeTab);
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', (event) => {
            const nextTab = tab.dataset.settingsTab;
            syncVisibility(nextTab);
            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('tab', nextTab);
            window.history.replaceState({}, '', nextUrl);
            event.preventDefault();
            window.location.href = tab.getAttribute('href');
        });
    });

    const avatarPreview = document.querySelector('[data-avatar-preview]');
    const avatarUpload = document.querySelector('[data-avatar-upload]');

    if (avatarUpload && avatarPreview) {
        avatarUpload.addEventListener('change', (event) => {
            const file = event.target.files && event.target.files[0];
            if (!file) {
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            avatarPreview.src = objectUrl;
            avatarPreview.style.display = 'block';
        });
    }

    const coverPreview = document.querySelector('[data-cover-preview]');
    const coverUpload = document.querySelector('[data-cover-upload]');

    if (coverUpload && coverPreview) {
        coverUpload.addEventListener('change', (event) => {
            const file = event.target.files && event.target.files[0];
            if (!file) {
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            coverPreview.src = objectUrl;
            coverPreview.style.display = 'block';
        });
    }

    const activeTab = new URLSearchParams(window.location.search).get('tab');
    if (activeTab && tabs.some((tab) => tab.dataset.settingsTab === activeTab)) {
        syncVisibility(activeTab);
    }
});
