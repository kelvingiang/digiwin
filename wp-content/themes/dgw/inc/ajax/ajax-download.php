<?php
declare(strict_types=1);

// [2026-10-05] - @author: Kelvin - Ajax Handler: Xử lý yêu cầu tải file tài liệu (Download Resource), đồng bộ Google Sheets và gửi thông báo Lark

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_my_download_file',        'my_download_file');
add_action('wp_ajax_nopriv_my_download_file', 'my_download_file');

add_action('wp_ajax_dgw_force_download',        'dgw_force_download');
add_action('wp_ajax_nopriv_dgw_force_download', 'dgw_force_download');

/**
 * [09/11/2026] - Chuyển đổi linh hoạt link Google Drive sang link tải trực tiếp (hỗ trợ cả /file/d/ và id=) kèm bypass xác nhận quét virus
 */
function dgw_convert_gdrive_url(string $url): string
{
    if (preg_match('/(?:\/file\/d\/|id=)([a-zA-Z0-9_-]{25,})/', $url, $matches)) {
        return 'https://drive.google.com/uc?export=download&id=' . $matches[1] . '&confirm=t';
    }
    return function_exists('convert_gdrive_to_download') ? convert_gdrive_to_download($url) : $url;
}

/**
 * [09/11/2026] - Trích xuất tên file và thông tin file từ HTTP Headers hoặc URL
 */
function dgw_detect_file_info(string $url, string $fallback_title = ''): array
{
    $response = wp_remote_head($url, [
        'timeout'     => 5,
        'redirection' => 5,
        'sslverify'   => false,
    ]);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 405) {
        $response = wp_remote_get($url, [
            'timeout'     => 5,
            'redirection' => 5,
            'sslverify'   => false,
            'headers'     => ['Range' => 'bytes=0-0'],
        ]);
    }

    $filename     = '';
    $content_type = '';
    $filesize     = null;
    $http_code    = 0;

    if (!is_wp_error($response)) {
        $http_code    = (int) wp_remote_retrieve_response_code($response);
        $headers      = wp_remote_retrieve_headers($response);
        $content_type = (string) ($headers['content-type'] ?? '');
        $filesize     = isset($headers['content-length']) ? (int) $headers['content-length'] : null;

        $cd = (string) ($headers['content-disposition'] ?? '');
        if (!empty($cd)) {
            if (preg_match('/filename\*=UTF-8\'\'([^;\s]+)/i', $cd, $matches)) {
                $filename = rawurldecode(trim($matches[1], ' "\''));
            } elseif (preg_match('/filename[^;=\n]*=(([\'"]).*?\2|[^;\s\n]*)/i', $cd, $matches)) {
                $filename = trim($matches[1], ' "\'');
            }
        }
    }

    if (empty($filename) || $filename === 'download' || $filename === 'uc' || strpos($filename, '.') === false) {
        $path = parse_url($url, PHP_URL_PATH);
        $base = basename($path ?: '');
        if (!empty($base) && strpos($base, '.') !== false) {
            $filename = $base;
        }
    }

    if (empty($filename) || $filename === 'download' || $filename === 'uc' || strpos($filename, '.') === false) {
        $ext = 'pdf';
        if (stripos($content_type, 'spreadsheetml') !== false || stripos($content_type, 'excel') !== false) {
            $ext = 'xlsx';
        } elseif (stripos($content_type, 'wordprocessingml') !== false || stripos($content_type, 'msword') !== false) {
            $ext = 'docx';
        } elseif (stripos($content_type, 'zip') !== false) {
            $ext = 'zip';
        }
        $safe_title = sanitize_file_name($fallback_title);
        $filename = (!empty($safe_title) ? $safe_title : 'document') . '.' . $ext;
    }

    return [
        'filename'     => $filename,
        'filesize'     => $filesize,
        'content_type' => $content_type,
        'http_code'    => $http_code,
    ];
}

/**
 * [09/11/2026] - Gửi HTTP Header ép buộc trình duyệt tải trực tiếp về ổ cứng, không mở viewer inline hay tab mới
 */
function dgw_send_force_download_headers(string $filename, ?int $filesize = null): void
{
    while (ob_get_level()) {
        ob_end_clean();
    }

    $safe_ascii_filename = sanitize_file_name($filename);
    if (empty($safe_ascii_filename)) {
        $safe_ascii_filename = 'document.pdf';
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $safe_ascii_filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    if ($filesize !== null && $filesize > 0) {
        header('Content-Length: ' . (string) $filesize);
    }
}

/**
 * [09/11/2026] - Stream dữ liệu file từ xa trực tiếp về trình duyệt với buffer 64KB, tối ưu bộ nhớ
 */
function dgw_stream_remote_file(string $url, string $filename): void
{
    @set_time_limit(0);

    dgw_send_force_download_headers($filename);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_BUFFERSIZE, 65536);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) {
        echo $chunk;
        if (function_exists('flush')) {
            flush();
        }
        return strlen($chunk);
    });

    curl_exec($ch);
    curl_close($ch);
    exit;
}

/**
 * [2026-10-05] - @author: Kelvin - Xử lý tải tài liệu dành cho thành viên đã đăng nhập
 */
function my_download_file(): void
{
    // [04/11/2026] - Chống cache cho phản hồi AJAX và kiểm tra phiên đăng nhập thực tế từ Cookie/DB trước tiên (tương thích LiteSpeed Cache)
    nocache_headers();

    $lang = function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn';

    // 1. [09/11/2026] - Kiểm tra nghiêm ngặt trạng thái đăng nhập trước khi cấp quyền tải file tài liệu
    $user = is_member_logged_in();
    if (!$user) {
        $msg = ($lang === 'cn') ? '請先登入會員以進行檔案下載' : 'Vui lòng đăng nhập trước khi tải tài liệu';
        wp_send_json_error(['code' => 'not_logged_in', 'message' => $msg]);
    }

    // 2. Nhận và làm sạch dữ liệu
    $post_id     = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $post_title  = isset($_POST['post_title']) ? sanitize_text_field(wp_unslash($_POST['post_title'])) : '';
    $post_source = (string) get_post_meta($post_id, '_metabox_source', true);
    $post_source = trim($post_source);

    // [09/11/2026] - Kiểm tra tồn tại và định dạng link tải hợp lệ, tránh lỗi chuyển trang ngoài ý muốn
    if (empty($post_source)) {
        $msg = ($lang === 'cn') ? '此文章目前尚無可供下載的檔案' : 'Tài liệu này hiện chưa có link tải';
        wp_send_json_error(['code' => 'no_link', 'message' => $msg]);
    }

    if (!filter_var($post_source, FILTER_VALIDATE_URL)) {
        $msg = ($lang === 'cn') ? '檔案下載連結格式不正確' : 'Link tải tài liệu không đúng định dạng';
        wp_send_json_error(['code' => 'invalid_link', 'message' => $msg]);
    }

    // Convert link Google Drive sang link download trực tiếp
    $download_url = dgw_convert_gdrive_url($post_source);

    // [09/11/2026] - Kiểm tra tính khả dụng của link và trích xuất thông tin file (tên file, content-type)
    $file_info = dgw_detect_file_info($download_url, $post_title);
    if ($file_info['http_code'] >= 400) {
        $msg = ($lang === 'cn') 
            ? '檔案下載連結已失效或不存在（錯誤代碼: ' . $file_info['http_code'] . '），請聯繫管理員！' 
            : 'Link tải tài liệu bị lỗi hoặc không tồn tại (Mã lỗi: ' . $file_info['http_code'] . '). Vui lòng liên hệ quản trị viên!';
        wp_send_json_error(['code' => 'broken_link', 'message' => $msg]);
    }

    $safe_filename = $file_info['filename'];

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

    // [09/11/2026] - Tạo link force download bảo mật để trình duyệt tải trực tiếp về máy, không mở tab mới
    $token = wp_create_nonce('dgw_dl_' . $post_id);
    $force_download_url = add_query_arg([
        'action'  => 'dgw_force_download',
        'post_id' => $post_id,
        'token'   => $token,
        'fn'      => rawurlencode($safe_filename),
    ], admin_url('admin-ajax.php'));

    // 7. Trả kết quả JSON cho frontend
    wp_send_json_success([
        'post_id'      => $post_id,
        'post_title'   => $post_title,
        'post_source'  => $download_url,
        'download_url' => $force_download_url,
        'file_name'    => $safe_filename,
        'message'      => 'Nhận thông tin thành công!',
    ]);
}

/**
 * [09/11/2026] - Endpoint xử lý stream tải trực tiếp file về máy tính người dùng (ép buộc tải về máy, không mở tab mới hay viewer)
 */
function dgw_force_download(): void
{
    nocache_headers();

    // 1. Kiểm tra đăng nhập
    $user = is_member_logged_in();
    if (!$user) {
        wp_die(
            'Vui lòng đăng nhập trước khi tải tài liệu',
            'Chưa đăng nhập',
            ['response' => 401]
        );
    }

    // 2. Xác thực post_id và token nonce bảo mật
    $post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
    $token   = isset($_GET['token']) ? sanitize_text_field(wp_unslash($_GET['token'])) : '';

    if ($post_id <= 0 || !wp_verify_nonce($token, 'dgw_dl_' . $post_id)) {
        wp_die(
            'Liên kết tải tài liệu không hợp lệ hoặc đã hết hạn. Vui lòng thử lại!',
            'Yêu cầu không hợp lệ',
            ['response' => 403]
        );
    }

    // 3. Lấy nguồn file
    $post_source = (string) get_post_meta($post_id, '_metabox_source', true);
    $post_source = trim($post_source);
    if (empty($post_source) || !filter_var($post_source, FILTER_VALIDATE_URL)) {
        wp_die(
            'Tài liệu này không có liên kết tải hợp lệ.',
            'Không tìm thấy file',
            ['response' => 404]
        );
    }

    // 4. Xác định tên file tải về
    $custom_fn    = isset($_GET['fn']) ? rawurldecode(sanitize_text_field(wp_unslash($_GET['fn']))) : '';
    $post_title   = get_the_title($post_id);
    $download_url = dgw_convert_gdrive_url($post_source);

    if (!empty($custom_fn) && strpos($custom_fn, '.') !== false) {
        $filename = $custom_fn;
    } else {
        $file_info = dgw_detect_file_info($download_url, $post_title);
        $filename  = $file_info['filename'] ?: (sanitize_file_name($post_title) . '.pdf');
    }

    // 5. Kiểm tra nếu file nằm ở thư mục uploads cục bộ
    $upload_dir = wp_upload_dir();
    if (strpos($post_source, $upload_dir['baseurl']) !== false) {
        $local_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $post_source);
        if (file_exists($local_path) && is_readable($local_path)) {
            dgw_send_force_download_headers($filename, filesize($local_path));
            readfile($local_path);
            exit;
        }
    }

    // 6. Stream trực tiếp từ URL bên ngoài (Google Drive, CDN, Server từ xa)
    dgw_stream_remote_file($download_url, $filename);
    exit;
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
