<div class="dash-topbar">
    <h1>ویرایش فاکتور <span dir="ltr"><?= e($invoice['invoice_number']) ?></span></h1>
    <a href="<?= APP_URL ?>/admin/invoices/view?id=<?= (int) $invoice['id'] ?>" class="btn btn-outline">بازگشت</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card" style="max-width:860px;">
    <p style="font-size:13px; color:var(--ink-soft); margin-bottom:16px;">صاحب فاکتور: <b style="color:var(--ink);"><?= e($invoice['owner_name']) ?></b></p>

    <form method="POST" action="<?= APP_URL ?>/admin/invoices/update" id="invoice-form">
        <?= Auth::csrfField() ?>
        <input type="hidden" name="id" value="<?= (int) $invoice['id'] ?>">

        <div style="display:flex; gap:16px; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:200px;">
                <label class="form-label">تاریخ فاکتور (شمسی)</label>
                <input type="text" name="invoice_date_shamsi" class="form-control" value="<?= e($invoice['invoice_date_shamsi'] ?? '') ?>">
            </div>
            <div class="form-group" style="flex:1; min-width:200px;">
                <label class="form-label">نام فروشنده (اختیاری)</label>
                <input type="text" name="seller_name" class="form-control" value="<?= e($invoice['seller_name'] ?? '') ?>">
            </div>
            <div class="form-group" style="flex:1; min-width:200px;">
                <label class="form-label">نام خریدار (اختیاری)</label>
                <input type="text" name="customer_name" class="form-control" value="<?= e($invoice['customer_name'] ?? '') ?>">
            </div>
        </div>

        <div id="line-items"></div>
        <button type="button" id="add-row-btn" class="btn btn-outline btn-sm" style="margin-top:8px;">+ افزودن ردیف محصول</button>

        <div class="invoice-total-box">جمع کل: <span id="grand-total" class="num">۰</span> ریال</div>

        <button type="submit" class="btn btn-primary" style="margin-top:18px;">ذخیره تغییرات</button>
    </form>
</div>

<script type="application/json" id="existing-items-data"><?= json_encode($items, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>
<script>
(function () {
    const container = document.getElementById('line-items');
    const existingItems = JSON.parse(document.getElementById('existing-items-data').textContent || '[]');

    function toFa(num) { return Number(num).toLocaleString('fa-IR'); }

    function recalcRow(row) {
        const price = Number(row.querySelector('.price-input').value || 0);
        const qty = Math.max(0, parseInt(row.querySelector('.qty-input').value || 0, 10));
        const discount = Math.max(0, Number(row.querySelector('.discount-input').value || 0));
        const rowTotal = Math.max(0, (price * qty) - discount);
        row.querySelector('.row-total').textContent = toFa(rowTotal) + ' ریال';
        row.dataset.total = rowTotal;
        recalcGrandTotal();
    }
    function recalcGrandTotal() {
        let sum = 0;
        container.querySelectorAll('.line-row').forEach(r => sum += Number(r.dataset.total || 0));
        document.getElementById('grand-total').textContent = toFa(sum);
    }
    function addRow(prefill) {
        prefill = prefill || {};
        const row = document.createElement('div');
        row.className = 'line-row line-row-manual';
        row.dataset.total = '0';
        row.innerHTML = `
            <input type="text" name="product_name[]" class="form-control name-input" placeholder="نام محصول/خدمت" value="${prefill.product_name ? String(prefill.product_name).replace(/"/g,'&quot;') : ''}">
            <input type="number" name="quantity[]" class="form-control qty-input" min="1" value="${prefill.quantity || 1}" placeholder="تعداد">
            <input type="number" name="unit_price[]" class="form-control price-input" min="0" value="${prefill.unit_price || ''}" placeholder="قیمت واحد">
            <input type="number" name="discount[]" class="form-control discount-input" min="0" value="${prefill.discount || ''}" placeholder="تخفیف (اختیاری)">
            <span class="row-total num">۰ ریال</span>
            <button type="button" class="btn btn-outline btn-sm remove-row-btn">حذف</button>
        `;
        container.appendChild(row);
        row.querySelectorAll('.qty-input, .price-input, .discount-input').forEach(i => i.addEventListener('input', () => recalcRow(row)));
        row.querySelector('.remove-row-btn').addEventListener('click', () => { row.remove(); recalcGrandTotal(); });
        recalcRow(row);
    }
    document.getElementById('add-row-btn').addEventListener('click', () => addRow());
    if (existingItems.length > 0) { existingItems.forEach(item => addRow(item)); } else { addRow(); }
})();
</script>
