<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Khởi tạo session đúng cách thông qua hook init
add_action('init', function() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}, 1); // Độ ưu tiên là 1 để chạy sớm nhất có thể

// @ini_set( 'upload_max_size' , '64M' );
// @ini_set( 'post_max_size', '64M');
// @ini_set( 'max_execution_time', '300' );

define('THEME_URL', get_stylesheet_directory());  // hang lay path thu muc theme
define('THEME_PART', get_stylesheet_directory_uri());
define('DS', DIRECTORY_SEPARATOR);  // phan nay thay doi dau / theo he dieu hanh khac nhau giua window va linx
define('DIR_HELPER', THEME_URL . DS . 'helper' . DS);

require_once(DIR_HELPER . 'define.php');
require_once(DIR_HELPER . 'style.php');
require_once(DIR_HELPER . 'function.php');
require_once(DIR_HELPER . 'require.php');



add_action('init', function () {
    if (isset($_GET['mail_test'])) {
        $ok = wp_mail(
            'giaminh0265@gmail.com',
            'Test Mail',
            'This is a test email'
        );

        echo $ok ? '✅ mail() sent' : '❌ mail() failed';
        exit;
    }
});

add_action('init', function () {
    // 1. 新增這段：專門處理 AJAX 請求 (POST)
    if (wp_doing_ajax() && isset($_POST['lang'])) {
        // 確保值只能是 'cn' 或 'vn'
        $lang = ($_POST['lang'] === 'cn') ? 'cn' : 'vn';
        
        // 第一時間強制覆寫 Cookie 變數，讓後續的 dgw_get_lang() 讀取到正確的值
        $_COOKIE['site_lang'] = $lang; 
        return; // AJAX 請求不需要執行後面的 setcookie 給瀏覽器
    }

    // 2. 原本的程式碼：處理一般頁面請求 (GET)
    if (isset($_GET['lang'])) {
        $lang = ($_GET['lang'] === 'cn') ? 'cn' : 'vn';

        setcookie(
            'site_lang',
            $lang,
            time() + YEAR_IN_SECONDS,
            '/'
        );

        $_COOKIE['site_lang'] = $lang;
    }
}, 1);


add_filter('language_attributes', function ($output) {
    $lang = dgw_get_lang();
    if ($lang === 'cn') {
        return 'lang="zh-TW"';
    }
    return 'lang="vi-VN"';
});

//=== khi cài Divi Builder sẽ tự tạo project  post-type câu dưới là bỏ đi cái post-type đó ================== 
add_action('init', function () {
    unregister_post_type('project');
}, 1000);

/* ==============================================================
  THAY DOI FILE DATA NGON NGU THEO SESSION LANGGUAGE
  =============================================================== */

function change_translate_text($translated)
{
    $lang = dgw_get_lang();
    $languages = ($lang === 'cn') ? 'zh_TW' : 'vi_VN';

    if (is_admin()) {
        $file = dirname(dirname(dirname(__FILE__))) . "/languages/admin_languages/data.php";
    } else {
        $file = dirname(dirname(dirname(__FILE__))) . "/languages/{$languages}/data.php";
    }

    if (file_exists($file)) {
        include_once $file;

        if (function_exists('getTranslate')) {
            $data = getTranslate();

            if (isset($data[$translated])) {
                return $data[$translated];
            }
        }
    }

    return $translated;
}
add_filter('gettext', 'change_translate_text', 20);

/* =======================================  
  FUNCTION OF THIS TEMPLATE
  ======================================= */

add_action('after_setup_theme', 'dgw_setup');
function dgw_setup()
{
    load_theme_textdomain('blankslate', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('automatic-feed-links');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form'));
    global $content_width;
    if (!isset($content_width)) {
        $content_width = 1920;
    }
    register_nav_menus(array('main-menu' => esc_html__('Main Menu', 'blankslate')));
}

add_action('wp_enqueue_scripts', 'dgw_load_scripts');
function dgw_load_scripts()
{
    wp_enqueue_style('blankslate-style', get_stylesheet_uri());
    wp_enqueue_script('jquery');
}

add_action('wp_footer', 'dgw_footer_scripts');

function dgw_footer_scripts()
{
?>
    <script>
        jQuery(document).ready(function($) {
            var deviceAgent = navigator.userAgent.toLowerCase();
            if (deviceAgent.match(/(iphone|ipod|ipad)/)) {
                $("html").addClass("ios");
                $("html").addClass("mobile");
            }
            if (navigator.userAgent.search("MSIE") >= 0) {
                $("html").addClass("ie");
            } else if (navigator.userAgent.search("Chrome") >= 0) {
                $("html").addClass("chrome");
            } else if (navigator.userAgent.search("Firefox") >= 0) {
                $("html").addClass("firefox");
            } else if (navigator.userAgent.search("Safari") >= 0 && navigator.userAgent.search("Chrome") < 0) {
                $("html").addClass("safari");
            } else if (navigator.userAgent.search("Opera") >= 0) {
                $("html").addClass("opera");
            }
        });
    </script>
<?php
}

add_filter('document_title_separator', 'dgw_document_title_separator');

function dgw_document_title_separator($sep)
{
    $sep = '|';
    return $sep;
}

add_filter('the_title', 'dgw_title');

function dgw_title($title)
{
    if ($title == '') {
        return '...';
    } else {
        return $title;
    }
}

add_filter('the_content_more_link', 'dgw_read_more_link');

function dgw_read_more_link()
{
    if (!is_admin()) {
        return ' <a href="' . esc_url(get_permalink()) . '" class="more-link">...</a>';
    }
}

add_filter('excerpt_more', 'dgw_excerpt_read_more_link');

function dgw_excerpt_read_more_link($more)
{
    if (!is_admin()) {
        global $post;
        return ' <a href="' . esc_url(get_permalink($post->ID)) . '" class="more-link">...</a>';
    }
}

add_filter('intermediate_image_sizes_advanced', 'dgw_image_insert_override');

function dgw_image_insert_override($sizes)
{
    unset($sizes['medium_large']);
    return $sizes;
}

add_action('widgets_init', 'dgw_widgets_init');

function dgw_widgets_init()
{
    register_sidebar(array(
        'name' => esc_html__('Sidebar Widget Area', 'blankslate'),
        'id' => 'primary-widget-area',
        'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}

add_action('wp_head', 'dgw_pingback_header');

function dgw_pingback_header()
{
    if (is_singular() && pings_open()) {
        printf('<link rel="pingback" href="%s" />' . "\n", esc_url(get_bloginfo('pingback_url')));
    }
}

add_action('comment_form_before', 'dgw_enqueue_comment_reply_script');

function dgw_enqueue_comment_reply_script()
{
    if (get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}

function dgw_custom_pings($comment)
{
?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>"><?php echo comment_author_link(); ?></li>
<?php
}

add_filter('get_comments_number', 'dgw_comment_count', 0);

function dgw_comment_count($count)
{
    if (!is_admin()) {
        global $id;
        $get_comments = get_comments('status=approve&post_id=' . $id);
        $comments_by_type = separate_comments($get_comments);
        return count($comments_by_type['comment']);
    } else {
        return $count;
    }
}


add_action('phpmailer_init', function ($phpmailer) {

    $phpmailer->isSMTP();

    $phpmailer->Host       = SMTP_HOST;
    $phpmailer->Port       = SMTP_PORT;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Username   = SMTP_USERNAME;
    $phpmailer->Password   = SMTP_PASSWORD;
    $phpmailer->SMTPSecure = SMTP_SECURE;

    // 🔥 開啟完整 SMTP Debug
    $phpmailer->SMTPDebug  = 3;
    $phpmailer->Debugoutput = function ($str, $level) {
        error_log("SMTP DEBUG [$level]: $str");
    };
});

// add_action('init', function () {

//     if (!isset($_GET['testmail'])) {
//         return;
//     }

//      error_log('>>> TESTMAIL INIT TRIGGERED <<<');

//     $sent = wp_mail(
//         get_option('admin_email'),
//         'WP Mail Test',
//         'This is a test email'
//     );

//     if ($sent) {
//         wp_die('✅ Mail sent');
//     } else {
//         wp_die('❌ Mail failed');
//     }
// });

