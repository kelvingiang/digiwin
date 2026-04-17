<?php

use SimplePie\Parse\Date;

require_once get_template_directory() . '/model/model-download.php';

/* =========================================================
// tao 2026-04 thêm file my-download.js  =======
========================================================= */
function my_enqueue_scripts()
{

    // 1. Đăng ký file JS của bạn
    wp_enqueue_script(
        'my-script',
        get_stylesheet_directory_uri() . '/js/my-download.js',
        ['jquery'],
        null,
        true
    );

    // 2. Truyền data từ PHP → JS (WordPress tự làm phần còn lại)
    wp_localize_script('my-script', 'MyAjax', [
        'ajax_url'   => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('my_nonce'),
        // 'post_id'    => get_the_ID(),
        // 'post_title' => get_the_title(),
    ]);
}
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');


/* =========================================================
// Hook vào footer, tự động gán data attribute vào button bằng JS  
========================================================= */
add_action('wp_footer', function () {
    $post_id    = get_the_ID();
    $post_title = get_the_title();
    $post_source = get_post_meta($post_id, '_metabox_source', true);
?>
    <script>
        jQuery(document).ready(function($) {
            $('#my-load-data').attr({
                'data-post-id': '<?php echo esc_js($post_id); ?>',
                'data-post-title': '<?php echo esc_js($post_title); ?>',
                'data-post-source': '<?php echo esc_js($post_source); ?>'
            });
        });
    </script>
<?php
});


/* =========================================================
// 註冊 AJAX 動作 (登入者與訪客皆可使用)
========================================================= */

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
    $user = is_custom_logged_in();
    if (!$user) {
        wp_send_json_error(['code' => 'not_logged_in']);
        return;
    }

    // 2. Nhận dữ liệu từ JS gửi lên
    $post_id    = isset($_POST['post_id'])    ? intval($_POST['post_id'])              : 0;
    $post_title = isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : '';
    $post_source = isset($_POST['post_source']) ? sanitize_text_field($_POST['post_source']) : '';

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
add_action('wp_ajax_download_custom_login',        'handle_custom_login');
add_action('wp_ajax_nopriv_download_custom_login', 'handle_custom_login');
function handle_custom_login()
{
    $model_download = new Model_Download();

    // ===== PHẦN BỊ THIẾU — xác thực email + password =====
    $email    = sanitize_email($_POST['email']);
    $password = $_POST['password'];

    // Kiểm tra rỗng
    if (empty($email) || empty($password)) {
        wp_send_json_error(['message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }

    // Tìm user theo email trong DB
    $user = $model_download->get_user_by_email($email);

    // Không tìm thấy user
    if (!$user) {
        wp_send_json_error(['message' => 'Email không tồn tại']);
        return;
    }

    // Kiểm tra password có khớp không
    if (!password_verify($password, $user->password)) {
        wp_send_json_error(['message' => 'Mật khẩu không đúng']);
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

    wp_send_json_success(['message' => 'Đăng nhập thành công!']);
}

/* =========================================================
PHẦN ĐĂNG KÝ  register 
========================================================= */
add_action('wp_ajax_download_custom_register',        'handle_custom_register');
add_action('wp_ajax_nopriv_download_custom_register', 'handle_custom_register');

function handle_custom_register()
{
    check_ajax_referer('my_nonce', 'nonce');

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

    // 2. 邏輯驗證 (Validation)

    // 檢查必填項 (修正了你原代碼中的括號語法錯誤)
    foreach (['username', 'email', 'password', 'company', 'phone'] as $field) {
        if (empty($registration_data[$field])) {
            wp_send_json_error(['message' => 'Vui lòng điền đầy đủ thông tin']);
            return;
        }
    }

    if (!is_email($registration_data['email'])) {
        wp_send_json_error(['message' => 'Email không hợp lệ']);
        return;
    }

    if (strlen($registration_data['password']) < 6) {
        wp_send_json_error(['message' => 'Mật khẩu phải có ít nhất 6 ký tự']);
        return;
    }


    $model_download = new Model_Download();

    // 3. 檢查唯一性 (Email & Username)
    $exists = $model_download->check_email_username_exists(
        ['email' => $registration_data['email']],
        ['username' => $registration_data['username']]
    );

    if ($exists > 0) {
        wp_send_json_error(['message' => 'Email hoặc tên đăng nhập đã tồn tại']);
        return;
    }

    // 4. 生成激活碼
    $registration_data['active_code'] = generate_active_code(6, 'number');

    // 5. 調用 Model 執行寫入

    $result = $model_download->insert_registration_data($registration_data);

    if ($result) {
        wp_send_json_success(['message' => 'Đăng ký thành công! Vui lòng đăng nhập']);
    } else {
        wp_send_json_error(['message' => 'Đăng ký thất bại, vui lòng thử lại']);
    }
}
/* =========================================================
// AJAX endpoint（登入 / 未登入 都可呼叫）
========================================================= */
add_action('wp_ajax_check_member_login',        'ajax_check_member_login');
add_action('wp_ajax_nopriv_check_member_login', 'ajax_check_member_login');

function ajax_check_member_login()
{
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'member_auth_nonce')) {
        wp_send_json_error('Invalid nonce', 403);
    }

    $user = is_custom_logged_in();

    wp_send_json_success([
        'logged_in' => (bool) $user,
        'email'     => $user->email ?? '',
        'name'      => $user->name  ?? '',
    ]);
}

/* =========================================================
// AJAX 登出
========================================================= */
add_action('wp_ajax_member_logout',        'ajax_member_logout');
add_action('wp_ajax_nopriv_member_logout', 'ajax_member_logout');

function ajax_member_logout()
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

    wp_send_json_success(['logged_out' => true]);
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
    $user = is_custom_logged_in();
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
    $user = is_custom_logged_in();
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

    // Làm sạch dữ liệu đầu vào
    $email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    if (empty($email) || !is_email($email)) {
        wp_send_json_error(['message' => 'Vui lòng nhập địa chỉ email hợp lệ.']);
    }

    $model_download = new Model_Download();
    $user = $model_download->get_user_by_email($email);


    if (!$user) {
        // Bảo mật: Đôi khi nên báo "Thành công" luôn để tránh bị dò tìm email người dùng
        wp_send_json_error(['message' => 'Email không tồn tại trong hệ thống.']);
    }

    // --- BẮT ĐẦU TẠO TOKEN ---
    // 1. Tạo một token ngẫu nhiên cực dài
    $plain_token = bin2hex(random_bytes(32)); 
    // 2. Hash token trước khi lưu vào DB (Bảo mật PHP 8.2)
    $hashed_token = hash('sha256', $plain_token);
    // 3. Thời gian hết hạn (24h kể từ bây giờ)
    $expiry = time() + (24 * 60 * 60);

    $model_download->update_token($email, $hashed_token, $expiry);

    // Tạo mã bảo mật reset mật khẩu của WordPress
    // $key = generate_simple_key(8);
    // if (is_wp_error($key)) {
    //     wp_send_json_error(['message' => 'Không thể tạo khóa khôi phục. Vui lòng thử lại sau.']);
    // }

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

    $subject = "Yêu cầu đặt lại mật khẩu - " . get_bloginfo('name');

    // 修復 4: 使用 HTML 格式 (更容易被郵件服務器接受)
    $reset_url = home_url('/reset-password/?key=' . $hashed_token .'&email=' . rawurlencode($email));

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
        wp_send_json_success(['message' => 'Thành công! Một liên kết đặt lại mật khẩu đã được gửi đến email của bạn.']);
    } else {
        // 詳細的錯誤信息 (僅在開發環境顯示)
        $error_msg = 'Lỗi hệ thống không thể gửi mail.';

        if (WP_DEBUG) {
            $error_msg .= ' (wp_mail() trả về FALSE. Kiểm tra log: /wp-content/forgot-password.log)';
        }

        wp_send_json_error(['message' => $error_msg]);
    }

    wp_die();
}


/* =========================================================
CAC FUNCTION THÔNG THƯỜNG KHÔNG GỌI AJAX ============================================================
========================================================= */
// 只在 member page 載入登入註冊的 HTML 結構 them function vo footer, HTML
// gọi shortcode trực tiếp
add_action('wp_footer', 'member_login_register_form');

// khi goi shortcode cho giá trị trả về 
add_action('wp_footer', function () {
    echo member_forgot_password_form();
});


/* =========================================================
// ===== HÀM KIỂM TRA ĐĂNG NHẬP =====
========================================================= */
function is_custom_logged_in()
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


/* =========================================================
// Hàm convert link Google Drive
========================================================= */
function convert_gdrive_to_download($url)
{
    // Lấy FILE_ID từ link
    if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $file_id = $matches[1];
        return 'https://drive.google.com/uc?export=download&id=' . $file_id;
    }
    return $url;
}


/* =========================================================
LẤY THÔNG TIN KHÁCH HÀNG THEO MÃ COOKIE
========================================================= */
function get_member_information($session_key)
{
    $model_download = new Model_Download();
    $data = $model_download->get_user_by_session($session_key);
    return $data;
}


/* =========================================================
HÀM TẠO MÃ ACTIVE NGẪU NHIÊN =====
========================================================= */
function generate_active_code($length = 6, $type = 'number')
{
    switch ($type) {

        // Chỉ số — ví dụ: 847392
        case 'number':
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= random_int(0, 9);
            }
            return $code;

            // Chữ hoa + số — ví dụ: A3K9BX
        case 'alphanumeric':
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // bỏ 0,O,1,I dễ nhầm
            $code  = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            return $code;

            // Hex — ví dụ: a3f8c2d1
        case 'hex':
            return bin2hex(random_bytes($length / 2));

        default:
            return generate_active_code($length, 'number');
    }
}

/* =========================================================
ramdom tạo mã khẩu khi member quên mật khẩu
========================================================= */
function generate_simple_key($length = 8)
{
    // Tập hợp các ký tự bao gồm chữ cái và chữ số
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    // Sử dụng random_int để đảm bảo tính bảo mật trên PHP 8.2
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomString;
}

/* =========================================================
只在 member page 載入 JS
 =========================================================*/
add_action('wp_enqueue_scripts', function () {
    if (!is_page('member')) return;

    wp_enqueue_script('member-auth', get_template_directory_uri() . '/js/member-auth.js', [], '1.0', true);
    wp_localize_script('member-auth', 'MemberAuth', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('member_auth_nonce'),
    ]);
});


/* =========================================================
phan ghi lên file google sheets
========================================================= */
require_once get_stylesheet_directory() . '/vendor/autoload.php';
function sync_to_google_sheets($data)
{
    // 1. 填入你的專屬 ID (從網址列取得)
    $spreadsheetId = '11XOFnz7wWw1L3GKLKNtmrnwQu5uY-YusMKXu9L6SFkI';

    // 2. 設定工作表名稱與寫入範圍 (根據你的截圖，工作表名稱通常是預設的 Sheet1)
    $range = 'Sheet1!A:E';

    // 3. 設定 JSON 金鑰的路徑 (請將你的 json 檔案放到安全的路徑)
    $path_to_json = dirname(__DIR__, 2) . '/class/eloquent-pact-493206-g4-cafc20f9a4d1.json';

    try {
        $client = new \Google\Client();
        $client->setAuthConfig($path_to_json);
        $client->addScope(\Google\Service\Sheets::SPREADSHEETS);

        $service = new \Google\Service\Sheets($client);

        // 4. 準備你要寫入的資料 (對應 A, B, C, D 欄)
        $values = [
            [
                $data['name'],
                $data['email'],
                $data['id'],
                $data['date'],
                $data['message']
            ]
        ];

        $body = new \Google\Service\Sheets\ValueRange([
            'values' => $values
        ]);

        $params = [
            'valueInputOption' => 'RAW' // 寫入純文字
        ];

        // 5. 執行寫入動作 (Append 會自動加在最後一行空白列)
        $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);

        return true;
    } catch (Exception $e) {
        error_log('Google Sheets 寫入失敗: ' . $e->getMessage());
        return false;
    }
}
