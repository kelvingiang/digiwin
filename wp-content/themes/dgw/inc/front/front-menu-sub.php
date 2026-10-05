<?php
/**
 * [02/10/2026] - Hiển thị ảnh đại diện và submenu điều hướng
 * Cập nhật: Sử dụng slug thay vì category ID trong URL (VD: /solution/cate/tiptop/tag/)
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
    foreach ($data as $val) {
        $slug_or_id = !empty($val['slug']) ? $val['slug'] : $val['ID'];
        echo '<div class="menu-sub-item" data-id="' . esc_attr($slug_or_id) . '" data-term-id="' . esc_attr((string)$val['ID']) . '">';
        echo '<a href="' . esc_url(home_url($val['page'] . '/cate/' . $slug_or_id . '/')) . '">';
        echo '<h2>' . esc_html($val['name']) . '</h2>';
        echo '</a>';
        echo '</div>';
    }
}