<?php

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
// ===== HÀM KIỂM TRA ĐĂNG NHẬP =====
========================================================= */
function is_custom_logged_in()
{
    global $wpdb;
    $table = $wpdb->prefix . 'download_registrations';

    // Không có cookie → chưa đăng nhập
    if (!isset($_COOKIE['custom_session'])) return false;

    $session_key = sanitize_text_field($_COOKIE['custom_session']);

    if (empty($session_key)) return false;

    // Tìm session_key trong DB
    $user = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE session_key = %s",
            $session_key
        )
    );

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
// 註冊 AJAX 動作 (登入者與訪客皆可使用)
========================================================= */

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
    $user = is_custom_logged_in();
    if (!$user) {
        wp_send_json_error(['code' => 'not_logged_in']);
        return;
    }

    // 2. Kiểm tra session_download
    // if (empty($user->session_download)) {
    //     wp_send_json_error(['code' => 'not_logged_in']);
    //     return;
    // }

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
    global $wpdb;
    $table = $wpdb->prefix . 'download_registrations';

    // ===== PHẦN BỊ THIẾU — xác thực email + password =====
    $email    = sanitize_email($_POST['email']);
    $password = $_POST['password'];

    // Kiểm tra rỗng
    if (empty($email) || empty($password)) {
        wp_send_json_error(['message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }

    // Tìm user theo email trong DB
    $user = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE email = %s",
            $email
        )
    );

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

    // Lấy thời gian + IP
    $current_time = current_time('Y-m-d H:i:s');
    $ip_address   = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }

    // Cập nhật vào DB
    $wpdb->update(
        $table,
        [
            'session_key' => $session_key,
            'last_login'  => $current_time,
            'ip_address'  => $ip_address,
        ],  // ← ghi đè mã cũ
        ['ID'          => $user->ID]
    );

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
phần register 
========================================================= */
add_action('wp_ajax_download_custom_register',        'handle_custom_register');
add_action('wp_ajax_nopriv_download_custom_register', 'handle_custom_register');

function handle_custom_register()
{
    check_ajax_referer('my_nonce', 'nonce');

    global $wpdb;
    $table = $wpdb->prefix . 'download_registrations';

    // 1. Nhận dữ liệu từ JS
    $username = sanitize_text_field($_POST['username']);
    $email    = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $company = sanitize_text_field($_POST['company']);
    $phone = sanitize_text_field($_POST['phone']);
    $tax = sanitize_text_field($_POST['tax']);
    $industry = sanitize_text_field($_POST['industry']);
    $department = sanitize_text_field($_POST['department']);
    $position = sanitize_text_field($_POST['position']);

    // 2. Kiểm tra rỗng
    if (empty($username) || empty($email) || empty($password) || empty($company || empty($phone) || empty($tax) || empty($industry) || empty($department))) {
        wp_send_json_error(['message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }   

    // 3. Kiểm tra email hợp lệ
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Email không hợp lệ']);
        return;
    }

    // 4. Kiểm tra password đủ mạnh
    if (strlen($password) < 6) {
        wp_send_json_error(['message' => 'Mật khẩu phải có ít nhất 6 ký tự']);
        return;
    }

    // 5. Kiểm tra email đã tồn tại chưa
    $email_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table WHERE email = %s",
            $email
        )
    );
    if ($email_exists) {
        wp_send_json_error(['message' => 'Email đã được sử dụng']);
        return;
    }

    // 6. Kiểm tra username đã tồn tại chưa
    $username_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table WHERE username = %s",
            $username
        )
    );
    if ($username_exists) {
        wp_send_json_error(['message' => 'Tên đăng nhập đã tồn tại']);
        return;
    }

    $active_code = generate_active_code(6, 'number'); // 6 chữ số

    // 7. Lưu vào database
    $inserted = $wpdb->insert(
        $table,
        [
            'username' => $username,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT), // mã hóa password
            'company'  => $company,
            'phone'    => $phone,
            'position' => $position,
            'tax'      => $tax,
            'industry' => $industry,
            'department' => $department,
            'active_code' => $active_code, 
            'create_date' => date('Y-m-d')  
        ],
        ['%s', '%s', '%s' , '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
    );

    // 8. Kiểm tra lưu thành công không
    if (!$inserted) {
        wp_send_json_error(['message' => 'Đăng ký thất bại, vui lòng thử lại']);
        return;
    }

    wp_send_json_success(['message' => 'Đăng ký thành công! Vui lòng đăng nhập']);
}

// ===== HÀM TẠO MÃ ACTIVE NGẪU NHIÊN =====
function generate_active_code($length = 6, $type = 'number') {
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