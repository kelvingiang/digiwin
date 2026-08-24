<?php
/**
 * Date: 2026-08-24
 * Template Name: About Page
 * Description: Custom template for About Page
 */
get_header();
get_template_part('templates/template', 'header'); 
$lang = dgw_get_lang();
?>
<div>
    <?php pageImg(get_the_ID()); ?>
</div>
<div class="container-fluid">
    <div class="row margin-top">
        <div class="col-xl-9 col-lg-9 col-md-12 col-sm-12 col-12">
            <div class="page-title">
                <h1><?php // _e('About') 
            ?> </h1>
            </div>
            <div>
                <?php echo get_post_meta(1, "_info_summary_" . $lang, true) ?>
            </div>
            <hr>
            <div id='operating'>
                <h3 class="article-title"><?php _e('Operating') ?></h3>
                <?php echo get_post_meta(1, "_info_operating_" . $lang, true) ?>
            </div>
            <hr>
            <div id='location'>
                <h3 class="article-title"><?php _e('Location') ?></h3>
                <?php echo get_post_meta(1, "_info_location_" . $lang, true) ?>
            </div>

            <div id='contact' style="color: #fff; background-color: #253B50;  ">
                <!-- <h2 style="padding: 1rem 1rem;   font-size: 1.1rem; "><?php // _e('Contact Us') 
                                                                    ?></h2> -->
                <div style="padding-left: 1rem; ">
                    <div style="font-size: 1rem;">
                        <?php echo get_post_meta(1, '_info_name_' . $lang, true) ?></div>
                    <div><label><?php _e('Phone'); ?> :</label><?php echo get_post_meta(1, '_info_phone', true) ?></div>
                    <div><label>E-mail :</label><?php echo get_post_meta(1, '_info_email', true) ?></div>
                    <div><label><?php _e('Address') ?> :
                        </label><?php echo get_post_meta(1, '_info_address_' . $lang, true) ?></div>
                </div>
                <div style="padding: 0.1rem; ">
                    <?php get_template_part('templates/template', 'googlemap')
          ?>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12">
            <div class="">
                <?php get_template_part('templates/template', 'side_cases'); ?>
                <?php //get_template_part('templates/template', 'side_active'); 
        ?>
            </div>
        </div>
    </div>
</div>
<?php
get_template_part('templates/template', 'footer');
get_footer(); ?>