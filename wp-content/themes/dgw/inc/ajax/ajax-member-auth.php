<?php
declare(strict_types=1);

// [2026-10-05] - @author: Kelvin - Ajax Handler: Xử lý toàn bộ các tác vụ xác thực và quản lý tài khoản Member

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-10-05] - @author: Kelvin - Bản đồ thông báo đa ngôn ngữ cho Member
 */
function get_member_messages(string $lang = 'vi'): array
{
    $i18n = [
        'vi' => [
            'empty'                  => 'Vui lòng điền đầy đủ thông tin',
            'email_invalid'          => 'Email không hợp lệ',
            'not_exist'              => 'Email không tồn tại',
            'current_password_wrong' => 'Mật khẩu hiện tại không đúng',
            'password_wrong'         => 'Mật khẩu không đúng',
            'password_short'         => 'Mật khẩu phải có ít nhất 6 ký tự',
            'password_mismatch'      => 'Mật khẩu xác nhận không khớp',
            'active_req'             => 'Tài khoản chưa được kích hoạt',
            'exist'                  => 'Email hoặc tên đăng nhập đã tồn tại',
            'success'                => 'Đăng nhập thành công!',
            'success_activate'       => 'Tài khoản của bạn đã được kích hoạt!',
            'success_change'         => 'Mật khẩu đã được cập nhật',
            'success_change_info'    => 'Thông tin đã được cập nhật',
            'success_register'       => 'Đăng ký thành công, vui lòng kiểm tra email để kích hoạt tài khoản',
            'mail_sent'              => 'Hãy kiểm tra email (có hiệu lực trong 24H)',
            'not_send'               => 'Không thể gửi email, vui lòng thử lại sau',
            'failure'                => 'Xác thực bảo mật thất bại',
            'error_system'           => 'Lỗi hệ thống, vui lòng thử lại',
            'login_req'              => 'Vui lòng đăng nhập trước',
            'login'                  => 'Vui lòng đăng nhập trước',
        ],
        'cn' => [
            'empty'                  => '請填寫完整資訊',
            'email_invalid'          => '電子郵件格式錯誤',
            'not_exist'              => '帳號不存在',
            'current_password_wrong' => '當前密碼錯誤',
            'password_wrong'         => '密碼錯誤',
            'password_short'         => '密碼長度至少為 6 個字元',
            'password_mismatch'      => '兩次輸入的密碼不一致',
            'active_req'             => '帳號尚未啟動',
            'exist'                  => '電子郵件或使用者名稱已存在',
            'success'                => '登入成功！',
            'success_activate'       => '您的帳戶已成功激活！',
            'success_change'         => '密碼已成功更新',
            'success_change_info'    => '資訊已成功更新',
            'success_register'       => '註冊成功，請前往信箱點擊啟動連結',
            'mail_sent'              => '請檢查 E-mail，將在24小時後失效',
            'not_send'               => '無法發送電子郵件，請稍後重試',
            'failure'                => '安全驗證失敗',
            'error_system'           => '系統錯誤，請稍後重試',
            'login_req'              => '請先登入',
            'login'                  => '請先登入',
        ],
    ];

    return $i18n[$lang] ?? $i18n['vi'];
}

/**
 * [2026-10-05] - @author: Kelvin - Helper function lấy instance duy nhất của Model_Download_Function
 */
if (!function_exists('get_model')) {
    function get_model()
    {
        static $instance = null;
        if (null === $instance) {
            if (!class_exists('Model_Download_Function')) {
                require_once get_stylesheet_directory() . '/model/model-download-function.php';
            }
            $instance = new Model_Download_Function();
        }
        return $instance;
    }
}

/**
 * [2026-10-05] - @author: Kelvin - Kiểm tra trạng thái đăng nhập của Member qua cookie session
 */
if (!function_exists('is_member_logged_in')) {
    function is_member_logged_in()
    {
        if (empty($_COOKIE['custom_session'])) {
            return false;
        }

        $session_key = sanitize_text_field($_COOKIE['custom_session']);
        if (empty($session_key)) {
            return false;
        }

        $user = get_model()->get_user_by_session($session_key);
        return $user ?: false;
    }
}

/* =========================================================
   1. ĐĂNG NHẬP (LOGIN)
========================================================= */
add_action('wp_ajax_download_member_login',        'handle_member_login');
add_action('wp_ajax_nopriv_download_member_login', 'handle_member_login');
function handle_member_login(): void
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');

    $email    = sanitize_email($_POST['email'] ?? '');
    $password = sanitize_text_field(wp_unslash($_POST['password'] ?? ''));

    if (empty($email) || empty($password)) {
        wp_send_json_error(['message' => $msg['empty']]);
    }

    $user = get_model()->get_user_by_email($email);
    if (!$user) {
        wp_send_json_error(['message' => $msg['not_exist']]);
    }

    if (!password_verify($password, $user->password)) {
        wp_send_json_error(['message' => $msg['password_wrong']]);
    }

    if ((int) $user->status !== 1) {
        wp_send_json_error(['message' => $msg['active_req']]);
    }

    $session_key = bin2hex(random_bytes(32));
    $ip_address  = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '');

    get_model()->update_login($user->ID, $session_key, $ip_address);

    setcookie(
        'custom_session',
        $session_key,
        [
            'expires'  => time() + (7 * 24 * 60 * 60),
            'path'     => '/',
            'domain'   => '',
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );

    wp_send_json_success(['message' => $msg['success']]);
}

/* =========================================================
   2. ĐĂNG KÝ (REGISTER)
========================================================= */
add_action('wp_ajax_download_member_register',        'handle_member_register');
add_action('wp_ajax_nopriv_download_member_register', 'handle_member_register');
function handle_member_register(): void
{
    check_ajax_referer('my_nonce', 'nonce');
    $msg = get_member_messages($_POST['lang'] ?? 'vi');

    // 0. Xác thực Cloudflare Turnstile
    $turnstile_response = sanitize_text_field($_POST['cf-turnstile-response'] ?? '');
    if (empty($turnstile_response)) {
        wp_send_json_error(['message' => 'Vui lòng xác nhận bạn không phải là người máy (Captcha).']);
    }

    $secret_key = '0x4AAAAAAEe5gomLwgyfkEkqWzPX0q3BMEY';
    $verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    $response = wp_remote_post($verify_url, [
        'body' => [
            'secret'   => $secret_key,
            'response' => $turnstile_response,
            'remoteip' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
        ],
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'Lỗi kết nối máy chủ xác thực Captcha.']);
    }

    $body   = wp_remote_retrieve_body($response);
    $result = json_decode($body);

    if (empty($result->success)) {
        wp_send_json_error(['message' => 'Xác thực Captcha thất bại.']);
    }

    $registration_data = [
        'username'   => sanitize_text_field(wp_unslash($_POST['username'] ?? '')),
        'email'      => sanitize_email(wp_unslash($_POST['email'] ?? '')),
        'password'   => sanitize_text_field(wp_unslash($_POST['password'] ?? '')),
        'company'    => sanitize_text_field(wp_unslash($_POST['company'] ?? '')),
        'phone'      => sanitize_text_field(wp_unslash($_POST['phone'] ?? '')),
        'tax'        => sanitize_text_field(wp_unslash($_POST['tax'] ?? '')),
        'industry'   => sanitize_text_field(wp_unslash($_POST['industry'] ?? '')),
        'department' => sanitize_text_field(wp_unslash($_POST['department'] ?? '')),
        'position'   => sanitize_text_field(wp_unslash($_POST['position'] ?? '')),
        'language'   => sanitize_text_field(wp_unslash($_POST['lang'] ?? '')),
    ];

    $suspicious_pattern = '/(\bOR\b|\bAND\b|=|<|>|--|;)/i';
    if (preg_match($suspicious_pattern, $registration_data['username']) || preg_match($suspicious_pattern, $registration_data['company'])) {
        wp_send_json_error(['message' => $msg['failure']]);
    }

    if (!is_email($registration_data['email'])) {
        wp_send_json_error(['message' => $msg['email_invalid']]);
    }

    if (strlen($registration_data['password']) < 6) {
        wp_send_json_error(['message' => $msg['password_short']]);
    }

    foreach (['username', 'email', 'password', 'company', 'phone'] as $field) {
        if (empty($registration_data[$field])) {
            wp_send_json_error(['message' => $msg['empty']]);
        }
    }

    $exists = get_model()->check_email_username_exists(
        ['email'    => $registration_data['email']],
        ['username' => $registration_data['username']]
    );

    if ($exists > 0) {
        wp_send_json_error(['message' => $msg['exist']]);
    }

    $plain_token                      = bin2hex(random_bytes(32));
    $registration_data['active_code'] = hash('sha256', $plain_token);
    $result                           = get_model()->insert_registration_data($registration_data);

    if ($result) {
        $position_label   = isset($_POST['position_label']) ? sanitize_text_field($_POST['position_label']) : $registration_data['position'];
        $industry_label   = isset($_POST['industry_label']) ? sanitize_text_field($_POST['industry_label']) : $registration_data['industry'];
        $department_label = isset($_POST['department_label']) ? sanitize_text_field($_POST['department_label']) : $registration_data['department'];

        $data_for_sheet = [
            'sheet'  => 'registerList',
            'values' => [
                $registration_data['username'],
                $position_label,
                $registration_data['email'],
                $registration_data['company'],
                "'" . $registration_data['phone'],
                "'" . $registration_data['tax'],
                $industry_label,
                $department_label,
                date('d-M-y H:i:s'),
            ],
        ];

        // Đồng bộ qua Service Google Sheets
        $sync_result = sync_to_google_sheets($data_for_sheet);
        if ($sync_result) {
            sendNotificationToLark('New member registration successful on ' . date('d-M-y') . ' at ' . date('H:i:s'));
        }

        $from_email = get_option('admin_email');
        $from_name  = get_bloginfo('name');
        $headers    = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: marketing_vn@digiwin.com',
        ];
        $subject   = 'Activate account - ' . get_bloginfo('name');
        $reset_url = home_url('/activate-member/?key=' . $plain_token . '&email=' . rawurlencode($registration_data['email']));

        $message = "
            <html>
                <body style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>Chúc mừng bạn đã đăng ký thành công</h2>
                    <p>Chào " . esc_html($registration_data['username']) . ",</p>
                    <p>Chào bạn đã là thành viên của trang web công ty Digiwin.</p>
                    <p>Vui lòng nhấp vào liên kết dưới đây để kích hoạt tài khoản của mình:</p>
                    <p>
                        <a href='" . esc_url($reset_url) . "' style='background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Kích hoạt tài khoản
                        </a>
                    </p>
                    <p><strong>Hoặc sao chép liên kết này vào trình duyệt:</strong><br>" . esc_url($reset_url) . "</p>
                    <br><hr><br>
                    <h2>恭喜您註冊成功</h2>
                    <p>您好 " . esc_html($registration_data['username']) . ",</p>
                    <p>歡迎您成為 鼎新 (Digiwin) 公司網站的會員。</p>
                    <p>請點擊下方連結以啟動您的帳號：</p>
                    <p>
                        <a href='" . esc_url($reset_url) . "' style='background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            啟動帳號
                        </a>
                    </p>
                    <p><strong>或者將此連結複製並貼上到瀏覽器中：</strong><br>" . esc_url($reset_url) . "</p>
                </body>
            </html>
        ";

        $sent = wp_mail(
            (string) $registration_data['email'],
            (string) $subject,
            (string) $message,
            $headers
        );

        if ($sent) {
            wp_send_json_success(['message' => $msg['success_register']]);
        } else {
            $phpmailer_error = $GLOBALS['phpmailer']->ErrorInfo ?? 'Unknown error';
            error_log('Email failed: ' . $phpmailer_error);
            wp_send_json_error([
                'message'      => $msg['not_send'],
                'error_detail' => $phpmailer_error,
            ]);
        }
    } else {
        wp_send_json_error(['message' => $msg['failure']]);
    }
}

/* =========================================================
   3. KIỂM TRA TRẠNG THÁI ĐĂNG NHẬP
========================================================= */
add_action('wp_ajax_check_member_login',        'handle_check_member_login');
add_action('wp_ajax_nopriv_check_member_login', 'handle_check_member_login');
function handle_check_member_login(): void
{
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    $user = is_member_logged_in();

    wp_send_json_success([
        'logged_in' => (bool) $user,
        'email'     => $user->email ?? '',
        'name'      => $user->name ?? '',
    ]);
}

/* =========================================================
   4. ĐĂNG XUẤT (LOGOUT)
========================================================= */
add_action('wp_ajax_member_logout',        'handle_member_logout');
add_action('wp_ajax_nopriv_member_logout', 'handle_member_logout');
function handle_member_logout(): void
{
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    if (!empty($_COOKIE['custom_session'])) {
        $session_key = sanitize_text_field($_COOKIE['custom_session']);
        get_model()->clear_session($session_key);
    }

    setcookie('custom_session', '', time() - 3600, '/');

    wp_send_json_success([
        'logged_out' => true,
        'message'    => 'Đăng xuất thành công.',
    ]);
}

/* =========================================================
   5. ĐỔI MẬT KHẨU (CHANGE PASSWORD)
========================================================= */
add_action('wp_ajax_member_change_password',        'handle_change_password');
add_action('wp_ajax_nopriv_member_change_password', 'handle_change_password');
function handle_change_password(): void
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');

    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(['message' => $msg['failure']], 403);
    }

    $user = is_member_logged_in();
    if (!$user) {
        wp_send_json_error(['message' => $msg['login_req']]);
    }

    $current  = sanitize_text_field(wp_unslash($_POST['current'] ?? ''));
    $password = sanitize_text_field(wp_unslash($_POST['password'] ?? ''));
    $confirm  = sanitize_text_field(wp_unslash($_POST['confirm'] ?? ''));

    if (empty($password) || empty($confirm) || empty($current)) {
        wp_send_json_error(['message' => $msg['password_wrong']]);
    }

    if (!password_verify($current, $user->password)) {
        wp_send_json_error(['message' => $msg['current_password_wrong']]);
    }

    if ($password !== $confirm) {
        wp_send_json_error(['message' => $msg['password_mismatch']]);
    }

    if (strlen($password) < 6) {
        wp_send_json_error(['message' => $msg['password_short']]);
    }

    if (!empty($_COOKIE['custom_session'])) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $session_key   = sanitize_text_field($_COOKIE['custom_session']);
        get_model()->update_password($session_key, $password_hash);
    }

    wp_send_json_success(['message' => $msg['success_change']]);
}

/* =========================================================
   6. RESET MẬT KHẨU (RESET PASSWORD)
========================================================= */
add_action('wp_ajax_member_reset_password',        'handle_reset_password');
add_action('wp_ajax_nopriv_member_reset_password', 'handle_reset_password');
function handle_reset_password(): void
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');

    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(['message' => $msg['failure']], 403);
    }

    $password = sanitize_text_field(wp_unslash($_POST['password'] ?? ''));
    $confirm  = sanitize_text_field(wp_unslash($_POST['confirm'] ?? ''));
    $key      = sanitize_text_field(wp_unslash($_POST['key'] ?? ''));
    $email    = sanitize_email(wp_unslash($_POST['email'] ?? ''));

    if (empty($password) || empty($confirm) || empty($key) || empty($email)) {
        wp_send_json_error(['message' => $msg['empty']]);
    }

    if ($password !== $confirm) {
        wp_send_json_error(['message' => $msg['password_mismatch']]);
    }

    if (strlen($password) < 6) {
        wp_send_json_error(['message' => $msg['password_short']]);
    }

    if (!empty($key)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        get_model()->reset_password($email, $password_hash);
    }

    wp_send_json_success(['message' => $msg['success_change']]);
}

/* =========================================================
   7. KÍCH HOẠT TÀI KHOẢN (ACTIVATE ACCOUNT)
========================================================= */
add_action('wp_ajax_member_active_account',        'handle_active_account');
add_action('wp_ajax_nopriv_member_active_account', 'handle_active_account');
function handle_active_account(): void
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');

    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(['message' => $msg['failure']], 403);
    }

    $key   = sanitize_text_field($_POST['key'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');

    if (empty($email) || empty($key)) {
        wp_send_json_error(['message' => $msg['empty']]);
    }

    $user = get_model()->get_user_by_email($email);
    if (!$user) {
        wp_send_json_error(['message' => $msg['not_exist']]);
    }

    $hashed_token = hash('sha256', $key);
    $activated    = get_model()->active_member($email, $hashed_token);

    if ($activated !== false) {
        wp_send_json_success(['message' => $msg['success_activate']]);
    } else {
        wp_send_json_error(['message' => $msg['failure']]);
    }

    wp_die();
}

/* =========================================================
   8. CẬP NHẬT THÔNG TIN THÀNH VIÊN (UPDATE PROFILE)
========================================================= */
add_action('wp_ajax_member_change_info',        'handle_change_info');
add_action('wp_ajax_nopriv_member_change_info', 'handle_change_info');
function handle_change_info(): void
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');

    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(['message' => $msg['failure']], 403);
    }

    $user = is_member_logged_in();
    if (!$user) {
        wp_send_json_error(['message' => $msg['login_req']]);
    }

    $update_data = [
        'username'   => sanitize_text_field($_POST['username'] ?? ''),
        'company'    => sanitize_text_field($_POST['company'] ?? ''),
        'phone'      => sanitize_text_field($_POST['phone'] ?? ''),
        'industry'   => sanitize_text_field($_POST['industry'] ?? ''),
        'department' => sanitize_text_field($_POST['department'] ?? ''),
        'position'   => sanitize_text_field($_POST['position'] ?? ''),
        'tax'        => sanitize_text_field($_POST['tax'] ?? ''),
    ];

    if (in_array('', $update_data, true)) {
        wp_send_json_error(['message' => $msg['empty']]);
    }

    if (!empty($_COOKIE['custom_session'])) {
        $session_key = sanitize_text_field($_COOKIE['custom_session']);
        get_model()->update_info($session_key, $update_data);
    }

    wp_send_json_success(['message' => $msg['success_change_info']]);
}

/* =========================================================
   9. QUÊN MẬT KHẨU (FORGOT PASSWORD)
========================================================= */
add_action('wp_ajax_member_forgot_password',        'handle_forgot_password');
add_action('wp_ajax_nopriv_member_forgot_password', 'handle_forgot_password');
function handle_forgot_password(): void
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');
    check_ajax_referer('ajax_forgot_nonce', 'nonce');

    $email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    if (empty($email) || !is_email($email)) {
        wp_send_json_error(['message' => $msg['email_invalid']]);
    }

    $user = get_model()->get_user_by_email($email);
    if (!$user) {
        wp_send_json_error(['message' => $msg['not_exist']]);
    }

    $plain_token  = bin2hex(random_bytes(32));
    $hashed_token = hash('sha256', $plain_token);
    $expiry       = time() + (24 * 60 * 60);

    get_model()->update_token($email, $hashed_token, $expiry);

    $from_email = get_option('admin_email');
    $from_name  = get_bloginfo('name');
    $headers    = [
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . $from_email . '>',
        'Reply-To: ' . $from_email,
    ];

    $subject   = 'Reset Password - ' . get_bloginfo('name');
    $reset_url = home_url('/reset-password/?key=' . $plain_token . '&email=' . rawurlencode($email));

    $message = "
        <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <h2>Yêu cầu đặt lại mật khẩu</h2>
                <p>Chào " . esc_html($user->username) . ",</p>
                <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                <p>Vui lòng nhấp vào liên kết dưới đây để tạo mật khẩu mới:</p>
                <p>
                    <a href='" . esc_url($reset_url) . "' style='background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Đặt lại mật khẩu
                    </a>
                </p>
                <p><strong>Hoặc sao chép liên kết này vào trình duyệt:</strong><br>" . esc_url($reset_url) . "</p>
                <hr>
                <h2>重設密碼請求</h2>
                <p>您好 " . esc_html($user->username) . ",</p>
                <p>我們收到了您的帳號重設密碼請求。</p>
                <p>請點擊下方連結以設定新密碼：</p>
                <p>
                    <a href='" . esc_url($reset_url) . "' style='background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        重設密碼
                    </a>
                </p>
            </body>
        </html>
    ";

    $sent = wp_mail((string) $email, (string) $subject, (string) $message, $headers);

    if ($sent) {
        wp_send_json_success(['message' => $msg['mail_sent']]);
    } else {
        wp_send_json_error(['message' => $msg['failure']]);
    }

    wp_die();
}

/* =========================================================
   10. ADMIN CẬP NHẬT MẬT KHẨU MEMBER TỪ TRANG QUẢN TRỊ
========================================================= */
add_action('wp_ajax_admin_update_client_password',        'ajax_admin_handle_client_password');
add_action('wp_ajax_nopriv_admin_update_client_password', 'ajax_admin_handle_client_password');
function ajax_admin_handle_client_password(): void
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');

    if (!current_user_can('edit_users')) {
        wp_send_json_error(['message' => 'Bạn không có quyền thực hiện thao tác này.']);
    }

    $email    = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $name     = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $password = sanitize_text_field(wp_unslash($_POST['password'] ?? ''));

    if (!empty($email) && !empty($password)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $result        = get_model()->reset_password($email, $password_hash);

        if ($result) {
            $from_email = get_option('admin_email');
            $from_name  = get_bloginfo('name');
            $headers    = [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $from_name . ' <' . $from_email . '>',
                'Reply-To: ' . $from_email,
            ];
            $subject = 'New Password - ' . get_bloginfo('name');
            $message = "
                <html>
                    <body style='font-family: Arial, sans-serif; color: #333;'>
                        <h2>Đặt lại mật khẩu</h2>
                        <p>Chào " . esc_html($name) . ",</p>
                        <p>Chúng tôi đã đặt lại mật khẩu cho tài khoản của bạn.</p>
                        <p>Mật khẩu mới: " . esc_html($password) . "</p>
                    </body>
                </html>
            ";

            $sent = wp_mail((string) $email, (string) $subject, (string) $message, $headers);
            if ($sent) {
                wp_send_json_success(['message' => 'Cập nhật mật khẩu thành công và đã gửi email cho khách hàng!']);
            } else {
                wp_send_json_error(['message' => $msg['failure']]);
            }
        } else {
            wp_send_json_error(['message' => 'Cập nhật cơ sở dữ liệu thất bại.']);
        }
    } else {
        wp_send_json_error(['message' => 'Email hoặc mật khẩu không hợp lệ.']);
    }

    wp_die();
}
