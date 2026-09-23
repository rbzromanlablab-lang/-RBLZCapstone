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

        const showNotice = (message, error = false) => {
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
            title.append(icon, document.createTextNode(error ? 'Action needed' : 'Success'));

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

        const setLoading = (form, loading) => {
            const button = form.querySelector('button[type="submit"], input[type="submit"]');
            if (!button) return;
            if (loading) {
                button.dataset.originalHtml = button.innerHTML;
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                button.innerHTML = '<span class="ajax-submit-spinner" aria-hidden="true"></span><span>Saving…</span>';
                button.style.display = 'inline-flex';
                button.style.alignItems = 'center';
                button.style.justifyContent = 'center';
                button.style.gap = '.5rem';
            } else {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                if (button.dataset.originalHtml !== undefined) {
                    button.innerHTML = button.dataset.originalHtml;
                    delete button.dataset.originalHtml;
                }
            }
        };

        document.addEventListener('submit', async (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-native-submit')) return;

            const formData = new FormData(form);
            const method = (formData.get('_method') || form.method || 'GET').toString().toUpperCase();
            if (!mutatingMethods.has(method) || event.defaultPrevented) return;

            event.preventDefault();
            if (form.dataset.ajaxSubmitting === 'true') return;
            form.dataset.ajaxSubmitting = 'true';
            clearErrors(form);
            setLoading(form, true);

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
                    throw new Error(data.message || 'The request could not be saved. Please try again.');
                }

                showNotice(data.message || 'Saved successfully.');
                if (data.redirect) {
                    setTimeout(() => window.location.assign(data.redirect), 650);
                } else {
                    setLoading(form, false);
                    delete form.dataset.ajaxSubmitting;
                }
            } catch (error) {
                showNotice(error.message || 'A network error occurred. Please try again.', true);
                setLoading(form, false);
                delete form.dataset.ajaxSubmitting;
            }
        });
    })();
</script>
