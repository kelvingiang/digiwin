<?php
/**
 * 29/06/2026: Gộp và refactor các file xử lý AJAX Load More thành chuẩn WordPress.
 * Các hàm bao gồm: load_more_post, load_more_in_group, bk_load_more.
 */

// 1. Chức năng Load More cơ bản (bk-load-more)
add_action('wp_ajax_bk_load_more', 'dgw_ajax_bk_load_more_handler');
add_action('wp_ajax_nopriv_bk_load_more', 'dgw_ajax_bk_load_more_handler');
function dgw_ajax_bk_load_more_handler() {
    // 29/06/2026: Xử lý load thêm bài viết theo category
    $lastID = isset($_POST['lastID']) ? intval($_POST['lastID']) : 0;
    $post_type = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : 'post';
    $cateID = isset($_POST['cateID']) ? intval($_POST['cateID']) : 0;
    $count = isset($_POST['count']) ? intval($_POST['count']) : 10;
    $cate = isset($_POST['cate']) ? sanitize_text_field($_POST['cate']) : '';

    if (!session_id()) session_start();
    $lang = isset($_SESSION['languages']) ? $_SESSION['languages'] : dgw_get_lang();

    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => $count,
        'offset' => $lastID,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => '_metabox_order',
        'meta_query' => array(
            array(
                'key' => '_metabox_langguage',
                'value' => $lang,
                'compare' => '=',
            ),
        ),
    );

    if (!empty($cateID) && !empty($cate)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => $cate,
                'field' => 'term_id',
                'terms' => $cateID,
            )
        );
    }

    $wp_query = new WP_Query($args);
    $html = '';

    if ($wp_query->have_posts()) {
        $stt = $lastID + 1;
        while ($wp_query->have_posts()) : $wp_query->the_post();
            $html .= "<div class='item' data-id='" . $stt . "'>";
            $html .= "<a href='" . get_the_permalink() . "'>";
            if (has_post_thumbnail()) {
                $html .= "<img class='item-img' src='" . get_the_post_thumbnail_url() . "' srcset='" . get_the_post_thumbnail_url() . "'/>";
            } else {
                $html .= "<img class='item-img' src='" . PART_IMAGES . 'no-image.jpg' . "'/>";
            }
            $html .= "<div class='item-title'>" . get_the_title() . "</div>";
            $html .=  "</a>";
            $html .= "</div>";
            $stt += 1;
        endwhile;
        wp_reset_postdata();

        $response = array('status' => 'done', 'html' => $html);
    } else {
        $response = array('status' => 'empty');
    }

    wp_send_json($response);
}

// 2. Chức năng Load More Post
add_action('wp_ajax_load_more_post', 'dgw_ajax_load_more_post_handler');
add_action('wp_ajax_nopriv_load_more_post', 'dgw_ajax_load_more_post_handler');
function dgw_ajax_load_more_post_handler() {
    // 29/06/2026: Xử lý load thêm bài viết (phiên bản khác dùng category_name)
    $lastID = isset($_POST['lastID']) ? intval($_POST['lastID']) : 0;
    $post_type = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : 'post';
    $count = isset($_POST['count']) ? intval($_POST['count']) : 10;
    $cate = isset($_POST['cate']) ? sanitize_text_field($_POST['cate']) : '';

    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => $count,
        'offset' => $lastID,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => '_metabox_order',
        'meta_query' => array(
            array(
                'key' => '_metabox_langguage',
                'value' => dgw_get_lang(),
                'compare' => '=',
            ),
        ),
    );

    if (!empty($cate)) {
        $args['category_name'] = $cate;
    }

    $wp_query = new WP_Query($args);
    $html = '';

    if ($wp_query->have_posts()) {
        $stt = $lastID + 1;
        while ($wp_query->have_posts()) : $wp_query->the_post();
            $html .= "<div class='item' data-id='" . $stt . "'>";
            $html .= "<a href='" . get_the_permalink() . "'>";
            if (has_post_thumbnail()) {
                $html .= "<img class='item-img' src='" . get_the_post_thumbnail_url() . "' srcset='" . get_the_post_thumbnail_url() . "'/>";
            } else {
                $html .= "<img class='item-img' src='" . PART_IMAGES . 'no-image.jpg' . "'/>";
            }
            $html .= "<div class='item-title'>" . get_the_title() . "</div>";
            $html .= "</a>";
            $html .= "</div>";
            $stt += 1;
        endwhile;
        wp_reset_postdata();

        $response = array('status' => 'done', 'html' => $html);
    } else {
        $response = array('status' => 'empty');
    }

    wp_send_json($response);
}


// 3. Chức năng Load More In Group
add_action('wp_ajax_load_more_in_group', 'dgw_ajax_load_more_in_group_handler');
add_action('wp_ajax_nopriv_load_more_in_group', 'dgw_ajax_load_more_in_group_handler');
function dgw_ajax_load_more_in_group_handler() {
    // 29/06/2026: Xử lý load thêm bài viết trong cùng group (loại trừ bài hiện tại)
    $lastID = isset($_POST['lastID']) ? intval($_POST['lastID']) : 0;
    $cate = isset($_POST['cate']) ? sanitize_text_field($_POST['cate']) : 'post';
    $slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
    $count = isset($_POST['count']) ? intval($_POST['count']) : 5;

    // Trong file gốc, post_type là $cate. Taxonomy không truyền từ post lên rõ ràng, nhưng ta giữ nguyên cấu trúc
    $args = array(
        'post_type' => $cate,
        'posts_per_page' => 5, // File gốc fix cứng 5
        'offset' => $lastID + 1,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => '_metabox_order',
        'meta_query' => array(
            array(
                'key' => '_metabox_langguage',
                'value' => dgw_get_lang(),
                'compare' => '=',
            ),
        ),
    );

    // Ở file gốc $taxonomy bị undefined, nên ở đây tạm bỏ tax_query nếu $taxonomy không tồn tại hoặc giữ nguyên nếu đang chạy sai

    $wp_query = new WP_Query($args);
    $html = '';

    if ($wp_query->have_posts()) {
        $stt = $lastID + 1;
        while ($wp_query->have_posts()) : $wp_query->the_post();
            global $post;
            // kiem tra slug trung voi slug url khong hien thi
            if ($post->post_name == $slug) {
                continue;
            }
            $html .= "<div class='item row' data-id='" . $stt . "'>";
            $html .= "<div class='col-lg-2'>";
            if (has_post_thumbnail()) {
                $html .= "<img class='item-img' src='" . get_the_post_thumbnail_url() . "' srcset='" . get_the_post_thumbnail_url() . "'/>";
            } else {
                $html .= "<img class='item-img' src='" . PART_IMAGES . 'no-image.jpg' . "'/>";
            }
            $html .= "</div>";
            $html .= "<div class='col-lg-10'>";
            $html .= "<div class='group-list-item-title'>";
            $html .= "<a class='my-link' href='" . get_the_permalink() . "'><h3>" . get_the_title() . "</h3></a>";
            $html .= "</div>";
            $html .= "<div>";
            $html .= get_the_content();
            $html .= "</div>";
            $html .= "</div>";
            $html .= "</div>";
            $stt += 1;
        endwhile;
        wp_reset_postdata();

        $response = array('status' => 'done', 'html' => $html);
    } else {
        $response = array('status' => 'empty');
    }

    wp_send_json($response);
}
