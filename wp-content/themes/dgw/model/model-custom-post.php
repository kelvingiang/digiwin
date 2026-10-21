<?php
declare(strict_types=1);

// [2026-10-05] - @author: Kelvin - Model: Xử lý truy vấn dữ liệu Custom Post Types theo chuẩn MVC

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
     * [2026-10-05] - @author: Kelvin - Lấy danh sách bài viết theo Category/Taxonomy
     */
    public static function get_posts_by_cate(string $postType, $cate, int $postCount, string $taxonomy): WP_Query
    {
        $args = [
            'post_type'      => $postType,
            'posts_per_page' => $postCount,
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'meta_key'       => '_metabox_order',
            'tax_query'      => [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => is_numeric($cate) ? 'term_id' : 'slug',
                    'terms'    => $cate,
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
     */
    public static function get_posts_at_side_cate(string $postType, int $postCount, string $taxonomy, $cate): WP_Query
    {
        $args = [
            'post_type'      => $postType,
            'posts_per_page' => $postCount,
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'meta_key'       => '_metabox_order',
            'tax_query'      => [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => is_numeric($cate) ? 'term_id' : 'slug',
                    'terms'    => $cate,
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
