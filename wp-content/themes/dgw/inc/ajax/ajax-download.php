<?php
function get_member_messages($lang = 'vi')
{
    $i18n = [
        'vi' => [
            'empty'           => "Vui lòng điền đầy đủ thông tin",
            'email_invalid'   => "Email không hợp lệ",
            'not_exist'       => "Email không tồn tại",
            'current_password_wrong' => "Mật khẩu hiện tại không đúng",
            'password_wrong'  => "Mật khẩu không đúng",
            'password_short'  => "Mật khẩu phải có ít nhất 6 ký tự",
            'password_mismatch' => "Mật khẩu xác nhận không khớp",
            'active_req'      => "Tài khoản chưa được kích hoạt",
            'exist'           => "Email hoặc tên đăng nhập đã tồn tại",
            'success'         => "Đăng nhập thành công!",
            'success_activate' => "Tài khoản của bạn đã được kích hoạt!",
            'success_change'  => "Mật khẩu đã được cập nhật",
            'success_change_info'  => "Thông tin đã được cập nhật",
            'success_register' => "Đăng ký thành công, vui lòng kiểm tra email để kích hoạt tài khoản",
            'mail_sent'       => "Hãy kiểm tra email (có hiệu lực trong 24H)",
            'not_send'        => "Không thể gửi email, vui lòng thử lại sau",
            'failure'         => "Xác thực bảo mật thất bại",
            'error_system'    => "Lỗi hệ thống, vui lòng thử lại",
            'login_req'       => "Vui lòng đăng nhập trước"
        ],
        'cn' => [
            'empty'           => "請填寫完整資訊",
            'email_invalid'   => "電子郵件格式錯誤",
            'not_exist'       => "帳號不存在",
            'current_password_wrong' => "當前密碼錯誤",
            'password_wrong'  => "密碼錯誤",
            'password_short'  => "密碼長度至少為 6 個字元",
            'password_mismatch' => "兩次輸入的密碼不一致",
            'active_req'      => "帳號尚未啟動",
            'exist'           => "電子郵件或使用者名稱已存在",
            'success'         => "登入成功！",
            'success_activate' => "您的帳戶已成功激活！",
            'success_change'  => "密碼已成功更新",
            'success_change_info'  => "資訊已成功更新",
            'success_register' => "註冊成功，請前往信箱點擊啟動連結",
            'mail_sent'       => "請檢查 E-mail，將在24小時後失效",
            'not_send'        => "無法發送電子郵件，請稍後重試",
            'failure'         => "安全驗證失敗",
            'error_system'    => "系統錯誤，請稍後重試",
            'login_req'       => "請先登入"
        ]
    ];
    return $i18n[$lang] ?? $i18n['vi'];
}

/* =========================================================
phần download file
========================================================= */
add_action('wp_ajax_my_download_file', 'my_download_file');
add_action('wp_ajax_nopriv_my_download_file', 'my_download_file');
function my_download_file()
{
    // 1. Kiểm tra nonce bảo mật
    if (!check_ajax_referer('my_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Không hợp lệ']);
        return;
    }

    // 1. Kiểm tra đăng nhập
    $user = is_member_logged_in();
    if (!$user) {
        wp_send_json_error(['code' => 'not_logged_in']);
        return;
    }

    // 2. Nhận dữ liệu từ JS gửi lên
    $post_id    = isset($_POST['post_id'])    ? intval($_POST['post_id'])              : 0;
    $post_title = isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : '';
    $post_source =  get_post_meta($post_id, '_metabox_source', true);

    if (empty($post_source)) {
        wp_send_json_error(['message' => 'Không có link file']);
        return;
    }
    // Convert link Google Drive sang link download
    $download_url = convert_gdrive_to_download($post_source);

    // Lưu vào database nội bộ
    get_model()->insert_download_detail([
        'user_id' => $user->ID,
        'title' => $post_title,
        'resource' => $post_source
    ]);

    // ==========================================
    // THÊM ĐOẠN MÃ ĐỒNG BỘ GOOGLE SHEETS VÀO ĐÂY
    // ==========================================

    // [24/06/2026] - Chỉ định rõ tên sheet (sheet1) cần ghi dữ liệu vào Google Sheets 
    // 1. Chuẩn bị dữ liệu theo đúng định dạng hàm sync_to_google_sheets yêu cầu
    $data_for_sheet = [
        "sheet"    => "acList",          // Tên sheet cần ghi dữ liệu vào (viết hoa chữ S)
        "title"    => $post_title,       // Cột A: Tên tài liệu
        "file"     => $post_source,      // Cột B: Link tài liệu gốc
        "name"     => $user->username,   // Cột C: Tên người dùng
        "email"    => $user->email,      // Cột D: Email người dùng
        "date"     => Date('d-M-y H:i:s') // Cột E: Thời gian
    ];

    // 2. Gọi hàm đồng bộ (nhớ bỏ tất cả các lệnh echo trong hàm sync_to_google_sheets nếu có)
    $sync_result = sync_to_google_sheets($data_for_sheet);

    // 3. (Tùy chọn) Log lại nếu đồng bộ thất bại, KHÔNG echo ra ngoài
    if ($sync_result) {
        sendNotificationToLark($post_title . " was downloaded by " . $user->username . ' on ' . Date('d-M-y') . ' at ' . Date('H:i:s'));
    } else {
        error_log('AJAX Success: Đồng bộ Google Sheets thành công cho bài viết ID: ' . $post_id);
    }

    // 3. Trả kết quả về JS
    wp_send_json_success([
        'post_id'    => $post_id,
        'post_title' => $post_title,
        'post_source' => $download_url,
        'message'    => 'Nhận thông tin thành công!'
    ]);
}

// Hàm gửi thông báo đến Lark (Feishu) =============================================
function sendNotificationToLark($message)
{
    $webhookUrl = "https://open.larksuite.com/open-apis/bot/v2/hook/2a2da959-0b1e-4b3c-ab3f-85703d65e3b0";

    $data = [
        "msg_type" => "text",
        "content" => [
            "text" => $message
        ]
    ];

    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

/* =========================================================
phần login 
========================================================= */
add_action('wp_ajax_download_member_login',        'handle_member_login');
add_action('wp_ajax_nopriv_download_member_login', 'handle_member_login');
function handle_member_login()
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');

    $email    = sanitize_email($_POST['email']);
    $password = sanitize_text_field(wp_unslash($_POST['password']));

    if (empty($email) || empty($password)) {
        wp_send_json_error(['message' => $msg['empty']]);
        return;
    }

    $user = get_model()->get_user_by_email($email);

    if (!$user) {
        wp_send_json_error(['message' => $msg['not_exist']]);
        return;
    }

    if (!password_verify($password, $user->password)) {
        wp_send_json_error(['message' => $msg['password_wrong']]);
        return;
    }

    if ($user->status != 1) {
        wp_send_json_error(['message' => $msg['active_req']]);
        return;
    }

    $session_key = bin2hex(random_bytes(32));
    $ip_address  = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

    get_model()->update_login($user->ID, $session_key, $ip_address);

    // ✅ JUST THIS - Cực đơn giản
    //$is_localhost = strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0') !== false;

    // setcookie(
    //     'custom_session',
    //     $session_key,
    //     time() + (7 * 24 * 60 * 60),
    //     COOKIEPATH,
    //     COOKIE_DOMAIN,
    //     false,  // Secure (tùy chọn)
    //     true    // HttpOnly (bắt buộc nên có) ← chống XSS
    // );

    // Tạm thời bỏ domain để test
    setcookie(
        'custom_session',
        $session_key,
        [
            'expires' => time() + (7 * 24 * 60 * 60),
            'path' => '/',
            'domain' => '',  // ← Bỏ domain test
            'secure' => false,  // ← Local dùng false
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );



    wp_send_json_success(['message' => $msg['success']]);
}
// function handle_member_login()
// {
//     $msg = get_member_messages($_POST['lang'] ?? 'vi');
//     // ===== PHẦN BỊ THIẾU — xác thực email + password =====
//     $email    = sanitize_email($_POST['email']);
//     $password = sanitize_text_field(wp_unslash($_POST['password']));

//     // Kiểm tra rỗng
//     if (empty($email) || empty($password)) {
//         wp_send_json_error(['message' => $msg['empty']]);
//         return;
//     }

//     // Tìm user theo email trong DB
//     $user = get_model()->get_user_by_email($email);

//     // Không tìm thấy user
//     if (!$user) {
//         wp_send_json_error(['message' => $msg['not_exist']]);
//         return;
//     }

//     // Kiểm tra password có khớp không
//     if (!password_verify($password, $user->password)) {
//         wp_send_json_error(['message' => $msg['password_wrong']]);
//         return;
//     }

//     // Kiểm tra account có đã kích hoạt chưa (status = 1)
//     if ($user->status != 1) {
//         wp_send_json_error(['message' => $msg['active_req']]);
//         return;
//     }
//     // ===== KẾT THÚC PHẦN XÁC THỰC =====

//     // Sinh session_key mới mỗi lần đăng nhập
//     $session_key = bin2hex(random_bytes(32));

//     $ip_address   = '';
//     if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
//         $ip_address = $_SERVER['HTTP_CLIENT_IP'];
//     } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//         $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
//     } else {
//         $ip_address = $_SERVER['REMOTE_ADDR'];
//     }

//     // Cập nhật vào DB
//     get_model()->update_login($user->ID, $session_key, $ip_address);

//     // Lưu vào cookie trình duyệt
//     // ✅ setcookie tương thích mọi phiên bản PHP
//     setcookie(
//         'custom_session',
//         $session_key,
//         time() + (7 * 24 * 60 * 60),
//         '/',
//         '',
//         true,
//         true
//     );

//     wp_send_json_success(['message' => $msg['success']]);
// }

/* =========================================================
PHẦN ĐĂNG KÝ  register 
========================================================= */
add_action('wp_ajax_download_member_register',        'handle_member_register');
add_action('wp_ajax_nopriv_download_member_register', 'handle_member_register');
function handle_member_register()
{
    check_ajax_referer('my_nonce', 'nonce');
    $msg = get_member_messages($_POST['lang'] ?? 'vi');
    
    // 0. Xác thực Cloudflare Turnstile
    $turnstile_response = isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '';
    if (empty($turnstile_response)) {
        wp_send_json_error(['message' => 'Vui lòng xác nhận bạn không phải là người máy (Captcha).']);
        return;
    }

    // TODO: Thay thế Secret Key thật khi đưa lên Production
    $secret_key = '0x4AAAAAAEe5gomLwgyfkEkqWzPX0q3BMEY'; // Real secret key
    $verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    
    $response = wp_remote_post($verify_url, [
        'body' => [
            'secret'   => $secret_key,
            'response' => $turnstile_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ]
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'Lỗi kết nối máy chủ xác thực Captcha.']);
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body);

    if (!$result->success) {
        wp_send_json_error(['message' => 'Xác thực Captcha thất bại.']);
        return;
    }

    //--------------------------------------------------------------------
    // 1. 接收並清洗數據 (Thêm wp_unslash để tránh bị lưu dấu \ vào database do wp_magic_quotes)
    $registration_data = [
        'username'   => sanitize_text_field(wp_unslash($_POST['username'] ?? '')),
        'email'      => sanitize_email(wp_unslash($_POST['email'] ?? '')),
        'password'   => sanitize_text_field(wp_unslash($_POST['password'] ?? '')), // 注意：這裡先拿原始值進行長度檢查，Model 層會加密
        'company'    => sanitize_text_field(wp_unslash($_POST['company'] ?? '')),
        'phone'      => sanitize_text_field(wp_unslash($_POST['phone'] ?? '')),
        'tax'        => sanitize_text_field(wp_unslash($_POST['tax'] ?? '')),
        'industry'   => sanitize_text_field(wp_unslash($_POST['industry'] ?? '')),
        'department' => sanitize_text_field(wp_unslash($_POST['department'] ?? '')),
        'position'   => sanitize_text_field(wp_unslash($_POST['position'] ?? '')),
        'language'   => sanitize_text_field(wp_unslash($_POST['lang'] ?? '')),
    ];

    // Kiểm tra và chặn các payload nghi ngờ là SQL Injection / XSS
    $suspicious_pattern = '/(\bOR\b|\bAND\b|=|<|>|--|;)/i';
    if (preg_match($suspicious_pattern, $registration_data['username']) || preg_match($suspicious_pattern, $registration_data['company'])) {
        wp_send_json_error(['message' => $msg['failure']]);
        return;
    }

    // 檢查必填項 (修正了你原代碼中的括號語法錯誤)
    if (!is_email($registration_data['email'])) {
        wp_send_json_error(['message' => $msg['email_invalid']]);
        return;
    }

    if (strlen($registration_data['password']) < 6) {
        wp_send_json_error(['message' => $msg['password_short']]);
        return;
    }

    foreach (['username', 'email', 'password', 'company', 'phone'] as $field) {
        if (empty($registration_data[$field])) {
            wp_send_json_error(['message' => $msg['empty']]);
            return;
        }
    }

    // 3. 檢查唯一性 (Email & Username)
    $exists = get_model()->check_email_username_exists(
        ['email' => $registration_data['email']],
        ['username' => $registration_data['username']]
    );

    if ($exists > 0) {
        wp_send_json_error(['message' => $msg['exist']]);
        return;
    }

    // --- BẮT ĐẦU TẠO TOKEN ---
    // 1. Tạo một token ngẫu nhiên cực dài
    $plain_token = bin2hex(random_bytes(32));
    // 2. Hash token trước khi lưu vào DB (Bảo mật PHP 8.2)
    $registration_data['active_code'] = hash('sha256', $plain_token);
    $result = get_model()->insert_registration_data($registration_data);
    if ($result) {
        // [24/06/2026] - Ghi thông tin đăng ký vào Google Sheets (registerList) sau khi lưu DB thành công
        // ---20-07-2026  BẮT ĐẦU CHỈNH SỬA: Lấy label thay vì value ---
        $position_label   = isset($_POST['position_label']) ? sanitize_text_field($_POST['position_label']) : $registration_data['position'];
        $industry_label   = isset($_POST['industry_label']) ? sanitize_text_field($_POST['industry_label']) : $registration_data['industry'];
        $department_label = isset($_POST['department_label']) ? sanitize_text_field($_POST['department_label']) : $registration_data['department'];
        // --- KẾT THÚC CHỈNH SỬA ---

        $data_for_sheet = [
            'sheet'  => 'registerList', // Tên sheet cần ghi dữ liệu vào (viết hoa chữ S)
            'values' => [
                $registration_data['username'],
                $position_label, // <-- Dùng label
                $registration_data['email'],
                $registration_data['company'],
                "'" . $registration_data['phone'], // Thêm dấu nháy đơn để Google Sheets giữ nguyên dạng chuỗi (không mất số 0)
                "'" . $registration_data['tax'],   // Thêm dấu nháy đơn để Google Sheets giữ nguyên dạng chuỗi
                $industry_label, // <-- Dùng label
                $department_label, // <-- Dùng label
                date('d-M-y H:i:s')
            ]
        ];
        // sync_to_google_sheets($data_for_sheet);
        // 2. Gọi hàm đồng bộ (nhớ bỏ tất cả các lệnh echo trong hàm sync_to_google_sheets nếu có)
        $sync_result = sync_to_google_sheets($data_for_sheet);

        // 3. (Tùy chọn) Log lại nếu đồng bộ thất bại, KHÔNG echo ra ngoài
        if ($sync_result) {
            sendNotificationToLark("New member registration successful  " . ' on ' . Date('d-M-y') . ' at ' . Date('H:i:s'));
        } else {
            error_log('AJAX Success: đăng ký Google Sheets thành công cho bài viết ID:');
        }
        // Tạo link reset mật khẩu chuyên nghiệp
        // ==================== 修復 1: 設置正確的郵件頭部 ====================
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // 修復 2: 添加明確的FROM地址 (使用WordPress網站郵箱)
        $from_email = get_option('admin_email');
        $from_name = get_bloginfo('name');
        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
        $headers[] = 'Reply-To: marketing_vn@digiwin.com';
        $subject = "Activate account - " . get_bloginfo('name');

        // 修復 4: 使用 HTML 格式 (更容易被郵件服務器接受)
        $reset_url = home_url('/activate-member/?key=' . $plain_token . '&email=' . rawurlencode($registration_data['email']));

        $message = "
                    <html>
                        <body style='font-family: Arial, sans-serif; color: #333;'>
                            <h2>Chúc mừng bạn đã đăng ký thành công</h2>
                            <p>Chào " . esc_html($registration_data['username']) . ",</p>
                            <p>chào bạn đã là thành viên của trang web công ty Digiwin.</p>
                            <p>Vui lòng nhấp vào liên kết dưới đây để kích hoạt tài khoản của mình:</p>
                            <p>
                                <a href='" . esc_url($reset_url) . "' style='background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                                    Kích hoạt tài khoản
                                </a>
                            </p>
                            <p><strong>Hoặc sao chép liên kết này vào trình duyệt:</strong><br>
                            " . esc_url($reset_url) . "</p>
                            <p><small>" . get_bloginfo('name') . "</small></p>
                        <br>
                        <hr>
                        <br>
                            <h2>恭喜您註冊成功</h2>
                            <p>您好 " . esc_html($registration_data['username']) . ",</p>
                            <p>歡迎您成為 鼎新 (Digiwin) 公司網站的會員。</p>
                            <p>請點擊下方連結以啟動您的帳號：</p>
                            <p>
                                <a href='" . esc_url($reset_url) . "' style='background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                                    啟動帳號
                                </a>
                            </p>
                            <p><strong>或者將此連結複製並貼上到瀏覽器中：</strong><br>
                            " . esc_url($reset_url) . "</p>
                            <p><small>" . get_bloginfo('name') . "</small></p>
                        </body>
                    </html>
                 ";

        // ==================== 修復 5: 添加詳細的調試日誌 ====================
        $log_message = "[" . date('Y-m-d H:i:s') . "] Register Attempt\n";
        $log_message .= "Email: " . $registration_data['email'] . "\n";
        $log_message .= "Username: " . (isset($registration_data['username']) ? $registration_data['username'] : 'N/A') . "\n";
        $log_message .= "Activation Link: " . $reset_url . "\n";
        $log_message .= "From: " . $from_email . "\n";
        $log_message .= "Headers: " . print_r($headers, true) . "\n";

        // 使用WordPress的debug.log或自訂日誌檔案
        error_log($log_message, 3, WP_CONTENT_DIR . '/register-mail.log');

        // ==================== 修復 6: 發送郵件並捕獲詳細錯誤 ====================
        $sent = wp_mail(
            (string)$registration_data['email'],           // TO
            (string)$subject,         // SUBJECT
            (string)$message,         // MESSAGE
            $headers                  // HEADERS (修復後)
        );
        if ($sent) {
            wp_send_json_success([
                'message' => $msg['success_register']
            ]);
        } else {
            $phpmailer_error = $GLOBALS['phpmailer']->ErrorInfo ?? 'Unknown error';
            error_log("Email failed: " . $phpmailer_error);
            wp_send_json_error([
                'message' => $msg['not_send'],
                'error_detail' => $phpmailer_error
            ]);
        }
    } else {
        wp_send_json_error(['message' => $msg['failure']]);
    }
}
/* =========================================================
// AJAX endpoint（登入 / 未登入 都可呼叫）
========================================================= */
add_action('wp_ajax_check_member_login',        'handle_check_member_login');
add_action('wp_ajax_nopriv_check_member_login', 'handle_check_member_login');
function handle_check_member_login()
{
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    $user = is_member_logged_in();

    wp_send_json_success([
        'logged_in' => (bool) $user,
        'email'     => $user->email ?? '',
        'name'      => $user->name  ?? '',
    ]);
}

/* =========================================================
// AJAX 登出
========================================================= */
add_action('wp_ajax_member_logout',        'handle_member_logout');
add_action('wp_ajax_nopriv_member_logout', 'handle_member_logout');
function handle_member_logout()
{
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    // 只清空 session_key 欄位，不刪整行
    if (!empty($_COOKIE['custom_session'])) {
        $session_key = sanitize_text_field($_COOKIE['custom_session']);

        get_model()->clear_session($session_key);
    }
    // 清除 cookie
    setcookie('custom_session', '', time() - 3600, '/');

    wp_send_json_success([
        'logged_out' => true,
        'message'    => 'Đăng xuất thành công.'
    ]);
}

/* =========================================================
UPDATA PASWORD 
========================================================= */
add_action('wp_ajax_member_change_password',        'handle_change_password');
add_action('wp_ajax_nopriv_member_change_password', 'handle_change_password');
function handle_change_password()
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');

    // 1. 驗證 Nonce (請確保你 wp_localize_script 也是用 'member_auth_nonce')
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(array('message' => $msg['failure']), 403);
    }

    // 2. 確認使用者有登入
    $user = is_member_logged_in();
    // Đã xóa dòng echo print_r để trả về chuẩn JSON
    if (!$user) {
        wp_send_json_error(array('message' => $msg['login']));
    }

    // 3. 接收前端傳來的密碼
    $current = sanitize_text_field(wp_unslash($_POST['current'])) ?? '';
    $password = sanitize_text_field(wp_unslash($_POST['password'])) ?? '';
    $confirm = sanitize_text_field(wp_unslash($_POST['confirm'])) ?? '';

    if (empty($password) || empty($confirm) || empty($current)) {
        wp_send_json_error(array('message' => $msg['password_wrong']));
    }

    // 4. Kiểm tra mật khẩu hiện tại
    // Tối ưu: Dùng trực tiếp $user->password đã lấy ở bước 2
    if (!password_verify($current, $user->password)) {
        wp_send_json_error(array('message' => $msg['current_password_wrong'])); // Bạn có thể thêm message 'current_password_wrong' cho rõ ràng hơn
    }

    if ($password !== $confirm) {
        wp_send_json_error(array('message' => $msg['password_mismatch']));
    }

    if (strlen($password) < 6) {
        wp_send_json_error(['message' => $msg['password_short']]);
    }



    // 5. Cập nhật mật khẩu mới
    if (!empty($_COOKIE['custom_session'])) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $session_key = sanitize_text_field($_COOKIE['custom_session']);

        get_model()->update_password($session_key, $password_hash);
    }

    // 6. Trả về kết quả thành công chuẩn JSON
    wp_send_json_success(array(
        'message' => $msg['success_change']
    ));
}
// function handle_change_password()
// {

//     $msg = get_member_messages($_POST['lang'] ?? 'vi');
//     // 1. 驗證 Nonce (請確保你 wp_localize_script 也是用 'member_auth_nonce')
//     if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
//         wp_send_json_error(array('message' => $msg['failure']), 403);
//     }

//     // 2. 確認使用者有登入
//     $user = is_member_logged_in();
//     echo "User: " . print_r($user, true); // DEBUG: 查看 $user 內容
//     if (!$user) {
//         wp_send_json_error(array('message' => $msg['login']));
//     }

//     // 3. 接收前端傳來的密碼
//     $current = sanitize_text_field(wp_unslash($_POST['current'])) ?? '';
//     $password = sanitize_text_field(wp_unslash($_POST['password'])) ?? '';
//     $confirm = sanitize_text_field(wp_unslash($_POST['confirm'])) ?? '';

//     if (empty($password) || empty($confirm) || empty($current)) {
//         wp_send_json_error(array('message' => $msg['password_wrong']));
//     }

//     if ($password !== $confirm) {
//         wp_send_json_error(array('message' => $msg['password_mismatch']));
//     }

//     if (strlen($password) < 6) {
//         wp_send_json_error(['message' => $msg['password_short']]);
//     }

//     if(!empty($current) && !empty($password) && !empty($confirm) && !empty($_COOKIE['custom_session'])) {
//         // Kiểm tra mật khẩu hiện tại có đúng không
//         $session_key = sanitize_text_field($_COOKIE['custom_session']);
//         $user_data = get_model()->get_user_by_session($session_key);

//         if (!$user_data || !password_verify($current, $user_data->password)) {
//             wp_send_json_error(array('message' => $msg['password_wrong']));
//         }
//     }

//     if (!empty($_COOKIE['custom_session'])) {
//         $password_hash = password_hash($password, PASSWORD_BCRYPT);
//         $session_key = sanitize_text_field($_COOKIE['custom_session']);

//         get_model()->update_password($session_key, $password_hash);
//     }

//     wp_send_json_success(array(
//         'message' => $msg['success_change']
//     ));
// }


/* =========================================================
RESET PASWORD 
========================================================= */
add_action('wp_ajax_member_reset_password',        'handle_reset_password');
add_action('wp_ajax_nopriv_member_reset_password', 'handle_reset_password');
function handle_reset_password()
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');
    // 1. 驗證 Nonce (請確保你 wp_localize_script 也是用 'member_auth_nonce')
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(array('message' => $msg['failure']), 403);
    }
    // 3. 接收前端傳來的密碼
    $password = sanitize_text_field(wp_unslash($_POST['password'])) ?? '';
    $confirm = sanitize_text_field(wp_unslash($_POST['confirm'])) ?? '';
    $key = sanitize_text_field(wp_unslash($_POST['key'])) ?? '';
    $email = sanitize_text_field(wp_unslash($_POST['email'])) ?? '';

    if (empty($password) || empty($confirm) || empty($key) || empty($email)) {
        wp_send_json_error(array('message' => $msg['empty']));
    }

    if ($password !== $confirm) {
        wp_send_json_error(array('message' => $msg['password_mismatch']));
    }

    if (strlen($password) < 6) {
        wp_send_json_error(['message' => $msg['password_short']]);
    }

    if (!empty($key)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        get_model()->reset_password($email, $password_hash);
    }

    wp_send_json_success(array(
        'message' => $msg['success_change']
    ));
}


/* =========================================================
activate account 
========================================================= */
add_action('wp_ajax_member_active_account',        'handle_active_account');
add_action('wp_ajax_nopriv_member_active_account', 'handle_active_account');
function handle_active_account()
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');
    // 1. Kiểm tra Nonce bảo mật
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(array('message' => $msg['failure']), 403);
    }

    // 2. Lấy dữ liệu từ Frontend
    $key   = sanitize_text_field($_POST['key'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');

    // 3. Kiểm tra dữ liệu đầu vào
    if (empty($email) || empty($key)) {
        wp_send_json_error(array('message' => $msg['empty']));
    }

    // 4. Kiểm tra User có tồn tại với Email này không
    $user = get_model()->get_user_by_email($email);
    if (!$user) {
        wp_send_json_error(array('message' => $msg['not_exist']));
    }

    // 5. Thực hiện kích hoạt (Gọi hàm update trong Model)
    // Lưu ý: Hàm này trả về số dòng bị ảnh hưởng hoặc false
    $hashed_token = hash('sha256', $key);
    $activated = get_model()->active_member($email, $hashed_token);

    if ($activated !== false) {
        // Nếu $activated === 0 nghĩa là tài khoản đã kích hoạt từ trước rồi (status đã là 1)
        // Nếu $activated > 0 nghĩa là vừa cập nhật thành công
        wp_send_json_success(array(
            'message' => $msg['success_activate']
        ));
    } else {
        // Trường hợp lỗi SQL hoặc không khớp active_code
        wp_send_json_error(array(
            'message' => $msg['failure']
        ));
    }

    wp_die(); // Luôn kết thúc hàm AJAX của WordPress bằng wp_die()
}

/* =========================================================
CẬP NHẬT THONG TIN KHÁCH HÀNG
========================================================= */
add_action('wp_ajax_member_change_info',        'handle_change_info');
add_action('wp_ajax_nopriv_member_change_info', 'handle_change_info');
function handle_change_info()
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');

    // 1. 驗證 Nonce (請確保你 wp_localize_script 也是用 'member_auth_nonce')
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(array('message' => $msg['failure']), 403);
    }

    // 2. 確認使用者有登入
    $user = is_member_logged_in();
    if (!$user) {
        wp_send_json_error(array('message' => $msg['login_req']));
    }

    // 3. 接收前端傳來的資訊
    $update_data = [
        'username'   => sanitize_text_field($_POST['username'] ?? ''),
        'company'    => sanitize_text_field($_POST['company'] ?? ''),
        'phone'      => sanitize_text_field($_POST['phone'] ?? ''),
        'industry'   => sanitize_text_field($_POST['industry'] ?? ''),
        'department' => sanitize_text_field($_POST['department'] ?? ''),
        'position'   => sanitize_text_field($_POST['position'] ?? ''),
        'tax'        => sanitize_text_field($_POST['tax'] ?? '')
    ];

    // 4. 驗證輸入的資訊 (檢查陣列中是否有任何一個值是空字串)
    if (in_array('', $update_data, true)) {
        wp_send_json_error(array('message' => $msg['empty']));
    }

    if (!empty($_COOKIE['custom_session'])) {

        $session_key = sanitize_text_field($_COOKIE['custom_session']);
        get_model()->update_info($session_key, $update_data);
    }

    wp_send_json_success(array(
        'message' => $msg['success_change_info']
    ));
}

add_action('wp_ajax_member_forgot_password',        'handle_forgot_password');
add_action('wp_ajax_nopriv_member_forgot_password', 'handle_forgot_password');
function handle_forgot_password()
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');
    // Kiểm tra bảo mật nonce (tên trường 'nonce' phải khớp với data gửi từ JS)
    check_ajax_referer('ajax_forgot_nonce', 'nonce');

    // Làm sạch dữ liệu đầu vào
    $email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    if (empty($email) || !is_email($email)) {
        wp_send_json_error(['message' => $msg['email_invalid']]);
    }
    $user = get_model()->get_user_by_email($email);
    if (!$user) {
        // Bảo mật: Đôi khi nên báo "Thành công" luôn để tránh bị dò tìm email người dùng
        wp_send_json_error(['message' => $msg['not_exist']]);
    }

    // --- BẮT ĐẦU TẠO TOKEN ---
    // 1. Tạo một token ngẫu nhiên cực dài
    $plain_token = bin2hex(random_bytes(32));
    // 2. Hash token trước khi lưu vào DB (Bảo mật PHP 8.2)
    $hashed_token = hash('sha256', $plain_token);
    // $expiry = time() + (5 * 60); // TEST 5 phút
    // 3. Thời gian hết hạn (24h kể từ bây giờ)
    $expiry = time() + (24 * 60 * 60);

    get_model()->update_token($email, $hashed_token, $expiry);

    // Tạo link reset mật khẩu chuyên nghiệp
    //$url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');
    // ==================== 修復 1: 設置正確的郵件頭部 ====================
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // 修復 2: 添加明確的FROM地址 (使用WordPress網站郵箱)
    $from_email = get_option('admin_email');
    $from_name = get_bloginfo('name');
    $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';

    // 修復 3: 添加Reply-To
    $headers[] = 'Reply-To: ' . $from_email;

    $subject = "Reset Password - " . get_bloginfo('name');

    // 修復 4: 使用 HTML 格式 (更容易被郵件服務器接受)
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
            <p><strong>Hoặc sao chép liên kết này vào trình duyệt:</strong><br>
            " . esc_url($reset_url) . "</p>
            <p>Nếu bạn không yêu cầu điều này, hãy bỏ qua email này. Liên kết này sẽ hết hạn trong 24 giờ.</p>
            <hr>
            <p><small>" . get_bloginfo('name') . "</small></p>
            <br>
            <hr>
            <br>
             <h2>重設密碼請求</h2>
                <p>您好 " . esc_html($user->username) . ",</p>
                <p>我們收到了您的帳號重設密碼請求。</p>
                <p>請點擊下方連結以設定新密碼：</p>
                <p>
                    <a href='" . esc_url($reset_url) . "' style='background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        重設密碼
                    </a>
                </p>
                <p><strong>或者將此連結複製並貼上到瀏覽器中：</strong><br>
                " . esc_url($reset_url) . "</p>
                <p>如果您未曾發送此請求，請忽略此郵件。該連結將在 24 小時後失效。</p>
                <hr>
                <p><small>" . get_bloginfo('name') . "</small></p>
        </body>
    </html>
    ";

    // ==================== 修復 5: 添加詳細的調試日誌 ====================
    $log_message = "[" . date('Y-m-d H:i:s') . "] Forgot Password Attempt\n";
    $log_message .= "Email: " . $email . "\n";
    $log_message .= "Username: " . (isset($user->username) ? $user->username : 'N/A') . "\n";
    $log_message .= "From: " . $from_email . "\n";
    $log_message .= "Headers: " . print_r($headers, true) . "\n";

    // 使用WordPress的debug.log或自訂日誌檔案
    error_log($log_message, 3, WP_CONTENT_DIR . '/forgot-password.log');

    // ==================== 修復 6: 發送郵件並捕獲詳細錯誤 ====================
    $sent = wp_mail(
        (string)$email,           // TO
        (string)$subject,         // SUBJECT
        (string)$message,         // MESSAGE
        $headers                  // HEADERS (修復後)
    );

    error_log("[" . date('Y-m-d H:i:s') . "] Email sent result: " . ($sent ? 'TRUE' : 'FALSE') . "\n", 3, WP_CONTENT_DIR . '/forgot-password.log');

    if ($sent) {
        wp_send_json_success(['message' => $msg['mail_sent']]);
    } else {
        wp_send_json_error(['message' => $msg['failure']]);
    }

    wp_die();
}


/* =========================================================
// ===== HÀM KIỂM TRA ĐĂNG NHẬP =====
========================================================= */
function is_member_logged_in()
{
    // Không có cookie → chưa đăng nhập
    if (!isset($_COOKIE['custom_session'])) return false;

    $session_key = sanitize_text_field($_COOKIE['custom_session']);

    if (empty($session_key)) return false;

    // Tìm session_key trong DB
    $user = get_model()->get_user_by_session($session_key);

    return $user ? $user : false;
}




add_action('wp_ajax_admin_update_client_password', 'ajax_admin_handle_client_password');
add_action('wp_ajax_nopriv_admin_update_client_password', 'ajax_admin_handle_client_password');

function ajax_admin_handle_client_password()
{
    $msg = get_member_messages($_POST['lang'] ?? 'vi');
    // 1. 安全檢查：確認目前操作者是否有編輯使用者的權限
    if (!current_user_can('edit_users')) {
        wp_send_json_error(['message' => '您沒有權限執行此操作。']);
    }

    $email  = sanitize_text_field(wp_unslash($_POST['email'])) ?? '';
    $name   = sanitize_text_field(wp_unslash($_POST['name'])) ?? '';
    $password = sanitize_text_field(wp_unslash($_POST['password'])) ?? '';

    if (!empty($email) && !empty($password)) {

        // 2. 加密新密碼
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // 3. 呼叫你的 Model 根據 ID 更新密碼
        // 假設你的 Model 有 update_password_by_id 這個方法
        $result = get_model()->reset_password($email, $password_hash);

        if ($result) {
            // wp_send_json_success(['message' => '客戶密碼已成功更新！']);
            // Tạo link reset mật khẩu chuyên nghiệp
            //$url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');
            // ==================== 修復 1: 設置正確的郵件頭部 ====================
            $headers = array('Content-Type: text/html; charset=UTF-8');

            // 修復 2: 添加明確的FROM地址 (使用WordPress網站郵箱)
            $from_email = get_option('admin_email');
            $from_name = get_bloginfo('name');
            $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
            // 修復 3: 添加Reply-To
            $headers[] = 'Reply-To: ' . $from_email;
            $subject = "New Password - " . get_bloginfo('name');
            $message = "
                        <html>
                            <body style='font-family: Arial, sans-serif; color: #333;'>
                                <h2>đặt lại mật khẩu</h2>
                                <p>Chào " . esc_html($name) . ",</p>
                                <p>Chúng tôi đã đặt lại mật khẩu cho tài khoản của bạn.</p>
                                <p>Mật khẩu mới: " . $password . " </p>
                                <hr>
                                <p><small>" . get_bloginfo('name') . "</small></p>
                                <br>
                                <hr>
                                <br>
                                <h2>重設密碼</h2>
                                    <p>您好 " . esc_html($name) . ",</p>
                                    <p>您的帳號已重設密碼。</p>
                                    <p>新密碼是：" . $password . "</p>
                                    <hr>
                                    <p><small>" . get_bloginfo('name') . "</small></p>
                            </body>
                        </html>
                     ";

            // ==================== 修復 5: 添加詳細的調試日誌 ====================
            $log_message = "[" . date('Y-m-d H:i:s') . "] Forgot Password Attempt\n";
            $log_message .= "Email: " . $email . "\n";
            $log_message .= "Username: " . (isset($user->username) ? $user->username : 'N/A') . "\n";
            $log_message .= "From: " . $from_email . "\n";
            $log_message .= "Headers: " . print_r($headers, true) . "\n";

            // 使用WordPress的debug.log或自訂日誌檔案
            error_log($log_message, 3, WP_CONTENT_DIR . '/forgot-password.log');

            // ==================== 修復 6: 發送郵件並捕獲詳細錯誤 ====================
            $sent = wp_mail(
                (string)$email,           // TO
                (string)$subject,         // SUBJECT
                (string)$message,         // MESSAGE
                $headers                  // HEADERS (修復後)
            );

            error_log("[" . date('Y-m-d H:i:s') . "] Email sent result: " . ($sent ? 'TRUE' : 'FALSE') . "\n", 3, WP_CONTENT_DIR . '/forgot-password.log');

            if ($sent) {
                wp_send_json_success(['message' => '客戶密碼已成功更新，並已發送新密碼到客戶郵箱！']);
            } else {
                wp_send_json_error(['message' => $msg['failure']]);
            }
        } else {
            wp_send_json_error(['message' => '資料庫更新失敗.']);
        }
    } else {
        wp_send_json_error(['message' => '無效的使用者 Email 或密碼。']);
    }



    wp_die();
}


/**
 * Helper function để lấy instance duy nhất của Model_Download_Function
 * Đảm bảo tối ưu bộ nhớ và code sạch sẽ.
 */
if (! function_exists('get_model')) {
    function get_model()
    {
        static $instance = null;
        if (null === $instance) {
            // Kiểm tra nếu class chưa tồn tại thì gọi file chứa nó vào
            if (!class_exists('Model_Download_Function')) {
                // SỬA ĐƯỜNG DẪN NÀY CHO ĐÚNG VỚI CẤU TRÚC THƯ MỤC CỦA BẠN
                // Ví dụ: file model nằm cùng thư mục:
                // require_once __DIR__ . '/ten-file-chua-class-model.php'; 

                // Hoặc nếu nó nằm trong thư mục model:
                require_once get_stylesheet_directory() . '/model/model_download_function.php';
            }

            $instance = new Model_Download_Function();
        }
        return $instance;
    }
}
