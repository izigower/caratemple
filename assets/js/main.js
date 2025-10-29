/*
 * CaraTemple main script.
 * Handles responsive sidebar interactions for the landing page layout.
 */

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const openButton = document.querySelector('[data-menu-toggle]');
    const closeButton = document.querySelector('[data-menu-close]');

    if (sidebar && openButton) {
        openButton.addEventListener('click', () => {
            sidebar.classList.add('is-open');
        });
    }

    if (sidebar && closeButton) {
        closeButton.addEventListener('click', () => {
            sidebar.classList.remove('is-open');
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
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar?.classList.contains('is-open')) {
            sidebar.classList.remove('is-open');
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 992) {
            sidebar?.classList.remove('is-open');
        }
    });

    initAuthValidation();
    initDiscussionValidation();
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
