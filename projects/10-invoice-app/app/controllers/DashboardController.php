<?php

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireUser();
        $invoiceModel = new Invoice();
        $userModel = new User();
        $subModel = new Subscription();

        $isPaid = $subModel->isPaidActive(Auth::userId());
        $features = $subModel->getPlanFeatures(Auth::userId());
        $quotaStatus = $userModel->getQuotaStatus(Auth::userId());

        $this->view('dashboard/home', [
            'title'        => 'داشبورد',
            'userName'     => $_SESSION['user_name'] ?? '',
            'invoiceCount' => $invoiceModel->countForUser(Auth::userId()),
            'recentInvoices' => $invoiceModel->recentForUser(Auth::userId(), 5),
            'isPaid'       => $isPaid,
            'planName'     => $features['plan_name'] ?? ($isPaid ? 'پولی' : 'رایگان'),
            // کارت سهمیه فقط وقتی نمایش داده می‌شود که پلن کاربر (رایگان یا پولی) واقعاً سقفی داشته باشد
            'quota'        => $quotaStatus['unlimited'] ? null : $quotaStatus,
        ], 'layouts/dashboard');
    }
}
