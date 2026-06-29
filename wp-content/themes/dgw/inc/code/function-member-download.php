<?php

use SimplePie\Parse\Date;

require_once get_template_directory() . '/model/model-download-function.php';
// 2026-06-26
// Cập nhật cho file: /inc/front/function-member-download.php
if ( ! function_exists( 'dgw_generate_expiry_time' ) ) {
    function dgw_generate_expiry_time(): string {
        $timezone = wp_timezone(); 
        $now = new DateTimeImmutable( 'now', $timezone );
        $expiry = $now->modify( '+24 hours' );
        return $expiry->format( 'Y-m-d H:i:s' );
    }
}

// 1. Hàm tạo thời điểm hết hạn (24 giờ kể từ lúc yêu cầu)
// function dgw_generate_expiry_time(): string
// {
//     // Sử dụng DateTimeImmutable của PHP 8.2 để an toàn và chính xác
//     $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));

//     // Thêm đúng 24 giờ cho mốc hết hạn
//     $expiry = $now->modify('+24 hours');

//     // Trả về định dạng chuẩn database MySQL: YYYY-MM-DD HH:MM:SS
//     return $expiry->format('Y-m-d H:i:s');
// }

// 2026-06-26
// Ghi chú: Khởi tạo mốc thời gian hết hạn (sau 24 giờ) chuẩn MySQL.
// Sử dụng function_exists để chống lỗi trùng lặp khi require file nhiều lần.


// 2. Hàm kiểm tra Token còn hạn hay không
function dgw_is_token_valid(string $expiry_from_db): bool
{
    try {
        $expiry_time = new DateTimeImmutable($expiry_from_db, new DateTimeZone('Asia/Ho_Chi_Minh'));
        $current_time = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));

        // Nếu thời gian hiện tại vẫn nhỏ hơn thời gian hết hạn => Hợp lệ
        return $current_time < $expiry_time;
    } catch (Exception $e) {
        return false;
    }
}


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
    // $post_source = get_post_meta($post_id, '_metabox_source', true);
?>
    <script>
        jQuery(document).ready(function($) {
            jQuery('#my-load-data').attr({
                'data-post-id': '<?php echo esc_js($post_id); ?>',
                'data-post-title': '<?php echo esc_js($post_title); ?>',
            });
        });
    </script>
<?php
});


/* =========================================================
// 註冊 AJAX 動作 (登入者與訪客皆可使用)
========================================================= */



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
    $model_download = new Model_Download_Function();
    $data = $model_download->get_user_by_session($session_key);
    return $data;
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


function get_current_custom_user() {
    $model_download = new Model_Download_Function();
    // Nếu không có cookie session, trả về null
    if (empty($_COOKIE['custom_session'])) {
        return null;
    }

    $session_key = $_COOKIE['custom_session'];
    
    // Giả sử model của bạn có hàm này để lấy user từ bảng DB dựa vào session_key
    // Bạn cần điều chỉnh tên hàm get_user_by_session cho đúng với core của bạn
    $user = $model_download->get_user_by_session($session_key); 
    
    return $user ?: null;
}

/* =========================================================
phan ghi lên file google sheets
========================================================= */
// require_once get_stylesheet_directory() . '/vendor/autoload.php';

require_once WP_CONTENT_DIR . '/themes/dgw/vendor/autoload.php';

function sync_to_google_sheets($data)
{
    // [24/06/2026] - Hỗ trợ nhận param 'sheet' để tự động ghi vào sheet mong muốn thay vì fix cứng Sheet1
    $spreadsheetId = '11XOFnz7wWw1L3GKLKNtmrnwQu5uY-YusMKXu9L6SFkI';
    $sheet_name = isset($data['sheet']) ? $data['sheet'] : 'Sheet1';
    // Nếu có truyền 'values' thì dùng trực tiếp mảng đó, ngược lại dùng cấu trúc mặc định 5 cột
    if (isset($data['values']) && is_array($data['values'])) {
        $values = [$data['values']];
        $range = $sheet_name . '!A:Z'; // Để rộng range tự động khớp số lượng dữ liệu
    } else {
        $values = [
            [
                isset($data['title']) ? $data['title'] : '',
                isset($data['file']) ? $data['file'] : '',
                isset($data['name']) ? $data['name'] : '',
                isset($data['email']) ? $data['email'] : '',
                isset($data['date']) ? $data['date'] : '',
            ]
        ];
        $range = $sheet_name . '!A:E';
    }

    // $path_to_json = ABSPATH . 'google-credentials.json';
    $path_to_json = WP_CONTENT_DIR . '/google-credentials.json';
    try {
        $client = new \Google\Client();
        $client->setAuthConfig($path_to_json);
        $client->addScope(\Google\Service\Sheets::SPREADSHEETS);

        $service = new \Google\Service\Sheets($client);

        $body = new \Google\Service\Sheets\ValueRange([
            'values' => $values
        ]);

        // Đổi thành USER_ENTERED để tối ưu định dạng hiển thị trên Sheets
        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);

        return true;
    } catch (Exception $e) {
        error_log('Google Sheets Sync Error: ' . $e->getMessage());
        return false;
    }
}
