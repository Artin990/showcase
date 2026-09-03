<div class="dash-topbar">
    <h1><?= e($user['name']) ?></h1>
    <a href="<?= APP_URL ?>/admin/users" class="btn btn-outline">بازگشت به لیست</a>
</div>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = getFlash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>

<div style="display:flex; gap:20px; flex-wrap:wrap;">
    <div class="card" style="flex:1; min-width:300px;">
        <h3 style="font-size:15px; margin-bottom:16px;">ویرایش اطلاعات کاربر</h3>
        <form method="POST" action="<?= APP_URL ?>/admin/users/update-info">
            <?= Auth::csrfField() ?>
            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">

            <div class="form-group">
                <label class="form-label">نام</label>
                <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">ایمیل</label>
                <input type="email" name="email" class="form-control" dir="ltr" value="<?= e($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">موبایل</label>
                <input type="text" name="phone" class="form-control" dir="ltr" value="<?= e($user['phone']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">نام فروشگاه/شرکت</label>
                <input type="text" name="store_name" class="form-control" value="<?= e($user['store_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">نوع فعالیت</label>
                <select name="business_type" class="form-control">
                    <option value="shop" <?= ($user['business_type'] ?? '') === 'shop' ? 'selected' : '' ?>>فروشگاه</option>
                    <option value="company" <?= ($user['business_type'] ?? '') === 'company' ? 'selected' : '' ?>>شرکت</option>
                </select>
            </div>
            <div class="info-row" style="margin-top:6px;"><span>تاریخ عضویت</span><b><?= e(toJalali($user['created_at'])) ?></b></div>

            <button type="submit" class="btn btn-primary" style="margin-top:10px;">ذخیره اطلاعات</button>
        </form>

        <form method="POST" action="<?= APP_URL ?>/admin/users/toggle-active" style="margin-top:16px; padding-top:16px; border-top:1px dashed var(--border);">
            <?= Auth::csrfField() ?>
            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
            وضعیت حساب: <?= $user['is_active'] ? '<span class="badge-active">فعال</span>' : '<span class="badge-inactive">غیرفعال</span>' ?>
            <button type="submit" class="btn <?= $user['is_active'] ? 'btn-danger' : 'btn-success' ?> btn-sm" style="margin-top:10px; display:block;">
                <?= $user['is_active'] ? 'غیرفعال کردن کاربر' : 'فعال کردن کاربر' ?>
            </button>
        </form>
    </div>

    <div class="card" style="flex:1; min-width:300px;">
        <h3 style="font-size:15px; margin-bottom:16px;">مدیریت اشتراک</h3>
        <p style="font-size:13px; color:var(--ink-soft); margin-bottom:16px;">
            وضعیت فعلی:
            <?php if (!empty($sub['plan_is_free'])): ?>
                <span class="badge-plan-free">رایگان</span>
            <?php else: ?>
                <span class="badge-plan-paid"><?= e($sub['plan_name']) ?> — <?= $sub['status'] === 'active' ? 'فعال' : 'منقضی' ?></span>
                <?php if ($sub['end_date']): ?> تا <?= e(toJalali($sub['end_date'])) ?><?php endif; ?>
            <?php endif; ?>
        </p>

        <form method="POST" action="<?= APP_URL ?>/admin/users/update-subscription" id="sub-form">
            <?= Auth::csrfField() ?>
            <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">

            <div class="form-group">
                <label class="form-label">پلن اشتراک</label>
                <select name="plan_id" id="plan-select" class="form-control">
                    <?php foreach ($plans as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" data-months="<?= (int) $p['duration_months'] ?>"
                                <?= (int) ($sub['plan_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>>
                            <?= e($p['name']) ?><?= $p['is_free'] ? '' : ' (' . formatNumber($p['price']) . ' تومان)' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">وضعیت</label>
                <select name="status" class="form-control">
                    <option value="active" <?= $sub['status'] === 'active' ? 'selected' : '' ?>>فعال</option>
                    <option value="expired" <?= $sub['status'] === 'expired' ? 'selected' : '' ?>>منقضی</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">تاریخ پایان اشتراک (شمسی)</label>
                <input type="text" name="end_date_shamsi" id="end-date-input" class="form-control" placeholder="مثال: ۱۴۰۴/۰۶/۱۰"
                       value="<?= e($sub['end_date'] ? toJalali($sub['end_date']) : '') ?>">
                <p style="font-size:11.5px; color:var(--ink-soft); margin-top:5px;">با انتخاب پلن، این فیلد خودکار محاسبه می‌شود؛ در صورت نیاز می‌توانید دستی تغییرش دهید.</p>
            </div>

            <button type="submit" class="btn btn-primary">ذخیره تغییرات اشتراک</button>
        </form>
    </div>
</div>

<?php if (!empty($receipts)): ?>
<div class="card table-card" style="margin-top:20px;">
    <h3 style="font-size:15px; padding:20px 20px 0;">رسیدهای پرداخت این کاربر</h3>
    <table class="table">
        <thead><tr><th>تصویر</th><th>پلن</th><th>کد تخفیف</th><th>تاریخ</th><th>وضعیت</th></tr></thead>
        <tbody>
        <?php foreach ($receipts as $r): ?>
            <tr>
                <td><a href="<?= APP_URL ?>/uploads/receipts/<?= e($r['receipt_image']) ?>" target="_blank"><img class="receipt-thumb" src="<?= APP_URL ?>/uploads/receipts/<?= e($r['receipt_image']) ?>"></a></td>
                <td><?= e($r['plan_name'] ?? '—') ?></td>
                <td dir="ltr" style="text-align:right;"><?= $r['discount_code'] ? e($r['discount_code']) : '—' ?></td>
                <td class="num"><?= e(toJalali($r['created_at'])) ?></td>
                <td>
                    <?php if ($r['status'] === 'pending'): ?><span class="status-tag status-pending">در انتظار</span>
                    <?php elseif ($r['status'] === 'approved'): ?><span class="status-tag status-approved">تایید شده</span>
                    <?php else: ?><span class="status-tag status-rejected">رد شده</span><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="card table-card" style="margin-top:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; padding:20px 20px 0;">
        <h3 style="font-size:15px;">فاکتورهای این کاربر <span class="count-pill"><?= formatNumber($invoiceResult['total']) ?></span></h3>
    </div>

    <?php if (empty($invoiceResult['items'])): ?>
        <div class="empty-state"><div class="empty-icon"><i data-lucide="receipt-text"></i></div><h3>این کاربر هنوز فاکتوری صادر نکرده است</h3></div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>شماره فاکتور</th><th>خریدار</th><th>مبلغ (ریال)</th><th>تاریخ</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($invoiceResult['items'] as $inv): ?>
                <tr>
                    <td dir="ltr" style="text-align:right;"><?= e($inv['invoice_number']) ?></td>
                    <td><?= e($inv['customer_name'] ?: '—') ?></td>
                    <td class="num"><?= formatNumber($inv['total_amount']) ?></td>
                    <td class="num"><?= e($inv['invoice_date_shamsi'] ?: toJalali($inv['created_at'])) ?></td>
                    <td class="actions invoice-action-cell">
                        <a href="<?= APP_URL ?>/admin/invoices/view?id=<?= (int) $inv['id'] ?>" class="btn btn-outline btn-sm" title="مشاهده"><i data-lucide="eye"></i></a>
                        <a href="<?= APP_URL ?>/admin/invoices/edit?id=<?= (int) $inv['id'] ?>" class="btn btn-outline btn-sm" title="ویرایش"><i data-lucide="square-pen"></i></a>
                        <a href="<?= APP_URL ?>/admin/invoices/view?id=<?= (int) $inv['id'] ?>&action=pdf" class="btn btn-outline btn-sm" title="دانلود PDF"><i data-lucide="file-text"></i></a>
                        <a href="<?= APP_URL ?>/admin/invoices/export-excel?id=<?= (int) $inv['id'] ?>" class="btn btn-outline btn-sm" title="دانلود Excel"><i data-lucide="sheet"></i></a>
                        <a href="<?= APP_URL ?>/admin/invoices/view?id=<?= (int) $inv['id'] ?>&action=png" class="btn btn-outline btn-sm" title="دانلود تصویر"><i data-lucide="file-image"></i></a>
                        <form method="POST" action="<?= APP_URL ?>/admin/invoices/delete" style="display:inline"
                              onsubmit="return confirm('این فاکتور حذف شود؟');">
                            <?= Auth::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int) $inv['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="حذف"><i data-lucide="trash-2"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if ($invoiceResult['total_pages'] > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $invoiceResult['total_pages']; $i++): ?>
            <a href="<?= APP_URL ?>/admin/users/view?id=<?= (int) $user['id'] ?>&ipage=<?= $i ?>" class="<?= $i === $invoiceResult['page'] ? 'active' : '' ?>"><?= formatNumber($i) ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<script>
(function () {
    const planSelect = document.getElementById('plan-select');
    const endDateInput = document.getElementById('end-date-input');
    if (!planSelect || !endDateInput) return;

    planSelect.addEventListener('change', function () {
        const months = parseInt(this.selectedOptions[0].dataset.months || '0', 10);
        if (months <= 0) return; // پلن رایگان - تاریخ رو دست‌نخورده می‌ذاریم

        fetch('<?= APP_URL ?>/admin/users/calc-end-date?months=' + months + '&user_id=<?= (int) $user['id'] ?>')
                    .then(r => r.json())
            .then(data => { if (data.date) endDateInput.value = data.date; })
            .catch(() => {});
    });
})();
</script>
