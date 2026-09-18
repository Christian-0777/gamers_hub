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

    const catalogUrl = window.accountPreferenceCatalog?.url;
    if (!catalogUrl) {
        return;
    }

    const selectedGames = new Map(
        Object.entries(window.accountPreferenceCatalog.games || {}).map(([id, game]) => [String(id), game])
    );
    const selectedDevelopers = new Map(
        (window.accountPreferenceCatalog.developers || []).map((developer) => [developer, developer])
    );
    const searchControllers = {};

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    }[character]));

    const syncHiddenInputs = () => {
        document.querySelectorAll('[data-account-game-input], [data-account-developer-input]').forEach((input) => input.remove());
        const form = document.querySelector('.settings-form');
        selectedGames.forEach((game) => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'games[]'; input.value = game.id; input.dataset.accountGameInput = game.id;
            form.append(input);
        });
        selectedDevelopers.forEach((developer) => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'developers[]'; input.value = developer; input.dataset.accountDeveloperInput = developer;
            form.append(input);
        });
    };

    const renderSelectedGames = () => {
        const body = document.querySelector('#account-selected-games');
        if (!body) return;
        body.innerHTML = selectedGames.size ? [...selectedGames.values()].map((game) => `
            <tr><td>${escapeHtml(game.name)}</td><td>${escapeHtml(game.developer || 'Unknown')}</td>
            <td><button type="button" data-account-remove-game="${escapeHtml(game.id)}">Remove</button></td></tr>`).join('')
            : '<tr><td class="preference-catalog-empty" colspan="3">No games added yet.</td></tr>';
    };

    const renderSelectedDevelopers = () => {
        const body = document.querySelector('#account-selected-developers');
        if (!body) return;
        body.innerHTML = selectedDevelopers.size ? [...selectedDevelopers.values()].map((developer) => `
            <tr><td>${escapeHtml(developer)}</td><td><button type="button" data-account-remove-developer="${escapeHtml(developer)}">Remove</button></td></tr>`).join('')
            : '<tr><td class="preference-catalog-empty" colspan="2">No developers added yet.</td></tr>';
    };

    const loadCatalogResults = async (type, query, target) => {
        if (!target) return;
        searchControllers[type]?.abort();
        searchControllers[type] = new AbortController();
        target.innerHTML = '<tr><td class="preference-catalog-empty" colspan="3">Searching...</td></tr>';
        const response = await fetch(`${catalogUrl}?type=${encodeURIComponent(type)}&q=${encodeURIComponent(query)}`, {
            headers: { Accept: 'application/json' },
            signal: searchControllers[type].signal,
        });
        const payload = await response.json();
        if (!payload.success) throw new Error(payload.error || 'Search failed');
        if (type === 'games') {
            target.innerHTML = payload.results.length ? payload.results.map((game) => `
                <tr><td>${escapeHtml(game.name)}</td><td>${escapeHtml(game.developer || 'Unknown')}</td>
                <td><button type="button" data-account-add-game="${escapeHtml(game.id)}" ${selectedGames.has(String(game.id)) ? 'disabled' : ''}>${selectedGames.has(String(game.id)) ? 'Added' : 'Add'}</button></td></tr>`).join('')
                : '<tr><td class="preference-catalog-empty" colspan="3">No matching games.</td></tr>';
        } else {
            target.innerHTML = payload.results.length ? payload.results.map((item) => `
                <tr><td>${escapeHtml(item.name)}</td><td><button type="button" data-account-add-developer="${escapeHtml(item.name)}" ${selectedDevelopers.has(item.name) ? 'disabled' : ''}>${selectedDevelopers.has(item.name) ? 'Added' : 'Add'}</button></td></tr>`).join('')
                : '<tr><td class="preference-catalog-empty" colspan="2">No matching developers.</td></tr>';
        }
    };

    const refreshCatalogResults = (type, query, target) => loadCatalogResults(type, query, target).catch((error) => {
        if (error.name === 'AbortError' || !target) return;
        target.innerHTML = '<tr><td class="preference-catalog-empty" colspan="3">Unable to load results.</td></tr>';
    });

    const gameSearch = document.querySelector('#account-game-search');
    const developerSearch = document.querySelector('#account-developer-search');
    gameSearch?.addEventListener('input', () => refreshCatalogResults('games', gameSearch.value.trim(), document.querySelector('#account-game-results')));
    developerSearch?.addEventListener('input', () => refreshCatalogResults('developers', developerSearch.value.trim(), document.querySelector('#account-developer-results')));

    document.addEventListener('click', (event) => {
        const addGame = event.target.closest('[data-account-add-game]');
        const removeGame = event.target.closest('[data-account-remove-game]');
        const addDeveloper = event.target.closest('[data-account-add-developer]');
        const removeDeveloper = event.target.closest('[data-account-remove-developer]');

        if (addGame) {
            const row = addGame.closest('tr');
            selectedGames.set(addGame.dataset.accountAddGame, {
                id: addGame.dataset.accountAddGame,
                name: row.cells[0].textContent,
                developer: row.cells[1].textContent,
            });
            syncHiddenInputs(); renderSelectedGames();
            refreshCatalogResults('games', gameSearch?.value.trim() || '', document.querySelector('#account-game-results'));
        }
        if (removeGame) {
            selectedGames.delete(removeGame.dataset.accountRemoveGame);
            syncHiddenInputs(); renderSelectedGames();
            refreshCatalogResults('games', gameSearch?.value.trim() || '', document.querySelector('#account-game-results'));
        }
        if (addDeveloper) {
            selectedDevelopers.set(addDeveloper.dataset.accountAddDeveloper, addDeveloper.dataset.accountAddDeveloper);
            syncHiddenInputs(); renderSelectedDevelopers();
            refreshCatalogResults('developers', developerSearch?.value.trim() || '', document.querySelector('#account-developer-results'));
        }
        if (removeDeveloper) {
            selectedDevelopers.delete(removeDeveloper.dataset.accountRemoveDeveloper);
            syncHiddenInputs(); renderSelectedDevelopers();
            refreshCatalogResults('developers', developerSearch?.value.trim() || '', document.querySelector('#account-developer-results'));
        }
    });

    renderSelectedGames(); renderSelectedDevelopers(); syncHiddenInputs();
    refreshCatalogResults('games', '', document.querySelector('#account-game-results'));
    refreshCatalogResults('developers', '', document.querySelector('#account-developer-results'));
});
