<?php
declare(strict_types=1);

namespace DGW\Models;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Model thao tác bảng pop-up (kế thừa BaseCustomTableModel, vá Path Traversal, đồng bộ xóa ảnh mobile và format SQL)
 * [2026-11-20] - @author: Kelvin - Quy hoạch về chuẩn PSR-4 src/Models/PopupModel
 */
class PopupModel extends BaseCustomTableModel
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

    /**
     * [20/11/2026] - Lấy dữ liệu Pop-up đang kích hoạt kèm cache kích thước ảnh Transient 24 giờ (tách từ templates/home-popup.php)
     *
     * @return array<string, mixed>|null
     */
    public static function getActiveForDisplay(string $lang): ?array
    {
        $data = (new self())->getActive();
        if (empty($data) || !is_array($data)) {
            return null;
        }

        $popupId     = !empty($data['id_' . $lang]) ? absint($data['id_' . $lang]) : 0;
        $popupLink   = !empty($data['link_' . $lang]) ? (string) $data['link_' . $lang] : '#';
        $popupTarget = (!empty($data['target']) && (int) $data['target'] === 1) ? '_blank' : '_self';
        $popupTitle  = !empty($data['title']) ? (string) $data['title'] : 'Digiwin Software Announcement';
        $rawTarget   = (string) ($data['target'] ?? '');

        $imgNamePc = !empty($data['img_' . $lang]) ? (string) $data['img_' . $lang] : '';
        $imgNameMb = !empty($data['img_mobile_' . $lang]) ? (string) $data['img_mobile_' . $lang] : '';

        $imgUrlPc = $imgNamePc !== '' ? PART_IMAGES . 'pop-up/' . $imgNamePc : '';
        $imgUrlMb = $imgNameMb !== '' ? PART_IMAGES . 'pop-up/' . $imgNameMb : '';

        $cacheKey = 'dgw_popup_dim_' . md5($imgNamePc . $imgNameMb);
        $imgSizes = get_transient($cacheKey);

        if (false === $imgSizes || !is_array($imgSizes)) {
            $imgSizes = ['pc' => null, 'mb' => null];

            if ($imgNamePc !== '') {
                $imgPathPc = DIR_IMAGES . 'pop-up' . DS . basename($imgNamePc);
                if (is_file($imgPathPc)) {
                    $sizePc = @getimagesize($imgPathPc);
                    if ($sizePc) {
                        $imgSizes['pc'] = ['w' => (int) $sizePc[0], 'h' => (int) $sizePc[1]];
                    }
                }
            }

            if ($imgNameMb !== '') {
                $imgPathMb = DIR_IMAGES . 'pop-up' . DS . basename($imgNameMb);
                if (is_file($imgPathMb)) {
                    $sizeMb = @getimagesize($imgPathMb);
                    if ($sizeMb) {
                        $imgSizes['mb'] = ['w' => (int) $sizeMb[0], 'h' => (int) $sizeMb[1]];
                    }
                }
            }

            set_transient($cacheKey, $imgSizes, DAY_IN_SECONDS);
        }

        return [
            'popup_id'     => $popupId,
            'popup_link'   => $popupLink,
            'popup_target' => $popupTarget,
            'popup_title'  => $popupTitle,
            'raw_target'   => $rawTarget,
            'img_url_pc'   => $imgUrlPc,
            'img_url_mb'   => $imgUrlMb,
            'size_pc'      => $imgSizes['pc'] ?? null,
            'size_mb'      => $imgSizes['mb'] ?? null,
        ];
    }
}
