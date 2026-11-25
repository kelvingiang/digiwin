<?php
declare(strict_types=1);

namespace DGW\Core;

use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Quản lý Rewrite Rule, Permalink phân cấp chuẩn SEO và 301 Redirect URL cũ
 */
final class RewriteRules
{
    private const REWRITE_VERSION = '1.0.6';

    private const MODULES_CONFIG = [
        'cases' => [
            'post_type'     => 'casestudies',
            'taxonomy'      => 'casestudies_category',
            'legacy_prefix' => 'casestudies',
        ],
        'solutions' => [
            'post_type'     => 'solutions',
            'taxonomy'      => 'solutions_category',
            'legacy_prefix' => 'solutions',
        ],
        'resource' => [
            'post_type'     => 'resources',
            'taxonomy'      => 'resources_category',
            'legacy_prefix' => 'resources',
        ],
        'activities' => [
            'post_type'     => 'active',
            'taxonomy'      => 'active_category',
            'legacy_prefix' => 'active',
        ],
    ];

    /**
     * [20/11/2026] - Khởi tạo RewriteRules qua static method để đồng bộ chuẩn Bootstrap PSR-4 (giữ __construct cho legacy alias)
     */
    public static function register(): void
    {
        new self();
    }

    public function __construct()
    {
        add_action('init', [$this, 'register_rewrite_rules'], 10);
        add_filter('query_vars', [$this, 'register_query_vars'], 10);
        add_filter('post_type_link', [$this, 'filter_dynamic_post_type_link'], 10, 2);
        add_action('template_redirect', [$this, 'redirect_legacy_module_urls'], 10);
        add_action('after_switch_theme', [$this, 'flush_rules_on_activation']);
        add_action('init', [$this, 'maybe_flush_rewrite_rules'], 99);
    }

    /**
     * [18/11/2026] - Đăng ký các Rewrite Rules phân cấp động theo từng module
     */
    public function register_rewrite_rules(): void
    {
        foreach (self::MODULES_CONFIG as $urlBase => $config) {
            $postType = $config['post_type'];

            add_rewrite_rule(
                '^' . preg_quote($urlBase, '/') . '/cate/([^/]+)/(?!tag(?:/|$))([^/]+)/?$',
                'index.php?' . $postType . '=$matches[2]',
                'top'
            );

            if ($urlBase === 'solutions') {
                add_rewrite_rule(
                    '^solution/cate/([^/]+)/(?!tag(?:/|$))([^/]+)/?$',
                    'index.php?' . $postType . '=$matches[2]',
                    'top'
                );
            } elseif ($urlBase === 'resource') {
                add_rewrite_rule(
                    '^resources/cate/([^/]+)/(?!tag(?:/|$))([^/]+)/?$',
                    'index.php?' . $postType . '=$matches[2]',
                    'top'
                );
            } elseif ($urlBase === 'activities') {
                add_rewrite_rule(
                    '^active/cate/([^/]+)/(?!tag(?:/|$))([^/]+)/?$',
                    'index.php?' . $postType . '=$matches[2]',
                    'top'
                );
            }
        }

        add_rewrite_rule(
            '^([^/]+)/cate/([^/]+)/tag/([^/]+)/?$',
            'index.php?pagename=$matches[1]&cate=$matches[2]&tag=$matches[3]',
            'top'
        );

        add_rewrite_rule(
            '^([^/]+)/cate/([^/]+)(?:/tag)?/?$',
            'index.php?pagename=$matches[1]&cate=$matches[2]',
            'top'
        );

        add_rewrite_rule(
            '^([^/]+)/sp/([^/]+)/?$',
            'index.php?pagename=$matches[1]&sp=$matches[2]',
            'top'
        );
    }

    /**
     * [18/11/2026] - Định dạng lại permalink CPT về /{url_base}/cate/{term_slug}/{post_slug}/
     */
    public function filter_dynamic_post_type_link(string $postLink, WP_Post $post): string
    {
        if (empty($post->post_name)) {
            return $postLink;
        }

        if (!in_array($post->post_status, ['publish', 'private'], true)) {
            return $postLink;
        }

        $matchedConfig = null;
        $matchedBase   = null;

        foreach (self::MODULES_CONFIG as $urlBase => $config) {
            if ($config['post_type'] === $post->post_type) {
                $matchedConfig = $config;
                $matchedBase   = $urlBase;
                break;
            }
        }

        if (!$matchedConfig || !$matchedBase) {
            return $postLink;
        }

        $terms    = wp_get_object_terms($post->ID, $matchedConfig['taxonomy']);
        $termSlug = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->slug : 'all';

        return home_url(user_trailingslashit("{$matchedBase}/cate/{$termSlug}/{$post->post_name}"));
    }

    /**
     * [18/11/2026] - 301 Redirect URL cũ không phân cấp sang URL chuẩn SEO
     */
    public function redirect_legacy_module_urls(): void
    {
        if (isset($_GET['et_fb']) || isset($_GET['et_bfb']) || is_preview()) {
            return;
        }

        $currentPostType = get_post_type();
        if (!$currentPostType || !is_singular($currentPostType)) {
            return;
        }

        foreach (self::MODULES_CONFIG as $config) {
            if ($config['post_type'] === $currentPostType) {
                $requestUri = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');

                if (!str_contains($requestUri, '/cate/')) {
                    $postId       = get_queried_object_id();
                    $canonicalUrl = get_permalink($postId);

                    if (!empty($canonicalUrl) && untrailingslashit($canonicalUrl) !== untrailingslashit(home_url($requestUri))) {
                        $queryString = $_SERVER['QUERY_STRING'] ?? '';
                        if (!empty($queryString)) {
                            $canonicalUrl .= '?' . $queryString;
                        }
                        wp_safe_redirect($canonicalUrl, 301);
                        exit;
                    }
                }
            }
        }
    }

    /**
     * [18/11/2026] - Bổ sung custom query vars cho WP_Query
     */
    public function register_query_vars(array $vars): array
    {
        $customVars = ['sp', 'cate', 'cat', 'ts', 'active'];

        foreach ($customVars as $var) {
            if (!in_array($var, $vars, true)) {
                $vars[] = $var;
            }
        }

        return $vars;
    }

    public function flush_rules_on_activation(): void
    {
        $this->register_rewrite_rules();
        flush_rewrite_rules(false);
    }

    public function maybe_flush_rewrite_rules(): void
    {
        $currentVersion = get_option('theme_rewrite_rules_version');

        if ($currentVersion !== self::REWRITE_VERSION) {
            $this->register_rewrite_rules();
            flush_rewrite_rules(false);
            update_option('theme_rewrite_rules_version', self::REWRITE_VERSION, false);
        }
    }
}
