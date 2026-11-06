<?php
declare(strict_types=1);

/**
 * [2026-10-05] - @author: Kelvin - Model: Xử lý truy vấn dữ liệu Custom Post Types theo chuẩn MVC
 * [2026-11-05] - Hỗ trợ Translation Group: Tự động gom các term trong cùng nhóm dịch để lấy bài viết chuẩn theo ngôn ngữ hiện hành
 */

if (!defined('ABSPATH')) {
    exit;
}

class Model_Custom_Post
{
    /**
     * [2026-10-05] - @author: Kelvin - Lấy danh sách bài viết theo Post Type và ngôn ngữ hiện tại
     */
    public static function get_posts(string $postType, int $postCount): WP_Query
    {
        $args = [
            'post_type'      => $postType,
            'posts_per_page' => $postCount,
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'meta_key'       => '_metabox_order',
            'meta_query'     => [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => dgw_get_lang(),
                    'compare' => '=',
                ],
            ],
        ];

        return new WP_Query($args);
    }

    /**
     * [2026-11-05] - Helper gom nhóm Translation Group cho Category
     * 
     * @param mixed $cate Term ID hoặc Slug
     * @param string $taxonomy Tên Taxonomy
     * @return array [field ('term_id' hoặc 'slug'), terms (array IDs hoặc Slugs)]
     */
    private static function resolve_category_terms($cate, string $taxonomy): array
    {
        $target_terms = [$cate];
        $field        = is_numeric($cate) ? 'term_id' : 'slug';

        $term_obj = is_numeric($cate)
            ? get_term((int) $cate, $taxonomy)
            : get_term_by('slug', (string) $cate, $taxonomy);

        if ($term_obj && !is_wp_error($term_obj)) {
            $group_id = get_term_meta($term_obj->term_id, 'translation_group', true);
            if (!empty($group_id)) {
                $group_terms = get_terms([
                    'taxonomy'   => $taxonomy,
                    'fields'     => 'ids',
                    'hide_empty' => false,
                    'meta_query' => [
                        [
                            'key'     => 'translation_group',
                            'value'   => $group_id,
                            'compare' => '=',
                        ],
                    ],
                ]);

                if (!empty($group_terms) && !is_wp_error($group_terms)) {
                    return ['term_id', $group_terms];
                }
            }
        }

        return [$field, $target_terms];
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy danh sách bài viết theo Category/Taxonomy
     * [2026-11-05] - Cập nhật xử lý Translation Group tự động
     */
    public static function get_posts_by_cate(string $postType, $cate, int $postCount, string $taxonomy): WP_Query
    {
        [$field, $terms] = self::resolve_category_terms($cate, $taxonomy);

        $args = [
            'post_type'      => $postType,
            'posts_per_page' => $postCount,
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'meta_key'       => '_metabox_order',
            'tax_query'      => [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => $field,
                    'terms'    => $terms,
                ],
            ],
            'meta_query'     => [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => dgw_get_lang(),
                    'compare' => '=',
                ],
            ],
        ];

        return new WP_Query($args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy bài viết hiển thị ở trang chủ (_metabox_home = 1)
     */
    public static function get_posts_at_home(string $postType, int $postCount): WP_Query
    {
        $args = [
            'post_type'      => $postType,
            'posts_per_page' => $postCount,
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'meta_key'       => '_metabox_order',
            'meta_query'     => [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => dgw_get_lang(),
                    'compare' => '=',
                ],
                [
                    'key'     => '_metabox_home',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
        ];

        return new WP_Query($args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy bài viết thuộc category hiển thị ở trang chủ
     */
    public static function get_posts_cate_at_home(string $postType, string $cateSlug, int $postCount): WP_Query
    {
        $args = [
            'post_type'          => $postType,
            'resources_category' => $cateSlug,
            'posts_per_page'     => $postCount,
            'orderby'            => 'meta_value_num',
            'order'              => 'DESC',
            'meta_key'           => '_metabox_order',
            'meta_query'         => [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => dgw_get_lang(),
                    'compare' => '=',
                ],
            ],
        ];

        return new WP_Query($args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy bài viết sidebar theo category
     * [2026-11-05] - Cập nhật xử lý Translation Group tự động
     */
    public static function get_posts_at_side_cate(string $postType, int $postCount, string $taxonomy, $cate): WP_Query
    {
        [$field, $terms] = self::resolve_category_terms($cate, $taxonomy);

        $args = [
            'post_type'      => $postType,
            'posts_per_page' => $postCount,
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'meta_key'       => '_metabox_order',
            'tax_query'      => [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => $field,
                    'terms'    => $terms,
                ],
            ],
            'meta_query'     => [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => dgw_get_lang(),
                    'compare' => '=',
                ],
            ],
        ];

        return new WP_Query($args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy bài viết sidebar
     */
    public static function get_posts_at_side(string $postType, int $postCount): WP_Query
    {
        $args = [
            'post_type'      => $postType,
            'posts_per_page' => $postCount,
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'meta_key'       => '_metabox_order',
            'meta_query'     => [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => dgw_get_lang(),
                    'compare' => '=',
                ],
            ],
        ];

        return new WP_Query($args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy bài viết có bật hiển thị sidebar (_metabox_sidebar = 1)
     */
    public static function get_posts_show_sidebar(string $postType): WP_Query
    {
        $args = [
            'post_type'  => $postType,
            'meta_query' => [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => dgw_get_lang(),
                    'compare' => '=',
                ],
                [
                    'key'     => '_metabox_sidebar',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
        ];

        return new WP_Query($args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy bài viết loại standard post theo category
     */
    public static function get_post_category(string $cate, int $postCount): WP_Query
    {
        $args = [
            'post_type'      => 'post',
            'category_name'  => $cate,
            'posts_per_page' => $postCount,
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'meta_key'       => '_metabox_order',
            'meta_query'     => [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => dgw_get_lang(),
                    'compare' => '=',
                ],
            ],
        ];

        return new WP_Query($args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Lấy bài viết loại standard post ở trang chủ
     */
    public static function get_post_category_at_home(string $cate, int $postCount): WP_Query
    {
        $args = [
            'post_type'      => 'post',
            'category_name'  => $cate,
            'posts_per_page' => $postCount,
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'meta_key'       => '_metabox_order',
            'meta_query'     => [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => dgw_get_lang(),
                    'compare' => '=',
                ],
                [
                    'key'     => '_metabox_home',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
        ];

        return new WP_Query($args);
    }
}
