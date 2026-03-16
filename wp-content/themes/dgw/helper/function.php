<?php
// require_once DIR_HELPER . 'code/function-front-in-group.php';
require_once DIR_HELPER . 'code/function-front-custom-post.php';
require_once DIR_HELPER . 'code/function-front-menu-side.php';
require_once DIR_HELPER . 'code/function-front-menu-sub.php';
require_once DIR_HELPER . 'code/function-front.php';
require_once DIR_HELPER . 'code/function-front-category.php';
require_once DIR_HELPER . 'code/function-front-menu.php';

require_once DIR_HELPER . 'code/admin-add-post-tag-field.php';
require_once DIR_HELPER . 'code/admin-add-post-taxonomy-field.php';
require_once DIR_HELPER . 'code/admin-add-filter.php';
require_once DIR_HELPER . 'code/admin-custom-columns.php';

require_once DIR_HELPER . 'code/function-ajax.php';
require_once DIR_HELPER . 'code/function-wp-send-mail.php';
require_once DIR_HELPER . 'code/function-custom-comment.php';


function dgw_get_lang()
{
    return $_COOKIE['site_lang'] ?? 'vn';
}

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

/* ==============================================================
  CHECK THE ARRAY IS NULL
  =============================================================== */

function MenuMain($arr, $class = "menu-main-item", $item_link = 'menu-main-item-link', $item_bg = 'menu-main-item-bg', $hassub = 'has-sub')
{
    foreach ($arr as $key => $val) :
?>
        <div class="<?php echo $class ?>">
            <a href="<?php echo home_url($key) ?>"
                class="<?php echo $item_link ?> <?php echo is_array($val['sub']) ? $hassub : '' ?>">
                <?php echo $val[dgw_get_lang()] ?>
            </a>
            <div class="<?php echo $item_bg ?>"></div>

            <?php if (is_array($val['sub'])) : ?>
                <div class="<?php echo $val['class'] ?>">
                    <!--/====== AP DUNG DEQUY CHO MENU NHIEU CAPV ================================================-->
                    <?php MenuMain($val['sub'], $val['class'] . '-item', $val['class'] . '-item-link', $val['class'] . '-item-bg', 'has-sub-sub'); ?>
                </div>
            <?php endif ?>
        </div>
    <?php
    endforeach;
}

function MenuMobile($arr, $item_link = 'menu-mobile-item-link')
{
    foreach ($arr as $key => $val) :
    ?>
        <a href="<?php echo home_url($key) ?>" style="  " class="<?php echo $item_link ?>">
            <?php echo $val[dgw_get_lang()] ?>
        </a>
    <?php
    endforeach;
}

//====== SAP LAI ARRAY THEO THU TU GIAM DAN AP DUNG CATEGORY =================
function cmp($a, $b)
{
    return strcmp($b['order'], $a['order']);
}

//==== GET PARAM TREN URL============================================
function getParams($name = null)
{
    if ($name == null || empty($name)) {
        return $_REQUEST; // TRA VE GIA TRI REQUEST
    } else {
        // TRUONG HOP name DC CHUYEN VAO 
        // KIEM TRA name CO TON TAI TRA VE name NGUOI ''
        $val = (isset($_REQUEST[$name])) ? $_REQUEST[$name] : ' ';
        return $val;
    }
}

function custom_redirect($location)
{
    global $post_type;
    $location = admin_url('edit.php?post_type=' . $post_type);
    return $location;
}

//============= KIEM DU LIEU CHUYEN QUA BANG PHUONG POST HAY GET======================
function isPost()
{
    $flag = ($_SERVER['REQUEST_METHOD'] == 'POST') ? TRUE : FALSE;
    return $flag;
}

//==== FUNCTION SHOW SUB CONTENT============================================
function mySubContent($data)
{
    $str = explode('<!--more-->', $data);
    return $str[0] . '....';
}

function getImage($name = '')
{
    return PART_IMAGES . $name;
}

//===============FUNCTION =================
function createRandom($length)
{
    //$characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $characters = "0123456789";
    $charsLength = strlen($characters) - 1;
    $string = "";
    for ($i = 0; $i < $length; $i++) {
        $randNum = mt_rand(0, $charsLength);
        $string .= $characters[$randNum];
    }
    return $string;
}

function toBack($num)
{
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $page = isset($_REQUEST['page']) ? sanitize_key($_REQUEST['page']) : '';
    $url = admin_url('admin.php?page=' . $page . '&paged=' . $paged . '&msg=' . intval($num));
    wp_redirect($url);
    exit;;
}

//======= THAY DOI LOGO DANG NHAP O ADMIN =====================================================
if (!is_admin()) {

    // custom admin login logo
    function custom_login_logo()
    {
        echo '<style type="text/css">
	h1 a { background-image: url(' . PART_IMAGES . 'logo.png' . ') !important; }
	</style>';
    }

    add_action('login_head', 'custom_login_logo');
} else {
    require_once DIR_HELPER . 'code/function-add-media.php';
    require_once DIR_HELPER . 'code/function-upload-file.php';
}

// ====================FUNCTION SEO=========================================================== 
function seo()
{
    //  global $suite;
    global $suite;
    $suite = array(
        'txtTitleSeo' => get_option('company_name_vn'),
        'strDescriptionSeo' => get_option('company_name_vn') . '-' . get_option('company_address_vn'),
        'strKeywordsSeo' => get_option('company_name_vn'),
        'strPageName' => get_query_var('pagename'),
    );
    if (is_home() == true) {

        // THE DOI GIA TRI CUA TITLE WP_HEAD
        function custom_title()
        {
            global $suite;
            return $suite['txtTitleSeo'];
        }

        add_filter('wp_title', 'custom_title');
        echo '<title>' . $suite['txtTitleSeo'] . '</title>';
        echo '<meta name="description" content="' . $suite['strDescriptionSeo'] . '" />';
        echo '<meta name="keywords" content="' . $suite['strKeywordsSeo'] . '" />';
    } else if (is_single() || is_page()) {
        /* ==============================
          global $post;
          $strSeoTitle = get_post_meta($post->ID, '_seo_title', true);
          $strSeoDescription = get_post_meta($post->ID, '_seo_description', true);
          $strSeoKeywords = get_post_meta($post->ID, '_seo_key', true);

          global $strTitle;
          if (empty($strSeoTitle) != false) {
          $strTitle = $suite['txtTitleSeo'] . '-' . get_query_var('pagename');
          } else {
          $strTitle = $suite['txtTitleSeo'] . ' - ' . $strSeoTitle;
          }

          // THE DOI GIA TRI CUA TITLE WP_HEAD
          function custom_title() {
          global $strTitle;
          return $strTitle;
          }
         */

        $cate = get_query_var('cate');
        $sp = get_query_var('sp');

        if (empty($cate) && empty($sp)) {
            add_filter('wp_title', 'custom_title');
            echo '<title> Digiwin' . $suite['strPageName']  . '</title>';
            echo '<meta name="description" content="' . $suite['strDescriptionSeo'] . '" />';
            echo '<meta name="keywords" content="' . $suite['strPageName'] . ', ' . $suite['txtTitleSeo'] . '" />';
        } elseif (!empty($cate)) {
            $cateArr = get_category_by_id($cate);
            add_filter('wp_title', 'custom_title');
            echo '<title>' . $cateArr['name_vn'] . ' Digiwin</title>';
            echo '<meta name="description" content="beautiful, luggage,' . $suite['strDescriptionSeo'] . ' - ' . $cateArr['name_vn'] . '" />';
            echo '<meta name="keywords" content="beautiful, luggage,' . $suite['strPageName'] . ', ' . $suite['txtTitleSeo'] . ', ' . $cateArr['name_vn'] . '" />';
        } elseif (!empty($sp)) {
            $proArr = get_product($sp);
            add_filter('wp_title', 'custom_title');
            echo '<title> ' . $proArr['seo_title'] . ' Digiwin</title>';
            echo '<meta name="description" content="' . $proArr['seo_description'] . '" />';
            echo '<meta name="keywords" content="' . 'Beautiful, ' . $proArr['seo_key'] . '" />';
        }
    } else if (is_tax() || is_tag() || is_category()) {
        global $taxonomy, $term;
        $term = get_term_by('slug', $term, $taxonomy);
        $term_id = $term->term_id;
        $term_meta = get_option("taxonomy_$term_id");

        $strSeoTitle = $term_meta['txtTitleSeo'];
        $strSeoDescription = $term_meta['strDescriptionSeo'];
        $strSeoKeywords = $term_meta['seo_keywords'];

        if (empty($strSeoTitle) != false) {
            $strTitle = $suite['txtTitleSeo'];
        } else {
            $strTitle = $suite['txtTitleSeo'] . ' - ' . $strSeoTitle;
        }

        // THE DOI GIA TRI CUA TITLE WP_HEAD
        function custom_title()
        {
            global $strTitle;
            return $strTitle;
        }

        add_filter('wp_title', 'custom_title');
        echo '<title>' . $strTitle . '</title>';
        echo '<meta name="description" content="' . $strSeoDescription . '" />';
        echo '<meta name="keywords" content="' . $strSeoKeywords . '" />';
    }
    echo '<meta name="robots" content="INDEX, FOLLOW" />';
    echo '<meta http-equiv="REFRESH" content="1800" />';
}

function uploadFileDownLoad($File, $name)
{

    if (!empty($File['file_upload']['name'])) {
        $errors = array();
        $file_name = $File['file_upload']['name'];
        $file_size = $File['file_upload']['size'];
        $file_tmp = $File['file_upload']['tmp_name'];
        //$file_type = $File['file_upload']['type'];
        //$file_trim = ((explode('.', $File['file_upload']['name'])));
        //$trim_name = strtolower($file_trim[0]);
        //$trim_type = strtolower($file_trim[1]);

        $cus_name = $file_name;

        if ($file_size > 10097152) {
            $errors[] = '上傳檔案容量不可大於 10 MB';
        }
        $path = DIR_FILE; /* get function path upload img dc khai bao tai file hepler */

        if (empty($errors) == true) {
            if (is_file(DIR_FILE . $name)) {
                unlink(DIR_FILE . $name);
            }

            move_uploaded_file($file_tmp, ($path . $cus_name));
            return $cus_name;
        } else {
            return $errors;
        }
    }
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
