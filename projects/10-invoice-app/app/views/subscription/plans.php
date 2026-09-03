<?php $freeFeatures = (new Subscription())->getPlanFeatures(Auth::userId()); ?>
<div class="dash-topbar">
    <h1>اشتراک</h1>
</div>

<div class="card" style="margin-bottom:22px;">
    <?php if ($isPaid): ?>
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <span class="plan-tag plan-tag-gold"><?= e($sub['plan_name'] ?? 'اشتراک پولی') ?> فعال</span>
            <?php if ($daysLeft !== null): ?>
                <span style="color:var(--ink-soft); font-size:13.5px;"><?= formatNumber($daysLeft) ?> روز تا پایان اشتراک</span>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <span class="plan-tag">اشتراک رایگان</span>
            <span style="color:var(--ink-soft); font-size:13.5px;">
                <?php if ($freeFeatures['monthly_invoice_limit'] !== null): ?>
                    سقف <?= formatNumber($freeFeatures['monthly_invoice_limit']) ?> فاکتور در ماه.
                <?php endif; ?>
                برای امکانات بیشتر، یکی از پلن‌های زیر را تهیه کنید.
            </span>
        </div>
        <?php if ($hasPending): ?>
            <div class="alert alert-success" style="margin-top:14px; margin-bottom:0;">
                رسید پرداخت شما ثبت شده و در انتظار بررسی و تایید است. پس از تایید، اشتراک شما فعال می‌شود.
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="plans-grid">
    <?php foreach ($plans as $p): ?>
        <?php $isFeatured = !empty($p['original_price']); ?>
        <div class="plan-card <?= $isFeatured ? 'featured' : '' ?>" style="border-top:3px solid <?= e($p['color']) ?>;">
            <?php if ($isFeatured): ?><div class="plan-badge-ribbon">پیشنهادی</div><?php endif; ?>
            <div class="plan-icon" style="color:<?= e($p['color']) ?>;"><i data-lucide="<?= e($p['icon']) ?>"></i></div>
            <div class="plan-name"><?= e($p['name']) ?></div>
            <?php if (!empty($p['description'])): ?>
                <div class="plan-desc"><?= e($p['description']) ?></div>
            <?php endif; ?>
            <div class="plan-price">
                <?php if ($isFeatured): ?><span class="old-price num"><?= formatNumber($p['original_price']) ?></span><?php endif; ?>
                <?= formatNumber($p['price']) ?> <span>تومان</span>
            </div>
            <?php if ($isFeatured): ?>
                <div class="discount-note"><?= formatNumber($p['original_price'] - $p['price']) ?> تومان تخفیف ویژه</div>
            <?php endif; ?>
            <ul class="plan-features">
                <li><?= $p['monthly_invoice_limit'] !== null ? 'سقف ' . formatNumber($p['monthly_invoice_limit']) . ' فاکتور در ماه' : 'صدور نامحدود فاکتور' ?></li>
                <?php if ($p['allow_image']): ?><li>دانلود خروجی تصویر</li><?php endif; ?>
                <?php if ($p['allow_pdf']): ?><li class="highlight">دانلود PDF</li><?php endif; ?>
                <?php if ($p['allow_excel']): ?><li class="highlight">دانلود Excel</li><?php endif; ?>
                <?php if ($p['allow_edit']): ?><li>ویرایش فاکتور</li><?php endif; ?>
                <?php if ($p['allow_custom_templates']): ?><li class="highlight">دسترسی به همه قالب‌های فاکتور</li><?php endif; ?>
                <?php if ($p['allow_hide_ad']): ?><li>حذف پیام پایانی از فاکتور</li><?php endif; ?>
            </ul>
            <a href="<?= APP_URL ?>/subscription/purchase?plan=<?= (int) $p['id'] ?>" class="btn <?= $isFeatured ? 'btn-gold' : 'btn-primary' ?> btn-block">خرید این پلن</a>
        </div>
    <?php endforeach; ?>
    <?php if (empty($plans)): ?>
        <p style="color:var(--ink-soft);">در حال حاضر پلن فعالی برای خرید وجود ندارد.</p>
    <?php endif; ?>
</div>

<?php if (!empty($receipts)): ?>
<div class="card table-card" style="margin-top:26px;">
    <h3 style="font-size:15px; padding:20px 20px 0;">تاریخچه پرداخت‌ها</h3>
    <table class="table">
        <thead><tr><th>پلن</th><th>تاریخ ارسال</th><th>وضعیت</th></tr></thead>
        <tbody>
        <?php foreach ($receipts as $r): ?>
            <tr>
                <td><?= e($r['plan_name'] ?? '—') ?></td>
                <td class="num"><?= e(toJalali($r['created_at'])) ?></td>
                <td>
                    <?php if ($r['status'] === 'pending'): ?>
                        <span class="status-tag status-pending">در انتظار بررسی</span>
                    <?php elseif ($r['status'] === 'approved'): ?>
                        <span class="status-tag status-approved">تایید شده</span>
                    <?php else: ?>
                        <span class="status-tag status-rejected">رد شده</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
