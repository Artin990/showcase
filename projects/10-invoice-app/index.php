<?php
/**
 * نقطه ورود اصلی برنامه
 * تمام درخواست‌ها (به لطف .htaccess) به این فایل هدایت می‌شوند
 */

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/core/helpers.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Auth.php';
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/Router.php';

// بارگذاری خودکار مدل‌ها و کنترلرها
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
        APP_PATH . '/controllers/admin/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

Auth::startSession();

$router = new Router();

// ---------------- صفحه اصلی (Landing / Dashboard) ----------------
$router->get('/', fn() => (new LandingController())->index());
$router->get('/contact', fn() => (new ContactController())->index());

// ---------------- مسیرهای احراز هویت ----------------
$router->get('/register', fn() => (new AuthController())->showRegister());
$router->post('/register', fn() => (new AuthController())->register());

$router->get('/login', fn() => (new AuthController())->showLogin());
$router->post('/login', fn() => (new AuthController())->login());

$router->post('/logout', fn() => (new AuthController())->logout());

$router->get('/forgot-password', fn() => (new AuthController())->showForgotPassword());
$router->post('/forgot-password', fn() => (new AuthController())->forgotPassword());

$router->get('/reset-password', fn() => (new AuthController())->showResetPassword());
$router->post('/reset-password', fn() => (new AuthController())->resetPassword());

// ---------------- پروفایل ----------------
$router->get('/profile', fn() => (new AuthController())->showProfile());
$router->post('/profile', fn() => (new AuthController())->updateProfile());
$router->post('/profile/change-password', fn() => (new AuthController())->changePassword());

// ---------------- داشبورد ----------------
$router->get('/dashboard', fn() => (new DashboardController())->index());

// ---------------- فاکتورها ----------------
$router->get('/invoices', fn() => (new InvoiceController())->index());
$router->get('/invoices/create', fn() => (new InvoiceController())->showCreate());
$router->post('/invoices/store', fn() => (new InvoiceController())->store());
$router->get('/invoices/edit', fn() => (new InvoiceController())->showEdit());
$router->post('/invoices/update', fn() => (new InvoiceController())->update());
$router->get('/invoices/view', fn() => (new InvoiceController())->show());
$router->post('/invoices/delete', fn() => (new InvoiceController())->delete());
$router->get('/invoices/export-excel', fn() => (new InvoiceController())->exportExcel());

// ---------------- اشتراک ----------------
$router->get('/subscription', fn() => (new SubscriptionController())->index());
$router->get('/subscription/purchase', fn() => (new SubscriptionController())->showPurchase());
$router->post('/subscription/purchase', fn() => (new SubscriptionController())->storePurchase());
$router->get('/subscription/check-discount', fn() => (new SubscriptionController())->checkDiscount());

// ---------------- پنل مدیریت ----------------
$router->get('/admin', fn() => redirect('/admin/login'));
$router->get('/admin/login', fn() => (new AdminAuthController())->showLogin());
$router->post('/admin/login', fn() => (new AdminAuthController())->login());
$router->post('/admin/logout', fn() => (new AdminAuthController())->logout());

$router->get('/admin/dashboard', fn() => (new AdminDashboardController())->index());

$router->get('/admin/users', fn() => (new AdminUserController())->index());
$router->get('/admin/users/view', fn() => (new AdminUserController())->show());
$router->post('/admin/users/toggle-active', fn() => (new AdminUserController())->toggleActive());
$router->post('/admin/users/update-subscription', fn() => (new AdminUserController())->updateSubscription());
$router->post('/admin/users/update-info', fn() => (new AdminUserController())->updateInfo());
$router->get('/admin/users/calc-end-date', fn() => (new AdminUserController())->calcEndDate());

$router->get('/admin/receipts', fn() => (new AdminReceiptController())->index());
$router->post('/admin/receipts/approve', fn() => (new AdminReceiptController())->approve());
$router->post('/admin/receipts/reject', fn() => (new AdminReceiptController())->reject());

$router->get('/admin/invoices/view', fn() => (new AdminInvoiceController())->show());
$router->get('/admin/invoices/edit', fn() => (new AdminInvoiceController())->showEdit());
$router->post('/admin/invoices/update', fn() => (new AdminInvoiceController())->update());
$router->post('/admin/invoices/delete', fn() => (new AdminInvoiceController())->delete());
$router->get('/admin/invoices/export-excel', fn() => (new AdminInvoiceController())->exportExcel());

$router->get('/admin/discounts', fn() => (new AdminDiscountController())->index());
$router->get('/admin/discounts/create', fn() => (new AdminDiscountController())->showCreate());
$router->post('/admin/discounts/store', fn() => (new AdminDiscountController())->store());
$router->get('/admin/discounts/edit', fn() => (new AdminDiscountController())->showEdit());
$router->post('/admin/discounts/update', fn() => (new AdminDiscountController())->update());
$router->post('/admin/discounts/toggle-active', fn() => (new AdminDiscountController())->toggleActive());
$router->post('/admin/discounts/delete', fn() => (new AdminDiscountController())->delete());

$router->get('/admin/plans', fn() => (new AdminPlanController())->index());
$router->get('/admin/plans/create', fn() => (new AdminPlanController())->showCreate());
$router->post('/admin/plans/store', fn() => (new AdminPlanController())->store());
$router->get('/admin/plans/edit', fn() => (new AdminPlanController())->showEdit());
$router->post('/admin/plans/update', fn() => (new AdminPlanController())->update());
$router->post('/admin/plans/toggle-active', fn() => (new AdminPlanController())->toggleActive());
$router->post('/admin/plans/delete', fn() => (new AdminPlanController())->delete());

$router->get('/admin/settings', fn() => (new AdminSettingsController())->index());
$router->post('/admin/settings/general', fn() => (new AdminSettingsController())->updateGeneral());
$router->post('/admin/settings/payment', fn() => (new AdminSettingsController())->updatePayment());
$router->post('/admin/settings/contact', fn() => (new AdminSettingsController())->updateContact());
$router->post('/admin/settings/ad-text', fn() => (new AdminSettingsController())->updateAdText());

$router->get('/admin/samples', fn() => (new AdminSampleInvoiceController())->index());
$router->post('/admin/samples/store', fn() => (new AdminSampleInvoiceController())->store());
$router->post('/admin/samples/delete', fn() => (new AdminSampleInvoiceController())->delete());

$router->get('/admin/admins', fn() => (new AdminManageController())->index());
$router->get('/admin/admins/create', fn() => (new AdminManageController())->showCreate());
$router->post('/admin/admins/store', fn() => (new AdminManageController())->store());
$router->post('/admin/admins/toggle-active', fn() => (new AdminManageController())->toggleActive());
$router->post('/admin/admins/delete', fn() => (new AdminManageController())->delete());

$router->dispatch();
