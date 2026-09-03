-- فاکتورچی — اسکریپت کامل دیتابیس (schema + داده‌های اولیه)
-- اجرا: mysql -uroot < database/invoice_app_final.sql
CREATE DATABASE IF NOT EXISTS invoice_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE invoice_app;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'مدیر ارشد','mohammad12345sadeghi@gmail.com','$2b$12$39DcOwYI/oLK7JGp1muBS.H4.iSeWlsXWfLsrxkgyu5lHN6AR9EzC','super_admin',1,'2026-08-07 22:46:05');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `discount_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discount_codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `value` decimal(18,2) NOT NULL,
  `max_uses` int(10) unsigned DEFAULT NULL COMMENT 'خالی = نامحدود',
  `used_count` int(10) unsigned NOT NULL DEFAULT 0,
  `expires_at` date DEFAULT NULL COMMENT 'خالی = بدون انقضا',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `discount_codes` WRITE;
/*!40000 ALTER TABLE `discount_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `discount_codes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `unit_price` decimal(18,0) NOT NULL DEFAULT 0,
  `discount` decimal(18,0) NOT NULL DEFAULT 0,
  `row_total` decimal(18,0) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_date_shamsi` varchar(20) DEFAULT NULL COMMENT 'تاریخ شمسی به‌صورت دستی وارد می‌شود',
  `seller_name` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `template` varchar(20) NOT NULL DEFAULT 'classic' COMMENT 'قالب نمایش فاکتور: classic, modern, minimal',
  `hide_ad` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'مخفی کردن تبلیغ انتهای فاکتور (فقط پلن‌های مجاز)',
  `total_amount` decimal(18,0) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_invoice_number` (`invoice_number`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_hash` char(64) NOT NULL,
  `user_key` char(64) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 1,
  `locked_until` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ip_key` (`ip_hash`,`user_key`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `payment_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_receipts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `plan_id` int(10) unsigned DEFAULT NULL,
  `receipt_image` varchar(255) NOT NULL,
  `discount_code` varchar(50) DEFAULT NULL,
  `amount` decimal(18,0) DEFAULT NULL COMMENT 'مبلغی که کاربر باید پرداخت می‌کرده (پس از تخفیف)',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `fk_receipt_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_receipts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `payment_receipts` WRITE;
/*!40000 ALTER TABLE `payment_receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_receipts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'gem' COMMENT 'نام آیکون Bootstrap Icons بدون پیشوند bi-',
  `color` varchar(20) NOT NULL DEFAULT '#0D9488' COMMENT 'رنگ اختصاصی نمایش این پلن',
  `duration_months` int(10) unsigned NOT NULL DEFAULT 1 COMMENT 'برای پلن رایگان بی‌معناست (۰)',
  `price` decimal(18,0) NOT NULL DEFAULT 0 COMMENT 'قیمت نهایی (تومان)',
  `original_price` decimal(18,0) DEFAULT NULL COMMENT 'قیمت قبل از تخفیف - برای نمایش خط‌خورده (اختیاری)',
  `monthly_invoice_limit` int(10) unsigned DEFAULT NULL COMMENT 'سقف تعداد فاکتور در ماه؛ خالی = نامحدود',
  `allow_pdf` tinyint(1) NOT NULL DEFAULT 0,
  `allow_excel` tinyint(1) NOT NULL DEFAULT 0,
  `allow_image` tinyint(1) NOT NULL DEFAULT 1,
  `allow_edit` tinyint(1) NOT NULL DEFAULT 1,
  `allow_custom_templates` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'دسترسی به قالب‌های اختصاصی فاکتور',
  `allow_hide_ad` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'اجازه مخفی کردن تبلیغ انتهای فاکتور',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_free` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'پلن سیستمی رایگان - فقط یک ردیف، غیرقابل حذف',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (1,'رایگان','برای شروع و آشنایی با سیستم','gift','#94A3B8',0,0,NULL,3,0,0,1,1,0,0,0,1,1,'2026-08-07 22:46:05'),(2,'یک ماهه','برای استفاده کوتاه‌مدت','gem','#0D9488',1,80000,NULL,NULL,1,1,1,1,1,1,1,1,0,'2026-08-07 22:46:05'),(3,'سه ماهه','صرفه‌جویی بیشتر با تعهد سه‌ماهه','star','#0D9488',3,150000,240000,NULL,1,1,1,1,1,1,2,1,0,'2026-08-07 22:46:05'),(4,'شش ماهه','مناسب کسب‌وکارهای فعال','award','#0F766E',6,270000,480000,NULL,1,1,1,1,1,1,3,1,0,'2026-08-07 22:46:05'),(5,'یک ساله','بیشترین صرفه‌جویی سالانه','trophy','#0F766E',12,480000,960000,NULL,1,1,1,1,1,1,4,1,0,'2026-08-07 22:46:05');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sample_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sample_invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sample_invoices` WRITE;
/*!40000 ALTER TABLE `sample_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `sample_invoices` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('card_holder_name','نام صاحب کارت'),('card_number','۶۰۳۷-XXXX-XXXX-XXXX'),('contact_instagram','your_instagram_id'),('contact_phone','۰۹۱۲۰۰۰۰۰۰۰'),('contact_telegram','your_telegram_id'),('invoice_ad_text','این فاکتور با سامانه فاکتورچی صادر شده است — فاکتورهای حرفه‌ای خود را رایگان بسازید.'),('site_favicon',''),('site_logo','logo-placeholder.svg'),('site_name','فاکتورچی');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `plan_id` int(10) unsigned DEFAULT NULL,
  `status` enum('active','expired') NOT NULL DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user` (`user_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `store_name` varchar(255) DEFAULT NULL,
  `business_type` enum('shop','company') DEFAULT NULL,
  `quota_used` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'تعداد فاکتور ایجاد/ویرایش‌شده در ماه جاری (فقط کاربران رایگان)',
  `quota_month` char(7) DEFAULT NULL COMMENT 'ماهی که quota_used برای آن محاسبه شده - فرمت YYYY-MM',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

