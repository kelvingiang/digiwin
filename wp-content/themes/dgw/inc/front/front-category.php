<?php

function getCategories($cate)
{
    $arr = array();
    $argsCate = array(
        'type' => 'post',
        // [2026-07-08] - @author: Kelvin - Thay -1 bằng số lượng cụ thể cho get_categories
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
                'name' => $option['cate_' . dgw_get_lang()],
                'class' => 'menu-main-sub-1-item',
                'order' => $option['cate_order'],
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
        // [2026-07-08] - @author: Kelvin - Thay -1 bằng số lượng cụ thể cho get_categories
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
                'name' => $option['cate_' . dgw_get_lang()],
                'class' => "",
                'order' => $option['cate_order'],
                'page' => $page,
                'sub' => '',
            );
        }
    }

    usort($arr, "cmp");

    return $arr;
}
