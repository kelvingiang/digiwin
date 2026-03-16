<?php
function pageImg($id)
{
    if (has_post_thumbnail($id)) :
        $image = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail');
        echo '<img class="page-img" src="' . $image[0] . '"alt="' . get_the_title() . '"/>';
    endif;
};



function menuSub($cate, $page)
{
    $data = getAllCategories($cate, 0, $page);
    foreach ($data as $val) {
        $sub = getAllCategories($cate, $val['ID'], $page);
        echo  '<div class="menu-sub-item" data-id="' . $val['ID'] . '">';
        echo  '<a href="' . home_url($val['page'] . '/cate/' .  $val['ID'] . '/tag/') . '">';
        echo  '<h2>' . $val['name'] . '</h2>';
        echo  '</a>';
        echo '</div>';
    }
}
