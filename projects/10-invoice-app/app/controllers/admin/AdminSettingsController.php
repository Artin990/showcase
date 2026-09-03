<?php

class AdminSettingsController extends Controller
{
    private Settings $settingsModel;

    private const ALLOWED_IMAGE_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const ALLOWED_ICON_MIME = ['image/png' => 'png', 'image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico'];
    private const MAX_SIZE = 2 * 1024 * 1024; // 2MB

    public function __construct()
    {
        parent::__construct();
        $this->settingsModel = new Settings();
    }

    public function index(): void
    {
        Auth::requireAdmin();
        $this->view('admin/settings/index', [
            'title' => 'تنظیمات سایت',
            'settings' => $this->settingsModel->all(),
        ], 'layouts/admin');
    }

    public function updateGeneral(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();

        $this->settingsModel->set('site_name', $this->input('site_name', 'سیستم صدور فاکتور'));

        $logoError = $this->handleUpload('logo', 'site_logo', self::ALLOWED_IMAGE_MIME);
        $faviconError = $this->handleUpload('favicon', 'site_favicon', self::ALLOWED_ICON_MIME);

        if ($logoError || $faviconError) {
            setFlash('error', $logoError ?: $faviconError);
        } else {
            setFlash('success', 'تنظیمات هویت سایت بروزرسانی شد.');
        }
        redirect('/admin/settings');
    }

    public function updatePayment(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();

        $this->settingsModel->setMany([
            'card_number' => $this->input('card_number', ''),
            'card_holder_name' => $this->input('card_holder_name', ''),
        ]);

        setFlash('success', 'اطلاعات پرداخت بروزرسانی شد.');
        redirect('/admin/settings');
    }

    public function updateContact(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();

        $this->settingsModel->setMany([
            'contact_phone' => $this->input('contact_phone', ''),
            'contact_telegram' => $this->input('contact_telegram', ''),
            'contact_instagram' => $this->input('contact_instagram', ''),
        ]);

        setFlash('success', 'اطلاعات تماس بروزرسانی شد.');
        redirect('/admin/settings');
    }

    public function updateAdText(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();

        $this->settingsModel->set('invoice_ad_text', trim($this->input('invoice_ad_text', '')));

        setFlash('success', 'پیام پایانی فاکتور بروزرسانی شد.');
        redirect('/admin/settings');
    }

    private function handleUpload(string $fieldName, string $settingKey, array $allowedMime): ?string
    {
        $file = $_FILES[$fieldName] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // فایلی انتخاب نشده - نادیده گرفته می‌شود
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'خطا در آپلود فایل.';
        }
        if ($file['size'] > self::MAX_SIZE) {
            return 'حجم فایل نباید بیشتر از ۲ مگابایت باشد.';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedMime[$mime])) {
            return 'فرمت فایل مجاز نیست.';
        }

        $ext = $allowedMime[$mime];
        $filename = bin2hex(random_bytes(12)) . '.' . $ext;
        $brandingDir = UPLOAD_PATH . '/branding';
        if (!is_dir($brandingDir)) {
            mkdir($brandingDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $brandingDir . '/' . $filename)) {
            return 'خطا در ذخیره فایل روی سرور.';
        }

        $this->settingsModel->set($settingKey, $filename);
        return null;
    }
}
