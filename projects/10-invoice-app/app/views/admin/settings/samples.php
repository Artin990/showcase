<div class="dash-topbar">
    <h1>نمونه فاکتورها (گالری صفحه اصلی)</h1>
</div>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = getFlash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>

<div class="card" style="max-width:480px; margin-bottom:20px;">
    <h3 style="font-size:15px; margin-bottom:14px;">افزودن تصویر جدید</h3>
    <form method="POST" action="<?= APP_URL ?>/admin/samples/store" enctype="multipart/form-data">
        <?= Auth::csrfField() ?>
        <div class="form-group">
            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp" required>
        </div>
        <button type="submit" class="btn btn-primary">آپلود تصویر</button>
    </form>
</div>

<div class="sample-gallery-grid">
    <?php foreach ($samples as $s): ?>
        <div class="sample-gallery-item">
            <img src="<?= APP_URL . '/uploads/samples/' . e($s['image']) ?>">
            <form method="POST" action="<?= APP_URL ?>/admin/samples/delete" onsubmit="return confirm('این تصویر حذف شود؟');">
                <?= Auth::csrfField() ?>
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">حذف</button>
            </form>
        </div>
    <?php endforeach; ?>
    <?php if (empty($samples)): ?>
        <p style="color:var(--ink-soft); font-size:13.5px;">هنوز تصویری آپلود نشده است.</p>
    <?php endif; ?>
</div>
