<?php
declare(strict_types=1);

// [2026-10-05] - @author: Kelvin - Quản lý CPT Solutions và chuẩn hóa Admin Columns theo chuẩn WordPress OOP & Security

if (!defined('ABSPATH')) {
    exit;
}

class Controller_Solutions
{
    /**
     * [2026-10-05] - @author: Kelvin - Khởi tạo hooks cho CPT Solutions và bảng quản trị WP-Admin
     */
    public function __construct()
    {
        // 1. Đăng ký CPT Solutions
        add_action('init', [$this, 'register_custom_post']);

        // 2. Tùy biến bảng dữ liệu trong WP-Admin (chỉ chạy trong admin để tối ưu hiệu năng)
        if (is_admin()) {
            add_filter('manage_edit-solutions_columns', [$this, 'manage_columns']);
            add_action('manage_solutions_posts_custom_column', [$this, 'render_columns'], 10, 2);
            add_filter('manage_edit-solutions_sortable_columns', [$this, 'sortable_views_column']);
            add_filter('request', [$this, 'sort_views_column']);
        }
    }

    /**
     * [2026-10-05] - @author: Kelvin - Đăng ký Custom Post Type Solutions
     */
    public function register_custom_post(): void
    {
        $labels = [
            'name'               => __('Solutions', 'dgw'),
            'singular_name'      => __('Solutions', 'dgw'),
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
            'menu_name'          => __('Solutions', 'dgw'),
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
            'rewrite'             => ['slug' => 'solutions', 'with_front' => false],
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 4,
            'supports'            => ['title', 'thumbnail', 'editor', 'comments'],
        ];

        register_post_type('solutions', $args);
    }

    /**
     * [2026-10-05] - @author: Kelvin - Quản lý các cột hiển thị trong danh sách bài viết Solutions
     * [09/11/2026] - Loại bỏ cột category thủ công, sử dụng chuẩn native 'show_admin_column' => true trong taxonomy-solution.php
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

        // [09/11/2026] - Cột Category do WordPress Native ('show_admin_column' => true trong taxonomy-solution.php) tự động quản lý
        $columns['home']        = __('Home Page', 'dgw');
        $columns['language']    = __('Language', 'dgw');
        $columns['order']       = __('Show Order', 'dgw');
        $columns['create-date'] = __('Create Date', 'dgw');

        return $columns;
    }

    /**
     * [09/11/2026] - Loại bỏ render cột category thủ công (đã dùng show_admin_column trong taxonomy-solution.php)
     * Các cột chung (home, language, order, create-date) đã được Custom_post_RenderCols xử lý toàn cục
     */
    public function render_columns(string $column, int $post_id): void
    {
        // Cột Category do WordPress Native xử lý (taxonomy-solution.php: show_admin_column => true)
        // Các cột chung (home, language, order, create-date) do Custom_post_RenderCols xử lý toàn cục
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
