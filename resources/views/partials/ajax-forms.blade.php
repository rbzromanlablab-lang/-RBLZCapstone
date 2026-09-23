<style>
    .ajax-notice {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 2000;
        width: min(390px, calc(100vw - 2rem));
        padding: 1rem;
        border: 0;
        border-radius: 1rem;
        color: #fff;
        background: #198754;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2);
        animation: ajax-notice-in 260ms ease-out;
    }
    .ajax-button-loading { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; }
    .ajax-notice.is-error { background: #b42318; }
    .ajax-notice-title { display: flex; align-items: center; gap: .55rem; font-weight: 700; }
    .ajax-notice p { margin: .3rem 0 0; }
    .ajax-notice-icon { font-size: 1.25rem; animation: ajax-check-in 320ms ease-out; }
    .ajax-submit-spinner {
        width: 1rem;
        height: 1rem;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: ajax-spin .7s linear infinite;
    }
    @keyframes ajax-notice-in {
        from { opacity: 0; transform: translateY(-12px) scale(.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes ajax-check-in {
        from { transform: scale(.55); }
        to { transform: scale(1); }
    }
    @keyframes ajax-spin { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) {
        .ajax-notice, .ajax-notice-icon, .ajax-submit-spinner { animation-duration: .01ms; }
    }
</style>
<script>
    (() => {
        const mutatingMethods = new Set(['POST', 'PUT', 'PATCH', 'DELETE']);
        let noticeTimer;
        const noticeStorageKey = 'pards.action-notice';

        const actionFeedback = (form, button) => {
            const label = (button instanceof HTMLInputElement ? button.value : button?.textContent || '')
                .replace(/\s+/g, ' ').trim() || 'Action';
            let loading = label + '…';
            const specialActions = [
                [/^log\s*out\b/i, 'Logging out…'],
                [/^log\s*in\b/i, 'Logging in…'],
                [/^resend\s+otp\b/i, 'Resending OTP…'],
                [/^send\s+otp\b/i, 'Sending OTP…'],
                [/^verify\b/i, 'Verifying account…'],
                [/^mark as returned\b/i, 'Recording return…'],
                [/^confirm assignment\b/i, 'Assigning property and creating receipt…'],
            ];
            const special = specialActions.find(([pattern]) => pattern.test(label));
            if (special) {
                loading = special[1];
            } else {
                const verbs = {save: 'Saving', update: 'Updating', delete: 'Deleting', remove: 'Removing',
                    submit: 'Submitting', forward: 'Forwarding', approve: 'Approving', reject: 'Rejecting',
                    activate: 'Activating', deactivate: 'Deactivating', assign: 'Assigning', confirm: 'Confirming',
                    create: 'Creating', return: 'Returning', upload: 'Uploading'};
                const verb = label.split(' ')[0].toLowerCase();
                if (verbs[verb]) loading = verbs[verb] + label.slice(verb.length) + '…';
            }
            return {
                title: label,
                loading: button?.dataset.loadingText || form.dataset.loadingText || loading,
                success: button?.dataset.successMessage || form.dataset.successMessage || label + ' completed successfully.',
            };
        };

        const showNotice = (message, error = false, actionTitle = 'Success') => {
            document.querySelector('[data-ajax-notice]')?.remove();
            clearTimeout(noticeTimer);

            const notice = document.createElement('div');
            notice.className = `ajax-notice${error ? ' is-error' : ''}`;
            notice.dataset.ajaxNotice = '';
            notice.setAttribute('role', error ? 'alert' : 'status');
            notice.setAttribute('aria-live', error ? 'assertive' : 'polite');

            const title = document.createElement('div');
            title.className = 'ajax-notice-title';
            const icon = document.createElement('span');
            icon.className = 'ajax-notice-icon';
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = error ? '!' : '✓';
            title.append(icon, document.createTextNode(error ? 'Action needed' : actionTitle));

            const copy = document.createElement('p');
            copy.textContent = message;
            notice.append(title, copy);
            document.body.append(notice);
            noticeTimer = setTimeout(() => notice.remove(), error ? 6500 : 4200);
        };

        const clearErrors = (form) => {
            form.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
            form.querySelectorAll('[data-ajax-error]').forEach((error) => error.remove());
        };

        const showErrors = (form, errors) => {
            let firstField;
            Object.entries(errors || {}).forEach(([name, messages]) => {
                const fieldName = name.replace(/\.([^\.]+)/g, '[$1]');
                const selectorName = CSS.escape(fieldName);
                const field = form.querySelector(`[name="${selectorName}"], [name="${selectorName}[]"]`);
                if (!field) return;
                field.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block';
                feedback.dataset.ajaxError = name;
                feedback.textContent = Array.isArray(messages) ? messages[0] : messages;
                const inputGroup = field.closest('.input-group');
                if (inputGroup) inputGroup.after(feedback);
                else field.after(feedback);
                firstField ??= field;
            });
            firstField?.focus({preventScroll: true});
            firstField?.scrollIntoView({behavior: 'smooth', block: 'center'});
        };

        const setLoading = (button, loading, message = '') => {
            if (!button) return;
            if (loading) {
                button.dataset.originalHtml = button.innerHTML;
                if (button instanceof HTMLInputElement) button.dataset.originalValue = button.value;
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                if (button instanceof HTMLInputElement) {
                    button.value = message;
                } else {
                    const spinner = document.createElement('span');
                    spinner.className = 'ajax-submit-spinner';
                    spinner.setAttribute('aria-hidden', 'true');
                    const text = document.createElement('span');
                    text.textContent = message;
                    button.replaceChildren(spinner, text);
                    button.classList.add('ajax-button-loading');
                }
            } else {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                button.classList.remove('ajax-button-loading');
                if (button.dataset.originalHtml !== undefined) {
                    button.innerHTML = button.dataset.originalHtml;
                    delete button.dataset.originalHtml;
                }
                if (button.dataset.originalValue !== undefined) {
                    button.value = button.dataset.originalValue;
                    delete button.dataset.originalValue;
                }
            }
        };

        try {
            const savedNotice = sessionStorage.getItem(noticeStorageKey);
            sessionStorage.removeItem(noticeStorageKey);
            if (savedNotice) {
                const notice = JSON.parse(savedNotice);
                if (notice.expiresAt > Date.now() && typeof notice.message === 'string' && typeof notice.title === 'string') {
                    showNotice(notice.message, false, notice.title);
                }
            }
        } catch { /* Feedback still works when browser storage is unavailable. */ }

        document.addEventListener('submit', async (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-native-submit')) return;

            const button = event.submitter || form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
            const feedback = actionFeedback(form, button);
            const formData = new FormData(form);
            if (button?.name) formData.append(button.name, button.value);
            const method = (formData.get('_method') || form.method || 'GET').toString().toUpperCase();
            if (!mutatingMethods.has(method) || event.defaultPrevented) return;

            event.preventDefault();
            if (form.dataset.ajaxSubmitting === 'true') return;
            form.dataset.ajaxSubmitting = 'true';
            clearErrors(form);
            setLoading(button, true, feedback.loading);

            try {
                const response = await fetch(form.action, {
                    method: form.method.toUpperCase() === 'GET' ? 'POST' : form.method.toUpperCase(),
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 422) showErrors(form, data.errors);
                    throw new Error(data.message || feedback.title + ' could not be completed. Please try again.');
                }

                const message = data.message || feedback.success;
                showNotice(message, false, feedback.title);
                if (data.redirect) {
                    try {
                        sessionStorage.setItem(noticeStorageKey, JSON.stringify({
                            message, title: feedback.title, expiresAt: Date.now() + 15000,
                        }));
                    } catch { /* The current page already displays the confirmation. */ }
                    setTimeout(() => window.location.assign(data.redirect), 650);
                } else {
                    setLoading(button, false);
                    delete form.dataset.ajaxSubmitting;
                }
            } catch (error) {
                showNotice(error.message || 'A network error occurred. Please try again.', true);
                setLoading(button, false);
                delete form.dataset.ajaxSubmitting;
            }
        });
    })();
</script>
