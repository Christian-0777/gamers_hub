(() => {
    const modal = document.querySelector('[data-post-modal]');
    const openButton = document.querySelector('[data-post-modal-open]');
    const mediaInput = document.querySelector('[data-post-media]');
    const mediaPreview = document.querySelector('[data-post-media-preview]');

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.hidden = true;
        document.body.classList.remove('post-modal-open');
    };

    if (modal && openButton) {
        openButton.addEventListener('click', () => {
            modal.hidden = false;
            document.body.classList.add('post-modal-open');
            modal.querySelector('textarea')?.focus();
        });
        modal.querySelectorAll('[data-post-modal-close]').forEach((button) => button.addEventListener('click', closeModal));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });
    }

    if (mediaInput && mediaPreview) {
        mediaInput.addEventListener('change', () => {
            mediaPreview.replaceChildren();
            const files = Array.from(mediaInput.files || []);
            if (files.length > 10) {
                mediaInput.value = '';
                window.alert('You can attach up to 10 files.');
                return;
            }

            files.forEach((file) => {
                const preview = document.createElement(file.type.startsWith('video/') ? 'video' : 'img');
                preview.src = URL.createObjectURL(file);
                preview.alt = file.name;
                if (file.type.startsWith('video/')) {
                    preview.muted = true;
                    preview.addEventListener('loadedmetadata', () => {
                        if (preview.duration > 300) {
                            mediaInput.value = '';
                            mediaPreview.replaceChildren();
                            window.alert(`${file.name} is longer than 5 minutes.`);
                        }
                    }, { once: true });
                }
                mediaPreview.appendChild(preview);
            });
        });
    }

    document.querySelectorAll('.feed-like-button').forEach((button) => {
        button.addEventListener('click', () => {
            const post = button.closest('.feed-post');
            const count = post.querySelector('.feed-like-count');
            const icon = button.querySelector('.material-symbols-rounded');
            const liked = button.classList.toggle('is-liked');
            const currentCount = Number.parseInt(count.textContent.replace(/,/g, ''), 10) || 0;

            count.textContent = (liked ? currentCount + 1 : Math.max(0, currentCount - 1)).toLocaleString();
            icon.textContent = liked ? 'favorite' : 'favorite';
        });
    });

    const searchInput = document.querySelector('.dashboard-search input');
    if (!searchInput) {
        return;
    }

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();

        document.querySelectorAll('.feed-post').forEach((post) => {
            post.hidden = query !== '' && !post.dataset.searchText.includes(query);
        });
    });
})();