<?php
function register_my_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'register_my_session');


add_action('wp_ajax_load_more_posts', 'ajax_load_more_posts');          // 已登入使用者
add_action('wp_ajax_nopriv_load_more_posts', 'ajax_load_more_posts');  // 未登入使用者

function ajax_load_more_posts()
{
    // 🔒 安全清理輸入資料
    $lastID = isset($_POST['lastID']) ? intval($_POST['lastID']) : 0;
    $post = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : 'post';
    $cateID = isset($_POST['cateID']) ? intval($_POST['cateID']) : 0;
    $count = isset($_POST['count']) ? intval($_POST['count']) : 5;
    $cate = isset($_POST['cate']) ? sanitize_text_field($_POST['cate']) : '';

    // ✅ 基本查詢參數
    $args = array(
        'post_type'      => $post,
        'posts_per_page' => $count,
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'meta_key'       => '_metabox_order',
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'     => '_metabox_langguage',
                'value'   => dgw_get_lang(),
                'compare' => '=',
            ),
        ),
    );

    // ✅ 若有分類
    if (!empty($cateID)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => $cate,
                'field'    => 'term_id',
                'terms'    => $cateID,
            ),
        );
    }

    // ✅ offset / pagination 控制
    $args['offset'] = $lastID;

    $wp_query = new WP_Query($args);

    if ($wp_query->have_posts()) {
        $html = '';
        $stt = $lastID + 1;

        while ($wp_query->have_posts()) :
            $wp_query->the_post();
            $thumb_id = get_post_thumbnail_id(get_the_ID());
            $url_data = wp_get_attachment_image_src($thumb_id, 'medium');
            $srcset = wp_get_attachment_image_srcset($thumb_id, 'medium');
            $html .= "<div class='item'  
                           data-id='" . esc_attr($stt) . "' 
                           data-link ='" . esc_url(get_the_permalink()) . "' 
                           data-post='" . esc_attr(get_the_ID()) . "'>";
            $html .= "<div>";
            if (has_post_thumbnail()) {
                $html .= "<img class='item-img' 
                         src='" . esc_url($url_data[0]) . "' 
                         srcset='" . esc_attr($srcset) . "'
                         fetchpriority='high'
                         width='" . esc_attr($url_data[1]) . "'
                         height='" .  esc_attr($url_data[2]) . "' 
                         alt='" . esc_attr(get_the_title()) . "' />";
            } else {
                $html .= "<img class='item-img' 
                         src='" . esc_url(PART_IMAGES . 'no-image.jpg') . "' 
                         srcset='" . PART_IMAGES . 'no-image.jpg' . "'
                         fetchpriority='high'
                         width='410'
                         height='270'
                         alt='" . esc_attr(get_the_title()) . "' />";
            }
            // 取得 template part 的輸出（用 buffer 捕獲）
            ob_start();
            get_template_part('templates/template', 'view_comment');
            $comment_html = ob_get_clean();
            $html .= $comment_html;
            $html .= "</div>";

            $html .= "<div class='item-title'><h3>" . esc_html(get_the_title()) . "</h3></div>";
            $html .= "</div>";

            $stt++;
        endwhile;

        wp_reset_postdata();

        wp_send_json(array(
            'status' => 'done',
            'html'   => $html,
        ));
    } else {
        wp_send_json(array('status' => 'empty'));
    }

    wp_die();
}


//=====================================================================================================
add_action('wp_ajax_plus_one_view', 'plus_one_view');          // 已登入使用者
add_action('wp_ajax_nopriv_plus_one_view', 'plus_one_view');

function plus_one_view()
{
    $postID = isset($_POST['postID']) ? intval($_POST['postID']) : 0;
    $view = get_post_meta($postID, '_metabox_view', true) ? intval(get_post_meta($postID, '_metabox_view', true)) : 0;
    update_post_meta($postID, '_metabox_view', $view + 1);
    wp_send_json(array(
        'status' => 'done',
        'html'   => $view + 1,
    ));
}

//=====================================================================================================
add_action('wp_ajax_plus_one_like', 'plus_one_like');          // 已登入使用者
add_action('wp_ajax_nopriv_plus_one_like', 'plus_one_like');

function plus_one_like()
{
    $postID = isset($_POST['postID']) ? intval($_POST['postID']) : 0;
    $view = get_post_meta($postID, '_metabox_like', true) ? intval(get_post_meta($postID, '_metabox_like', true)) : 0;
    update_post_meta($postID, '_metabox_like', $view + 1);
    wp_send_json(array(
        'status' => 'done',
        // 'html'   => $view + 1,
    ));
}

// 註冊 AJAX 動作 (登入者與訪客皆可使用)
add_action('wp_ajax_change_languages', 'change_languages');
add_action('wp_ajax_nopriv_change_languages', 'change_languages');

function change_languages()
{
    $response = ['status' => 'error'];

    if (!empty($_POST['type'])) {
        // 邏輯判斷
        $lang = ($_POST['type'] === 'cn') ? 'cn' : 'vn';

        // 設定 Cookie (使用 WordPress 內建常數)
        setcookie(
            'site_lang',
            $lang,
            time() + YEAR_IN_SECONDS,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl() // 根據是否為 HTTPS 自動設定 Secure 屬性
        );

        // 確保當前請求也能讀取到
        $_COOKIE['site_lang'] = $lang;

        $response = ['status' => 'ok', 'current_lang' => $lang];
    }

    // 發送 JSON 並結束執行
    wp_send_json($response);
}

// AJAX 驗證混合題
add_action('wp_ajax_check_math_captcha', 'check_math_captcha');
add_action('wp_ajax_nopriv_check_math_captcha', 'check_math_captcha');

function check_math_captcha() {
    session_start();

    $user_ans = isset($_POST['answer']) ? intval($_POST['answer']) : null;
    $real_ans = isset($_SESSION['comment_captcha_answer']) ? intval($_SESSION['comment_captcha_answer']) : null;

    if ($user_ans === $real_ans) {
        wp_send_json(['status' => 'ok']);
    } else {
        wp_send_json(['status' => 'fail']);
    }
}



// lấy IP của mạng =========================
// function getUserIP() {
//     if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
//         $ip = $_SERVER['HTTP_CLIENT_IP'];
//     } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//         // 可能有多個IP，用第一個
//         $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
//     } else {
//         $ip = $_SERVER['REMOTE_ADDR'];
//     }
//     return trim($ip);
// }