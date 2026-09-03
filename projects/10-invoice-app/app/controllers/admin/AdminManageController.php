<?php

class AdminManageController extends Controller
{
    private Admin $adminModel;

    public function __construct()
    {
        parent::__construct();
        $this->adminModel = new Admin();
    }

    public function index(): void
    {
        Auth::requireSuperAdmin();
        $this->view('admin/admins/index', [
            'title' => 'مدیریت حساب‌های مدیر',
            'admins' => $this->adminModel->all(),
        ], 'layouts/admin');
    }

    public function showCreate(): void
    {
        Auth::requireSuperAdmin();
        $this->view('admin/admins/form', ['title' => 'افزودن مدیر جدید'], 'layouts/admin');
    }

    public function store(): void
    {
        Auth::requireSuperAdmin();
        $this->verifyCsrfOrDie();

        $name = $this->input('name');
        $email = strtolower($this->input('email'));
        $password = $this->input('password');

        $errors = [];
        if (mb_strlen($name) < 3) {
            $errors[] = 'نام باید حداقل ۳ کاراکتر باشد.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'ایمیل معتبر نیست.';
        } elseif ($this->adminModel->emailExists($email)) {
            $errors[] = 'این ایمیل قبلا برای یک مدیر ثبت شده است.';
        }
        if (mb_strlen($password) < 8) {
            $errors[] = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
        }
        foreach (Auth::passwordIssues($password) as $issue) {
            $errors[] = $issue;
        }

        if (!empty($errors)) {
            $this->view('admin/admins/form', [
                'title' => 'افزودن مدیر جدید', 'errors' => $errors,
                'old' => ['name' => $name, 'email' => $email],
            ], 'layouts/admin');
            return;
        }

        $this->adminModel->create($name, $email, $password, 'admin');
        setFlash('success', 'مدیر جدید با موفقیت ایجاد شد.');
        redirect('/admin/admins');
    }

    public function toggleActive(): void
    {
        Auth::requireSuperAdmin();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        if ($id === Auth::adminId()) {
            setFlash('error', 'نمی‌توانید حساب خودتان را غیرفعال کنید.');
            redirect('/admin/admins');
        }

        $target = $this->adminModel->find($id);
        if ($target && $target['role'] === 'super_admin') {
            setFlash('error', 'غیرفعال‌سازی مدیر ارشد امکان‌پذیر نیست.');
            redirect('/admin/admins');
        }

        $this->adminModel->toggleActive($id);
        setFlash('success', 'وضعیت مدیر بروزرسانی شد.');
        redirect('/admin/admins');
    }

    public function delete(): void
    {
        Auth::requireSuperAdmin();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        if ($id === Auth::adminId()) {
            setFlash('error', 'نمی‌توانید حساب خودتان را حذف کنید.');
            redirect('/admin/admins');
        }

        $target = $this->adminModel->find($id);
        if ($target && $target['role'] === 'super_admin') {
            setFlash('error', 'حذف مدیر ارشد امکان‌پذیر نیست.');
            redirect('/admin/admins');
        }

        $this->adminModel->delete($id);
        setFlash('success', 'مدیر حذف شد.');
        redirect('/admin/admins');
    }
}
