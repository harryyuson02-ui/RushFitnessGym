document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.getAttribute('aria-controls'));
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
    });
});

document.querySelectorAll('[data-digits-only]').forEach((input) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, Number(input.maxLength));
    });
});

document.querySelectorAll('[data-ph-mobile]').forEach((input) => {
    const error = input.closest('label')?.querySelector('[aria-live="polite"]');
    const validate = () => {
        const valid = /^09[0-9]{9}$/.test(input.value) && input.value.length === 11;
        const message = valid ? '' : input.title;
        input.setCustomValidity(message);
        input.setAttribute('aria-invalid', String(!valid));
        if (error) {
            error.textContent = message;
            error.hidden = valid;
        }
    };

    input.addEventListener('input', validate);
    input.addEventListener('blur', validate);
    input.addEventListener('invalid', validate);
    if (input.value !== '') validate();
});

document.addEventListener('click', (event) => {
    document.querySelectorAll('.rush-menu[open]').forEach((menu) => {
        if (!menu.contains(event.target)) menu.open = false;
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('.rush-menu[open]').forEach((menu) => {
        menu.open = false;
        menu.querySelector('summary').focus();
    });
});

document.querySelectorAll('[data-multistep-form]').forEach((form) => {
    const panels = [...form.querySelectorAll('[data-step-panel]')];
    const indicators = [...form.querySelectorAll('[data-step-indicator]')];
    const previous = form.querySelector('[data-step-previous]');
    const next = form.querySelector('[data-step-next]');
    const submit = form.querySelector('[data-step-submit]');
    let step = Number(form.dataset.initialStep ?? 0);

    const render = () => {
        panels.forEach((panel, index) => { panel.hidden = index !== step; });
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('bg-lime-50', index <= step);
            indicator.querySelector('span').classList.toggle('bg-lime-400', index <= step);
        });
        previous.hidden = step === 0;
        next.hidden = step === panels.length - 1;
        submit.hidden = step !== panels.length - 1;
    };

    next.addEventListener('click', () => {
        const fields = [...panels[step].querySelectorAll('input, select, textarea')];
        if (fields.some((field) => !field.reportValidity())) return;
        const availabilityFields = fields.filter((field) => field.name === 'availability_days[]');
        if (availabilityFields.length > 0 && !availabilityFields.some((field) => field.checked)) {
            availabilityFields[0].setCustomValidity('Select at least one available day.');
            availabilityFields[0].reportValidity();
            availabilityFields[0].addEventListener('change', () => availabilityFields[0].setCustomValidity(''), { once: true });
            return;
        }
        step = Math.min(step + 1, panels.length - 1);
        render();
    });
    previous.addEventListener('click', () => { step = Math.max(step - 1, 0); render(); });
    render();
});

document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
    input.addEventListener('change', () => {
        const fields = document.querySelector('[data-gcash-fields]');
        if (!fields) return;
        const isGcash = document.querySelector('input[name="payment_method"]:checked')?.value === 'gcash';
        fields.hidden = !isGcash;
        fields.querySelectorAll('input').forEach((field) => { field.required = isGcash; });
    });
    if (input.checked) input.dispatchEvent(new Event('change'));
});

const memberList = document.querySelector('[data-member-list]');
const memberTotal = document.querySelector('[data-member-total]');

if (memberList) {
    let isRefreshing = false;

    const refreshMembers = async () => {
        if (isRefreshing || document.visibilityState !== 'visible') return;

        isRefreshing = true;

        try {
            const response = await fetch(window.location.href, {
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) return;

            const documentCopy = new DOMParser().parseFromString(await response.text(), 'text/html');
            const updatedMemberList = documentCopy.querySelector('[data-member-list]');
            const updatedMemberTotal = documentCopy.querySelector('[data-member-total]');

            if (updatedMemberList) memberList.innerHTML = updatedMemberList.innerHTML;
            if (updatedMemberTotal && memberTotal) memberTotal.textContent = updatedMemberTotal.textContent;
        } catch {
            // Keep the current directory visible when a background refresh cannot connect.
        } finally {
            isRefreshing = false;
        }
    };

    window.setInterval(refreshMembers, 5000);
    document.addEventListener('visibilitychange', refreshMembers);
}
