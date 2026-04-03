<?php

function ColorCode($id)
{
    switch ($id) {
        case "1":
            $color = 'black';
            break;
        case "2":
            $color = 'red';
            break;
        case "3":
            $color = 'blue';
            break;
        case "4":
            $color = 'pink';
            break;
        case "5":
            $color = 'silver';
            break;
        case "6":
            $color = 'green';
            break;
    }
    return $color;
}

/*================================================================
SEARCH  POST  BY METABOX 
==================================================================*/

function get_category_summary($id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'product_category';
    $sql = "SELECT summary_vn, summary_cn, img FROM  $table WHERE ID = $id ";
    $row = $wpdb->get_row($sql, ARRAY_A);
    return $row;
}

function get_product_category()
{
    global $wpdb;
    $table = $wpdb->prefix . 'product_category';
    $sql = "SELECT * FROM  $table WHERE kind = 'p' ORDER BY `orders` DESC  ";
    $row = $wpdb->get_results($sql, ARRAY_A);
    return $row;
}

function get_category_by_id($ID)
{
    global $wpdb;
    $table = $wpdb->prefix . 'product_category';
    $sql = "SELECT name_cn, name_vn FROM  $table WHERE  ID = $ID  ";
    $row = $wpdb->get_row($sql, ARRAY_A);
    return $row;
}

function get_show_name($kind, $val)
{
    global $wpdb;
    $table = $wpdb->prefix . 'product_category';
    $sql = "SELECT * FROM  $table WHERE kind = '$kind' AND val = '$val' ";
    $row = $wpdb->get_row($sql, ARRAY_A);
    return $row;
}

function get_product($id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'product';
    $sql = "SELECT * FROM  $table WHERE ID = $id";
    $row = $wpdb->get_row($sql, ARRAY_A);
    return $row;
}

function get_products($cate)
{
    global $wpdb;
    $table = $wpdb->prefix . 'product';
    if ($cate == '') {
        $sql = "SELECT * FROM  $table WHERE trash = 0 ORDER BY `is_order` DESC";
    } else {
        $sql = "SELECT * FROM  $table WHERE trash = 0 AND category = $cate ORDER BY `is_order` DESC";
    }
    $row = $wpdb->get_results($sql, ARRAY_A);
    return $row;
}




