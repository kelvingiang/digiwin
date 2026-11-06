<?php
function register_my_session()
{
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'register_my_session');


add_action('wp_ajax_load_more_posts', 'ajax_load_more_posts');          // 已登入使用者
add_action('wp_ajax_nopriv_load_more_posts', 'ajax_load_more_posts');  // 未登入使用者

function ajax_load_more_posts()
{
    // 🔒 [2026-10-09] - @author: Kelvin - Hỗ trợ lọc category theo cả slug lẫn term_id, tránh bị intval ép về 0
    // 🔒 [2026-11-05] - Hỗ trợ gom nhóm Translation Group và giữ thẻ gốc div.item tương thích selector scroll
    $lastID   = isset($_POST['lastID']) ? intval($_POST['lastID']) : 0;
    $post     = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : 'post';
    $raw_cate = isset($_POST['cateID']) ? sanitize_text_field(wp_unslash($_POST['cateID'])) : '';
    $cateID   = is_numeric($raw_cate) ? intval($raw_cate) : $raw_cate;
    $count    = isset($_POST['count']) ? intval($_POST['count']) : 5;
    $cate     = isset($_POST['cate']) ? sanitize_text_field($_POST['cate']) : '';

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

    // ✅ [2026-10-09 / 2026-11-05]: Hỗ trợ gom nhóm Translation Group khi lọc theo category
    if (!empty($cateID)) {
        $target_terms = array($cateID);
        $field = is_numeric($cateID) ? 'term_id' : 'slug';

        $term_obj = is_numeric($cateID)
            ? get_term((int) $cateID, $cate)
            : get_term_by('slug', (string) $cateID, $cate);

        if ($term_obj && !is_wp_error($term_obj)) {
            $group_id = get_term_meta($term_obj->term_id, 'translation_group', true);
            if (!empty($group_id)) {
                $group_terms = get_terms(array(
                    'taxonomy'   => $cate,
                    'fields'     => 'ids',
                    'hide_empty' => false,
                    'meta_query' => array(
                        array(
                            'key'     => 'translation_group',
                            'value'   => $group_id,
                            'compare' => '=',
                        ),
                    ),
                ));
                if (!empty($group_terms) && !is_wp_error($group_terms)) {
                    $target_terms = $group_terms;
                    $field        = 'term_id';
                }
            }
        }

        $args['tax_query'] = array(
            array(
                'taxonomy' => $cate,
                'field'    => $field,
                'terms'    => $target_terms,
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
            $post_id  = get_the_ID();
            $thumb_id = get_post_thumbnail_id($post_id);
            $url_data = wp_get_attachment_image_src($thumb_id, 'medium');
            $srcset   = wp_get_attachment_image_srcset($thumb_id, 'medium');
            $permalink = get_the_permalink($post_id);

            // [2026-11-05] Thẻ div.item chuẩn SEO kèm link a bên trong
            $html .= "<div class='item'  
                           data-id='" . esc_attr((string) $stt) . "' 
                           data-link='" . esc_url($permalink) . "' 
                           data-post='" . esc_attr((string) $post_id) . "'>";
            $html .= "<div>";
            $html .= "<a href='" . esc_url($permalink) . "' style='display:block; text-decoration:none; color:inherit;'>";
            if (has_post_thumbnail()) {
                $html .= "<img class='item-img' 
                         src='" . esc_url($url_data[0]) . "' 
                         srcset='" . esc_attr((string) $srcset) . "'
                         fetchpriority='high'
                         width='" . esc_attr((string) $url_data[1]) . "'
                         height='" .  esc_attr((string) $url_data[2]) . "' 
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
            $html .= "</a>";
            // 取得 template part 的輸出（用 buffer 捕獲）
            ob_start();
            get_template_part('templates/view-comment');
            $comment_html = ob_get_clean();
            $html .= $comment_html;
            $html .= "</div>";

            $html .= "<div class='item-title'><a href='" . esc_url($permalink) . "' style='text-decoration:none; color:inherit; display:block; width:100%;'><h3>" . esc_html(get_the_title()) . "</h3></a></div>";
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

// [2026-10-12] - @author: Kelvin - Tối ưu hóa API plus_one_like: validate post ID, sanitize và trả về count mới nhất cho client
add_action('wp_ajax_plus_one_like', 'plus_one_like');          // 已登入使用者
add_action('wp_ajax_nopriv_plus_one_like', 'plus_one_like');

function plus_one_like()
{
    $postID = isset($_POST['postID']) ? absint($_POST['postID']) : 0;
    if ($postID <= 0 || !get_post_status($postID)) {
        wp_send_json_error(array('status' => 'error', 'message' => 'Invalid Post ID'));
        wp_die();
    }

    $current_likes = (int) get_post_meta($postID, '_metabox_like', true);
    $new_likes     = $current_likes + 1;
    update_post_meta($postID, '_metabox_like', $new_likes);

    wp_send_json_success(array(
        'status' => 'done',
        'count'  => $new_likes,
        'html'   => $new_likes,
    ));
    wp_die();
}

// [2026-10-06] - Đã chuyển chức năng change_languages AJAX sang file inc/ajax/ajax-language.php để quản lý tập trung và tránh trùng lặp hook.

// AJAX 驗證混合題
add_action('wp_ajax_check_math_captcha', 'check_math_captcha');
add_action('wp_ajax_nopriv_check_math_captcha', 'check_math_captcha');

function check_math_captcha()
{
    session_start();

    $user_ans = isset($_POST['answer']) ? intval($_POST['answer']) : null;
    $real_ans = isset($_SESSION['comment_captcha_answer']) ? intval($_SESSION['comment_captcha_answer']) : null;

    if ($user_ans === $real_ans) {
        wp_send_json(['status' => 'ok']);
    } else {
        wp_send_json(['status' => 'fail']);
    }
}
