<?php
declare(strict_types=1);

/**
 * 2026-10-09 - @author: Kelvin - Tối ưu danh sách Solutions: Layout Modern Corporate Card, Phân trang SEO URL cate/tag
 * [2026-11-20] - @author: Kelvin - Tái sử dụng component templates/load-more.php tránh lặp code JS
 */
get_header(); ?>

<div class="banner">
    <?php get_template_part('templates/slider-owl'); ?>
</div>

<div class="menu-sub">
    <?php
    $menu_category = 'solutions_category';
    $menu_page     = 'solution';
    menuSub($menu_category, $menu_page);
    ?>
</div>

<div class="container-fluid">
    <div class="data-list">
        <?php
        global $wp;
        $param     = $wp->query_vars;
        $postCount = (int) get_option('posts_per_page', 10);

        $tag  = isset($param['tag']) ? sanitize_text_field((string) $param['tag']) : '';
        $cate = isset($param['cate']) ? sanitize_text_field((string) $param['cate']) : '';

        $postType  = 'solutions';
        $tax       = 'solutions_category';
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

<?php get_footer(); ?>
