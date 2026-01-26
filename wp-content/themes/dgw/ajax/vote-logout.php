<?php
define('WP_USE_THEMES', false);
require('../../../../wp-load.php');
date_default_timezone_set('Asia/Ho_Chi_Minh');

unset($_SESSION['vote-login']);
$response = array('status' => 'success', 'message' => '');
echo json_encode($response);
