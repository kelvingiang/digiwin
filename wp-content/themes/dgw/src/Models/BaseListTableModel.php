<?php
declare(strict_types=1);

namespace DGW\Models;

use WP_List_Table;
use wpdb;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * [2026-11-19] - @author: Kelvin - Base Model dùng chung cho các bảng quản trị WP_List_Table (tránh lặp code, tối ưu COUNT và Late Escaping)
 */
abstract class BaseListTableModel extends WP_List_Table
{
    protected wpdb $db;
    protected string $table;
    protected int $_pre_page = 30;
    protected string $_sql = '';
    protected string $_count_sql = '';

    /**
     * [2026-11-19] - @author: Kelvin - Khởi tạo kết nối $wpdb một lần duy nhất và cấu hình bảng
     */
    public function __construct(string $tableSuffix, array $listArgs = [], ?wpdb $db = null)
    {
        if (null === $db) {
            global $wpdb;
            $db = $wpdb;
        }

        $this->db    = $db;
        $this->table = $this->db->prefix . $tableSuffix;

        $defaults = [
            'plural'   => 'ID',
            'singular' => 'ID',
            'ajax'     => false,
            'screen'   => null,
        ];

        parent::__construct(array_merge($defaults, $listArgs));
    }

    /**
     * [2026-11-20] - @author: Kelvin - Truy vấn dữ liệu phân trang của bảng con
     */
    abstract protected function table_data(): array;

    /**
     * [2026-11-20] - @author: Kelvin - Nạp dữ liệu và thiết lập phân trang dùng chung cho mọi WP_List_Table
     */
    public function prepare_items(): void
    {
        $columns               = $this->get_columns();
        $hidden                = $this->get_hidden_columns();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        $this->items = $this->table_data();
        $totalItems  = $this->total_items();

        $this->set_pagination_args([
            'total_items' => $totalItems,
            'per_page'    => $this->_pre_page,
            'total_pages' => (int) ceil($totalItems / max(1, $this->_pre_page)),
        ]);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Chuẩn hóa danh sách cột ẩn mặc định
     */
    public function get_hidden_columns(): array
    {
        return [];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Đếm tổng số dòng phục vụ phân trang bằng SELECT COUNT(*) thay vì chạy lại toàn bộ query dữ liệu
     */
    public function total_items(): int
    {
        if ($this->_count_sql !== '') {
            return (int) $this->db->get_var($this->_count_sql);
        }

        if ($this->_sql !== '') {
            return (int) $this->db->query($this->_sql);
        }

        return 0;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Tổng số bản ghi của bảng
     */
    public function total_list(): int
    {
        return (int) $this->db->get_var("SELECT COUNT(*) FROM {$this->table}");
    }

    /**
     * [2026-11-19] - @author: Kelvin - Tổng số bản ghi trong thùng rác
     */
    public function total_trash(): int
    {
        return (int) $this->db->get_var($this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE trash = %d", 1));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Tổng số bản ghi đang hoạt động
     */
    public function total_publish(): int
    {
        return (int) $this->db->get_var($this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE trash = %d", 0));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Hiển thị bộ lọc trạng thái (All / Published / Trash) với active class chính xác và escape đầy đủ
     */
    public function get_views(): array
    {
        $current = function_exists('getParams') ? (string) getParams('customvar', 'all') : 'all';
        if ($current === '') {
            $current = 'all';
        }

        $allUrl       = esc_url(remove_query_arg('customvar'));
        $publishedUrl = esc_url(add_query_arg('customvar', 'published'));
        $trashUrl     = esc_url(add_query_arg('customvar', 'trash'));

        return [
            'all' => sprintf(
                '<a href="%s"%s>%s (%d)</a>',
                $allUrl,
                $current === 'all' ? ' class="current"' : '',
                esc_html__('All', 'dgw'),
                $this->total_list()
            ),
            'published' => sprintf(
                '<a href="%s"%s>%s (%d)</a>',
                $publishedUrl,
                $current === 'published' ? ' class="current"' : '',
                esc_html__('Published', 'dgw'),
                $this->total_publish()
            ),
            'trash' => sprintf(
                '<a href="%s"%s>%s (%d)</a>',
                $trashUrl,
                $current === 'trash' ? ' class="current"' : '',
                esc_html__('Trash', 'dgw'),
                $this->total_trash()
            ),
        ];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Bulk actions chuẩn theo trạng thái thùng rác
     */
    public function get_bulk_actions(): array
    {
        $customvar = function_exists('getParams') ? (string) getParams('customvar') : '';

        if ($customvar === 'trash') {
            return [
                'restore' => __('Restore', 'dgw'),
                'delete'  => __('Delete Permanently', 'dgw'),
            ];
        }

        return [
            'trash' => __('Trash', 'dgw'),
        ];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render checkbox hàng với Late Escaping
     */
    public function column_cb($item): string
    {
        $singular = (string) ($this->_args['singular'] ?? 'ID');
        $id       = absint($item['ID'] ?? 0);

        return sprintf(
            '<input type="checkbox" name="%s[]" value="%d" />',
            esc_attr($singular),
            $id
        );
    }

    /**
     * [2026-11-19] - @author: Kelvin - Whitelist tham số sắp xếp ORDER BY và hướng sắp xếp
     *
     * @return array{0: string, 1: string}
     */
    protected function resolveOrdering(array $allowedOrderby, string $defaultOrderby = 'ID'): array
    {
        $rawOrderby = !empty($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : $defaultOrderby;
        $orderby    = in_array($rawOrderby, $allowedOrderby, true) ? $rawOrderby : $defaultOrderby;

        $rawOrder = !empty($_GET['order']) ? strtoupper(sanitize_key(wp_unslash($_GET['order']))) : 'DESC';
        $order    = ($rawOrder === 'ASC') ? 'ASC' : 'DESC';

        return [$orderby, $order];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Bọc LIMIT/OFFSET an toàn cho truy vấn phân trang
     */
    protected function buildPagedQuery(string $baseSql, array $params): string
    {
        $paged  = max(1, absint($_REQUEST['paged'] ?? 1));
        $offset = ($paged - 1) * $this->_pre_page;

        return $this->db->prepare(
            "{$baseSql} LIMIT %d OFFSET %d",
            ...array_merge($params, [$this->_pre_page, $offset])
        );
    }

    /**
     * [2026-11-19] - @author: Kelvin - Tạo thẻ <a> thao tác dòng an toàn (không khoảng trắng thừa trong href, escape chuẩn)
     * [2026-11-20] - @author: Kelvin - Bổ sung Nonce cho các thao tác thay đổi dữ liệu chống CSRF
     */
    protected function buildActionLink(string $page, string $action, int $id, string $label): string
    {
        $url = add_query_arg(
            [
                'page'   => $page,
                'action' => $action,
                'id'     => $id,
            ],
            admin_url('admin.php')
        );

        if (in_array($action, ['trash', 'restore', 'delete', 'active', 'passive'], true)) {
            $url = wp_nonce_url($url, "dgw_{$page}_{$action}_{$id}");
        }

        return sprintf('<a href="%s">%s</a>', esc_url($url), esc_html($label));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Kiểm tra hiện tại đang ở tab thùng rác hay không
     */
    protected function isTrashView(): bool
    {
        return function_exists('getParams') && getParams('customvar') === 'trash';
    }
}
