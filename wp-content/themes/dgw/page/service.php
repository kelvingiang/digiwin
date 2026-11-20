<?php
declare(strict_types=1);

/**
 * [2026-10-12] - @author: Kelvin
 * Template Name: Services Page
 * Description: Sửa lỗi biến global ready trong JS và tái sử dụng component templates/load-more.php
 */
get_header(); ?>

<div>
    <?php pageImg(get_the_ID()); ?>
</div>

<div class="menu-sub">
    <?php
    $menu_category = 'services_category';
    $menu_page     = 'services';
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

        $postType  = 'services';
        $tax       = 'services_category';
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
