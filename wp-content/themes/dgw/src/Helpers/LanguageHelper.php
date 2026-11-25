<?php
declare(strict_types=1);

namespace DGW\Helpers;

use WP_Post;
use WP_Term;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Xử lý nhận diện ngôn ngữ, Translation Group, Paired Pages và đồng bộ locale/textdomain
 * [2026-11-20] - @author: Kelvin - Hợp nhất các nâng cấp từ nhánh language (paired pages, normalizeLangCode, supportedLocalesMap) theo chuẩn PSR-4
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
     * [17/11/2026] - Bảng cấu hình chuẩn hóa 3 ngôn ngữ của hệ thống (Tiếng Việt, Trung Phồn thể, Trung Giản thể)
     *
     * @return array<string, array{hreflang: string, og_locale: string, wp_locale: string}>
     */
    public static function getSupportedLocalesMap(): array
    {
        return [
            'vn' => ['hreflang' => 'vi-VN', 'og_locale' => 'vi_VN', 'wp_locale' => 'vi_VN'],
            'cn' => ['hreflang' => 'zh-TW', 'og_locale' => 'zh_TW', 'wp_locale' => 'zh_TW'],
            'sc' => ['hreflang' => 'zh-CN', 'og_locale' => 'zh_CN', 'wp_locale' => 'zh_CN'],
        ];
    }

    /**
     * [17/11/2026] - Chuẩn hóa mã ngôn ngữ đầu vào (từ URL/Cookie/Meta) về mã nội bộ ('vn', 'cn', 'sc')
     */
    public static function normalizeLangCode(string $rawLang): ?string
    {
        $normalized = strtolower(str_replace('_', '-', trim($rawLang)));

        if (in_array($normalized, ['vn', 'vi', 'vi-vn'], true)) {
            return 'vn';
        }
        if (in_array($normalized, ['cn', 'tw', 'zh-tw'], true)) {
            return 'cn';
        }
        if (in_array($normalized, ['sc', 'zh', 'zh-cn'], true)) {
            return 'sc';
        }

        return null;
    }

    /**
     * [17/11/2026] - Kiểm tra hệ thống đã có gói ngôn ngữ Tiếng Trung Giản thể (zh_CN) hoặc hook kích hoạt hay chưa
     */
    public static function hasSimplifiedChineseLocale(): bool
    {
        $langDir = get_template_directory() . '/languages';
        $hasFile = file_exists($langDir . '/zh_CN.l10n.php') || file_exists($langDir . '/zh_CN.mo');

        return (bool) apply_filters('dgw_enable_zh_cn_hreflang', $hasFile);
    }

    /**
     * [16/11/2026] - Bảng ánh xạ các trang Page tách URL riêng theo ngôn ngữ
     *
     * @return array<string, array{lang: string, group: array<string, array<int, string>>}>
     */
    public static function getPairedPagesMap(): array
    {
        $aboutGroup = [
            'vn' => ['about-vn'],
            'cn' => ['about-cn'],
            'sc' => ['about-sc', 'about-zh'],
        ];
        $contactGroup = [
            'vn' => ['contact-vn'],
            'cn' => ['contact-cn'],
            'sc' => ['contact-sc', 'contact-zh'],
        ];

        return [
            'about-vn'   => ['lang' => 'vn', 'group' => $aboutGroup],
            'about-cn'   => ['lang' => 'cn', 'group' => $aboutGroup],
            'about-sc'   => ['lang' => 'sc', 'group' => $aboutGroup],
            'about-zh'   => ['lang' => 'sc', 'group' => $aboutGroup],
            'contact-vn' => ['lang' => 'vn', 'group' => $contactGroup],
            'contact-cn' => ['lang' => 'cn', 'group' => $contactGroup],
            'contact-sc' => ['lang' => 'sc', 'group' => $contactGroup],
            'contact-zh' => ['lang' => 'sc', 'group' => $contactGroup],
        ];
    }

    /**
     * [16/11/2026] - Nhận diện slug trang hiện tại nếu thuộc nhóm trang tách URL theo ngôn ngữ
     */
    public static function detectPairedPageSlug(): ?string
    {
        $pairedMap = self::getPairedPagesMap();

        if (!empty($GLOBALS['wp_query'])) {
            $pagename = sanitize_title((string) get_query_var('pagename'));
            if ($pagename !== '' && isset($pairedMap[$pagename])) {
                return $pagename;
            }

            if (is_page()) {
                $queried = get_queried_object();
                if ($queried instanceof WP_Post) {
                    $postSlug = sanitize_title((string) $queried->post_name);
                    if (isset($pairedMap[$postSlug])) {
                        return $postSlug;
                    }
                }
            }
        }

        if (!empty($_SERVER['REQUEST_URI'])) {
            $requestPath = trim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            if ($requestPath !== '') {
                $segments = explode('/', $requestPath);
                $lastSlug = sanitize_title((string) end($segments));
                if (isset($pairedMap[$lastSlug])) {
                    return $lastSlug;
                }
            }
        }

        return null;
    }

    /**
     * [16/11/2026] - Nhận diện ngôn ngữ ('vn', 'cn' hoặc 'sc') từ các trang Page tách URL theo ngôn ngữ
     */
    public static function detectPairedPageLanguage(): ?string
    {
        $slug      = self::detectPairedPageSlug();
        $pairedMap = self::getPairedPagesMap();

        if ($slug !== null && isset($pairedMap[$slug]['lang'])) {
            return $pairedMap[$slug]['lang'];
        }

        return null;
    }

    /**
     * [16/11/2026] - Lấy tập URL đối ứng theo ngôn ngữ cho trang Page tách URL theo slug
     *
     * @return array{vn?: string, cn?: string, sc?: string}
     */
    public static function getPairedPageUrls(string $pageSlug): array
    {
        $pairedMap      = self::getPairedPagesMap();
        $normalizedSlug = sanitize_title($pageSlug);

        if (!isset($pairedMap[$normalizedSlug]['group'])) {
            return [];
        }

        $group = $pairedMap[$normalizedSlug]['group'];
        $urls  = [];

        foreach ($group as $langKey => $candidateSlugs) {
            $resolvedUrl = '';

            foreach ($candidateSlugs as $candidateSlug) {
                $targetPage = get_page_by_path($candidateSlug);
                if ($targetPage instanceof WP_Post && $targetPage->post_status === 'publish') {
                    $resolvedUrl = (string) get_permalink($targetPage);
                    break;
                }
            }

            if ($resolvedUrl === '') {
                if ($langKey === 'sc') {
                    if (in_array($normalizedSlug, $candidateSlugs, true)) {
                        $resolvedUrl = home_url(user_trailingslashit($normalizedSlug));
                    }
                } else {
                    $resolvedUrl = home_url(user_trailingslashit($candidateSlugs[0]));
                }
            }

            if ($resolvedUrl !== '') {
                $urls[$langKey] = $resolvedUrl;
            }
        }

        return $urls;
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
     * [18/11/2026] - Nhận diện ngôn ngữ ('vn', 'cn' hoặc 'sc') theo slug hoặc Term Meta term_lang
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
            if (preg_match('/[_-](cn|tw)$/', $cleanSlug)) {
                return 'cn';
            }
            if (preg_match('/[_-](sc|zh)$/', $cleanSlug)) {
                return 'sc';
            }
        }

        $termObj = SeoHelper::getCategoryByParam($cateParam);
        if ($termObj instanceof WP_Term) {
            $termLang = (string) get_term_meta($termObj->term_id, 'term_lang', true);
            if (in_array($termLang, ['vn', 'cn', 'sc'], true)) {
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
                $termObj = SeoHelper::getCategoryByParam((int) $term, $taxonomy ?: null);
            }
        } elseif (is_string($term)) {
            if (!empty($taxonomy)) {
                $termObj = get_term_by('slug', $term, $taxonomy);
            }
            if (!$termObj || is_wp_error($termObj)) {
                $termObj = SeoHelper::getCategoryByParam($term, $taxonomy ?: null);
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
     * [18/11/2026] - Lấy ngôn ngữ hiện hành ('vn', 'cn' hoặc 'sc')
     * [2026-11-20] - @author: Kelvin - Chỉ đọc _metabox_langguage trên is_single() và ưu tiên nhận diện paired page (about-vn/cn, contact-vn/cn)
     */
    public static function getLang(): string
    {
        $default = 'vn';

        if (wp_doing_ajax() && isset($_REQUEST['lang'])) {
            $normalized = self::normalizeLangCode(sanitize_text_field(wp_unslash((string) $_REQUEST['lang'])));
            if ($normalized !== null) {
                return $normalized;
            }
        }

        if (!is_admin() && is_single()) {
            $queriedId = get_queried_object_id();
            if ($queriedId) {
                $postLang = (string) get_post_meta($queriedId, '_metabox_langguage', true);
                if (in_array($postLang, ['cn', 'vn', 'sc'], true)) {
                    return $postLang;
                }
            }
        }

        if (!is_admin()) {
            $cateLang = self::detectCategoryLanguage();
            if (in_array($cateLang, ['cn', 'vn', 'sc'], true)) {
                return (string) $cateLang;
            }

            $pairedLang = self::detectPairedPageLanguage();
            if (in_array($pairedLang, ['cn', 'vn', 'sc'], true)) {
                return (string) $pairedLang;
            }
        }

        if (isset($_GET['lang'])) {
            $normalized = self::normalizeLangCode(sanitize_text_field(wp_unslash((string) $_GET['lang'])));
            return $normalized ?? 'vn';
        }

        if (isset($_COOKIE['site_lang'])) {
            $normalized = self::normalizeLangCode(sanitize_text_field(wp_unslash((string) $_COOKIE['site_lang'])));
            return $normalized ?? 'vn';
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
     * [18/11/2026] - Đồng bộ Cookie, Session và Locale theo bài viết Single, Category hoặc Paired Page
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
                $postLang = (string) get_post_meta($queriedId, '_metabox_langguage', true);
                if (in_array($postLang, ['cn', 'vn', 'sc'], true)) {
                    $targetLang = $postLang;
                }
            }
        }

        if (!$targetLang) {
            $cateLang = self::detectCategoryLanguage();
            if (in_array($cateLang, ['cn', 'vn', 'sc'], true)) {
                $targetLang = $cateLang;
            }
        }

        if (!$targetLang) {
            $pairedLang = self::detectPairedPageLanguage();
            if (in_array($pairedLang, ['cn', 'vn', 'sc'], true)) {
                $targetLang = $pairedLang;
            }
        }

        if ($targetLang) {
            $currentCookieLang    = $_COOKIE['site_lang'] ?? '';
            $_COOKIE['site_lang'] = $targetLang;

            if (!headers_sent() && $currentCookieLang !== $targetLang) {
                // [25/11/2026] - Đồng bộ cờ secure và samesite=Lax cho cookie site_lang
                RequestHelper::setSecureCookie(
                    'site_lang',
                    $targetLang,
                    time() + YEAR_IN_SECONDS,
                    false,
                    'Lax',
                    COOKIEPATH ? (string) COOKIEPATH : '/',
                    defined('COOKIE_DOMAIN') ? (string) COOKIE_DOMAIN : ''
                );
            }

            if (session_status() === PHP_SESSION_ACTIVE || !empty($_SESSION)) {
                $_SESSION['languages'] = $targetLang;
            }

            $localeMap    = self::getSupportedLocalesMap();
            $targetLocale = $localeMap[$targetLang]['wp_locale'] ?? 'vi_VN';
            if ($targetLocale === 'zh_CN' && !self::hasSimplifiedChineseLocale()) {
                $targetLocale = 'zh_TW';
            }
            if (function_exists('switch_to_locale')) {
                switch_to_locale($targetLocale);
            }
            self::reloadThemeTextdomain($targetLocale);
        }
    }

    /**
     * [18/11/2026] - Cố định locale Admin là zh_TW, đồng bộ Frontend theo getLang()
     */
    public static function filterDetermineLocale($locale)
    {
        if (is_admin() && !wp_doing_ajax()) {
            return 'zh_TW';
        }
        return self::filterLocale($locale);
    }

    /**
     * [18/11/2026] - Ánh xạ locale Frontend sang zh_TW, zh_CN hoặc vi_VN
     */
    public static function filterLocale($locale)
    {
        if (is_admin() && !wp_doing_ajax()) {
            return 'zh_TW';
        }

        $lang      = self::getLang();
        $localeMap = self::getSupportedLocalesMap();
        $wpLocale  = $localeMap[$lang]['wp_locale'] ?? 'vi_VN';
        if ($wpLocale === 'zh_CN' && !self::hasSimplifiedChineseLocale()) {
            return 'zh_TW';
        }

        return $wpLocale;
    }
}
