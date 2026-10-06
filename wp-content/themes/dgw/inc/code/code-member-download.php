<?php
declare(strict_types=1);

// [2026-10-05] - @author: Kelvin - Helper & Hook Setup: Quản lý token thời hạn, asset download và phiên đăng nhập thành viên

if (!defined('ABSPATH')) {
    exit;
}

require_once DIR_MODEL . 'model-download-function.php';

/**
 * [2026-10-05] - @author: Kelvin - Tạo mốc thời gian hết hạn sau 24 giờ chuẩn định dạng MySQL
 */
if (!function_exists('dgw_generate_expiry_time')) {
    function dgw_generate_expiry_time(): string
    {
        $timezone = wp_timezone();
        $now      = new DateTimeImmutable('now', $timezone);
        $expiry   = $now->modify('+24 hours');
        return $expiry->format('Y-m-d H:i:s');
    }
}

/**
 * [2026-10-05] - @author: Kelvin - Kiểm tra token còn trong thời hạn hợp lệ hay không
 */
if (!function_exists('dgw_is_token_valid')) {
    function dgw_is_token_valid(string $expiry_from_db): bool
    {
        try {
            $timezone     = wp_timezone();
            $expiry_time  = new DateTimeImmutable($expiry_from_db, $timezone);
            $current_time = new DateTimeImmutable('now', $timezone);

            return $current_time < $expiry_time;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

/**
 * [2026-10-05] - @author: Kelvin - Convert link Google Drive sang link download trực tiếp
 */
if (!function_exists('convert_gdrive_to_download')) {
    function convert_gdrive_to_download(string $url): string
    {
        if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $file_id = $matches[1];
            return 'https://drive.google.com/uc?export=download&id=' . $file_id;
        }
        return $url;
    }
}

/**
 * [2026-10-05] - @author: Kelvin - Lấy thông tin tài khoản thành viên dựa trên session key
 */
if (!function_exists('get_member_information')) {
    function get_member_information(string $session_key)
    {
        $model_download = new Model_Download_Function();
        return $model_download->get_user_by_session($session_key);
    }
}

/**
 * [2026-10-05] - @author: Kelvin - Lấy thông tin thành viên hiện tại đang đăng nhập qua cookie
 */
if (!function_exists('get_current_custom_user')) {
    function get_current_custom_user()
    {
        if (empty($_COOKIE['custom_session'])) {
            return null;
        }

        $session_key = sanitize_text_field($_COOKIE['custom_session']);
        $model       = new Model_Download_Function();
        $user        = $model->get_user_by_session($session_key);

        return $user ?: null;
    }
}

/**
 * [2026-10-05] - @author: Kelvin - Đăng ký script download tài liệu
 */
function my_enqueue_scripts(): void
{
    wp_enqueue_script(
        'my-script',
        get_stylesheet_directory_uri() . '/js/my-download.js',
        ['jquery'],
        '1.0.0',
        true
    );

    wp_localize_script('my-script', 'MyAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('my_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');

/**
 * [2026-10-05] - @author: Kelvin - Nạp JS xác thực thành viên chỉ trên trang member
 */
add_action('wp_enqueue_scripts', function (): void {
    if (!is_page('member')) {
        return;
    }

    wp_enqueue_script('member-auth', get_template_directory_uri() . '/js/member-auth.js', ['jquery'], '1.0.0', true);
    wp_localize_script('member-auth', 'MemberAuth', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('member_auth_nonce'),
    ]);
});

/**
 * [2026-10-05] - @author: Kelvin - Gán data attribute cho nút tải tài liệu trong footer
 */
add_action('wp_footer', function (): void {
    $post_id    = get_the_ID();
    $post_title = get_the_title();
    if (!$post_id) {
        return;
    }
    ?>
    <script>
        jQuery(document).ready(function($) {
            $('#my-load-data').attr({
                'data-post-id': '<?php echo esc_js((string) $post_id); ?>',
                'data-post-title': '<?php echo esc_js((string) $post_title); ?>'
            });
        });
    </script>
    <?php
});

/**
 * [2026-10-05] - @author: Kelvin - Nạp modal form đăng nhập và quên mật khẩu vào footer
 */
if (function_exists('member_login_register_form')) {
    add_action('wp_footer', 'member_login_register_form');
}

if (function_exists('member_forgot_password_form')) {
    add_action('wp_footer', function (): void {
        echo member_forgot_password_form();
    });
}
