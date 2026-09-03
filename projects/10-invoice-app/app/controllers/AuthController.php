<?php

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    // ---------------- ثبت‌نام ----------------

    public function showRegister(): void
    {
        if (Auth::isUserLoggedIn()) {
            redirect('/dashboard');
        }
        $this->view('auth/register', ['title' => 'ثبت‌نام'], 'layouts/auth');
    }

    public function register(): void
    {
        $this->verifyCsrfOrDie();

        $name         = $this->input('name');
        $email        = strtolower($this->input('email'));
        $phone        = $this->input('phone');
        $storeName    = $this->input('store_name');
        $businessType = $this->input('business_type');
        $password     = $this->input('password');
        $confirm      = $this->input('password_confirm');

        $errors = [];

        if (mb_strlen($name) < 3) {
            $errors[] = 'نام باید حداقل ۳ کاراکتر باشد.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'ایمیل وارد شده معتبر نیست.';
        } elseif ($this->userModel->emailExists($email)) {
            $errors[] = 'این ایمیل قبلا ثبت شده است.';
        }
        if (mb_strlen(trim($phone)) < 8) {
            $errors[] = 'شماره موبایل الزامی است و باید معتبر باشد.';
        }
        if (mb_strlen(trim($storeName)) < 2) {
            $errors[] = 'نام فروشگاه یا شرکت الزامی است.';
        }
        if (!in_array($businessType, ['shop', 'company'], true)) {
            $errors[] = 'لطفا نوع فعالیت را انتخاب کنید.';
        }
        foreach (Auth::passwordIssues($password) as $issue) {
            $errors[] = $issue;
        }
        if ($password !== $confirm) {
            $errors[] = 'تکرار رمز عبور مطابقت ندارد.';
        }

        if (!empty($errors)) {
            $this->view('auth/register', [
                'title'  => 'ثبت‌نام',
                'errors' => $errors,
                'old'    => ['name' => $name, 'email' => $email, 'phone' => $phone, 'store_name' => $storeName, 'business_type' => $businessType],
            ], 'layouts/auth');
            return;
        }

        $userId = $this->userModel->create($name, $email, $password, $phone, $storeName, $businessType);
        $user = $this->userModel->findById($userId);

        Auth::loginUser($user);
        setFlash('success', 'ثبت‌نام با موفقیت انجام شد. خوش آمدید!');
        redirect('/dashboard');
    }

    // ---------------- ورود ----------------

    public function showLogin(): void
    {
        if (Auth::isUserLoggedIn()) {
            redirect('/dashboard');
        }
        $this->view('auth/login', ['title' => 'ورود به حساب'], 'layouts/auth');
    }

    public function login(): void
    {
        $this->verifyCsrfOrDie();

        $email       = strtolower($this->input('email'));
        $password    = $this->input('password');
        $throttleKey = 'user:' . $email;

        if (Auth::isLockedOut($throttleKey)) {
            $wait = Auth::lockoutSecondsLeft($throttleKey);
            $this->view('auth/login', [
                'title'  => 'ورود به حساب',
                'errors' => ["به دلیل تلاش‌های ناموفق زیاد، لطفا {$wait} ثانیه دیگر دوباره تلاش کنید."],
                'old'    => ['email' => $email],
            ], 'layouts/auth');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($user, $password)) {
            Auth::registerFailedAttempt($throttleKey);
            $this->view('auth/login', [
                'title'  => 'ورود به حساب',
                'errors' => ['ایمیل یا رمز عبور اشتباه است.'],
                'old'    => ['email' => $email],
            ], 'layouts/auth');
            return;
        }

        if ((int) $user['is_active'] === 0) {
            $this->view('auth/login', [
                'title'  => 'ورود به حساب',
                'errors' => ['حساب کاربری شما غیرفعال شده است. با پشتیبانی تماس بگیرید.'],
                'old'    => ['email' => $email],
            ], 'layouts/auth');
            return;
        }

        Auth::clearLoginAttempts($throttleKey);
        Auth::loginUser($user);
        redirect('/dashboard');
    }

    public function logout(): void
    {
        $this->verifyCsrfOrDie();
        Auth::logoutUser();
        redirect('/login');
    }

    // ---------------- فراموشی رمز عبور ----------------

    public function showForgotPassword(): void
    {
        $this->view('auth/forgot-password', ['title' => 'فراموشی رمز عبور'], 'layouts/auth');
    }

    public function forgotPassword(): void
    {
        $this->verifyCsrfOrDie();
        $email = strtolower($this->input('email'));

        // محدودسازی نرخ درخواست (IP + ایمیل) برای جلوگیری از ایمیل‌بمب و چرخش توکن
        $throttleKey = 'forgot:' . $email;
        if (Auth::isLockedOut($throttleKey)) {
            sleep(2);
            setFlash('success', 'اگر این ایمیل در سیستم ثبت شده باشد، لینک بازیابی رمز عبور برایتان ارسال می‌شود.');
            redirect('/forgot-password');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        // برای جلوگیری از افشای اینکه چه ایمیل‌هایی ثبت‌نام کرده‌اند،
        // همیشه همین پیام موفقیت نمایش داده می‌شود.
        $genericMessage = 'اگر این ایمیل در سیستم ثبت شده باشد، لینک بازیابی رمز عبور برایتان ارسال می‌شود.';

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . RESET_TOKEN_LIFETIME_MIN . ' minutes'));
            // فقط هش توکن در دیتابیس ذخیره می‌شود (در صورت افشای دیتابیس، توکن‌ها قابل استفاده نیستند)
            $this->userModel->setResetToken($user['id'], $token, $expiresAt);
            Auth::registerFailedAttempt($throttleKey);

            $resetLink = APP_URL . '/reset-password?token=' . $token;

            // تلاش برای ارسال ایمیل با تابع داخلی PHP mail()
            // نکته: روی اکثر هاست‌های cPanel باید یک حساب ایمیل با همین دامنه بسازید
            // و SPF/DKIM را در بخش Email Deliverability فعال کنید تا ایمیل‌ها اسپم نشوند.
            $subject = 'بازیابی رمز عبور - ' . APP_NAME;
            $message = "برای بازیابی رمز عبور خود روی لینک زیر کلیک کنید:\n" . $resetLink .
                       "\n\nاین لینک تا " . RESET_TOKEN_LIFETIME_MIN . " دقیقه دیگر معتبر است.";
            $headers = 'From: no-reply@' . parse_url(APP_URL, PHP_URL_HOST);
            @mail($user['email'], $subject, $message, $headers);

            // فقط در محیط توسعه محلی (APP_DEBUG=true یعنی درخواست از 127.0.0.1) لینک نمایش داده می‌شود
            if (APP_DEBUG) {
                setFlash('debug_reset_link', $resetLink);
            }
        }

        setFlash('success', $genericMessage);
        redirect('/forgot-password');
    }

    public function showResetPassword(): void
    {
        $token = $this->input('token');
        $user = $token ? $this->userModel->findByValidResetToken($token) : null;

        if (!$user) {
            setFlash('error', 'لینک بازیابی رمز عبور نامعتبر یا منقضی شده است.');
            redirect('/forgot-password');
        }

        $this->view('auth/reset-password', [
            'title' => 'تعیین رمز عبور جدید',
            'token' => $token,
        ], 'layouts/auth');
    }

    public function resetPassword(): void
    {
        $this->verifyCsrfOrDie();
        $token = $this->input('token');
        $password = $this->input('password');
        $confirm = $this->input('password_confirm');

        $user = $token ? $this->userModel->findByValidResetToken($token) : null;

        if (!$user) {
            setFlash('error', 'لینک بازیابی رمز عبور نامعتبر یا منقضی شده است.');
            redirect('/forgot-password');
        }

        $errors = [];
        foreach (Auth::passwordIssues($password) as $issue) {
            $errors[] = $issue;
        }
        if ($password !== $confirm) {
            $errors[] = 'تکرار رمز عبور مطابقت ندارد.';
        }

        if (!empty($errors)) {
            $this->view('auth/reset-password', [
                'title'  => 'تعیین رمز عبور جدید',
                'token'  => $token,
                'errors' => $errors,
            ], 'layouts/auth');
            return;
        }

        $this->userModel->updatePassword($user['id'], $password);
        $this->userModel->clearResetToken($user['id']);

        setFlash('success', 'رمز عبور با موفقیت تغییر کرد. اکنون می‌توانید وارد شوید.');
        redirect('/login');
    }

    // ---------------- ویرایش پروفایل ----------------

    public function showProfile(): void
    {
        Auth::requireUser();
        $user = $this->userModel->findById(Auth::userId());
        $this->view('dashboard/profile', ['title' => 'ویرایش پروفایل', 'user' => $user], 'layouts/dashboard');
    }

    public function updateProfile(): void
    {
        Auth::requireUser();
        $this->verifyCsrfOrDie();

        $name = $this->input('name');
        $phone = $this->input('phone');
        $storeName = $this->input('store_name');
        $businessType = $this->input('business_type');

        $errors = [];
        if (mb_strlen($name) < 3) {
            $errors[] = 'نام باید حداقل ۳ کاراکتر باشد.';
        }
        if (mb_strlen(trim($phone)) < 8) {
            $errors[] = 'شماره موبایل الزامی است و باید معتبر باشد.';
        }
        if (mb_strlen(trim($storeName)) < 2) {
            $errors[] = 'نام فروشگاه یا شرکت الزامی است.';
        }
        if (!in_array($businessType, ['shop', 'company'], true)) {
            $errors[] = 'لطفا نوع فعالیت را انتخاب کنید.';
        }

        if (!empty($errors)) {
            $user = $this->userModel->findById(Auth::userId());
            $this->view('dashboard/profile', [
                'title' => 'ویرایش پروفایل', 'user' => $user, 'errors' => $errors,
            ], 'layouts/dashboard');
            return;
        }

        $this->userModel->updateProfile(Auth::userId(), $name, $phone, $storeName, $businessType);
        setFlash('success', 'اطلاعات پروفایل بروزرسانی شد.');
        redirect('/profile');
    }

    public function changePassword(): void
    {
        Auth::requireUser();
        $this->verifyCsrfOrDie();

        $current = $this->input('current_password');
        $new = $this->input('new_password');
        $confirm = $this->input('new_password_confirm');

        $user = $this->userModel->findById(Auth::userId());
        $errors = [];

        if (!$this->userModel->verifyPassword($user, $current)) {
            $errors[] = 'رمز عبور فعلی اشتباه است.';
        }
        foreach (Auth::passwordIssues($new) as $issue) {
            $errors[] = $issue;
        }
        if ($new !== $confirm) {
            $errors[] = 'تکرار رمز عبور جدید مطابقت ندارد.';
        }

        if (!empty($errors)) {
            $this->view('dashboard/profile', [
                'title' => 'ویرایش پروفایل', 'user' => $user, 'errors' => $errors,
            ], 'layouts/dashboard');
            return;
        }

        $this->userModel->updatePassword($user['id'], $new);
        setFlash('success', 'رمز عبور با موفقیت تغییر کرد.');
        redirect('/profile');
    }
}