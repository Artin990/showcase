<?php

class LandingController extends Controller
{
    public function index(): void
    {
        // اگر کاربر وارد شده باشد، مستقیما به داشبورد هدایت می‌شود و لندینگ را نمی‌بیند
        if (Auth::isUserLoggedIn()) {
            redirect('/dashboard');
        }

        $sampleModel = new SampleInvoice();
        $planModel = new Plan();

        $this->view('landing/index', [
            'title' => setting('site_name', APP_NAME),
            'samples' => $sampleModel->all(),
            'plans' => $planModel->purchasable(),
        ], 'layouts/landing');
    }
}
