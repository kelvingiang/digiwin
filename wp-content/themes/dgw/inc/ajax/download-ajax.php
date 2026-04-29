<?php

/* =========================================================
phần download file
========================================================= */
add_action('wp_ajax_my_download_file', 'my_download_file');
add_action('wp_ajax_nopriv_my_download_file', 'my_download_file');

function my_download_file()
{
    $model_download = new Model_Download();
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
    $model_download->insert_download_detail([
        'user_id' => $user->ID,
        'title' => $post_title,
        'resource' => $post_source
    ]);

    // ==========================================
    // THÊM ĐOẠN MÃ ĐỒNG BỘ GOOGLE SHEETS VÀO ĐÂY
    // ==========================================

    // 1. Chuẩn bị dữ liệu theo đúng định dạng hàm sync_to_google_sheets yêu cầu
    $data_for_sheet = [
        "name"    => $post_title,       // Cột A: Tên tài liệu
        "email"   => $post_source,      // Cột B: Link tài liệu gốc
        "id"      => $user->ID,
        "date"    => Date('d-M-y H:i:s'),    // Cột C: ID người dùng
        "message" => "Tải thành công 111"   // Cột D: Thông điệp trạng thái
    ];

    // 2. Gọi hàm đồng bộ (nhớ bỏ tất cả các lệnh echo trong hàm sync_to_google_sheets nếu có)
    $sync_result = sync_to_google_sheets($data_for_sheet);

    // 3. (Tùy chọn) Log lại nếu đồng bộ thất bại, KHÔNG echo ra ngoài
    if (!$sync_result) {
        error_log('AJAX Error: Đồng bộ Google Sheets thất bại cho bài viết ID: ' . $post_id);
    }
    // ==========================================


    // 3. Trả kết quả về JS
    wp_send_json_success([
        'post_id'    => $post_id,
        'post_title' => $post_title,
        'post_source' => $download_url,
        'message'    => 'Nhận thông tin thành công!'
    ]);
}

/* =========================================================
phần login 
========================================================= */
add_action('wp_ajax_download_member_login',        'handle_member_login');
add_action('wp_ajax_nopriv_download_member_login', 'handle_member_login');
function handle_member_login()
{
    $model_download = new Model_Download();


    $lang = $_POST['lang'] ?? 'vi'; // 取得語系，預設英文

    $i18n = [
        'vn' => [
            'empty'     => "Vui lòng điền đầy đủ thông tin",
            'email'     => "Email không tồn tại",
            'password'  => "Mật khẩu không đúng",
            'active'    => "Tài khoản chưa kích hoạt",
            'success'   => " Đăng nhập thành công!",
        ],
        'cn' => [
            'empty'     => "請填寫完整資訊",
            'email'     => "帳號不存在",
            'password'  => "密碼錯誤",
            'active'    => "帳號尚未啟動",
            'success'   => "登入成功！"
        ],

    ];
    // 取得目前的語言包，如果沒對應到就用英文
    $msg = $i18n[$lang] ?? $i18n['vn'];

    // ===== PHẦN BỊ THIẾU — xác thực email + password =====
    $email    = sanitize_email($_POST['email']);
    $password = $_POST['password'];

    // Kiểm tra rỗng
    if (empty($email) || empty($password)) {
        wp_send_json_error(['message' => $msg['empty']]);
        return;
    }

    // Tìm user theo email trong DB
    $user = $model_download->get_user_by_email($email);

    // Không tìm thấy user
    if (!$user) {
        wp_send_json_error(['message' => $msg['email']]);
        return;
    }

    // Kiểm tra password có khớp không
    if (!password_verify($password, $user->password)) {
        wp_send_json_error(['message' => $msg['password']]);
        return;
    }

    // Kiểm tra account có đã kích hoạt chưa (status = 1)
    if ($user->status != 1) {
        wp_send_json_error(['message' => $msg['active']]);
        return;
    }
    // ===== KẾT THÚC PHẦN XÁC THỰC =====

    // Sinh session_key mới mỗi lần đăng nhập
    $session_key = bin2hex(random_bytes(32));

    $ip_address   = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }

    // Cập nhật vào DB
    $model_download->update_login($user->ID, $session_key, $ip_address);

    // Lưu vào cookie trình duyệt
    // ✅ setcookie tương thích mọi phiên bản PHP
    setcookie(
        'custom_session',
        $session_key,
        time() + (7 * 24 * 60 * 60),
        '/',
        '',
        true,
        true
    );

    wp_send_json_success(['message' => $msg['success']]);
}

/* =========================================================
PHẦN ĐĂNG KÝ  register 
========================================================= */
add_action('wp_ajax_download_member_register',        'handle_member_register');
add_action('wp_ajax_nopriv_download_member_register', 'handle_member_register');

function handle_member_register()
{
    check_ajax_referer('my_nonce', 'nonce');

    $lang = $_POST['lang'] ?? 'vi'; // 取得語系，預設英文

    $i18n = [
        'vn' => [
            'empty'     => "Vui lòng điền đầy đủ thông tin",
            'email'     => "Email không hợp lệ",
            'password'  => "Mật khẩu phải có ít nhất 6 ký tự",
            'exist'     => "Email hoặc tên đăng nhập đã tồn tại",
            'success'   => "Đăng ký thành công! Vui lòng kiểm tra email để kích hoạt tài khoản",
            'not_send'  => "Đăng ký thành công nhưng hệ thống không thể gửi mail kích hoạt. Vui lòng liên hệ quản trị viên",
            'failure'   => "Đăng ký thất bại, vui lòng thử lại"
        ],
        'cn' => [
            'empty'     => "請填寫完整資訊",
            'email'     => "電子郵件格式錯誤",
            'password'  => "密碼長度至少為 6 個字元",
            'exist'     => "電子郵件或使用者名稱已存在",
            'success'   => "註冊成功！請檢查您的電子郵件以啟動帳號",
            'not_send'  => "註冊成功，但系統無法傳送啟動郵件。請聯繫管理員",
            'failure'   => "註冊失敗，請稍後重試"
        ],

    ];
    // 取得目前的語言包，如果沒對應到就用英文
    $msg = $i18n[$lang] ?? $i18n['vn'];
    //--------------------------------------------------------------------
    // 1. 接收並清洗數據
    $registration_data = [
        'username'   => sanitize_text_field($_POST['username']),
        'email'      => sanitize_email($_POST['email']),
        'password'   => $_POST['password'], // 注意：這裡先拿原始值進行長度檢查，Model 層會加密
        'company'    => sanitize_text_field($_POST['company']),
        'phone'      => sanitize_text_field($_POST['phone']),
        'tax'        => sanitize_text_field($_POST['tax']),
        'industry'   => sanitize_text_field($_POST['industry']),
        'department' => sanitize_text_field($_POST['department']),
        'position'   => sanitize_text_field($_POST['position']),
    ];

    // 檢查必填項 (修正了你原代碼中的括號語法錯誤)
    if (!is_email($registration_data['email'])) {
        wp_send_json_error(['message' => $msg['email']]);
        return;
    }

    if (strlen($registration_data['password']) < 6) {
        wp_send_json_error(['message' => $msg['password']]);
        return;
    }

    foreach (['username', 'email', 'password', 'company', 'phone'] as $field) {
        if (empty($registration_data[$field])) {
            wp_send_json_error(['message' => $msg['empty']]);
            return;
        }
    }

    $model_download = new Model_Download();

    // 3. 檢查唯一性 (Email & Username)
    $exists = $model_download->check_email_username_exists(
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
    $result = $model_download->insert_registration_data($registration_data);
    if ($result) {
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

        $subject = "Activate account - " . get_bloginfo('name');

        // 修復 4: 使用 HTML 格式 (更容易被郵件服務器接受)
        $reset_url = home_url('/active-member/?key=' . $registration_data['active_code'] . '&email=' . rawurlencode($registration_data['email']));

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
        $log_message = "[" . date('Y-m-d H:i:s') . "] Forgot Password Attempt\n";
        $log_message .= "Email: " . $registration_data['email'] . "\n";
        $log_message .= "Username: " . (isset($registration_data['username']) ? $registration_data['username'] : 'N/A') . "\n";
        $log_message .= "From: " . $from_email . "\n";
        $log_message .= "Headers: " . print_r($headers, true) . "\n";

        // 使用WordPress的debug.log或自訂日誌檔案
        error_log($log_message, 3, WP_CONTENT_DIR . '/forgot-password.log');

        // ==================== 修復 6: 發送郵件並捕獲詳細錯誤 ====================
        $sent = wp_mail(
            (string)$registration_data['email'],           // TO
            (string)$subject,         // SUBJECT
            (string)$message,         // MESSAGE
            $headers                  // HEADERS (修復後)
        );
        if ($sent) {
            wp_send_json_success([
                'message' => $msg['success']
            ]);
        } else {
            wp_send_json_success([
                'message' => $msg['not_send']
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
        $model_download = new Model_Download();
        $model_download->clear_session($session_key);
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
    // 1. 驗證 Nonce (請確保你 wp_localize_script 也是用 'member_auth_nonce')
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(array('message' => '安全驗證失敗'), 403);
    }

    // 2. 確認使用者有登入
    $user = is_member_logged_in();
    if (!$user) {
        wp_send_json_error(array('message' => '請先登入'));
    }

    // 3. 接收前端傳來的密碼
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (empty($password) || empty($confirm)) {
        wp_send_json_error(array('message' => '請輸入密碼'));
    }

    if ($password !== $confirm) {
        wp_send_json_error(array('message' => '兩次輸入的密碼不一致'));
    }

    if (!empty($_COOKIE['custom_session'])) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $session_key = sanitize_text_field($_COOKIE['custom_session']);
        $model_download = new Model_Download();
        $model_download->update_password($session_key, $password_hash);
    }

    wp_send_json_success(array(
        'message' => '密碼已成功更新'
    ));
}


/* =========================================================
RESET PASWORD 
========================================================= */
add_action('wp_ajax_member_reset_password',        'handle_reset_password');
add_action('wp_ajax_nopriv_member_reset_password', 'handle_reset_password');

function handle_reset_password()
{
    // 1. 驗證 Nonce (請確保你 wp_localize_script 也是用 'member_auth_nonce')
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(array('message' => '安全驗證失敗'), 403);
    }

    // 2. 確認使用者有登入

    // 3. 接收前端傳來的密碼
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $key = $_POST['key'] ?? '';
    $email = $_POST['email'] ?? '';

    if (empty($password) || empty($confirm) || empty($key)) {
        wp_send_json_error(array('message' => '請輸入密碼'));
    }

    if ($password !== $confirm) {
        wp_send_json_error(array('message' => '兩次輸入的密碼不一致'));
    }

    if (!empty($key)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $model_download = new Model_Download();
        $model_download->reset_password($email, $password_hash);
    }

    wp_send_json_success(array(
        'message' => '密碼已成功更新'
    ));
}


/* =========================================================
RESET PASWORD 
========================================================= */
add_action('wp_ajax_member_active_account',        'handle_active_account');
add_action('wp_ajax_nopriv_member_active_account', 'handle_active_account');

function handle_active_account()
{
    // 1. Kiểm tra Nonce bảo mật
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(array('message' => 'Xác thực bảo mật thất bại.'), 403);
    }

    // 2. Lấy dữ liệu từ Frontend
    $key   = sanitize_text_field($_POST['key'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');

    // 3. Kiểm tra dữ liệu đầu vào
    if (empty($email) || empty($key)) {
        wp_send_json_error(array('message' => 'Thông tin kích hoạt không đầy đủ.'));
    }

    $model_download = new Model_Download();

    // 4. Kiểm tra User có tồn tại với Email này không
    $user = $model_download->get_user_by_email($email);
    if (!$user) {
        wp_send_json_error(array('message' => 'Tài khoản không tồn tại.'));
    }

    // 5. Thực hiện kích hoạt (Gọi hàm update trong Model)
    // Lưu ý: Hàm này trả về số dòng bị ảnh hưởng hoặc false
    $activated = $model_download->active_member($email, $key);

    if ($activated !== false) {
        // Nếu $activated === 0 nghĩa là tài khoản đã kích hoạt từ trước rồi (status đã là 1)
        // Nếu $activated > 0 nghĩa là vừa cập nhật thành công
        wp_send_json_success(array(
            'message' => 'Tài khoản của bạn đã được kích hoạt thành công!'
        ));
    } else {
        // Trường hợp lỗi SQL hoặc không khớp active_code
        wp_send_json_error(array(
            'message' => 'Kích hoạt thất bại. Mã kích hoạt không chính xác hoặc đã hết hạn.'
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
    // 1. 驗證 Nonce (請確保你 wp_localize_script 也是用 'member_auth_nonce')
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error(array('message' => '安全驗證失敗'), 403);
    }

    // 2. 確認使用者有登入
    $user = is_member_logged_in();
    if (!$user) {
        wp_send_json_error(array('message' => '請先登入'));
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
        wp_send_json_error(array('message' => '請填寫所有欄位'));
    }

    if (!empty($_COOKIE['custom_session'])) {
        $model_download = new Model_Download();
        $session_key = sanitize_text_field($_COOKIE['custom_session']);
        $model_download->update_info($session_key, $update_data);
    }

    wp_send_json_success(array(
        'message' => '密碼已成功更新'
    ));
}

add_action('wp_ajax_member_forgot_password',        'handle_forgot_password');
add_action('wp_ajax_nopriv_member_forgot_password', 'handle_forgot_password');

function handle_forgot_password()
{
    // Kiểm tra bảo mật nonce (tên trường 'nonce' phải khớp với data gửi từ JS)
    check_ajax_referer('ajax_forgot_nonce', 'nonce');


    $lang = $_POST['lang'] ?? 'vi'; // 取得語系，預設英文

    $i18n = [
        'vn' => [
            'not_exist' => "Email không tồn tại",
            'email'     => "Vui lòng nhập địa chỉ email hợp lệ",
            'success'   => "Thành công! hãy kiểm tra email, link này chỉ có hiệu lực trong 24H",
            'failure'   => "Lỗi hệ thống không thể gửi mail, hãy liên lạc với chúng tôi"
        ],
        'cn' => [
            'not_exist' => "電子郵件不存在",
            'email'     => "請輸入有效的電子郵件地址",
            'success'   => "成功！重設密碼連結已傳送到您的 E-mail, 該連結將在24小時後失效",
            'failure'   => "系統錯誤，無法傳送郵件，請與我們聯繫"
        ],

    ];
    // 取得目前的語言包，如果沒對應到就用英文
    $msg = $i18n[$lang] ?? $i18n['vn'];

    // Làm sạch dữ liệu đầu vào
    $email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    if (empty($email) || !is_email($email)) {
        wp_send_json_error(['message' => $msg['email']]);
    }

    $model_download = new Model_Download();
    $user = $model_download->get_user_by_email($email);


    if (!$user) {
        // Bảo mật: Đôi khi nên báo "Thành công" luôn để tránh bị dò tìm email người dùng
        wp_send_json_error(['message' => $msg['not_exist']]);
    }

    // --- BẮT ĐẦU TẠO TOKEN ---
    // 1. Tạo một token ngẫu nhiên cực dài
    $plain_token = bin2hex(random_bytes(32));
    // 2. Hash token trước khi lưu vào DB (Bảo mật PHP 8.2)
    $hashed_token = hash('sha256', $plain_token);
    // 3. Thời gian hết hạn (24h kể từ bây giờ)
    $expiry = time() + (24 * 60 * 60);

    $model_download->update_token($email, $hashed_token, $expiry);

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
    $reset_url = home_url('/reset-password/?key=' . $hashed_token . '&email=' . rawurlencode($email));

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
        wp_send_json_success(['message' => $msg['success']]);
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
    $model_download = new Model_Download();

    // Không có cookie → chưa đăng nhập
    if (!isset($_COOKIE['custom_session'])) return false;

    $session_key = sanitize_text_field($_COOKIE['custom_session']);

    if (empty($session_key)) return false;

    // Tìm session_key trong DB
    $user = $model_download->get_user_by_session($session_key);

    return $user ? $user : false;
}
