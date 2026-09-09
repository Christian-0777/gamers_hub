        // Helper function for time ago
        function timeAgo(date) {
            const seconds = Math.floor((new Date() - new Date(date)) / 1000);
            let interval = seconds / 31536000;
            if (interval > 1) return Math.floor(interval) + "y ago";
            interval = seconds / 2592000;
            if (interval > 1) return Math.floor(interval) + "mo ago";
            interval = seconds / 86400;
            if (interval > 1) return Math.floor(interval) + "d ago";
            interval = seconds / 3600;
            if (interval > 1) return Math.floor(interval) + "h ago";
            interval = seconds / 60;
            if (interval > 1) return Math.floor(interval) + "m ago";
            return Math.floor(seconds) + "s ago";
        }

        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied to clipboard!');
            });
        }

        // Sticky header scroll behavior
        const stickyHeader = document.getElementById('stickyHeader');
        const profileHeader = document.getElementById('profileHeader');
        const profileTabs = document.getElementById('profileTabs');

        window.addEventListener('scroll', () => {
            const headerHeight = profileHeader.offsetHeight;
            if (window.scrollY > headerHeight - 100) {
                stickyHeader.classList.add('visible');
            } else {
                stickyHeader.classList.remove('visible');
            }
        });

        // Tab navigation
        document.querySelectorAll('.tab-item').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = tab.dataset.tab;
                
                // Remove active class from all tabs
                document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.content-section').forEach(section => section.classList.remove('active'));
                
                // Add active class to clicked tab
                tab.classList.add('active');
                document.getElementById(tabName + 'Section').classList.add('active');
                
                // Update URL
                const username = document.body.dataset.username;
                window.history.replaceState({}, '', `@${encodeURIComponent(username)}?tab=${tabName}`);
                
                // Scroll to content
                document.getElementById('profileTabs').scrollIntoView({ behavior: 'smooth' });
            });
        });

        // Smooth scroll for mobile tabs
        const tabsContainer = document.querySelector('.tabs-container');
        let isDown = false;
        let startX;
        let scrollLeft;

        tabsContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - tabsContainer.offsetLeft;
            scrollLeft = tabsContainer.scrollLeft;
        });

        tabsContainer.addEventListener('mouseleave', () => {
            isDown = false;
        });

        tabsContainer.addEventListener('mouseup', () => {
            isDown = false;
        });

        tabsContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - tabsContainer.offsetLeft;
            const walk = (x - startX) * 1;
            tabsContainer.scrollLeft = scrollLeft - walk;
        });
