<?php

class AdminDashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();

        $userModel = new User();
        $invoiceModel = new Invoice();
        $subModel = new Subscription();
        $discountModel = new DiscountCode();

        $this->view('admin/dashboard', [
            'title' => 'داشبورد مدیریت',
            'userCount' => $userModel->countAll(),
            'invoiceCount' => $invoiceModel->countAll(),
            'pendingReceipts' => $subModel->pendingReceiptsCount(),
            'activeDiscountCount' => $discountModel->countActive(),
        ], 'layouts/admin');
    }
}
