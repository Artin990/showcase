<?php

class ContactController extends Controller
{
    public function index(): void
    {
        $data = [
            'title' => 'تماس با ما',
            'phone' => setting('contact_phone'),
            'telegram' => setting('contact_telegram'),
            'instagram' => setting('contact_instagram'),
        ];

        if (Auth::isUserLoggedIn()) {
            $this->view('contact/index', $data, 'layouts/dashboard');
        } else {
            $this->view('contact/index', $data, 'layouts/landing');
        }
    }
}
