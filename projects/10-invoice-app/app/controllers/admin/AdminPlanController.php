<?php

class AdminPlanController extends Controller
{
    private Plan $planModel;

    public function __construct()
    {
        parent::__construct();
        $this->planModel = new Plan();
    }

    public function index(): void
    {
        Auth::requireAdmin();
        $this->view('admin/plans/index', [
            'title' => 'مدیریت پلن‌های اشتراک',
            'plans' => $this->planModel->all(),
        ], 'layouts/admin');
    }

    public function showCreate(): void
    {
        Auth::requireAdmin();
        $this->view('admin/plans/form', [
            'title' => 'ایجاد پلن جدید', 'mode' => 'create', 'plan' => null,
        ], 'layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();

        [$errors, $data] = $this->validateInput();

        if (!empty($errors)) {
            $this->view('admin/plans/form', [
                'title' => 'ایجاد پلن جدید', 'mode' => 'create', 'plan' => $data, 'errors' => $errors,
            ], 'layouts/admin');
            return;
        }

        $this->planModel->create($data);
        setFlash('success', 'پلن جدید با موفقیت ایجاد شد.');
        redirect('/admin/plans');
    }

    public function showEdit(): void
    {
        Auth::requireAdmin();
        $id = (int) $this->input('id');
        $plan = $this->planModel->find($id);

        if (!$plan) {
            setFlash('error', 'پلن یافت نشد.');
            redirect('/admin/plans');
        }

        $this->view('admin/plans/form', [
            'title' => 'ویرایش پلن', 'mode' => 'edit', 'plan' => $plan,
        ], 'layouts/admin');
    }

    public function update(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        $existing = $this->planModel->find($id);
        if (!$existing) {
            setFlash('error', 'پلن یافت نشد.');
            redirect('/admin/plans');
        }

        [$errors, $data] = $this->validateInput($existing['is_free'] == 1);

        if (!empty($errors)) {
            $data['id'] = $id;
            $data['is_free'] = $existing['is_free'];
            $this->view('admin/plans/form', [
                'title' => 'ویرایش پلن', 'mode' => 'edit', 'plan' => $data, 'errors' => $errors,
            ], 'layouts/admin');
            return;
        }

        $this->planModel->update($id, $data);
        setFlash('success', 'پلن با موفقیت ویرایش شد.');
        redirect('/admin/plans');
    }

    public function toggleActive(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();

        if (!$this->planModel->toggleActive((int) $this->input('id'))) {
            setFlash('error', 'وضعیت پلن رایگان سیستمی قابل تغییر نیست.');
        } else {
            setFlash('success', 'وضعیت پلن بروزرسانی شد.');
        }
        redirect('/admin/plans');
    }

    public function delete(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();

        if (!$this->planModel->delete((int) $this->input('id'))) {
            setFlash('error', 'این پلن قابل حذف نیست (پلن رایگان سیستمی است یا یافت نشد).');
        } else {
            setFlash('success', 'پلن حذف شد. کاربران این پلن به پلن رایگان منتقل شدند.');
        }
        redirect('/admin/plans');
    }

    private function validateInput(bool $isFreeSystemPlan = false): array
    {
        $name = trim($this->input('name', ''));
        $description = trim($this->input('description', ''));
        $icon = trim($this->input('icon', 'gem')) ?: 'gem';
        $color = trim($this->input('color', '#0D9488')) ?: '#0D9488';
        $durationMonths = $isFreeSystemPlan ? 0 : max(1, (int) $this->input('duration_months', 1));
        $price = $isFreeSystemPlan ? 0 : (float) $this->input('price', 0);
        $originalPriceInput = trim($this->input('original_price', ''));
        $originalPrice = ($originalPriceInput !== '' && !$isFreeSystemPlan) ? (float) $originalPriceInput : null;
        $limitInput = trim($this->input('monthly_invoice_limit', ''));
        $monthlyLimit = $limitInput !== '' ? max(0, (int) $limitInput) : null;
        $sortOrder = (int) $this->input('sort_order', 0);

        $allowPdf = $this->input('allow_pdf') ? 1 : 0;
        $allowExcel = $this->input('allow_excel') ? 1 : 0;
        $allowImage = $this->input('allow_image') ? 1 : 0;
        $allowEdit = $this->input('allow_edit') ? 1 : 0;
        $allowCustomTemplates = $this->input('allow_custom_templates') ? 1 : 0;
        $allowHideAd = $this->input('allow_hide_ad') ? 1 : 0;

        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors[] = 'نام پلن باید حداقل ۲ کاراکتر باشد.';
        }
        if (!$isFreeSystemPlan && $durationMonths < 1) {
            $errors[] = 'مدت زمان پلن باید حداقل ۱ ماه باشد.';
        }
        if ($price < 0) {
            $errors[] = 'قیمت نامعتبر است.';
        }
        if ($originalPrice !== null && $originalPrice <= $price) {
            $errors[] = 'قیمت اصلی (قبل از تخفیف) باید بیشتر از قیمت نهایی باشد.';
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $color = '#0D9488';
        }

        return [$errors, [
            'name' => $name,
            'description' => $description ?: null,
            'icon' => $icon,
            'color' => $color,
            'duration_months' => $durationMonths,
            'price' => $price,
            'original_price' => $originalPrice,
            'monthly_invoice_limit' => $monthlyLimit,
            'allow_pdf' => $allowPdf,
            'allow_excel' => $allowExcel,
            'allow_image' => $allowImage,
            'allow_edit' => $allowEdit,
            'allow_custom_templates' => $allowCustomTemplates,
            'allow_hide_ad' => $allowHideAd,
            'sort_order' => $sortOrder,
        ]];
    }
}
