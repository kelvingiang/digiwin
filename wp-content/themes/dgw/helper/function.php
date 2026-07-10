<?php
// dua toan bo cac function vao file nay de giam do phuc tap cua file functions.php
require_once get_template_directory() . '/inc/init.php';


// [2026-07-09] - Refactor dgw_get_lang and locale mapping for AJAX/get/cookie consistency
function dgw_get_lang()
{
    $default = 'vn';

    if (wp_doing_ajax()) {
        if (isset($_REQUEST['lang'])) {
            $lang = sanitize_text_field($_REQUEST['lang']);
            if ($lang === 'cn' || $lang === 'zh-TW') {
                return 'cn';
            }
            if ($lang === 'vi-VN' || $lang === 'vn') {
                return 'vn';
            }
        }
    }

    if (isset($_GET['lang'])) {
        $lang = sanitize_text_field($_GET['lang']);
        if ($lang === 'cn' || $lang === 'zh-TW') {
            return 'cn';
        }
        return 'vn';
    }

    if (isset($_COOKIE['site_lang'])) {
        $lang = sanitize_text_field($_COOKIE['site_lang']);
        return $lang === 'cn' ? 'cn' : 'vn';
    }

    return $default;
}

add_filter('locale', function ($locale) {
    if (is_admin() && !wp_doing_ajax()) {
        return $locale;
    }

    $lang = dgw_get_lang();
    if ($lang === 'cn') {
        return 'zh_TW';
    }

    return 'vi_VN';
});

// sắp xếp lại trình tự các input trong phần comment ==========
add_filter('comment_form_fields', function ($fields) {
    // 把 author 和 email 欄位放前面，comment 欄位放最後
    $comment_field = $fields['comment'];
    unset($fields['comment']);
    $fields['comment'] = $comment_field;
    return $fields;
});


// 01/12/2025  sắp xếp lại mục chọn cho phép lick hiện thị comment vô side bar bên phải
add_action('add_meta_boxes', function () {

    $post_types = array('post', 'resources', 'solutions', 'casestudies', 'active', 'services', 'industries');

    foreach ($post_types as $pt) {

        // 移除原本在主欄的 Discussion
        remove_meta_box('commentstatusdiv', $pt, 'normal');

        // 加到右側欄
        add_meta_box(
            'commentstatusdiv',
            __('討論'),
            'post_comment_status_meta_box',
            $pt,
            'side',
            'default'
        );
    }
});

// loại bỏ tất cá các bài có liên quan đến author cả chính lẫn phụ
add_action('template_redirect', function () {
    if (is_author()) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        include(get_query_template('404'));
        exit;
    }
});



//====== SAP LAI ARRAY THEO THU TU GIAM DAN AP DUNG CATEGORY =================
function cmp($a, $b)
{
    return strcmp((string)($b['order'] ?? ''), (string)($a['order'] ?? ''));
}

// [2026-07-09] - Refactor getParams: trả default an toàn, sanitize dữ liệu, không trả khoảng trắng
function dgw_sanitize_request_value($value)
{
    if (is_array($value)) {
        return array_map('dgw_sanitize_request_value', $value);
    }
    return sanitize_text_field(wp_unslash($value));
}

//==== GET PARAM TREN URL============================================
function getParams($name = null, $default = '')
{
    if ($name === null || $name === '') {
        return dgw_sanitize_request_value($_REQUEST);
    }

    $name = sanitize_key($name);

    if (!isset($_REQUEST[$name])) {
        return $default;
    }

    return dgw_sanitize_request_value($_REQUEST[$name]);
}

function custom_redirect($location = '')
{
    global $post_type;
    $url = admin_url('edit.php?post_type=' . rawurlencode($post_type));

    if (empty($location)) {
        return $url;
    }

    if (is_array($location)) {
        return add_query_arg($location, $url);
    }

    $location = ltrim($location, '&?');
    if (strpos($location, '=') !== false) {
        return $url . '&' . $location;
    }

    return $url;
}

//============= KIEM DU LIEU CHUYEN QUA BANG PHUONG POST HAY GET======================
function isPost()
{
    return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
}





function toBack($num)
{
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $page = isset($_REQUEST['page']) ? sanitize_key($_REQUEST['page']) : '';
    $url = admin_url('admin.php?page=' . $page . '&paged=' . $paged . '&msg=' . intval($num));
    wp_redirect($url);
    exit;
}

// [2026-07-09] - Cập nhật style / cú pháp: sửa exit;; và chỉnh format login logo
//======= THAY DOI LOGO DANG NHAP O ADMIN =====================================================
// if (!is_admin()) {

function custom_login_logo()
{
    echo '<style type="text/css">\n'
        . '    h1 a { background-image: url(' . esc_url(PART_IMAGES . 'logo.png') . ') !important; }\n'
        . '</style>';
}

add_action('login_head', 'custom_login_logo');
// } else {
//     require_once DIR_HELPER . 'code/code-add-media.php';
//     require_once DIR_HELPER . 'code/code-upload-file.php';
// }

// [2026-07-09] - Refactor SEO output to use WordPress title/meta hooks and avoid nested title functions.
add_filter('document_title_parts', 'dgw_seo_document_title_parts');
add_action('wp_head', 'dgw_seo_meta_tags');

function dgw_seo_document_title_parts($title_parts)
{
    $site_name = get_option('company_name_vn') ?: get_bloginfo('name');
    $page_name = get_query_var('pagename');
    $title = '';

    if (is_home() || is_front_page()) {
        $title = $site_name;
    } elseif (is_single() || is_page()) {
        $cate = get_query_var('cate');
        $sp = get_query_var('sp');

        if (empty($cate) && empty($sp)) {
            $title = trim('Digiwin ' . $page_name);
        } elseif (!empty($cate)) {
            $cate_obj = get_category(intval($cate));
            if ($cate_obj) {
                $title = $cate_obj->name . ' Digiwin';
            }
        } elseif (!empty($sp)) {
            $proArr = get_product($sp);
            if (!empty($proArr['seo_title'])) {
                $title = $proArr['seo_title'] . ' Digiwin';
            }
        }
    } elseif (is_tax() || is_tag() || is_category()) {
        $term = get_queried_object();
        if ($term && isset($term->term_id)) {
            $term_meta = get_option('taxonomy_' . $term->term_id);
            $strSeoTitle = $term_meta['txtTitleSeo'] ?? '';
            if (empty($strSeoTitle)) {
                $title = $site_name;
            } else {
                $title = $site_name . ' - ' . $strSeoTitle;
            }
        }
    }

    if (!empty($title)) {
        $title_parts['title'] = $title;
    }

    return $title_parts;
}

function dgw_seo_meta_tags()
{
    if (defined('RANK_MATH_VERSION')) {
        return;
    }

    $site_name = get_option('company_name_vn') ?: get_bloginfo('name');
    $site_description = get_option('company_name_vn') . ' - ' . get_option('company_address_vn');
    $site_keywords = get_option('company_name_vn');
    $description = '';
    $keywords = '';
    $page_name = get_query_var('pagename');

    if (is_home() || is_front_page()) {
        $description = $site_description;
        $keywords = $site_keywords;
    } elseif (is_single() || is_page()) {
        $cate = get_query_var('cate');
        $sp = get_query_var('sp');

        if (empty($cate) && empty($sp)) {
            $description = $site_description;
            $keywords = trim($page_name . ', ' . $site_name, ', ');
        } elseif (!empty($cate)) {
            $cate_obj = get_category(intval($cate));
            $category_name = $cate_obj ? $cate_obj->name : '';
            $description = 'beautiful, luggage, ' . $site_description . ' - ' . $category_name;
            $keywords = trim('beautiful, luggage, ' . $page_name . ', ' . $site_name . ', ' . $category_name, ', ');
        } elseif (!empty($sp)) {
            $proArr = get_product($sp);
            $description = $proArr['seo_description'] ?? $site_description;
            $keywords = !empty($proArr['seo_key']) ? 'Beautiful, ' . $proArr['seo_key'] : $site_keywords;
        }
    } elseif (is_tax() || is_tag() || is_category()) {
        $term = get_queried_object();
        if ($term && isset($term->term_id)) {
            $term_meta = get_option('taxonomy_' . $term->term_id);
            $description = $term_meta['strDescriptionSeo'] ?? $site_description;
            $keywords = $term_meta['seo_keywords'] ?? $site_keywords;
        }
    }

    if (!empty($description)) {
        echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
    }
    if (!empty($keywords)) {
        echo '<meta name="keywords" content="' . esc_attr($keywords) . '" />' . "\n";
    }
}

function uploadFileDownLoad($File, $name)
{
    if (empty($File['file_upload']['name'])) {
        return '';
    }

    if (!empty($File['file_upload']['error']) && $File['file_upload']['error'] !== UPLOAD_ERR_OK) {
        return 'Upload error: ' . intval($File['file_upload']['error']);
    }

    $file_name = sanitize_file_name($File['file_upload']['name']);
    $file_size = intval($File['file_upload']['size']);
    $file_tmp = $File['file_upload']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed_ext = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'txt', 'csv', 'png', 'jpg', 'jpeg', 'gif');
    if (!in_array($file_ext, $allowed_ext, true)) {
        return '不支援的檔案格式';
    }

    if ($file_size > 10097152) {
        return '上傳檔案容量不可大於 10 MB';
    }

    $upload_dir = untrailingslashit(DIR_FILE) . DS;
    if (!file_exists($upload_dir) && !wp_mkdir_p($upload_dir)) {
        return '無法建立上傳資料夾';
    }

    if (!empty($name) && is_file($upload_dir . $name)) {
        unlink($upload_dir . $name);
    }

    $destination_name = wp_unique_filename($upload_dir, $file_name);
    $destination = $upload_dir . $destination_name;

    if (move_uploaded_file($file_tmp, $destination)) {
        return $destination_name;
    }

    return 'File upload failed.';
}

// Mục đích của đoạn code:
// Kiểm tra nếu người dùng đã đăng nhập vào WordPress.
// Đẩy biến is_internal: 'yes' lên DataLayer.
// Dựa vào biến này, mình sẽ cấu hình trên Google Tag Manager để chặn không kích hoạt thẻ GA4, giúp dữ liệu báo cáo sạch hơn.

add_action('wp_head', function () {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $role = (array) $user->roles;
    ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'user_status',
                'user_role': '<?php echo $role[0]; ?>',
                'is_internal': 'yes'
            });
        </script>
<?php
    }
});


add_action('wp_enqueue_scripts', 'remove_wp_block_library_css', 100);
function remove_wp_block_library_css()
{
    wp_dequeue_style('wp-block-library');          // 移除核心區塊樣式
    wp_dequeue_style('wp-block-library-theme');    // 移除主題區塊樣式
    wp_dequeue_style('wc-block-style');            // 如果有 WooCommerce，移除其區塊樣式
}



add_action('after_setup_theme', function () {
    add_image_size('casestudies-desktop', 410, 307, true); // crop đúng tỉ lệ
    add_image_size('casestudies-mobile', 150, 113, true);
    add_image_size('slider-desktop', 1920, 800, true);
    add_image_size('slider-mobile',  768,  500, true);
    add_image_size('card-desktop', 300, 200, true);
    add_image_size('card-mobile',  480, 320, true);
});


// Cho phép inline styles trong table
add_filter( 'safe_style_css', function( $styles ) {
    $styles[] = 'background-color';
    $styles[] = 'color';
    return $styles;
});

add_filter( 'wp_kses_allowed_html', function( $allowedposttags ) {
    $allowedposttags['table']['style'] = true;
    $allowedposttags['tr']['style'] = true;
    $allowedposttags['td']['style'] = true;
    $allowedposttags['th']['style'] = true;
    return $allowedposttags;
}, 10, 1 );


// ==================== EMAIL DEBUG ====================
// Bật debug cho PHPMailer - Chỉ khi WP_DEBUG = true
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('phpmailer_init', function($phpmailer) {
        // Bật debug mode
        $phpmailer->SMTPDebug = 2; // 0=off, 1=client, 2=client+server
        
        // Ghi log debug output
        $phpmailer->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: " . $str);
        };
    });
}


// Date: 2026-06-23
// Chức năng: Thêm thẻ meta noindex, nofollow riêng cho trang 'member' để chặn Google index
add_action( 'wp_head', 'custom_noindex_member_page' );
function custom_noindex_member_page() {
    // Kiểm tra slug của trang hiện tại, thay 'member' bằng slug chính xác nếu cần
    if ( is_page( 'member' ) ) {
        echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
    }
}

