<?php
declare(strict_types=1);

/**
 * [2026-10-12] - @author: Kelvin
 * Template Name: Join Digiwin Page
 * Description: Tối ưu bảo mật (Late Escaping/Sanitize), chuẩn posts_per_page và tái sử dụng component templates/load-more.php
 */
get_header(); ?>

<div>
    <?php pageImg(get_the_ID()); ?>
</div>
<div class="page-title-h1">
    <h1><?php echo esc_html__('join', 'dgw'); ?></h1>
</div>
<div class="menu-sub"></div>

<div class="container-fluid">
    <div class="data-list">
        <?php
        $postCount = (int) get_option('posts_per_page', 10);
        $postType  = 'joinus';
        $tax       = 'joinus_category';
        /**
         * [2026-11-20] - @author: Kelvin - Lấy term tuyển dụng tương ứng với ngôn ngữ hiện tại (recruitstaff_vn / recruitstaff_cn)
         */
        $lang      = function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn';
        $term      = get_term_by('slug', 'recruitstaff_' . $lang, $tax)
            ?: get_term_by('slug', 'recruitstaff', $tax)
            ?: get_term_by('slug', 'recruitstaff_vn', $tax);
        $cate      = '';

        if ($term) {
            $cate = (string) $term->term_id;
            getCustomsPostByCate($postType, $term->term_id, $postCount, $tax);
        }
        ?>
    </div>

    <?php
    get_template_part('templates/load-more', null, [
        'action'    => 'load_more_posts',
        'post_type' => $postType,
        'taxonomy'  => $tax,
        'term_id'   => $cate,
    ]);
    ?>
</div>

<?php get_footer(); ?>
