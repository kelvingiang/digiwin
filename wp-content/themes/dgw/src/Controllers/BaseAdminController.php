<?php
declare(strict_types=1);

namespace DGW\Controllers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Base Controller cho các trang quản trị WP-Admin (chuẩn hóa phân quyền, Nonce, URL và hook load-page)
 */
abstract class BaseAdminController
{
    protected string $capability = 'manage_categories';
    protected string $hookSuffix = '';

    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký hook admin_menu khi khởi tạo controller
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký menu/submenu và gắn hook xử lý trước khi render HTML (load-{hookSuffix})
     */
    final public function registerMenu(): void
    {
        $hook = $this->addMenuPage();
        if (is_string($hook) && $hook !== '') {
            $this->hookSuffix = $hook;
            add_action("load-{$hook}", [$this, 'handleEarlyRequest']);
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Khai báo menu hoặc submenu WP-Admin, trả về hook_suffix
     */
    abstract protected function addMenuPage();

    /**
     * [2026-11-20] - @author: Kelvin - Xử lý redirect, xuất file, lưu form và hành động thay đổi dữ liệu trước khi xuất HTML header
     */
    public function handleEarlyRequest(): void
    {
        if (!current_user_can($this->capability)) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'dgw'), 403);
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render giao diện mặc định của trang admin
     */
    abstract public function renderPage(): void;

    /**
     * [2026-11-20] - @author: Kelvin - Dựng URL lọc danh sách an toàn bằng add_query_arg()
     */
    protected function buildFilterUrl(): string
    {
        $args = [
            'page' => sanitize_key((string) getParams('page')),
        ];

        $filterCategory = (string) getParams('filter_category', '0');
        if ($filterCategory !== '' && $filterCategory !== '0') {
            $args['filter_category'] = $filterCategory;
        }

        $search = trim((string) getParams('s'));
        if ($search !== '') {
            $args['s'] = $search;
        }

        return add_query_arg($args, admin_url('admin.php'));
    }

    /**
     * [2026-11-20] - @author: Kelvin - Chuyển hướng nếu người dùng nhấn Apply bộ lọc WP_List_Table (action = -1)
     */
    protected function redirectIfFilterSubmitted(): void
    {
        if ((string) getParams('action') === '-1') {
            wp_safe_redirect($this->buildFilterUrl());
            exit;
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lấy action hợp lệ từ WP_List_Table (hỗ trợ cả bulk action phía trên và phía dưới bảng)
     */
    protected function resolveListAction(): string
    {
        $action  = (string) getParams('action');
        $action2 = (string) getParams('action2');

        if (($action === '' || $action === '-1') && $action2 !== '' && $action2 !== '-1') {
            return $action2;
        }

        return $action;
    }

    /**
     * [2026-11-20] - @author: Kelvin - Kiểm tra Nonce cho thao tác đơn lẻ (GET) hoặc hàng loạt (POST bulk-ID) trên WP_List_Table
     */
    protected function verifyListActionNonce(string $page, string $action): void
    {
        $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash((string) $_REQUEST['_wpnonce'])) : '';

        // Bulk action từ WP_List_Table sử dụng nonce 'bulk-ID'
        if (!empty($_POST['id']) && is_array($_POST['id'])) {
            if (!wp_verify_nonce($nonce, 'bulk-ID')) {
                wp_die(esc_html__('Security check failed.', 'dgw'), 403);
            }
            return;
        }

        $id = absint(getParams('id'));
        if (!wp_verify_nonce($nonce, "dgw_{$page}_{$action}_{$id}")) {
            wp_die(esc_html__('Security check failed.', 'dgw'), 403);
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Kiểm tra Nonce cho thao tác xuất file Excel
     */
    protected function verifyNonceAction(string $nonceAction, string $nonceField = '_wpnonce'): void
    {
        $nonce = isset($_REQUEST[$nonceField]) ? sanitize_text_field(wp_unslash((string) $_REQUEST[$nonceField])) : '';
        if (!wp_verify_nonce($nonce, $nonceAction)) {
            wp_die(esc_html__('Security check failed.', 'dgw'), 403);
        }
    }
}
