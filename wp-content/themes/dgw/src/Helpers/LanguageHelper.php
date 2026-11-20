<?php
declare(strict_types=1);

namespace DGW\Helpers;

use WP_Term;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Xử lý nhận diện ngôn ngữ, Translation Group và đồng bộ locale/textdomain
 */
class LanguageHelper
{
    /**
     * [18/11/2026] - Đăng ký các hook đồng bộ ngôn ngữ Frontend/Backend
     */
    public static function register(): void
    {
        add_action('template_redirect', [self::class, 'syncLanguageContext'], 1);
        add_filter('admin_locale', static fn (): string => 'zh_TW');
        add_filter('determine_locale', [self::class, 'filterDetermineLocale']);
        add_filter('locale', [self::class, 'filterLocale']);
    }

    /**
     * [18/11/2026] - Nhận diện an toàn category slug hiện tại của trang
     */
    public static function detectCurrentCategorySlug(): ?string
    {
        $cate = get_query_var('cate');
        if (!empty($cate)) {
            return (string) $cate;
        }

        if (!is_admin() && (is_tax() || is_category())) {
            $queried = get_queried_object();
            if ($queried instanceof WP_Term) {
                return (string) $queried->slug;
            }
        }

        if (!empty($_SERVER['REQUEST_URI'])) {
            $requestPath = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if (preg_match('@/(?:cate|category)/([^/?#]+)(?:/(?:tag(?:/[^/?#]*)?)?)?/?$@i', $requestPath, $matches)) {
                return urldecode($matches[1]);
            }
        }

        return null;
    }

    /**
     * [18/11/2026] - Nhận diện ngôn ngữ ('vn' hoặc 'cn') theo slug hoặc Term Meta term_lang
     */
    public static function detectCategoryLanguage($cateParam = null): ?string
    {
        if (empty($cateParam)) {
            $cateParam = self::detectCurrentCategorySlug();
        }

        if (empty($cateParam)) {
            return null;
        }

        if (is_string($cateParam)) {
            $cleanSlug = strtolower(trim($cateParam));
            if (preg_match('/[_-](vn|vi)$/', $cleanSlug)) {
                return 'vn';
            }
            if (preg_match('/[_-](cn|tw|zh)$/', $cleanSlug)) {
                return 'cn';
            }
        }

        $termObj = SeoHelper::getCategoryByParam($cateParam);
        if ($termObj instanceof WP_Term) {
            $termLang = get_term_meta($termObj->term_id, 'term_lang', true);
            if ($termLang === 'vn' || $termLang === 'cn') {
                return $termLang;
            }
        }

        return null;
    }

    /**
     * [18/11/2026] - Tìm term bản dịch tương ứng theo Translation Group
     * [2026-11-20] - @author: Kelvin - Bổ sung fallback SeoHelper::getCategoryByParam() khi truyền ID số mà taxonomy mặc định không khớp
     */
    public static function getTranslatedTerm($term, string $targetLang, string $taxonomy = 'solutions_category'): ?WP_Term
    {
        $termObj = null;
        if ($term instanceof WP_Term) {
            $termObj = $term;
        } elseif (is_numeric($term)) {
            if (!empty($taxonomy)) {
                $termObj = get_term((int) $term, $taxonomy);
            }
            if (!$termObj || is_wp_error($termObj)) {
                $termObj = SeoHelper::getCategoryByParam((int) $term);
            }
        } elseif (is_string($term)) {
            if (!empty($taxonomy)) {
                $termObj = get_term_by('slug', $term, $taxonomy);
            }
            if (!$termObj || is_wp_error($termObj)) {
                $termObj = SeoHelper::getCategoryByParam($term);
            }
        }

        if (!$termObj || is_wp_error($termObj)) {
            return null;
        }

        $currentLang = get_term_meta($termObj->term_id, 'term_lang', true);
        if ($currentLang === $targetLang) {
            return $termObj;
        }

        $groupId = get_term_meta($termObj->term_id, 'translation_group', true) ?: $termObj->term_id;
        if (empty($groupId)) {
            return null;
        }

        $tax = !empty($termObj->taxonomy) ? $termObj->taxonomy : $taxonomy;

        $linkedTerms = get_terms([
            'taxonomy'   => $tax,
            'hide_empty' => false,
            'meta_query' => [
                [
                    'key'     => 'translation_group',
                    'value'   => $groupId,
                    'compare' => '=',
                ],
                [
                    'key'     => 'term_lang',
                    'value'   => $targetLang,
                    'compare' => '=',
                ],
            ],
        ]);

        if (!empty($linkedTerms) && !is_wp_error($linkedTerms)) {
            return reset($linkedTerms) ?: null;
        }

        return null;
    }

    /**
     * [18/11/2026] - Lấy ngôn ngữ hiện hành ('vn' hoặc 'cn')
     * [2026-11-20] - @author: Kelvin - Chỉ đọc _metabox_langguage trên is_single() thay vì is_singular() để tránh Page cố định đè lên cookie site_lang
     */
    public static function getLang(): string
    {
        $default = 'vn';

        if (wp_doing_ajax() && isset($_REQUEST['lang'])) {
            $lang = sanitize_text_field(wp_unslash($_REQUEST['lang']));
            if ($lang === 'cn' || $lang === 'zh-TW') {
                return 'cn';
            }
            if ($lang === 'vi-VN' || $lang === 'vn') {
                return 'vn';
            }
        }

        if (!is_admin() && is_single()) {
            $queriedId = get_queried_object_id();
            if ($queriedId) {
                $postLang = get_post_meta($queriedId, '_metabox_langguage', true);
                if ($postLang === 'cn' || $postLang === 'vn') {
                    return $postLang;
                }
            }
        }

        if (!is_admin()) {
            $cateLang = self::detectCategoryLanguage();
            if ($cateLang === 'cn' || $cateLang === 'vn') {
                return $cateLang;
            }
        }

        if (isset($_GET['lang'])) {
            $lang = sanitize_text_field(wp_unslash($_GET['lang']));
            return ($lang === 'cn' || $lang === 'zh-TW') ? 'cn' : 'vn';
        }

        if (isset($_COOKIE['site_lang'])) {
            $lang = sanitize_text_field(wp_unslash($_COOKIE['site_lang']));
            return $lang === 'cn' ? 'cn' : 'vn';
        }

        return $default;
    }

    /**
     * [18/11/2026] - Nạp lại textdomain theo locale chỉ định (.l10n.php hoặc .mo)
     */
    public static function reloadThemeTextdomain(string $locale): void
    {
        $langDir = get_template_directory() . '/languages';
        $phpFile = $langDir . '/' . $locale . '.l10n.php';
        $moFile  = $langDir . '/' . $locale . '.mo';

        unload_textdomain('dgw');
        if (file_exists($phpFile)) {
            load_textdomain('dgw', $phpFile, $locale);
        } elseif (file_exists($moFile)) {
            load_textdomain('dgw', $moFile, $locale);
        } else {
            load_theme_textdomain('dgw', $langDir);
        }
    }

    /**
     * [18/11/2026] - Đồng bộ Cookie, Session và Locale theo bài viết Single hoặc Category
     * [2026-11-20] - @author: Kelvin - Sử dụng is_single() thay vì is_singular() và luôn đồng bộ textdomain theo getLang()
     */
    public static function syncLanguageContext(): void
    {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        $targetLang = null;

        if (is_single()) {
            $queriedId = get_queried_object_id();
            if ($queriedId) {
                $postLang = get_post_meta($queriedId, '_metabox_langguage', true);
                if ($postLang === 'cn' || $postLang === 'vn') {
                    $targetLang = $postLang;
                }
            }
        }

        if (!$targetLang) {
            $cateLang = self::detectCategoryLanguage();
            if ($cateLang === 'cn' || $cateLang === 'vn') {
                $targetLang = $cateLang;
            }
        }

        if ($targetLang) {
            $currentCookieLang    = $_COOKIE['site_lang'] ?? '';
            $_COOKIE['site_lang'] = $targetLang;

            if (!headers_sent() && $currentCookieLang !== $targetLang) {
                setcookie(
                    'site_lang',
                    $targetLang,
                    time() + YEAR_IN_SECONDS,
                    COOKIEPATH ? COOKIEPATH : '/',
                    COOKIE_DOMAIN,
                    is_ssl(),
                    false
                );
            }

            if (session_status() === PHP_SESSION_ACTIVE || !empty($_SESSION)) {
                $_SESSION['languages'] = $targetLang;
            }

            $targetLocale = ($targetLang === 'cn') ? 'zh_TW' : 'vi_VN';
            if (function_exists('switch_to_locale')) {
                switch_to_locale($targetLocale);
            }
            self::reloadThemeTextdomain($targetLocale);
        }
    }

    /**
     * [18/11/2026] - Cố định locale Admin là zh_TW
     * [2026-11-20] - @author: Kelvin - Đồng bộ determine_locale trên Frontend theo getLang()
     */
    public static function filterDetermineLocale($locale)
    {
        if (is_admin() && !wp_doing_ajax()) {
            return 'zh_TW';
        }
        return (self::getLang() === 'cn') ? 'zh_TW' : 'vi_VN';
    }

    /**
     * [18/11/2026] - Ánh xạ locale Frontend sang zh_TW hoặc vi_VN
     */
    public static function filterLocale($locale)
    {
        if (is_admin() && !wp_doing_ajax()) {
            return 'zh_TW';
        }

        return (self::getLang() === 'cn') ? 'zh_TW' : 'vi_VN';
    }
}
