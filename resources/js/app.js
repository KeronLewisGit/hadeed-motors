import Alpine from 'alpinejs';

document.documentElement.classList.add('js'); // also set in <head>; kept for safety

/* Theme toggle. The initial theme is set by an inline script in <head> to avoid a flash. */
window.toggleTheme = () => {
    const root = document.documentElement;
    const current = root.dataset.theme || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    const next = current === 'dark' ? 'light' : 'dark';
    root.dataset.theme = next;
    try { localStorage.setItem('theme', next); } catch (e) { /* storage blocked: the choice lasts this page only */ }
};

/* Scroll reveal */
const reveal = () => {
    const items = document.querySelectorAll('[data-reveal]');
    if (!('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px' });
    items.forEach((el) => io.observe(el));
};

/*
 * Loan estimator. Same formula as App\Support\LoanEstimator (PHP), so the live figure
 * and the JS-free form result always agree. The rate comes from the server (admin setting).
 */
export const monthlyPayment = (principal, annualRate, months) => {
    if (principal <= 0 || months <= 0) return 0;
    const r = annualRate / 100 / 12;
    if (r <= 0) return Math.round((principal / months) * 100) / 100;
    return Math.round(((principal * r) / (1 - Math.pow(1 + r, -months))) * 100) / 100;
};

Alpine.data('loanEstimator', (config) => ({
    rate: Number(config.rate) || 0,
    price: config.price ?? '',
    deposit: config.deposit ?? '',
    term: Number(config.term) || 60,
    clean(v) { return Math.max(0, Number(String(v).replace(/[^0-9.]/g, '')) || 0); },
    get principal() { return Math.max(0, this.clean(this.price) - Math.min(this.clean(this.deposit), this.clean(this.price))); },
    get monthly() { return monthlyPayment(this.principal, this.rate, this.term); },
    get ready() { return this.clean(this.price) > 0 && this.rate > 0; },
    money(n, cents = false) { return 'EC$' + Number(n).toLocaleString('en-US', { minimumFractionDigits: cents ? 2 : 0, maximumFractionDigits: cents ? 2 : 0 }); },
}));

/* Test-drive times: only the times still free on the chosen day. Without JS every time is listed and the server checks. */
Alpine.data('slotPicker', (config) => ({
    grid: config.grid ?? {},
    date: config.date ?? '',
    time: config.time ?? '',
    get times() { return this.grid[this.date] ?? []; },
    init() {
        this.$watch('date', () => { if (!this.times.includes(this.time)) this.time = ''; });
    },
}));

/* Vehicle gallery: arrow keys and swipe-free thumbnails. */
Alpine.data('gallery', (count) => ({
    i: 0,
    count,
    go(n) { this.i = (n + this.count) % this.count; },
    key(e) {
        if (e.key === 'ArrowRight') { this.go(this.i + 1); e.preventDefault(); }
        if (e.key === 'ArrowLeft') { this.go(this.i - 1); e.preventDefault(); }
    },
}));

/* Submit buttons: one press only, with a visible "Sending…" state. */
Alpine.data('leadForm', () => ({
    submitting: false,
    submit(e) {
        if (!e.target.checkValidity()) return;
        this.submitting = true;
    },
}));

/* Column chart hover: one tooltip that follows the focused or hovered column. */
Alpine.data('columnChart', () => ({
    tip: null,
    show(el) {
        const box = el.getBoundingClientRect();
        const host = this.$root.getBoundingClientRect();
        this.tip = { label: el.dataset.label, value: el.dataset.value, x: box.left - host.left + box.width / 2, y: box.top - host.top };
    },
    hide() { this.tip = null; },
}));

/* Dashboard chrome: density, mobile nav, "/" to search. */
Alpine.data('adminShell', () => ({
    nav: false,
    density: (() => { try { return localStorage.getItem('density') || 'comfortable'; } catch (e) { return 'comfortable'; } })(),
    init() {
        this.$el.dataset.density = this.density;
        window.addEventListener('keydown', (e) => {
            const typing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);
            if (e.key === '/' && !typing) {
                const search = document.querySelector('[data-search]');
                if (search) { e.preventDefault(); search.focus(); }
            }
        });
    },
    toggleDensity() {
        this.density = this.density === 'compact' ? 'comfortable' : 'compact';
        this.$el.dataset.density = this.density;
        try { localStorage.setItem('density', this.density); } catch (e) { /* ignore */ }
    },
}));

window.Alpine = Alpine;
Alpine.start();
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', reveal);
else reveal();
