<?php
// dua toan bo cac function vao file nay de giam do phuc tap cua file functions.php
require_once get_template_directory() . '/inc/init.php';

/**
 * [2026-10-05] - @author: Kelvin - Fallback tuong thich nguoc cho cac loi goi get_template_part('templates/template', $name)
 * Tu dong dieu huong sang template moi trong thu muc templates/ theo chuan kebab-case
 */
add_action('get_template_part_templates/template', function ($slug, $name = null, $args = []) {
    if (empty($name)) {
        return;
    }
    $cleanName = str_replace('_', '-', $name);
    get_template_part('templates/' . $cleanName, null, $args);
}, 10, 3);

/**
 * Ngày thực hiện: 06/11/2026
 * Chức năng: Nhận diện an toàn category slug hiện tại của trang
 * Mục đích: Hỗ trợ phát hiện URL danh mục (vd: /solution/cate/tiptop_vn/, /cases/cate/...)
 *           ngay cả trước khi WP_Query hoàn tất hoặc trong các hook locale/template_redirect
 *
 * @return string|null Slug của category hoặc null nếu không phải trang cate
 */
function dgw_detect_current_category_slug(): ?string
{
    // 1. Kiểm tra query var 'cate' nếu đã được WordPress parse
    $cate = get_query_var('cate');
    if (!empty($cate)) {
        return (string) $cate;
    }

    // 2. Kiểm tra nếu đang ở trang taxonomy archive trực tiếp
    if (!is_admin() && (is_tax() || is_category())) {
        $queried = get_queried_object();
        if ($queried instanceof WP_Term) {
            return (string) $queried->slug;
        }
    }

    // 3. Fallback trích xuất trực tiếp từ REQUEST_URI (chuẩn URL /{page}/cate/{slug}/...)
    if (!empty($_SERVER['REQUEST_URI'])) {
        $request_path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (preg_match('@/(?:cate|category)/([^/?#]+)(?:/(?:tag(?:/[^/?#]*)?)?)?/?$@i', $request_path, $matches)) {
            return urldecode($matches[1]);
        }
    }

    return null;
}

/**
 * Ngày thực hiện: 06/11/2026
 * Chức năng: Nhận diện ngôn ngữ ('vn' hoặc 'cn') của category dựa trên slug hoặc Term Meta (term_lang)
 * Mục đích: Tự động phát hiện và đồng bộ ngôn ngữ hệ thống theo link danh mục được dán vào
 *
 * @param string|int|null $cate_param Slug hoặc Term ID của category. Nếu null sẽ tự lấy theo trang hiện hành
 * @return string|null 'vn'|'cn' nếu xác định được, hoặc null nếu không mang ngôn ngữ chuyên biệt
 */
function dgw_detect_category_language($cate_param = null): ?string
{
    if (empty($cate_param)) {
        $cate_param = dgw_detect_current_category_slug();
    }

    if (empty($cate_param)) {
        return null;
    }

    // 1. Kiểm tra hậu tố slug rõ ràng (_vn, -vn, _cn, -cn, _tw, -tw, _sc, -sc, _zh, -zh, _vi, -vi)
    if (is_string($cate_param)) {
        $clean_slug = strtolower(trim($cate_param));
        if (preg_match('/[_-](vn|vi)$/', $clean_slug)) {
            return 'vn';
        }
        if (preg_match('/[_-](cn|tw)$/', $clean_slug)) {
            return 'cn';
        }
        if (preg_match('/[_-](sc|zh)$/', $clean_slug)) {
            return 'sc';
        }
    }

    // 2. Tìm term trong taxonomy và kiểm tra Term Meta 'term_lang'
    if (function_exists('dgw_get_category_by_param')) {
        $term_obj = dgw_get_category_by_param($cate_param);
        if ($term_obj instanceof WP_Term) {
            $term_lang = get_term_meta($term_obj->term_id, 'term_lang', true);
            if (in_array($term_lang, array('vn', 'cn', 'sc'), true)) {
                return $term_lang;
            }
        }
    }

    return null;
}

/**
 * Ngày thực hiện: 06/11/2026
 * Chức năng: Tìm đối tượng term bản dịch tương ứng theo Translation Group
 * Mục đích: Chuyển đổi URL mượt mà giữa các ngôn ngữ cho cùng một danh mục (vd: tiptop_vn <-> tiptop_cn)
 *
 * @param WP_Term|int|string $term Đối tượng term, term_id hoặc slug
 * @param string $target_lang Ngôn ngữ đích cần tìm ('cn' hoặc 'vn')
 * @param string $taxonomy Tên Taxonomy tương ứng (mặc định 'solutions_category')
 * @return WP_Term|null
 */
function dgw_get_translated_term($term, string $target_lang, string $taxonomy = 'solutions_category'): ?WP_Term
{
    $term_obj = null;
    if ($term instanceof WP_Term) {
        $term_obj = $term;
    } elseif (is_numeric($term)) {
        $term_obj = get_term((int) $term, $taxonomy);
    } elseif (is_string($term)) {
        if (!empty($taxonomy)) {
            $term_obj = get_term_by('slug', $term, $taxonomy);
        }
        if (!$term_obj && function_exists('dgw_get_category_by_param')) {
            $term_obj = dgw_get_category_by_param($term);
        }
    }

    if (!$term_obj || is_wp_error($term_obj)) {
        return null;
    }

    $current_lang = get_term_meta($term_obj->term_id, 'term_lang', true);
    if ($current_lang === $target_lang) {
        return $term_obj;
    }

    $group_id = get_term_meta($term_obj->term_id, 'translation_group', true) ?: $term_obj->term_id;
    if (empty($group_id)) {
        return null;
    }

    $tax = !empty($term_obj->taxonomy) ? $term_obj->taxonomy : $taxonomy;

    $linked_terms = get_terms([
        'taxonomy'   => $tax,
        'hide_empty' => false,
        'meta_query' => [
            [
                'key'     => 'translation_group',
                'value'   => $group_id,
                'compare' => '=',
            ],
            [
                'key'     => 'term_lang',
                'value'   => $target_lang,
                'compare' => '=',
            ],
        ],
    ]);

    if (!empty($linked_terms) && !is_wp_error($linked_terms)) {
        return reset($linked_terms);
    }

    return null;
}

/**
 * Ngày thực hiện: 17/11/2026
 * Chức năng: Bảng cấu hình chuẩn hóa 3 ngôn ngữ của hệ thống (Tiếng Việt, Trung Phồn thể, Trung Giản thể)
 * Mục đích: Dùng chung cho hreflang (vi-VN, zh-TW, zh-CN), Open Graph og:locale (vi_VN, zh_TW, zh_CN) và WordPress locale
 *
 * @return array<string, array{hreflang: string, og_locale: string, wp_locale: string}>
 */
function dgw_get_supported_locales_map(): array
{
    return array(
        'vn' => array('hreflang' => 'vi-VN', 'og_locale' => 'vi_VN', 'wp_locale' => 'vi_VN'),
        'cn' => array('hreflang' => 'zh-TW', 'og_locale' => 'zh_TW', 'wp_locale' => 'zh_TW'),
        'sc' => array('hreflang' => 'zh-CN', 'og_locale' => 'zh_CN', 'wp_locale' => 'zh_CN'),
    );
}

/**
 * Ngày thực hiện: 17/11/2026
 * Chức năng: Chuẩn hóa mã ngôn ngữ đầu vào (từ URL/Cookie/Meta) về mã nội bộ ('vn', 'cn', 'sc')
 *
 * @param string $raw_lang Mã ngôn ngữ thô
 * @return string|null
 */
function dgw_normalize_lang_code(string $raw_lang): ?string
{
    $normalized = strtolower(str_replace('_', '-', trim($raw_lang)));

    if (in_array($normalized, array('vn', 'vi', 'vi-vn'), true)) {
        return 'vn';
    }
    if (in_array($normalized, array('cn', 'tw', 'zh-tw'), true)) {
        return 'cn';
    }
    if (in_array($normalized, array('sc', 'zh', 'zh-cn'), true)) {
        return 'sc';
    }

    return null;
}

/**
 * Ngày thực hiện: 17/11/2026
 * Chức năng: Kiểm tra hệ thống đã có gói ngôn ngữ Tiếng Trung Giản thể (zh_CN) hoặc hook kích hoạt cho trang dùng chung URL hay chưa
 *
 * @return bool
 */
function dgw_has_simplified_chinese_locale(): bool
{
    $lang_dir = get_template_directory() . '/languages';
    $has_file = file_exists($lang_dir . '/zh_CN.l10n.php') || file_exists($lang_dir . '/zh_CN.mo');

    return (bool) apply_filters('dgw_enable_zh_cn_hreflang', $has_file);
}

/**
 * Ngày thực hiện: 16/11/2026
 * Cập nhật: 17/11/2026 - Bổ sung ánh xạ Tiếng Trung Giản thể (about-sc / about-zh, contact-sc / contact-zh)
 * Chức năng: Bảng ánh xạ các trang Page tách URL riêng theo ngôn ngữ
 * Mục đích: Dùng chung cho nhận diện ngôn ngữ, chuyển hướng language switcher, hreflang và og:locale:alternate
 *
 * @return array<string, array{lang: string, group: array<string, array<int, string>>}>
 */
function dgw_get_paired_pages_map(): array
{
    $about_group = array(
        'vn' => array('about-vn'),
        'cn' => array('about-cn'),
        'sc' => array('about-sc', 'about-zh'),
    );
    $contact_group = array(
        'vn' => array('contact-vn'),
        'cn' => array('contact-cn'),
        'sc' => array('contact-sc', 'contact-zh'),
    );

    return array(
        'about-vn'   => array('lang' => 'vn', 'group' => $about_group),
        'about-cn'   => array('lang' => 'cn', 'group' => $about_group),
        'about-sc'   => array('lang' => 'sc', 'group' => $about_group),
        'about-zh'   => array('lang' => 'sc', 'group' => $about_group),
        'contact-vn' => array('lang' => 'vn', 'group' => $contact_group),
        'contact-cn' => array('lang' => 'cn', 'group' => $contact_group),
        'contact-sc' => array('lang' => 'sc', 'group' => $contact_group),
        'contact-zh' => array('lang' => 'sc', 'group' => $contact_group),
    );
}

/**
 * Ngày thực hiện: 16/11/2026
 * Chức năng: Nhận diện slug trang hiện tại nếu thuộc nhóm trang tách URL theo ngôn ngữ
 * Mục đích: Hoạt động an toàn cả trước và sau khi WP_Query parse (trên hook locale, template_redirect và template)
 *
 * @return string|null
 */
function dgw_detect_paired_page_slug(): ?string
{
    $paired_map = dgw_get_paired_pages_map();

    if (!empty($GLOBALS['wp_query'])) {
        $pagename = sanitize_title((string) get_query_var('pagename'));
        if ($pagename !== '' && isset($paired_map[$pagename])) {
            return $pagename;
        }

        if (is_page()) {
            $queried = get_queried_object();
            if ($queried instanceof WP_Post) {
                $post_slug = sanitize_title((string) $queried->post_name);
                if (isset($paired_map[$post_slug])) {
                    return $post_slug;
                }
            }
        }
    }

    if (!empty($_SERVER['REQUEST_URI'])) {
        $request_path = trim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        if ($request_path !== '') {
            $segments  = explode('/', $request_path);
            $last_slug = sanitize_title((string) end($segments));
            if (isset($paired_map[$last_slug])) {
                return $last_slug;
            }
        }
    }

    return null;
}

/**
 * Ngày thực hiện: 16/11/2026
 * Cập nhật: 17/11/2026 - Hỗ trợ thêm 'sc' (zh-CN)
 * Chức năng: Nhận diện ngôn ngữ ('vn', 'cn' hoặc 'sc') từ các trang Page tách URL theo ngôn ngữ
 *
 * @return string|null
 */
function dgw_detect_paired_page_language(): ?string
{
    $slug       = dgw_detect_paired_page_slug();
    $paired_map = dgw_get_paired_pages_map();

    if ($slug !== null && isset($paired_map[$slug]['lang'])) {
        return $paired_map[$slug]['lang'];
    }

    return null;
}

/**
 * Ngày thực hiện: 16/11/2026
 * Cập nhật: 17/11/2026 - Đồng bộ cả 3 ngôn ngữ (vn, cn, sc) cho các trang tách URL; chỉ xuất 'sc' khi trang Giản thể đã được tạo/publish
 * Chức năng: Lấy tập URL đối ứng theo ngôn ngữ cho trang Page tách URL theo slug
 * Mục đích: Dùng cho nút chuyển đổi ngôn ngữ (languages.php), thẻ hreflang (dgw_get_seo_urls) và og:locale:alternate
 *
 * @param string $page_slug Slug của trang hiện tại
 * @return array{vn?: string, cn?: string, sc?: string}
 */
function dgw_get_paired_page_urls(string $page_slug): array
{
    $paired_map      = dgw_get_paired_pages_map();
    $normalized_slug = sanitize_title($page_slug);

    if (!isset($paired_map[$normalized_slug]['group'])) {
        return array();
    }

    $group = $paired_map[$normalized_slug]['group'];
    $urls  = array();

    foreach ($group as $lang_key => $candidate_slugs) {
        $resolved_url = '';

        foreach ($candidate_slugs as $candidate_slug) {
            $target_page = get_page_by_path($candidate_slug);
            if ($target_page instanceof WP_Post && $target_page->post_status === 'publish') {
                $resolved_url = (string) get_permalink($target_page);
                break;
            }
        }

        // Với bản Giản thể ('sc'): chỉ thêm vào danh sách khi trang thực sự tồn tại/publish hoặc đang xem đúng trang đó (tránh hreflang trỏ 404)
        if ($resolved_url === '') {
            if ($lang_key === 'sc') {
                if (in_array($normalized_slug, $candidate_slugs, true)) {
                    $resolved_url = home_url(user_trailingslashit($normalized_slug));
                }
            } else {
                $resolved_url = home_url(user_trailingslashit($candidate_slugs[0]));
            }
        }

        if ($resolved_url !== '') {
            $urls[$lang_key] = $resolved_url;
        }
    }

    return $urls;
}

// [2026-07-09] - Refactor dgw_get_lang and locale mapping for AJAX/get/cookie consistency
// [05/11/2026] - Ưu tiên nhận diện ngôn ngữ theo bài viết chi tiết (Single Post)
// [06/11/2026] - Ưu tiên nhận diện ngôn ngữ theo link trang danh mục (Category/Cate) khi link mang định danh ngôn ngữ cụ thể
// [16/11/2026] - Ưu tiên nhận diện ngôn ngữ theo các trang tách URL (about-vn/cn/sc, contact-vn/cn/sc)
// [17/11/2026] - Hỗ trợ nhận diện thêm Tiếng Trung Giản thể ('sc' -> zh-CN)
function dgw_get_lang()
{
    $default = 'vn';

    if (wp_doing_ajax()) {
        if (isset($_REQUEST['lang'])) {
            $normalized = dgw_normalize_lang_code(sanitize_text_field($_REQUEST['lang']));
            if ($normalized !== null) {
                return $normalized;
            }
        }
    }

    // [05/11/2026] - Ưu tiên ngôn ngữ bài viết tuyệt đối khi đang xem trang chi tiết (Single Post)
    if (!is_admin() && is_singular()) {
        $queried_id = get_queried_object_id();
        if ($queried_id) {
            $post_lang = get_post_meta($queried_id, '_metabox_langguage', true);
            if (in_array($post_lang, array('cn', 'vn', 'sc'), true)) {
                return $post_lang;
            }
        }
    }

    // [06/11/2026] - Ưu tiên ngôn ngữ theo link trang danh mục (Category/Cate) khi link dán vào mang ngôn ngữ cụ thể
    if (!is_admin()) {
        $cate_lang = dgw_detect_category_language();
        if (in_array($cate_lang, array('cn', 'vn', 'sc'), true)) {
            return $cate_lang;
        }

        // [16/11/2026] - Ưu tiên ngôn ngữ theo trang tách URL (about-vn/cn/sc, contact-vn/cn/sc)
        $paired_lang = dgw_detect_paired_page_language();
        if (in_array($paired_lang, array('cn', 'vn', 'sc'), true)) {
            return $paired_lang;
        }
    }

    if (isset($_GET['lang'])) {
        $normalized = dgw_normalize_lang_code(sanitize_text_field($_GET['lang']));
        return $normalized ?? 'vn';
    }

    if (isset($_COOKIE['site_lang'])) {
        $normalized = dgw_normalize_lang_code(sanitize_text_field($_COOKIE['site_lang']));
        return $normalized ?? 'vn';
    }

    return $default;
}

/**
 * [05/11/2026] - Nạp lại textdomain theo locale chỉ định
 * Hỗ trợ load chính xác file .l10n.php và .mo trong thư mục languages/ của theme
 *
 * @param string $locale
 */
function dgw_reload_theme_textdomain(string $locale): void
{
    $lang_dir = get_template_directory() . '/languages';
    $php_file = $lang_dir . '/' . $locale . '.l10n.php';
    $mo_file  = $lang_dir . '/' . $locale . '.mo';

    unload_textdomain('dgw');
    if (file_exists($php_file)) {
        load_textdomain('dgw', $php_file, $locale);
    } elseif (file_exists($mo_file)) {
        load_textdomain('dgw', $mo_file, $locale);
    } else {
        load_theme_textdomain('dgw', $lang_dir);
    }
}

/**
 * [05/11/2026] - Tự động đồng bộ Cookie, Session và Locale theo bài viết Single
 * [06/11/2026] - Tự động đồng bộ Cookie, Session và Locale theo trang danh mục (Category/Cate) khi link dán vào khác ngôn ngữ hiện tại
 * Khi truy cập bài viết chi tiết hoặc trang danh mục (vd: /solution/cate/tiptop_vn/), nếu ngôn ngữ khác ngôn ngữ hiện tại,
 * hệ thống lập tức đồng bộ Cookie site_lang, Session và nạp lại textdomain theo ngôn ngữ của link
 * để toàn bộ Menu, Footer, Submenu, bài viết và switcher chuyển đổi đồng bộ ngay lập tức.
 */
add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $target_lang = null;

    // 1. Kiểm tra ngôn ngữ từ bài viết chi tiết (Single Post)
    if (is_singular()) {
        $queried_id = get_queried_object_id();
        if ($queried_id) {
            $post_lang = get_post_meta($queried_id, '_metabox_langguage', true);
            if (in_array($post_lang, array('cn', 'vn', 'sc'), true)) {
                $target_lang = $post_lang;
            }
        }
    }

    // 2. [06/11/2026] Kiểm tra ngôn ngữ từ trang danh mục (Cate) nếu không phải Single
    if (!$target_lang) {
        $cate_lang = dgw_detect_category_language();
        if (in_array($cate_lang, array('cn', 'vn', 'sc'), true)) {
            $target_lang = $cate_lang;
        }
    }

    // 3. [16/11/2026] Kiểm tra ngôn ngữ từ trang tách URL (about-vn/cn/sc, contact-vn/cn/sc)
    if (!$target_lang) {
        $paired_lang = dgw_detect_paired_page_language();
        if (in_array($paired_lang, array('cn', 'vn', 'sc'), true)) {
            $target_lang = $paired_lang;
        }
    }

    if ($target_lang) {
        $current_cookie_lang = $_COOKIE['site_lang'] ?? '';

        // 1. Cập nhật cookie runtime và gửi header setcookie
        $_COOKIE['site_lang'] = $target_lang;
        if (!headers_sent() && $current_cookie_lang !== $target_lang) {
            setcookie(
                'site_lang',
                $target_lang,
                time() + YEAR_IN_SECONDS,
                COOKIEPATH ? COOKIEPATH : '/',
                COOKIE_DOMAIN,
                is_ssl(),
                false
            );
        }

        // 2. Đồng bộ Session nếu có
        if (session_status() === PHP_SESSION_ACTIVE || !empty($_SESSION)) {
            $_SESSION['languages'] = $target_lang;
        }

        // 3. Chuyển đổi locale và nạp lại textdomain tương ứng
        $locale_map    = dgw_get_supported_locales_map();
        $target_locale = $locale_map[$target_lang]['wp_locale'] ?? 'vi_VN';
        if ($target_locale === 'zh_CN' && !dgw_has_simplified_chinese_locale()) {
            // Fallback giao diện gettext về zh_TW nếu theme chưa có file dịch zh_CN riêng
            $target_locale = 'zh_TW';
        }
        if (function_exists('switch_to_locale')) {
            switch_to_locale($target_locale);
        }
        dgw_reload_theme_textdomain($target_locale);
    }
}, 1);

/**
 * [2026-10-07] - Thiết lập cấu hình ngôn ngữ hệ thống:
 * - Backend (WP-Admin): Cố định duy nhất 1 ngôn ngữ là Tiếng Trung (zh_TW), không bị ảnh hưởng bởi Frontend.
 * - Frontend: Áp dụng cơ chế đa ngôn ngữ (Việt - Trung Phồn thể - Trung Giản thể) dựa trên cookie site_lang / URL param.
 */
add_filter('admin_locale', function () {
    return 'zh_TW';
});

add_filter('determine_locale', function ($locale) {
    if (is_admin() && !wp_doing_ajax()) {
        return 'zh_TW';
    }
    return $locale;
});

add_filter('locale', function ($locale) {
    // 1. Phía Backend Admin: Cố định 100% tiếng Trung
    if (is_admin() && !wp_doing_ajax()) {
        return 'zh_TW';
    }

    // 2. Phía Frontend: Chuyển đổi linh hoạt giữa Tiếng Việt, Trung Phồn thể và Trung Giản thể
    $lang       = dgw_get_lang();
    $locale_map = dgw_get_supported_locales_map();
    $wp_locale  = $locale_map[$lang]['wp_locale'] ?? 'vi_VN';
    if ($wp_locale === 'zh_CN' && !dgw_has_simplified_chinese_locale()) {
        return 'zh_TW';
    }

    return $wp_locale;
});

// sắp xếp lại trình tự các input trong phần comment ==========
add_filter('comment_form_fields', function ($fields) {
    // 把 author 和 email 欄位放前面，comment 欄位放最後
    $comment_field = $fields['comment'];
    unset($fields['comment']);
    $fields['comment'] = $comment_field;
    return $fields;
});


// 01/12/2025  sắp xếp lại mục chọn cho phép lick hiện thị comment vô side bar bên phải
add_action('add_meta_boxes', function () {

    $post_types = array('post', 'resources', 'solutions', 'casestudies', 'active', 'services', 'industries');

    foreach ($post_types as $pt) {

        // 移除原本在主欄的 Discussion
        remove_meta_box('commentstatusdiv', $pt, 'normal');

        // 加到右側欄
        add_meta_box(
            'commentstatusdiv',
            __('Discuss', 'dgw'),
            'post_comment_status_meta_box',
            $pt,
            'side',
            'default'
        );
    }
});

// loại bỏ tất cá các bài có liên quan đến author cả chính lẫn phụ
add_action('template_redirect', function () {
    if (is_author()) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        include(get_query_template('404'));
        exit;
    }
});



//====== SAP LAI ARRAY THEO THU TU GIAM DAN AP DUNG CATEGORY =================
function cmp($a, $b)
{
    return strcmp((string)($b['order'] ?? ''), (string)($a['order'] ?? ''));
}

// [2026-07-09] - Refactor getParams: trả default an toàn, sanitize dữ liệu, không trả khoảng trắng
function dgw_sanitize_request_value($value)
{
    if (is_array($value)) {
        return array_map('dgw_sanitize_request_value', $value);
    }
    return sanitize_text_field(wp_unslash($value));
}

//==== GET PARAM TREN URL============================================
function getParams($name = null, $default = '')
{
    if ($name === null || $name === '') {
        return dgw_sanitize_request_value($_REQUEST);
    }

    $name = sanitize_key($name);

    if (!isset($_REQUEST[$name])) {
        return $default;
    }

    return dgw_sanitize_request_value($_REQUEST[$name]);
}

function custom_redirect($location = '')
{
    global $post_type;
    $url = admin_url('edit.php?post_type=' . rawurlencode($post_type));

    if (empty($location)) {
        return $url;
    }

    if (is_array($location)) {
        return add_query_arg($location, $url);
    }

    $location = ltrim($location, '&?');
    if (strpos($location, '=') !== false) {
        return $url . '&' . $location;
    }

    return $url;
}

//============= KIEM DU LIEU CHUYEN QUA BANG PHUONG POST HAY GET======================
function isPost()
{
    return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
}





function toBack($num)
{
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $page = isset($_REQUEST['page']) ? sanitize_key($_REQUEST['page']) : '';
    $url = admin_url('admin.php?page=' . $page . '&paged=' . $paged . '&msg=' . intval($num));
    wp_redirect($url);
    exit;
}

// [2026-07-09] - Cập nhật style / cú pháp: sửa exit;; và chỉnh format login logo
//======= THAY DOI LOGO DANG NHAP O ADMIN =====================================================
// if (!is_admin()) {

function custom_login_logo()
{
    echo '<style type="text/css">\n'
        . '    h1 a { background-image: url(' . esc_url(PART_IMAGES . 'logo.png') . ') !important; }\n'
        . '</style>';
}

add_action('login_head', 'custom_login_logo');
// } else {
//     require_once DIR_HELPER . 'code/code-add-media.php';
//     require_once DIR_HELPER . 'code/code-upload-file.php';
// }

// [2026-07-09] - Refactor SEO output to use WordPress title/meta hooks and avoid nested title functions.
// [16/11/2026] - Đồng bộ tên site, tiêu đề RSS feed, canonical và hreflang (vi-VN, zh-TW, x-default) theo ngôn ngữ đang chọn
// [2026-11-17] - @author: Kelvin - Đặt priority = 2 cho dgw_seo_meta_tags để đẩy thẻ SEO/OG lên đầu <head>, ngay dưới <title>
add_filter('document_title_parts', 'dgw_seo_document_title_parts');
add_action('wp_head', 'dgw_seo_meta_tags', 2);
add_filter('option_blogname', 'dgw_filter_option_blogname');
add_filter('feed_links_args', 'dgw_filter_feed_links_args');
add_filter('feed_links_extra_args', 'dgw_filter_feed_links_extra_args');
add_action('init', function (): void {
    if (!defined('RANK_MATH_VERSION')) {
        remove_action('wp_head', 'rel_canonical');
    }
});

/**
 * Ngày thực hiện: 16/11/2026
 * Chức năng: Đồng bộ giá trị blogname ở Frontend theo ngôn ngữ Trung/Việt đang chọn
 * Mục đích: Giúp các thẻ <link rel="alternate" type="application/rss+xml" title="..."> và get_bloginfo('name') đổi theo ngôn ngữ
 *
 * @param mixed $value Giá trị option blogname gốc
 * @return mixed
 */
function dgw_filter_option_blogname($value)
{
    if (is_admin() || !did_action('wp')) {
        return $value;
    }

    static $is_resolving = false;
    if ($is_resolving) {
        return $value;
    }

    $is_resolving = true;
    $lang = function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn';
    $site = dgw_get_seo_site_info($lang);
    $is_resolving = false;

    return ($site['name'] !== '') ? $site['name'] : $value;
}

/**
 * Ngày thực hiện: 16/11/2026
 * [2026-11-17] - @author: Kelvin - Đồng bộ mẫu tiêu đề RSS Feed chính (feed_links) cho cả 3 ngôn ngữ (vn, cn, sc)
 * Mục đích: Đảm bảo thuộc tính title của các thẻ <link type="application/rss+xml"> đồng bộ hoàn toàn với ngôn ngữ đang chọn
 *
 * @param array $args Tham số cấu hình feed_links()
 * @return array
 */
function dgw_filter_feed_links_args(array $args): array
{
    $lang = function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn';
    $templates = array(
        'vn' => array(
            'feedtitle' => '%1$s %2$s Dòng tin',
            'comstitle' => '%1$s %2$s Dòng bình luận',
        ),
        'cn' => array(
            'feedtitle' => '%1$s %2$s 訂閱',
            'comstitle' => '%1$s %2$s 留言訂閱',
        ),
        'sc' => array(
            'feedtitle' => '%1$s %2$s 订阅',
            'comstitle' => '%1$s %2$s 评论订阅',
        ),
    );

    if (isset($templates[$lang])) {
        return array_merge($args, $templates[$lang]);
    }

    return $args;
}

/**
 * Ngày thực hiện: 16/11/2026
 * [2026-11-17] - @author: Kelvin - Đồng bộ mẫu tiêu đề RSS Feed mở rộng (feed_links_extra) cho cả 3 ngôn ngữ (vn, cn, sc)
 * Mục đích: Đồng bộ title của thẻ RSS feed cho trang chi tiết, chuyên mục, thẻ, taxonomy, tìm kiếm
 *
 * @param array $args Tham số cấu hình feed_links_extra()
 * @return array
 */
function dgw_filter_feed_links_extra_args(array $args): array
{
    $lang = function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn';
    $templates = array(
        'vn' => array(
            'singletitle'   => '%1$s %2$s %3$s Dòng bình luận',
            'cattitle'      => '%1$s %2$s %3$s Chuyên mục',
            'tagtitle'      => '%1$s %2$s %3$s Thẻ',
            'taxtitle'      => '%1$s %2$s %3$s %4$s',
            'authortitle'   => '%1$s %2$s Bài viết bởi %3$s',
            'searchtitle'   => '%1$s %2$s Kết quả tìm kiếm cho &#8220;%3$s&#8221;',
            'posttypetitle' => '%1$s %2$s %3$s',
        ),
        'cn' => array(
            'singletitle'   => '%1$s %2$s %3$s 留言訂閱',
            'cattitle'      => '%1$s %2$s %3$s 分類訂閱',
            'tagtitle'      => '%1$s %2$s %3$s 標籤訂閱',
            'taxtitle'      => '%1$s %2$s %3$s %4$s 訂閱',
            'authortitle'   => '%1$s %2$s %3$s 的文章訂閱',
            'searchtitle'   => '%1$s %2$s &#8220;%3$s&#8221; 的搜尋結果訂閱',
            'posttypetitle' => '%1$s %2$s %3$s 訂閱',
        ),
        'sc' => array(
            'singletitle'   => '%1$s %2$s %3$s 评论订阅',
            'cattitle'      => '%1$s %2$s %3$s 分类订阅',
            'tagtitle'      => '%1$s %2$s %3$s 标签订阅',
            'taxtitle'      => '%1$s %2$s %3$s %4$s 订阅',
            'authortitle'   => '%1$s %2$s %3$s 的文章订阅',
            'searchtitle'   => '%1$s %2$s &#8220;%3$s&#8221; 的搜索结果订阅',
            'posttypetitle' => '%1$s %2$s %3$s 订阅',
        ),
    );

    if (isset($templates[$lang])) {
        return array_merge($args, $templates[$lang]);
    }

    return $args;
}

/**
 * [2026-11-16] - @author: Kelvin - Ánh xạ slug trang module sang Taxonomy danh mục tương ứng
 * Mục đích: Giúp dgw_get_category_by_param() và bộ chuyển đổi ngôn ngữ ưu tiên tìm term đúng taxonomy của module đang xem
 *
 * @param string|null $page_slug Slug trang module (nếu null sẽ tự lấy từ query var pagename hoặc REQUEST_URI)
 * @return string|null
 */
function dgw_detect_current_module_taxonomy(?string $page_slug = null): ?string
{
    $page_tax_map = array(
        'cases'        => 'casestudies_category',
        'casestudies'  => 'casestudies_category',
        'solution'     => 'solutions_category',
        'solutions'    => 'solutions_category',
        'resource'     => 'resources_category',
        'resources'    => 'resources_category',
        'activities'   => 'active_category',
        'active'       => 'active_category',
        'service'      => 'services_category',
        'services'     => 'services_category',
        'industry'     => 'industries_category',
        'industries'   => 'industries_category',
        'join-digiwin' => 'joinus_category',
        'joinus'       => 'joinus_category',
    );

    if ($page_slug === null || $page_slug === '') {
        if (!empty($GLOBALS['wp_query'])) {
            $page_slug = (string) get_query_var('pagename');
        }
        if (empty($page_slug) && !empty($_SERVER['REQUEST_URI'])) {
            $request_path = trim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            $home_path    = trim((string) parse_url(home_url('/'), PHP_URL_PATH), '/');
            if ($home_path !== '' && str_starts_with($request_path . '/', $home_path . '/')) {
                $request_path = trim(substr($request_path, strlen($home_path)), '/');
            }
            if ($request_path !== '') {
                $path_parts = explode('/', $request_path);
                $page_slug  = $path_parts[0] ?? '';
            }
        }
    }

    $normalized_slug = sanitize_title((string) $page_slug);
    return $page_tax_map[$normalized_slug] ?? null;
}

/**
 * [02/10/2026] - Lay doi tuong WP_Term an toan theo slug hoac term_id
 * [06/11/2026] - Bổ sung đầy đủ các taxonomy của theme: active_category, industries_category, joinus_category
 * [2026-11-16] - @author: Kelvin - Ưu tiên tìm term theo taxonomy của module hiện tại trước khi quét danh sách mặc định
 */
function dgw_get_category_by_param($cate, ?string $preferred_taxonomy = null)
{
    if (empty($cate)) {
        return null;
    }

    $preferred_taxonomy = $preferred_taxonomy ?: dgw_detect_current_module_taxonomy();

    if (is_numeric($cate)) {
        if (!empty($preferred_taxonomy)) {
            $term = get_term((int) $cate, $preferred_taxonomy);
            if ($term instanceof WP_Term) {
                return $term;
            }
        }
        $term = get_term((int) $cate);
        return ($term instanceof WP_Term) ? $term : null;
    }

    $slug = sanitize_title((string) $cate);
    $taxonomies = array(
        'solutions_category',
        'casestudies_category',
        'resources_category',
        'active_category',
        'services_category',
        'industries_category',
        'joinus_category',
        'advertising_category',
        'category'
    );

    if (!empty($preferred_taxonomy)) {
        $taxonomies = array_values(array_unique(array_merge(array($preferred_taxonomy), $taxonomies)));
    }

    foreach ($taxonomies as $taxonomy) {
        $term = get_term_by('slug', $slug, $taxonomy);
        if ($term instanceof WP_Term) {
            return $term;
        }
    }

    return null;
}

/**
 * Ngày thực hiện: 16/11/2026
 * Chức năng: Lấy thông tin tên, địa chỉ, mô tả giá trị doanh nghiệp và từ khóa của website theo ngôn ngữ ('cn' hoặc 'vn')
 * Mục đích: Cung cấp meta description và og:description cô đọng (không lặp chuỗi hay dồn địa chỉ 2 miền) đồng bộ Trung - Việt
 *
 * @param string $lang Mã ngôn ngữ ('cn' hoặc 'vn')
 * @return array{name: string, address: string, description: string, keywords: string}
 */
function dgw_get_seo_site_info(string $lang): array
{
    $lang_suffix = in_array($lang, array('cn', 'sc'), true) ? $lang : 'vn';

    $site_name = trim((string) get_post_meta(1, '_info_name_' . $lang_suffix, true));
    if ($site_name === '' && $lang_suffix === 'sc') {
        $site_name = '鼎捷软件(越南)股份公司';
    }
    if ($site_name === '') {
        $site_name = trim((string) get_option('company_name_' . $lang_suffix));
    }
    if ($site_name === '') {
        $site_name = get_bloginfo('name');
    }

    $site_address = trim((string) get_post_meta(1, '_info_address_' . $lang_suffix, true));
    if ($site_address === '' && $lang_suffix === 'sc') {
        $site_address = trim((string) get_post_meta(1, '_info_address_cn', true));
    }
    if ($site_address === '') {
        $site_address = trim((string) get_option('company_address_' . $lang_suffix));
    }

    // [16/11/2026] - Dùng câu giới thiệu giá trị doanh nghiệp chuẩn SEO
    // [17/11/2026] - Bổ sung mô tả chuẩn Tiếng Trung Giản thể (zh-CN)
    $site_descriptions = array(
        'vn' => 'Digiwin Việt Nam cung cấp giải pháp chuyển đổi số cho doanh nghiệp sản xuất: phần mềm ERP, MES, WMS và nhà máy thông minh AIoT giúp tối ưu vận hành.',
        'cn' => '鼎捷軟件(越南)提供製造業全方位數位轉型方案，涵蓋 ERP、MES、WMS 與 AIoT 智慧工廠整合服務，助力企業優化生產效率與營運管理。',
        'sc' => '鼎捷软件(越南)提供制造业全方位数字化转型方案，涵盖 ERP、MES、WMS 与 AIoT 智慧工厂整合服务，助力企业优化生产效率与运营管理。',
    );
    $site_description = $site_descriptions[$lang_suffix] ?? $site_descriptions['vn'];

    return array(
        'name'        => wp_strip_all_tags($site_name),
        'address'     => wp_strip_all_tags($site_address),
        'description' => wp_strip_all_tags($site_description),
        'keywords'    => wp_strip_all_tags($site_name),
    );
}

/**
 * Ngày thực hiện: 16/11/2026
 * Cập nhật: 17/11/2026 - Bổ sung mô tả Tiếng Trung Giản thể ('sc' -> zh-CN) cho about-sc/zh, contact-sc/zh và các Page chính
 * Chức năng: Trả về mô tả giá trị SEO riêng cho từng trang Page chính theo ngôn ngữ hiện hành ('vn', 'cn' hoặc 'sc')
 * Mục đích: Tránh lặp tiêu đề trang + tên công ty + địa chỉ trong meta description và og:description
 *
 * @param string $page_slug Slug của trang Page hiện tại
 * @param int    $post_id   ID của Page
 * @param string $lang      Mã ngôn ngữ ('cn', 'sc' hoặc 'vn')
 * @param array  $site      Thông tin site từ dgw_get_seo_site_info()
 * @return string
 */
function dgw_get_page_seo_description(string $page_slug, int $post_id, string $lang, array $site): string
{
    $lang_key        = in_array($lang, array('cn', 'sc'), true) ? $lang : 'vn';
    $normalized_slug = sanitize_title($page_slug);

    $about_desc = array(
        'vn' => 'Tìm hiểu về Digiwin Việt Nam – đơn vị cung cấp phần mềm ERP II, MES, WMS và giải pháp nhà máy thông minh uy tín từ năm 2008 cho doanh nghiệp sản xuất.',
        'cn' => '認識鼎捷軟件(越南)股份公司，自 2008 年深耕越南市場，專注提供 ERP II、MES、WMS 與智慧製造數位轉型解決方案，協助企業提升競爭力。',
        'sc' => '认识鼎捷软件(越南)股份公司，自 2008 年深耕越南市场，专注提供 ERP II、MES、WMS 与智能制造数字化转型解决方案，助力企业提升竞争力。',
    );
    $contact_desc = array(
        'vn' => 'Liên hệ Digiwin Việt Nam tại TP.HCM và Hà Nội để được tư vấn chuyên sâu giải pháp ERP, MES, WMS, AIoT và lộ trình chuyển đổi số cho doanh nghiệp.',
        'cn' => '聯絡鼎捷軟件(越南)胡志明市與河內服務據點，取得 ERP、MES、WMS、AIoT 智慧工廠顧問諮詢與企業數位轉型規劃服務。',
        'sc' => '联络鼎捷软件(越南)胡志明市与河内服务据点，获取 ERP、MES、WMS、AIoT 智慧工厂顾问咨询与企业数字化转型规划服务。',
    );
    $solution_desc = array(
        'vn' => 'Hệ sinh thái giải pháp Digiwin: TIPTOP GP, Workflow ERP, MES/SFT, WMS và AIoT giúp doanh nghiệp sản xuất tối ưu quy trình, kiểm soát chi phí và chất lượng.',
        'cn' => '鼎捷軟件(越南)完整解決方案：TIPTOP GP、Workflow ERP、MES/SFT、WMS 與 AIoT 智慧工廠，全面提升製造營運效能。',
        'sc' => '鼎捷软件(越南)完整解决方案：TIPTOP GP、Workflow ERP、MES/SFT、WMS 与 AIoT 智慧工厂，全面提升制造运营效能。',
    );
    $industries_desc = array(
        'vn' => 'Giải pháp chuyển đổi số chuyên sâu theo từng ngành: cơ khí, điện – điện tử, nhựa cao su, gỗ nội thất và thiết bị y tế từ Digiwin Việt Nam.',
        'cn' => '針對電子、機械、塑橡膠、家具與醫療器材等產業量身打造的鼎捷智慧製造解決方案，精準克服各行業管理痛點。',
        'sc' => '针对电子、机械、塑橡胶、家具与医疗器材等产业量身打造的鼎捷智能制造解决方案，精准克服各行业管理痛点。',
    );
    $service_desc = array(
        'vn' => 'Dịch vụ tư vấn, triển khai ERP/MES, đào tạo vận hành và hỗ trợ kỹ thuật e-Service chuyên nghiệp, đồng hành dài lâu cùng doanh nghiệp Việt Nam.',
        'cn' => '提供專業 ERP/MES 顧問導入、系統培訓與 e-Service 線上技術支援，全方位陪伴越南企業穩健推進數位轉型。',
        'sc' => '提供专业 ERP/MES 顾问实施、系统培训与 e-Service 在线技术支持，全方位陪伴越南企业稳健推进数字化转型。',
    );

    $page_descriptions = array(
        'about-vn'     => $about_desc,
        'about-cn'     => $about_desc,
        'about-sc'     => $about_desc,
        'about-zh'     => $about_desc,
        'contact-vn'   => $contact_desc,
        'contact-cn'   => $contact_desc,
        'contact-sc'   => $contact_desc,
        'contact-zh'   => $contact_desc,
        'cases'        => array(
            'vn' => 'Khám phá các dự án chuyển đổi số thành công cùng Digiwin Việt Nam trong ngành chế tạo, điện tử, nhựa, cơ khí và nhiều lĩnh vực sản xuất tiêu biểu.',
            'cn' => '瀏覽鼎捷軟件(越南)跨產業成功案例，涵蓋製造業、電子、塑膠、金屬加工與醫療器材等領域的 ERP、MES 數位轉型實績。',
            'sc' => '浏览鼎捷软件(越南)跨行业成功案例，涵盖制造业、电子、塑胶、金属加工与医疗器材等领域的 ERP、MES 数字化转型实绩。',
        ),
        'solution'     => $solution_desc,
        'solutions'    => $solution_desc,
        'resource'     => array(
            'vn' => 'Tổng hợp bài viết chuyên sâu, tin tức ngành sản xuất, sách trắng chuyển đổi số và tài liệu giải pháp ERP, MES miễn phí từ chuyên gia Digiwin Việt Nam.',
            'cn' => '匯集鼎捷軟件(越南)專欄文章、產業趨勢洞察、數位轉型白皮書與 ERP/MES 專業資源下載，助企業掌握智慧製造新趨勢。',
            'sc' => '汇集鼎捷软件(越南)专栏文章、行业趋势洞察、数字化转型白皮书与 ERP/MES 专业资源下载，助企业掌握智能制造新趋势。',
        ),
        'activities'   => array(
            'vn' => 'Cập nhật hội thảo trực tuyến (Webinar), triển lãm công nghệ và các sự kiện chuyển đổi số mới nhất do Digiwin Việt Nam tổ chức cho doanh nghiệp.',
            'cn' => '掌握鼎捷軟件(越南)最新線上研討會、產業科技展會與企業數位轉型交流活動資訊，與專家面對面探討智慧製造實務。',
            'sc' => '掌握鼎捷软件(越南)最新在线研讨会、产业科技展会与企业数字化转型交流活动信息，与专家面对面探讨智能制造实务。',
        ),
        'industries'   => $industries_desc,
        'industry'     => $industries_desc,
        'service'      => $service_desc,
        'services'     => $service_desc,
        'join-digiwin' => array(
            'vn' => 'Gia nhập đội ngũ Digiwin Việt Nam để phát triển sự nghiệp trong lĩnh vực tư vấn triển khai ERP, MES và giải pháp công nghệ chuyển đổi số hàng đầu.',
            'cn' => '加入鼎捷軟件(越南)專業團隊，在 ERP、MES 與智慧製造數位轉型領域發揮專長，與亞太區頂尖顧問共同成長。',
            'sc' => '加入鼎捷软件(越南)专业团队，在 ERP、MES 与智能制造数字化转型领域发挥专长，与亚太区顶尖顾问共同成长。',
        ),
        'partner'      => array(
            'vn' => 'Hợp tác cùng Digiwin Việt Nam mở rộng hệ sinh thái công nghệ và giải pháp chuyển đổi số toàn diện cho cộng đồng doanh nghiệp sản xuất.',
            'cn' => '與鼎捷軟件(越南)攜手成為策略合作夥伴，共同建構製造業數位轉型生態系，為企業客戶創造更高營運價值。',
            'sc' => '与鼎捷软件(越南)携手成为战略合作伙伴，共同构建制造业数字化转型生态系，为企业客户创造更高运营价值。',
        ),
    );

    if (isset($page_descriptions[$normalized_slug][$lang_key])) {
        return $page_descriptions[$normalized_slug][$lang_key];
    }

    if ($post_id > 0) {
        $excerpt = dgw_extract_clean_excerpt($post_id, 155);
        if ($excerpt !== '') {
            return $excerpt;
        }
    }

    return $site['description'];
}

/**
 * Ngày thực hiện: 16/11/2026
 * Cập nhật: 17/11/2026 - Hỗ trợ thêm slug Giản thể about-sc/zh, contact-sc/zh
 * Chức năng: Lấy tiêu đề trang đã dịch theo locale hiện hành cho các Page dùng chung slug
 * Mục đích: Đồng bộ thẻ <title> và <meta name="description"> khi người dùng chuyển ngôn ngữ trên các trang Page
 *
 * @param string $page_slug Slug của trang hiện tại
 * @param int    $post_id   ID của Page (nếu có)
 * @return string
 */
function dgw_get_translated_page_title(string $page_slug, int $post_id = 0): string
{
    $normalized_slug = sanitize_title($page_slug);
    $lang            = function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn';

    if ($lang === 'sc' && !dgw_has_simplified_chinese_locale()) {
        if (in_array($normalized_slug, array('about-sc', 'about-zh'), true)) {
            return '关于我们';
        }
        if (in_array($normalized_slug, array('contact-sc', 'contact-zh'), true)) {
            return '联络我们';
        }
    }

    if ($normalized_slug !== '' && function_exists('menu_main_list')) {
        $menu_items = menu_main_list();
        if (isset($menu_items[$normalized_slug]['name'])) {
            return wp_strip_all_tags((string) $menu_items[$normalized_slug]['name']);
        }
    }

    $extra_page_titles = array(
        'about-vn'   => __('About Digiwin', 'dgw'),
        'about-cn'   => __('About Digiwin', 'dgw'),
        'about-sc'   => __('About Digiwin', 'dgw'),
        'about-zh'   => __('About Digiwin', 'dgw'),
        'contact-vn' => __('Contact Us', 'dgw'),
        'contact-cn' => __('Contact Us', 'dgw'),
        'contact-sc' => __('Contact Us', 'dgw'),
        'contact-zh' => __('Contact Us', 'dgw'),
        'industry'   => __('Industries', 'dgw'),
        'industries' => __('Industries', 'dgw'),
        'service'    => __('Service', 'dgw'),
        'services'   => __('Service', 'dgw'),
        'download'   => __('Download File digiwin', 'dgw'),
        'member'     => __('Member', 'dgw'),
        'news'       => __('Latest News', 'dgw'),
    );

    if (isset($extra_page_titles[$normalized_slug])) {
        return wp_strip_all_tags((string) $extra_page_titles[$normalized_slug]);
    }

    if ($post_id > 0) {
        $post_title = trim((string) get_the_title($post_id));
        if ($post_title !== '' && $post_title !== '...') {
            return wp_strip_all_tags($post_title);
        }
    }

    return $normalized_slug !== '' ? ucwords(str_replace(array('-', '_'), ' ', $normalized_slug)) : '';
}

/**
 * Ngày thực hiện: 16/11/2026
 * Chức năng: Trích xuất đoạn mô tả sạch từ excerpt hoặc nội dung bài viết (loại bỏ shortcode Divi & HTML)
 * Mục đích: Cung cấp fallback meta description chuẩn SEO (tối đa 155 ký tự) cho bài viết chi tiết
 *
 * @param int $post_id ID bài viết
 * @param int $max_len Độ dài tối đa của mô tả
 * @return string
 */
function dgw_extract_clean_excerpt(int $post_id, int $max_len = 155): string
{
    $raw = '';
    if (has_excerpt($post_id)) {
        $raw = (string) get_post_field('post_excerpt', $post_id);
    }
    if (trim($raw) === '') {
        $raw = (string) get_post_field('post_content', $post_id);
    }

    // Loại bỏ thẻ shortcode Divi Builder nhưng vẫn giữ nội dung văn bản bên trong nếu có
    $clean = preg_replace('/\[\/?et_pb_[^\]]*\]/', ' ', $raw);
    $clean = strip_shortcodes((string) $clean);
    $clean = wp_strip_all_tags($clean, true);
    $clean = trim((string) preg_replace('/\s+/', ' ', $clean));

    if ($clean === '') {
        return '';
    }

    return wp_html_excerpt($clean, $max_len, '...');
}

/**
 * Ngày thực hiện: 16/11/2026
 * Chức năng: Lấy dữ liệu SEO (title, description, keywords) cho một đối tượng WP_Term theo ngôn ngữ hiện hành
 * Mục đích: Dùng chung cho trang danh mục ?cate=... và trang archive taxonomy/category, tránh lặp logic
 *
 * @param WP_Term $term            Đối tượng term hiện tại
 * @param string  $lang            Mã ngôn ngữ ('cn' hoặc 'vn')
 * @param array   $site            Thông tin site từ dgw_get_seo_site_info()
 * @param bool    $allow_translate Cho phép ánh xạ sang term bản dịch đối ứng hay không
 * @return array{title: string, description: string, keywords: string}
 */
function dgw_get_term_seo_data(WP_Term $term, string $lang, array $site, bool $allow_translate = true): array
{
    if ($allow_translate && function_exists('dgw_get_translated_term')) {
        $translated_term = dgw_get_translated_term($term, $lang, $term->taxonomy);
        if ($translated_term instanceof WP_Term) {
            $term = $translated_term;
        }
    }

    $term_meta = get_option('taxonomy_' . $term->term_id);
    $seo_title = trim((string) ($term_meta['txtTitleSeo'] ?? ''));
    $seo_desc  = trim((string) ($term_meta['strDescriptionSeo'] ?? ''));
    $seo_key   = trim((string) ($term_meta['seo_keywords'] ?? ''));
    $term_desc = trim((string) wp_strip_all_tags((string) $term->description));

    $title = ($seo_title !== '') ? $seo_title : $term->name;
    if ($seo_desc !== '') {
        $description = $seo_desc;
    } elseif ($term_desc !== '') {
        $description = wp_html_excerpt($term_desc, 155, '...');
    } else {
        // [16/11/2026] - Câu mô tả tự nhiên theo danh mục & giá trị doanh nghiệp, không lặp chuỗi địa chỉ
        // [17/11/2026] - Hỗ trợ thêm Tiếng Trung Giản thể ('sc' -> zh-CN)
        if ($lang === 'cn') {
            $description = sprintf('探索%s「%s」相關解決方案與成功實務資訊，協助企業提升營運效能並推動智慧製造轉型。', $site['name'], $term->name);
        } elseif ($lang === 'sc') {
            $description = sprintf('探索%s「%s」相关解决方案与成功实务信息，助力企业提升运营效能并推动智能制造转型。', $site['name'], $term->name);
        } else {
            $description = sprintf('Khám phá giải pháp và nội dung chuyên sâu về %s từ %s, đồng hành tối ưu vận hành và chuyển đổi số doanh nghiệp.', $term->name, $site['name']);
        }
    }
    $keywords = ($seo_key !== '') ? $seo_key : trim($term->name . ', ' . $site['name'], ', ');

    return array(
        'title'       => $title,
        'description' => $description,
        'keywords'    => $keywords,
    );
}

/**
 * Ngày thực hiện: 16/11/2026
 * Chức năng: Tổng hợp ngữ cảnh SEO (title, description, keywords, site_name, site_address) theo ngôn ngữ hiện tại
 * Mục đích: Dùng chung cho cả dgw_seo_document_title_parts() và dgw_seo_meta_tags(), tránh trùng lặp logic
 *
 * @return array{title: string, description: string, keywords: string, site_name: string, site_address: string}
 */
function dgw_get_seo_context(): array
{
    $lang = function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn';
    $site = dgw_get_seo_site_info($lang);

    $title       = '';
    $description = $site['description'];
    $keywords    = $site['keywords'];

    if (is_home() || is_front_page()) {
        $title = $site['name'];
    } elseif (is_page()) {
        // [2026-11-16] - @author: Kelvin - Tách riêng nhánh is_page() để xử lý SEO theo cate, sp hoặc tiêu đề Page đã dịch
        $post_id   = (int) get_queried_object_id();
        $page_slug = (string) (get_query_var('pagename') ?: ($post_id ? get_post_field('post_name', $post_id) : ''));
        $cate      = get_query_var('cate');
        $sp        = get_query_var('sp');

        if (!empty($cate)) {
            $preferred_tax = dgw_detect_current_module_taxonomy($page_slug);
            $cate_obj      = dgw_get_category_by_param($cate, $preferred_tax);
            if ($cate_obj instanceof WP_Term) {
                $term_seo    = dgw_get_term_seo_data($cate_obj, $lang, $site, true);
                $title       = $term_seo['title'];
                $description = $term_seo['description'];
                $keywords    = $term_seo['keywords'];
            }
        } elseif (!empty($sp)) {
            $pro_arr = function_exists('get_product') ? get_product($sp) : array();
            if (!empty($pro_arr) && is_array($pro_arr)) {
                $title       = trim((string) ($pro_arr['seo_title'] ?? '')) ?: ($post_id ? get_the_title($post_id) : '');
                $description = trim((string) ($pro_arr['seo_description'] ?? '')) ?: $site['description'];
                $keywords    = trim((string) ($pro_arr['seo_key'] ?? '')) ?: $site['keywords'];
            }
        } else {
            $seo_title      = $post_id ? trim((string) get_post_meta($post_id, '_seo_title', true)) : '';
            $seo_desc       = $post_id ? trim((string) get_post_meta($post_id, '_seo_description', true)) : '';
            $seo_key        = $post_id ? trim((string) get_post_meta($post_id, '_seo_key', true)) : '';
            $fallback_title = dgw_get_translated_page_title($page_slug, $post_id);

            $title       = ($seo_title !== '') ? $seo_title : $fallback_title;
            $description = ($seo_desc !== '') ? $seo_desc : dgw_get_page_seo_description($page_slug, $post_id, $lang, $site);
            $keywords    = ($seo_key !== '') ? $seo_key : trim($fallback_title . ', ' . $site['name'], ', ');
        }
    } elseif (is_single()) {
        // [2026-11-16] - @author: Kelvin - Tách riêng nhánh is_single() để luôn lấy đúng dữ liệu SEO của bài viết chi tiết
        $post_id     = (int) get_queried_object_id();
        $seo_title   = $post_id ? trim((string) get_post_meta($post_id, '_seo_title', true)) : '';
        $seo_desc    = $post_id ? trim((string) get_post_meta($post_id, '_seo_description', true)) : '';
        $seo_key     = $post_id ? trim((string) get_post_meta($post_id, '_seo_key', true)) : '';
        $post_title  = $post_id ? trim((string) get_the_title($post_id)) : '';
        $excerpt     = $post_id ? dgw_extract_clean_excerpt($post_id) : '';

        $title       = ($seo_title !== '') ? $seo_title : $post_title;
        $description = ($seo_desc !== '') ? $seo_desc : ($excerpt !== '' ? $excerpt : $site['description']);
        $keywords    = ($seo_key !== '') ? $seo_key : trim($post_title . ', ' . $site['name'], ', ');
    } elseif (is_tax() || is_tag() || is_category()) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            $term_seo    = dgw_get_term_seo_data($term, $lang, $site, !is_tag());
            $title       = $term_seo['title'];
            $description = $term_seo['description'];
            $keywords    = $term_seo['keywords'];
        }
    }

    return array(
        'title'        => wp_strip_all_tags($title),
        'description'  => wp_strip_all_tags($description),
        'keywords'     => wp_strip_all_tags($keywords),
        'site_name'    => $site['name'],
        'site_address' => $site['address'],
    );
}

/**
 * Ngày thực hiện: 16/11/2026
 * Chức năng: Tự động cập nhật <title> theo ngôn ngữ Trung/Việt đang chọn qua filter document_title_parts
 * Mục đích: Đồng bộ cả phần tiêu đề trang lẫn tên website (site) theo dữ liệu đa ngôn ngữ của theme
 */
function dgw_seo_document_title_parts($title_parts)
{
    $seo = dgw_get_seo_context();

    if (is_home() || is_front_page()) {
        // [16/11/2026] - Trang chủ chỉ hiển thị tên công ty theo ngôn ngữ đang chọn để tối ưu độ dài thẻ <title> chuẩn SEO
        $title_parts['title'] = $seo['site_name'];
        unset($title_parts['tagline'], $title_parts['site']);
        return $title_parts;
    }

    if ($seo['title'] !== '') {
        $title_parts['title'] = $seo['title'];
    }

    if ($seo['site_name'] !== '' && $seo['title'] !== $seo['site_name']) {
        $title_parts['site'] = $seo['site_name'];
    } else {
        unset($title_parts['site']);
    }

    return $title_parts;
}

/**
 * Ngày thực hiện: 16/11/2026
 * Chức năng: Tạo URL chuẩn phân cấp cho trang danh mục dạng /{pagename}/cate/{cate_slug}/[tag/{tag_slug}/]
 * Mục đích: Dùng chung khi sinh canonical và hreflang cho các trang danh mục tùy biến của theme
 *
 * @param string $pagename  Slug của Page cha (vd: solution, cases, resource, activities)
 * @param string $cate_slug Slug của category
 * @param string $tag_slug  Slug của tag (nếu có)
 * @return string
 */
function dgw_build_cate_page_url(string $pagename, string $cate_slug, string $tag_slug = ''): string
{
    $path = trim($pagename, '/') . '/cate/' . $cate_slug;
    if ($tag_slug !== '') {
        $path .= '/tag/' . $tag_slug;
    }

    return home_url(user_trailingslashit($path));
}

/**
 * Ngày thực hiện: 16/11/2026
 * Cập nhật: 17/11/2026 - Hỗ trợ thêm Tiếng Trung Giản thể (zh-CN) khi có gói ngôn ngữ zh_CN hoặc tham số ?lang=sc
 * Chức năng: Tạo bộ canonical và hreflang cho các trang dùng chung URL giữa các ngôn ngữ
 * Mục đích: Đảm bảo Googlebot index đúng cả bản Tiếng Việt (mặc định), Trung Phồn thể (?lang=cn) và Trung Giản thể (?lang=sc nếu kích hoạt)
 *
 * @param string $base_url URL gốc không chứa query param ?lang
 * @param string $url_lang Ngôn ngữ truyền trên URL hiện tại ('vn', 'cn', 'sc' hoặc '')
 * @return array{canonical: string, hreflang: array<string, string>}
 */
function dgw_build_shared_lang_seo_urls(string $base_url, string $url_lang): array
{
    if ($base_url === '') {
        return array('canonical' => '', 'hreflang' => array());
    }

    $cn_url = add_query_arg('lang', 'cn', $base_url);
    $sc_url = add_query_arg('lang', 'sc', $base_url);

    $canonical = $base_url;
    if ($url_lang === 'cn') {
        $canonical = $cn_url;
    } elseif ($url_lang === 'sc') {
        $canonical = $sc_url;
    }

    $hreflang = array(
        'vi-VN' => $base_url,
        'zh-TW' => $cn_url,
    );

    if (dgw_has_simplified_chinese_locale() || $url_lang === 'sc') {
        $hreflang['zh-CN'] = $sc_url;
    }

    $hreflang['x-default'] = $base_url;

    return array(
        'canonical' => $canonical,
        'hreflang'  => $hreflang,
    );
}

/**
 * Ngày thực hiện: 16/11/2026
 * Cập nhật: 17/11/2026 - Bổ sung ánh xạ Tiếng Trung Giản thể (zh-CN) cho term có bản dịch 'sc'
 * Chức năng: Tìm danh sách URL hreflang (vi-VN, zh-TW, zh-CN, x-default) cho một đối tượng WP_Term dựa trên Translation Group
 * Mục đích: Khai báo tập term đối ứng giữa Tiếng Việt, Trung Phồn thể và Trung Giản thể cho Google
 *
 * @param WP_Term  $term         Term hiện tại
 * @param callable $url_resolver Callback nhận WP_Term và trả về URL của term đó
 * @param string   $fallback_url URL fallback nếu term chưa được gắn term_lang
 * @param string   $url_lang     Ngôn ngữ trên URL (?lang=) dùng cho fallback
 * @return array<string, string>
 */
function dgw_build_term_hreflang_urls(WP_Term $term, callable $url_resolver, string $fallback_url, string $url_lang): array
{
    $term_lang  = get_term_meta($term->term_id, 'term_lang', true);
    $locale_map = dgw_get_supported_locales_map();
    $hreflang   = array();

    foreach ($locale_map as $lang_key => $locale_info) {
        $translated = function_exists('dgw_get_translated_term') ? dgw_get_translated_term($term, $lang_key, $term->taxonomy) : null;
        if (!($translated instanceof WP_Term) && $term_lang === $lang_key) {
            $translated = $term;
        }
        if ($translated instanceof WP_Term) {
            $resolved = (string) $url_resolver($translated);
            if ($resolved !== '') {
                $hreflang[$locale_info['hreflang']] = $resolved;
            }
        }
    }

    if (empty($hreflang)) {
        $shared = dgw_build_shared_lang_seo_urls($fallback_url, $url_lang);
        return $shared['hreflang'];
    }

    $hreflang['x-default'] = $hreflang['vi-VN'] ?? reset($hreflang);
    return $hreflang;
}

/**
 * Ngày thực hiện: 16/11/2026
 * Cập nhật: 17/11/2026 - Hỗ trợ đầy đủ 3 ngôn ngữ (vi-VN, zh-TW, zh-CN) và đồng bộ paired pages (about-vn/cn/sc, contact-vn/cn/sc)
 * Chức năng: Tính toán thẻ canonical và tập hợp hreflang (vi-VN, zh-TW, zh-CN, x-default) cho trang hiện tại
 * Mục đích: Phục vụ Technical SEO đa ngôn ngữ chuẩn Google cho Trang chủ, Page, Danh mục, Bài viết chi tiết và Archive
 *
 * @return array{canonical: string, hreflang: array<string, string>}
 */
function dgw_get_seo_urls(): array
{
    if (is_404()) {
        return array('canonical' => '', 'hreflang' => array());
    }

    $url_lang = '';
    if (isset($_GET['lang'])) {
        $url_lang = dgw_normalize_lang_code(sanitize_text_field(wp_unslash($_GET['lang']))) ?? 'vn';
    }

    if (is_home() || is_front_page()) {
        $base_url = is_paged()
            ? get_pagenum_link(max(1, (int) get_query_var('paged')))
            : home_url('/');
        return dgw_build_shared_lang_seo_urls((string) $base_url, $url_lang);
    }

    if (is_page()) {
        $post_id   = (int) get_queried_object_id();
        $pagename  = (string) (get_query_var('pagename') ?: ($post_id ? get_post_field('post_name', $post_id) : ''));
        $cate      = get_query_var('cate');
        $sp        = get_query_var('sp');
        $tag_slug  = sanitize_title((string) get_query_var('tag'));

        if (!empty($cate)) {
            $preferred_tax = dgw_detect_current_module_taxonomy($pagename);
            $cate_obj      = dgw_get_category_by_param($cate, $preferred_tax);
            if ($cate_obj instanceof WP_Term) {
                $canonical = dgw_build_cate_page_url($pagename, $cate_obj->slug, $tag_slug);
                $hreflang  = dgw_build_term_hreflang_urls(
                    $cate_obj,
                    static function (WP_Term $t) use ($pagename, $tag_slug): string {
                        return dgw_build_cate_page_url($pagename, $t->slug, $tag_slug);
                    },
                    $canonical,
                    $url_lang
                );
                return array('canonical' => $canonical, 'hreflang' => $hreflang);
            }

            $fallback_url = dgw_build_cate_page_url($pagename, sanitize_title((string) $cate), $tag_slug);
            return dgw_build_shared_lang_seo_urls($fallback_url, $url_lang);
        }

        if (!empty($sp)) {
            $base_url = home_url(user_trailingslashit(trim($pagename, '/') . '/sp/' . sanitize_title((string) $sp)));
            return dgw_build_shared_lang_seo_urls($base_url, $url_lang);
        }

        $base_url = (string) (wp_get_canonical_url($post_id) ?: get_permalink($post_id));

        if (is_page('member')) {
            return array('canonical' => $base_url, 'hreflang' => array());
        }

        // [16/11/2026] - Dùng chung dgw_get_paired_page_urls() cho các trang tách slug theo ngôn ngữ
        // [17/11/2026] - Tự động xuất đồng bộ cả 3 thẻ vi-VN, zh-TW và zh-CN (nếu trang Giản thể tồn tại) kèm x-default
        $paired_urls = dgw_get_paired_page_urls($pagename);
        if (!empty($paired_urls)) {
            $locale_map = dgw_get_supported_locales_map();
            $hreflang   = array();

            foreach ($locale_map as $lang_key => $locale_info) {
                if (!empty($paired_urls[$lang_key])) {
                    $hreflang[$locale_info['hreflang']] = $paired_urls[$lang_key];
                }
            }
            if (!empty($hreflang)) {
                $hreflang['x-default'] = $hreflang['vi-VN'] ?? reset($hreflang);
                return array('canonical' => $base_url, 'hreflang' => $hreflang);
            }
        }

        return dgw_build_shared_lang_seo_urls($base_url, $url_lang);
    }

    if (is_single()) {
        $post_id   = (int) get_queried_object_id();
        $canonical = (string) (wp_get_canonical_url($post_id) ?: get_permalink($post_id));
        if ($canonical === '') {
            return array('canonical' => '', 'hreflang' => array());
        }

        $post_lang     = (string) get_post_meta($post_id, '_metabox_langguage', true);
        $locale_map    = dgw_get_supported_locales_map();
        $hreflang_code = $locale_map[$post_lang]['hreflang'] ?? 'vi-VN';

        return array(
            'canonical' => $canonical,
            'hreflang'  => array(
                $hreflang_code => $canonical,
                'x-default'    => $canonical,
            ),
        );
    }

    if (is_tax() || is_category() || is_tag()) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            $term_link = get_term_link($term);
            $canonical = (!is_wp_error($term_link) && !empty($term_link)) ? (string) $term_link : '';
            if ($canonical === '') {
                return array('canonical' => '', 'hreflang' => array());
            }

            if (is_tag()) {
                return dgw_build_shared_lang_seo_urls($canonical, $url_lang);
            }

            $hreflang = dgw_build_term_hreflang_urls(
                $term,
                static function (WP_Term $t): string {
                    $link = get_term_link($t);
                    return (!is_wp_error($link) && !empty($link)) ? (string) $link : '';
                },
                $canonical,
                $url_lang
            );
            return array('canonical' => $canonical, 'hreflang' => $hreflang);
        }
    }

    if (is_search()) {
        return array('canonical' => (string) get_search_link(), 'hreflang' => array());
    }

    if (is_archive()) {
        $paged    = max(1, (int) get_query_var('paged'));
        $base_url = (string) get_pagenum_link($paged);
        return dgw_build_shared_lang_seo_urls($base_url, $url_lang);
    }

    return array('canonical' => '', 'hreflang' => array());
}

/**
 * Ngày thực hiện: 16/11/2026
 * Chức năng: Lấy URL ảnh đại diện cho Open Graph (og:image)
 * Mục đích: Ưu tiên ảnh nổi bật (Featured Image) của bài viết/trang, fallback về logo chính của theme
 *
 * @return string
 */
function dgw_get_og_image_url(): string
{
    if (is_singular()) {
        $post_id = (int) get_queried_object_id();
        if ($post_id > 0 && has_post_thumbnail($post_id)) {
            $thumb_url = get_the_post_thumbnail_url($post_id, 'large');
            if (!empty($thumb_url)) {
                return (string) $thumb_url;
            }
        }
    }

    if (defined('PART_IMAGES')) {
        return PART_IMAGES . 'logo.png';
    }

    return get_stylesheet_directory_uri() . '/images/logo.png';
}

/**
 * Ngày thực hiện: 16/11/2026
 * Cập nhật: 17/11/2026 - Hỗ trợ đầy đủ og:locale và nhiều thẻ og:locale:alternate cho 3 ngôn ngữ (vi_VN, zh_TW, zh_CN)
 * Chức năng: Xuất các thẻ Open Graph (og:locale, og:locale:alternate, og:type, og:title, og:description, og:url, og:site_name, og:image) và Twitter Card
 * Mục đích: Đảm bảo mạng xã hội (Facebook, Zalo, LinkedIn, X...) hiển thị đúng ngôn ngữ và thông tin khi chia sẻ liên kết
 *
 * @param array $seo  Ngữ cảnh SEO từ dgw_get_seo_context()
 * @param array $urls Canonical và hreflang từ dgw_get_seo_urls()
 */
function dgw_render_open_graph_tags(array $seo, array $urls): void
{
    $lang       = function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn';
    $locale_map = dgw_get_supported_locales_map();
    $og_locale  = $locale_map[$lang]['og_locale'] ?? 'vi_VN';
    $og_type    = is_single() ? 'article' : 'website';
    $og_title   = ($seo['title'] !== '') ? $seo['title'] : $seo['site_name'];
    $og_url     = ($urls['canonical'] !== '') ? $urls['canonical'] : home_url('/');
    $og_image   = dgw_get_og_image_url();

    $meta_properties = array(
        'og:locale'      => $og_locale,
        'og:type'        => $og_type,
        'og:title'       => $og_title,
        'og:description' => $seo['description'],
        'og:url'         => $og_url,
        'og:site_name'   => $seo['site_name'],
        'og:image'       => $og_image,
    );

    foreach ($meta_properties as $property => $content) {
        if ($content === '') {
            continue;
        }
        $escaped_content = ($property === 'og:url' || $property === 'og:image')
            ? esc_url($content)
            : esc_attr($content);
        echo '<meta property="' . esc_attr($property) . '" content="' . $escaped_content . '" />' . "\n";
    }

    // Khai báo toàn bộ các ngôn ngữ đối ứng (vi_VN, zh_TW, zh_CN) nếu trang có URL bản dịch tương ứng
    foreach ($locale_map as $lang_key => $locale_info) {
        if ($lang_key === $lang) {
            continue;
        }
        if (!empty($urls['hreflang'][$locale_info['hreflang']])) {
            echo '<meta property="og:locale:alternate" content="' . esc_attr($locale_info['og_locale']) . '" />' . "\n";
        }
    }

    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
}

/**
 * Ngày thực hiện: 16/11/2026
 * Chức năng: Xuất thẻ <meta name="description">, <link rel="canonical">, <link rel="alternate" hreflang="..."> và Open Graph
 * Mục đích: Đồng bộ đầy đủ Technical SEO đa ngôn ngữ (vi-VN, zh-TW, x-default), canonical và chia sẻ mạng xã hội trong <head> (đã loại bỏ thẻ meta keywords lỗi thời)
 */
function dgw_seo_meta_tags()
{
    if (defined('RANK_MATH_VERSION')) {
        return;
    }

    $seo  = dgw_get_seo_context();
    $urls = dgw_get_seo_urls();

    if ($seo['description'] !== '') {
        echo '<meta name="description" content="' . esc_attr($seo['description']) . '" />' . "\n";
    }
    if ($urls['canonical'] !== '') {
        echo '<link rel="canonical" href="' . esc_url($urls['canonical']) . '" />' . "\n";
    }
    foreach ($urls['hreflang'] as $lang_code => $alt_url) {
        if ($alt_url !== '') {
            echo '<link rel="alternate" hreflang="' . esc_attr($lang_code) . '" href="' . esc_url($alt_url) . '" />' . "\n";
        }
    }

    dgw_render_open_graph_tags($seo, $urls);
}

function uploadFileDownLoad($File, $name)
{
    if (empty($File['file_upload']['name'])) {
        return '';
    }

    if (!empty($File['file_upload']['error']) && $File['file_upload']['error'] !== UPLOAD_ERR_OK) {
        return 'Upload error: ' . intval($File['file_upload']['error']);
    }

    $file_name = sanitize_file_name($File['file_upload']['name']);
    $file_size = intval($File['file_upload']['size']);
    $file_tmp = $File['file_upload']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed_ext = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'txt', 'csv', 'png', 'jpg', 'jpeg', 'gif');
    if (!in_array($file_ext, $allowed_ext, true)) {
        return '不支援的檔案格式';
    }

    if ($file_size > 10097152) {
        return '上傳檔案容量不可大於 10 MB';
    }

    $upload_dir = untrailingslashit(DIR_FILE) . DS;
    if (!file_exists($upload_dir) && !wp_mkdir_p($upload_dir)) {
        return '無法建立上傳資料夾';
    }

    if (!empty($name) && is_file($upload_dir . $name)) {
        unlink($upload_dir . $name);
    }

    $destination_name = wp_unique_filename($upload_dir, $file_name);
    $destination = $upload_dir . $destination_name;

    if (move_uploaded_file($file_tmp, $destination)) {
        return $destination_name;
    }

    return 'File upload failed.';
}

// Mục đích của đoạn code:
// Kiểm tra nếu người dùng đã đăng nhập vào WordPress.
// Đẩy biến is_internal: 'yes' lên DataLayer.
// Dựa vào biến này, mình sẽ cấu hình trên Google Tag Manager để chặn không kích hoạt thẻ GA4, giúp dữ liệu báo cáo sạch hơn.

add_action('wp_head', function () {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $role = (array) $user->roles;
    ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'user_status',
                'user_role': '<?php echo $role[0]; ?>',
                'is_internal': 'yes'
            });
        </script>
<?php
    }
});


add_action('wp_enqueue_scripts', 'remove_wp_block_library_css', 100);
function remove_wp_block_library_css()
{
    wp_dequeue_style('wp-block-library');          // 移除核心區塊樣式
    wp_dequeue_style('wp-block-library-theme');    // 移除主題區塊樣式
    wp_dequeue_style('wc-block-style');            // 如果有 WooCommerce，移除其區塊樣式
}



add_action('after_setup_theme', function () {
    add_image_size('casestudies-desktop', 410, 307, true); // crop đúng tỉ lệ
    add_image_size('casestudies-mobile', 150, 113, true);
    add_image_size('slider-desktop', 1920, 800, true);
    add_image_size('slider-mobile',  768,  500, true);
    add_image_size('card-desktop', 300, 200, true);
    add_image_size('card-mobile',  480, 320, true);
});


// Cho phép inline styles trong table
add_filter( 'safe_style_css', function( $styles ) {
    $styles[] = 'background-color';
    $styles[] = 'color';
    return $styles;
});

add_filter( 'wp_kses_allowed_html', function( $allowedposttags ) {
    $allowedposttags['table']['style'] = true;
    $allowedposttags['tr']['style'] = true;
    $allowedposttags['td']['style'] = true;
    $allowedposttags['th']['style'] = true;
    return $allowedposttags;
}, 10, 1 );


// ==================== EMAIL DEBUG ====================
// Bật debug cho PHPMailer - Chỉ khi WP_DEBUG = true
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('phpmailer_init', function($phpmailer) {
        // Bật debug mode
        $phpmailer->SMTPDebug = 2; // 0=off, 1=client, 2=client+server
        
        // Ghi log debug output
        $phpmailer->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: " . $str);
        };
    });
}


// Date: 2026-06-23
// Chức năng: Thêm thẻ meta noindex, nofollow riêng cho trang 'member' để chặn Google index
add_action( 'wp_head', 'custom_noindex_member_page' );
function custom_noindex_member_page() {
    // Kiểm tra slug của trang hiện tại, thay 'member' bằng slug chính xác nếu cần
    if ( is_page( 'member' ) ) {
        echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
    }
}

/**
 * Date: 2026-10-13
 * Function: Điều chỉnh chất lượng nén ảnh WebP/JPEG xuống 78% để tối ưu Core Web Vitals (FCP/LCP)
 */
add_filter('wp_editor_set_quality', 'digiwin_optimize_image_quality', 10, 2);
function digiwin_optimize_image_quality(int $quality, string $mime_type): int {
    if (in_array($mime_type, ['image/webp', 'image/jpeg'], true)) {
        return 78; // Mức cân bằng tối ưu giữa dung lượng và độ nét theo chuẩn Lighthouse
    }
    return $quality;
}