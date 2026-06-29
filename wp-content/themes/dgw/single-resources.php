<?php get_header(); ?>
<?php
$post_id = get_the_ID();
$cate = wp_get_post_terms($post_id, 'resources_category');
$cate_ID = (!is_wp_error($cate) && !empty($cate)) ? $cate[0]->term_id : 0;;
$source = get_post_meta($post_id, '_metabox_source', true);
?>
<div class="page-title-h1">
    <h1><?php echo __('resource') ?></h1>
</div>
<div class="menu-sub"></div>
<div id="single-row">
    <div class="single-content">
        <?php if (have_posts()) :
            while (have_posts()) :
                the_post(); ?>
                <div class="single-space">
                    <h2 class="single-space-title"><?php the_title() ?></h2>
                    <div class="single-space-content">
                        <?php the_content(); ?>
                    </div>
                    <div class="single-space-download-btn">
                        <?php if ($source) : ?>
                            <a class="btn-download" id="my-load-data" download>
                                <svg class="icon" width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                <?php echo __('Download File digiwin' , 'dgw') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php get_template_part('templates/template', 'view_like'); ?>
                </div>
        <?php
            endwhile;
        endif;

        if (comments_open() || get_comments_number()) {
            comments_template();
        }
        ?>
    </div>
</div>




<?php get_footer();
