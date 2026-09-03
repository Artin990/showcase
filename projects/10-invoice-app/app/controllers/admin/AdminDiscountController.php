<?php

class AdminDiscountController extends Controller
{
    private DiscountCode $discountModel;

    public function __construct()
    {
        parent::__construct();
        $this->discountModel = new DiscountCode();
    }

    public function index(): void
    {
        Auth::requireAdmin();
        $this->view('admin/discounts/index', [
            'title' => 'کدهای تخفیف',
            'codes' => $this->discountModel->all(),
        ], 'layouts/admin');
    }

    public function showCreate(): void
    {
        Auth::requireAdmin();
        $this->view('admin/discounts/form', [
            'title' => 'ایجاد کد تخفیف', 'mode' => 'create', 'code' => null,
        ], 'layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();

        [$errors, $data] = $this->validateInput();

        if (!empty($errors)) {
            $this->view('admin/discounts/form', [
                'title' => 'ایجاد کد تخفیف', 'mode' => 'create', 'code' => $data, 'errors' => $errors,
            ], 'layouts/admin');
            return;
        }

        if ($this->discountModel->findByCode($data['code'])) {
            $this->view('admin/discounts/form', [
                'title' => 'ایجاد کد تخفیف', 'mode' => 'create', 'code' => $data,
                'errors' => ['این کد تخفیف قبلا ثبت شده است.'],
            ], 'layouts/admin');
            return;
        }

        $this->discountModel->create($data['code'], $data['type'], $data['value'], $data['max_uses'], $data['expires_at']);
        setFlash('success', 'کد تخفیف با موفقیت ایجاد شد.');
        redirect('/admin/discounts');
    }

    public function showEdit(): void
    {
        Auth::requireAdmin();
        $id = (int) $this->input('id');
        $code = $this->discountModel->find($id);

        if (!$code) {
            setFlash('error', 'کد تخفیف یافت نشد.');
            redirect('/admin/discounts');
        }

        $this->view('admin/discounts/form', [
            'title' => 'ویرایش کد تخفیف', 'mode' => 'edit', 'code' => $code,
        ], 'layouts/admin');
    }

    public function update(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        [$errors, $data] = $this->validateInput();

        if (empty($errors)) {
            // بررسی تکراری‌نبودن کد در کدهای دیگر (به‌جز همین کد فعلی)
            $existing = $this->discountModel->findByCode($data['code']);
            if ($existing && (int) $existing['id'] !== $id) {
                $errors[] = 'این کد تخفیف قبلا ثبت شده است.';
            }
        }

        if (!empty($errors)) {
            $data['id'] = $id;
            $this->view('admin/discounts/form', [
                'title' => 'ویرایش کد تخفیف', 'mode' => 'edit', 'code' => $data, 'errors' => $errors,
            ], 'layouts/admin');
            return;
        }

        $this->discountModel->update($id, $data['code'], $data['type'], $data['value'], $data['max_uses'], $data['expires_at']);
        setFlash('success', 'کد تخفیف با موفقیت ویرایش شد.');
        redirect('/admin/discounts');
    }

    public function toggleActive(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();
        $this->discountModel->toggleActive((int) $this->input('id'));
        setFlash('success', 'وضعیت کد تخفیف بروزرسانی شد.');
        redirect('/admin/discounts');
    }

    public function delete(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();
        $this->discountModel->delete((int) $this->input('id'));
        setFlash('success', 'کد تخفیف حذف شد.');
        redirect('/admin/discounts');
    }

    private function validateInput(): array
    {
        $code = strtoupper(trim($this->input('code', '')));
        $type = $this->input('type', 'percent');
        $value = (float) $this->input('value', 0);
        $maxUses = $this->input('max_uses', '');

        $errors = [];
        if (mb_strlen($code) < 3) {
            $errors[] = 'کد تخفیف باید حداقل ۳ کاراکتر باشد.';
        }
        if (!in_array($type, ['percent', 'fixed'], true)) {
            $type = 'percent';
        }
        if ($value <= 0 || ($type === 'percent' && $value > 100)) {
            $errors[] = 'مقدار تخفیف نامعتبر است.';
        }

        $expiresAtJalali = trim($this->input('expires_at', ''));
        $expiresAt = $expiresAtJalali !== '' ? fromJalali($expiresAtJalali) : null;
        if ($expiresAtJalali !== '' && $expiresAt === null) {
            $errors[] = 'تاریخ انقضا معتبر نیست (فرمت صحیح: ۱۴۰۴/۰۶/۱۰).';
        }

        $data = [
            'code' => $code,
            'type' => $type,
            'value' => $value,
            'max_uses' => ($maxUses !== '' && (int) $maxUses > 0) ? (int) $maxUses : null,
            'expires_at' => $expiresAt,
        ];

        return [$errors, $data];
    }
}
