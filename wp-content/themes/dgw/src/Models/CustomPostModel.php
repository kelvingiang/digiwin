<?php
declare(strict_types=1);

namespace DGW\Models;

use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-10-05] - @author: Kelvin - Model: Xử lý truy vấn dữ liệu Custom Post Types theo chuẩn MVC
 * [2026-11-05] - Hỗ trợ Translation Group: Tự động gom các term trong cùng nhóm dịch để lấy bài viết chuẩn theo ngôn ngữ hiện hành
 * [2026-11-19] - @author: Kelvin - Tối ưu hiệu năng WP_Query (no_found_rows, update_post_term_cache, giới hạn unbounded query), gom helper dùng chung và memoization category group
 * [2026-11-20] - @author: Kelvin - Chuẩn hóa hằng số meta key, loại bỏ trùng lặp và chuyển từ model/ sang chuẩn PSR-4 src/Models/
 */
class CustomPostModel
{
    private const MAX_SAFE_LIMIT = 50;
    private const META_LANG      = '_metabox_langguage';
    private const META_ORDER     = '_metabox_order';
    private const META_HOME      = '_metabox_home';
    private const META_SIDEBAR   = '_metabox_sidebar';

    /**
     * Cache tĩnh trong một request cho resolve_category_terms tránh lặp query get_term/get_terms
     *
     * @var array<string, array{0: string, 1: array}>
     */
    private static array $resolvedTermsCache = [];

    /**
     * [2026-11-19] - @author: Kelvin - Chuẩn hóa giới hạn số bài viết, ngăn chặn unbounded query (-1) gây chậm DB
     */
    private static function normalize_limit(int $postCount): int
    {
        return $postCount <= 0 ? self::MAX_SAFE_LIMIT : $postCount;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Dựng cấu hình WP_Query dùng chung đã bật các cờ tối ưu TTFB
     * [20/11/2026] - Hỗ trợ truyền ngôn ngữ $lang tùy chọn cho các tác vụ AJAX
     */
    private static function build_base_args(string $postType, int $postCount, array $extraMetaQuery = [], array $extraArgs = [], ?string $lang = null): array
    {
        $resolvedLang = ($lang !== null && $lang !== '')
            ? $lang
            : (function_exists('dgw_get_lang') ? (string) dgw_get_lang() : 'vn');
        $metaQuery    = array_merge([
            [
                'key'     => self::META_LANG,
                'value'   => $resolvedLang,
                'compare' => '=',
            ],
        ], $extraMetaQuery);

        $hasTaxQuery = !empty($extraArgs['tax_query'])
            || !empty($extraArgs['category_name'])
            || !empty($extraArgs['resources_category']);

        $defaults = [
            'post_type'              => $postType,
            'posts_per_page'         => self::normalize_limit($postCount),
            'orderby'                => 'meta_value_num',
            'order'                  => 'DESC',
            'meta_key'               => self::META_ORDER,
            'meta_query'             => $metaQuery,
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_term_cache' => $hasTaxQuery,
        ];

        $merged = array_merge($defaults, $extraArgs);
        if (array_key_exists('meta_key', $extraArgs) && $extraArgs['meta_key'] === '') {
            unset($merged['meta_key']);
        }

        return $merged;
    }

    /**
     * [2026-11-05] - Helper gom nhóm Translation Group cho Category
     * [2026-11-19] - @author: Kelvin - Bổ sung memoization tĩnh trong request
     *
     * @param mixed  $cate     Term ID hoặc Slug
     * @param string $taxonomy Tên Taxonomy
     * @return array{0: string, 1: array}
     */
    private static function resolve_category_terms($cate, string $taxonomy): array
    {
        $cacheKey = $taxonomy . '|' . (is_scalar($cate) ? (string) $cate : serialize($cate));
        if (isset(self::$resolvedTermsCache[$cacheKey])) {
            return self::$resolvedTermsCache[$cacheKey];
        }

        $targetTerms = [$cate];
        $field       = is_numeric($cate) ? 'term_id' : 'slug';

        $termObj = is_numeric($cate)
            ? get_term((int) $cate, $taxonomy)
            : get_term_by('slug', (string) $cate, $taxonomy);

        if ($termObj && !is_wp_error($termObj)) {
            $groupId = get_term_meta($termObj->term_id, 'translation_group', true);
            if (!empty($groupId)) {
                $groupTerms = get_terms([
                    'taxonomy'   => $taxonomy,
                    'fields'     => 'ids',
                    'hide_empty' => false,
                    'meta_query' => [
                        [
                            'key'     => 'translation_group',
                            'value'   => $groupId,
                            'compare' => '=',
                        ],
                    ],
                ]);

                if (!empty($groupTerms) && !is_wp_error($groupTerms)) {
                    return self::$resolvedTermsCache[$cacheKey] = ['term_id', $groupTerms];
                }
            }
        }

        return self::$resolvedTermsCache[$cacheKey] = [$field, $targetTerms];
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy danh sách bài viết theo Post Type và ngôn ngữ hiện tại
     */
    public static function get_posts(string $postType, int $postCount): WP_Query
    {
        return new WP_Query(self::build_base_args($postType, $postCount));
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy danh sách bài viết theo Category/Taxonomy
     * [2026-11-05] - Cập nhật xử lý Translation Group tự động
     */
    public static function get_posts_by_cate(string $postType, $cate, int $postCount, string $taxonomy): WP_Query
    {
        [$field, $terms] = self::resolve_category_terms($cate, $taxonomy);

        $args = self::build_base_args($postType, $postCount, [], [
            'tax_query' => [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => $field,
                    'terms'    => $terms,
                ],
            ],
        ]);

        return new WP_Query($args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy bài viết hiển thị ở trang chủ (_metabox_home = 1)
     */
    public static function get_posts_at_home(string $postType, int $postCount): WP_Query
    {
        $args = self::build_base_args($postType, $postCount, [
            [
                'key'     => self::META_HOME,
                'value'   => '1',
                'compare' => '=',
            ],
        ]);

        return new WP_Query($args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy bài viết thuộc category hiển thị ở trang chủ
     */
    public static function get_posts_cate_at_home(string $postType, string $cateSlug, int $postCount): WP_Query
    {
        $args = self::build_base_args($postType, $postCount, [], [
            'resources_category' => $cateSlug,
        ]);

        return new WP_Query($args);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Tái sử dụng get_posts_by_cate() tránh lặp code cho sidebar category
     */
    public static function get_posts_at_side_cate(string $postType, int $postCount, string $taxonomy, $cate): WP_Query
    {
        return self::get_posts_by_cate($postType, $cate, $postCount, $taxonomy);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Tái sử dụng get_posts() tránh lặp code cho sidebar
     */
    public static function get_posts_at_side(string $postType, int $postCount): WP_Query
    {
        return self::get_posts($postType, $postCount);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lấy bài viết có bật hiển thị sidebar (_metabox_sidebar = 1) qua build_base_args()
     */
    public static function get_posts_show_sidebar(string $postType): WP_Query
    {
        $args = self::build_base_args(
            $postType,
            20,
            [
                [
                    'key'     => self::META_SIDEBAR,
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
            [
                'orderby'  => 'date',
                'meta_key' => '',
            ]
        );

        return new WP_Query($args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy bài viết loại standard post theo category
     */
    public static function get_post_category(string $cate, int $postCount): WP_Query
    {
        $args = self::build_base_args('post', $postCount, [], [
            'category_name' => $cate,
        ]);

        return new WP_Query($args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy bài viết loại standard post ở trang chủ
     */
    public static function get_post_category_at_home(string $cate, int $postCount): WP_Query
    {
        $args = self::build_base_args('post', $postCount, [
            [
                'key'     => self::META_HOME,
                'value'   => '1',
                'compare' => '=',
            ],
        ], [
            'category_name' => $cate,
        ]);

        return new WP_Query($args);
    }

    /**
     * [20/11/2026] - Truy vấn danh sách Hero Slider trang chủ chuẩn tối ưu TTFB
     */
    public static function get_sliders(int $limit = 10): WP_Query
    {
        return new WP_Query(self::build_base_args('slider', $limit));
    }

    /**
     * [20/11/2026] - Truy vấn AJAX Load More theo post type, category (Translation Group) và ngôn ngữ
     *
     * @param mixed $cateId
     */
    public static function get_load_more_posts(string $postType, $cateId, string $taxonomy, int $count, int $offset, ?string $lang = null): WP_Query
    {
        $extraArgs = [
            'post_status' => 'publish',
            'offset'      => max(0, $offset),
        ];

        if (!empty($cateId) && $taxonomy !== '') {
            [$field, $terms]        = self::resolve_category_terms($cateId, $taxonomy);
            $extraArgs['tax_query'] = [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => $field,
                    'terms'    => $terms,
                ],
            ];
        }

        return new WP_Query(self::build_base_args($postType, $count, [], $extraArgs, $lang));
    }

    /**
     * [20/11/2026] - Truy vấn AJAX Load More theo category_name
     */
    public static function get_load_more_by_category_name(string $postType, string $categoryName, int $count, int $offset): WP_Query
    {
        $extraArgs = [
            'post_status' => 'publish',
            'offset'      => max(0, $offset),
        ];

        if ($categoryName !== '') {
            $extraArgs['category_name'] = $categoryName;
        }

        return new WP_Query(self::build_base_args($postType, $count, [], $extraArgs));
    }

    /**
     * [20/11/2026] - Truy vấn AJAX Load More bài viết trong cùng nhóm
     */
    public static function get_load_more_in_group(string $postType, int $count, int $offset): WP_Query
    {
        return new WP_Query(self::build_base_args($postType, $count, [], [
            'post_status' => 'publish',
            'offset'      => max(0, $offset),
        ]));
    }
}
