<?php

class AdminInvoiceController extends Controller
{
    private Invoice $invoiceModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel = new Invoice();
        $this->userModel = new User();
    }

    public function show(): void
    {
        Auth::requireAdmin();
        $id = (int) $this->input('id');
        $invoice = $this->invoiceModel->adminFind($id);

        if (!$invoice) {
            setFlash('error', 'فاکتور یافت نشد.');
            redirect('/admin/users');
        }

        $this->view('invoices/show', [
            'title'   => 'فاکتور ' . $invoice['invoice_number'],
            'invoice' => $invoice,
            'items'   => $this->invoiceModel->itemsFor($id),
            'isPaid'  => true, // ادمین همیشه به همه خروجی‌ها دسترسی دارد
            'isAdmin' => true,
            'autoAction' => $this->input('action', ''),
            'features' => [
                'allow_pdf' => true, 'allow_excel' => true, 'allow_image' => true,
                'allow_edit' => true, 'allow_custom_templates' => true, 'allow_hide_ad' => true,
            ],
            'adText' => setting('invoice_ad_text', ''),
        ], 'layouts/print');
    }

    public function showEdit(): void
    {
        Auth::requireAdmin();
        $id = (int) $this->input('id');
        $invoice = $this->invoiceModel->adminFind($id);

        if (!$invoice) {
            setFlash('error', 'فاکتور یافت نشد.');
            redirect('/admin/users');
        }

        $this->view('admin/invoices/edit', [
            'title' => 'ویرایش فاکتور ' . $invoice['invoice_number'],
            'invoice' => $invoice,
            'items' => $this->invoiceModel->itemsFor($id),
        ], 'layouts/admin');
    }

    public function update(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        $existing = $this->invoiceModel->adminFind($id);
        if (!$existing) {
            setFlash('error', 'فاکتور یافت نشد.');
            redirect('/admin/users');
        }

        $customerName = $this->input('customer_name', '');
        $sellerName   = $this->input('seller_name', '');
        $invoiceDate  = $this->input('invoice_date_shamsi', '');

        $names      = $_POST['product_name'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $prices     = $_POST['unit_price'] ?? [];
        $discounts  = $_POST['discount'] ?? [];

        $lines = [];
        $errors = [];

        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            $qty = (int) ($quantities[$i] ?? 0);
            $price = (float) ($prices[$i] ?? 0);
            $discount = (float) ($discounts[$i] ?? 0);

            if ($name === '' && $qty <= 0 && $price <= 0) {
                continue;
            }
            if ($name === '' || $qty <= 0 || $price < 0) {
                $errors[] = 'اطلاعات ردیف‌های فاکتور کامل نیست.';
                continue;
            }
            $lines[] = ['product_name' => $name, 'quantity' => $qty, 'unit_price' => $price, 'discount' => $discount];
        }

        if (empty($lines) && empty($errors)) {
            $errors[] = 'حداقل باید یک ردیف محصول با اطلاعات معتبر وارد کنید.';
        }

        if (!empty($errors)) {
            $this->view('admin/invoices/edit', [
                'title' => 'ویرایش فاکتور ' . $existing['invoice_number'],
                'invoice' => array_merge($existing, [
                    'customer_name' => $customerName, 'seller_name' => $sellerName, 'invoice_date_shamsi' => $invoiceDate,
                ]),
                'items' => [], 'errors' => $errors,
            ], 'layouts/admin');
            return;
        }

        $this->invoiceModel->adminUpdateWithItems($id, $customerName, $sellerName, $invoiceDate, $lines);
        setFlash('success', 'فاکتور با موفقیت ویرایش شد.');
        redirect('/admin/invoices/view?id=' . $id);
    }

    public function delete(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        $invoice = $this->invoiceModel->adminFind($id);
        $userId = $invoice['user_id'] ?? null;

        $this->invoiceModel->adminDelete($id);
        setFlash('success', 'فاکتور حذف شد.');

        redirect($userId ? '/admin/users/view?id=' . $userId : '/admin/users');
    }

    /** خروجی اکسل از پنل مدیریت - بدون بررسی اشتراک (ادمین همیشه دسترسی کامل دارد) */
    public function exportExcel(): void
    {
        Auth::requireAdmin();
        $id = (int) $this->input('id');
        $invoice = $this->invoiceModel->adminFind($id);

        if (!$invoice) {
            setFlash('error', 'فاکتور یافت نشد.');
            redirect('/admin/users');
        }

        $items = $this->invoiceModel->itemsFor($id);
        require APP_PATH . '/core/ExcelInvoiceExporter.php';
        ExcelInvoiceExporter::stream($invoice, $items);
    }
}
