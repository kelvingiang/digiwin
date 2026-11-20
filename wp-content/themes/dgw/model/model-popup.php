<?php
declare(strict_types=1);

use DGW\Models\BaseListTableModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Bảng quản trị Pop-up (kế thừa BaseListTableModel, tối ưu COUNT và escape XSS)
 */
class Model_Pop_up extends BaseListTableModel
{
    /**
     * [2026-11-19] - @author: Kelvin - Khởi tạo cấu hình bảng wp_pop_up
     */
    public function __construct($args = [])
    {
        parent::__construct('pop_up', is_array($args) ? $args : []);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Khai báo danh sách cột hiển thị
     */
    public function get_columns(): array
    {
        return [
            'cb'     => '<input type="checkbox" />',
            'title'  => __('Title', 'dgw'),
            'status' => __('Status', 'dgw'),
            'img'    => __('Image', 'dgw'),
            'link'   => __('Link', 'dgw'),
            'date'   => __('Create Date', 'dgw'),
        ];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Khai báo các cột hỗ trợ sắp xếp
     */
    public function get_sortable_columns(): array
    {
        return [
            'status' => ['status', true],
            'date'   => ['create_date', true],
        ];
    }

    /**
     * [18/11/2026] - Truy vấn danh sách pop-up dùng $this->db->prepare(), whitelist ORDER BY và esc_like()
     * [2026-11-19] - @author: Kelvin - Tạo song song _count_sql tối ưu đếm tổng dòng
     */
    protected function table_data(): array
    {
        [$orderby, $order] = $this->resolveOrdering(['ID', 'title', 'status', 'create_date'], 'ID');

        $trash  = $this->isTrashView() ? 1 : 0;
        $where  = ['m.trash = %d'];
        $params = [$trash];
        $search = function_exists('getParams') ? trim((string) getParams('s')) : '';

        if ($search !== '') {
            $where[]  = 'm.title LIKE %s';
            $params[] = '%' . $this->db->esc_like($search) . '%';
        }

        $whereClause      = implode(' AND ', $where);
        $this->_count_sql = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} AS m WHERE {$whereClause}", ...$params);

        $baseSql    = "SELECT m.* FROM {$this->table} AS m WHERE {$whereClause} ORDER BY m.{$orderby} {$order}";
        $this->_sql = $this->db->prepare($baseSql, ...$params);

        return $this->db->get_results($this->buildPagedQuery($baseSql, $params), ARRAY_A) ?: [];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render cột tiêu đề kèm row actions với Late Escaping
     */
    public function column_title($item): string
    {
        $page  = function_exists('getParams') ? sanitize_key((string) getParams('page')) : '';
        $id    = absint($item['ID'] ?? 0);
        $title = esc_html((string) ($item['title'] ?? ''));

        if ($this->isTrashView()) {
            $actions = [
                'restore' => $this->buildActionLink($page, 'restore', $id, __('Restore', 'dgw')),
                'delete'  => $this->buildActionLink($page, 'delete', $id, __('Delete Permanently', 'dgw')),
            ];
        } else {
            $status  = (string) ($item['status'] ?? '');
            $actions = [];

            if ($status === '0') {
                $actions['active'] = $this->buildActionLink($page, 'active', $id, '啟用');
            } elseif ($status === '1') {
                $actions['passive'] = $this->buildActionLink($page, 'passive', $id, '停止');
            }

            $actions['edit']  = $this->buildActionLink($page, 'edit', $id, __('Edit', 'dgw'));
            $actions['trash'] = $this->buildActionLink($page, 'trash', $id, __('Trash', 'dgw'));
        }

        $editUrl = add_query_arg(['page' => $page, 'action' => 'edit', 'id' => $id], admin_url('admin.php'));

        return sprintf('<strong><a href="%s">%s</a></strong>%s', esc_url($editUrl), $title, $this->row_actions($actions));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render cột ảnh pop-up với Late Escaping
     * [2026-11-20] - @author: Kelvin - Fallback sang img_vn nếu bản ghi chưa có img_cn
     */
    public function column_img($item): string
    {
        $rawImg   = !empty($item['img_cn']) ? (string) $item['img_cn'] : (string) ($item['img_vn'] ?? '');
        $filename = sanitize_file_name($rawImg);
        if ($filename === '') {
            return '';
        }

        return sprintf(
            '<div><img src="%s" alt="%s" /></div>',
            esc_url(PART_IMAGES . 'pop-up/' . $filename),
            esc_attr((string) ($item['title'] ?? 'pop-up image'))
        );
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render cột link VN/CN với Late Escaping
     */
    public function column_link($item): string
    {
        return esc_html((string) ($item['link_vn'] ?? '')) . '<br>' . esc_html((string) ($item['link_cn'] ?? ''));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render cột trạng thái kích hoạt
     */
    public function column_status($item): string
    {
        return ((string) ($item['status'] ?? '') === '1') ? '<div class="show-home"></div>' : '';
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render cột ngày tạo với Late Escaping
     */
    public function column_date($item): string
    {
        return esc_html((string) ($item['create_date'] ?? ''));
    }
}
