<?php
$tpl = in_array($invoice['template'] ?? 'classic', ['classic', 'modern', 'minimal', 'elegant', 'corporate'], true) ? $invoice['template'] : 'classic';
$showAd = empty($invoice['hide_ad']) && !empty($adText);
?>
<?php if ($msg = getFlash('success')): ?>
    <div class="alert alert-success no-print" style="max-width:780px; margin:0 auto 12px;"><?= e($msg) ?></div>
<?php endif; ?>

<div class="invoice-paper tpl-<?= $tpl ?>" id="invoice-paper" data-invoice-number="<?= e($invoice['invoice_number']) ?>">

    <div class="invoice-header">
        <div>
            <div class="invoice-brand"><i data-lucide="receipt-text"></i> <?= e(setting('site_name', APP_NAME)) ?></div>
            <?php if (!empty($invoice['seller_name'])): ?>
                <div class="invoice-seller">فروشنده: <?= e($invoice['seller_name']) ?></div>
            <?php endif; ?>
        </div>
        <div class="invoice-meta">
            <div class="invoice-title">فاکتور فروش</div>
            <div class="meta-row"><span>شماره فاکتور</span><b dir="ltr"><?= e($invoice['invoice_number']) ?></b></div>
            <?php if (!empty($invoice['invoice_date_shamsi'])): ?>
                <div class="meta-row"><span>تاریخ</span><b><?= e($invoice['invoice_date_shamsi']) ?></b></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($invoice['customer_name'])): ?>
    <div class="invoice-customer">
        <span>خریدار:</span> <b><?= e($invoice['customer_name']) ?></b>
    </div>
    <?php endif; ?>

    <div class="invoice-body-pad">
    <table class="invoice-table">
        <thead>
            <tr>
                <th>#</th>
                <th>نام محصول</th>
                <th>تعداد</th>
                <th>قیمت واحد (ریال)</th>
                <th>تخفیف (ریال)</th>
                <th>مبلغ (ریال)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $i => $item): ?>
            <tr>
                <td class="num"><?= formatNumber($i + 1) ?></td>
                <td><?= e($item['product_name']) ?></td>
                <td class="num"><?= formatNumber($item['quantity']) ?></td>
                <td class="num"><?= formatNumber($item['unit_price']) ?></td>
                <td class="num"><?= $item['discount'] > 0 ? formatNumber($item['discount']) : '—' ?></td>
                <td class="num"><?= formatNumber($item['row_total']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="invoice-total-row">
        <span>جمع کل فاکتور</span>
        <b class="num"><?= formatNumber($invoice['total_amount']) ?> ریال</b>
    </div>

    <?php if ($showAd): ?>
        <div class="invoice-footer"><?= e($adText) ?></div>
    <?php endif; ?>
    </div>
</div>
