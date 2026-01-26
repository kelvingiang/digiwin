<?php get_header(); ?>
<div class="container-fluid">
    <div class="menu-sub">
        <?php
        $menu_category = 'casestudies_category';
        $menu_page = 'cases';
        menuSub($menu_category, $menu_page);
        ?>
    </div>
    <div id="single-two-row">
        <div class="single-content">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <div class="single-space">
                        <h2 class="single-space-title">
                            <?php the_title() ?>
                        </h2>
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
        <div class="single-sidebar">
            <?php get_template_part('templates/template', 'side_cases');  ?>
            <?php get_template_part('templates/template', 'side_active'); ?>
            <?php get_template_part('templates/template', 'side_articles'); ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>