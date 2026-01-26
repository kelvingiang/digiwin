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
        echo  '<div class="menu-sub-item" data-id="'. $val['ID'] .'">';
        echo  '<a href="' . home_url($val['page'] . '/cate/' .  $val['ID'] . '/tag/') . '">';
        echo  $val['name'];
        if (!empty($sub)) {
            echo "<i style=' margin-left: 0.3rem' class='fas fa-angle-down'></i>";
        }
        echo  '</a>';
        if (!empty($sub)) {
            echo "<div class='menu-sub-child'>";
            foreach ($sub as $skey => $sval) {
                echo "<div >";
                echo "<a class='my-link' id='" . $sval['ID'] . "' href='" . home_url($val['page'] . '/cate/' .  $val['ID'] . '/tag/' . $sval['ID']) . "'>" . $sval['name'] . "</a>";
                echo "</div>";
            }
            echo "</div>";
        }
        echo '</div>';
    }
}
