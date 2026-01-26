<?php

define('WP_USE_THEMES', false);
require('../../../../wp-load.php');
$lastID = $_POST['lastID'];
$post = $_POST['post'];
$cateID = $_POST['cateID'];
$count = $_POST['count'];
$cate = $_POST['cate'];

if (empty($cateID)) {
    $args = array(
        'post_type' => $post,
        'posts_per_page' => $count,
        'offset' => $lastID,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => '_metabox_order',
        // get cac bai trong category
        'meta_query'    => array(
            array(
                'key'       => '_metabox_langguage',
                'value'     =>  $_SESSION['languages'],
                'compare'   => '=',
            ),
        ),
    );
} else {
    $args = array(
        'post_type' => $post,
        'posts_per_page' => $count,
        'offset' => $lastID,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => '_metabox_order',
        // get cac bai trong category
        'tax_query' => array(
            array(
                'taxonomy' => $cate,   // taxonomy name
                'field' => 'term_id',  // term_id, slug or name
                'terms' => $cateID, // term id, term slug or term name
            )
        ),
        'meta_query'    => array(
            array(
                'key'       => '_metabox_langguage',
                'value'     =>  $_SESSION['languages'],
                'compare'   => '=',
            ),
        ),
    );
}

$wp_query = new WP_Query($args);
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
