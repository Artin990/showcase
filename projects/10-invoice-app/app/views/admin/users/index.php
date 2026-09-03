<div class="dash-topbar">
    <h1>مدیریت کاربران <span class="count-pill"><?= formatNumber($result['total']) ?></span></h1>
</div>

<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>

<form method="GET" action="<?= APP_URL ?>/admin/users" class="search-bar">
    <input type="text" name="q" class="form-control" placeholder="جستجو با نام، ایمیل یا موبایل..." value="<?= e($search) ?>">
    <button type="submit" class="btn btn-outline">جستجو</button>
</form>

<div class="card table-card">
    <?php if (empty($result['items'])): ?>
        <div class="empty-state">
            <div class="empty-icon"><i data-lucide="users"></i></div>
            <h3>کاربری یافت نشد</h3>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>نام</th>
                    <th>ایمیل</th>
                    <th>موبایل</th>
                    <th>تاریخ عضویت</th>
                    <th>اشتراک</th>
                    <th>پایان اشتراک</th>
                    <th>فاکتورها</th>
                    <th>وضعیت</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($result['items'] as $u): ?>
                <tr>
                    <td><?= e($u['name']) ?></td>
                    <td dir="ltr" style="text-align:right;"><?= e($u['email']) ?></td>
                    <td dir="ltr" style="text-align:right;"><?= e($u['phone']) ?></td>
                    <td class="num"><?= e(toJalali($u['created_at'])) ?></td>
                    <td>
                        <?php if (!empty($u['plan_is_free'])): ?>
                            <span class="badge-plan-free">رایگان</span>
                        <?php else: ?>
                            <span class="badge-plan-paid"><?= e($u['plan_name']) ?> (<?= $u['sub_status'] === 'active' ? 'فعال' : 'منقضی' ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td class="num"><?= $u['end_date'] ? e(toJalali($u['end_date'])) : '—' ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/admin/users/view?id=<?= (int) $u['id'] ?>" class="num" style="color:var(--navy); font-weight:700;">
                            <?= formatNumber($u['invoice_count']) ?>
                        </a>
                    </td>
                    <td>
                        <?php if ($u['is_active']): ?>
                            <span class="badge-active">فعال</span>
                        <?php else: ?>
                            <span class="badge-inactive">غیرفعال</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="<?= APP_URL ?>/admin/users/view?id=<?= (int) $u['id'] ?>" class="btn btn-outline btn-sm">مشاهده</a>
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
            <a href="<?= APP_URL ?>/admin/users?page=<?= $i ?>&q=<?= urlencode($search) ?>" class="<?= $i === $result['page'] ? 'active' : '' ?>"><?= formatNumber($i) ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
