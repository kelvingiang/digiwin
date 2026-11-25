<?php
declare(strict_types=1);

namespace DGW\Ajax;

use DGW\Core\MemberDownload;
use DGW\Models\MemberDownloadModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - AJAX Handler xử lý toàn bộ tác vụ xác thực và quản lý tài khoản Member
 */
class MemberAuthAjax
{
    /**
     * [18/11/2026] - Đăng ký toàn bộ AJAX action xác thực thành viên
     */
    public static function register(): void
    {
        $actions = [
            'download_member_login'      => 'handleLogin',
            'download_member_register'   => 'handleRegister',
            'check_member_login'         => 'handleCheckLogin',
            'member_logout'              => 'handleLogout',
            'member_change_password'     => 'handleChangePassword',
            'member_reset_password'      => 'handleResetPassword',
            'member_active_account'      => 'handleActiveAccount',
            'member_change_info'         => 'handleChangeInfo',
            'member_forgot_password'     => 'handleForgotPassword',
            'admin_update_client_password' => 'handleAdminClientPassword',
        ];

        foreach ($actions as $action => $method) {
            add_action('wp_ajax_' . $action, [self::class, $method]);
            add_action('wp_ajax_nopriv_' . $action, [self::class, $method]);
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lấy instance duy nhất của MemberDownloadModel (PSR-4)
     */
    public static function getModel(): MemberDownloadModel
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new MemberDownloadModel();
        }
        return $instance;
    }

    /**
     * [18/11/2026] - Bản đồ thông báo đa ngôn ngữ cho Member
     */
    public static function getMessages(string $lang = 'vi'): array
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
                'exist'                  => 'Email đã tồn tại',
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
                'exist'                  => '電子郵件已存在',
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
     * [18/11/2026] - 1. Đăng nhập thành viên
     */
    public static function handleLogin(): void
    {
        $msg      = self::getMessages($_POST['lang'] ?? 'vi');
        $email    = sanitize_email($_POST['email'] ?? '');
        $password = sanitize_text_field(wp_unslash($_POST['password'] ?? ''));

        if (empty($email) || empty($password)) {
            wp_send_json_error(['message' => $msg['empty']]);
        }

        $user = self::getModel()->get_user_by_email($email);
        if (!$user) {
            wp_send_json_error(['message' => $msg['not_exist']]);
        }

        if (!password_verify($password, $user->password)) {
            wp_send_json_error(['message' => $msg['password_wrong']]);
        }

        if ((int) $user->status !== 1) {
            wp_send_json_error(['message' => $msg['active_req']]);
        }

        $sessionKey = bin2hex(random_bytes(32));
        $ipAddress  = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '');

        self::getModel()->update_login($user->ID, $sessionKey, $ipAddress);

        $cookieExpire = time() + (7 * 24 * 60 * 60);
        setcookie('custom_session', $sessionKey, $cookieExpire, '/');
        $_COOKIE['custom_session'] = $sessionKey;

        wp_send_json_success([
            'message'    => $msg['success'],
            'user'       => [
                'id'       => $user->ID,
                'username' => $user->username,
                'email'    => $user->email,
            ],
            'member_url' => home_url('/member'),
        ]);
    }

    /**
     * [18/11/2026] - 2. Đăng ký thành viên mới (chỉ kiểm tra trùng email)
     */
    public static function handleRegister(): void
    {
        check_ajax_referer('my_nonce', 'nonce');
        $msg = self::getMessages($_POST['lang'] ?? 'vi');

        $turnstileResponse = sanitize_text_field($_POST['cf-turnstile-response'] ?? '');
        if (empty($turnstileResponse)) {
            wp_send_json_error(['message' => 'Vui lòng xác nhận bạn không phải là người máy (Captcha).']);
        }

        // [25/11/2026] - Đọc Cloudflare Turnstile secret key từ EnvConfig (wp-config.php / .env) thay vì hardcode
        $turnstileSecret = defined('TURNSTILE_SECRET_KEY') ? (string) TURNSTILE_SECRET_KEY : '';
        if ($turnstileSecret === '') {
            error_log('Turnstile Config Error: Missing TURNSTILE_SECRET_KEY.');
            wp_send_json_error(['message' => $msg['error_system']]);
        }

        $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret'   => $turnstileSecret,
                'response' => $turnstileResponse,
                'remoteip' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
            ],
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Lỗi kết nối máy chủ xác thực Captcha.']);
        }

        $result = json_decode(wp_remote_retrieve_body($response));
        if (empty($result->success)) {
            wp_send_json_error(['message' => 'Xác thực Captcha thất bại.']);
        }

        $registrationData = [
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

        $suspiciousPattern = '/(\bOR\b|\bAND\b|=|<|>|--|;)/i';
        if (preg_match($suspiciousPattern, $registrationData['username']) || preg_match($suspiciousPattern, $registrationData['company'])) {
            wp_send_json_error(['message' => $msg['failure']]);
        }

        if (!is_email($registrationData['email'])) {
            wp_send_json_error(['message' => $msg['email_invalid']]);
        }

        if (strlen($registrationData['password']) < 6) {
            wp_send_json_error(['message' => $msg['password_short']]);
        }

        foreach (['username', 'email', 'password', 'company', 'phone'] as $field) {
            if (empty($registrationData[$field])) {
                wp_send_json_error(['message' => $msg['empty']]);
            }
        }

        $exists = self::getModel()->check_email_username_exists($registrationData['email']);
        if ($exists > 0) {
            wp_send_json_error(['message' => $msg['exist']]);
        }

        $plainToken                      = bin2hex(random_bytes(32));
        $registrationData['active_code'] = hash('sha256', $plainToken);
        $inserted                        = self::getModel()->insert_registration_data($registrationData);

        if ($inserted) {
            $positionLabel   = isset($_POST['position_label']) ? sanitize_text_field($_POST['position_label']) : $registrationData['position'];
            $industryLabel   = isset($_POST['industry_label']) ? sanitize_text_field($_POST['industry_label']) : $registrationData['industry'];
            $departmentLabel = isset($_POST['department_label']) ? sanitize_text_field($_POST['department_label']) : $registrationData['department'];

            $dataForSheet = [
                'sheet'  => 'registerList',
                'values' => [
                    $registrationData['username'],
                    $positionLabel,
                    $registrationData['email'],
                    $registrationData['company'],
                    "'" . $registrationData['phone'],
                    "'" . $registrationData['tax'],
                    $industryLabel,
                    $departmentLabel,
                    date('d-M-y H:i:s'),
                ],
            ];

            $syncResult = sync_to_google_sheets($dataForSheet);
            if ($syncResult) {
                sendNotificationToLark('New member registration successful on ' . date('d-M-y') . ' at ' . date('H:i:s'));
            }

            $fromEmail = get_option('admin_email');
            $fromName  = get_bloginfo('name');
            $headers   = [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $fromName . ' <' . $fromEmail . '>',
                'Reply-To: marketing_vn@digiwin.com',
            ];
            $subject   = 'Activate account - ' . get_bloginfo('name');
            $resetUrl  = home_url('/activate-member/?key=' . $plainToken . '&email=' . rawurlencode($registrationData['email']));

            $message = "
                <html>
                    <body style='font-family: Arial, sans-serif; color: #333;'>
                        <h2>Chúc mừng bạn đã đăng ký thành công</h2>
                        <p>Chào " . esc_html($registrationData['username']) . ",</p>
                        <p>Chào bạn đã là thành viên của trang web công ty Digiwin.</p>
                        <p>Vui lòng nhấp vào liên kết dưới đây để kích hoạt tài khoản của mình:</p>
                        <p>
                            <a href='" . esc_url($resetUrl) . "' style='background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                                Kích hoạt tài khoản
                            </a>
                        </p>
                        <p><strong>Hoặc sao chép liên kết này vào trình duyệt:</strong><br>" . esc_url($resetUrl) . "</p>
                        <br><hr><br>
                        <h2>恭喜您註冊成功</h2>
                        <p>您好 " . esc_html($registrationData['username']) . ",</p>
                        <p>歡迎您成為 鼎新 (Digiwin) 公司網站的會員。</p>
                        <p>請點擊下方連結以啟動您的帳號：</p>
                        <p>
                            <a href='" . esc_url($resetUrl) . "' style='background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                                啟動帳號
                            </a>
                        </p>
                        <p><strong>或者將此連結複製並貼上到瀏覽器中：</strong><br>" . esc_url($resetUrl) . "</p>
                    </body>
                </html>
            ";

            $sent = wp_mail((string) $registrationData['email'], (string) $subject, (string) $message, $headers);
            if ($sent) {
                wp_send_json_success(['message' => $msg['success_register']]);
            }

            $phpmailerError = $GLOBALS['phpmailer']->ErrorInfo ?? 'Unknown error';
            error_log('Email failed: ' . $phpmailerError);
            wp_send_json_error([
                'message'      => $msg['not_send'],
                'error_detail' => $phpmailerError,
            ]);
        }

        wp_send_json_error(['message' => $msg['failure']]);
    }

    /**
     * [18/11/2026] - 3. Kiểm tra trạng thái đăng nhập thành viên
     */
    public static function handleCheckLogin(): void
    {
        nocache_headers();
        $user = MemberDownload::isMemberLoggedIn();

        wp_send_json_success([
            'logged_in'  => (bool) $user,
            'email'      => $user->email ?? '',
            'name'       => $user->username ?? '',
            'user'       => $user ? [
                'id'       => $user->ID,
                'username' => $user->username,
                'email'    => $user->email,
            ] : null,
            'member_url' => home_url('/member'),
        ]);
    }

    /**
     * [18/11/2026] - 4. Đăng xuất thành viên
     */
    public static function handleLogout(): void
    {
        nocache_headers();

        if (!empty($_COOKIE['custom_session'])) {
            $sessionKey = sanitize_text_field($_COOKIE['custom_session']);
            self::getModel()->clear_session($sessionKey);
            unset($_COOKIE['custom_session']);
        }

        setcookie('custom_session', '', time() - 3600, '/');

        wp_send_json_success([
            'logged_out' => true,
            'message'    => 'Đăng xuất thành công.',
        ]);
    }

    /**
     * [18/11/2026] - 5. Đổi mật khẩu thành viên đang đăng nhập
     */
    public static function handleChangePassword(): void
    {
        $msg = self::getMessages($_POST['lang'] ?? 'vi');

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
            wp_send_json_error(['message' => $msg['failure']], 403);
        }

        $user = MemberDownload::isMemberLoggedIn();
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
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $sessionKey   = sanitize_text_field($_COOKIE['custom_session']);
            self::getModel()->update_password($sessionKey, $passwordHash);
        }

        wp_send_json_success(['message' => $msg['success_change']]);
    }

    /**
     * [18/11/2026] - 6. Đặt lại mật khẩu có xác thực token SHA-256 (hash_equals) và thời hạn expiry
     */
    public static function handleResetPassword(): void
    {
        $msg = self::getMessages($_POST['lang'] ?? 'vi');

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

        if (!is_email($email)) {
            wp_send_json_error(['message' => $msg['email_invalid']]);
        }

        if ($password !== $confirm) {
            wp_send_json_error(['message' => $msg['password_mismatch']]);
        }

        if (strlen($password) < 6) {
            wp_send_json_error(['message' => $msg['password_short']]);
        }

        $user = self::getModel()->get_user_by_email($email);
        if (!$user) {
            wp_send_json_error(['message' => $msg['not_exist']]);
        }

        $hashedToken = hash('sha256', $key);
        $storedToken = (string) ($user->token ?? '');
        $expiry      = (int) ($user->expiry ?? 0);

        if (empty($storedToken) || $expiry <= 0 || time() > $expiry || !hash_equals($storedToken, $hashedToken)) {
            wp_send_json_error(['message' => $msg['failure']], 403);
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $updated      = self::getModel()->reset_password($email, $passwordHash, $hashedToken);

        if ($updated === false || (int) $updated < 1) {
            wp_send_json_error(['message' => $msg['error_system']]);
        }

        wp_send_json_success(['message' => $msg['success_change']]);
    }

    /**
     * [18/11/2026] - 7. Kích hoạt tài khoản thành viên
     */
    public static function handleActiveAccount(): void
    {
        $msg = self::getMessages($_POST['lang'] ?? 'vi');

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
            wp_send_json_error(['message' => $msg['failure']], 403);
        }

        $key   = sanitize_text_field($_POST['key'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');

        if (empty($email) || empty($key)) {
            wp_send_json_error(['message' => $msg['empty']]);
        }

        $user = self::getModel()->get_user_by_email($email);
        if (!$user) {
            wp_send_json_error(['message' => $msg['not_exist']]);
        }

        $hashedToken = hash('sha256', $key);
        $activated   = self::getModel()->active_member($email, $hashedToken);

        if ($activated !== false) {
            wp_send_json_success(['message' => $msg['success_activate']]);
        }

        wp_send_json_error(['message' => $msg['failure']]);
    }

    /**
     * [18/11/2026] - 8. Cập nhật thông tin thành viên
     */
    public static function handleChangeInfo(): void
    {
        $msg = self::getMessages($_POST['lang'] ?? 'vi');

        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
            wp_send_json_error(['message' => $msg['failure']], 403);
        }

        $user = MemberDownload::isMemberLoggedIn();
        if (!$user) {
            wp_send_json_error(['message' => $msg['login_req']]);
        }

        $updateData = [
            'username'   => sanitize_text_field($_POST['username'] ?? ''),
            'company'    => sanitize_text_field($_POST['company'] ?? ''),
            'phone'      => sanitize_text_field($_POST['phone'] ?? ''),
            'industry'   => sanitize_text_field($_POST['industry'] ?? ''),
            'department' => sanitize_text_field($_POST['department'] ?? ''),
            'position'   => sanitize_text_field($_POST['position'] ?? ''),
            'tax'        => sanitize_text_field($_POST['tax'] ?? ''),
        ];

        if (in_array('', $updateData, true)) {
            wp_send_json_error(['message' => $msg['empty']]);
        }

        if (!empty($_COOKIE['custom_session'])) {
            $sessionKey = sanitize_text_field($_COOKIE['custom_session']);
            self::getModel()->update_info($sessionKey, $updateData);
        }

        wp_send_json_success(['message' => $msg['success_change_info']]);
    }

    /**
     * [18/11/2026] - 9. Quên mật khẩu (gửi link reset về email)
     */
    public static function handleForgotPassword(): void
    {
        $msg = self::getMessages($_POST['lang'] ?? 'vi');
        check_ajax_referer('ajax_forgot_nonce', 'nonce');

        $email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(['message' => $msg['email_invalid']]);
        }

        $user = self::getModel()->get_user_by_email($email);
        if (!$user) {
            wp_send_json_error(['message' => $msg['not_exist']]);
        }

        $plainToken  = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $plainToken);
        $expiry      = time() + (24 * 60 * 60);

        self::getModel()->update_token($email, $hashedToken, $expiry);

        $fromEmail = get_option('admin_email');
        $fromName  = get_bloginfo('name');
        $headers   = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'Reply-To: ' . $fromEmail,
        ];

        $subject  = 'Reset Password - ' . get_bloginfo('name');
        $resetUrl = home_url('/reset-password/?key=' . $plainToken . '&email=' . rawurlencode($email));

        $message = "
            <html>
                <body style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>Yêu cầu đặt lại mật khẩu</h2>
                    <p>Chào " . esc_html($user->username) . ",</p>
                    <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                    <p>Vui lòng nhấp vào liên kết dưới đây để tạo mật khẩu mới:</p>
                    <p>
                        <a href='" . esc_url($resetUrl) . "' style='background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Đặt lại mật khẩu
                        </a>
                    </p>
                    <p><strong>Hoặc sao chép liên kết này vào trình duyệt:</strong><br>" . esc_url($resetUrl) . "</p>
                    <hr>
                    <h2>重設密碼請求</h2>
                    <p>您好 " . esc_html($user->username) . ",</p>
                    <p>我們收到了您的帳號重設密碼請求。</p>
                    <p>請點擊下方連結以設定新密碼：</p>
                    <p>
                        <a href='" . esc_url($resetUrl) . "' style='background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            重設密碼
                        </a>
                    </p>
                </body>
            </html>
        ";

        $sent = wp_mail((string) $email, (string) $subject, (string) $message, $headers);
        if ($sent) {
            wp_send_json_success(['message' => $msg['mail_sent']]);
        }

        wp_send_json_error(['message' => $msg['failure']]);
    }

    /**
     * [18/11/2026] - 10. Admin cập nhật mật khẩu thành viên từ trang quản trị
     * [2026-11-20] - @author: Kelvin - Bổ sung kiểm tra Nonce CSRF và đồng bộ quyền manage_categories
     */
    public static function handleAdminClientPassword(): void
    {
        $msg = self::getMessages($_POST['lang'] ?? 'vi');

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash((string) $_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'dgw_admin_client_pw_nonce')) {
            wp_send_json_error(['message' => 'Phiên làm việc không hợp lệ (CSRF Nonce).'], 403);
        }

        if (!current_user_can('manage_categories') && !current_user_can('edit_users')) {
            wp_send_json_error(['message' => 'Bạn không có quyền thực hiện thao tác này.'], 403);
        }

        $email    = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $name     = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $password = sanitize_text_field(wp_unslash($_POST['password'] ?? ''));

        if (!empty($email) && !empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $result       = self::getModel()->reset_password($email, $passwordHash);

            if ($result) {
                $fromEmail = get_option('admin_email');
                $fromName  = get_bloginfo('name');
                $headers   = [
                    'Content-Type: text/html; charset=UTF-8',
                    'From: ' . $fromName . ' <' . $fromEmail . '>',
                    'Reply-To: ' . $fromEmail,
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
                }

                wp_send_json_error(['message' => $msg['failure']]);
            }

            wp_send_json_error(['message' => 'Cập nhật cơ sở dữ liệu thất bại.']);
        }

        wp_send_json_error(['message' => 'Email hoặc mật khẩu không hợp lệ.']);
    }
}
