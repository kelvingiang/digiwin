<?php
declare(strict_types=1);

namespace DGW\Controllers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Base Controller dùng chung cho toàn bộ Custom Post Types và chuẩn hóa WP-Admin Columns
 */
class BasePostTypeController
{
    private string $postType;
    private array $postTypeConfig;
    private array $unsetColumns;
    private array $customColumns;
    private array $orderQueryKeys;

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo hooks đăng ký CPT và tùy biến bảng quản trị WP-Admin
     */
    public function __construct(string $postType, array $config)
    {
        $this->postType       = $postType;
        $this->postTypeConfig = $config;
        $this->unsetColumns   = $config['unset_columns'] ?? ['date', 'categories', 'comments', 'home', 'language', 'order', 'create-date'];
        $this->customColumns  = $config['custom_columns'] ?? [
            'home'        => 'Home Page',
            'language'    => 'Language',
            'order'       => 'Show Order',
            'create-date' => 'Create Date',
        ];
        $this->orderQueryKeys = $config['order_query_keys'] ?? ['order'];

        add_action('init', [$this, 'registerCustomPost']);

        if (is_admin()) {
            add_filter("manage_edit-{$this->postType}_columns", [$this, 'manageColumns']);
            add_filter("manage_edit-{$this->postType}_sortable_columns", [$this, 'sortableColumns']);
            add_filter('request', [$this, 'sortOrderColumn']);
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký Custom Post Type theo cấu hình chuẩn hóa
     */
    public function registerCustomPost(): void
    {
        $singular   = (string) ($this->postTypeConfig['singular_name'] ?? ucfirst($this->postType));
        $plural     = (string) ($this->postTypeConfig['name'] ?? $singular);
        $menuName   = (string) ($this->postTypeConfig['menu_name'] ?? $singular);

        $labels = [
            'name'               => __($plural, 'dgw'),
            'singular_name'      => __($singular, 'dgw'),
            'add_new'            => __('Add New', 'dgw'),
            'add_new_item'       => __('Add Item', 'dgw'),
            'edit_item'          => __('Edit', 'dgw'),
            'new_item'           => __('Add Item', 'dgw'),
            'all_items'          => __('All Items', 'dgw'),
            'view_item'          => __('View Item', 'dgw'),
            'search_items'       => __('Search', 'dgw'),
            'not_found'          => __('No items found.', 'dgw'),
            'not_found_in_trash' => __('No found in Trash.', 'dgw'),
            'parent_item_colon'  => '',
            'menu_name'          => __($menuName, 'dgw'),
        ];

        // Cho phép nối thêm hậu tố kích thước (vd: Slider 1300 x 430) sau khi dịch
        if (!empty($this->postTypeConfig['name_suffix'])) {
            $labels['name'] .= (string) $this->postTypeConfig['name_suffix'];
        }

        $args = [
            'labels'              => $labels,
            'public'              => true,
            'exclude_from_search' => (bool) ($this->postTypeConfig['exclude_from_search'] ?? true),
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_icon'           => PART_ICON . 'icon-link.png',
            'query_var'           => true,
            'rewrite'             => [
                'slug'       => (string) ($this->postTypeConfig['rewrite_slug'] ?? $this->postType),
                'with_front' => false,
            ],
            'capability_type'     => 'post',
            'has_archive'         => (bool) ($this->postTypeConfig['has_archive'] ?? true),
            'hierarchical'        => false,
            'menu_position'       => (int) ($this->postTypeConfig['menu_position'] ?? 6),
            'supports'            => $this->postTypeConfig['supports'] ?? ['thumbnail', 'editor', 'title'],
        ];

        if (isset($this->postTypeConfig['show_in_rest'])) {
            $args['show_in_rest'] = (bool) $this->postTypeConfig['show_in_rest'];
        }

        register_post_type($this->postType, $args);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Tùy biến các cột hiển thị trong danh sách CPT (Category dùng show_admin_column native)
     * [2026-11-25] - @author: Kelvin - Hỗ trợ chèn cột 'img' ngay sau cột tiêu đề khi CPT cấu hình trong custom_columns
     */
    public function manageColumns(array $columns): array
    {
        foreach ($this->unsetColumns as $colKey) {
            unset($columns[$colKey]);
        }

        if (isset($this->customColumns['img'])) {
            $imgLabel   = __((string) $this->customColumns['img'], 'dgw');
            $newColumns = [];
            $inserted   = false;

            foreach ($columns as $key => $value) {
                $newColumns[$key] = $value;
                if ($key === 'title') {
                    $newColumns['img'] = $imgLabel;
                    $inserted          = true;
                }
            }

            if (!$inserted) {
                $newColumns['img'] = $imgLabel;
            }

            $columns = $newColumns;
        }

        foreach ($this->customColumns as $colKey => $label) {
            if ($colKey === 'img') {
                continue;
            }
            $columns[$colKey] = __((string) $label, 'dgw');
        }

        return $columns;
    }

    /**
     * [2026-11-20] - @author: Kelvin - Khai báo các cột cho phép sắp xếp trong danh sách CPT
     */
    public function sortableColumns(array $columns): array
    {
        $columns['order']       = 'order';
        $columns['create-date'] = 'date';
        return $columns;
    }

    /**
     * [2026-11-20] - @author: Kelvin - Sắp xếp theo _metabox_order dạng số (meta_value_num) đúng CPT hiện tại
     */
    public function sortOrderColumn(array $vars): array
    {
        if (isset($vars['post_type']) && $vars['post_type'] !== $this->postType) {
            return $vars;
        }

        if (isset($vars['orderby']) && in_array($vars['orderby'], $this->orderQueryKeys, true)) {
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
