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
    // [04/11/2026] - Chống cache cho phản hồi AJAX và kiểm tra phiên đăng nhập thực tế từ Cookie/DB trước tiên (tương thích LiteSpeed Cache)
    nocache_headers();

    // 1. Kiểm tra đăng nhập
    $user = is_member_logged_in();
    if (!$user) {
        wp_send_json_error(['code' => 'not_logged_in', 'message' => 'Vui lòng đăng nhập trước khi tải tài liệu']);
    }

    // 2. Nhận và làm sạch dữ liệu
    $post_id     = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $post_title  = isset($_POST['post_title']) ? sanitize_text_field(wp_unslash($_POST['post_title'])) : '';
    $post_source = (string) get_post_meta($post_id, '_metabox_source', true);

    if (empty($post_source)) {
        wp_send_json_error(['message' => 'Tài liệu này hiện chưa có link tải']);
    }

    // Convert link Google Drive sang link download trực tiếp
    $download_url = convert_gdrive_to_download($post_source);

    // 4. [03/11/2026] - Lưu lịch sử tải vào cơ sở dữ liệu nội bộ (kid = 1: Download file)
    get_model()->insert_download_detail([
        'user_id'  => $user->ID,
        'title'    => $post_title,
        'resource' => $post_source,
        'kid'      => 1,
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

add_action('wp_ajax_my_watch_youtube',        'my_watch_youtube');
add_action('wp_ajax_nopriv_my_watch_youtube', 'my_watch_youtube');

/**
 * [03/11/2026] - Ajax Handler: Xử lý ghi nhận thành viên xem video YouTube tại youtube-popup.php,
 * lưu lịch sử vào DB, đồng bộ dữ liệu sang Google Sheets (sheet wytList) và gửi thông báo Lark Bot.
 */
function my_watch_youtube(): void
{
    // [04/11/2026] - Chống cache cho phản hồi AJAX và kiểm tra phiên đăng nhập thực tế từ Cookie/DB trước tiên (tương thích LiteSpeed Cache)
    nocache_headers();

    // 1. Kiểm tra đăng nhập thành viên
    $user = is_member_logged_in();
    if (!$user) {
        wp_send_json_error(['code' => 'not_logged_in', 'message' => 'Vui lòng đăng nhập trước khi xem video']);
    }

    // 2. Nhận và làm sạch dữ liệu đầu vào
    $post_id      = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $post_title   = isset($_POST['post_title']) ? sanitize_text_field(wp_unslash($_POST['post_title'])) : '';
    $youtube_link = (string) get_post_meta($post_id, '_metabox_youtube', true);

    if (empty($youtube_link)) {
        wp_send_json_error(['message' => 'Bài viết này hiện chưa có link video YouTube']);
    }

    // 4. [03/11/2026] - Lưu lịch sử xem video vào cơ sở dữ liệu nội bộ (kid = 2: Xem video YouTube)
    get_model()->insert_download_detail([
        'user_id'  => $user->ID,
        'title'    => $post_title,
        'resource' => $youtube_link,
        'kid'      => 2,
    ]);

    // 5. Đồng bộ dữ liệu qua Service Google Sheets (ghi vào sheet wytList)
    $data_for_sheet = [
        'sheet' => 'wytList',
        'title' => $post_title,
        'file'  => $youtube_link,
        'name'  => $user->username ?? '',
        'email' => $user->email ?? '',
        'date'  => date('d-M-y H:i:s'),
    ];

    $sync_result = sync_to_google_sheets($data_for_sheet);

    // 6. Gửi thông báo đến Lark Bot nếu đồng bộ Google Sheets thành công
    if ($sync_result) {
        $lark_msg = sprintf(
            "%s was watched by %s on %s at %s",
            $post_title,
            $user->username ?? 'Guest',
            date('d-M-y'),
            date('H:i:s')
        );
        sendNotificationToLark($lark_msg);
    } else {
        error_log('AJAX YouTube Watch: Đồng bộ Google Sheets (wytList) thất bại cho bài viết ID: ' . $post_id);
    }

    // 7. Trả kết quả JSON về cho frontend để mở Popup YouTube
    wp_send_json_success([
        'post_id'      => $post_id,
        'post_title'   => $post_title,
        'youtube_link' => $youtube_link,
        'message'      => 'Ghi nhận thông tin xem video thành công!',
    ]);
}
