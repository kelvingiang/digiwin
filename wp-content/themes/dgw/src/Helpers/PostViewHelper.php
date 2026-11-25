<?php
declare(strict_types=1);

namespace DGW\Helpers;

use DGW\Models\CategoryModel;
use DGW\Models\CustomPostModel;
use DGW\Models\LogoModel;
use DGW\Models\PopupModel;
use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Controller/Presenter điều phối dữ liệu Custom Post và nạp View Templates (thay thế inc/front/front-custom-post.php, front-menu-sub.php, front-menu-side.php)
 * [2026-11-20] - @author: Kelvin - Chuyển sang sử dụng CustomPostModel PSR-4 trực tiếp, bỏ require_once DIR_MODEL
 */
class PostViewHelper
{
    /**
     * [2026-11-19] - @author: Kelvin - Lặp WP_Query và render thẻ bài viết qua View templates/post-item.php
     */
    public static function renderList(WP_Query $customQuery): void
    {
        if (!$customQuery->have_posts()) {
            return;
        }

        $stt = 1;
        while ($customQuery->have_posts()) {
            $customQuery->the_post();
            get_template_part('templates/post-item', null, ['stt' => $stt]);
            $stt++;
        }

        wp_reset_postdata();
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy bài viết Custom Post Type và render View
     */
    public static function renderPosts(string $postType, int $postCount): void
    {
        self::renderList(CustomPostModel::get_posts($postType, $postCount));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy bài viết Custom Post Type theo Category và render View
     */
    public static function renderPostsByCate(string $postType, $cate, int $postCount, string $taxonomy): void
    {
        self::renderList(CustomPostModel::get_posts_by_cate($postType, $cate, $postCount, $taxonomy));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Chuẩn bị dữ liệu và render menu danh mục con của Case Studies qua View templates/post-cate-nav.php
     */
    public static function renderPostCateNav(array $param): void
    {
        $parentId   = isset($param['cate']) ? (int) $param['cate'] : 0;
        $categories = get_categories([
            'type'       => 'post',
            'number'     => 100,
            'taxonomy'   => 'casestudies_category',
            'hide_empty' => 0,
            'parent'     => $parentId,
        ]);

        if (empty($categories) || is_wp_error($categories)) {
            return;
        }

        $lang  = function_exists('dgw_get_lang') ? (string) dgw_get_lang() : 'vn';
        $items = [];

        foreach ($categories as $term) {
            $option = get_option('option_casestudies_category_' . $term->term_id, []);
            $optArr = is_array($option) ? $option : [];
            $items[$term->term_id] = [
                'ID'    => $term->term_id,
                'name'  => $optArr['cate_' . $lang] ?? $term->name,
                'order' => $optArr['cate_order'] ?? 0,
            ];
        }

        if (empty($items)) {
            return;
        }

        get_template_part('templates/post-cate-nav', null, [
            'items'       => $items,
            'current_tag' => isset($param['tag']) ? (string) $param['tag'] : '',
            'pagename'    => isset($param['pagename']) ? (string) $param['pagename'] : '',
            'cate_val'    => isset($param['cate']) ? (string) $param['cate'] : '',
        ]);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render ảnh đại diện banner của trang
     */
    public static function renderPageImage(int $postId): void
    {
        if (!has_post_thumbnail($postId)) {
            return;
        }

        $image = wp_get_attachment_image_src((int) get_post_thumbnail_id($postId), 'single-post-thumbnail');
        if (!empty($image[0])) {
            echo '<img class="page-img" src="' . esc_url($image[0]) . '" alt="' . esc_attr(get_the_title($postId)) . '"/>';
        }
    }

    /**
     * [2026-11-19] - @author: Kelvin - Chuẩn bị dữ liệu và render submenu điều hướng qua templates/menu-sub.php
     */
    public static function renderMenuSub(string $taxonomy, string $page): void
    {
        $data = CategoryModel::getAllCategories($taxonomy, 0, $page);
        if (empty($data)) {
            return;
        }

        $currentCateSlug = get_query_var('cate');
        if (empty($currentCateSlug) && is_singular()) {
            $terms = wp_get_object_terms((int) get_the_ID(), $taxonomy);
            if (!empty($terms) && !is_wp_error($terms)) {
                $currentCateSlug = $terms[0]->slug;
            }
        }

        get_template_part('templates/menu-sub', null, [
            'items'             => $data,
            'current_cate_slug' => (string) $currentCateSlug,
            'is_single'         => is_single(),
        ]);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Chuẩn bị dữ liệu và render menu sidebar qua templates/menu-side.php
     */
    public static function renderMenuSide(string $taxonomy, string $page): void
    {
        global $wp;
        $param       = is_array($wp->query_vars ?? null) ? $wp->query_vars : [];
        $currentCate = isset($param['cate']) ? (string) $param['cate'] : '';
        $currentTag  = isset($param['tag']) ? (string) $param['tag'] : '';

        $parents = CategoryModel::getAllCategories($taxonomy, 0, $page);
        foreach ($parents as $index => $parentItem) {
            $parentId                  = (int) ($parentItem['ID'] ?? 0);
            $parents[$index]['children'] = $parentId > 0
                ? CategoryModel::getAllCategories($taxonomy, $parentId, $page)
                : [];
        }

        get_template_part('templates/menu-side', null, [
            'items'        => $parents,
            'current_cate' => $currentCate,
            'current_tag'  => $currentTag,
        ]);
    }

    public static function getAtHome(string $postType, int $postCount): WP_Query
    {
        return CustomPostModel::get_posts_at_home($postType, $postCount);
    }

    public static function getCateAtHome(string $postType, string $cateSlug, int $postCount): WP_Query
    {
        return CustomPostModel::get_posts_cate_at_home($postType, $cateSlug, $postCount);
    }

    public static function getAtSideCate(string $postType, int $postCount, string $taxonomy, $cate): WP_Query
    {
        return CustomPostModel::get_posts_at_side_cate($postType, $postCount, $taxonomy, $cate);
    }

    public static function getAtSide(string $postType, int $postCount): WP_Query
    {
        return CustomPostModel::get_posts_at_side($postType, $postCount);
    }

    public static function getShowSidebar(string $postType): WP_Query
    {
        return CustomPostModel::get_posts_show_sidebar($postType);
    }

    public static function getPostCategory(string $cate, int $postCount): WP_Query
    {
        return CustomPostModel::get_post_category($cate, $postCount);
    }

    public static function getPostCategoryAtHome(string $cate, int $postCount): WP_Query
    {
        return CustomPostModel::get_post_category_at_home($cate, $postCount);
    }

    /**
     * [20/11/2026] - Lấy danh sách logo trang chủ qua LogoModel và nạp View templates/home-cases-logo.php
     */
    public static function renderHomeLogos(): void
    {
        $logoList = LogoModel::getCachedHomeLogos();
        get_template_part('templates/home-cases-logo', null, ['logo_list' => $logoList]);
    }

    /**
     * [20/11/2026] - Lấy Pop-up đang kích hoạt qua PopupModel và nạp View templates/home-popup.php
     */
    public static function renderHomePopup(): void
    {
        $lang  = function_exists('dgw_get_lang') ? (string) dgw_get_lang() : 'vn';
        $popup = PopupModel::getActiveForDisplay($lang);

        if ($popup !== null) {
            get_template_part('templates/home-popup', null, ['popup' => $popup]);
        }
    }

    /**
     * [20/11/2026] - Chuẩn bị dữ liệu Hero Slider (ảnh preload LCP và danh sách slides) từ CustomPostModel
     *
     * @return array{preload_desktop: string, preload_mobile: string, slides: array<int, array<string, mixed>>}
     */
    public static function prepareSliderData(int $limit = 10): array
    {
        $sliderQuery    = CustomPostModel::get_sliders($limit);
        $preloadDesktop = '';
        $preloadMobile  = '';
        $slides         = [];

        if ($sliderQuery->have_posts()) {
            $firstPostId  = (int) $sliderQuery->posts[0]->ID;
            $firstThumbId = (int) get_post_thumbnail_id($firstPostId);
            $firstDesktop = $firstThumbId > 0 ? wp_get_attachment_image_src($firstThumbId, 'large') : false;
            $firstMobile  = $firstThumbId > 0 ? wp_get_attachment_image_src($firstThumbId, 'medium_large') : false;

            if (is_array($firstDesktop) && !empty($firstDesktop[0])) {
                $preloadDesktop = (string) $firstDesktop[0];
            }
            if (is_array($firstMobile) && !empty($firstMobile[0])) {
                $preloadMobile = (string) $firstMobile[0];
            }

            while ($sliderQuery->have_posts()) {
                $sliderQuery->the_post();
                $postId     = (int) get_the_ID();
                $link       = (string) get_post_meta($postId, '_metabox_link', true);
                $thumbId    = (int) get_post_thumbnail_id($postId);
                $urlDesktop = $thumbId > 0 ? wp_get_attachment_image_src($thumbId, 'large') : false;
                $urlMobile  = $thumbId > 0 ? wp_get_attachment_image_src($thumbId, 'medium_large') : false;

                if (!is_array($urlDesktop) || empty($urlDesktop[0])) {
                    continue;
                }

                $slides[] = [
                    'post_id'     => $postId,
                    'title'       => (string) the_title_attribute(['echo' => false]),
                    'link'        => $link,
                    'has_link'    => ($link !== '' && $link !== '#'),
                    'url_desktop' => (string) $urlDesktop[0],
                    'width'       => (int) ($urlDesktop[1] ?? 0),
                    'height'      => (int) ($urlDesktop[2] ?? 0),
                    'url_mobile'  => (is_array($urlMobile) && !empty($urlMobile[0])) ? (string) $urlMobile[0] : (string) $urlDesktop[0],
                ];
            }

            wp_reset_postdata();
        }

        return [
            'preload_desktop' => $preloadDesktop,
            'preload_mobile'  => $preloadMobile,
            'slides'          => $slides,
        ];
    }

    /**
     * [20/11/2026] - Chuẩn bị dữ liệu Slider và render qua View templates/slider-owl.php
     */
    public static function renderSlider(int $limit = 10): void
    {
        get_template_part('templates/slider-owl', null, self::prepareSliderData($limit));
    }
}
