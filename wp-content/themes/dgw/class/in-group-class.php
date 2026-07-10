<?php

class In_Group_Class
{

    public function view_product_cate($title, $cate)
    {
        global $wp_query;
        $args = array(
            'post_type' => 'product',
            // [2026-07-08] - @author: Kelvin - Giới hạn hiển thị sản phẩm thay vì -1, tối ưu TTFB
            'posts_per_page' => 20,
            'no_found_rows' => true,
            'update_post_term_cache' => false,
            'order' => 'DESC',
            'orderby' => 'meta_value',
            'meta_key' => '_metabox_order',
            // SAP XEP TANG DAN THEO METAKEY
            'meta_query' => array(
                array(
                    'key' => '_show_home_page',
                    'value' => 1,
                    'compare' => '='
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cate', // taxonomy name
                    'field' => 'slug', // term_id, slug or name
                    'terms' => $cate, // term id, term slug or term name
                )
            )
        );

        $wp_query = new WP_Query($args);
        if ($wp_query->have_posts()) :
?>
<div class="row">
    <div class="col-lg-12" style="height: 40px; background-color:  #eeeeee; border-top: 2px solid #ccc">
        <h3 class="group-title" style="margin-top: 8px"><?php echo $title ?></h3>
    </div>
    <?php
                while ($wp_query->have_posts()) :
                    $wp_query->the_post();

                    if ($post_id == get_the_ID())
                        continue;
                ?>
    <div class="col-lg-3 list-item">
        <div class="box">
            <?php if (has_post_thumbnail()) : // Check if Thumbnail exists     
                            ?>
            <img src="<?php the_post_thumbnail_url() ?>" /><br>
            <?php endif; ?>
            <a href="<?php echo the_permalink(); ?>"><?php the_title() ?></a>

            <div class="box-title">
                <?php
                                global $wpdb;
                                $table_product = $wpdb->prefix . 'product';
                                $sql = "SELECT * FROM $table_product WHERE PID =" . get_the_ID();
                                $product_info = $wpdb->get_row($sql, ARRAY_A);
                                ?>
                <div class="content"><label><?php echo $product_info['note']; ?></label></div>
            </div>
        </div>
    </div>
    <?php
                endwhile;
                ?>
</div>
<?php
        endif;
    }

    public function view_product_post($tax_id, $post_id)
    {
        global $wp_query;
        $showNum = 10;
        // $_SESSION['offset'] = $offset;
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => $showNum,
            'order' => 'ASC',
            'orderby' => 'rand',
            // SAP XEP TANG DAN THEO METAKEY
            //            'orderby' => 'meta_value_num', 
            //            'meta_key' => '_dp_order',
            //            'meta_query' => array(
            //                array(
            //                    'key' => '_dp_active',
            //                    'value' => 1,
            //                    'compare' => 'LIKE'
            //                )
            //),
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cate', // taxonomy name
                    'field' => 'term_id', // term_id, slug or name
                    'terms' => $tax_id, // term id, term slug or term name
                )
            )
        );

        $wp_query = new WP_Query($args);
        if ($wp_query->have_posts()) :
        ?>
<hr class=" style-hr">
<div class="row">
    <div class="col-lg-12">
        <h3 class="group-title">Sản Phẩm Cùng Loại</h3>
    </div>
    <?php
                while ($wp_query->have_posts()) :
                    $wp_query->the_post();

                    if ($post_id == get_the_ID())
                        continue;
                ?>
    <div class="col-lg-2 list-item">
        <a href="<?php the_permalink(); ?>">
            <div class="box">
                <?php if (has_post_thumbnail()) : // Check if Thumbnail exists          
                                ?>
                <img src="<?php the_post_thumbnail_url() ?>" />
                <?php endif; ?>
                <label style="font-size: 0.8em"><?php the_title(); ?></label>
            </div>
        </a>
    </div>
    <?php
                endwhile;
            endif;
            ?>
</div>
<?php
            //  include_once (DIR_CLASS . 'pagination.class.php');
            wp_reset_postdata();
            wp_reset_query();
        }

        public function view_custom_post($tax_id, $post_id)
        {
            global $wp_query;

            $showNum = 10;
            $_SESSION['offset'] = $offset;
            $args = array(
                'post_type' => 'article',
                'posts_per_page' => $showNum,
                'order' => 'ASC',
                'orderby' => 'rand',
                // SAP XEP TANG DAN THEO METAKEY
                //            'orderby' => 'meta_value_num', 
                //            'meta_key' => '_dp_order',
                'meta_query' => array(
                    array(
                        'key' => '_dp_active',
                        'value' => 1,
                        'compare' => 'LIKE'
                    )
                ),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'articleCate', // taxonomy name
                        'field' => 'term_id', // term_id, slug or name
                        'terms' => $tax_id, // term id, term slug or term name
                    )
                )
            );

            $wp_query = new WP_Query($args);
            echo '    <div class="list-group" style="font-size: 13px">';
            if ($wp_query->have_posts()) :
                while ($wp_query->have_posts()) :
                    $wp_query->the_post();
                    $title = get_post_meta(get_the_ID(), '_dp_title_' . dgw_get_lang(), TRUE);
                    if ($post_id == get_the_ID())
                        continue;
            ?>
<a href="<?php the_permalink(); ?>" class="list-group-item item-text"><?php echo $title ?></a>
<?php
                endwhile;
            endif;

            echo '</div>';

            //  include_once (DIR_CLASS . 'pagination.class.php');
            wp_reset_postdata();
            wp_reset_query();
        }

        public function view_post($tax_id, $post_id, $offset)
        {
            global $wp_query;


            $showNum = 20;
            $arr = array(
                'post_type' => 'post',
                'cat' => $tax_id,
                'posts_per_page' => $showNum,
                'offset' => $offset,
                'orderby' => 'ID',
                'order' => 'DESC',
            );
            $wp_query = new WP_Query($arr);
            ?>
<h2 class="orther_post">其他訊息</h2>
<?php
            if ($wp_query->have_posts()) :
                while ($wp_query->have_posts()) :
                    $wp_query->the_post();
                    if ($post_id == get_the_ID())
                        continue;
            ?>
<div class="in-group-list">
    <div style="margin-bottom: 5px; font-weight:  bold"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </div>
    <div style="margin-left: 10px">
        <?php
                            //                        echo substr(get_the_content(), 0, 200) . '.....'; 
                            the_content_feed();
                            ?>
    </div>
</div>
<?php
                endwhile;
            endif;
            ?>
<div class="more">
    <?php
                switch ($tax_id) {
                    case 1:
                        $page_link = 'news';
                        break;
                    case 2:
                        $page_link = 'assembly';
                        break;
                    case 16:
                        $page_link = 'event';
                        break;
                }
                ?>
    <a class="btn btn-primary" href="<?php echo home_url($page_link); ?>">更多</a>
</div>
<?php
        }
    }
    ?>