<div class="dash-topbar">
    <h1>مدیریت پلن‌های اشتراک <span class="count-pill"><?= formatNumber(count($plans)) ?></span></h1>
    <a href="<?= APP_URL ?>/admin/plans/create" class="btn btn-success">+ پلن جدید</a>
</div>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = getFlash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>

<div class="card table-card">
    <table class="table">
        <thead><tr><th></th><th>نام پلن</th><th>مدت</th><th>قیمت</th><th>سقف فاکتور</th><th>امکانات</th><th>وضعیت</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($plans as $p): ?>
            <tr>
                <td><i data-lucide="<?= e($p['icon']) ?>" style="color:<?= e($p['color']) ?>; width:18px; height:18px;"></i></td>
                <td><?= e($p['name']) ?><?= $p['is_free'] ? ' <span class="badge-plan-free">سیستمی</span>' : '' ?></td>
                <td class="num"><?= $p['is_free'] ? '—' : formatNumber($p['duration_months']) . ' ماه' ?></td>
                <td class="num"><?= $p['is_free'] ? 'رایگان' : formatNumber($p['price']) . ' تومان' ?></td>
                <td class="num"><?= $p['monthly_invoice_limit'] !== null ? formatNumber($p['monthly_invoice_limit']) : 'نامحدود' ?></td>
                <td style="font-size:16px; display:flex; gap:6px;">
                    <i data-lucide="file-text" style="color:<?= $p['allow_pdf'] ? 'var(--color-success)' : 'var(--color-text-muted)' ?>;" title="PDF"></i>
                    <i data-lucide="sheet" style="color:<?= $p['allow_excel'] ? 'var(--color-success)' : 'var(--color-text-muted)' ?>;" title="Excel"></i>
                    <i data-lucide="pencil" style="color:<?= $p['allow_edit'] ? 'var(--color-success)' : 'var(--color-text-muted)' ?>;" title="ویرایش"></i>
                    <i data-lucide="palette" style="color:<?= $p['allow_custom_templates'] ? 'var(--color-success)' : 'var(--color-text-muted)' ?>;" title="قالب‌ها"></i>
                </td>
                <td><?= $p['is_active'] ? '<span class="badge-active">فعال</span>' : '<span class="badge-inactive">غیرفعال</span>' ?></td>
                <td class="actions">
                    <a href="<?= APP_URL ?>/admin/plans/edit?id=<?= (int) $p['id'] ?>" class="btn btn-outline btn-sm">ویرایش</a>
                    <?php if (!$p['is_free']): ?>
                        <form method="POST" action="<?= APP_URL ?>/admin/plans/toggle-active" style="display:inline">
                            <?= Auth::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm"><?= $p['is_active'] ? 'غیرفعال کن' : 'فعال کن' ?></button>
                        </form>
                        <form method="POST" action="<?= APP_URL ?>/admin/plans/delete" style="display:inline"
                              onsubmit="return confirm('این پلن حذف شود؟ (کاربران این پلن به رایگان منتقل می‌شوند)');">
                            <?= Auth::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
