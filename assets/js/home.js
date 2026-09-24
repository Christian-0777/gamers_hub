(() => {
    const modal = document.querySelector('[data-post-modal]');
    const openButton = document.querySelector('[data-post-modal-open]');
    const mediaInput = document.querySelector('[data-post-media]');
    const mediaPreview = document.querySelector('[data-post-media-preview]');
    const topicSelect = document.querySelector('.post-form select[name="game_id"]');
    const postTypeSelect = document.querySelector('.post-form select[name="post_type"]');

    const syncTopicPostTypes = () => {
        if (!topicSelect || !postTypeSelect) {
            return;
        }

        const restrictedTopic = /^(developer|publisher):/.test(topicSelect.value);
        const compatibleTypes = ['text', 'discussion', 'review', 'question'];
        const selectedOption = postTypeSelect.options[postTypeSelect.selectedIndex];

        Array.from(postTypeSelect.options).forEach((option) => {
            option.hidden = restrictedTopic && !compatibleTypes.includes(option.value);
        });

        if (restrictedTopic && selectedOption && !compatibleTypes.includes(selectedOption.value)) {
            postTypeSelect.value = 'text';
        }
    };

    topicSelect?.addEventListener('change', syncTopicPostTypes);
    syncTopicPostTypes();

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

    const closeDetailsModal = (detailsModal) => {
        detailsModal.hidden = true;
        document.body.classList.remove('post-details-modal-open');
    };

    document.querySelectorAll('[data-post-details-open]').forEach((header) => {
        const openDetailsModal = () => {
            const detailsModal = header.closest('.feed-post')?.querySelector('[data-post-details-modal]');
            if (!detailsModal) {
                return;
            }

            detailsModal.hidden = false;
            document.body.classList.add('post-details-modal-open');
            detailsModal.querySelector('[data-post-details-close]')?.focus();
            refreshPostComments(header.closest('.feed-post')).catch(() => {});
        };

        header.addEventListener('click', (event) => {
            if (event.target.closest('[data-post-more]')) {
                return;
            }
            openDetailsModal();
        });

        header.addEventListener('keydown', (event) => {
            if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('[data-post-more]')) {
                event.preventDefault();
                openDetailsModal();
            }
        });
    });

    document.querySelectorAll('[data-post-details-modal]').forEach((detailsModal) => {
        const updateModalCount = (selector, value) => {
            const count = detailsModal.querySelector(selector);
            if (count) {
                count.textContent = Number(value || 0).toLocaleString();
            }
        };

        const submitInteraction = async (interaction, content = '') => {
            const socketAction = interaction === 'comment'
                ? 'create_comment'
                : interaction === 'react' ? 'react_post' : 'share_post';
            if (window.gamersHubRequest && window.gamersHubSocket?.readyState === WebSocket.OPEN) {
                return window.gamersHubRequest(socketAction, {
                    post_id: Number(detailsModal.dataset.postId || 0),
                    ...(content !== '' ? { content } : {}),
                });
            }
            const formData = new FormData();
            formData.set('action', 'post_interaction');
            formData.set('interaction', interaction);
            formData.set('post_id', detailsModal.dataset.postId || '0');
            formData.set('csrf_token', detailsModal.dataset.csrfToken || '');
            if (content !== '') {
                formData.set('content', content);
            }

            const endpoint = interaction === 'comment' ? detailsModal.dataset.commentsApi : window.location.href;
            if (interaction === 'comment') {
                formData.set('action', 'create');
            }
            const response = await fetch(endpoint, { method: 'POST', body: formData });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Unable to update this post.');
            }
            return result;
        };

        detailsModal.querySelector('[data-post-interaction="react"]')?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            button.disabled = true;
            try {
                const result = await submitInteraction('react');
                const post = detailsModal.closest('.feed-post');
                button.classList.toggle('is-liked', result.liked);
                button.setAttribute('aria-pressed', result.liked ? 'true' : 'false');
                updateModalCount('[data-modal-like-count]', result.like_count);
                post?.querySelector('.feed-like-count')?.replaceChildren(document.createTextNode(Number(result.like_count || 0).toLocaleString()));
                post?.querySelector('.feed-like-button')?.classList.toggle('is-liked', result.liked);
            } catch (error) {
                window.alert(error.message);
            } finally {
                button.disabled = false;
            }
        });

        detailsModal.querySelector('[data-post-interaction="share"]')?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            button.disabled = true;
            try {
                const result = await submitInteraction('share');
                const post = detailsModal.closest('.feed-post');
                button.classList.add('is-shared');
                updateModalCount('[data-modal-share-count]', result.share_count);
                post?.querySelector('.feed-share-count')?.replaceChildren(document.createTextNode(Number(result.share_count || 0).toLocaleString()));
                post?.querySelector('[data-feed-share]')?.classList.add('is-shared');
            } catch (error) {
                window.alert(error.message);
            } finally {
                button.disabled = false;
            }
        });

        detailsModal.querySelector('[data-focus-comment]')?.addEventListener('click', () => {
            detailsModal.querySelector('[data-comment-form] input')?.focus();
        });

        detailsModal.querySelector('[data-comment-form]')?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = event.currentTarget;
            const input = form.querySelector('input[name="content"]');
            const submitButton = form.querySelector('button');
            const content = input.value.trim();
            if (!content) {
                return;
            }

            submitButton.disabled = true;
            try {
                const result = await submitInteraction('comment', content);
                const comment = result.comment;
                let commentList = detailsModal.querySelector('.post-comments-list');
                if (!commentList) {
                    commentList = document.createElement('div');
                    commentList.className = 'post-comments-list';
                    detailsModal.querySelector('.post-details-comments')?.appendChild(commentList);
                }
                if (comment) {
                    appendCommentElement(commentList, comment, detailsModal);
                }

                detailsModal.querySelector('.post-comments-empty')?.remove();
                updateModalCount('[data-modal-comment-count]', result.comment_count);
                updateModalCount('[data-modal-comment-heading]', result.comment_count);
                const post = detailsModal.closest('.feed-post');
                post?.querySelector('.feed-comment-count')?.replaceChildren(document.createTextNode(Number(result.comment_count || 0).toLocaleString()));
                post?.querySelector('.feed-inline-comments-heading span')?.replaceChildren(document.createTextNode(Number(result.comment_count || 0).toLocaleString()));
                input.value = '';
            } catch (error) {
                window.alert(error.message);
            } finally {
                submitButton.disabled = false;
            }
        });

        detailsModal.querySelectorAll('[data-post-details-close]').forEach((button) => {
            button.addEventListener('click', () => closeDetailsModal(detailsModal));
        });

        detailsModal.querySelector('[data-comments-more]')?.addEventListener('click', (event) => {
            detailsModal.querySelectorAll('.is-extra-comment').forEach((comment) => comment.classList.add('is-visible'));
            event.currentTarget.remove();
        });

        detailsModal.querySelectorAll('[data-comment-expand]').forEach((button) => {
            button.addEventListener('click', () => {
                const commentText = button.previousElementSibling;
                commentText?.classList.remove('is-long-comment');
                button.remove();
            });
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        document.querySelectorAll('[data-post-details-modal]:not([hidden])').forEach(closeDetailsModal);
    });

    document.querySelectorAll('[data-feed-comments-toggle]').forEach((button) => {
        const post = button.closest('.feed-post');
        const commentsPanel = post?.querySelector('[data-feed-comments]');
        const detailsModal = post?.querySelector('[data-post-details-modal]');
        const form = commentsPanel?.querySelector('[data-inline-comment-form]');

        button.addEventListener('click', () => {
            if (!commentsPanel) {
                return;
            }

            const expanded = commentsPanel.hidden;
            commentsPanel.hidden = !expanded;
            post.classList.toggle('is-comments-expanded', expanded);
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            if (expanded) {
                form?.querySelector('input')?.focus();
                refreshPostComments(post).catch(() => {});
            }
        });

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const input = form.querySelector('input[name="content"]');
            const submitButton = form.querySelector('button');
            const content = input.value.trim();
            if (!content || !detailsModal) {
                return;
            }

            const formData = new FormData();
            formData.set('action', 'create');
            formData.set('post_id', detailsModal.dataset.postId || '0');
            formData.set('csrf_token', detailsModal.dataset.csrfToken || '');
            formData.set('content', content);
            submitButton.disabled = true;

            try {
                const result = window.gamersHubSocket?.readyState === WebSocket.OPEN
                    ? await window.gamersHubRequest('create_comment', { post_id: Number(detailsModal.dataset.postId || 0), content })
                    : await (async () => {
                        const response = await fetch(detailsModal.dataset.commentsApi, { method: 'POST', body: formData });
                        const responseData = await response.json();
                        if (!response.ok || !responseData.success) throw new Error(responseData.message || 'Unable to add your comment.');
                        return responseData;
                    })();

                const comment = result.comment;
                if (comment) {
                    appendCommentElement(commentsPanel.querySelector('[data-inline-comments-list]'), comment, detailsModal);
                }

                commentsPanel.querySelector('[data-inline-comments-empty]')?.remove();
                const count = Number(result.comment_count || 0).toLocaleString();
                post.querySelector('.feed-comment-count').textContent = count;
                commentsPanel.querySelector('.feed-inline-comments-heading span').textContent = count;
                input.value = '';
            } catch (error) {
                window.alert(error.message);
            } finally {
                submitButton.disabled = false;
            }
        });
    });

    const getCommentContext = (element) => {
        const post = element.closest('.feed-post');
        return {
            post,
            detailsModal: post?.querySelector('[data-post-details-modal]'),
            commentsList: element.closest('[data-inline-comments-list], .post-comments-list'),
        };
    };

    const sendCommentRequest = async (detailsModal, action, values) => {
        const socketAction = action === 'create' ? 'create_comment' : action === 'reply' ? 'reply_comment' : 'react_comment';
        if (window.gamersHubRequest && window.gamersHubSocket?.readyState === WebSocket.OPEN) {
            return window.gamersHubRequest(socketAction, {
                post_id: Number(detailsModal.dataset.postId || 0),
                ...Object.fromEntries(Object.entries(values).map(([key, value]) => [key, key === 'comment_id' || key === 'parent_id' ? Number(value) : value])),
            });
        }
        const formData = new FormData();
        formData.set('action', action);
        formData.set('post_id', detailsModal.dataset.postId || '0');
        formData.set('csrf_token', detailsModal.dataset.csrfToken || '');
        Object.entries(values).forEach(([key, value]) => formData.set(key, value));

        const response = await fetch(detailsModal.dataset.commentsApi, { method: 'POST', body: formData });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to update this comment.');
        }
        return result;
    };

    const appendCommentElement = (commentsList, comment, detailsModal) => {
        if (!commentsList || !detailsModal || !comment) {
            return;
        }
        const commentElement = document.createElement('article');
        commentElement.className = `post-comment${comment.parent_id ? ' is-comment-reply' : ''}`;
        commentElement.dataset.commentId = comment.id;

        const avatar = document.createElement('img');
        avatar.className = 'feed-avatar';
        avatar.src = comment.avatar_url || detailsModal.dataset.defaultAvatar || '';
        avatar.alt = '';

        const body = document.createElement('div');
        if (comment.parent_id && (comment.reply_to_display_name || comment.reply_to_username)) {
            const replyContext = document.createElement('span');
            replyContext.className = 'post-comment-reply-context';
            replyContext.textContent = `${comment.display_name || comment.username} replied to ${comment.reply_to_display_name || comment.reply_to_username}`;
            body.appendChild(replyContext);
        }
        const author = document.createElement('strong');
        author.textContent = comment.display_name || comment.username;
        const meta = document.createElement('span');
        meta.textContent = `@${comment.username} · Just now`;
        const text = document.createElement('p');
        text.textContent = comment.content;

        const actions = document.createElement('div');
        actions.className = 'post-comment-actions';
        const reactButton = document.createElement('button');
        reactButton.type = 'button';
        reactButton.dataset.commentReact = '';
        reactButton.dataset.commentId = comment.id;
        reactButton.setAttribute('aria-pressed', Number(comment.viewer_reacted) > 0 ? 'true' : 'false');
        reactButton.className = Number(comment.viewer_reacted) > 0 ? 'is-reacted' : '';
        reactButton.innerHTML = `React <b>${Number(comment.reaction_count || 0).toLocaleString()}</b>`;
        const replyButton = document.createElement('button');
        replyButton.type = 'button';
        replyButton.dataset.commentReply = '';
        replyButton.dataset.commentId = comment.id;
        replyButton.textContent = 'Reply';
        actions.append(reactButton, replyButton);

        const replyForm = document.createElement('form');
        replyForm.className = 'comment-reply-form';
        replyForm.dataset.commentReplyForm = '';
        replyForm.hidden = true;
        replyForm.innerHTML = '<input name="content" maxlength="2000" placeholder="Write a reply..." required><button type="submit" aria-label="Post reply"><span class="material-symbols-rounded" aria-hidden="true">send</span></button>';

        body.append(author, meta, text, actions, replyForm);
        commentElement.append(avatar, body);

        if (comment.parent_id) {
            const parentComment = commentsList.querySelector(`[data-comment-id="${comment.parent_id}"]`);
            if (parentComment) {
                let replies = parentComment.querySelector(':scope > .comment-replies');
                if (!replies) {
                    replies = document.createElement('div');
                    replies.className = 'comment-replies';
                    parentComment.appendChild(replies);
                }
                replies.appendChild(commentElement);
                return;
            }
        }

        commentsList.appendChild(commentElement);
    };

    const ensureCommentList = (container, className) => {
        if (!container) return null;
        let list = container.querySelector(`.${className}`);
        if (!list) {
            list = document.createElement('div');
            list.className = className;
            container.appendChild(list);
        }
        return list;
    };

    const refreshPostComments = async (post) => {
        const detailsModal = post?.querySelector('[data-post-details-modal]');
        const commentsApi = detailsModal?.dataset.commentsApi;
        const postId = Number(post?.dataset.postId || 0);
        if (!post || !detailsModal || !commentsApi || !postId) return;

        const response = await fetch(`${commentsApi}?action=list&post_id=${postId}`, { cache: 'no-store' });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Unable to load the latest comments.');

        const comments = result.comments || [];
        const inlineList = ensureCommentList(post.querySelector('[data-feed-comments]'), 'feed-inline-comments-list');
        const modalList = ensureCommentList(detailsModal.querySelector('.post-details-comments'), 'post-comments-list');
        [inlineList, modalList].forEach((list) => {
            if (!list) return;
            list.replaceChildren();
            comments.forEach((comment) => appendCommentElement(list, comment, detailsModal));
        });
        post.querySelector('[data-inline-comments-empty]')?.remove();
        detailsModal.querySelector('.post-comments-empty')?.remove();
        const count = Number(result.comment_count ?? comments.length).toLocaleString();
        post.querySelector('.feed-comment-count')?.replaceChildren(document.createTextNode(count));
        post.querySelector('.feed-inline-comments-heading span')?.replaceChildren(document.createTextNode(count));
        detailsModal.querySelector('[data-modal-comment-count]')?.replaceChildren(document.createTextNode(count));
        detailsModal.querySelector('[data-modal-comment-heading]')?.replaceChildren(document.createTextNode(count));
    };

    const bindDynamicPostComments = (post) => {
        if (!post || post.dataset.commentsBound === 'true') return;
        post.dataset.commentsBound = 'true';
        const header = post.querySelector('[data-post-details-open]');
        const detailsModal = post.querySelector('[data-post-details-modal]');
        const openModal = () => {
            if (!detailsModal) return;
            detailsModal.hidden = false;
            document.body.classList.add('post-details-modal-open');
            refreshPostComments(post).catch(() => {});
        };
        header?.addEventListener('click', (event) => {
            if (!event.target.closest('[data-post-more]')) openModal();
        });
        header?.addEventListener('keydown', (event) => {
            if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('[data-post-more]')) {
                event.preventDefault();
                openModal();
            }
        });
        post.querySelector('[data-feed-comments-toggle]')?.addEventListener('click', () => {
            refreshPostComments(post).catch(() => {});
        });
    };

    document.addEventListener('click', async (event) => {
        const reactButton = event.target.closest('[data-comment-react]');
        if (reactButton) {
            const context = getCommentContext(reactButton);
            if (!context.detailsModal) {
                return;
            }
            reactButton.disabled = true;
            try {
                const result = await sendCommentRequest(context.detailsModal, 'react', { comment_id: reactButton.dataset.commentId });
                reactButton.classList.toggle('is-reacted', result.reacted);
                reactButton.setAttribute('aria-pressed', result.reacted ? 'true' : 'false');
                reactButton.querySelector('b').textContent = Number(result.reaction_count).toLocaleString();
            } catch (error) {
                window.alert(error.message);
            } finally {
                reactButton.disabled = false;
            }
            return;
        }

        const replyButton = event.target.closest('[data-comment-reply]');
        if (replyButton) {
            const comment = replyButton.closest('.post-comment');
            const replyForm = comment?.querySelector('[data-comment-reply-form]');
            if (replyForm) {
                replyForm.hidden = !replyForm.hidden;
                if (!replyForm.hidden) {
                    replyForm.querySelector('input')?.focus();
                }
            }
        }
    });

    document.addEventListener('submit', async (event) => {
        const replyForm = event.target.closest('[data-comment-reply-form]');
        if (!replyForm) {
            return;
        }
        event.preventDefault();
        const context = getCommentContext(replyForm);
        const input = replyForm.querySelector('input[name="content"]');
        const submitButton = replyForm.querySelector('button');
        const content = input.value.trim();
        if (!content || !context.detailsModal || !context.commentsList) {
            return;
        }

        submitButton.disabled = true;
        try {
            const parentComment = replyForm.closest('[data-comment-id]');
            const result = await sendCommentRequest(context.detailsModal, 'reply', {
                parent_id: parentComment.dataset.commentId,
                content,
            });
            context.post.querySelector('.feed-comment-count').textContent = Number(result.comment_count).toLocaleString();
            context.commentsList.closest('.feed-inline-comments, .post-details-comments')?.querySelector('[data-modal-comment-heading], .feed-inline-comments-heading span')?.replaceChildren(document.createTextNode(Number(result.comment_count).toLocaleString()));
            context.post.querySelector('[data-modal-comment-count]')?.replaceChildren(document.createTextNode(Number(result.comment_count).toLocaleString()));
            context.post.querySelector('.feed-inline-comments-heading span')?.replaceChildren(document.createTextNode(Number(result.comment_count).toLocaleString()));
            context.post.querySelector('[data-inline-comments-empty]')?.remove();
            appendCommentElement(context.commentsList, result.comment, context.detailsModal);
            input.value = '';
            replyForm.hidden = true;
        } catch (error) {
            window.alert(error.message);
        } finally {
            submitButton.disabled = false;
        }
    });

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
        button.addEventListener('click', async () => {
            const post = button.closest('.feed-post');
            const count = post?.querySelector('.feed-like-count');
            const detailsModal = post?.querySelector('[data-post-details-modal]');
            const postId = post?.dataset.postId || '0';
            const csrfToken = detailsModal?.dataset.csrfToken || '';
            const formData = new FormData();
            formData.set('action', 'post_interaction');
            formData.set('interaction', 'react');
            formData.set('post_id', postId);
            formData.set('csrf_token', csrfToken);

            try {
                const result = window.gamersHubSocket?.readyState === WebSocket.OPEN
                    ? await window.gamersHubRequest('react_post', { post_id: Number(postId) })
                    : await (async () => {
                        const response = await fetch(window.location.href, { method: 'POST', body: formData });
                        const responseData = await response.json();
                        if (!response.ok || !responseData.success) throw new Error(responseData.message || 'Unable to update this post.');
                        return responseData;
                    })();

                const liked = Boolean(result.liked);
                const likeCount = Number(result.like_count || 0);
                button.classList.toggle('is-liked', liked);
                button.setAttribute('aria-pressed', liked ? 'true' : 'false');
                if (count) {
                    count.textContent = likeCount.toLocaleString();
                }

                const modalButton = detailsModal?.querySelector('[data-post-interaction="react"]');
                if (modalButton) {
                    modalButton.classList.toggle('is-liked', liked);
                    modalButton.setAttribute('aria-pressed', liked ? 'true' : 'false');
                    const modalCount = detailsModal.querySelector('[data-modal-like-count]');
                    if (modalCount) {
                        modalCount.textContent = likeCount.toLocaleString();
                    }
                }
            } catch (error) {
                window.alert(error.message);
            }
        });
    });

    document.querySelectorAll('[data-feed-share]').forEach((button) => {
        button.addEventListener('click', async () => {
            const post = button.closest('.feed-post');
            const detailsModal = post?.querySelector('[data-post-details-modal]');
            const postId = Number(post?.dataset.postId || 0);
            if (!postId) return;
            button.disabled = true;
            try {
                const result = window.gamersHubSocket?.readyState === WebSocket.OPEN
                    ? await window.gamersHubRequest('share_post', { post_id: postId })
                    : await (async () => {
                        const formData = new FormData();
                        formData.set('action', 'post_interaction');
                        formData.set('interaction', 'share');
                        formData.set('post_id', String(postId));
                        formData.set('csrf_token', detailsModal?.dataset.csrfToken || '');
                        const response = await fetch(window.location.href, { method: 'POST', body: formData });
                        const responseData = await response.json();
                        if (!response.ok || !responseData.success) throw new Error(responseData.message || 'Unable to share this post.');
                        return responseData;
                    })();
                const count = post.querySelector('.feed-share-count');
                if (count) count.textContent = Number(result.share_count || 0).toLocaleString();
                button.classList.add('is-shared');
            } catch (error) {
                window.alert(error.message);
            } finally {
                button.disabled = false;
            }
        });
    });

    window.addEventListener('gamershub:socket-message', (event) => {
        const data = event.detail;
        if (data.type === 'post_reaction' || data.type === 'post_share') {
            document.querySelectorAll(`.feed-post[data-post-id="${CSS.escape(String(data.post_id))}"]`).forEach((post) => {
                const count = post.querySelector(data.type === 'post_reaction' ? '.feed-like-count' : '.feed-share-count');
                if (count) count.textContent = Number(data[data.type === 'post_reaction' ? 'like_count' : 'share_count']).toLocaleString();
            });
        }
        if (data.type === 'comment_created' || data.type === 'comment_reply') {
            document.querySelectorAll(`.feed-post[data-post-id="${CSS.escape(String(data.post_id))}"]`).forEach((post) => {
                const count = Number(data.comment_count || 0).toLocaleString();
                post.querySelector('.feed-comment-count').textContent = count;
                post.querySelector('.feed-inline-comments-heading span').textContent = count;
                post.querySelector('[data-modal-comment-count]')?.replaceChildren(document.createTextNode(count));
                post.querySelector('[data-modal-comment-heading]')?.replaceChildren(document.createTextNode(count));
                const comment = data.comment;
                if (!comment) return;
                const detailsModal = post.querySelector('[data-post-details-modal]');
                const inlineList = ensureCommentList(post.querySelector('[data-feed-comments]'), 'feed-inline-comments-list');
                const modalList = ensureCommentList(detailsModal?.querySelector('.post-details-comments'), 'post-comments-list');
                const commentSelector = `[data-comment-id="${CSS.escape(String(comment.id))}"]`;
                if (inlineList && !inlineList.querySelector(commentSelector)) appendCommentElement(inlineList, comment, detailsModal);
                if (modalList && !modalList.querySelector(commentSelector)) appendCommentElement(modalList, comment, detailsModal);
                post.querySelector('[data-inline-comments-empty]')?.remove();
                detailsModal?.querySelector('.post-comments-empty')?.remove();
            });
        }
        if (data.type === 'comment_reaction') {
            document.querySelectorAll(`.feed-post[data-post-id="${CSS.escape(String(data.post_id))}"] [data-comment-id="${CSS.escape(String(data.comment_id))}"] [data-comment-react]`).forEach((button) => {
                button.querySelector('b').textContent = Number(data.reaction_count || 0).toLocaleString();
            });
        }
    });

    const feedStream = document.querySelector('.feed-stream');
    const feedSentinel = feedStream?.querySelector('[data-feed-sentinel]');
    const feedPageSize = 20;
    let feedOffset = document.querySelectorAll('.feed-post').length;
    let feedLoading = false;
    let feedHasMore = Boolean(feedSentinel);

    const loadMoreFeedPosts = async () => {
        if (!feedStream || !feedSentinel || feedLoading || !feedHasMore) {
            return;
        }

        feedLoading = true;
        feedSentinel.hidden = false;
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('feed_offset', String(feedOffset));
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) {
                throw new Error('Unable to load more posts.');
            }

            const markup = await response.text();
            const nextDocument = new DOMParser().parseFromString(markup, 'text/html');
            const nextPosts = Array.from(nextDocument.querySelectorAll('.feed-post'));
            const existingPostIds = new Set(Array.from(feedStream.querySelectorAll('.feed-post')).map((post) => post.dataset.postId));
            const newPosts = nextPosts.filter((post) => !existingPostIds.has(post.dataset.postId));

            newPosts.forEach((post) => {
                feedStream.insertBefore(post, feedSentinel);
                bindDynamicPostComments(post);
            });
            feedOffset += nextPosts.length;
            feedHasMore = nextPosts.length === feedPageSize;
            if (!feedHasMore) {
                feedSentinel.hidden = true;
                feedSentinel.textContent = 'You have reached the end of your feed.';
            }
        } catch (error) {
            feedSentinel.textContent = error.message;
        } finally {
            feedLoading = false;
        }
    };

    if (feedSentinel && 'IntersectionObserver' in window) {
        const feedObserver = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                loadMoreFeedPosts();
            }
        }, { rootMargin: '0px 0px 500px' });
        feedObserver.observe(feedSentinel);
    } else if (feedSentinel) {
        window.addEventListener('scroll', () => {
            const remaining = feedSentinel.getBoundingClientRect().top - window.innerHeight;
            if (remaining < 500) {
                loadMoreFeedPosts();
            }
        }, { passive: true });
    }

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