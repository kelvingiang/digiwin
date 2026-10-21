<?php
declare(strict_types=1);

// [2026-10-05] - @author: Kelvin - Refactor tầng Controller/Presenter cho Custom Posts theo chuẩn MVC tách biệt Model và View

if (!defined('ABSPATH')) {
    exit;
}

require_once DIR_MODEL . 'model-custom-post.php';

/**
 * [2026-10-05] - @author: Kelvin - Điều phối lặp dữ liệu WP_Query và render từng thẻ qua View Component
 */
function renderCustomPostList(WP_Query $custom_query): void
{
    if (!$custom_query->have_posts()) {
        return;
    }

    $stt = 1;
    while ($custom_query->have_posts()) {
        $custom_query->the_post();

        // Nạp View Template Component độc lập
        get_template_part('templates/post-item', null, ['stt' => $stt]);

        $stt++;
    }

    wp_reset_postdata();
}

/**
 * [2026-10-05] - @author: Kelvin - Controller: Lấy bài viết Custom Post Type và render View
 */
function getCustomsPost(string $postType, $postCount): void
{
    $query = Model_Custom_Post::get_posts($postType, (int) $postCount);
    renderCustomPostList($query);
}

/**
 * [2026-10-05] - @author: Kelvin - Controller: Lấy bài viết Custom Post Type theo Category và render View
 */
function getCustomsPostByCate(string $postType, $cate, $postCount, string $taxonomy): void
{
    $query = Model_Custom_Post::get_posts_by_cate($postType, $cate, (int) $postCount, $taxonomy);
    renderCustomPostList($query);
}

/**
 * [2026-10-05] - @author: Kelvin - Render menu danh mục con của Case Studies với Late Escaping
 */
function getCustomsPostCate(array $param): void
{
    $arr = [];
    $parent_id = isset($param['cate']) ? (int) $param['cate'] : 0;

    $categories = get_categories([
        'type'       => 'post',
        'number'     => 100,
        'taxonomy'   => 'casestudies_category',
        'hide_empty' => 0,
        'parent'     => $parent_id,
    ]);

    if (!empty($categories) && !is_wp_error($categories)) {
        $lang = dgw_get_lang();
        foreach ($categories as $value) {
            $option = get_option("option_casestudies_category_{$value->term_id}");
            $name   = isset($option['cate_' . $lang]) ? $option['cate_' . $lang] : $value->name;
            $order  = isset($option['cate_order']) ? $option['cate_order'] : 0;

            $arr[$value->term_id] = [
                'ID'    => $value->term_id,
                'name'  => $name,
                'class' => 'menu-main-sub-1-item',
                'order' => $order,
                'sub'   => '',
            ];
        }
    }

    if (empty($arr)) {
        return;
    }

    $current_tag = isset($param['tag']) ? (string) $param['tag'] : '';
    $pagename    = isset($param['pagename']) ? (string) $param['pagename'] : '';
    $cate_val    = isset($param['cate']) ? (string) $param['cate'] : '';
    ?>
    <nav class="menu-cate-list">
        <?php foreach ($arr as $key => $val): ?>
            <?php $is_active = ($current_tag === (string) $key); ?>
            <div class="<?php echo $is_active ? 'menu-cate-list-active' : ''; ?>">
                <?php if ($is_active): ?>
                    <label><?php echo esc_html((string) $val['name']); ?></label>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url($pagename . '/cate/' . $cate_val . '/tag/' . $val['ID'])); ?>">
                        <?php echo esc_html((string) $val['name']); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </nav>
    <?php
}

/**
 * [2026-10-05] - @author: Kelvin - Delegate truy vấn bài viết trang chủ sang Model
 */
function getCustomPostAtHome(string $postType, $postCount): WP_Query
{
    return Model_Custom_Post::get_posts_at_home($postType, (int) $postCount);
}

/**
 * [2026-10-05] - @author: Kelvin - Delegate truy vấn bài viết category trang chủ sang Model
 */
function getCustomPostCateAtHome(string $postType, string $cateSlug, $postCount): WP_Query
{
    return Model_Custom_Post::get_posts_cate_at_home($postType, $cateSlug, (int) $postCount);
}

/**
 * [2026-10-05] - @author: Kelvin - Delegate truy vấn bài viết sidebar theo category sang Model
 */
function getCustomPostAtSideCate(string $postType, $postCount, string $taxonomy, $cate): WP_Query
{
    return Model_Custom_Post::get_posts_at_side_cate($postType, (int) $postCount, $taxonomy, $cate);
}

/**
 * [2026-10-05] - @author: Kelvin - Delegate truy vấn bài viết sidebar sang Model
 */
function getCustomPostAtSide(string $postType, $postCount): WP_Query
{
    return Model_Custom_Post::get_posts_at_side($postType, (int) $postCount);
}

/**
 * [2026-10-05] - @author: Kelvin - Delegate truy vấn bài viết hiển thị sidebar sang Model
 */
function getCustomPostShowSidebar(string $postType): WP_Query
{
    return Model_Custom_Post::get_posts_show_sidebar($postType);
}

/**
 * [2026-10-05] - @author: Kelvin - Delegate truy vấn post category sang Model
 */
function getPostCategory(string $cate, $postCount): WP_Query
{
    return Model_Custom_Post::get_post_category($cate, (int) $postCount);
}

/**
 * [2026-10-05] - @author: Kelvin - Delegate truy vấn post category ở trang chủ sang Model
 */
function getPostCategoryAtHome(string $cate, $postCount): WP_Query
{
    return Model_Custom_Post::get_post_category_at_home($cate, (int) $postCount);
}
