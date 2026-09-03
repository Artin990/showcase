<div class="dash-topbar">
    <h1>تماس با ما</h1>
</div>

<div class="card" style="max-width:520px;">
    <p style="color:var(--ink-soft); font-size:14px; margin-bottom:22px; line-height:2;">
        برای هر گونه سوال، مشکل یا پیشنهاد می‌توانید از راه‌های زیر با ما در ارتباط باشید.
    </p>

    <div class="contact-list">
        <?php if ($phone): ?>
        <a href="tel:<?= e($phone) ?>" class="contact-item">
            <span class="contact-icon"><i data-lucide="phone"></i></span>
            <div>
                <div class="contact-label">شماره تماس</div>
                <div class="contact-value" dir="ltr"><?= e($phone) ?></div>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($telegram): ?>
        <a href="https://t.me/<?= e($telegram) ?>" target="_blank" rel="noopener" class="contact-item">
            <span class="contact-icon"><i data-lucide="send"></i></span>
            <div>
                <div class="contact-label">تلگرام</div>
                <div class="contact-value" dir="ltr">@<?= e($telegram) ?></div>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($instagram): ?>
        <a href="https://instagram.com/<?= e($instagram) ?>" target="_blank" rel="noopener" class="contact-item">
            <span class="contact-icon"><i data-lucide="instagram"></i></span>
            <div>
                <div class="contact-label">اینستاگرام</div>
                <div class="contact-value" dir="ltr">@<?= e($instagram) ?></div>
            </div>
        </a>
        <?php endif; ?>
    </div>
</div>
