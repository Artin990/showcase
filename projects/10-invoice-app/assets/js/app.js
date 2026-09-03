// ---------------- سوییچ تم روشن/تاریک (دستی) ----------------
// پیش‌فرض: پیروی از سیستم‌عامل (auto). اگر کاربر انتخاب کند، در localStorage ذخیره می‌شود.
(function () {
    const KEY = 'siteTheme';
    const root = document.documentElement;

    function applyTheme(theme) {
        if (theme === 'dark' || theme === 'light') {
            root.setAttribute('data-theme', theme);
        } else {
            root.removeAttribute('data-theme');
        }
    }

    function syncIcons() {
        const current = root.getAttribute('data-theme');
        const dark = current === 'dark';
        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            btn.innerHTML = dark
                ? '<i data-lucide="sun"></i>'
                : '<i data-lucide="moon-star"></i>';
            btn.title = dark ? 'حالت روشن' : 'حالت تاریک';
        });
        if (window.lucide) window.lucide.createIcons();
    }

    function safeGet(key) {
        try { return window.localStorage.getItem(key); } catch (e) { return null; }
    }
    function safeSet(key, val) {
        try { window.localStorage.setItem(key, val); } catch (e) {}
    }

    applyTheme(safeGet(KEY) || '');

    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            safeSet(KEY, next);
            applyTheme(next);
            syncIcons();
        });
    });

    // هم‌اهنگ‌سازی بین تب‌ها
    window.addEventListener('storage', function (e) {
        if (e.key === KEY) {
            applyTheme(safeGet(KEY) || '');
            syncIcons();
        }
    });

    // وقتی کاربر تم دستی انتخاب نکرده، آیکون با حالت سیستم هماهنگ می‌شود
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
        if (!safeGet(KEY)) syncIcons();
    });

    syncIcons();
})();

// ---------------- منوی موبایل (Sidebar Toggle) ----------------
(function () {
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    if (!toggleBtn || !sidebar) return;

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay?.classList.remove('visible');
    }

    toggleBtn.addEventListener('click', function () {
        sidebar.classList.toggle('open');
        overlay?.classList.toggle('visible');
    });

    // دکمه بستن داخل دراپر موبایل
    const closeBtn = document.getElementById('mobile-menu-close');
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);

    overlay?.addEventListener('click', closeSidebar);

    // بستن منو با کلیک روی هر لینک (برای تجربه بهتر در موبایل)
    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', closeSidebar);
    });
})();

// ---------------- ظاهر شدن ظریف عناصر هنگام اسکرول (Landing Page) ----------------
(function () {
    const revealEls = document.querySelectorAll('.reveal');
    if (!revealEls.length) return;

    if (!('IntersectionObserver' in window)) {
        revealEls.forEach(el => el.classList.add('revealed'));
        return;
    }

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    revealEls.forEach(el => observer.observe(el));
})();

// ---------------- آکاردئون سوالات متداول ----------------
(function () {
    document.querySelectorAll('.faq-item').forEach(function (item) {
        const question = item.querySelector('.faq-question');
        question?.addEventListener('click', function () {
            const wasOpen = item.classList.contains('open');
            item.parentElement.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
            if (!wasOpen) item.classList.add('open');
        });
    });
})();
// ---------------- ویرایش فاکتور (بدون کسر سهمیه) ----------------
// از نسخه جدید، ویرایش فاکتور سهمیه ماهانه مصرف نمی‌کند؛
// فقط برای فاکتورهای دارای سقف سهمیه، یک هشدار ساده نمایش داده می‌شود.
(function () {
    const links = document.querySelectorAll('[data-edit-warning]');
    if (!links.length) return;

    const STORAGE_KEY = 'hideInvoiceEditQuotaWarning';

    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
        <div class="modal-box">
            <h3><i data-lucide="receipt-text" style="color:var(--gold);"></i> ویرایش فاکتور</h3>
            <p>ویرایش این فاکتور سهمیه جدیدی از سهمیه ماهانه شما مصرف نمی‌کند. ادامه می‌دهید؟</p>
            <label class="modal-checkbox">
                <input type="checkbox" id="dont-show-edit-warning">
                <span>دیگر نمایش نده</span>
            </label>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline btn-sm" id="modal-cancel-btn">انصراف</button>
                <button type="button" class="btn btn-primary btn-sm" id="modal-continue-btn">ادامه</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
    if (window.lucide) window.lucide.createIcons();

    let pendingHref = null;

    function closeModal() {
        overlay.classList.remove('visible');
        pendingHref = null;
    }

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });
    overlay.querySelector('#modal-cancel-btn').addEventListener('click', closeModal);
    overlay.querySelector('#modal-continue-btn').addEventListener('click', function () {
        if (overlay.querySelector('#dont-show-edit-warning').checked) {
            localStorage.setItem(STORAGE_KEY, '1');
        }
        const href = pendingHref;
        closeModal();
        if (href) window.location.href = href;
    });

    links.forEach(function (link) {
        link.addEventListener('click', function (e) {
            if (localStorage.getItem(STORAGE_KEY) === '1') return;
            e.preventDefault();
            pendingHref = link.getAttribute('href');
            overlay.classList.add('visible');
        });
    });
})();

(function () {
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            // اگر فرم توسط confirm() یا اعتبارسنجی دیگری لغو شده باشد، کاری نکن
            if (e.defaultPrevented) return;

            const submitBtn = form.querySelector('button[type="submit"]');
            if (!submitBtn || submitBtn.dataset.noLoading) return;

            submitBtn.dataset.originalText = submitBtn.dataset.originalText || submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'در حال پردازش...';
        });
    });
})();
