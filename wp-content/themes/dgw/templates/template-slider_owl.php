<?php
$args = array(
    'post_type' => 'slider',
    // [2026-07-08] - @author: Kelvin - Tối ưu truy vấn: Giới hạn 10 ảnh thay vì -1, tắt đếm tổng trang để giảm TTFB
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

// --- 優化點 1: 確保 Preload 在最前面執行 ---
if ($wp_query->have_posts()) {
    $first_post = $wp_query->posts[0];
    $first_thumb_id = get_post_thumbnail_id($first_post->ID);
    $first_img_data = wp_get_attachment_image_src($first_thumb_id, 'medium');
    if ($first_img_data) {
        // 建議將此標籤放在 wp_head() 附近，若不行，放在這裡也能幫助瀏覽器提早發現
        echo '<link rel="preload" fetchpriority="high" as="image" href="' . esc_url($first_img_data[0]) . '">';
    }
}
?>

<style>
    /* --- 優化點 2: 防止渲染延遲 (Render Delay) --- */
    #slider {
        border-bottom: 2px solid rgba(208, 228, 247, 0.5);
        min-height: 300px;
        /* 給予基本高度，防止頁面跳動 */
        position: relative;
        overflow: hidden;
    }

    /* 在 JS 載入前，強制顯示第一張圖，隱藏其他的，這能大幅降低渲染延遲感 */
    .owl-carousel:not(.owl-loaded) .item {
        display: none;
    }

    .owl-carousel:not(.owl-loaded) .item:first-child {
        display: block;
    }

    #slider .item img {
        width: 100%;
        height: auto;
        display: block;
    }
</style>

<div id="slider">
    <div class="owl-carousel owl-theme">
        <?php
        if ($wp_query->have_posts()) :
            $count = 0;
            while ($wp_query->have_posts()) : 
                $wp_query->the_post();
                $count++;
                $link = get_post_meta($post->ID, '_metabox_link', true);
                $thumb_id = get_post_thumbnail_id($post->ID);
                $url_desktop = wp_get_attachment_image_src($thumb_id, 'large');        // Desktop
                $url_mobile  = wp_get_attachment_image_src($thumb_id, 'medium_large'); // Mobile (768px)
        ?>
                <div class="item" <?php if (!empty($link)) : ?>data-link="<?php echo esc_url($link); ?>" <?php endif; ?>>
                    <picture>
                        <!-- Mobile <= 768px -->
                        <source
                            media="(max-width: 768px)"
                            srcset="<?php echo esc_url($url_mobile[0]); ?>">

                        <!-- Desktop > 768px -->
                        <source
                            media="(min-width: 769px)"
                            srcset="<?php echo esc_url($url_desktop[0]); ?>">

                        <!-- Fallback -->
                        <img
                            alt="<?php the_title_attribute(); ?>"
                            src="<?php echo esc_url($url_desktop[0]); ?>"
                            decoding="<?php echo ($count === 1) ? 'sync' : 'async'; ?>"
                            loading="<?php echo ($count === 1) ? 'eager' : 'lazy'; ?>"
                            fetchpriority="<?php echo ($count === 1) ? 'high' : 'low'; ?>"
                            width="<?php echo $url_desktop[1]; ?>"
                            height="<?php echo $url_desktop[2]; ?>">
                    </picture>

                    <div class="owl-slider-content">
                        <?php the_content(); ?>
                    </div>
                </div>
        <?php
            endwhile;
        endif;
        wp_reset_postdata();
        ?>
    </div>
</div>

<script>
    // 這裡保持你原本的 Owl Carousel 初始化代碼即可
    jQuery(document).ready(function() {
        jQuery('#slider .owl-carousel').owlCarousel({
            loop: true,
            margin: 10,
            nav: false,
            autoplay: true,
            autoplayTimeout: 3000,
            autoplaySpeed: 500,
            dots: true,
            autoplayHoverPause: true,
            items: 1,
            onInitialized: function(event) {
                jQuery(event.target).find('.owl-dot').each(function(index) {
                    jQuery(this).attr('aria-label', 'digiwin solution ' + (index + 1) + ' page');
                });
            }
        });

        jQuery('#slider').on('click', '.item', function() {
            const link = jQuery(this).data('link'); // 建議用 jQuery(this)
            if (link) {
                window.location.href = link;
            }
        });
    });
</script>