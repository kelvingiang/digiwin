<?php
define('WP_USE_THEMES', false);
require('../../../../wp-load.php');
date_default_timezone_set('Asia/Ho_Chi_Minh');

global $wpdb;
$table   = $wpdb->prefix . 'shareholder';
$email = $_POST['email'];
$pass = md5($_POST['pass']);

$sql    = "SELECT ID, name_cn, name_en, pass, email FROM $table WHERE `email` = '$email' AND `pass` = '$pass'";
$row   = $wpdb->get_row($sql, ARRAY_A);

if (count($row) > 0) {

    $_SESSION['vote-login'] = $row;
    $response = array('status' => 'success', 'message' => '');
} else {
    $response = array('status' => 'failure', 'message' => '您的E-mail或者密碼不正確 <br> E-mail hoặc Mật mã không đúng' );
}


echo json_encode($response);
