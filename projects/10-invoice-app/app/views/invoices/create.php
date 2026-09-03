<?php
$isEdit = $mode === 'edit';
$formAction = $isEdit ? APP_URL . '/invoices/update' : APP_URL . '/invoices/store';
$dateVal = $old['invoice_date_shamsi'] ?? ($invoice['invoice_date_shamsi'] ?? todayJalali());
$useStoreChecked = ($old['use_store_name'] ?? '') === '1'
    || (!$isEdit && !empty($user['store_name']))
    || ($isEdit && !empty($invoice['seller_name']) && !empty($user['store_name']) && $invoice['seller_name'] === $user['store_name']);

$templates = [
    'classic'   => ['name' => 'کلاسیک',   'desc' => 'سبک رسمی و سنتی',         'colors' => ['#16324F', '#F3E6C9']],
    'modern'    => ['name' => 'مدرن',     'desc' => 'تمیز با رنگ سبز آبی',     'colors' => ['#0D9488', '#5EEAD4']],
    'minimal'   => ['name' => 'مینیمال',  'desc' => 'ساده، سیاه و سفید',       'colors' => ['#0F172A', '#E2E8F0']],
    'elegant'   => ['name' => 'شیک',      'desc' => 'لوکس با لمسه طلایی',      'colors' => ['#1E293B', '#F59E0B']],
    'corporate' => ['name' => 'شرکتی',    'desc' => 'رسمی با نشان سرمه‌ای',    'colors' => ['#0F172A', '#0D9488']],
];
$selectedTemplate = $old['template'] ?? ($invoice['template'] ?? 'classic');
$adText = setting('invoice_ad_text', '');
$siteName = setting('site_name', APP_NAME);
$nextNum = $nextNumber ?? '';

/* ---------- داده نمونه برای پیش‌نمایش قالب‌ها (گام دوم) ---------- */
$sampleRows = [
    ['نام محصول', 'گوشی موبایل', 2, 25000000, 0],
    ['قاب محافظ', 4, 350000, 100000],
    ['شارژر سریع ۲۵ وات', 1, 1200000, 0],
];
$sampleTotal = 52500000;
$sampleNumber = 'FA-2026-08-09-X7K2M';

function sampleRow($name, $qty, $price, $disc, $i, $total): string
{
    return '<tr>'
        . '<td class="num">' . formatNumber($i + 1) . '</td>'
        . '<td>' . e($name) . '</td>'
        . '<td class="num">' . formatNumber($qty) . '</td>'
        . '<td class="num">' . formatNumber($price) . '</td>'
        . '<td class="num">' . ($disc > 0 ? formatNumber($disc) : '—') . '</td>'
        . '<td class="num">' . formatNumber($total) . '</td>'
        . '</tr>';
}

function samplePaper(string $tpl): string
{
    $site = setting('site_name', 'فاکتورچی');
    $rows = '';
    $rows .= sampleRow('گوشی موبایل', 2, 25000000, 0, 0, 50000000);
    $rows .= sampleRow('قاب محافظ', 4, 350000, 100000, 1, 1300000);
    $rows .= sampleRow('شارژر سریع ۲۵ وات', 1, 1200000, 0, 2, 1200000);
    return '<div class="invoice-paper tpl-' . e($tpl) . '">'
        . '<div class="invoice-header">'
        .     '<div><div class="invoice-brand"><i data-lucide="receipt-text"></i> ' . e($site) . '</div>'
        .     '<div class="invoice-seller">فروشنده: فروشگاه نمونه</div></div>'
        .     '<div class="invoice-meta">'
        .         '<div class="invoice-title">فاکتور فروش</div>'
        .         '<div class="meta-row"><span>شماره فاکتور</span><b dir="ltr">FA-2026-08-09-X7K2M</b></div>'
        .         '<div class="meta-row"><span>تاریخ</span><b>۱۴۰۵/۰۵/۱۸</b></div>'
        .     '</div>'
        . '</div>'
        . '<div class="invoice-customer"><span>خریدار:</span> <b>مشتری نمونه</b></div>'
        . '<div class="invoice-body-pad">'
        .     '<table class="invoice-table">'
        .         '<thead><tr><th>#</th><th>نام محصول</th><th>تعداد</th><th>قیمت واحد (ریال)</th><th>تخفیف (ریال)</th><th>مبلغ (ریال)</th></tr></thead>'
        .         '<tbody>' . $rows . '</tbody>'
        .     '</table>'
        .     '<div class="invoice-total-row"><span>جمع کل فاکتور</span><b class="num">' . formatNumber(52500000) . ' ریال</b></div>'
        . '</div>'
        . '</div>';
}
?>
<div class="dash-topbar">
    <div>
        <h1><?= $isEdit ? 'ویرایش فاکتور' : 'ساخت فاکتور جدید' ?></h1>
        <p class="page-subtitle"><?= $isEdit ? 'تغییرات را اعمال کنید؛ شماره فاکتور ثابت می‌ماند.' : 'اطلاعات را وارد کنید و در گام بعد قالب را انتخاب کنید.' ?></p>
    </div>
    <a href="<?= APP_URL ?>/invoices" class="btn btn-outline"><i data-lucide="arrow-right"></i> بازگشت</a>
</div>

<?php if (!empty($quota)): ?>
    <?php $quotaPlanName = $features['plan_name'] ?? 'اشتراک'; ?>
    <div class="alert <?= $quota['remaining'] > 0 ? 'alert-success' : 'alert-error' ?> quota-alert">
        سهمیه ماه جاری پلن «<?= e($quotaPlanName) ?>»: <b><?= formatNumber($quota['remaining']) ?></b> از <?= formatNumber($quota['limit']) ?> باقی‌مانده.
        <a href="<?= APP_URL ?>/subscription" class="quota-upgrade-link">مدیریت اشتراک</a>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error cv-errors">
        <b>لطفاً موارد زیر را اصلاح کنید:</b>
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="cv-layout">
    <form method="POST" action="<?= $formAction ?>" id="invoice-form" novalidate>
        <?= Auth::csrfField() ?>
        <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int) $invoice['id'] ?>"><?php endif; ?>

        <!-- ============ گام ۱: اطلاعات ============ -->
        <div id="step-1">
            <!-- ۱) اطلاعات فاکتور -->
            <section class="cv-card">
                <header class="cv-card-head">
                    <span class="cv-step">۱</span>
                    <h2>اطلاعات فاکتور</h2>
                </header>
                <div class="cv-body">
                    <div class="cv-invoice-no">
                        <span class="cv-no-label">شماره فاکتور</span>
                        <b class="cv-no-value" id="fv-number" dir="ltr"><?= e($nextNum ?: '—') ?></b>
                        <?php if (!$isEdit): ?><span class="cv-no-auto">خودکار</span><?php endif; ?>
                    </div>
                    <div class="form-group" style="max-width:260px;">
                        <label class="form-label" for="invoice-date">تاریخ فاکتور (شمسی)</label>
                        <input type="text" id="invoice-date" name="invoice_date_shamsi" class="form-control input-ltr-fa" placeholder="مثال: ۱۴۰۵/۰۵/۱۸"
                               value="<?= e($dateVal) ?>">
                    </div>
                </div>
            </section>

            <!-- ۲) محصولات -->
            <section class="cv-card">
                <header class="cv-card-head">
                    <span class="cv-step">۲</span>
                    <h2>محصولات</h2>
                    <span class="cv-card-note">نام، تعداد و قیمت را وارد کنید</span>
                </header>
                <div class="cv-body">
                    <div class="cv-items-head">
                        <span class="ci-name">نام محصول</span>
                        <span class="ci-qty">تعداد</span>
                        <span class="ci-price">قیمت واحد</span>
                        <span class="ci-disc">تخفیف</span>
                        <span class="ci-total">مبلغ</span>
                        <span class="ci-del"></span>
                    </div>
                    <div id="line-items"></div>
                    <button type="button" id="add-row-btn" class="btn btn-ghost cv-add-btn">
                        <i data-lucide="plus"></i> افزودن محصول
                    </button>

                    <!-- جمع‌بندی فشرده در همین کارت -->
                    <div class="cv-totals-strip">
                        <div class="cv-totals-item">
                            <span>جمع محصولات</span>
                            <b class="num" id="sum-subtotal">۰</b>
                        </div>
                        <div class="cv-totals-item cv-totals-disc">
                            <span>تخفیف</span>
                            <b class="num" id="sum-discount">۰</b>
                        </div>
                        <div class="cv-totals-item cv-totals-final">
                            <span>مبلغ نهایی</span>
                            <b class="num" id="sum-total">۰ <small>ریال</small></b>
                        </div>
                    </div>
                    <div class="cv-submit-err" id="form-errors"></div>
                </div>
            </section>

            <!-- تنظیمات نمایش (جمع‌شده و ساده) -->
            <section class="cv-card">
                <header class="cv-card-head">
                    <span class="cv-step">۳</span>
                    <h2>تنظیمات نمایش</h2>
                </header>
                <div class="cv-body">
                    <div class="cv-settings-grid">
                        <?php $hasStore = !empty($user['store_name']); ?>
                        <label class="switch-toggle <?= $hasStore ? '' : 'switch-disabled' ?>">
                            <input type="checkbox" name="use_store_name" value="1" <?= $useStoreChecked ? 'checked' : '' ?> <?= $hasStore ? '' : 'disabled' ?>>
                            <span class="switch-track"><span class="switch-thumb"></span></span>
                            <span>نمایش نام فروشگاه<?= $hasStore ? ' («' . e($user['store_name']) . '»)' : '' ?></span>
                        </label>
                        <?php if (!$hasStore): ?>
                            <p class="cv-hint">نام فروشگاه/شرکتی ثبت نشده است. در <a href="<?= APP_URL ?>/profile">پروفایل</a> ذخیره کنید.</p>
                        <?php endif; ?>
                        <?php if (!empty($features['allow_hide_ad'])): ?>
                            <?php
                            $showAdChecked = array_key_exists('hide_ad', $old ?? [])
                                ? ($old['hide_ad'] !== '1')
                                : empty($invoice['hide_ad']);
                            ?>
                            <label class="switch-toggle">
                                <input type="checkbox" name="show_ad" id="set-ad" value="1" <?= $showAdChecked ? 'checked' : '' ?>>
                                <span class="switch-track"><span class="switch-thumb"></span></span>
                                <span>پیام پایانی در انتهای فاکتور</span>
                            </label>
                        <?php else: ?>
                            <label class="switch-toggle switch-disabled" title="در پلن رایگان، حذف پیام پایانی فعال نیست">
                                <input type="checkbox" id="set-ad" checked disabled>
                                <span class="switch-track"><span class="switch-thumb"></span></span>
                                <span>پیام پایانی در انتهای فاکتور <span class="cv-lock-badge">پلن رایگان</span></span>
                            </label>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <div class="cv-actions">
                <button type="button" class="btn btn-primary btn-lg" id="go-template-btn">
                    <i data-lucide="arrow-left"></i> ادامه و انتخاب قالب
                </button>
                <a href="<?= APP_URL ?>/invoices" class="btn btn-outline">انصراف</a>
            </div>
        </div>

        <!-- ============ گام ۲: انتخاب قالب ============ -->
        <div id="step-2" style="display:none;">
            <div class="cv-stepper">
                <span class="cv-step-dot done"><i data-lucide="check"></i> اطلاعات</span>
                <span class="cv-step-line"></span>
                <span class="cv-step-dot current">قالب</span>
            </div>

            <section class="cv-card">
                <header class="cv-card-head">
                    <span class="cv-step">۴</span>
                    <h2>قالب فاکتور را انتخاب کنید</h2>
                    <?php if (empty($features['allow_custom_templates'])): ?>
                        <span class="cv-lock-badge">پلن رایگان: فقط کلاسیک</span>
                    <?php endif; ?>
                </header>
                <div class="cv-body">
                    <div class="cv-tpl-cards">
                        <?php foreach ($templates as $key => $tpl):
                            $disabled = empty($features['allow_custom_templates']) && $key !== 'classic';
                            $checked = $selectedTemplate === $key;
                        ?>
                        <label class="tpl-card <?= $checked ? 'selected' : '' ?> <?= $disabled ? 'tpl-card-disabled' : '' ?>" data-tpl="<?= $key ?>">
                            <input type="radio" name="template" value="<?= $key ?>" <?= $checked ? 'checked' : '' ?> <?= $disabled ? 'disabled' : '' ?>>
                            <span class="tpl-mini tpl-mini-<?= $key ?>">
                                <span class="tpl-mini-bar"></span><span class="tpl-mini-lines"></span>
                            </span>
                            <span class="tpl-card-name"><?= $tpl['name'] ?></span>
                            <span class="tpl-card-desc"><?= $tpl['desc'] ?></span>
                            <span class="tpl-card-check"><i data-lucide="check"></i></span>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <p class="cv-hint cv-sample-hint">
                        <i data-lucide="mouse-pointer-click" style="width:14px;height:14px;vertical-align:-2px;"></i>
                        روی هر قالب کلیک کنید تا نمونه فاکتور آن را ببینید.
                    </p>

                    <div class="cv-sample-pane">
                        <?php foreach (array_keys($templates) as $key): ?>
                            <div class="tpl-sample" data-tpl="<?= $key ?>" style="<?= $key === $selectedTemplate ? '' : 'display:none;' ?>">
                                <?= samplePaper($key) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <div class="cv-actions">
                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                    <i data-lucide="badge-check"></i> <?= $isEdit ? 'ذخیره تغییرات' : 'ساخت فاکتور' ?>
                </button>
                <button type="button" class="btn btn-outline" id="back-btn"><i data-lucide="arrow-right"></i> بازگشت به اطلاعات</button>
            </div>
        </div>
    </form>
</div>

<script type="application/json" id="existing-items-data"><?= json_encode($items, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>
<script>
(function () {
    'use strict';
    var container = document.getElementById('line-items');
    var form = document.getElementById('invoice-form');
    var existingItems = JSON.parse(document.getElementById('existing-items-data').textContent || '[]');
    var siteName = <?= json_encode($siteName, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var adText = <?= json_encode($adText, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var faDigits = function (s) { return String(s).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[+d]; }); };
    var num = new Intl.NumberFormat('fa-IR');

    /* ---------- ردیف‌های محصول ---------- */
    function rowTotal(row) {
        var qty = Math.max(0, parseInt(row.querySelector('.qty-input').value || '0', 10));
        var price = Math.max(0, Number(row.querySelector('.price-input').value || 0));
        var disc = Math.max(0, Number(row.querySelector('.discount-input').value || 0));
        return { qty: qty, price: price, disc: disc, total: Math.max(0, qty * price - disc) };
    }
    function recalcRow(row) {
        var t = rowTotal(row);
        row.querySelector('.row-total').textContent = t.total > 0 ? num.format(t.total) : '۰';
        row.dataset.total = String(t.total);
        markRow(row, t);
        recalcAll();
    }
    function markRow(row, t) {
        var bad = false;
        var name = row.querySelector('.name-input');
        if (name.value.trim() !== '' ) {
            if (t.qty < 1) { bad = true; }
            if (t.price <= 0) { bad = true; }
            if (t.disc > t.qty * t.price) { bad = true; }
        }
        row.classList.toggle('row-invalid', bad);
    }
    function addRow(prefill) {
        prefill = prefill || {};
        var row = document.createElement('div');
        row.className = 'line-row';
        row.dataset.total = '0';
        row.innerHTML =
            '<div class="ci-name"><input type="text" name="product_name[]" class="form-control name-input" placeholder="نام محصول/خدمت" value="' + (prefill.product_name ? String(prefill.product_name).replace(/"/g, '&quot;') : '') + '"></div>' +
            '<div class="ci-qty"><input type="tel" inputmode="numeric" name="quantity[]" class="form-control qty-input" min="1" value="' + (prefill.quantity || 1) + '" placeholder="تعداد"></div>' +
            '<div class="ci-price"><input type="tel" inputmode="numeric" name="unit_price[]" class="form-control price-input" min="0" value="' + (prefill.unit_price || '') + '" placeholder="قیمت واحد (ریال)"></div>' +
            '<div class="ci-disc"><input type="tel" inputmode="numeric" name="discount[]" class="form-control discount-input" min="0" value="' + (prefill.discount || '') + '" placeholder="تخفیف (اختیاری)"></div>' +
            '<div class="ci-total"><span class="row-total num">۰</span></div>' +
            '<div class="ci-del"><button type="button" class="btn-remove-row" title="حذف ردیف"><i data-lucide="trash-2"></i></button></div>';
        container.appendChild(row);
        row.querySelectorAll('.qty-input, .price-input, .discount-input, .name-input').forEach(function (el) {
            el.addEventListener('input', function () { recalcRow(row); });
        });
        row.querySelector('.btn-remove-row').addEventListener('click', function () {
            row.remove(); recalcAll(); if (window.lucide) lucide.createIcons();
        });
        recalcRow(row);
    }

    function totals() {
        var sub = 0, disc = 0, fin = 0;
        container.querySelectorAll('.line-row').forEach(function (r) {
            var t = rowTotal(r);
            sub += t.qty * t.price; disc += t.disc; fin += t.total;
        });
        return { sub: sub, disc: disc, fin: fin };
    }
    function recalcAll() {
        var t = totals();
        document.getElementById('sum-subtotal').textContent = num.format(t.sub);
        document.getElementById('sum-discount').textContent = num.format(t.disc);
        document.getElementById('sum-total').textContent = num.format(t.fin);
    }

    /* ---------- اعتبارسنجی سمت کلاینت ---------- */
    function validate() {
        var errs = [];
        var dateVal = document.getElementById('invoice-date').value.trim();
        if (!dateVal) errs.push('تاریخ فاکتور را وارد کنید.');
        var any = false;
        container.querySelectorAll('.line-row').forEach(function (r) {
            var t = rowTotal(r);
            var name = r.querySelector('.name-input').value.trim();
            if (name === '' && t.qty === 0 && t.price === 0) return;
            any = true;
            if (name === '') errs.push('نام محصول را برای همه ردیف‌ها وارد کنید.');
            else if (t.qty < 1) errs.push('تعداد باید حداقل ۱ باشد.');
            else if (t.price <= 0) errs.push('قیمت واحد باید بزرگ‌تر از صفر باشد.');
            else if (t.disc > t.qty * t.price) errs.push('تخفیف نمی‌تواند بیشتر از مبلغ محصول باشد.');
        });
        if (!any) errs.push('حداقل یک محصول با نام، تعداد و قیمت وارد کنید.');
        var box = document.getElementById('form-errors');
        box.innerHTML = errs.map(function (e) { return '<div>' + esc(e) + '</div>'; }).join('');
        box.style.display = errs.length ? '' : 'none';
        return errs.length === 0;
    }
    function esc(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

    /* ---------- گام‌ها ---------- */
    var step1 = document.getElementById('step-1');
    var step2 = document.getElementById('step-2');

    document.getElementById('go-template-btn').addEventListener('click', function () {
        if (!validate()) {
            var target = document.getElementById('form-errors');
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        step1.style.display = 'none';
        step2.style.display = '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    document.getElementById('back-btn').addEventListener('click', function () {
        step2.style.display = 'none';
        step1.style.display = '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    /* ---------- انتخاب قالب + نمایش نمونه ---------- */
    document.querySelectorAll('.tpl-card').forEach(function (card) {
        card.addEventListener('click', function () {
            var radio = card.querySelector('input[name="template"]');
            if (radio.disabled) return;
            radio.checked = true;
            document.querySelectorAll('.tpl-card').forEach(function (c) { c.classList.remove('selected'); });
            card.classList.add('selected');
            var t = radio.value;
            document.querySelectorAll('.tpl-sample').forEach(function (s) {
                s.style.display = (s.dataset.tpl === t) ? '' : 'none';
            });
        });
    });

    /* ---------- رویدادها ---------- */
    document.getElementById('add-row-btn').addEventListener('click', function () { addRow(); if (window.lucide) lucide.createIcons(); });

    if (existingItems.length > 0) { existingItems.forEach(addRow); } else { addRow(); }

    /* اعتبارسنجی پیش از ارسال — فقط اگر خطا باشد جلوی ارسال گرفته می‌شود */
    form.addEventListener('submit', function (e) {
        if (!validate()) {
            e.preventDefault();
            var target = document.getElementById('form-errors');
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    document.getElementById('invoice-date').addEventListener('input', recalcAll);

    // دکمه ساخت — حالت در حال ثبت
    form.addEventListener('submit', function () {
        var btn = document.getElementById('submit-btn');
        if (btn.disabled) return;
        btn.disabled = true;
        btn.classList.add('btn-loading');
        btn.dataset.loading = '<?= $isEdit ? 'در حال ذخیره...' : 'در حال ساخت فاکتور...' ?>';
    });

    recalcAll();
})();
</script>
