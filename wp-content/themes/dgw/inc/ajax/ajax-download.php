<?php
declare(strict_types=1);

// [2026-10-05] - @author: Kelvin - Ajax Handler: Xử lý yêu cầu tải file tài liệu (Download Resource), đồng bộ Google Sheets và gửi thông báo Lark

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_my_download_file',        'my_download_file');
add_action('wp_ajax_nopriv_my_download_file', 'my_download_file');

/**
 * [2026-10-05] - @author: Kelvin - Xử lý tải tài liệu dành cho thành viên đã đăng nhập
 */
function my_download_file(): void
{
    // 1. Kiểm tra Nonce bảo mật
    if (!check_ajax_referer('my_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Yêu cầu không hợp lệ (Invalid Nonce)']);
    }

    // 2. Kiểm tra đăng nhập
    $user = is_member_logged_in();
    if (!$user) {
        wp_send_json_error(['code' => 'not_logged_in', 'message' => 'Vui lòng đăng nhập trước khi tải tài liệu']);
    }

    // 3. Nhận và làm sạch dữ liệu
    $post_id     = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $post_title  = isset($_POST['post_title']) ? sanitize_text_field(wp_unslash($_POST['post_title'])) : '';
    $post_source = (string) get_post_meta($post_id, '_metabox_source', true);

    if (empty($post_source)) {
        wp_send_json_error(['message' => 'Tài liệu này hiện chưa có link tải']);
    }

    // Convert link Google Drive sang link download trực tiếp
    $download_url = convert_gdrive_to_download($post_source);

    // 4. Lưu lịch sử tải vào cơ sở dữ liệu nội bộ
    get_model()->insert_download_detail([
        'user_id'  => $user->ID,
        'title'    => $post_title,
        'resource' => $post_source,
    ]);

    // 5. Đồng bộ qua Service Google Sheets
    $data_for_sheet = [
        'sheet' => 'acList',
        'title' => $post_title,
        'file'  => $post_source,
        'name'  => $user->username ?? '',
        'email' => $user->email ?? '',
        'date'  => date('d-M-y H:i:s'),
    ];

    $sync_result = sync_to_google_sheets($data_for_sheet);

    // 6. Gửi thông báo đến Lark Bot nếu đồng bộ thành công
    if ($sync_result) {
        $lark_msg = sprintf(
            "%s was downloaded by %s on %s at %s",
            $post_title,
            $user->username ?? 'Guest',
            date('d-M-y'),
            date('H:i:s')
        );
        sendNotificationToLark($lark_msg);
    } else {
        error_log('AJAX Download: Đồng bộ Google Sheets thất bại cho bài viết ID: ' . $post_id);
    }

    // 7. Trả kết quả JSON cho frontend
    wp_send_json_success([
        'post_id'     => $post_id,
        'post_title'  => $post_title,
        'post_source' => $download_url,
        'message'     => 'Nhận thông tin thành công!',
    ]);
}
