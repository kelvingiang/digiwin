<?php

/**
 * [2026-10-13] - @author: Kelvin - Hiển thị ảnh đại diện và submenu điều hướng (.menu-sub):
 * - Giữ nguyên thẻ <a> bọc <h2> cho các item đang active để người dùng tiện click quay lại category đó.
 * - Nhận diện tự động category active từ URL / Query Var hoặc từ bài viết đơn (single page).
 */

function pageImg($id)
{
    if (has_post_thumbnail($id)) :
        $image = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail');
        echo '<img class="page-img" src="' . esc_url($image[0]) . '" alt="' . esc_attr(get_the_title($id)) . '"/>';
    endif;
}

function menuSub($cate, $page)
{
    $data = getAllCategories($cate, 0, $page);
    if (empty($data)) {
        return;
    }

    // 1. Xác định category đang được chọn từ URL / Query Var
    $current_cate_slug = get_query_var('cate');
    if (empty($current_cate_slug) && is_singular()) {
        $terms = wp_get_object_terms(get_the_ID(), $cate);
        if (!empty($terms) && !is_wp_error($terms)) {
            $current_cate_slug = $terms[0]->slug;
        }
    }

    // 2. Render từng item với thẻ <a> bọc <h2>
    foreach ($data as $val) {
        $slug_or_id = !empty($val['slug']) ? (string)$val['slug'] : (string)$val['ID'];
        $is_active   = (!empty($current_cate_slug) && ((string)$current_cate_slug === (string)$slug_or_id || (string)$current_cate_slug === (string)$val['ID']));
        $active_cls  = $is_active ? ' active' : '';
        // 2026-10-19: Sử dụng span trên trang single, các trang khác dùng h2
        $tag = (is_single()) ? 'span' : 'h2';

        echo '<div class="menu-sub-item' . esc_attr($active_cls) . '" data-id="' . esc_attr($slug_or_id) . '" data-term-id="' . esc_attr((string)$val['ID']) . '">';
        echo '<a href="' . esc_url(home_url($val['page'] . '/cate/' . $slug_or_id . '/')) . '" class="' . esc_attr(trim($active_cls)) . '">';
        echo '<' . $tag . ' class="menu-title">' . esc_html($val['name']) . '</' . $tag . '>';
        echo '</a>';
        echo '</div>';
    }
}
