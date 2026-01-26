<?php get_header();
$cate =  wp_get_post_terms($post->ID, 'resources_category');
$cate_ID = $cate[0]->term_id;
?>
<div class="menu-sub"></div>
<div id="single-row">
    <div class="single-content">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <div class="single-space">
                    <h2 class="single-space-title"><?php the_title() ?></h2>
                    <div class="single-space-content">
                        <?php the_content(); ?>
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
