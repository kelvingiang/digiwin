<?php
declare(strict_types=1);

// [2026-10-05] - @author: Kelvin - Quản lý CPT Slider và chuẩn hóa Admin Columns theo chuẩn WordPress OOP & Security

if (!defined('ABSPATH')) {
    exit;
}

class Controller_Slider
{
    /**
     * [2026-10-05] - @author: Kelvin - Khởi tạo hooks cho CPT Slider và bảng quản trị WP-Admin
     */
    public function __construct()
    {
        // 1. Đăng ký CPT Slider
        add_action('init', [$this, 'register_custom_post']);

        // 2. Tùy biến bảng dữ liệu trong WP-Admin (chỉ chạy trong admin để tối ưu hiệu năng)
        if (is_admin()) {
            add_filter('manage_edit-slider_columns', [$this, 'manage_columns']);
            add_action('manage_slider_posts_custom_column', [$this, 'render_columns'], 10, 2);
            add_filter('manage_edit-slider_sortable_columns', [$this, 'sortable_views_column']);
            add_filter('request', [$this, 'sort_views_column']);
        }
    }

    /**
     * [2026-10-05] - @author: Kelvin - Đăng ký Custom Post Type Slider (1300 x 430)
     */
    public function register_custom_post(): void
    {
        $labels = [
            'name'               => __('Slider', 'dgw') . ' 1300 x 430',
            'singular_name'      => __('Slider', 'dgw'),
            'add_new'            => __('Add New', 'dgw'),
            'add_new_item'       => __('Add Item', 'dgw'),
            'edit_item'          => __('Edit', 'dgw'),
            'new_item'           => __('Add Item', 'dgw'),
            'all_items'          => __('All Items', 'dgw'),
            'view_item'          => __('View Item', 'dgw'),
            'search_items'       => __('Search', 'dgw'),
            'not_found'          => __('No slides found.', 'dgw'),
            'not_found_in_trash' => __('No found in Trash.', 'dgw'),
            'parent_item_colon'  => '',
            'menu_name'          => __('Slider', 'dgw'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_icon'           => PART_ICON . 'icon-link.png',
            'query_var'           => true,
            'rewrite'             => ['slug' => 'slider', 'with_front' => false],
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 6,
            'supports'            => ['title', 'thumbnail', 'editor'],
        ];

        register_post_type('slider', $args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Quản lý các cột hiển thị trong danh sách bài viết Slider
     */
    public function manage_columns(array $columns): array
    {
        unset(
            $columns['home'],
            $columns['categories'],
            $columns['comments']
        );

        $columns['order']       = __('Show Order', 'dgw');
        $columns['create-date'] = __('Create Date', 'dgw');

        return $columns;
    }

    /**
     * [2026-10-05] - @author: Kelvin - Hiển thị nội dung cột cho Slider
     */
    public function render_columns(string $column, int $post_id): void
    {
        // Các cột chung (order, create-date) đã được Custom_post_RenderCols xử lý toàn cục
    }

    /**
     * [2026-10-05] - @author: Kelvin - Thiết lập các cột cho phép sắp xếp
     */
    public function sortable_views_column(array $columns): array
    {
        $columns['order']       = 'order';
        $columns['create-date'] = 'date';
        return $columns;
    }

    /**
     * [2026-10-05] - @author: Kelvin - Tối ưu query sắp xếp thứ tự theo số (meta_value_num)
     */
    public function sort_views_column(array $vars): array
    {
        if (isset($vars['orderby']) && 'order' === $vars['orderby']) {
            $vars = array_merge(
                $vars,
                [
                    'meta_key' => '_metabox_order',
                    'orderby'  => 'meta_value_num',
                ]
            );
        }
        return $vars;
    }
}
