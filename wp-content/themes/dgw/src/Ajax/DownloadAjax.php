<?php
declare(strict_types=1);

namespace DGW\Ajax;

use DGW\Core\MemberDownload;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - AJAX Handler xử lý tải tài liệu (Download Resource) và xem video YouTube
 * [25/11/2026] - Đẩy tác vụ đồng bộ Google Sheets & Lark Webhook sang WP-Cron bất đồng bộ tránh block luồng AJAX
 */
class DownloadAjax
{
    private const ASYNC_SYNC_HOOK = 'dgw_async_sync_external_notifications';

    /**
     * [25/11/2026] - Đăng ký hook WP-Cron xử lý đồng bộ Google Sheets & Lark ở nền (chạy cả khi không phải wp_doing_ajax)
     */
    public static function registerCronHooks(): void
    {
        add_action(self::ASYNC_SYNC_HOOK, [self::class, 'handleAsyncSync'], 10, 3);
    }

    /**
     * [25/11/2026] - Thực thi đồng bộ Google Sheets và gửi thông báo Lark trong tiến trình nền
     *
     * @param array<string, mixed> $sheetData
     */
    public static function handleAsyncSync(array $sheetData, string $larkMessage, string $logContext = ''): void
    {
        $syncResult = sync_to_google_sheets($sheetData);

        if ($syncResult) {
            if ($larkMessage !== '') {
                sendNotificationToLark($larkMessage);
            }
        } else {
            error_log('Async External Sync Error' . ($logContext !== '' ? " ({$logContext})" : '') . ': Đồng bộ Google Sheets thất bại.');
        }
    }

    /**
     * [25/11/2026] - Lên lịch tác vụ đồng bộ bất đồng bộ qua WP-Cron và kích hoạt spawn_cron() không chặn
     *
     * @param array<string, mixed> $sheetData
     */
    private static function dispatchAsyncSync(array $sheetData, string $larkMessage, string $logContext = ''): void
    {
        wp_schedule_single_event(time(), self::ASYNC_SYNC_HOOK, [$sheetData, $larkMessage, $logContext]);

        if (function_exists('spawn_cron')) {
            spawn_cron();
        }
    }

    /**
     * [18/11/2026] - Đăng ký AJAX actions tải file và xem video
     */
    public static function register(): void
    {
        add_action('wp_ajax_my_download_file', [self::class, 'handleDownloadFile']);
        add_action('wp_ajax_nopriv_my_download_file', [self::class, 'handleDownloadFile']);

        add_action('wp_ajax_dgw_force_download', [self::class, 'handleForceDownload']);
        add_action('wp_ajax_nopriv_dgw_force_download', [self::class, 'handleForceDownload']);

        add_action('wp_ajax_my_watch_youtube', [self::class, 'handleWatchYoutube']);
        add_action('wp_ajax_nopriv_my_watch_youtube', [self::class, 'handleWatchYoutube']);
    }

    /**
     * [18/11/2026] - Chuyển đổi linh hoạt link Google Drive sang link tải trực tiếp
     */
    public static function convertGdriveUrl(string $url): string
    {
        if (preg_match('/(?:\/file\/d\/|id=)([a-zA-Z0-9_-]{25,})/', $url, $matches)) {
            return 'https://drive.google.com/uc?export=download&id=' . $matches[1] . '&confirm=t';
        }
        return MemberDownload::convertGdriveToDownload($url);
    }

    /**
     * [18/11/2026] - Trích xuất tên file và thông tin file từ HTTP Headers hoặc URL
     * [25/11/2026] - Bật xác thực chứng chỉ SSL (sslverify => true) chống tấn công MITM
     */
    public static function detectFileInfo(string $url, string $fallbackTitle = ''): array
    {
        $response = wp_remote_head($url, [
            'timeout'     => 5,
            'redirection' => 5,
            'sslverify'   => true,
        ]);

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 405) {
            $response = wp_remote_get($url, [
                'timeout'     => 5,
                'redirection' => 5,
                'sslverify'   => true,
                'headers'     => ['Range' => 'bytes=0-0'],
            ]);
        }

        $filename    = '';
        $contentType = '';
        $filesize    = null;
        $httpCode    = 0;

        if (!is_wp_error($response)) {
            $httpCode    = (int) wp_remote_retrieve_response_code($response);
            $headers     = wp_remote_retrieve_headers($response);
            $contentType = (string) ($headers['content-type'] ?? '');
            $filesize    = isset($headers['content-length']) ? (int) $headers['content-length'] : null;

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
            if (stripos($contentType, 'spreadsheetml') !== false || stripos($contentType, 'excel') !== false) {
                $ext = 'xlsx';
            } elseif (stripos($contentType, 'wordprocessingml') !== false || stripos($contentType, 'msword') !== false) {
                $ext = 'docx';
            } elseif (stripos($contentType, 'zip') !== false) {
                $ext = 'zip';
            }
            $safeTitle = sanitize_file_name($fallbackTitle);
            $filename  = (!empty($safeTitle) ? $safeTitle : 'document') . '.' . $ext;
        }

        return [
            'filename'     => $filename,
            'filesize'     => $filesize,
            'content_type' => $contentType,
            'http_code'    => $httpCode,
        ];
    }

    /**
     * [18/11/2026] - Gửi HTTP Header ép buộc trình duyệt tải trực tiếp về máy
     */
    private static function sendForceDownloadHeaders(string $filename, ?int $filesize = null): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $safeAsciiFilename = sanitize_file_name($filename);
        if (empty($safeAsciiFilename)) {
            $safeAsciiFilename = 'document.pdf';
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $safeAsciiFilename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        if ($filesize !== null && $filesize > 0) {
            header('Content-Length: ' . (string) $filesize);
        }
    }

    /**
     * [18/11/2026] - Stream dữ liệu file từ xa trực tiếp về trình duyệt với buffer 64KB
     * [25/11/2026] - Bật xác thực SSL/TLS đầy đủ (CURLOPT_SSL_VERIFYPEER = true, VERIFYHOST = 2) chống MITM
     */
    private static function streamRemoteFile(string $url, string $filename): void
    {
        @set_time_limit(0);
        self::sendForceDownloadHeaders($filename);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $caBundle = defined('ABSPATH') && defined('WPINC') ? ABSPATH . WPINC . '/certificates/ca-bundle.crt' : '';
        if ($caBundle !== '' && file_exists($caBundle)) {
            curl_setopt($ch, CURLOPT_CAINFO, $caBundle);
        }

        curl_setopt($ch, CURLOPT_BUFFERSIZE, 65536);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, static function ($ch, $chunk) {
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
     * [18/11/2026] - Xử lý yêu cầu tải tài liệu dành cho thành viên đã đăng nhập
     * [25/11/2026] - Bổ sung check_ajax_referer('my_nonce', 'nonce') và lấy post_title trực tiếp từ server
     */
    public static function handleDownloadFile(): void
    {
        nocache_headers();
        check_ajax_referer('my_nonce', 'nonce');
        $lang = function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn';

        $user = MemberDownload::isMemberLoggedIn();
        if (!$user) {
            $msg = ($lang === 'cn') ? '請先登入會員以進行檔案下載' : 'Vui lòng đăng nhập trước khi tải tài liệu';
            wp_send_json_error(['code' => 'not_logged_in', 'message' => $msg]);
        }

        $postId = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if ($postId <= 0 || get_post_status($postId) !== 'publish') {
            $msg = ($lang === 'cn') ? '文章不存在或無效' : 'Tài liệu không tồn tại hoặc không hợp lệ';
            wp_send_json_error(['code' => 'invalid_post', 'message' => $msg], 404);
        }

        $postTitle  = trim(wp_strip_all_tags(html_entity_decode((string) get_the_title($postId), ENT_QUOTES, 'UTF-8')));
        $postSource = trim((string) get_post_meta($postId, '_metabox_source', true));

        if (empty($postSource)) {
            $msg = ($lang === 'cn') ? '此文章目前尚無可供下載的檔案' : 'Tài liệu này hiện chưa có link tải';
            wp_send_json_error(['code' => 'no_link', 'message' => $msg]);
        }

        if (!filter_var($postSource, FILTER_VALIDATE_URL)) {
            $msg = ($lang === 'cn') ? '檔案下載連結格式不正確' : 'Link tải tài liệu không đúng định dạng';
            wp_send_json_error(['code' => 'invalid_link', 'message' => $msg]);
        }

        $downloadUrl = self::convertGdriveUrl($postSource);
        $fileInfo    = self::detectFileInfo($downloadUrl, $postTitle);

        if ($fileInfo['http_code'] >= 400) {
            $msg = ($lang === 'cn')
                ? '檔案下載連結已失效或不存在（錯誤代碼: ' . $fileInfo['http_code'] . '），請聯繫管理員！'
                : 'Link tải tài liệu bị lỗi hoặc không tồn tại (Mã lỗi: ' . $fileInfo['http_code'] . '). Vui lòng liên hệ quản trị viên!';
            wp_send_json_error(['code' => 'broken_link', 'message' => $msg]);
        }

        $safeFilename = $fileInfo['filename'];

        MemberAuthAjax::getModel()->insert_download_detail([
            'user_id'  => $user->ID,
            'title'    => $postTitle,
            'resource' => $postSource,
            'kid'      => 1,
        ]);

        // [25/11/2026] - Đẩy đồng bộ Google Sheets & Lark sang tác vụ nền WP-Cron tránh block phản hồi AJAX
        $sheetData = [
            'sheet' => 'acList',
            'title' => $postTitle,
            'file'  => $postSource,
            'name'  => $user->username ?? '',
            'email' => $user->email ?? '',
            'date'  => date('d-M-y H:i:s'),
        ];
        $larkMsg = sprintf(
            '%s was downloaded by %s on %s at %s',
            $postTitle,
            $user->username ?? 'Guest',
            date('d-M-y'),
            date('H:i:s')
        );
        self::dispatchAsyncSync($sheetData, $larkMsg, 'Download Post ID: ' . $postId);

        $token            = wp_create_nonce('dgw_dl_' . $postId);
        $forceDownloadUrl = add_query_arg([
            'action'  => 'dgw_force_download',
            'post_id' => $postId,
            'token'   => $token,
            'fn'      => rawurlencode($safeFilename),
        ], admin_url('admin-ajax.php'));

        wp_send_json_success([
            'post_id'      => $postId,
            'post_title'   => $postTitle,
            'post_source'  => $downloadUrl,
            'download_url' => $forceDownloadUrl,
            'file_name'    => $safeFilename,
            'message'      => 'Nhận thông tin thành công!',
        ]);
    }

    /**
     * [18/11/2026] - Endpoint stream tải trực tiếp file về máy người dùng
     */
    public static function handleForceDownload(): void
    {
        nocache_headers();

        $user = MemberDownload::isMemberLoggedIn();
        if (!$user) {
            wp_die('Vui lòng đăng nhập trước khi tải tài liệu', 'Chưa đăng nhập', ['response' => 401]);
        }

        $postId = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
        $token  = isset($_GET['token']) ? sanitize_text_field(wp_unslash($_GET['token'])) : '';

        if ($postId <= 0 || !wp_verify_nonce($token, 'dgw_dl_' . $postId) || get_post_status($postId) !== 'publish') {
            wp_die('Liên kết tải tài liệu không hợp lệ hoặc đã hết hạn. Vui lòng thử lại!', 'Yêu cầu không hợp lệ', ['response' => 403]);
        }

        $postSource = trim((string) get_post_meta($postId, '_metabox_source', true));
        if (empty($postSource) || !filter_var($postSource, FILTER_VALIDATE_URL)) {
            wp_die('Tài liệu này không có liên kết tải hợp lệ.', 'Không tìm thấy file', ['response' => 404]);
        }

        $customFn    = isset($_GET['fn']) ? rawurldecode(sanitize_text_field(wp_unslash($_GET['fn']))) : '';
        $postTitle   = trim(wp_strip_all_tags(html_entity_decode((string) get_the_title($postId), ENT_QUOTES, 'UTF-8')));
        $downloadUrl = self::convertGdriveUrl($postSource);

        if (!empty($customFn) && strpos($customFn, '.') !== false) {
            $filename = $customFn;
        } else {
            $fileInfo = self::detectFileInfo($downloadUrl, $postTitle);
            $filename = $fileInfo['filename'] ?: (sanitize_file_name($postTitle) . '.pdf');
        }

        // [25/11/2026] - Xác thực realpath($localPath) nằm trong realpath($uploadDir['basedir']) chống Path Traversal
        $uploadDir = wp_upload_dir();
        $baseUrl   = rtrim((string) $uploadDir['baseurl'], '/') . '/';
        if (strpos($postSource, $baseUrl) === 0) {
            $relativePath  = substr($postSource, strlen($baseUrl));
            $candidatePath = (string) $uploadDir['basedir'] . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $realBaseDir   = realpath((string) $uploadDir['basedir']);
            $realLocalPath = realpath($candidatePath);

            if (
                $realBaseDir !== false
                && $realLocalPath !== false
                && str_starts_with($realLocalPath, rtrim($realBaseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)
                && is_file($realLocalPath)
                && is_readable($realLocalPath)
            ) {
                self::sendForceDownloadHeaders($filename, filesize($realLocalPath));
                readfile($realLocalPath);
                exit;
            }
        }

        self::streamRemoteFile($downloadUrl, $filename);
        exit;
    }

    /**
     * [18/11/2026] - Ghi nhận thành viên xem video YouTube, lưu DB, đồng bộ Google Sheets và gửi Lark
     * [25/11/2026] - Bổ sung check_ajax_referer('my_nonce', 'nonce') và lấy post_title trực tiếp từ server
     */
    public static function handleWatchYoutube(): void
    {
        nocache_headers();
        check_ajax_referer('my_nonce', 'nonce');

        $user = MemberDownload::isMemberLoggedIn();
        if (!$user) {
            wp_send_json_error(['code' => 'not_logged_in', 'message' => 'Vui lòng đăng nhập trước khi xem video']);
        }

        $postId = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if ($postId <= 0 || get_post_status($postId) !== 'publish') {
            wp_send_json_error(['message' => 'Bài viết không tồn tại hoặc không hợp lệ'], 404);
        }

        $postTitle   = trim(wp_strip_all_tags(html_entity_decode((string) get_the_title($postId), ENT_QUOTES, 'UTF-8')));
        $youtubeLink = (string) get_post_meta($postId, '_metabox_youtube', true);

        if (empty($youtubeLink)) {
            wp_send_json_error(['message' => 'Bài viết này hiện chưa có link video YouTube']);
        }

        MemberAuthAjax::getModel()->insert_download_detail([
            'user_id'  => $user->ID,
            'title'    => $postTitle,
            'resource' => $youtubeLink,
            'kid'      => 2,
        ]);

        // [25/11/2026] - Đẩy đồng bộ Google Sheets & Lark sang tác vụ nền WP-Cron tránh block phản hồi AJAX
        $sheetData = [
            'sheet' => 'wytList',
            'title' => $postTitle,
            'file'  => $youtubeLink,
            'name'  => $user->username ?? '',
            'email' => $user->email ?? '',
            'date'  => date('d-M-y H:i:s'),
        ];
        $larkMsg = sprintf(
            '%s was watched by %s on %s at %s',
            $postTitle,
            $user->username ?? 'Guest',
            date('d-M-y'),
            date('H:i:s')
        );
        self::dispatchAsyncSync($sheetData, $larkMsg, 'YouTube Post ID: ' . $postId);

        wp_send_json_success([
            'post_id'      => $postId,
            'post_title'   => $postTitle,
            'youtube_link' => $youtubeLink,
            'message'      => 'Ghi nhận thông tin xem video thành công!',
        ]);
    }
}
