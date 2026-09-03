<div class="dash-topbar">
    <h1>خوش آمدید، <?= e($userName) ?> <i data-lucide="thumbs-up"></i></h1>
</div>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>

<div class="dash-stats">
    <div class="card dash-stat">
        <div class="dash-stat-label">تعداد فاکتورهای صادرشده</div>
        <div class="num dash-stat-value"><?= formatNumber($invoiceCount) ?></div>
        <a href="<?= APP_URL ?>/invoices" class="btn btn-outline btn-sm">مشاهده فاکتورها</a>
    </div>

    <div class="card dash-stat dash-stat-action">
        <div class="dash-stat-label">صدور فاکتور جدید</div>
        <a href="<?= APP_URL ?>/invoices/create" class="btn btn-success">+ فاکتور جدید</a>
    </div>

    <?php if ($quota === null): ?>
        <div class="card dash-stat">
            <div class="dash-stat-label">
                <?php if ($isPaid): ?><i data-lucide="badge-check" class="dash-gold"></i><?php endif; ?>
                اشتراک شما: <?= e($planName) ?>
            </div>
            <div class="dash-stat-value">صدور نامحدود فاکتور</div>
            <div class="dash-stat-hint">بدون محدودیت ماهانه</div>
        </div>
    <?php else: ?>
        <div class="card dash-stat quota-card">
            <div class="dash-stat-label">سهمیه <?= e($planName) ?> این ماه</div>
            <div class="dash-quota-row">
                <span class="num dash-stat-value"><?= formatNumber($quota['remaining']) ?></span>
                <span class="dash-stat-hint">از <?= formatNumber($quota['limit']) ?> فاکتور باقی‌مانده</span>
            </div>
            <div class="quota-progress-track">
                <div class="quota-progress-fill" style="width: <?= $quota['limit'] > 0 ? min(100, ($quota['used'] / $quota['limit']) * 100) : 100 ?>%;"></div>
            </div>
            <?php if ($quota['remaining'] <= 0): ?>
                <p class="dash-stat-hint dash-quota-empty">سهمیه این ماه تمام شد. <a href="<?= APP_URL ?>/subscription" class="quota-upgrade-link">ارتقا دهید</a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($recentInvoices)): ?>
<div class="card">
    <div class="dash-card-head">
        <h3>آخرین فاکتورها</h3>
        <a href="<?= APP_URL ?>/invoices" class="btn btn-ghost btn-sm">همه فاکتورها</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>شماره فاکتور</th>
                    <th>مشتری</th>
                    <th>مبلغ (ریال)</th>
                    <th>تاریخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentInvoices as $inv): ?>
                <tr>
                    <td class="num"><?= e($inv['invoice_number']) ?></td>
                    <td><?= e($inv['customer_name'] ?: '—') ?></td>
                    <td class="num"><?= formatNumber($inv['total_amount']) ?></td>
                    <td><?= e($inv['created_at']) ?></td>
                    <td><a href="<?= APP_URL ?>/invoices/view?id=<?= (int) $inv['id'] ?>" class="btn btn-outline btn-sm">مشاهده</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card dash-help-card">
    <h3 style="margin-bottom:10px; font-size:16px;">نیاز به راهنمایی دارید؟</h3>
    <p style="color:var(--ink-soft); font-size:14px; line-height:2; margin-bottom:14px;">
        هر سوالی درباره سیستم یا اشتراک دارید، از طریق راه‌های زیر با ما در ارتباط باشید.
    </p>
    <a href="<?= APP_URL ?>/contact" class="btn btn-outline btn-sm">تماس با ما</a>
</div>