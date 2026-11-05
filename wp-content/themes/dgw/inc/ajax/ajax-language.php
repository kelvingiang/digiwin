<?php
/**
 * 29/06/2026: Gộp và refactor các file xử lý AJAX Language thành chuẩn WordPress.
 * [05/11/2026]: Loại bỏ chức năng đổi ngôn ngữ bằng Session cũ (change_language), hệ thống sử dụng đồng bộ Cookie site_lang.
 */

// Chức năng Đổi ngôn ngữ (Dùng Cookie site_lang)
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
