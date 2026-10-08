<?php
/**
 * 29/06/2026: Gộp và refactor các file xử lý AJAX Language thành chuẩn WordPress.
 * Các hàm bao gồm: change_language, change_languages
 */

// 1. Chức năng Đổi ngôn ngữ (Dùng Cookie - từ file change_languages.php)
add_action('wp_ajax_change_languages', 'dgw_ajax_change_languages_handler');
add_action('wp_ajax_nopriv_change_languages', 'dgw_ajax_change_languages_handler');
function dgw_ajax_change_languages_handler() {
    /**
     * [2026-10-06] - Xử lý chuyển đổi ngôn ngữ thông qua Cookie site_lang ('cn' hoặc 'vn')
     * Đảm bảo bảo mật với sanitize_text_field và phản hồi JSON chuẩn cấu trúc
     */
    $response = ['status' => 'error'];

    if (!empty($_POST['type'])) {
        $type = sanitize_text_field(wp_unslash($_POST['type']));
        $lang = ($type === 'cn') ? 'cn' : 'vn';

        setcookie(
            'site_lang',
            $lang,
            time() + YEAR_IN_SECONDS,
            '/'
        );

        // Thiết lập biến cookie để request hiện tại có thể dùng ngay
        $_COOKIE['site_lang'] = $lang;

        $response = [
            'status'       => 'ok',
            'current_lang' => $lang,
        ];
    }

    wp_send_json($response);
}

// 2. Chức năng Đổi ngôn ngữ (Dùng Session - từ file bk.change_language.php / change_language.php)
add_action('wp_ajax_change_language', 'dgw_ajax_change_language_handler');
add_action('wp_ajax_nopriv_change_language', 'dgw_ajax_change_language_handler');
function dgw_ajax_change_language_handler() {
    // 29/06/2026: Xử lý thay đổi ngôn ngữ bằng Session (Dự phòng cho bản cũ)
    if (!session_id()) session_start();
    
    $response = array('status' => 'error');

    if (isset($_POST['type'])) {
        $_SESSION['languages'] = sanitize_text_field($_POST['type']);
        $response = array('status' => 'ok');
    }

    wp_send_json($response);
}
