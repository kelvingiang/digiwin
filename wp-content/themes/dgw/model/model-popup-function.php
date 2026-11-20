<?php
declare(strict_types=1);

use DGW\Models\BaseCustomTableModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Model thao tác bảng pop-up (kế thừa BaseCustomTableModel, vá Path Traversal, đồng bộ xóa ảnh mobile và format SQL)
 */
class Model_Popup_Function extends BaseCustomTableModel
{
    private string $table;

    /**
     * [2026-11-19] - @author: Kelvin - Khởi tạo kết nối DB và tên bảng pop_up
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'pop_up';
    }

    /**
     * [18/11/2026] - Lấy danh sách pop-up dùng prepared statement, sửa lỗi cú pháp khi $status rỗng
     */
    public function getAll($status = '')
    {
        if ($status !== '' && $status !== null) {
            $sql = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = %d", (int) $status);
        } else {
            $sql = "SELECT * FROM {$this->table}";
        }

        return $this->db->get_results($sql, ARRAY_A);
    }

    /**
     * [18/11/2026] - Lấy chi tiết pop-up theo ID dùng $this->db->prepare()
     */
    public function getItem($id)
    {
        $popupId = absint($id);
        if ($popupId <= 0) {
            return null;
        }

        $sql = $this->db->prepare("SELECT * FROM {$this->table} WHERE ID = %d", $popupId);
        return $this->db->get_row($sql, ARRAY_A);
    }

    /**
     * [18/11/2026] - Lấy pop-up đang kích hoạt dùng $this->db->prepare()
     */
    public function getActive()
    {
        $sql = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = %d", 1);
        return $this->db->get_row($sql, ARRAY_A);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lưu mới hoặc cập nhật pop-up với sanitize đầu vào, format chuẩn SQL và xóa đủ 4 ảnh cũ khi thay thế
     */
    public function Save($arrData, $option)
    {
        $target = (!empty($arrData['chk-target']) && (int) $arrData['chk-target'] === 1) ? 1 : 0;

        $data = [
            'title'   => sanitize_text_field((string) ($arrData['txt-title'] ?? '')),
            'link_cn' => esc_url_raw((string) ($arrData['txt-link-cn'] ?? '')),
            'link_vn' => esc_url_raw((string) ($arrData['txt-link-vn'] ?? '')),
            'id_cn'   => sanitize_text_field((string) ($arrData['txt-id-cn'] ?? '')),
            'id_vn'   => sanitize_text_field((string) ($arrData['txt-id-vn'] ?? '')),
            'target'  => $target,
        ];
        $formats = ['%s', '%s', '%s', '%s', '%s', '%d'];

        $imageFieldMap = [
            'file-img-cn'          => 'img_cn',
            'file-img-vertical-cn' => 'img_mobile_cn',
            'file-img-vn'          => 'img_vn',
            'file-img-vertical-vn' => 'img_mobile_vn',
        ];

        foreach ($imageFieldMap as $inputKey => $dbColumn) {
            if (!empty($arrData[$inputKey])) {
                $data[$dbColumn] = sanitize_file_name((string) $arrData[$inputKey]);
                $formats[]       = '%s';
            }
        }

        $editId = absint($arrData['hid-id'] ?? 0);

        if ($editId === 0) {
            $data['create_date'] = wp_date('Y-m-d');
            $formats[]           = '%s';
        } else {
            $item = $this->getItem($editId);
            if (is_array($item)) {
                foreach ($imageFieldMap as $inputKey => $dbColumn) {
                    if (isset($arrData[$inputKey]) && !empty($item[$dbColumn]) && $item[$dbColumn] !== $arrData[$inputKey]) {
                        $this->removeImgByName((string) $item[$dbColumn]);
                    }
                }
            }
        }

        if ($option === 'add') {
            $this->db->insert($this->table, $data, $formats);
        } elseif ($option === 'edit' && $editId > 0) {
            $this->db->update($this->table, $data, ['ID' => $editId], $formats, ['%d']);
        }
    }

    /**
     * [18/11/2026] - Chuyển pop-up vào/ra thùng rác dùng prepared statement cho danh sách ID
     * [2026-11-19] - @author: Kelvin - Sử dụng helper bulkUpdateByIds() từ BaseCustomTableModel
     */
    public function toTrash($arrData = [], $options = [])
    {
        $trash = ($options === 'trash') ? 1 : 0;
        $this->bulkUpdateByIds($this->table, ['trash' => $trash], ['%d'], $arrData['id'] ?? null);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Kích hoạt duy nhất pop-up được chọn
     */
    public function toActive($arrData = [], $options = [])
    {
        $id = absint($arrData['id'] ?? 0);
        if ($id <= 0) {
            return;
        }

        $sql = $this->db->prepare(
            "UPDATE {$this->table} SET status = CASE WHEN ID = %d THEN 1 ELSE 0 END",
            $id
        );
        $this->db->query($sql);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Ngưng kích hoạt pop-up theo ID
     */
    public function toPassive($arrData = [], $options = [])
    {
        $id = absint($arrData['id'] ?? 0);
        if ($id <= 0) {
            return;
        }

        $this->db->update($this->table, ['status' => 0], ['ID' => $id], ['%d'], ['%d']);
    }

    /**
     * [18/11/2026] - Xóa vĩnh viễn pop-up và ảnh liên quan dùng prepared statement cho danh sách ID
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
    }

    /**
     * [2026-11-19] - @author: Kelvin - Xóa toàn bộ 4 file ảnh desktop/mobile của pop-up khi xóa bản ghi
     */
    public function removeImg($id)
    {
        $data = $this->getItem($id);
        if (!is_array($data)) {
            return;
        }

        foreach (['img_vn', 'img_cn', 'img_mobile_vn', 'img_mobile_cn'] as $column) {
            if (!empty($data[$column])) {
                $this->removeImgByName((string) $data[$column]);
            }
        }
    }

    /**
     * [2026-11-19] - @author: Kelvin - Xóa file ảnh an toàn bằng basename() chống Path Traversal
     */
    public function removeImgByName($name)
    {
        $filename = basename((string) $name);
        if ($filename === '' || $filename === '.' || $filename === '..') {
            return;
        }

        $filePath = DIR_IMAGES . 'pop-up' . DS . $filename;
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }
}
