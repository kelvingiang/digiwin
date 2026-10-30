<?php
declare(strict_types=1);

/**
 * ============================================================================
 * [2026-10-13] - @author: Kelvin - Tối ưu hóa Rewrite Rule & Permalink phân cấp chuẩn SEO
 * File: inc/code/code-rewrite.php
 * Mục đích:
 * 1. Chuyển đổi toàn bộ link chi tiết bài viết sang cấu trúc danh mục phân cấp:
 *    - Case Studies: /{cases}/cate/{term_slug}/{post_slug}/
 *    - Solutions:    /{solutions}/cate/{term_slug}/{post_slug}/
 *    - Resources:    /{resource}/cate/{term_slug}/{post_slug}/
 *    - Activities:   /{activities}/cate/{term_slug}/{post_slug}/
 * 2. Tự động ánh xạ chuẩn xác CPT và Taxonomy thực tế của theme.
 * 3. Hỗ trợ 301 Redirect tự động từ các link cũ (vd: /casestudies/{slug}/ -> /cases/cate/{term}/{slug}/) bảo toàn SEO.
 * 4. Tự động flush rewrite rules khi cập nhật version.
 * ============================================================================
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Rewrite_Url
{
    /**
     * [2026-10-30] - Tăng version để tự động flush rewrite rules duy nhất 1 lần trên môi trường Apache
     */
    private const REWRITE_VERSION = '1.0.6';

    /**
     * [2026-10-13] - Bảng cấu hình ánh xạ URL chuẩn cho tất cả module:
     * - url_base:      Tiền tố URL danh mục ngoài frontend (cases, solutions, resource, activities)
     * - post_type:     Tên định danh Custom Post Type chuẩn trong hệ thống
     * - taxonomy:      Tên định danh Taxonomy danh mục tương ứng
     * - legacy_prefix: Tiền tố URL cũ để tự động 301 redirect sang URL phân cấp mới
     */
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
     * [2026-10-13] - Đăng ký các Rewrite Rules phân cấp động theo từng module
     */
    public function register_rewrite_rules(): void
    {
        // 1. Đăng ký động rule chi tiết bài viết: /{base}/cate/{term_slug}/{post_slug}/
        foreach (self::MODULES_CONFIG as $url_base => $config) {
            $post_type = $config['post_type'];

            // Match URL bài viết: /{base}/cate/{term}/{post_slug}/ (loại trừ đường dẫn tag)
            add_rewrite_rule(
                '^' . preg_quote($url_base, '/') . '/cate/([^/]+)/(?!tag(?:/|$))([^/]+)/?$',
                'index.php?' . $post_type . '=$matches[2]',
                'top'
            );

            // Dự phòng các biến thể số ít / số nhiều (solution vs solutions, resource vs resources)
            if ($url_base === 'solutions') {
                add_rewrite_rule(
                    '^solution/cate/([^/]+)/(?!tag(?:/|$))([^/]+)/?$',
                    'index.php?' . $post_type . '=$matches[2]',
                    'top'
                );
            } elseif ($url_base === 'resource') {
                add_rewrite_rule(
                    '^resources/cate/([^/]+)/(?!tag(?:/|$))([^/]+)/?$',
                    'index.php?' . $post_type . '=$matches[2]',
                    'top'
                );
            } elseif ($url_base === 'activities') {
                add_rewrite_rule(
                    '^active/cate/([^/]+)/(?!tag(?:/|$))([^/]+)/?$',
                    'index.php?' . $post_type . '=$matches[2]',
                    'top'
                );
            }
        }

        // 2. Rule danh mục kết hợp Tag: /{pagename}/cate/{cate_slug}/tag/{tag_slug}/
        add_rewrite_rule(
            '^([^/]+)/cate/([^/]+)/tag/([^/]+)/?$',
            'index.php?pagename=$matches[1]&cate=$matches[2]&tag=$matches[3]',
            'top'
        );

        // 3. Rule danh mục: /{pagename}/cate/{cate_slug}/
        add_rewrite_rule(
            '^([^/]+)/cate/([^/]+)(?:/tag)?/?$',
            'index.php?pagename=$matches[1]&cate=$matches[2]',
            'top'
        );

        // 4. Rule sản phẩm tùy biến: /{pagename}/sp/{sp_slug}/
        add_rewrite_rule(
            '^([^/]+)/sp/([^/]+)/?$',
            'index.php?pagename=$matches[1]&sp=$matches[2]',
            'top'
        );
    }

    /**
     * [2026-10-30] - @author: Senior Full-Stack Developer - Tự động định dạng lại permalink khi gọi get_permalink() hoặc the_permalink()
     * Đưa link bài viết về dạng: /{url_base}/cate/{term_slug}/{post_slug}/
     * Fix lỗi Divi Builder: Không can thiệp nếu bài viết chưa có slug (draft/auto-draft) hoặc chưa publish để Divi load đúng ID bài viết
     */
    public function filter_dynamic_post_type_link(string $post_link, WP_Post $post): string
    {
        // 1. Nếu post_name rỗng (bài viết mới tạo, auto-draft hoặc draft chưa có slug) -> giữ nguyên permalink gốc của WP
        if (empty($post->post_name)) {
            return $post_link;
        }

        // 2. Nếu bài viết chưa xuất bản (draft, pending, auto-draft) -> giữ link preview mặc định (?p=ID&post_type=...) cho Divi Builder
        if (!in_array($post->post_status, ['publish', 'private'], true)) {
            return $post_link;
        }

        $matched_config = null;
        $matched_base   = null;

        foreach (self::MODULES_CONFIG as $url_base => $config) {
            if ($config['post_type'] === $post->post_type) {
                $matched_config = $config;
                $matched_base   = $url_base;
                break;
            }
        }

        if (!$matched_config || !$matched_base) {
            return $post_link;
        }

        // Lấy term thuộc taxonomy chính của post
        $terms = wp_get_object_terms($post->ID, $matched_config['taxonomy']);
        $term_slug = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->slug : 'all';

        return home_url(user_trailingslashit("{$matched_base}/cate/{$term_slug}/{$post->post_name}"));
    }

    /**
     * [2026-10-30] - @author: Senior Full-Stack Developer - Technical SEO: 301 Redirect tự động từ URL cũ không có phân cấp sang URL phân cấp mới
     * Bỏ qua chuyển hướng nếu đang mở Divi Builder (et_fb, et_bfb) hoặc preview để tránh mất tham số và lỗi iframe builder
     */
    public function redirect_legacy_module_urls(): void
    {
        // Bỏ qua chuyển hướng nếu là request của Divi Builder hoặc Preview
        if (isset($_GET['et_fb']) || isset($_GET['et_bfb']) || is_preview()) {
            return;
        }

        $current_post_type = get_post_type();
        if (!$current_post_type || !is_singular($current_post_type)) {
            return;
        }

        foreach (self::MODULES_CONFIG as $url_base => $config) {
            if ($config['post_type'] === $current_post_type) {
                $request_uri = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');

                // Nếu request không chứa /cate/ (tức là URL cũ dạng /{legacy_prefix}/{slug}/ hoặc /{base}/{slug}/)
                if (!str_contains($request_uri, '/cate/')) {
                    $post_id       = get_queried_object_id();
                    $canonical_url = get_permalink($post_id);

                    if (!empty($canonical_url) && untrailingslashit($canonical_url) !== untrailingslashit(home_url($request_uri))) {
                        // Bảo lưu toàn bộ query string (nếu có) khi thực hiện 301 redirect
                        $query_string = $_SERVER['QUERY_STRING'] ?? '';
                        if (!empty($query_string)) {
                            $canonical_url .= '?' . $query_string;
                        }
                        wp_safe_redirect($canonical_url, 301);
                        exit;
                    }
                }
            }
        }
    }

    /**
     * Thêm các tham số nhận từ URL vào danh sách cho phép của WP_Query.
     *
     * @param array<int, string> $vars
     * @return array<int, string>
     */
    public function register_query_vars(array $vars): array
    {
        $custom_vars = ['sp', 'cate', 'cat', 'ts', 'active'];

        foreach ($custom_vars as $var) {
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
        $current_version = get_option('theme_rewrite_rules_version');

        if ($current_version !== self::REWRITE_VERSION) {
            $this->register_rewrite_rules();
            flush_rewrite_rules(false);
            update_option('theme_rewrite_rules_version', self::REWRITE_VERSION, false);
        }
    }
}

new Rewrite_Url();