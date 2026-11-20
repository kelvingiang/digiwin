<?php
declare(strict_types=1);

/**
 * [2026-10-12] - @author: Kelvin
 * Template Name: Actives Page
 * Description: Tối ưu bảo mật (Late Escaping/Sanitize), lấy chuẩn posts_per_page và tái sử dụng component templates/load-more.php
 */
get_header(); ?>

<div>
    <?php pageImg(get_the_ID()); ?>
</div>
<div class="page-title-h1">
    <h1><?php echo esc_html__('active', 'dgw'); ?></h1>
</div>
<div class="menu-sub">
    <?php
    $menu_category = 'active_category';
    $menu_page     = 'activities';
    menuSub($menu_category, $menu_page);
    ?>
</div>

<div class="container-fluid">
    <div class="page-col">
        <div>
            <div class="data-list">
                <?php
                global $wp;
                $param     = $wp->query_vars;
                $postCount = (int) get_option('posts_per_page', 10);

                $tag  = isset($param['tag']) ? sanitize_text_field((string) $param['tag']) : '';
                $cate = isset($param['cate']) ? sanitize_text_field((string) $param['cate']) : '';

                $postType  = 'active';
                $tax       = 'active_category';
                $term_slug = $tag !== '' ? $tag : $cate;

                if ($term_slug === '') {
                    getCustomsPost($postType, $postCount);
                } else {
                    getCustomsPostByCate($postType, $term_slug, $postCount, $tax);
                }
                ?>
            </div>

            <?php
            get_template_part('templates/load-more', null, [
                'action'    => 'load_more_posts',
                'post_type' => $postType,
                'taxonomy'  => $tax,
                'term_id'   => $term_slug,
            ]);
            ?>
        </div>
        <div>
            <?php get_template_part('templates/side-cases'); ?>
            <?php get_template_part('templates/side-articles'); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
