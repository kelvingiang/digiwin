<?php

define('WP_USE_THEMES', false);
require('../../../../wp-load.php');
$lastID = $_POST['lastID'];
$cate = $_POST['cate'];
$slug = $_POST['slug'];
$count = $_POST['count'];


$args = array(
    'post_type' => $cate,
    'posts_per_page' => 5,
    'offset' => $lastID + 1,
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
    'meta_key' => '_metabox_order',
    // get cac bai trong category
    'tax_query' => array(
        array(
            'taxonomy' => $taxonomy,   // taxonomy name
            'field' => 'term_id',  // term_id, slug or name
            'terms' => $cate, // term id, term slug or term name
        )
    ),

    'meta_query'    => array(
        array(
            'key'       => '_metabox_langguage',
            'value'     =>  dgw_get_lang(),
            'compare'   => '=',
        ),
    ),
);


$wp_query = new WP_Query($args);
if ($wp_query->have_posts()) {

    $stt = $lastID + 1;
    while ($wp_query->have_posts()) : $wp_query->the_post();
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
    $response = array(
        'status' => 'done',
        'html' => $html,
    );
} else {
    $response = array(
        'status' => 'empty',
    );
}

echo json_encode($response);
