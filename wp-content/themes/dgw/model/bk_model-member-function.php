<?php

class Model_Member_Function {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'member';
    }

    public function getAll($status = '') {
        global $wpdb;
        if (empty($status)) {
            $sql = "SELECT * FROM $this->table WHERE status = $status";
        } else {
            $sql = "SELECT * FROM $this->table";
        }
        $row = $wpdb->get_results($sql, ARRAY_A);
        return $row;
    }

    public function getItem($id) {
        global $wpdb;
        $sql = "SELECT * FROM $this->table WHERE ID = $id";
        $row = $wpdb->get_row($sql, ARRAY_A);
        return $row;
    }

    public function Save($arrData, $option) {
        global $current_user;
        $data = array(
            'company' => $arrData['txt_company'],
            'career' => $arrData['txt_career'],
            'use_soft' => $arrData['txt_soft'],
            'is_order' => $arrData['txt_order'],
            'description' => $arrData['txt_description'],
            'seo_title' => $arrData['txt_seo_title'],    
            'seo_key' => $arrData['txt_seo_key'],    
            'seo_description' => $arrData['txt_seo_description'],    
        );

        $add = array(
            'create_date' => date("d-m-Y"),
            'create_by' => $current_user->user_login,
        );

        $update = array(
            'update_date' => date("d-m-Y"),
            'update_by' => $current_user->user_login,
        );

        global $wpdb;
        if ($option == 'add') {
            $AddData = array_merge($data, $add);
            $wpdb->insert($this->table, $AddData);
        } elseif ($option == 'edit') {
            echo '<pre>';
            print_r($arrData);
            echo '</pre>';

            echo $option;
          
            $UpdateData = array_merge($data, $update);
            $where = array('ID' => $arrData['hid-id']);
            $wpdb->update($this->table, $UpdateData, $where);
        }
    }

    // THAY DOI TRANG THAI 
    public function toTrash($arrData = array(), $options = array()) {
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

    // XOA DATA
    public function deleteItem($arrData = array(), $options = array()) {
        global $wpdb;
// KIEM TRA PHAN DELETE CÓ PHAN DANG CHUOI HAY KHONG

        if (!is_array($arrData['id'])) {
            $where = array('ID' => absint($arrData['id']));
            $wpdb->delete($this->table, $where);
        } else {
            $arrData['id'] = array_map('absint', $arrData['id']);
            $ids = join(',', $arrData['id']);
            echo '<br>' . $ids;
            $sql = "DELETE FROM $this->table WHERE ID IN ($ids)";
            $wpdb->query($sql);
        }
    }

}
