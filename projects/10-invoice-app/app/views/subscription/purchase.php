<div class="dash-topbar">
    <h1>خرید اشتراک <?= e($plan['name']) ?></h1>
    <a href="<?= APP_URL ?>/subscription" class="btn btn-outline">بازگشت</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width:520px; margin-bottom:20px;">
    <div style="font-size:13px; color:var(--ink-soft); margin-bottom:6px;">مبلغ قابل پرداخت</div>
    <div class="num" id="display-price" style="font-size:28px; font-weight:800; color:var(--navy-deep); margin-bottom:18px;">
        <?= formatNumber($price) ?> تومان
    </div>

    <div class="card-info-box">
        <div class="row"><span>شماره کارت</span><b class="num" dir="ltr"><?= e(setting('card_number')) ?></b></div>
        <div class="row"><span>به نام</span><b><?= e(setting('card_holder_name')) ?></b></div>
    </div>
    <p style="font-size:12.5px; color:var(--ink-soft); margin-top:12px; line-height:2;">
        مبلغ بالا را به شماره کارت فوق واریز کرده و سپس تصویر رسید پرداخت را در فرم زیر آپلود کنید.
        پس از ثبت درخواست، اشتراک شما پس از بررسی توسط مدیر سیستم فعال می‌شود.
    </p>
</div>

<div class="card" style="max-width:520px;">
    <form method="POST" action="<?= APP_URL ?>/subscription/purchase" enctype="multipart/form-data" id="purchase-form">
        <?= Auth::csrfField() ?>
        <input type="hidden" name="plan" value="<?= (int) $plan['id'] ?>">

        <div class="form-group">
            <label class="form-label">کد تخفیف (اختیاری)</label>
            <div style="display:flex; gap:8px;">
                <input type="text" name="discount_code" id="discount-code-input" class="form-control" style="flex:1;" dir="ltr">
                <button type="button" id="apply-discount-btn" class="btn btn-outline btn-sm" data-no-loading="1">اعمال کد</button>
            </div>
            <div id="discount-message" style="font-size:12.5px; margin-top:6px;"></div>
        </div>

        <div class="form-group">
            <label class="form-label">تصویر رسید پرداخت</label>
            <input type="file" name="receipt" id="receipt-input" class="form-control" accept="image/jpeg,image/png,image/webp" required>
        </div>

        <img id="receipt-preview" style="display:none; max-width:100%; border-radius:10px; border:1px solid var(--border); margin-bottom:16px;">

        <button type="submit" class="btn btn-success btn-block">ثبت رسید پرداخت</button>
    </form>
</div>

<script>
document.getElementById('receipt-input').addEventListener('change', function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById('receipt-preview');
    if (!file) { preview.style.display = 'none'; return; }
    const reader = new FileReader();
    reader.onload = ev => { preview.src = ev.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(file);
});

document.getElementById('apply-discount-btn').addEventListener('click', function () {
    const code = document.getElementById('discount-code-input').value.trim();
    const msgBox = document.getElementById('discount-message');
    if (!code) { msgBox.textContent = ''; return; }

    msgBox.style.color = 'var(--ink-soft)';
    msgBox.textContent = 'در حال بررسی...';

    const params = new URLSearchParams({ plan: '<?= (int) $plan['id'] ?>', code: code });
    fetch('<?= APP_URL ?>/subscription/check-discount?' + params.toString())
        .then(r => r.json())
        .then(data => {
            if (data.valid) {
                msgBox.style.color = 'var(--ledger-green-deep)';
                msgBox.textContent = data.message;
                document.getElementById('display-price').textContent =
                    Number(data.final_price).toLocaleString('fa-IR') + ' تومان';
            } else {
                msgBox.style.color = 'var(--color-danger)';
                msgBox.textContent = data.message;
                document.getElementById('display-price').textContent = '<?= formatNumber($price) ?> تومان';
            }
        })
        .catch(() => { msgBox.style.color = 'var(--color-danger)'; msgBox.textContent = 'خطا در بررسی کد.'; });
});
</script>
