<section class="hero-section">
    <div class="hero-inner">
        <div class="hero-text reveal">
            <span class="badge"><i data-lucide="star"></i> <?= e(setting('site_name', APP_NAME)) ?> — صدور فاکتور آنلاین</span>
            <h1>صدور فاکتور حرفه‌ای،<br>فقط در چند ثانیه</h1>
            <p>فاکتورهای دقیق و حرفه‌ای بسازید، تخفیف هر ردیف رو خودکار حساب کنید، و خروجی تصویر، PDF یا اکسل بگیرید — همه در یک پنل ساده و سریع.</p>
            <div class="hero-cta">
                <a href="<?= APP_URL ?>/register" class="btn btn-primary btn-lg"><i data-lucide="rocket"></i> شروع رایگان</a>
                <a href="#samples" class="btn btn-outline btn-lg"><i data-lucide="eye"></i> مشاهده نمونه فاکتور</a>
            </div>
            <div class="hero-trust">
                <span><i data-lucide="circle-check"></i> بدون نیاز به کارت اعتباری</span>
                <span><i data-lucide="circle-check"></i> شروع در کمتر از ۱ دقیقه</span>
            </div>
        </div>
        <div class="hero-visual reveal">
            <div class="mock-invoice floaty">
                <div class="row"><span>فاکتور شماره</span><b class="num">۱۰۴۲</b></div>
                <div class="row"><span>طراحی وبسایت × ۱</span><b class="num">۴,۵۰۰,۰۰۰</b></div>
                <div class="row"><span>پشتیبانی یک‌ماهه × ۱</span><b class="num">۸۵۰,۰۰۰</b></div>
                <div class="row"><span>تخفیف</span><b class="num">۲۰۰,۰۰۰-</b></div>
                <div class="row total"><span>جمع کل</span><b class="num">۵,۱۵۰,۰۰۰ ریال</b></div>
            </div>
        </div>
    </div>
</section>

<section class="features-section" id="features">
    <div class="section-inner">
        <span class="section-eyebrow reveal">امکانات</span>
        <h2 class="section-title reveal">همه‌چیز برای صدور فاکتور حرفه‌ای</h2>
        <p class="section-subtitle reveal">از ورود دستی اقلام تا خروجی آماده چاپ، تمام چیزی که برای فاکتورنویسی نیاز دارید.</p>
        <div class="features-grid">
            <div class="feature-card reveal">
                <div class="feature-icon"><i data-lucide="zap"></i></div>
                <h3>صدور فاکتور در چند ثانیه</h3>
                <p>اقلام، تعداد و قیمت رو وارد کنید؛ جمع کل به‌صورت آنی و خودکار محاسبه می‌شه.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i data-lucide="percent"></i></div>
                <h3>تخفیف هوشمند هر ردیف</h3>
                <p>برای هر قلم فاکتور جداگانه تخفیف بدید؛ مبلغ نهایی خودش دقیق حساب می‌شه.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i data-lucide="calendar"></i></div>
                <h3>تاریخ شمسی کامل</h3>
                <p>همه‌ی تاریخ‌ها، از صدور فاکتور تا اشتراک، کاملا شمسی و مطابق تقویم ایرانی هستن.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i data-lucide="download"></i></div>
                <h3>خروجی تصویر، PDF و Excel</h3>
                <p>فاکتور رو رایگان به‌صورت تصویر، یا با اشتراک پولی به‌صورت PDF و اکسل حرفه‌ای دانلود کنید.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i data-lucide="store"></i></div>
                <h3>مناسب فروشگاه و شرکت</h3>
                <p>نام فروشگاه یا شرکت‌تون رو یک‌بار ثبت کنید و با یک کلیک روی هر فاکتور بیارید.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i data-lucide="shield-check"></i></div>
                <h3>امن و قابل اعتماد</h3>
                <p>اطلاعات شما با استانداردهای امنیتی روز و رمزنگاری مناسب محافظت می‌شه.</p>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($samples)): ?>
<section class="samples-section" id="samples">
    <div class="section-inner">
        <span class="section-eyebrow reveal">نمونه کار</span>
        <h2 class="section-title reveal">نمونه فاکتورها</h2>
        <p class="section-subtitle reveal">چند نمونه از فاکتورهایی که با <?= e(setting('site_name', APP_NAME)) ?> ساخته شده‌اند.</p>
        <div class="samples-grid">
            <?php foreach ($samples as $s): ?>
                <div class="sample-frame reveal">
                    <img src="<?= APP_URL . '/uploads/samples/' . e($s['image']) ?>" loading="lazy" alt="نمونه فاکتور">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="pricing-section" id="pricing">
    <div class="section-inner">
        <span class="section-eyebrow reveal">قیمت‌گذاری</span>
        <h2 class="section-title reveal">اشتراک متناسب با نیاز شما</h2>
        <p class="section-subtitle reveal">شروع رایگانه؛ هر زمان خواستید ارتقا بدید.</p>
        <div class="plans-grid landing-plans">
            <?php foreach ($plans as $p): ?>
                <?php $isFeatured = !empty($p['original_price']); ?>
                <div class="plan-card reveal <?= $isFeatured ? 'featured' : '' ?>" style="border-top:3px solid <?= e($p['color']) ?>;">
                    <?php if ($isFeatured): ?><div class="plan-badge-ribbon">پیشنهادی</div><?php endif; ?>
                    <div class="plan-icon" style="color:<?= e($p['color']) ?>;"><i data-lucide="<?= e($p['icon']) ?>"></i></div>
                    <div class="plan-name"><?= e($p['name']) ?></div>
                    <div class="plan-price">
                        <?php if ($isFeatured): ?><span class="old-price num"><?= formatNumber($p['original_price']) ?></span><?php endif; ?>
                        <?= formatNumber($p['price']) ?> <span>تومان</span>
                    </div>
                    <ul class="plan-features">
                        <li><?= $p['monthly_invoice_limit'] !== null ? 'سقف ' . formatNumber($p['monthly_invoice_limit']) . ' فاکتور در ماه' : 'صدور نامحدود فاکتور' ?></li>
                        <?php if ($p['allow_image']): ?><li>دانلود خروجی تصویر</li><?php endif; ?>
                        <?php if ($p['allow_pdf'] && $p['allow_excel']): ?><li class="highlight">دانلود PDF و Excel</li><?php endif; ?>
                    </ul>
                    <a href="<?= APP_URL ?>/register" class="btn <?= $isFeatured ? 'btn-gold' : 'btn-primary' ?> btn-block">شروع کنید</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="faq-section" id="faq">
    <div class="section-inner section-narrow">
        <span class="section-eyebrow reveal">پرسش‌های متداول</span>
        <h2 class="section-title reveal">سوالات متداول</h2>
        <div class="faq-list reveal">
            <div class="faq-item">
                <button class="faq-question">
                    <?= e(setting('site_name', APP_NAME)) ?> چیست؟
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="faq-answer"><p>یک سرویس آنلاین برای صدور، مدیریت و دانلود فاکتور فروش، مناسب فروشگاه‌ها و شرکت‌های کوچک و متوسط.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question">
                    آیا استفاده از سیستم رایگان است؟
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="faq-answer"><p>بله، ثبت‌نام و صدور فاکتور کاملا رایگان است. برای دانلود خروجی PDF و Excel نیاز به اشتراک پولی دارید.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question">
                    چطور اشتراک تهیه کنم؟
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="faq-answer"><p>از صفحه اشتراک، پلن موردنظرتون رو انتخاب کنید، مبلغ رو کارت‌به‌کارت واریز کنید و تصویر رسید رو آپلود کنید. اشتراک شما پس از بررسی فعال می‌شود.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question">
                    آیا می‌توانم فاکتور صادرشده را ویرایش کنم؟
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="faq-answer"><p>بله، هر فاکتور را در هر زمان می‌توانید ویرایش و دوباره دانلود کنید.</p></div>
            </div>
            <div class="faq-item">
                <button class="faq-question">
                    آیا اطلاعات من امن است؟
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="faq-answer"><p>بله. اطلاعات شما با رمزنگاری رمز عبور، محافظت در برابر حملات رایج وب و دسترسی محدود به داده‌ها نگهداری می‌شود.</p></div>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner">
    <div class="section-inner cta-inner reveal">
        <h2>همین حالا شروع کنید</h2>
        <p>ثبت‌نام رایگان است و کمتر از یک دقیقه طول می‌کشد.</p>
        <a href="<?= APP_URL ?>/register" class="btn btn-gold btn-lg"><i data-lucide="arrow-left"></i> ثبت‌نام رایگان</a>
    </div>
</section>
