<?php
declare(strict_types=1);

namespace DGW\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Các hàm hỗ trợ xử lý Request, Sanitize, Redirect và sắp xếp
 */
class RequestHelper
{
    /**
     * [18/11/2026] - So sánh giảm dần theo khóa 'order'
     */
    public static function cmp($a, $b): int
    {
        return strcmp((string) ($b['order'] ?? ''), (string) ($a['order'] ?? ''));
    }

    /**
     * [18/11/2026] - Làm sạch dữ liệu request đệ quy
     */
    public static function sanitizeValue($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'sanitizeValue'], $value);
        }
        return sanitize_text_field(wp_unslash((string) $value));
    }

    /**
     * [18/11/2026] - Lấy tham số từ $_REQUEST an toàn
     */
    public static function getParams($name = null, $default = '')
    {
        if ($name === null || $name === '') {
            return self::sanitizeValue($_REQUEST);
        }

        $name = sanitize_key((string) $name);
        if (!isset($_REQUEST[$name])) {
            return $default;
        }

        return self::sanitizeValue($_REQUEST[$name]);
    }

    /**
     * [18/11/2026] - Tạo URL chuyển hướng trong trang danh sách post_type của WP-Admin
     */
    public static function customRedirect($location = ''): string
    {
        global $post_type;
        $url = admin_url('edit.php?post_type=' . rawurlencode((string) $post_type));

        if (empty($location)) {
            return $url;
        }

        if (is_array($location)) {
            return add_query_arg($location, $url);
        }

        $location = ltrim((string) $location, '&?');
        if (strpos($location, '=') !== false) {
            return $url . '&' . $location;
        }

        return $url;
    }

    /**
     * [18/11/2026] - Kiểm tra phương thức HTTP hiện tại có phải POST không
     */
    public static function isPost(): bool
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * [18/11/2026] - Chuyển hướng quay lại trang danh sách Admin kèm mã thông báo
     */
    public static function toBack($num): void
    {
        $paged = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
        $page  = isset($_REQUEST['page']) ? sanitize_key((string) $_REQUEST['page']) : '';
        $url   = admin_url('admin.php?page=' . $page . '&paged=' . $paged . '&msg=' . (int) $num);
        wp_redirect($url);
        exit;
    }
}
