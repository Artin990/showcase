<?php

class InvoiceController extends Controller
{
    private Invoice $invoiceModel;
    private User $userModel;
    private Subscription $subModel;

    private const ALLOWED_TEMPLATES = ['classic', 'modern', 'minimal', 'elegant', 'corporate'];

    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel = new Invoice();
        $this->userModel    = new User();
        $this->subModel     = new Subscription();
    }

    public function index(): void
    {
        Auth::requireUser();
        $page = max(1, (int) $this->input('page', 1));
        $result = $this->invoiceModel->paginateForUser(Auth::userId(), $page);
        $features = $this->subModel->getPlanFeatures(Auth::userId());

        $this->view('invoices/index', [
            'title'  => 'فاکتورها',
            'result' => $result,
            'isPaid' => $this->subModel->isPaidActive(Auth::userId()),
            'hasQuotaLimit' => $features['monthly_invoice_limit'] !== null,
        ], 'layouts/dashboard');
    }

    public function showCreate(): void
    {
        Auth::requireUser();
        $user = $this->userModel->findById(Auth::userId());
        $isPaid = $this->subModel->isPaidActive(Auth::userId());
        $features = $this->subModel->getPlanFeatures(Auth::userId());

        $this->view('invoices/create', [
            'title' => 'صدور فاکتور جدید',
            'mode' => 'create',
            'user' => $user,
            'invoice' => null,
            'items' => [],
            'nextNumber' => $this->invoiceModel->nextInvoiceNumber(),
            'isPaid' => $isPaid,
            'features' => $features,
            'quota' => $features['monthly_invoice_limit'] === null ? null : $this->userModel->getQuotaStatus(Auth::userId()),
        ], 'layouts/dashboard');
    }

    public function store(): void
    {
        Auth::requireUser();
        $this->verifyCsrfOrDie();

        $features = $this->subModel->getPlanFeatures(Auth::userId());
        $quotaStatus = $this->userModel->getQuotaStatus(Auth::userId());

        if (!$quotaStatus['unlimited'] && $quotaStatus['remaining'] <= 0) {
            setFlash('error', 'سهمیه ماهانه شما برای صدور فاکتور تمام شده است. برای ادامه، اشتراک تهیه کنید.');
            redirect('/subscription');
        }

        [$errors, $data] = $this->collectAndValidate($features);

        if (!empty($errors)) {
            $this->view('invoices/create', [
                'title' => 'صدور فاکتور جدید', 'mode' => 'create',
                'user' => $this->userModel->findById(Auth::userId()),
                'invoice' => null, 'items' => $data['lines'],
                'errors' => $errors, 'old' => $data['old'],
                'nextNumber' => $this->invoiceModel->nextInvoiceNumber(),
                'isPaid' => $this->subModel->isPaidActive(Auth::userId()), 'features' => $features,
                'quota' => $quotaStatus['unlimited'] ? null : $quotaStatus,
            ], 'layouts/dashboard');
            return;
        }

        $invoice = $this->invoiceModel->createWithItems(
            Auth::userId(), $data['customer_name'], $data['seller_name'], $data['invoice_date_shamsi'],
            $data['lines'], $data['template'], $data['hide_ad']
        );

        if (!$invoice) {
            setFlash('error', 'خطایی در صدور فاکتور رخ داد. دوباره تلاش کنید.');
            redirect('/invoices/create');
        }

        $this->userModel->consumeQuota(Auth::userId());

        setFlash('success', 'فاکتور با موفقیت صادر شد.');
        redirect('/invoices/view?id=' . $invoice['id']);
    }

    public function showEdit(): void
    {
        Auth::requireUser();
        $id = (int) $this->input('id');
        $invoice = $this->invoiceModel->findForUser($id, Auth::userId());

        if (!$invoice) {
            setFlash('error', 'فاکتور یافت نشد.');
            redirect('/invoices');
        }

        $isPaid = $this->subModel->isPaidActive(Auth::userId());
        $features = $this->subModel->getPlanFeatures(Auth::userId());

        if (!$features['allow_edit']) {
            setFlash('error', 'پلن فعلی شما اجازه ویرایش فاکتور را نمی‌دهد.');
            redirect('/invoices/view?id=' . $id);
        }

        $this->view('invoices/create', [
            'title' => 'ویرایش فاکتور ' . $invoice['invoice_number'],
            'mode' => 'edit',
            'user' => $this->userModel->findById(Auth::userId()),
            'invoice' => $invoice,
            'items' => $this->invoiceModel->itemsFor($id),
            'nextNumber' => $invoice['invoice_number'],
            'isPaid' => $isPaid,
            'features' => $features,
            'quota' => $features['monthly_invoice_limit'] === null ? null : $this->userModel->getQuotaStatus(Auth::userId()),
        ], 'layouts/dashboard');
    }

    public function update(): void
    {
        Auth::requireUser();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        $existing = $this->invoiceModel->findForUser($id, Auth::userId());
        if (!$existing) {
            setFlash('error', 'فاکتور یافت نشد.');
            redirect('/invoices');
        }

        $features = $this->subModel->getPlanFeatures(Auth::userId());
        if (!$features['allow_edit']) {
            setFlash('error', 'پلن فعلی شما اجازه ویرایش فاکتور را نمی‌دهد.');
            redirect('/invoices/view?id=' . $id);
        }

        // ویرایش فاکتور سهمیه جدیدی مصرف نمی‌کند (فقط صدور فاکتور جدید سهمیه برمی‌دارد)

        [$errors, $data] = $this->collectAndValidate($features);

        if (!empty($errors)) {
            $data['old']['id'] = $id;
            $this->view('invoices/create', [
                'title' => 'ویرایش فاکتور ' . $existing['invoice_number'], 'mode' => 'edit',
                'user' => $this->userModel->findById(Auth::userId()),
                'invoice' => array_merge($existing, $data['old']), 'items' => $data['lines'],
                'errors' => $errors, 'isPaid' => $this->subModel->isPaidActive(Auth::userId()),
                'features' => $features, 'quota' => $features['monthly_invoice_limit'] === null ? null : $this->userModel->getQuotaStatus(Auth::userId()),
            ], 'layouts/dashboard');
            return;
        }

        $ok = $this->invoiceModel->updateWithItems(
            $id, Auth::userId(), $data['customer_name'], $data['seller_name'], $data['invoice_date_shamsi'],
            $data['lines'], $data['template'], $data['hide_ad']
        );

        if (!$ok) {
            setFlash('error', 'خطایی در ویرایش فاکتور رخ داد. دوباره تلاش کنید.');
            redirect('/invoices/edit?id=' . $id);
        }

        setFlash('success', 'فاکتور با موفقیت ویرایش شد.');
        redirect('/invoices/view?id=' . $id);
    }

    public function show(): void
    {
        Auth::requireUser();
        $id = (int) $this->input('id');
        $invoice = $this->invoiceModel->findForUser($id, Auth::userId());

        if (!$invoice) {
            setFlash('error', 'فاکتور یافت نشد.');
            redirect('/invoices');
        }

        $items = $this->invoiceModel->itemsFor($id);
        $features = $this->subModel->getPlanFeatures(Auth::userId());

        $this->view('invoices/show', [
            'title'   => 'فاکتور ' . $invoice['invoice_number'],
            'invoice' => $invoice,
            'items'   => $items,
            'isPaid'  => $this->subModel->isPaidActive(Auth::userId()),
            'features' => $features,
            'adText'  => setting('invoice_ad_text', ''),
        ], 'layouts/print');
    }

    public function delete(): void
    {
        Auth::requireUser();
        $this->verifyCsrfOrDie();

        $id = (int) $this->input('id');
        $this->invoiceModel->delete($id, Auth::userId());

        setFlash('success', 'فاکتور حذف شد.');
        redirect('/invoices');
    }

    /** جمع‌آوری و اعتبارسنجی مشترک فرم ایجاد/ویرایش فاکتور */
    private function collectAndValidate(array $features): array
    {
        $customerName = trim($this->input('customer_name', ''));
        $invoiceDate  = trim($this->input('invoice_date_shamsi', ''));
        $useStoreName = $this->input('use_store_name', '');

        if ($useStoreName === '1') {
            $user = $this->userModel->findById(Auth::userId());
            $sellerName = $user['store_name'] ?? '';
        } else {
            $sellerName = trim($this->input('seller_name', ''));
        }

        $errors = [];

        // اعتبارسنجی‌های پایه فرم — نام خریدار و فروشنده هر دو اختیاری هستند
        if (mb_strlen($customerName) > 120) {
            $errors[] = 'نام خریدار بیش از حد مجاز است.';
        }
        if ($invoiceDate === '') {
            $errors[] = 'تاریخ فاکتور الزامی است.';
        } elseif (fromJalali($invoiceDate) === null) {
            $errors[] = 'تاریخ فاکتور معتبر نیست (فرمت صحیح: ۱۴۰۴/۰۶/۱۰).';
        }
        if (mb_strlen($sellerName) > 120) {
            $errors[] = 'نام فروشنده بیش از حد مجاز است.';
        }

        // انتخاب قالب: فقط اگر پلن اجازه قالب‌های اختصاصی می‌دهد؛ در غیر این‌صورت همیشه کلاسیک
        $templateInput = $this->input('template', 'classic');
        $template = ($features['allow_custom_templates'] && in_array($templateInput, self::ALLOWED_TEMPLATES, true))
            ? $templateInput : 'classic';

        // نمایش/مخفی کردن تبلیغ: چک‌باکس یعنی «نمایش تبلیغ»؛ اگر تیک نخورده باشه یعنی مخفی بشه
        // چک‌باکس‌های غیرفعال اصلاً در POST نمی‌آیند، پس نبودش یعنی کاربر تیکش رو برداشته
        $showAdSubmitted = $this->input('show_ad', '') === '1';
        $hideAd = $features['allow_hide_ad'] && !$showAdSubmitted;

        $names      = $_POST['product_name'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $prices     = $_POST['unit_price'] ?? [];
        $discounts  = $_POST['discount'] ?? [];

        $lines = [];

        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            $qty = (int) ($quantities[$i] ?? 0);
            $price = (float) ($prices[$i] ?? 0);
            $discount = (float) ($discounts[$i] ?? 0);

            if ($name === '' && $qty <= 0 && $price <= 0) {
                continue;
            }
            if ($name === '' || $qty <= 0 || $price < 0) {
                $errors[] = 'اطلاعات ردیف‌های فاکتور کامل نیست (نام محصول، تعداد و قیمت الزامی هستند).';
                continue;
            }
            if ($discount < 0 || $discount > ($qty * $price)) {
                $errors[] = 'مبلغ تخفیف ردیف «' . $name . '» نامعتبر است.';
                continue;
            }

            $lines[] = [
                'product_name' => $name,
                'quantity'     => $qty,
                'unit_price'   => $price,
                'discount'     => $discount,
            ];
        }

        if (empty($lines) && empty($errors)) {
            $errors[] = 'حداقل باید یک ردیف محصول با اطلاعات معتبر وارد کنید.';
        }

        return [$errors, [
            'customer_name' => $customerName,
            'seller_name' => $sellerName,
            'invoice_date_shamsi' => $invoiceDate,
            'lines' => $lines,
            'template' => $template,
            'hide_ad' => $hideAd,
            'old' => [
                'customer_name' => $customerName,
                'seller_name' => $this->input('seller_name', ''),
                'invoice_date_shamsi' => $invoiceDate,
                'use_store_name' => $useStoreName,
                'template' => $templateInput,
                'hide_ad' => $showAdSubmitted ? '0' : '1',
            ],
        ]];
    }

    /**
     * خروجی اکسل حرفه‌ای - بدون نیاز به کتابخانه خارجی (SpreadsheetML).
     * فقط اگر پلن کاربر اجازه دانلود اکسل را بدهد فعال است.
     */
    public function exportExcel(): void
    {
        Auth::requireUser();
        $id = (int) $this->input('id');
        $invoice = $this->invoiceModel->findForUser($id, Auth::userId());

        if (!$invoice) {
            setFlash('error', 'فاکتور یافت نشد.');
            redirect('/invoices');
        }

        $features = $this->subModel->getPlanFeatures(Auth::userId());
        if (!$features['allow_excel']) {
            setFlash('error', 'دانلود فایل اکسل در پلن فعلی شما فعال نیست.');
            redirect('/subscription');
        }

        $items = $this->invoiceModel->itemsFor($id);
        require APP_PATH . '/core/ExcelInvoiceExporter.php';
        ExcelInvoiceExporter::stream($invoice, $items);
    }
}
