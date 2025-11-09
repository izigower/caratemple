/*
 * CaraTemple main script.
 * Handles responsive sidebar interactions for the landing page layout.
 */

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const openButton = document.querySelector('[data-menu-toggle]');
    const closeButton = document.querySelector('[data-menu-close]');

    const setExpandedState = (expanded) => {
        if (openButton) {
            openButton.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    };

    if (sidebar && openButton) {
        openButton.addEventListener('click', () => {
            sidebar.classList.add('is-open');
            setExpandedState(true);
        });
    }

    if (sidebar && closeButton) {
        closeButton.addEventListener('click', () => {
            sidebar.classList.remove('is-open');
            setExpandedState(false);
        });
    }

    document.addEventListener('click', (event) => {
        if (!sidebar?.classList.contains('is-open')) {
            return;
        }

        if (event.target instanceof Node && sidebar.contains(event.target)) {
            return;
        }

        if (event.target === openButton) {
            return;
        }

        sidebar.classList.remove('is-open');
        setExpandedState(false);
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar?.classList.contains('is-open')) {
            sidebar.classList.remove('is-open');
            setExpandedState(false);
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 992) {
            sidebar?.classList.remove('is-open');
            setExpandedState(false);
        }
    });

    initAuthValidation();
    initDiscussionValidation();
    initAdminActions();
});

function initAuthValidation() {
    const forms = document.querySelectorAll('[data-validate="auth"]');

    if (!forms.length) {
        return;
    }

    const validators = {
        username: (value) => {
            if (!value) {
                return { valid: false, message: 'Le pseudo est requis.' };
            }
            if (!/^[A-Za-z0-9_]{3,20}$/.test(value)) {
                return { valid: false, message: '3 à 20 caractères autorisés (lettres, chiffres, underscore).' };
            }
            return { valid: true };
        },
        email: (value) => {
            if (!value) {
                return { valid: false, message: 'L\'email est requis.' };
            }
            const emailPattern = /^(?:[a-zA-Z0-9_+.-]+)@(?:[a-zA-Z0-9.-]+)\.[a-zA-Z]{2,}$/u;
            if (!emailPattern.test(value)) {
                return { valid: false, message: 'Format d\'email invalide.' };
            }
            return { valid: true };
        },
        password: (value) => {
            if (!value) {
                return { valid: false, message: 'Le mot de passe est requis.' };
            }
            const hasLength = value.length >= 8;
            const hasLetter = /[A-Za-z]/.test(value);
            const hasNumber = /\d/.test(value);

            if (!hasLength || !hasLetter || !hasNumber) {
                return { valid: false, message: '8 caractères minimum avec lettres et chiffres.' };
            }

            return { valid: true };
        },
        password_confirm: (value, form) => {
            const passwordField = form.querySelector('input[name="password"]');
            const passwordValue = passwordField ? passwordField.value : '';

            if (!value) {
                return { valid: false, message: 'Confirme ton mot de passe.' };
            }

            if (value !== passwordValue) {
                return { valid: false, message: 'Les mots de passe doivent correspondre.' };
            }

            return { valid: true };
        },
    };

    forms.forEach((form) => {
        const inputs = form.querySelectorAll('[data-validate-field]');

        const validateInput = (input) => {
            const fieldKey = input.dataset.validateField;
            const value = input.value.trim();
            const feedback = form.querySelector(`[data-feedback="${fieldKey}"]`);
            const defaultMessage = feedback?.dataset.default ?? '';
            const fieldWrapper = input.closest('[data-field]');
            const statusIndicator = fieldWrapper?.querySelector('.input-status');

            const validator = validators[fieldKey];
            let result = { valid: true, message: defaultMessage };

            if (typeof validator === 'function') {
                result = validator(value, form);
            }

            if (!result.valid) {
                fieldWrapper?.classList.add('is-invalid');
                fieldWrapper?.classList.remove('is-valid');
                if (feedback) {
                    feedback.textContent = result.message || defaultMessage;
                }
                if (statusIndicator) {
                    statusIndicator.textContent = '⚠️';
                }
                return false;
            }

            fieldWrapper?.classList.remove('is-invalid');
            fieldWrapper?.classList.add('is-valid');
            if (feedback) {
                feedback.textContent = defaultMessage;
            }
            if (statusIndicator) {
                statusIndicator.textContent = '✔️';
            }
            return true;
        };

        inputs.forEach((input) => {
            const fieldWrapper = input.closest('[data-field]');
            const statusIndicator = fieldWrapper?.querySelector('.input-status');

            if (fieldWrapper?.classList.contains('is-invalid') && statusIndicator) {
                statusIndicator.textContent = '⚠️';
            }

            input.addEventListener('input', () => {
                validateInput(input);
            });

            input.addEventListener('blur', () => {
                validateInput(input);
            });

            if (input.value && form.dataset.authType === 'register') {
                validateInput(input);
            }
        });

        form.addEventListener('submit', (event) => {
            let isFormValid = true;
            inputs.forEach((input) => {
                const isValid = validateInput(input);
                if (!isValid) {
                    isFormValid = false;
                }
            });

            if (!isFormValid) {
                event.preventDefault();
            }
        });
    });
}

function initDiscussionValidation() {
    const forms = document.querySelectorAll('[data-validate="discussion"]');

    if (!forms.length) {
        return;
    }

    const validators = {
        title: (value) => {
            if (!value) {
                return { valid: false, message: 'Le titre est requis.' };
            }
            if (value.length < 6) {
                return { valid: false, message: '6 caractères minimum.' };
            }
            return { valid: true };
        },
        body: (value) => {
            if (!value) {
                return { valid: false, message: 'Le message est requis.' };
            }
            if (value.length < 20) {
                return { valid: false, message: 'Développe ton idée (20 caractères minimum).' };
            }
            return { valid: true };
        },
        message: (value) => {
            if (!value) {
                return { valid: false, message: 'Ton message est requis.' };
            }
            if (value.length < 3) {
                return { valid: false, message: 'Au moins 3 caractères.' };
            }
            return { valid: true };
        },
        tag_line: (value) => {
            if (value.length > 120) {
                return { valid: false, message: '120 caractères maximum.' };
            }
            return { valid: true };
        },
        category: (value) => {
            if (!value) {
                return { valid: false, message: 'Sélectionne une catégorie.' };
            }
            return { valid: true };
        },
    };

    forms.forEach((form) => {
        const inputs = form.querySelectorAll('[data-validate-field]');

        const validateInput = (input) => {
            const fieldKey = input.dataset.validateField;
            const value = input.value.trim();
            const feedback = form.querySelector(`[data-feedback="${fieldKey}"]`);
            const defaultMessage = feedback?.dataset.default ?? '';
            const fieldWrapper = input.closest('[data-field]');
            const statusIndicator = fieldWrapper?.querySelector('.input-status');

            const validator = validators[fieldKey];
            let result = { valid: true, message: defaultMessage };

            if (typeof validator === 'function') {
                result = validator(value, form);
            }

            if (!result.valid) {
                fieldWrapper?.classList.add('is-invalid');
                fieldWrapper?.classList.remove('is-valid');
                if (feedback) {
                    feedback.textContent = result.message || defaultMessage;
                }
                if (statusIndicator) {
                    statusIndicator.textContent = '⚠️';
                }
                return false;
            }

            fieldWrapper?.classList.remove('is-invalid');
            fieldWrapper?.classList.add('is-valid');
            if (feedback) {
                feedback.textContent = defaultMessage;
            }
            if (statusIndicator) {
                statusIndicator.textContent = '✔️';
            }
            return true;
        };

        inputs.forEach((input) => {
            const fieldWrapper = input.closest('[data-field]');
            const statusIndicator = fieldWrapper?.querySelector('.input-status');

            if (fieldWrapper?.classList.contains('is-invalid') && statusIndicator) {
                statusIndicator.textContent = '⚠️';
            }

            input.addEventListener('input', () => {
                validateInput(input);
            });

            input.addEventListener('blur', () => {
                validateInput(input);
            });
        });

        form.addEventListener('submit', (event) => {
            let isFormValid = true;
            inputs.forEach((input) => {
                const isValid = validateInput(input);
                if (!isValid) {
                    isFormValid = false;
                }
            });

            if (!isFormValid) {
                event.preventDefault();
            }
        });
    });
}

function initAdminActions() {
    const forms = document.querySelectorAll('form[data-confirm]');

    if (!forms.length) {
        return;
    }

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirm || 'Confirmer cette action ?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
}

/**
 * Initialize Ajax like buttons
 */
function initLikeButtons() {
    const likeButtons = document.querySelectorAll('[data-like-button]');

    if (!likeButtons.length) {
        return;
    }

    likeButtons.forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();

            const postId = button.dataset.postId;
            if (!postId) {
                return;
            }

            // Disable button during request
            button.disabled = true;

            try {
                const response = await fetch('/api/like.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `post_id=${encodeURIComponent(postId)}`
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                if (data.success) {
                    // Update like count
                    const countElement = button.querySelector('[data-like-count]');
                    if (countElement) {
                        countElement.textContent = data.likes_count;
                    }

                    // Update button state
                    if (data.is_liked) {
                        button.classList.add('is-liked');
                        button.setAttribute('aria-pressed', 'true');
                    } else {
                        button.classList.remove('is-liked');
                        button.setAttribute('aria-pressed', 'false');
                    }
                } else {
                    console.error('Like toggle failed:', data.error);
                }
            } catch (error) {
                console.error('Error toggling like:', error);
            } finally {
                button.disabled = false;
            }
        });
    });
}

// Initialize like buttons on page load
document.addEventListener('DOMContentLoaded', () => {
    initLikeButtons();
});

/**
 * Initialize Ajax admin delete buttons
 */
function initAdminDeleteButtons() {
    const deleteButtons = document.querySelectorAll('[data-admin-delete]');

    if (!deleteButtons.length) {
        return;
    }

    deleteButtons.forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();

            const action = button.dataset.action;
            const confirmMessage = button.dataset.confirm || 'Confirmer la suppression ?';
            
            if (!window.confirm(confirmMessage)) {
                return;
            }

            // Get form data
            const form = button.closest('form');
            if (!form) {
                return;
            }

            const formData = new FormData(form);
            
            // Disable button during request
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Suppression...';

            try {
                const response = await fetch('/api/admin_delete.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    const row = button.closest('tr');
                    if (row) {
                        row.style.opacity = '0.5';
                        row.style.transition = 'opacity 0.3s ease';
                        setTimeout(() => {
                            row.remove();
                        }, 300);
                    }

                    // Show notification
                    showNotification(data.message || 'Suppression réussie', 'success');

                    // Redirect if needed
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 500);
                    }
                } else {
                    showNotification(data.error || 'Erreur lors de la suppression', 'error');
                    button.disabled = false;
                    button.textContent = originalText;
                }
            } catch (error) {
                console.error('Error deleting:', error);
                showNotification('Erreur réseau', 'error');
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification--${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#2ab673' : type === 'error' ? '#ff4444' : '#3aa9f2'};
        color: white;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Initialize admin delete buttons on page load
document.addEventListener('DOMContentLoaded', () => {
    initAdminDeleteButtons();
});

/**
 * Initialize instant search
 */
function initInstantSearch() {
    const searchInput = document.querySelector('[data-instant-search]');
    const searchResults = document.querySelector('[data-search-results]');

    if (!searchInput || !searchResults) {
        return;
    }

    let searchTimeout;
    let currentQuery = '';

    searchInput.addEventListener('input', (event) => {
        const query = event.target.value.trim();

        // Clear previous timeout
        clearTimeout(searchTimeout);

        // If query is empty, hide results
        if (query === '') {
            searchResults.innerHTML = '';
            searchResults.style.display = 'none';
            return;
        }

        // If query is too short, show hint
        if (query.length < 2) {
            searchResults.innerHTML = '<div class="search-hint">Tape au moins 2 caractères...</div>';
            searchResults.style.display = 'block';
            return;
        }

        // Debounce search (wait 300ms after last keystroke)
        searchTimeout = setTimeout(async () => {
            currentQuery = query;

            try {
                const response = await fetch(`/api/search.php?q=${encodeURIComponent(query)}`);
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                // Check if this is still the current query
                if (query !== currentQuery) {
                    return;
                }

                if (data.success) {
                    displaySearchResults(data.results, searchResults);
                } else {
                    searchResults.innerHTML = '<div class="search-error">Erreur de recherche</div>';
                    searchResults.style.display = 'block';
                }
            } catch (error) {
                console.error('Search error:', error);
                searchResults.innerHTML = '<div class="search-error">Erreur réseau</div>';
                searchResults.style.display = 'block';
            }
        }, 300);
    });

    // Close results when clicking outside
    document.addEventListener('click', (event) => {
        if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Show results when focusing on search input
    searchInput.addEventListener('focus', () => {
        if (searchResults.innerHTML !== '') {
            searchResults.style.display = 'block';
        }
    });
}

/**
 * Display search results
 */
function displaySearchResults(results, container) {
    if (results.length === 0) {
        container.innerHTML = '<div class="search-empty">Aucun résultat trouvé</div>';
        container.style.display = 'block';
        return;
    }

    let html = '<ul class="search-results-list">';
    
    results.forEach((result) => {
        html += `
            <li class="search-result-item">
                <a href="${result.url}" class="search-result-link">
                    <div class="search-result-title">${escapeHtml(result.title)}</div>
                    <div class="search-result-meta">
                        <span class="search-result-category">${escapeHtml(result.category)}</span>
                        <span class="search-result-author">par ${escapeHtml(result.username)}</span>
                        <span class="search-result-stats">💬 ${result.replies_count} · 👁️ ${result.views_count}</span>
                    </div>
                </a>
            </li>
        `;
    });
    
    html += '</ul>';
    
    container.innerHTML = html;
    container.style.display = 'block';
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize instant search on page load
document.addEventListener('DOMContentLoaded', () => {
    initInstantSearch();
});

/**
 * Initialize Ajax reply form
 */
function initAjaxReplyForm() {
    const replyForm = document.querySelector('[data-ajax-reply-form]');

    if (!replyForm) {
        return;
    }

    const messageField = replyForm.querySelector('[name="message"]');
    const submitButton = replyForm.querySelector('button[type="submit"]');
    const repliesContainer = document.querySelector('[data-replies-container]');

    replyForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(replyForm);
        const message = formData.get('message');

        // Validate message
        if (!message || message.trim().length < 3) {
            showNotification('Le message doit contenir au moins 3 caractères', 'error');
            return;
        }

        // Disable form during submission
        submitButton.disabled = true;
        const originalText = submitButton.textContent;
        submitButton.textContent = 'Publication...';

        try {
            const response = await fetch('/api/post_reply.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Clear the form
                messageField.value = '';

                // Add the new reply to the list
                if (repliesContainer && data.post) {
                    const replyHtml = createReplyHtml(data.post);
                    repliesContainer.insertAdjacentHTML('beforeend', replyHtml);

                    // Scroll to the new reply
                    const newReply = repliesContainer.lastElementChild;
                    if (newReply) {
                        newReply.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        
                        // Highlight the new reply
                        newReply.style.animation = 'highlightNew 2s ease';
                    }
                }

                // Update reply count
                updateReplyCount();

                showNotification(data.message || 'Réponse publiée !', 'success');
            } else {
                showNotification(data.error || 'Erreur lors de la publication', 'error');
            }
        } catch (error) {
            console.error('Error posting reply:', error);
            showNotification('Erreur réseau', 'error');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    });
}

/**
 * Create HTML for a new reply
 */
function createReplyHtml(post) {
    return `
        <article class="reply-card" id="post-${post.id}" style="animation: slideInUp 0.3s ease;">
            <header class="reply-card__header">
                <div class="reply-card__author">
                    <div class="avatar" aria-hidden="true">🐢</div>
                    <div>
                        <h3>${escapeHtml(post.username)}</h3>
                        <p>${escapeHtml(post.relative_time)}</p>
                    </div>
                </div>
                <div class="reply-card__stats">
                    <span aria-label="0 likes">❤️ 0</span>
                </div>
            </header>
            <div class="reply-card__content">
                ${escapeHtml(post.body).replace(/\n/g, '<br>')}
            </div>
            <footer class="reply-card__footer">
                <button 
                    class="btn ghost" 
                    data-like-button 
                    data-post-id="${post.id}"
                    aria-pressed="false">
                    <span>J'aime</span> · <span data-like-count>0</span>
                </button>
            </footer>
        </article>
    `;
}

/**
 * Update reply count in the page
 */
function updateReplyCount() {
    const countElement = document.querySelector('[data-reply-count]');
    if (countElement) {
        const currentCount = parseInt(countElement.textContent) || 0;
        countElement.textContent = currentCount + 1;
    }
}

// Initialize Ajax reply form on page load
document.addEventListener('DOMContentLoaded', () => {
    initAjaxReplyForm();
});
