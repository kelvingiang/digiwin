<?php
/**
 * [02/10/2026] - Bổ sung slug vào danh sách category trả về
 * Mục đích: Cho phép tạo đường dẫn thân thiện SEO (VD: /cate/tiptop/tag/ thay vì /cate/25/tag/).
 */

function getCategories($cate)
{
    $arr = array();
    $argsCate = array(
        'type' => 'post',
        'number' => 100,
        'taxonomy' => $cate,
        'hide_empty' => 0,
        'parent' => 0,
    );
    $categories = get_categories($argsCate);

    if ($categories) {
        foreach ($categories as $key => $value) {
            $option = get_option("option_" . $cate . "_" . $value->term_id . "");
            $arr[$value->term_id] = array(
                'ID' => $value->term_id,
                'slug' => $value->slug, // [02/10/2026] Bổ sung slug
                'name' => $option['cate_' . dgw_get_lang()] ?? $value->name,
                'class' => 'menu-main-sub-1-item',
                'order' => $option['cate_order'] ?? 0,
                'sub' => '',
            );
        }
    }

    usort($arr, "cmp");

    return $arr;
}

function getAllCategories($cate, $parent, $page)
{
    $arr = array();
    $lang = dgw_get_lang();
    $argsCate = array(
        'type' => 'post',
        'number' => 100,
        'taxonomy' => $cate,
        'hide_empty' => 0,
        'parent' => $parent,
    );

    $categories = get_categories($argsCate);

    if ($categories) {
        foreach ($categories as $key => $value) {
            $option = get_option("option_" . $cate . "_" . $value->term_id . "");
            $arr[$value->term_id] = array(
                'ID' => $value->term_id,
                'slug' => $value->slug, // [02/10/2026] Bổ sung slug
                'name' => $option['cate_' . dgw_get_lang()] ?? $value->name,
                'class' => "",
                'order' => $option['cate_order'] ?? 0,
                'page' => $page,
                'sub' => '',
            );
        }
    }

    usort($arr, "cmp");

    return $arr;
}