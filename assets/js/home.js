(() => {
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