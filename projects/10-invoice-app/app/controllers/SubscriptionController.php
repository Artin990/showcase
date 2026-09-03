<?php

class SubscriptionController extends Controller
{
    private Subscription $subModel;
    private DiscountCode $discountModel;
    private Plan $planModel;

    private const ALLOWED_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_SIZE = 5 * 1024 * 1024; // 5MB

    public function __construct()
    {
        parent::__construct();
        $this->subModel = new Subscription();
        $this->discountModel = new DiscountCode();
        $this->planModel = new Plan();
    }

    public function index(): void
    {
        Auth::requireUser();
        $userId = Auth::userId();

        $this->view('subscription/plans', [
            'title'    => 'اشتراک',
            'sub'      => $this->subModel->getForUser($userId),
            'isPaid'   => $this->subModel->isPaidActive($userId),
            'daysLeft' => $this->subModel->daysLeft($userId),
            'receipts' => $this->subModel->receiptsForUser($userId),
            'hasPending' => $this->subModel->hasPendingReceipt($userId),
            'plans'    => $this->planModel->purchasable(),
        ], 'layouts/dashboard');
    }

    public function showPurchase(): void
    {
        Auth::requireUser();
        $planId = (int) $this->input('plan');
        $plan = $this->planModel->find($planId);

        if (!$plan || !$plan['is_active'] || $plan['is_free']) {
            redirect('/subscription');
        }

        $this->view('subscription/purchase', [
            'title' => 'خرید اشتراک',
            'plan'  => $plan,
            'price' => (float) $plan['price'],
        ], 'layouts/dashboard');
    }

    /** بررسی زنده (AJAX) اعتبار کد تخفیف - بدون ثبت مصرف */
    public function checkDiscount(): void
    {
        Auth::requireUser();
        header('Content-Type: application/json; charset=utf-8');

        // محدودسازی نرخ پرس‌وجو (یک بار در ثانیه برای هر کاربر) تا از یک پرس‌وجوی گسترده جلوگیری شود
        $now = time();
        if (($now - (int) ($_SESSION['discount_ts'] ?? 0)) < 1) {
            echo json_encode(['valid' => false, 'message' => 'لطفا کمی صبر کنید.']);
            return;
        }
        $_SESSION['discount_ts'] = $now;

        $planId = (int) $this->input('plan');
        $code = $this->input('code');
        $plan = $this->planModel->find($planId);

        if (!$plan) {
            echo json_encode(['valid' => false, 'message' => 'پلن نامعتبر است.']);
            return;
        }

        $discount = $this->discountModel->validateCode($code);

        if (!$discount) {
            echo json_encode(['valid' => false, 'message' => 'کد تخفیف نامعتبر، غیرفعال یا منقضی شده است.']);
            return;
        }

        $finalPrice = $this->discountModel->applyDiscount($discount, (float) $plan['price']);
        echo json_encode([
            'valid' => true,
            'final_price' => $finalPrice,
            'message' => 'کد تخفیف با موفقیت اعمال شد.',
        ]);
    }

    public function storePurchase(): void
    {
        Auth::requireUser();
        $this->verifyCsrfOrDie();

        $planId = (int) $this->input('plan');
        $plan = $this->planModel->find($planId);
        if (!$plan || !$plan['is_active'] || $plan['is_free']) {
            redirect('/subscription');
        }

        $price = (float) $plan['price'];
        $codeInput = trim($this->input('discount_code', ''));
        $appliedCode = null;
        $discountRow = null;

        if ($codeInput !== '') {
            $discountRow = $this->discountModel->validateCode($codeInput);
            if ($discountRow) {
                $price = $this->discountModel->applyDiscount($discountRow, $price);
                $appliedCode = $discountRow['code'];
            } else {
                // کد نامعتبر نباید بی‌سروصدا نادیده گرفته شود؛ به کاربر اطلاع می‌دهیم
                $this->view('subscription/purchase', [
                    'title' => 'خرید اشتراک', 'plan' => $plan, 'price' => (float) $plan['price'],
                    'error' => 'کد تخفیف وارد شده نامعتبر، غیرفعال یا منقضی است.',
                    'old_code' => $codeInput,
                ], 'layouts/dashboard');
                return;
            }
        }

        $error = null;
        $file = $_FILES['receipt'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $error = 'لطفا تصویر رسید پرداخت را انتخاب کنید.';
        } elseif ($file['size'] > self::MAX_SIZE) {
            $error = 'حجم فایل نباید بیشتر از ۵ مگابایت باشد.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!isset(self::ALLOWED_MIME[$mime])) {
                $error = 'فرمت فایل باید تصویر (JPG, PNG یا WEBP) باشد.';
            }
        }

        if ($error) {
            $this->view('subscription/purchase', [
                'title' => 'خرید اشتراک', 'plan' => $plan, 'price' => (float) $plan['price'], 'error' => $error,
            ], 'layouts/dashboard');
            return;
        }

        $ext = self::ALLOWED_MIME[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $receiptDir = UPLOAD_PATH . '/receipts';
        if (!is_dir($receiptDir)) {
            @mkdir($receiptDir, 0755, true);
        }
        $destination = $receiptDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->view('subscription/purchase', [
                'title' => 'خرید اشتراک', 'plan' => $plan, 'price' => (float) $plan['price'],
                'error' => 'خطا در آپلود فایل. دوباره تلاش کنید.',
            ], 'layouts/dashboard');
            return;
        }

        $this->subModel->createReceipt(Auth::userId(), $planId, $filename, $appliedCode, $price);

        if ($discountRow) {
            $this->discountModel->incrementUsage((int) $discountRow['id']);
        }

        setFlash('success', 'درخواست خرید شما با موفقیت ثبت شد. پس از بررسی توسط مدیر، اشتراک شما فعال خواهد شد.');
        redirect('/subscription');
    }
}
