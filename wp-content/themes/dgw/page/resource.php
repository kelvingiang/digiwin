<?php
declare(strict_types=1);

/**
 * [2026-10-12] - @author: Kelvin
 * Template Name: Resources Page
 * Description: Tối ưu bảo mật (Late Escaping/Sanitize), lấy chuẩn posts_per_page và tái sử dụng component templates/load-more.php
 */
get_header(); ?>

<div>
    <?php pageImg(get_the_ID()); ?>
</div>
<div class="page-title-h1">
    <h1><?php echo esc_html__('resource', 'dgw'); ?></h1>
</div>
<div class="menu-sub">
    <?php
    $menu_category = 'resources_category';
    $menu_page     = 'resource';
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

                $postType  = 'resources';
                $tax       = 'resources_category';
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
            <?php
            $current_cate = $cate !== '' ? $cate : (string) get_query_var('cate');
            $request_uri  = (string) ($_SERVER['REQUEST_URI'] ?? '');
            $is_digiknow  = in_array($current_cate, ['digiknow_vn', 'digiknow_cn'], true)
                || strpos($current_cate, 'digiknow') === 0
                || strpos($request_uri, '/resource/cate/digiknow') !== false;

            if ($is_digiknow) :
                get_template_part('templates/side-solution');
            else :
                get_template_part('templates/side-active');
            endif;
            ?>
            <?php get_template_part('templates/side-articles'); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
