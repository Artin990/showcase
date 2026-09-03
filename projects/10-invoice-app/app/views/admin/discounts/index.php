<div class="dash-topbar">
    <h1>کدهای تخفیف <span class="count-pill"><?= formatNumber(count($codes)) ?></span></h1>
    <a href="<?= APP_URL ?>/admin/discounts/create" class="btn btn-success">+ کد تخفیف جدید</a>
</div>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = getFlash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>

<div class="card table-card">
    <?php if (empty($codes)): ?>
        <div class="empty-state"><div class="empty-icon"><i data-lucide="tag"></i></div><h3>هنوز کد تخفیفی ایجاد نکرده‌اید</h3></div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>کد</th><th>نوع</th><th>مقدار</th><th>سقف استفاده</th><th>مصرف‌شده</th><th>انقضا</th><th>وضعیت</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($codes as $c): ?>
                <tr>
                    <td dir="ltr" style="text-align:right; font-weight:700;"><?= e($c['code']) ?></td>
                    <td><?= $c['type'] === 'percent' ? 'درصدی' : 'مبلغ ثابت' ?></td>
                    <td class="num"><?= $c['type'] === 'percent' ? formatNumber($c['value']) . '٪' : formatNumber($c['value']) . ' تومان' ?></td>
                    <td class="num"><?= $c['max_uses'] ? formatNumber($c['max_uses']) : 'نامحدود' ?></td>
                    <td class="num"><?= formatNumber($c['used_count']) ?></td>
                    <td class="num"><?= $c['expires_at'] ? e(toJalali($c['expires_at'])) : '—' ?></td>
                    <td>
                        <?php if ($c['is_active']): ?><span class="badge-active">فعال</span>
                        <?php else: ?><span class="badge-inactive">غیرفعال</span><?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="<?= APP_URL ?>/admin/discounts/edit?id=<?= (int) $c['id'] ?>" class="btn btn-outline btn-sm">ویرایش</a>
                        <form method="POST" action="<?= APP_URL ?>/admin/discounts/toggle-active" style="display:inline">
                            <?= Auth::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm"><?= $c['is_active'] ? 'غیرفعال کن' : 'فعال کن' ?></button>
                        </form>
                        <form method="POST" action="<?= APP_URL ?>/admin/discounts/delete" style="display:inline"
                              onsubmit="return confirm('این کد تخفیف حذف شود؟');">
                            <?= Auth::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
