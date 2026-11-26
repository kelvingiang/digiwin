<?php
declare(strict_types=1);

namespace DGW\Helpers;

use DGW\Models\CategoryModel;
use DGW\Models\CustomPostModel;
use DGW\Models\LogoModel;
use DGW\Models\PopupModel;
use WP_Query;
use WP_Term;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Controller/Presenter điều phối dữ liệu Custom Post và nạp View Templates (thay thế inc/front/front-custom-post.php, front-menu-sub.php, front-menu-side.php)
 * [2026-11-20] - @author: Kelvin - Chuyển sang sử dụng CustomPostModel PSR-4 trực tiếp, bỏ require_once DIR_MODEL
 * [25/11/2026] - Chuyển logic đếm lượt xem và setcookie chống F5 spam từ template view-like.php sang hook template_redirect chuẩn MVC
 */
class PostViewHelper
{
    /**
     * [25/11/2026] - Đăng ký hook template_redirect đếm lượt xem bài viết trước khi xuất HTML
     * [26/11/2026] - Bổ sung hook tự động xóa Transient Cache khối Home News khi cập nhật bài viết hoặc danh mục liên quan
     */
    public static function register(): void
    {
        add_action('template_redirect', [self::class, 'trackSinglePostView']);
        add_action('save_post_active', [self::class, 'clearHomeNewsCache']);
        add_action('save_post_resources', [self::class, 'clearHomeNewsCache']);
        add_action('save_post_casestudies', [self::class, 'clearHomeNewsCache']);
        add_action('deleted_post', [self::class, 'clearHomeNewsCache']);
        add_action('created_resources_category', [self::class, 'clearHomeNewsCache']);
        add_action('edited_resources_category', [self::class, 'clearHomeNewsCache']);
        add_action('delete_resources_category', [self::class, 'clearHomeNewsCache']);
    }

    /**
     * [25/11/2026] - Tăng lượt xem bài viết và gắn cookie chống F5 spam (3 giờ) trước khi headers được gửi
     */
    public static function trackSinglePostView(): void
    {
        $allowedPostTypes = ['post', 'active', 'casestudies', 'industries', 'joinus', 'resources', 'services', 'solutions'];
        if (is_admin() || wp_doing_ajax() || is_preview() || !is_singular($allowedPostTypes)) {
            return;
        }

        $postId = get_queried_object_id();
        if ($postId <= 0 || get_post_status($postId) !== 'publish') {
            return;
        }

        $cookieName = 'dgw_viewed_' . $postId;
        if (isset($_COOKIE[$cookieName])) {
            return;
        }

        $currentView = (int) get_post_meta($postId, '_metabox_view', true);
        update_post_meta($postId, '_metabox_view', $currentView + 1);

        RequestHelper::setSecureCookie(
            $cookieName,
            '1',
            time() + (3 * HOUR_IN_SECONDS),
            true,
            'Lax',
            (defined('COOKIEPATH') && COOKIEPATH) ? (string) COOKIEPATH : '/',
            defined('COOKIE_DOMAIN') ? (string) COOKIE_DOMAIN : ''
        );
        $_COOKIE[$cookieName] = '1';
    }
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

    // [26/11/2026] - Truyền thêm $taxonomy tùy chọn xuống CustomPostModel::get_posts_cate_at_home()
    public static function getCateAtHome(string $postType, string $cateSlug, int $postCount, string $taxonomy = 'resources_category'): WP_Query
    {
        return CustomPostModel::get_posts_cate_at_home($postType, $cateSlug, $postCount, $taxonomy);
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
     * [25/11/2026] - Đảm bảo owl.carousel-js được enqueue ở footer nếu template slider được gọi trên trang khác
     */
    public static function renderSlider(int $limit = 10): void
    {
        wp_enqueue_script('owl.carousel-js');
        get_template_part('templates/slider-owl', null, self::prepareSliderData($limit));
    }

    /**
     * [26/11/2026] - @author: Kelvin - Xóa Transient Cache của khối Home News cho tất cả ngôn ngữ khi bài viết hoặc danh mục thay đổi
     */
    public static function clearHomeNewsCache(): void
    {
        foreach (['vn', 'cn', 'sc'] as $langCode) {
            delete_transient('dgw_home_news_tabs_' . $langCode);
        }
    }

    /**
     * [26/11/2026] - @author: Kelvin - Xây dựng cấu hình các tab Home News kèm category slug và URL Xem thêm theo từng ngôn ngữ
     *
     * @return array<string, array{title_class: string, content_class: string, label: string, more_url: string, query_type: string, post_type: string, taxonomy: string, cate_slug: string}>
     */
    public static function getHomeNewsTabConfigs(string $lang): array
    {
        $resolvedLang = in_array($lang, ['vn', 'cn', 'sc'], true) ? $lang : 'vn';

        $resolveResourceCateSlug = static function (array $slugMap) use ($resolvedLang): string {
            $fallbackSlug = $slugMap[$resolvedLang] ?? $slugMap['cn'];
            $translated   = LanguageHelper::getTranslatedTerm($slugMap['cn'], $resolvedLang, 'resources_category');

            if ($translated instanceof WP_Term && $translated->slug !== '') {
                return (string) $translated->slug;
            }

            return $fallbackSlug;
        };

        $newsSlug     = $resolveResourceCateSlug(['vn' => 'news_vn', 'cn' => 'news_cn', 'sc' => 'news_cn']);
        $articleSlug  = $resolveResourceCateSlug(['vn' => 'digiknow_vn', 'cn' => 'digiknow_cn', 'sc' => 'digiknow_cn']);
        $downloadSlug = $resolveResourceCateSlug(['vn' => 'download_vn', 'cn' => 'download_cn', 'sc' => 'download_cn']);

        return [
            'event' => [
                'title_class'   => 'title-event',
                'content_class' => 'content-event',
                'label'         => 'Latest Event',
                'more_url'      => home_url('activities'),
                'query_type'    => 'side',
                'post_type'     => 'active',
                'taxonomy'      => 'active_category',
                'cate_slug'     => '',
            ],
            'news' => [
                'title_class'   => 'title-news',
                'content_class' => 'content-news',
                'label'         => 'News Center',
                'more_url'      => SeoHelper::buildCatePageUrl('resource', $newsSlug),
                'query_type'    => 'cate',
                'post_type'     => 'resources',
                'taxonomy'      => 'resources_category',
                'cate_slug'     => $newsSlug,
            ],
            'article' => [
                'title_class'   => 'title-article',
                'content_class' => 'content-article',
                'label'         => 'Column Article',
                'more_url'      => SeoHelper::buildCatePageUrl('resource', $articleSlug),
                'query_type'    => 'cate',
                'post_type'     => 'resources',
                'taxonomy'      => 'resources_category',
                'cate_slug'     => $articleSlug,
            ],
            'cases' => [
                'title_class'   => 'title-cases',
                'content_class' => 'content-cases',
                'label'         => 'Classic Case',
                'more_url'      => home_url('cases'),
                'query_type'    => 'side',
                'post_type'     => 'casestudies',
                'taxonomy'      => 'casestudies_category',
                'cate_slug'     => '',
            ],
            'download' => [
                'title_class'   => 'title-download',
                'content_class' => 'content-download',
                'label'         => 'Information Request',
                'more_url'      => SeoHelper::buildCatePageUrl('resource', $downloadSlug),
                'query_type'    => 'cate',
                'post_type'     => 'resources',
                'taxonomy'      => 'resources_category',
                'cate_slug'     => $downloadSlug,
            ],
        ];
    }

    /**
     * [26/11/2026] - @author: Kelvin - Chuẩn bị dữ liệu 5 tab Home News từ Transient Cache hoặc CustomPostModel theo ngôn ngữ (giảm TTFB)
     *
     * @return array{tab_configs: array<string, array<string, string>>, tabs_data: array<string, array<int, array{id: int, title: string, permalink: string, date: string}>>}
     */
    public static function prepareHomeNewsData(?string $lang = null): array
    {
        $currentLang  = ($lang !== null && $lang !== '')
            ? $lang
            : (function_exists('dgw_get_lang') ? (string) dgw_get_lang() : 'vn');
        $transientKey = 'dgw_home_news_tabs_' . $currentLang;
        $cached       = get_transient($transientKey);

        if (is_array($cached) && isset($cached['tab_configs'], $cached['tabs_data'])) {
            return $cached;
        }

        $tabConfigs = self::getHomeNewsTabConfigs($currentLang);
        $tabsData   = [];

        foreach ($tabConfigs as $tabKey => $cfg) {
            $tabQuery = ($cfg['query_type'] === 'cate')
                ? CustomPostModel::get_posts_cate_at_home($cfg['post_type'], $cfg['cate_slug'], 5, $cfg['taxonomy'])
                : CustomPostModel::get_posts_at_side($cfg['post_type'], 5);

            // Dùng foreach($tabQuery->posts) thay vì while/the_post() để tránh Divi Builder chèn #et-boc qua hook loop_start/loop_end
            $items = [];
            if ($tabQuery && !empty($tabQuery->posts)) {
                foreach ($tabQuery->posts as $postItem) {
                    $pid     = (int) $postItem->ID;
                    $items[] = [
                        'id'        => $pid,
                        'title'     => (string) get_the_title($pid),
                        'permalink' => (string) get_permalink($pid),
                        'date'      => (string) get_the_date('Y-m-d', $pid),
                    ];
                }
            }
            $tabsData[$tabKey] = $items;
        }

        $payload = [
            'tab_configs' => $tabConfigs,
            'tabs_data'   => $tabsData,
        ];

        set_transient($transientKey, $payload, 30 * MINUTE_IN_SECONDS);

        return $payload;
    }

    /**
     * [26/11/2026] - @author: Kelvin - Chuẩn bị dữ liệu Home News và render qua View templates/home-news.php chuẩn MVC
     */
    public static function renderHomeNews(): void
    {
        get_template_part('templates/home-news', null, self::prepareHomeNewsData());
    }
}
