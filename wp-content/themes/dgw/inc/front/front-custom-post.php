<?php
function getCustomsPost($postType, $postCount)
{
    $arr = array(
        'post_type' => $postType,
        'posts_per_page' => $postCount,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => '_metabox_order',

        // get cac bai trong category
        'meta_query'    => array(
            array(
                'key'       => '_metabox_langguage',
                'value'     =>  dgw_get_lang(),
                'compare'   => '=',
            ),
        ),
    );
    $custom_query = new WP_Query($arr);
    $stt = 1;
    if ($custom_query->have_posts()) :
        while ($custom_query->have_posts()) :
            $custom_query->the_post();
            $thumb_id = get_post_thumbnail_id(get_the_ID());
            $url_data = wp_get_attachment_image_src($thumb_id, 'medium');
            $srcset = wp_get_attachment_image_srcset($thumb_id, 'medium');
?>
            <div class="item" data-id="<?php echo  esc_attr($stt) ?>"
                data-link="<?php echo esc_url(get_the_permalink()); ?>"
                data-post="<?php echo esc_attr(get_the_ID()); ?>">
                <div>
                    <?php if (has_post_thumbnail()) : ?>
                        <img class="item-img"
                            alt="<?php the_title_attribute(); ?>"
                            src="<?php echo esc_url($url_data[0]) ?>"
                            srcset="<?php echo esc_attr($srcset) ?>"
                            fetchpriority="high"
                            width="<?php echo esc_attr($url_data[1]); ?>"
                            height="<?php echo esc_attr($url_data[2]); ?>" />
                    <?php else : ?>
                        <img class="item-img"
                            alt="<?php the_title_attribute(); ?>"
                            src="<?php echo PART_IMAGES . 'no-image.jpg' ?>"
                            srcset="<?php echo PART_IMAGES . 'no-image.jpg' ?>"
                            loading="lazy"
                            width="410"
                            height="270" />
                    <?php endif ?>
                    <?php
                    get_template_part('templates/template', 'view_comment');
                    ?>
                </div>

                <div class="item-title">
                    <h3><?php the_title() ?></h3>
                </div>
            </div>
        <?php
            $stt++;
        endwhile;
        // 必須加上這一行，重置全域 $post 變數
        wp_reset_postdata();
    endif;
}

function getCustomsPostByCate($postType, $cate, $postCount, $taxonomy)
{
    $arr = array(
        'post_type' => $postType,
        'posts_per_page' => $postCount,
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
    $custom_query = new WP_Query($arr);

    //return $wp_query;
    if ($custom_query->have_posts()) :
        $stt = 1;
        while ($custom_query->have_posts()) :
            $custom_query->the_post();
            $thumb_id = get_post_thumbnail_id(get_the_ID());
            $url = wp_get_attachment_image_src($thumb_id, 'medium');
            $srcset = wp_get_attachment_image_srcset($thumb_id, 'medium');
        ?>
            <div class="item" data-id="<?php echo  esc_attr($stt) ?>"
                data-link="<?php echo esc_url(get_the_permalink()); ?>"
                data-post="<?php echo esc_attr(get_the_ID()); ?>">
                <div>
                    <?php if (has_post_thumbnail()) { ?>
                        <img class="item-img"
                            alt="<?php the_title_attribute(); ?>"
                            src="<?php echo esc_url($url[0]) ?>"
                            srcset="<?php echo esc_attr($srcset) ?>"
                            sizes="(max-width: 400px) 100vw, 300px"
                            loading="lazy"
                            width="<?php echo esc_attr($url[1]); ?>"
                            height="<?php echo ($url[2]); ?>" />
                    <?php } else { ?>
                        <img class="item-img"
                            alt="<?php the_title_attribute(); ?>"
                            src="<?php echo PART_IMAGES . 'no-image.jpg' ?>"
                            srcset="<?php echo PART_IMAGES . 'no-image.jpg' ?>"
                            loading="lazy"
                            width="410"
                            height="270" />
                    <?php } ?>

                    <?php
                    get_template_part('templates/template', 'view_comment');
                    ?>
                </div>

                <div class="item-title">
                    <h3><?php the_title() ?></h3>
                </div>
            </div>
    <?php
            $stt++;
        endwhile;
        // 必須加上這一行，重置全域 $post 變數
        wp_reset_postdata();
    endif;
}

function getCustomsPostCate($param)
{
    $arr = array();
    $argsCate = array(
        'type' => 'post',
        // [2026-07-08] - @author: Kelvin - Sửa cấu hình get_categories: 'posts_per_page' không hợp lệ, dùng 'number'
        'number' => 100,
        'taxonomy' => 'casestudies_category',
        'hide_empty' => 0,
        'parent' => $param['cate'],
    );
    $categories = get_categories($argsCate);

    if ($categories) {
        foreach ($categories as $key => $value) {
            $option = get_option("option_casestudies_category_$value->term_id");
            $arr[$value->term_id] = array(
                'ID' => $value->term_id,
                'name' => $option['cate_' . dgw_get_lang()],
                'class' => 'menu-main-sub-1-item',
                'order' => $option['cate_order'],
                'sub' => '',
            );
        }
    }
    ?>
    <nav class="menu-cate-list">
        <?php foreach ($arr as $key => $val) { ?>
            <div class="<?php echo $param['tag'] == $key ? 'menu-cate-list-active' : '' ?>">
                <?php if ($param['tag'] == $key) { ?>
                    <label><?php echo $val['name']; ?></label>
                <?php } else { ?>
                    <a href="<?php echo home_url($param['pagename']) . '/cate/' .  $param['cate'] . '/tag/' . $val['ID'] ?>">
                        <?php echo $val['name']; ?>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>
    </nav>
<?php
}

function getCustomPostAtHome($postType, $postCount)
{
    $arr = array(
        'post_type' => $postType,
        'posts_per_page' => $postCount,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => '_metabox_order',

        // get cac bai trong category
        'meta_query'    => array(
            array(
                'key'       => '_metabox_langguage',
                'value'     =>  dgw_get_lang(),
                'compare'   => '=',
            ),

            array(
                'key'       => '_metabox_home',
                'value'     =>  '1',
                'compare'   => '=',
            ),

        ),
    );

    $query = new WP_Query($arr);
    return $query;
}

function getCustomPostCateAtHome($postType, $cateSlug, $postCount)
{
    $arr = array(
        'post_type' => $postType,
        'resources_category' => $cateSlug,
        'posts_per_page' => $postCount,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => '_metabox_order',

        // get cac bai trong category
        'meta_query'    => array(
            array(
                'key'       => '_metabox_langguage',
                'value'     =>  dgw_get_lang(),
                'compare'   => '=',
            ),
        ),
    );

    $query = new WP_Query($arr);
    return $query;
}


function getCustomPostAtSideCate($postType, $postCount, $taxonomy, $cate)
{
    $arr = array(
        'post_type' => $postType,
        'posts_per_page' => $postCount,
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
                'value'     => dgw_get_lang(),
                'compare'   => '=',
            ),
        ),
    );

    $query = new WP_Query($arr);
    return $query;
}

function getCustomPostAtSide($postType, $postCount)
{
    $arr = array(
        'post_type' => $postType,
        'posts_per_page' => $postCount,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => '_metabox_order',

        'meta_query'    => array(
            array(
                'key'       => '_metabox_langguage',
                'value'     =>  dgw_get_lang(),
                'compare'   => '=',
            ),
        ),
    );

    $query = new WP_Query($arr);
    return $query;
}

function getCustomPostShowSidebar($postType)
{
    $arr = array(
        'post_type' => $postType,
        // 'posts_per_page' => $postCount,
        // 'orderby' => 'meta_value_num',
        // 'order' => 'DESC',
        // 'meta_key' => '_metabox_order',

        'meta_query'    => array(
            array(
                'key'       => '_metabox_langguage',
                'value'     =>  dgw_get_lang(),
                'compare'   => '=',
            ),

            array(
                'key'       => '_metabox_sidebar',
                'value'     =>  '1',
                'compare'   => '=',
            ),
        ),
    );

    $query = new WP_Query($arr);
    return $query;
}

function getPostCategory($cate, $postCount)
{
    $arr = array(
        'post_type' => 'post',
        'category_name' => $cate,
        'posts_per_page' => $postCount,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => '_metabox_order',
        // get cac bai trong category

        'meta_query'    => array(
            array(
                'key'       => '_metabox_langguage',
                'value'     =>  dgw_get_lang(),
                'compare'   => '=',
            ),
        ),
    );

    $query = new WP_Query($arr);
    return $query;
}

function getPostCategoryAtHome($cate, $postCount)
{
    $arr = array(
        'post_type' => 'post',
        'category_name' => $cate,
        'posts_per_page' => $postCount,
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => '_metabox_order',
        // get cac bai trong category

        'meta_query'    => array(
            array(
                'key'       => '_metabox_langguage',
                'value'     =>  dgw_get_lang(),
                'compare'   => '=',
            ),

            array(
                'key'       => '_metabox_home',
                'value'     =>  '1',
                'compare'   => '=',
            ),

        ),
    );

    $query = new WP_Query($arr);
    return $query;
}
