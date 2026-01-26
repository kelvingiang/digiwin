<?php

class Model_Popup_Function
{

    private $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'pop_up';
    }

    public function getAll($status = '')
    {
        global $wpdb;
        if (empty($status)) {
            $sql = "SELECT * FROM $this->table WHERE status = $status";
        } else {
            $sql = "SELECT * FROM $this->table";
        }
        $row = $wpdb->get_results($sql, ARRAY_A);
        return $row;
    }

    public function getItem($id)
    {
        global $wpdb;
        $sql = "SELECT * FROM $this->table WHERE ID = $id";
        $row = $wpdb->get_row($sql, ARRAY_A);
        return $row;
    }

    public function getActive()
    {
        global $wpdb;
        $sql = "SELECT * FROM $this->table WHERE status = 1";
        $row = $wpdb->get_row($sql, ARRAY_A);
        return $row;
    }

    public function Save($arrData, $option)
    {
        global $current_user;
        $target = (!empty($arrData['chk-target']) && $arrData['chk-target'] == 1) ? 1 : 0;

        // echo '<pre>'; print_r($arrData); echo '</pre>';
        // die();
        $data = array(
            'title' => $arrData['txt-title'],
            'link_cn' => $arrData['txt-link-cn'],
            'link_vn' => $arrData['txt-link-vn'],
            'id_cn' => $arrData['txt-id-cn'],
            'id_vn' => $arrData['txt-id-vn'],
            'target' => $target,
        );

        if (!empty($arrData['file-img-cn'])) {
            $img1['img_cn'] = $arrData['file-img-cn'];
            $data = array_merge($data, $img1);
        }

        if (!empty($arrData['file-img-vertical-cn'])) {
            $img1['img_mobile_cn'] = $arrData['file-img-vertical-cn'];
            $data = array_merge($data, $img1);
        }

        if (!empty($arrData['file-img-vn'])) {
            $img2['img_vn'] = $arrData['file-img-vn'];
            $data = array_merge($data, $img2);
        }

        if (!empty($arrData['file-img-vertical-vn'])) {
            $img1['img_mobile_vn'] = $arrData['file-img-vertical-vn'];
            $data = array_merge($data, $img1);
        }

        if (empty($arrData['hid-id'])) {
            $date['create_date'] = date("Y-d-m");
            $data = array_merge($data, $date);
        }

        // xoa hinh cu khi cap nhat hinh moi
        if (!empty($arrData['hid-id'])) {
            // lay data da co trong database so sanh gia upload img moi 
            $item = $this->getItem($arrData['hid-id']);

            // neu co khac se xoa file anh cu 
            if (isset($arrData['file-img-vn']) && $item['img_vn'] != $arrData['file-img-vn']) {
                $this->removeImgByName($item['img_vn']);
            }

            if (isset($arrData['file-img-cn']) && $item['img_cn'] != $arrData['file-img-cn']) {
                $this->removeImgByName($item['img_cn']);
            }
        }

        global $wpdb;
        if ($option == 'add') {
            $wpdb->insert($this->table, $data);
        } elseif ($option == 'edit') {
            $where = array('ID' => $arrData['hid-id']);
            $wpdb->update($this->table, $data, $where);
        }
    }



    // THAY DOI TRANG THAI 
    public function toTrash($arrData = array(), $options = array())
    {
        global $wpdb;
        $trash = ($options == 'trash') ? 1 : 0;

        // KIEM TRA PHAN UPFDATE CÓ PHAN DANG CHUOI HAY KHONG

        if (!is_array($arrData['id'])) {
            $data = array('trash' => $trash);
            $where = array('ID' => absint($arrData['id']));
            $wpdb->update($this->table, $data, $where);
        } else {
            $arrData['id'] = array_map('absint', $arrData['id']);
            $ids = join(',', $arrData['id']);
            $sql = "UPDATE $this->table SET trash = $trash  WHERE id IN ($ids)";
            $wpdb->query($sql);
        }
    }

    // THAY DOI TRANG THAI 
    public function toActive($arrData = array(), $options = array())
    {
        global $wpdb;
        $id = (int) $arrData['id']; // 記得轉型避免 SQL Injection

        $sql = $wpdb->prepare("
                UPDATE $this->table
                SET status = CASE 
                WHEN id = %d THEN 1
                ELSE 0
                END", $id);

        $wpdb->query($sql);
    }

    // THAY DOI TRANG THAI 
    public function toPassive($arrData = array(), $options = array())
    {
        global $wpdb;
        $id = (int) $arrData['id']; // 記得轉型避免 SQL Injection

        $sql = $wpdb->prepare("
                UPDATE $this->table
                SET status = 0 
                WHERE id = %d ", $id);
        $wpdb->query($sql);
    }

    // XOA DATA
    public function toDelete($arrData = array(), $options = array())
    {
        global $wpdb;

        // KIEM TRA PHAN DELETE CÓ PHAN DANG CHUOI HAY KHONG
        $ids = (array) $arrData['id'];               // 確保是陣列
        $ids = array_map('absint', $ids);            // 安全轉型

        foreach ($ids as $id) {
            $this->removeImg($id);                   // 逐一刪除圖片
        }

        $ids_sql = join(',', $ids);
        $sql = "DELETE FROM $this->table WHERE ID IN ($ids_sql)";
        $wpdb->query($sql);
    }

    public function removeImg($id)
    {
        $data = $this->getItem($id);
        $path = DIR_IMAGES . 'pop-up' . DS;
        foreach ($data as $key => $val) {
            if ($key == "img_vn" || $key == "img_cn") {
                if (is_file($path . $val)) {
                    unlink($path . $val);
                }
            }
        }
    }

    public function removeImgByName($name)
    {
        $path = DIR_IMAGES . 'pop-up' . DS;
        if (is_file($path . $name)) {
            unlink($path . $name);
        }
    }
}
