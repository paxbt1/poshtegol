import './bootstrap';

const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

window.toast = (message, type = 'info') => {
    let stack = document.querySelector('.toast-stack');
    if (!stack) {
        stack = document.createElement('div');
        stack.className = 'toast-stack';
        document.body.appendChild(stack);
    }

    const item = document.createElement('div');
    item.className = `toast ${type}`;
    item.textContent = message;
    stack.appendChild(item);
    setTimeout(() => item.remove(), 3600);
};

window.formatMoney = (amount) => new Intl.NumberFormat('fa-IR').format(Number(amount || 0)) + ' تومان';

const clearErrors = (form) => {
    form.querySelectorAll('[data-error-for]').forEach((el) => {
        el.textContent = '';
    });
};

const showErrors = (form, errors = {}) => {
    Object.entries(errors || {}).forEach(([key, messages]) => {
        const target = form.querySelector(`[data-error-for="${key}"]`);
        if (target) target.textContent = messages[0];
    });
};

document.querySelectorAll('form[data-ajax]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(form);

        const button = form.querySelector('[type="submit"]');
        const oldText = button?.textContent;
        const actionUrl = form.getAttribute('action') || form.dataset.action || window.location.href;
        const method = (form.getAttribute('method') || 'POST').toUpperCase();

        if (button) {
            button.disabled = true;
            button.textContent = 'در حال انجام...';
        }

        try {
            const response = await fetch(actionUrl, {
                method,
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: new FormData(form),
            });

            let data = {};
            try {
                data = await response.json();
            } catch (jsonError) {
                data = {};
            }

            if (!response.ok) {
                showErrors(form, data.errors);
                window.toast(data.message || 'اطلاعات واردشده را بررسی کنید.', 'error');
                return;
            }

            window.toast(data.message || 'انجام شد.', 'success');
            form.dispatchEvent(new CustomEvent('ajax:success', { detail: data }));

            if (data.redirect) {
                window.location.href = data.redirect;
            }
        } catch (error) {
            window.toast('ارتباط با سرور برقرار نشد.', 'error');
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = oldText;
            }
        }
    });
});

document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
        await navigator.clipboard.writeText(button.dataset.copy);
        window.toast('لینک کپی شد.', 'success');
    });
});

document.querySelectorAll('[data-auth-tab]').forEach((button) => {
    button.addEventListener('click', () => {
        const target = button.dataset.authTab;
        document.querySelectorAll('[data-auth-tab]').forEach((item) => item.classList.toggle('active', item === button));
        document.querySelectorAll('[data-auth-panel]').forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.authPanel !== target);
        });
    });
});

const predictionForm = document.querySelector('[data-prediction-form]');
if (predictionForm) {
    const previewUrl = predictionForm.dataset.previewUrl;
    const entryLabel = document.querySelector('[data-entry-amount]');
    const payableLabel = document.querySelector('[data-payable-amount]');
    const payForm = document.querySelector('[data-pay-form]');
    const payButton = document.querySelector('[data-pay-button]');

    const preview = async () => {
        const response = await fetch(previewUrl, {
            method: 'POST',
            headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
            body: new FormData(predictionForm),
        });
        const data = await response.json();
        if (entryLabel) entryLabel.textContent = data.entry_amount_label || window.formatMoney(data.entry_amount);
        if (payableLabel) payableLabel.textContent = data.payable_amount_label || window.formatMoney(data.payable_amount);
    };

    predictionForm.querySelectorAll('input, select').forEach((field) => field.addEventListener('change', preview));
    preview();

    predictionForm.addEventListener('ajax:success', (event) => {
        if (payForm && event.detail.pay_url) {
            payForm.action = event.detail.pay_url;
            payForm.classList.remove('hidden');
            payButton?.removeAttribute('disabled');
            payForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
}

const liveRoom = document.querySelector('[data-live-room]');
if (liveRoom) {
    const refreshLive = async () => {
        const response = await fetch(liveRoom.dataset.statusUrl, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) return;
        const data = await response.json();
        document.querySelector('[data-live-score]').textContent = data.score;
        document.querySelector('[data-live-minute]').textContent = data.minute ? `دقیقه ${data.minute}` : 'زمان نامشخص';
        if (data.prediction_status) {
            const badge = document.querySelector('[data-prediction-live-badge]');
            badge.textContent = data.prediction_status.label;
            badge.className = `badge ${data.prediction_status.class}`;
        }
        const events = document.querySelector('[data-live-events]');
        if (events) {
            events.innerHTML = data.events.map((event) => `<div class="leaderboard-row"><span class="rank-bubble">${event.minute ?? '-'}</span><div><strong>${event.title}</strong><div class="muted small">${event.description ?? event.team ?? ''}</div></div></div>`).join('');
        }
    };

    setInterval(refreshLive, 15000);
}

const toPersianNumber = (value) => String(value).replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[digit]);

const updateCountdowns = () => {
    const now = Date.now();
    document.querySelectorAll('[data-countdown]').forEach((box) => {
        const startsAt = box.dataset.startsAt;
        if (!startsAt) {
            box.textContent = 'زمان نامشخص';
            return;
        }

        const target = new Date(startsAt).getTime();
        if (Number.isNaN(target)) {
            box.textContent = 'زمان نامشخص';
            return;
        }

        const diff = target - now;
        if (diff <= 0) {
            if (box.dataset.mode === 'compact') {
                box.textContent = 'شروع شده یا پایان یافته';
            } else {
                box.innerHTML = '<div class="countdown-item"><strong>شروع</strong><span>بازی آغاز شده</span></div>';
            }
            return;
        }

        const totalSeconds = Math.floor(diff / 1000);
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        if (box.dataset.mode === 'compact') {
            box.textContent = `${toPersianNumber(days)} روز، ${toPersianNumber(hours)} ساعت، ${toPersianNumber(minutes)} دقیقه، ${toPersianNumber(seconds)} ثانیه`;
            return;
        }

        box.innerHTML = `
            <div class="countdown-item"><strong>${toPersianNumber(days)}</strong><span>روز</span></div>
            <div class="countdown-item"><strong>${toPersianNumber(hours)}</strong><span>ساعت</span></div>
            <div class="countdown-item"><strong>${toPersianNumber(minutes)}</strong><span>دقیقه</span></div>
            <div class="countdown-item"><strong>${toPersianNumber(seconds)}</strong><span>ثانیه</span></div>
        `;
    });
};

updateCountdowns();
setInterval(updateCountdowns, 1000);
