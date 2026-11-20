<?php
declare(strict_types=1);

use DGW\Models\BaseListTableModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Bảng quản trị Thành viên & Thống kê Download/View (kế thừa BaseListTableModel, tối ưu COUNT và vá Stored XSS)
 */
class Model_Download extends BaseListTableModel
{
    private string $download_table = '';

    /**
     * [2026-11-19] - @author: Kelvin - Khởi tạo cấu hình bảng thành viên và bảng chi tiết download
     */
    public function __construct($args = [])
    {
        if (!function_exists('convert_to_screen')) {
            require_once ABSPATH . 'wp-admin/includes/template.php';
            require_once ABSPATH . 'wp-admin/includes/screen.php';
        }

        parent::__construct('download_registrations', is_array($args) ? $args : []);
        $this->download_table = $this->db->prefix . 'download_detail';
    }

    /**
     * [2026-11-19] - @author: Kelvin - Khai báo danh sách cột hiển thị
     */
    public function get_columns(): array
    {
        return [
            'cb'                    => '<input type="checkbox" />',
            'member_email'          => __('E-mail', 'dgw'),
            'member_name'           => __('Full Name', 'dgw'),
            'member_company'        => __('Company Name', 'dgw'),
            'member_phone'          => __('Contact Number', 'dgw'),
            'member_download_count' => __('Download Count', 'dgw'),
            'member_view_count'     => __('View Count', 'dgw'),
            'create_date'           => __('Create Date', 'dgw'),
        ];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Danh sách cột sắp xếp (giữ cấu hình hiện hành)
     */
    public function get_sortable_columns(): array
    {
        return [];
    }

    /**
     * [18/11/2026] - Helper dựng truy vấn danh sách thành viên dùng chung cho bảng và export, bọc $this->db->prepare() và whitelist ORDER BY
     * [2026-11-19] - @author: Kelvin - Tạo song song _count_sql không cần JOIN download_detail để tối ưu tốc độ đếm phân trang
     */
    private function build_list_query(bool $withPagination = false): string
    {
        [$orderby, $order] = $this->resolveOrdering(['ID', 'email', 'username', 'company', 'phone', 'create_date'], 'ID');

        $trash  = $this->isTrashView() ? 1 : 0;
        $where  = ['m.trash = %d'];
        $params = [$trash];
        $search = function_exists('getParams') ? trim((string) getParams('s')) : '';

        if ($search !== '') {
            $where[]  = 'm.company LIKE %s';
            $params[] = '%' . $this->db->esc_like($search) . '%';
        }

        $whereClause      = implode(' AND ', $where);
        $this->_count_sql = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} AS m WHERE {$whereClause}", ...$params);

        $baseSql = "SELECT m.*,
                COUNT(CASE WHEN d.kid = 1 THEN 1 END) AS download_count,
                COUNT(CASE WHEN d.kid = 2 THEN 1 END) AS view_count
            FROM {$this->table} AS m
            LEFT JOIN {$this->download_table} AS d ON m.ID = d.user_id
            WHERE {$whereClause}
            GROUP BY m.ID
            ORDER BY m.{$orderby} {$order}";

        $this->_sql = $this->db->prepare($baseSql, ...$params);

        if (!$withPagination) {
            return $this->_sql;
        }

        return $this->buildPagedQuery($baseSql, $params);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy dữ liệu thành viên phân trang cho bảng quản trị
     */
    protected function table_data(): array
    {
        return $this->db->get_results($this->build_list_query(true), ARRAY_A) ?: [];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy toàn bộ dữ liệu thành viên phục vụ xuất Excel
     */
    public function get_export_data(): array
    {
        return $this->db->get_results($this->build_list_query(false), ARRAY_A) ?: [];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render cột email kèm row actions với Late Escaping
     */
    public function column_member_email($item): string
    {
        $page  = function_exists('getParams') ? sanitize_key((string) getParams('page')) : '';
        $id    = absint($item['ID'] ?? 0);
        $email = esc_html((string) ($item['email'] ?? ''));

        if ($this->isTrashView()) {
            $actions = [
                'restore' => $this->buildActionLink($page, 'restore', $id, __('Restore', 'dgw')),
                'delete'  => $this->buildActionLink($page, 'delete', $id, __('Delete Permanently', 'dgw')),
            ];
        } else {
            $actions = [
                'edit'  => $this->buildActionLink($page, 'edit', $id, __('Edit', 'dgw')),
                'trash' => $this->buildActionLink($page, 'trash', $id, __('Trash', 'dgw')),
            ];
        }

        $editUrl = add_query_arg(['page' => $page, 'action' => 'edit', 'id' => $id], admin_url('admin.php'));

        return sprintf('<strong><a href="%s">%s</a></strong>%s', esc_url($editUrl), $email, $this->row_actions($actions));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render tên thành viên và trạng thái kích hoạt
     */
    public function column_member_name($item): string
    {
        $username = esc_html((string) ($item['username'] ?? ''));
        $status   = ((int) ($item['status'] ?? 0) === 1) ? ' * ' : ' ';

        return $username . $status;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render tên công ty với Late Escaping chống Stored XSS
     */
    public function column_member_company($item): string
    {
        return esc_html((string) ($item['company'] ?? ''));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render số điện thoại với Late Escaping
     */
    public function column_member_phone($item): string
    {
        return esc_html((string) ($item['phone'] ?? ''));
    }

    /**
     * [03/11/2026] - Hiển thị tổng số lượt tải file (kid = 1)
     */
    public function column_member_download_count($item): int
    {
        return (int) ($item['download_count'] ?? 0);
    }

    /**
     * [03/11/2026] - Hiển thị tổng số lượt xem video YouTube (kid = 2)
     */
    public function column_member_view_count($item): int
    {
        return (int) ($item['view_count'] ?? 0);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render ngày tạo tài khoản với Late Escaping
     */
    public function column_create_date($item): string
    {
        return esc_html((string) ($item['create_date'] ?? ''));
    }
}
