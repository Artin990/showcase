<div class="dash-topbar">
    <h1>فاکتورها <span class="count-pill"><?= formatNumber($result['total']) ?></span></h1>
    <a href="<?= APP_URL ?>/invoices/create" class="btn btn-success">+ صدور فاکتور جدید</a>
</div>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = getFlash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>

<div class="card table-card">
    <?php if (empty($result['items'])): ?>
        <div class="empty-state">
            <div class="empty-icon"><i data-lucide="receipt"></i></div>
            <h3>هنوز فاکتوری صادر نکرده‌اید</h3>
            <p>اولین فاکتور خود را همین حالا صادر کنید.</p>
            <a href="<?= APP_URL ?>/invoices/create" class="btn btn-success">صدور اولین فاکتور</a>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>شماره فاکتور</th>
                    <th>مشتری</th>
                    <th>مبلغ کل (ریال)</th>
                    <th>تاریخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['items'] as $inv): ?>
                <tr>
                    <td class="num" dir="ltr" style="text-align:right;"><?= e($inv['invoice_number']) ?></td>
                    <td><?= e($inv['customer_name'] ?: '—') ?></td>
                    <td class="num"><?= formatNumber($inv['total_amount']) ?></td>
                    <td class="num"><?= e($inv['invoice_date_shamsi'] ?: toJalali($inv['created_at'])) ?></td>
                    <td class="actions">
                        <a href="<?= APP_URL ?>/invoices/view?id=<?= (int) $inv['id'] ?>" class="btn btn-outline btn-sm">مشاهده</a>
                        <a href="<?= APP_URL ?>/invoices/edit?id=<?= (int) $inv['id'] ?>" class="btn btn-outline btn-sm" <?= $hasQuotaLimit ? 'data-edit-warning="1"' : '' ?>>ویرایش</a>
                        <form method="POST" action="<?= APP_URL ?>/invoices/delete" style="display:inline"
                              onsubmit="return confirm('آیا از حذف این فاکتور مطمئن هستید؟');">
                            <?= Auth::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int) $inv['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if ($result['total_pages'] > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
            <a href="<?= APP_URL ?>/invoices?page=<?= $i ?>" class="<?= $i === $result['page'] ? 'active' : '' ?>"><?= formatNumber($i) ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
