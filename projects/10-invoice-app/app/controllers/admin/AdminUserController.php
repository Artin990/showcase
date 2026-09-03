<?php

class AdminUserController extends Controller
{
    private User $userModel;
    private Subscription $subModel;
    private Invoice $invoiceModel;
    private Plan $planModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->subModel = new Subscription();
        $this->invoiceModel = new Invoice();
        $this->planModel = new Plan();
    }

    public function index(): void
    {
        Auth::requireAdmin();
        $search = $this->input('q', '');
        $page = max(1, (int) $this->input('page', 1));

        $this->view('admin/users/index', [
            'title'  => 'مدیریت کاربران',
            'result' => $this->userModel->adminPaginate($search, $page),
            'search' => $search,
        ], 'layouts/admin');
    }

    public function show(): void
    {
        Auth::requireAdmin();
        $id = (int) $this->input('id');
        $user = $this->userModel->findById($id);

        if (!$user) {
            setFlash('error', 'کاربر یافت نشد.');
            redirect('/admin/users');
        }

        $invoicePage = max(1, (int) $this->input('ipage', 1));

        $this->view('admin/users/show', [
            'title'    => 'جزئیات کاربر',
            'user'     => $user,
            'sub'      => $this->subModel->getForUser($id),
            'receipts' => $this->subModel->receiptsForUser($id),
            'invoiceResult' => $this->invoiceModel->adminPaginateAll('', $invoicePage, $id),
            'plans'    => $this->planModel->allActive(),
        ], 'layouts/admin');
    }

    public function updateInfo(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        $name = $this->input('name');
        $email = strtolower($this->input('email'));
        $phone = $this->input('phone');
        $storeName = $this->input('store_name');
        $businessType = $this->input('business_type');

        $errors = [];
        if (mb_strlen($name) < 3) {
            $errors[] = 'نام باید حداقل ۳ کاراکتر باشد.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'ایمیل معتبر نیست.';
        } else {
            $existing = $this->userModel->findByEmail($email);
            if ($existing && (int) $existing['id'] !== $id) {
                $errors[] = 'این ایمیل برای کاربر دیگری ثبت شده است.';
            }
        }
        if (!in_array($businessType, ['shop', 'company'], true)) {
            $businessType = null;
        }

        if (!empty($errors)) {
            setFlash('error', implode(' ', $errors));
            redirect('/admin/users/view?id=' . $id);
        }

        $this->userModel->adminUpdate($id, $name, $email, $phone, $storeName, $businessType);
        setFlash('success', 'اطلاعات کاربر بروزرسانی شد.');
        redirect('/admin/users/view?id=' . $id);
    }

    public function toggleActive(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        $this->userModel->toggleActive($id);
        setFlash('success', 'وضعیت کاربر بروزرسانی شد.');
        redirect('/admin/users/view?id=' . $id);
    }

    public function updateSubscription(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();

        $id = (int) $this->input('id');
        $planId = (int) $this->input('plan_id', 0);
        $status = $this->input('status');
        $endDateJalali = trim($this->input('end_date_shamsi', ''));
        $endDate = $endDateJalali !== '' ? fromJalali($endDateJalali) : null;

        if ($planId <= 0 || !$this->planModel->find($planId)) {
            $freePlan = $this->planModel->getFreePlan();
            $planId = $freePlan['id'] ?? $planId;
        }
        if (!in_array($status, ['active', 'expired'], true)) {
            $status = 'active';
        }

        $this->subModel->adminSetSubscription($id, $planId, $status, $endDate);

        setFlash('success', 'اشتراک کاربر با موفقیت بروزرسانی شد.');
        redirect('/admin/users/view?id=' . $id);
    }

    /** محاسبه تاریخ پایان پیشنهادی (شمسی) بر اساس تعداد ماه - برای پرکردن خودکار فیلد در پنل مدیریت */
    public function calcEndDate(): void
    {
        Auth::requireAdmin();
        header('Content-Type: application/json; charset=utf-8');

        $months = max(1, (int) $this->input('months', 1));
        $userId = (int) $this->input('user_id', 0);

        // تاریخ شروع تمدید: حداکثرِ امروز و تاریخ پایان اشتراک فعلی (تا تداخلی ایجاد نشود)
        $base = date('Y-m-d');
        if ($userId > 0) {
            $sub = $this->subModel->getForUser($userId);
            if (!empty($sub['end_date']) && strtotime($sub['end_date']) > strtotime($base)) {
                $base = date('Y-m-d', strtotime($sub['end_date']));
            }
        }
        $gregorian = date('Y-m-d', strtotime("+{$months} months", strtotime($base)));

        echo json_encode(['date' => toJalali($gregorian)]);
    }
}
