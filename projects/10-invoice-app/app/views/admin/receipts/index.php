<div class="dash-topbar">
    <h1>رسیدهای پرداخت <span class="count-pill"><?= formatNumber($result['total']) ?></span></h1>
</div>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = getFlash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>

<div class="search-bar">
    <?php foreach (['pending' => 'در انتظار', 'approved' => 'تایید شده', 'rejected' => 'رد شده', 'all' => 'همه'] as $key => $label): ?>
        <a href="<?= APP_URL ?>/admin/receipts?status=<?= $key ?>"
           class="btn btn-sm <?= $status === $key ? 'btn-primary' : 'btn-outline' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="card table-card">
    <?php if (empty($result['items'])): ?>
        <div class="empty-state">
            <div class="empty-icon"><i data-lucide="receipt"></i></div>
            <h3>رسیدی در این وضعیت یافت نشد</h3>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr><th>تصویر</th><th>کاربر</th><th>پلن</th><th>کد تخفیف</th><th>مبلغ</th><th>تاریخ ارسال</th><th>وضعیت</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($result['items'] as $r): ?>
                <tr>
                    <td><a href="<?= APP_URL ?>/uploads/receipts/<?= e($r['receipt_image']) ?>" target="_blank"><img class="receipt-thumb" src="<?= APP_URL ?>/uploads/receipts/<?= e($r['receipt_image']) ?>"></a></td>
                    <td><?= e($r['user_name']) ?><br><span style="color:var(--ink-soft); font-size:12px;" dir="ltr"><?= e($r['user_email']) ?></span></td>
                    <td><?= e($r['plan_name'] ?? '—') ?></td>
                    <td dir="ltr" style="text-align:right;"><?= $r['discount_code'] ? e($r['discount_code']) : '—' ?></td>
                    <td class="num"><?= $r['amount'] !== null ? formatNumber($r['amount']) . ' ت' : '—' ?></td>
                    <td class="num"><?= e(toJalaliDateTime($r['created_at'])) ?></td>
                    <td>
                        <?php if ($r['status'] === 'pending'): ?><span class="status-tag status-pending">در انتظار</span>
                        <?php elseif ($r['status'] === 'approved'): ?><span class="status-tag status-approved">تایید شده</span>
                        <?php else: ?><span class="status-tag status-rejected">رد شده</span><?php endif; ?>
                    </td>
                    <td class="actions">
                        <?php if ($r['status'] === 'pending'): ?>
                            <form method="POST" action="<?= APP_URL ?>/admin/receipts/approve" style="display:inline"
                                  onsubmit="return confirm('اشتراک این کاربر فعال شود؟');">
                                <?= Auth::csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">تایید</button>
                            </form>
                            <form method="POST" action="<?= APP_URL ?>/admin/receipts/reject" style="display:inline"
                                  onsubmit="return confirm('این رسید رد شود؟');">
                                <?= Auth::csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">رد</button>
                            </form>
                        <?php else: ?>
                            <a href="<?= APP_URL ?>/admin/users/view?id=<?= (int) $r['user_id'] ?>" class="btn btn-outline btn-sm">مشاهده کاربر</a>
                        <?php endif; ?>
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
            <a href="<?= APP_URL ?>/admin/receipts?status=<?= $status ?>&page=<?= $i ?>" class="<?= $i === $result['page'] ? 'active' : '' ?>"><?= formatNumber($i) ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
