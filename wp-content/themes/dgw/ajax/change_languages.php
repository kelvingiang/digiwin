<?php
define('WP_USE_THEMES', false);
require_once '../../../../wp-load.php';

$response = ['status' => 'error'];

if (!empty($_POST['type'])) {

    $lang = ($_POST['type'] === 'cn') ? 'cn' : 'vn';

    setcookie(
        'site_lang',
        $lang,
        time() + YEAR_IN_SECONDS,
        COOKIEPATH,
        COOKIE_DOMAIN
    );

    // 讓當次 request 立即可讀
    $_COOKIE['site_lang'] = $lang;

    $response = ['status' => 'ok'];
}

wp_send_json($response);