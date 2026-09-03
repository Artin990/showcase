<div class="dash-topbar">
    <h1>مدیریت حساب‌های مدیر</h1>
    <a href="<?= APP_URL ?>/admin/admins/create" class="btn btn-success">+ افزودن مدیر</a>
</div>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = getFlash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>

<div class="card table-card">
    <table class="table">
        <thead><tr><th>نام</th><th>ایمیل</th><th>نقش</th><th>وضعیت</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($admins as $a): ?>
            <tr>
                <td><?= e($a['name']) ?></td>
                <td dir="ltr" style="text-align:right;"><?= e($a['email']) ?></td>
                <td><?= $a['role'] === 'super_admin' ? '<span class="badge-plan-paid">مدیر ارشد</span>' : '<span class="badge-plan-free">مدیر</span>' ?></td>
                <td><?= $a['is_active'] ? '<span class="badge-active">فعال</span>' : '<span class="badge-inactive">غیرفعال</span>' ?></td>
                <td class="actions">
                    <?php if ($a['role'] !== 'super_admin'): ?>
                        <form method="POST" action="<?= APP_URL ?>/admin/admins/toggle-active" style="display:inline">
                            <?= Auth::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm"><?= $a['is_active'] ? 'غیرفعال کن' : 'فعال کن' ?></button>
                        </form>
                        <form method="POST" action="<?= APP_URL ?>/admin/admins/delete" style="display:inline"
                              onsubmit="return confirm('این مدیر حذف شود؟');">
                            <?= Auth::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                        </form>
                    <?php else: ?>
                        <span style="color:var(--ink-soft); font-size:12.5px;">غیرقابل تغییر</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
