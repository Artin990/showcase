<?php

class AdminSampleInvoiceController extends Controller
{
    private SampleInvoice $model;
    private const ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_SIZE = 4 * 1024 * 1024; // 4MB

    public function __construct()
    {
        parent::__construct();
        $this->model = new SampleInvoice();
    }

    public function index(): void
    {
        Auth::requireAdmin();
        $this->view('admin/settings/samples', [
            'title' => 'نمونه فاکتورها',
            'samples' => $this->model->all(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();

        $file = $_FILES['image'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            setFlash('error', 'لطفا یک تصویر انتخاب کنید.');
            redirect('/admin/samples');
        }
        if ($file['size'] > self::MAX_SIZE) {
            setFlash('error', 'حجم فایل نباید بیشتر از ۴ مگابایت باشد.');
            redirect('/admin/samples');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::ALLOWED_MIME[$mime])) {
            setFlash('error', 'فرمت فایل باید تصویر (JPG, PNG یا WEBP) باشد.');
            redirect('/admin/samples');
        }

        $ext = self::ALLOWED_MIME[$mime];
        $filename = bin2hex(random_bytes(12)) . '.' . $ext;
        $dir = UPLOAD_PATH . '/samples';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            setFlash('error', 'خطا در ذخیره فایل روی سرور.');
            redirect('/admin/samples');
        }

        $this->model->create($filename, count($this->model->all()));
        setFlash('success', 'تصویر نمونه فاکتور اضافه شد.');
        redirect('/admin/samples');
    }

    public function delete(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        $sample = $this->model->find($id);
        if ($sample) {
            @unlink(UPLOAD_PATH . '/samples/' . $sample['image']);
            $this->model->delete($id);
        }

        setFlash('success', 'تصویر حذف شد.');
        redirect('/admin/samples');
    }
}
