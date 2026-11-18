<?php
/**
 * Date: 2026-08-24
 * Template Name: About Page
 * Description: Custom template for About Page
 */
get_header();
// [2026-11-16] - @author: Kelvin - Chỉ nạp templates/header và templates/footer khi header.php/footer.php chưa tự động nạp theo pagename (tránh trùng lặp HTML)
// [2026-11-17] - @author: Kelvin - Bổ sung about-sc, about-zh để đồng bộ danh sách tự động nạp header/footer
$current_pagename = get_query_var('pagename', 1);
$has_auto_layout  = in_array($current_pagename, array('', 'about-cn', 'about-sc', 'about-zh', 'about-vn'), true);
if (!$has_auto_layout) {
    get_template_part('templates/header');
}
$lang = dgw_get_lang();
?>
<div>
    <?php pageImg(get_the_ID()); ?>
</div>
<div class="container-fluid">
    <div class="row margin-top">
        <div class="col-xl-9 col-lg-9 col-md-12 col-sm-12 col-12">
            <div class="page-title">
                <h1><?php echo esc_html(__('About Digiwin', 'dgw')); ?></h1>
            </div>
            <div>
                <?php echo wp_kses_post((string) get_post_meta(1, '_info_summary_' . $lang, true)); ?>
            </div>
            <hr>
            <div id='operating'>
                <h3 class="article-title"><?php echo esc_html(__('Operating', 'dgw')); ?></h3>
                <?php echo wp_kses_post((string) get_post_meta(1, '_info_operating_' . $lang, true)); ?>
            </div>
            <hr>
            <div id='location'>
                <h3 class="article-title"><?php echo esc_html(__('Location', 'dgw')); ?></h3>
                <?php echo wp_kses_post((string) get_post_meta(1, '_info_location_' . $lang, true)); ?>
            </div>

            <div id='contact' style="color: #fff; background-color: #253B50;  ">
                <!-- <h2 style="padding: 1rem 1rem;   font-size: 1.1rem; "><?php // __('Contact Us', 'dgw') 
                                                                    ?></h2> -->
                <div style="padding-left: 1rem; ">
                    <div style="font-size: 1rem;">
                        <?php echo esc_html((string) get_post_meta(1, '_info_name_' . $lang, true)); ?></div>
                    <div><label><?php echo esc_html(__('Phone', 'dgw')); ?> :</label><?php echo esc_html((string) get_post_meta(1, '_info_phone', true)); ?></div>
                    <div><label>E-mail :</label><?php echo esc_html((string) get_post_meta(1, '_info_email', true)); ?></div>
                    <div><label><?php echo esc_html(__('Address', 'dgw')); ?> :
                        </label><?php echo esc_html((string) get_post_meta(1, '_info_address_' . $lang, true)); ?></div>
                </div>
                <div style="padding: 0.1rem; ">
                    <?php get_template_part('templates/googlemap')
          ?>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12">
            <div class="">
                <?php get_template_part('templates/side-cases'); ?>
                <?php //get_template_part('templates/side-active'); 
        ?>
            </div>
        </div>
    </div>
</div>
<?php
if (!$has_auto_layout) {
    get_template_part('templates/footer');
}
get_footer(); ?>