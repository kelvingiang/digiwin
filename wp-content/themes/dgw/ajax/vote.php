<?php

define('WP_USE_THEMES', false);
require('../../../../wp-load.php');
date_default_timezone_set('Asia/Ho_Chi_Minh');

$vote_id = $_POST['id'];
$vote = $_POST['vote'];
$person_id = $_POST['person'];


if (!empty($vote_id)) {
    global $wpdb;
    $table   = $wpdb->prefix . 'vote_detail';

    $sql    = "SELECT ID FROM $table WHERE `vote_id` = '$vote_id' AND `person_id` = '$person_id'";
    $row   = $wpdb->get_row($sql, ARRAY_A);
   
    if (count($row) > 0) {
        $data = array('val' => $vote, 'update_date' => date('m-d-Y  H:i:s'));
        $where = array('ID' => $row['ID']);
        $wpdb->update($table, $data, $where);
      
         
    } else {
        $data = array(
            'vote_id' => $vote_id,
            'person_id' => $person_id,
            'val' => $vote,
            'create_date' => date('m-d-Y  H:i:s'),
        );
        $wpdb->insert($table, $data);
      
    }

    $response = array('status' => 'success', 'message' => $data);
    // end ================================================================================================     

} else {
    $response = array('status' => 'error', 'message' => '0000');
}

echo json_encode($response);
