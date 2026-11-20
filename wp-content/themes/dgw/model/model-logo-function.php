<?php
declare(strict_types=1);

use DGW\Models\BaseCustomTableModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Model thao tác bảng logo đối tác (kế thừa BaseCustomTableModel, loại bỏ global $wpdb và debug die())
 * [2026-11-20] - @author: Kelvin - Bổ sung xóa file ảnh logo cũ trên ổ đĩa khi cập nhật hoặc xóa vĩnh viễn bản ghi
 */
class Model_Logo_Function extends BaseCustomTableModel
{
    private string $table;

    /**
     * [2026-11-19] - @author: Kelvin - Khởi tạo kết nối DB và tên bảng logo
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'logo';
    }

    /**
     * [18/11/2026] - Lấy danh sách logo dùng prepared statement, sửa lỗi cú pháp khi $status rỗng
     */
    public function getAll($status = '')
    {
        if ($status !== '' && $status !== null) {
            $sql = $this->db->prepare("SELECT * FROM {$this->table} WHERE trash = %d", (int) $status);
        } else {
            $sql = "SELECT * FROM {$this->table}";
        }

        return $this->db->get_results($sql, ARRAY_A);
    }

    /**
     * [18/11/2026] - Lấy chi tiết logo theo ID dùng $this->db->prepare()
     */
    public function getItem($id)
    {
        $logoId = absint($id);
        if ($logoId <= 0) {
            return null;
        }

        $sql = $this->db->prepare("SELECT * FROM {$this->table} WHERE ID = %d", $logoId);
        return $this->db->get_row($sql, ARRAY_A);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lưu mới hoặc cập nhật logo với sanitize đầu vào, dọn ảnh cũ và xóa cache trang chủ
     */
    public function Save($arrData, $option)
    {
        global $current_user;
        $userLogin = isset($current_user->user_login) ? (string) $current_user->user_login : '';
        $today     = wp_date('Y-m-d');

        $data = [
            'company' => sanitize_text_field((string) ($arrData['txt-company'] ?? '')),
            'img'     => sanitize_file_name((string) ($arrData['txt-img'] ?? '')),
            'link'    => esc_url_raw((string) ($arrData['txt-link'] ?? '')),
        ];

        if ($option === 'add') {
            $addData = array_merge($data, [
                'create_date' => $today,
                'create_by'   => $userLogin,
            ]);
            $this->db->insert($this->table, $addData, ['%s', '%s', '%s', '%s', '%s']);
        } elseif ($option === 'edit') {
            $editId = absint($arrData['hid-id'] ?? 0);
            if ($editId > 0) {
                $existing = $this->getItem($editId);
                if (is_array($existing) && !empty($existing['img']) && !empty($data['img']) && $existing['img'] !== $data['img']) {
                    $this->removeImgByName((string) $existing['img']);
                }

                $updateData = array_merge($data, [
                    'update_date' => $today,
                    'update_by'   => $userLogin,
                ]);
                $this->db->update($this->table, $updateData, ['ID' => $editId], ['%s', '%s', '%s', '%s', '%s'], ['%d']);
            }
        }

        delete_transient('dgw_home_cases_logos');
    }

    /**
     * [18/11/2026] - Chuyển logo vào/ra thùng rác dùng prepared statement cho danh sách ID
     * [2026-11-19] - @author: Kelvin - Sử dụng helper bulkUpdateByIds() từ BaseCustomTableModel
     */
    public function toTrash($arrData = [], $options = [])
    {
        $trash = ($options === 'trash') ? 1 : 0;
        $this->bulkUpdateByIds($this->table, ['trash' => $trash], ['%d'], $arrData['id'] ?? null);
        delete_transient('dgw_home_cases_logos');
    }

    /**
     * [2026-11-20] - @author: Kelvin - Xóa vĩnh viễn logo kèm xóa file ảnh vật lý trên ổ đĩa và làm mới cache trang chủ
     */
    public function toDelete($arrData = [], $options = [])
    {
        $ids = $this->normalizeIds($arrData['id'] ?? null);
        if (empty($ids)) {
            return;
        }

        foreach ($ids as $id) {
            $this->removeImg($id);
        }

        $this->bulkDeleteByIds($this->table, $ids);
        delete_transient('dgw_home_cases_logos');
    }

    /**
     * [2026-11-20] - @author: Kelvin - Xóa file ảnh logo trên ổ đĩa theo ID bản ghi
     */
    public function removeImg($id): void
    {
        $data = $this->getItem($id);
        if (is_array($data) && !empty($data['img'])) {
            $this->removeImgByName((string) $data['img']);
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Xóa file ảnh logo an toàn bằng basename() chống Path Traversal
     */
    public function removeImgByName(string $name): void
    {
        $filename = basename($name);
        if ($filename === '' || $filename === '.' || $filename === '..') {
            return;
        }

        $filePath = DIR_IMAGES . 'logo' . DS . $filename;
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }
}
