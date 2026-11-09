<?php
declare(strict_types=1);

// [2026-10-05] - @author: Kelvin - Quản lý CPT Join Digiwin và chuẩn hóa Admin Columns theo chuẩn WordPress OOP & Security

if (!defined('ABSPATH')) {
    exit;
}

class Controller_Join_Us
{
    /**
     * [2026-10-05] - @author: Kelvin - Khởi tạo hooks cho CPT Join Us và bảng quản trị WP-Admin
     */
    public function __construct()
    {
        // 1. Đăng ký CPT Join Us
        add_action('init', [$this, 'register_custom_post']);

        // 2. Tùy biến bảng dữ liệu trong WP-Admin (chỉ chạy trong admin để tối ưu hiệu năng)
        if (is_admin()) {
            add_filter('manage_edit-joinus_columns', [$this, 'manage_columns']);
            add_action('manage_joinus_posts_custom_column', [$this, 'render_columns'], 10, 2);
            add_filter('manage_edit-joinus_sortable_columns', [$this, 'sortable_views_column']);
            add_filter('request', [$this, 'sort_views_column']);
        }
    }

    /**
     * [2026-10-05] - @author: Kelvin - Đăng ký Custom Post Type Join Digiwin
     */
    public function register_custom_post(): void
    {
        $labels = [
            'name'               => __('Join Digiwin', 'dgw'),
            'singular_name'      => __('Join Digiwin', 'dgw'),
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
            'menu_name'          => __('Join Digiwin', 'dgw'),
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
            'rewrite'             => ['slug' => 'joinus', 'with_front' => false],
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 4,
            'supports'            => ['title', 'thumbnail', 'editor'],
        ];

        register_post_type('joinus', $args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Quản lý các cột hiển thị trong danh sách bài viết Join Us
     * [09/11/2026] - Loại bỏ cột category thủ công, sử dụng chuẩn native 'show_admin_column' => true trong taxonomy-joinus.php
     */
    public function manage_columns(array $columns): array
    {
        unset(
            $columns['date'],
            $columns['categories'],
            $columns['comments'],
            // $columns['author'],
            $columns['home'],
            $columns['language'],
            $columns['order'],
            $columns['create-date']
        );

        // [09/11/2026] - Cột Category do WordPress Native ('show_admin_column' => true trong taxonomy-joinus.php) tự động quản lý
        $columns['language']    = __('Language', 'dgw');
        $columns['order']       = __('Show Order', 'dgw');
        $columns['create-date'] = __('Create Date', 'dgw');

        return $columns;
    }

    /**
     * [09/11/2026] - Loại bỏ render cột category thủ công (đã dùng show_admin_column trong taxonomy-joinus.php)
     * Các cột chung (language, order, create-date) đã được Custom_post_RenderCols xử lý toàn cục
     */
    public function render_columns(string $column, int $post_id): void
    {
        // Cột Category do WordPress Native xử lý (taxonomy-joinus.php: show_admin_column => true)
        // Các cột chung (language, order, create-date) do Custom_post_RenderCols xử lý toàn cục
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
        if (isset($vars['orderby']) && $vars['orderby'] === 'order') {
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
