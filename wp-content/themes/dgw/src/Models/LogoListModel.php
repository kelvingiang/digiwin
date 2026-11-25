<?php
declare(strict_types=1);

namespace DGW\Models;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Bảng quản trị Logo đối tác (kế thừa BaseListTableModel, tối ưu COUNT và escape XSS)
 * [2026-11-20] - @author: Kelvin - Quy hoạch về chuẩn PSR-4 src/Models/LogoListModel
 */
class LogoListModel extends BaseListTableModel
{
    /**
     * [2026-11-19] - @author: Kelvin - Khởi tạo cấu hình bảng wp_logo
     */
    public function __construct($args = [])
    {
        parent::__construct('logo', is_array($args) ? $args : []);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Khai báo danh sách cột hiển thị
     */
    public function get_columns(): array
    {
        return [
            'cb'      => '<input type="checkbox" />',
            'company' => __('Company Name', 'dgw'),
            'img'     => __('Trademark', 'dgw'),
            'link'    => __('Link', 'dgw'),
            'date'    => __('Create Date', 'dgw'),
        ];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Khai báo các cột hỗ trợ sắp xếp
     */
    public function get_sortable_columns(): array
    {
        return [
            'company' => ['company', true],
            'date'    => ['create_date', true],
        ];
    }

    /**
     * [18/11/2026] - Truy vấn danh sách logo dùng $this->db->prepare(), whitelist ORDER BY và esc_like()
     * [2026-11-19] - @author: Kelvin - Tạo song song _count_sql tối ưu đếm tổng dòng
     */
    protected function table_data(): array
    {
        [$orderby, $order] = $this->resolveOrdering(['ID', 'company', 'create_date', 'update_date'], 'ID');

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

        $baseSql    = "SELECT m.* FROM {$this->table} AS m WHERE {$whereClause} ORDER BY m.{$orderby} {$order}";
        $this->_sql = $this->db->prepare($baseSql, ...$params);

        return $this->db->get_results($this->buildPagedQuery($baseSql, $params), ARRAY_A) ?: [];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render cột tên công ty kèm row actions với Late Escaping
     */
    public function column_company($item): string
    {
        $page    = function_exists('getParams') ? sanitize_key((string) getParams('page')) : '';
        $id      = absint($item['ID'] ?? 0);
        $company = esc_html((string) ($item['company'] ?? ''));

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

        return sprintf('<strong><a href="%s">%s</a></strong>%s', esc_url($editUrl), $company, $this->row_actions($actions));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render cột hình ảnh logo với Late Escaping
     */
    public function column_img($item): string
    {
        $filename = sanitize_file_name((string) ($item['img'] ?? ''));
        if ($filename === '') {
            return '';
        }

        return sprintf(
            '<img src="%s" alt="%s" />',
            esc_url(PART_IMAGES . 'logo/' . $filename),
            esc_attr((string) ($item['company'] ?? 'logo image'))
        );
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render cột liên kết với Late Escaping
     */
    public function column_link($item): string
    {
        return esc_html((string) ($item['link'] ?? ''));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render cột ngày tạo với Late Escaping
     */
    public function column_date($item): string
    {
        return esc_html((string) ($item['create_date'] ?? ''));
    }
}
