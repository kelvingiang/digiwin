<?php
declare(strict_types=1);

namespace DGW\Models;

use wpdb;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Base Model dùng chung cho các Model thao tác bảng custom (lưu $wpdb và xử lý batch an toàn)
 */
abstract class BaseCustomTableModel
{
    protected wpdb $db;

    /**
     * [2026-11-19] - @author: Kelvin - Lưu tham chiếu $wpdb một lần duy nhất trong constructor
     */
    public function __construct(?wpdb $db = null)
    {
        if (null === $db) {
            global $wpdb;
            $db = $wpdb;
        }

        $this->db = $db;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Chuẩn hóa danh sách ID dương từ input đơn lẻ hoặc mảng
     *
     * @return int[]
     */
    protected function normalizeIds($rawId): array
    {
        if ($rawId === null || $rawId === '' || $rawId === []) {
            return [];
        }

        $list = is_array($rawId) ? $rawId : [$rawId];
        return array_values(array_filter(array_map('absint', $list)));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Cập nhật hàng loạt cột trash (và tùy chọn cột khác) theo danh sách ID bằng prepared statement
     */
    protected function bulkUpdateByIds(string $table, array $columns, array $formats, $rawId): bool
    {
        $ids = $this->normalizeIds($rawId);
        if (empty($ids) || empty($columns)) {
            return false;
        }

        if (count($ids) === 1) {
            $result = $this->db->update($table, $columns, ['ID' => $ids[0]], $formats, ['%d']);
            return $result !== false;
        }

        $setParts = [];
        $values   = [];
        $idx      = 0;
        foreach ($columns as $col => $val) {
            $fmt        = $formats[$idx] ?? '%s';
            $setParts[] = "`{$col}` = {$fmt}";
            $values[]   = $val;
            $idx++;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $sql          = $this->db->prepare(
            "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE ID IN ({$placeholders})",
            ...array_merge($values, $ids)
        );

        return $this->db->query($sql) !== false;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Xóa vĩnh viễn hàng loạt bản ghi theo danh sách ID bằng prepared statement
     */
    protected function bulkDeleteByIds(string $table, $rawId): bool
    {
        $ids = $this->normalizeIds($rawId);
        if (empty($ids)) {
            return false;
        }

        if (count($ids) === 1) {
            return $this->db->delete($table, ['ID' => $ids[0]], ['%d']) !== false;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $sql          = $this->db->prepare(
            "DELETE FROM {$table} WHERE ID IN ({$placeholders})",
            ...$ids
        );

        return $this->db->query($sql) !== false;
    }
}
