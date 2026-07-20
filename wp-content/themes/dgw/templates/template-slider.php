<?php
$args = array(
    'post_type' => 'slider',
    // [2026-07-08] - @author: Kelvin - Tối ưu truy vấn: Giới hạn 10 ảnh thay vì -1, tắt đếm tổng trang
    'posts_per_page' => 10,
    'no_found_rows' => true,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
    'orderby' => 'meta_value',
    'order' => 'DESC',
    'meta_key' => '_metabox_order',
    'meta_query' => array(
        array(
            'key' => '_metabox_langguage',
            'value' => dgw_get_lang(),
            'compare' => '='
        )
    )

);
$wp_query = new WP_Query($args);
?>

<div class="container-fluid">
    <div id="carouselExampleFade" class="carousel slide carousel-fade" data-mdb-ride="carousel">
        <ol class="carousel-indicators">
            <?php $st = 0 ?>
            <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                <li data-target="#carouselExampleFade" data-slide-to="<?php echo $st ?>" class="<?php echo $st == 0 ? 'active' : '' ?>">
                </li>
                <?php $st = $st + 1; ?>
            <?php endwhile; ?>
        </ol>
        <div class="carousel-inner">
            <?php if ($wp_query->have_posts()) : ?>
                <?php $stt = 1 ?>
                <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
                    <?php $url = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'large'); ?>
                    <div class="carousel-item <?php echo $stt == 1 ? 'active' : '' ?>" data-interval="10000">
                        <?php if (has_post_thumbnail()) {
                            the_post_thumbnail('large', array(
                                'class' => 'd-block w-100',
                                'alt'   => get_the_title(),
                                // 如果是 Carousel 的第一張圖，可以加入 fetchpriority="high" 提升 LCP
                                'fetchpriority' => ($stt == 1 ? 'high' : 'auto'),
                            ));
                        } ?>
                        <!-- <img class=" d-block w-100" src="<?php echo $url[0] ?>" alt="<?php echo the_title(); ?>" > -->
                        <div class="carousel-caption d-none d-sm-block slider-text">
                            <a href='#'>
                                <div class="slider-title">
                                    <h2><?php the_title(); ?></h2>
                                </div>
                                <div class="slider-content">
                                    <?php the_content(); ?>
                                </div>
                            </a>
                        </div>
                    </div>
                    <?php $stt = $stt + 1; ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <a class="carousel-control-prev" href="#carouselExampleFade" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleFade" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</div>
<style>
    .carousel-indicators {
        position: absolute;
        bottom: 0;
        margin: 0;
        left: 0;
        right: 0;
        width: auto;

    }

    .carousel-indicators li,
    .carousel-indicators li.active {
        float: left;
        width: 33%;
        height: 10px;
        margin: 0;
        border-radius: 0;
        border: 0;
        background: #ccc;
    }

    .carousel-indicators li.active {
        background: orange;
    }

    /*
    .carousel-inner .carousel-item {
        transition: -webkit-transform 4s ease;
        transition: transform 4s ease;
        transition: transform 4s ease, -webkit-transform 4s ease;
    }
    */
</style>

<script>
    jQuery(document).ready(function() {
        jQuery('#media').carousel({
            pause: true,
            interval: 80000,
        });
    });
</script>