<?php

class AdminAuthController extends Controller
{
    private Admin $adminModel;

    public function __construct()
    {
        parent::__construct();
        $this->adminModel = new Admin();
    }

    public function showLogin(): void
    {
        if (Auth::isAdminLoggedIn()) {
            redirect('/admin/dashboard');
        }
        $this->view('admin/login', ['title' => 'ورود مدیر'], 'layouts/auth');
    }

    public function login(): void
    {
        $this->verifyCsrfOrDie();

        $email = strtolower($this->input('email'));
        $password = $this->input('password');
        $throttleKey = 'admin:' . $email;

        if (Auth::isLockedOut($throttleKey)) {
            $wait = Auth::lockoutSecondsLeft($throttleKey);
            $this->view('admin/login', [
                'title' => 'ورود مدیر',
                'errors' => ["به دلیل تلاش‌های ناموفق زیاد، لطفا {$wait} ثانیه دیگر دوباره تلاش کنید."],
                'old' => ['email' => $email],
            ], 'layouts/auth');
            return;
        }

        $admin = $this->adminModel->findByEmail($email);

        if (!$admin || !$this->adminModel->verifyPassword($admin, $password)) {
            Auth::registerFailedAttempt($throttleKey);
            $this->view('admin/login', [
                'title' => 'ورود مدیر',
                'errors' => ['ایمیل یا رمز عبور اشتباه است.'],
                'old' => ['email' => $email],
            ], 'layouts/auth');
            return;
        }

        if (!$admin['is_active']) {
            Auth::registerFailedAttempt($throttleKey);
            $this->view('admin/login', [
                'title' => 'ورود مدیر',
                'errors' => ['این حساب مدیریتی غیرفعال شده است.'],
                'old' => ['email' => $email],
            ], 'layouts/auth');
            return;
        }

        Auth::clearLoginAttempts($throttleKey);
        Auth::loginAdmin($admin);
        redirect('/admin/dashboard');
    }

    public function logout(): void
    {
        $this->verifyCsrfOrDie();
        Auth::logoutAdmin();
        redirect('/admin/login');
    }
}
