<?php

define('WP_USE_THEMES', false);
require('../../../../wp-load.php');
date_default_timezone_set('Asia/Ho_Chi_Minh');

$old_pass = md5($_POST['old_pass']);
$new_pass = md5($_POST['new_pass']);
$person_id = $_POST['person'];


if (!empty($old_pass)) {
    global $wpdb;
    $table   = $wpdb->prefix . 'shareholder';

    $sql    = "SELECT ID FROM $table WHERE `ID` = '$person_id' AND `pass` = '$old_pass'";
    $row   = $wpdb->get_row($sql, ARRAY_A);

    if (count($row) > 0) {
        $data = array('pass' => $new_pass, 'update_date' => date('m-d-Y'));
        $where = array('ID' => $row['ID']);
        $wpdb->update($table, $data, $where);

        $response = array('status' => 'success', 'message' => $data);
    } else {
        $response = array('status' => 'failure', 'message' => '0000');
    }

    // end ================================================================================================     

}

echo json_encode($response);
