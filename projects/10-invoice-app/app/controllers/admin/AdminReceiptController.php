<?php

class AdminReceiptController extends Controller
{
    private Subscription $subModel;

    public function __construct()
    {
        parent::__construct();
        $this->subModel = new Subscription();
    }

    public function index(): void
    {
        Auth::requireAdmin();
        $status = $this->input('status', 'pending');
        $page = max(1, (int) $this->input('page', 1));

        if (!in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $this->view('admin/receipts/index', [
            'title'  => 'رسیدهای پرداخت',
            'result' => $this->subModel->adminAllReceipts($status === 'all' ? null : $status, $page),
            'status' => $status,
        ], 'layouts/admin');
    }

    public function approve(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        if ($this->subModel->approveReceipt($id)) {
            setFlash('success', 'رسید تایید و اشتراک کاربر فعال شد.');
        } else {
            setFlash('error', 'خطا در تایید رسید (ممکن است قبلا بررسی شده باشد).');
        }
        redirect('/admin/receipts');
    }

    public function reject(): void
    {
        Auth::requireAdmin();
        $this->verifyCsrfOrDie();
        $id = (int) $this->input('id');

        if ($this->subModel->rejectReceipt($id)) {
            setFlash('success', 'رسید رد شد.');
        } else {
            setFlash('error', 'خطا در رد رسید (ممکن است قبلا بررسی شده باشد).');
        }
        redirect('/admin/receipts');
    }
}
