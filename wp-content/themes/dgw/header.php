<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <?php // [2026-11-17] - @author: Kelvin - Bổ sung initial-scale=1.0 chuẩn responsive & Mobile SEO ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- <meta name="facebook-domain-verification" content="91lenwxb0nqyrpfos1s5gl8kttei4l" /> -->
    <meta name="google-site-verification" content="8Cqw_SSKDqlxTUmeaXPfqLLvUEKhzxeq33PG33Ln6O4" />

    <?php
    // Meta description được sinh bởi dgw_seo_meta_tags() trong helper/function.php
    ?>

    <?php
    // [16/11/2026] - Ưu tiên Site Icon của WordPress (wp_site_icon); chỉ xuất icon fallback từ theme khi chưa cấu hình Site Icon và file thực sự tồn tại để tránh trùng thẻ và lỗi 404
    if (!function_exists('has_site_icon') || !has_site_icon()) {
        $theme_dir = get_template_directory();
        $theme_uri = get_template_directory_uri();

        if (file_exists($theme_dir . '/images/icons/favicon.ico')) {
            echo '<link rel="icon" href="' . esc_url($theme_uri . '/images/icons/favicon.ico') . '" sizes="any" />' . "\n";
        }
        if (file_exists($theme_dir . '/images/icons/favicon.png')) {
            echo '<link rel="icon" type="image/png" href="' . esc_url($theme_uri . '/images/icons/favicon.png') . '" />' . "\n";
        }
        if (file_exists($theme_dir . '/images/icons/apple-touch-icon.png')) {
            echo '<link rel="apple-touch-icon" href="' . esc_url($theme_uri . '/images/icons/apple-touch-icon.png') . '" />' . "\n";
        }
    }
    ?>

    <?php wp_head(); ?>
    <!-- phan theo doi va quang cao cua zalo  -->
    <script async="" src="https://s.zzcdn.me/ztr/ztracker.js?id=7056180858377605120"></script>

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-PC6FVTMZ');
    </script>
    <!-- End Google Tag Manager -->

    <style>
        /* [2026-10-12] - @author: Kelvin - Vô hiệu hóa triệt để lỗi CSS core WordPress thu nhỏ body#error-page còn 700px trên hosting */
        html,
        body,
        body#error-page {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent;
        }
    </style>
</head>

<body <?php body_class(); ?>>

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PC6FVTMZ"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
    // cho phép hiển thi menu chính ====================
    $paged = get_query_var('pagename', 1);

    switch ($paged) {
        case '':
        case 'about-cn':
        case 'about-sc':
        case 'about-zh':
        case 'about-vn':
        case 'cases':
        case 'industries':
        case 'solution':
        case 'service':
        case 'resource':
        case 'activities':
        case 'contact-cn':
        case 'contact-sc':
        case 'contact-zh':
        case 'contact-vn':
        case 'join-digiwin':
        case 'partner':
            // case 'test':
            get_template_part('templates/header');
    }
    ?>
