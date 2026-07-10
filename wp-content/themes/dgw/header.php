<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width" />
    <!-- <meta name="facebook-domain-verification" content="91lenwxb0nqyrpfos1s5gl8kttei4l" /> -->
    <meta name="google-site-verification" content="8Cqw_SSKDqlxTUmeaXPfqLLvUEKhzxeq33PG33Ln6O4" />

    <?php
    // Meta description được sinh bởi dgw_seo_meta_tags() trong helper/function.php
    ?>

    <link rel="icon" type="image/x-icon" href="<?php echo esc_url(get_template_directory_uri()); ?>/images/icons/favicon.ico">
    <link rel="apple-touch-icon"
        href="<?php echo esc_url(get_template_directory_uri()); ?>/images/icons/apple-touch-icon.png">

    <?php wp_head(); ?>
    <!-- phan theo doi va quang cao cua zalo  -->
    <script async="" src="https://s.zzcdn.me/ztr/ztracker.js?id=7056180858377605120"></script>
    <?php if ( ! is_page( 'member' ) ) : ?>
    <link rel="preload" as="image"
        href="https://www.digiwin.com.vn/wp-content/uploads/2022/12/About-us-Top-banner-rev-2025.webp"
        fetchpriority="high">
    <?php endif; ?>

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
        case 'about-vn':
        case 'cases':
        case 'industries':
        case 'solution':
        case 'service':
        case 'resource':
        case 'activities':
        case 'contact-cn':
        case 'contact-vn':
        case 'join-digiwin':
        case 'partner':
            // case 'test':
            get_template_part('templates/template', 'header');
    }
    ?>
