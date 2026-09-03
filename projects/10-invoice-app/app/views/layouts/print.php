<?php
$__isAdmin = $isAdmin ?? false;
$__autoAction = $autoAction ?? '';
$__features = $features ?? ['allow_pdf' => $isPaid, 'allow_excel' => $isPaid, 'allow_image' => true, 'allow_edit' => true];
$__backUrl = $__isAdmin ? (APP_URL . '/admin/users/view?id=' . (int) $invoice['user_id']) : (APP_URL . '/invoices');
$__editUrl = $__isAdmin ? (APP_URL . '/admin/invoices/edit?id=' . (int) $invoice['id']) : (APP_URL . '/invoices/edit?id=' . (int) $invoice['id']);
$__excelUrl = $__isAdmin ? (APP_URL . '/admin/invoices/export-excel?id=' . (int) $invoice['id']) : (APP_URL . '/invoices/export-excel?id=' . (int) $invoice['id']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? setting('site_name', APP_NAME)) ?></title>

<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<script src="<?= APP_URL ?>/assets/vendor/html2canvas.min.js"></script>
<script src="<?= APP_URL ?>/assets/vendor/jspdf.umd.min.js"></script>
<style>
    body { background: var(--color-surface-2); }
    .print-toolbar {
        max-width: 780px; margin: 20px auto 0; display: flex; justify-content: space-between; align-items: center;
        padding: 0 14px; flex-wrap: wrap; gap: 10px;
    }
    @media (max-width: 600px) {
        .invoice-paper { padding: 24px 18px !important; }
        .invoice-header { flex-direction: column; gap: 16px; }
        .invoice-meta { text-align: right; }
        .print-toolbar { justify-content: center; text-align: center; }
        .invoice-customer { font-size: 13px; line-height: 1.9; }
        .invoice-table { margin-top: 16px; }
        .invoice-table thead th,
        .invoice-table tbody td { padding: 9px 6px; font-size: 11px; }
        .invoice-table tbody td { word-break: break-word; }
        .invoice-total-row { flex-direction: column; align-items: flex-start; gap: 6px; padding: 12px 14px; font-size: 13.5px; }
        .invoice-total-row b { font-size: 15px; max-width: 100%; }
        .invoice-footer { margin-top: 22px; font-size: 10.5px; line-height: 1.9; }
    }
    @media (max-width: 560px) {
        .print-toolbar { flex-direction: column; align-items: stretch; gap: 10px; }
        .print-toolbar > div { width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .print-toolbar .btn { margin: 0; justify-content: center; }
    }
    .invoice-wrap { max-width: 780px; margin: 16px auto 60px; }
    @media print {
        body { background: #fff; }
        .no-print { display: none !important; }
        .invoice-wrap { margin: 0; max-width: 100%; }
        .invoice-paper { box-shadow: none !important; border: none !important; }
    }
</style>
<script>
(function(){try{var t=localStorage.getItem('siteTheme');if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t);}}catch(e){}})();
</script>
</head>
<body>
<?php if ($__isAdmin): ?>
<div style="max-width:780px; margin:14px auto 0; padding:0 14px;" class="no-print">
    <span class="badge-plan-paid"><i data-lucide="shield-check"></i> در حال مشاهده به‌عنوان مدیر — صاحب فاکتور: <?= e($invoice['owner_name'] ?? '') ?></span>
</div>
<?php endif; ?>
<div class="print-toolbar no-print">
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="<?= $__backUrl ?>" class="btn btn-outline btn-sm"><i data-lucide="arrow-right"></i> بازگشت</a>
        <?php if ($__features['allow_edit']): ?>
            <a href="<?= $__editUrl ?>" class="btn btn-outline btn-sm" <?= (!$__isAdmin && ($__features['monthly_invoice_limit'] ?? null) !== null) ? 'data-edit-warning="1"' : '' ?>><i data-lucide="square-pen"></i> ویرایش فاکتور</a>
        <?php endif; ?>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <button onclick="printLight()" class="btn btn-outline btn-sm"><i data-lucide="printer"></i> چاپ</button>
        <?php if ($__features['allow_image']): ?>
            <button id="download-png-btn" class="btn btn-success btn-sm"><i data-lucide="file-image"></i> دانلود تصویر (PNG)</button>
        <?php endif; ?>
        <button id="download-pdf-btn" class="btn <?= $__features['allow_pdf'] ? 'btn-primary' : 'btn-outline' ?> btn-sm">
            <i data-lucide="file-text"></i> دانلود PDF <?= $__features['allow_pdf'] ? '' : '<i data-lucide="lock"></i>' ?>
        </button>
        <button id="download-xls-btn" class="btn <?= $__features['allow_excel'] ? 'btn-gold' : 'btn-outline' ?> btn-sm">
            <i data-lucide="sheet"></i> دانلود Excel <?= $__features['allow_excel'] ? '' : '<i data-lucide="lock"></i>' ?>
        </button>
    </div>
</div>

<div class="invoice-wrap">
    <?= $content ?>
</div>

<script>
const ALLOW_PDF = <?= $__features['allow_pdf'] ? 'true' : 'false' ?>;
const ALLOW_EXCEL = <?= $__features['allow_excel'] ? 'true' : 'false' ?>;
const APP_URL_JS = '<?= APP_URL ?>';
const EXCEL_URL = '<?= $__excelUrl ?>';

function withLightTheme(fn) {
    const html = document.documentElement;
    const prev = html.getAttribute('data-theme');
    html.setAttribute('data-theme', 'light');
    requestAnimationFrame(function () {
        fn();
        if (prev) html.setAttribute('data-theme', prev); else html.removeAttribute('data-theme');
    });
}

function capturePaper() {
    const paper = document.getElementById('invoice-paper');
    const origW = paper.style.width, origMax = paper.style.maxWidth;
    const origML = paper.style.marginLeft, origMR = paper.style.marginRight;
    // خروجی همیشه با عرض ثابت ۷۶۰px رندر شود — مستقل از عرض گوشی
    paper.style.width = '760px';
    paper.style.maxWidth = 'none';
    paper.style.marginLeft = 'auto';
    paper.style.marginRight = 'auto';
    return html2canvas(paper, { scale: 2, backgroundColor: '#ffffff' }).then(function (canvas) {
        paper.style.width = origW; paper.style.maxWidth = origMax;
        paper.style.marginLeft = origML; paper.style.marginRight = origMR;
        return canvas;
    }, function (err) {
        paper.style.width = origW; paper.style.maxWidth = origMax;
        paper.style.marginLeft = origML; paper.style.marginRight = origMR;
        throw err;
    });
}

function downloadPng(onDone) {
    const btn = document.getElementById('download-png-btn');
    if (!btn) return;
    const original = btn.textContent;
    btn.textContent = 'در حال آماده‌سازی...';
    btn.disabled = true;

    withLightTheme(function () {
        capturePaper().then(function (canvas) {
            const link = document.createElement('a');
            link.download = (document.getElementById('invoice-paper').dataset.invoiceNumber || 'invoice') + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            btn.textContent = original;
            btn.disabled = false;
            if (onDone) onDone();
        }).catch(function () {
            alert('خطا در ساخت تصویر. لطفا دوباره تلاش کنید.');
            btn.textContent = original;
            btn.disabled = false;
        });
    });
}

function showUpgradeAlert() {
    if (confirm('این قابلیت در پلن فعلی شما فعال نیست.\nآیا می‌خواهید صفحه اشتراک را مشاهده کنید؟')) {
        window.location.href = APP_URL_JS + '/subscription';
    }
}

function printLight() {
    const html = document.documentElement;
    const prev = html.getAttribute('data-theme');
    html.setAttribute('data-theme', 'light');
    try { window.print(); } finally { if (prev) html.setAttribute('data-theme', prev); else html.removeAttribute('data-theme'); }
}

function downloadPdf(onDone) {
    const paper = document.getElementById('invoice-paper');
    if (!ALLOW_PDF) { showUpgradeAlert(); return; }
    const btn = document.getElementById('download-pdf-btn');
    const original = btn.textContent;
    btn.textContent = 'در حال آماده‌سازی...';
    btn.disabled = true;

    withLightTheme(function () {
        capturePaper().then(function (canvas) {
        const { jsPDF } = window.jspdf;
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
        const pageWidth = pdf.internal.pageSize.getWidth();
        const imgHeight = (canvas.height * pageWidth) / canvas.width;
        pdf.addImage(imgData, 'PNG', 0, 0, pageWidth, imgHeight);
        pdf.save((paper.dataset.invoiceNumber || 'invoice') + '.pdf');
        btn.textContent = original;
        btn.disabled = false;
        if (onDone) onDone();
    }).catch(function () {
            alert('خطا در ساخت PDF. لطفا دوباره تلاش کنید.');
            btn.textContent = original;
            btn.disabled = false;
        });
    });
}

document.getElementById('download-png-btn')?.addEventListener('click', () => downloadPng());
document.getElementById('download-pdf-btn')?.addEventListener('click', () => downloadPdf());
document.getElementById('download-xls-btn')?.addEventListener('click', function () {
    if (!ALLOW_EXCEL) { showUpgradeAlert(); return; }
    window.location.href = EXCEL_URL;
});

// در صورت ورود از لینک‌های اقدام سریع پنل مدیریت (مثلا ?action=pdf) دانلود به‌صورت خودکار شروع می‌شود
const AUTO_ACTION = '<?= e($__autoAction) ?>';
if (AUTO_ACTION === 'pdf') downloadPdf();
if (AUTO_ACTION === 'png') downloadPng();
</script>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
<script defer src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script defer>document.addEventListener('DOMContentLoaded', function () { if (window.lucide) lucide.createIcons(); });</script>
</body>
</html>
