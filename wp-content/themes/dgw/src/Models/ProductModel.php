<?php
declare(strict_types=1);

namespace DGW\Models;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Model truy vấn bảng sản phẩm custom (tách từ inc/front/front-core.php)
 */
class ProductModel
{
    /**
     * [2026-11-19] - @author: Kelvin - Lấy thông tin chi tiết sản phẩm theo ID bằng prepared statement
     */
    public static function getById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'product';
        $sql   = $wpdb->prepare("SELECT * FROM {$table} WHERE ID = %d", $id);
        $row   = $wpdb->get_row($sql, ARRAY_A);

        return is_array($row) ? $row : null;
    }
}
