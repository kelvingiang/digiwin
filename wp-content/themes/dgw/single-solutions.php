<?php
/**
 * [2026-10-12] - @author: Kelvin - Đồng bộ cấu trúc chuẩn với single-casestudies.php: Technical SEO (H1 thực tế, Semantic HTML), submenu, layout 2 cột kèm sidebar
 */
get_header(); ?>
<div class="container-fluid">
    <!-- <div class="page-title-h1">
        <span><?php //echo esc_html(get_the_title()); ?></span>
    </div> -->
    <div class="menu-sub">
        <?php
        $menu_category = 'solutions_category';
        $menu_page     = 'solution';
        menuSub($menu_category, $menu_page);
        ?>
    </div>
    <div id="single-row">
        <div class="single-content">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('single-space'); ?>>
                        <!-- <h2 class="single-space-title">
                            <?php //echo esc_html(get_the_title()); ?>
                        </h2> -->
                        <div class="single-space-content entry-content">
                            <?php the_content(); ?>
                        </div>
                        <?php get_template_part('templates/view-like'); ?>
                    </article>
            <?php
                endwhile;
            endif;

            if (comments_open() || get_comments_number()) {
                comments_template();
            }
            ?>
        </div>
        <!-- <aside class="single-sidebar" aria-label="<?php //echo esc_attr__('Sidebar Content', 'dgw'); ?>">
            <?php // get_template_part('templates/side-active'); ?>
            <?php //get_template_part('templates/side-cases');  ?>
            <?php //get_template_part('templates/side-articles'); ?>
        </aside> -->
    </div>
</div>
<?php get_footer(); ?>