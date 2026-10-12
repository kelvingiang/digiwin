<?php

/**
 * [2026-10-09] - @author: Kelvin
 * Chức năng: Template hiển thị Hero Slider banner trang chủ (Owl Carousel).
 * - Fix N+1 Query: Bật update_post_meta_cache để gom nhóm truy vấn metadata.
 * - Sửa orderby: 'meta_value_num' để sắp xếp đúng thứ tự số học.
 * - Tối ưu Technical SEO: Sử dụng thẻ <a> chuẩn thay cho div[data-link] để Googlebot crawl link.
 * - Tối ưu LCP & Core Web Vitals: fetchpriority="high", decoding="sync" cho slide đầu tiên.
 * - Giảm tải JS: Loại bỏ click handler thừa qua JS.
 */

$current_lang = dgw_get_lang();

// [2026-10-09] - @author: Kelvin - Cấu hình query tối ưu hiệu năng và sửa N+1 queries
$args = array(
    'post_type'              => 'slider',
    'posts_per_page'         => 10,
    'no_found_rows'          => true,
    'update_post_meta_cache' => true,  // Bật true để WP nạp toàn bộ meta trong 1 query duy nhất
    'update_post_term_cache' => false,
    'orderby'                => 'meta_value_num', // Sắp xếp theo số học chuẩn xác
    'order'                  => 'DESC',
    'meta_key'               => '_metabox_order',
    'meta_query'             => array(
        array(
            'key'     => '_metabox_langguage',
            'value'   => $current_lang,
            'compare' => '='
        )
    )
);

$slider_query = new WP_Query($args);

// [2026-10-09] - @author: Kelvin - Preload ảnh slide đầu tiên hỗ trợ LCP
if ($slider_query->have_posts()) {
    $first_post_id  = $slider_query->posts[0]->ID;
    $first_thumb_id = get_post_thumbnail_id($first_post_id);
    $first_desktop  = wp_get_attachment_image_src($first_thumb_id, 'large');
    $first_mobile   = wp_get_attachment_image_src($first_thumb_id, 'medium_large');

    if ($first_desktop) {
        echo '<link rel="preload" fetchpriority="high" as="image" href="' . esc_url($first_desktop[0]) . '" media="(min-width: 769px)">' . "\n";
    }
    if ($first_mobile) {
        echo '<link rel="preload" fetchpriority="high" as="image" href="' . esc_url($first_mobile[0]) . '" media="(max-width: 768px)">' . "\n";
    }
}
?>

<div id="slider">
    <div class="owl-carousel owl-theme">
        <?php
        if ($slider_query->have_posts()) :
            $slide_index = 0;
            while ($slider_query->have_posts()) :
                $slider_query->the_post();
                $slide_index++;
                $post_id     = get_the_ID();
                $link        = get_post_meta($post_id, '_metabox_link', true);
                $thumb_id    = get_post_thumbnail_id($post_id);
                $url_desktop = wp_get_attachment_image_src($thumb_id, 'large');
                $url_mobile  = wp_get_attachment_image_src($thumb_id, 'medium_large');

                if (!$url_desktop) {
                    continue;
                }

                $has_link = !empty($link) && $link !== '#';
                $tag_name = $has_link ? 'a' : 'div';
                $tag_attr = $has_link ? 'href="' . esc_url($link) . '" title="' . the_title_attribute(array('echo' => false)) . '"' : '';
        ?>
                <!-- [2026-10-09] - @author: Kelvin - Dùng thẻ <a> để chuẩn SEO thay vì bắt sự kiện JS -->
                <<?php echo $tag_name; ?> class="item" <?php echo $tag_attr; ?>>
                    <picture>
                        <!-- Mobile <= 768px -->
                        <source
                            media="(max-width: 768px)"
                            srcset="<?php echo esc_url($url_mobile ? $url_mobile[0] : $url_desktop[0]); ?>">

                        <!-- Desktop > 768px -->
                        <source
                            media="(min-width: 769px)"
                            srcset="<?php echo esc_url($url_desktop[0]); ?>">

                        <!-- Fallback Image -->
                        <img
                            alt="<?php the_title_attribute(); ?>"
                            src="<?php echo esc_url($url_desktop[0]); ?>"
                            decoding="<?php echo ($slide_index === 1) ? 'sync' : 'async'; ?>"
                            loading="<?php echo ($slide_index === 1) ? 'eager' : 'lazy'; ?>"
                            fetchpriority="<?php echo ($slide_index === 1) ? 'high' : 'low'; ?>"
                            width="<?php echo esc_attr((string) $url_desktop[1]); ?>"
                            height="<?php echo esc_attr((string) $url_desktop[2]); ?>">
                    </picture>
                </<?php echo $tag_name; ?>>
        <?php
            endwhile;
        endif;
        wp_reset_postdata();
        ?>
    </div>
</div>

<script>
    // [2026-10-09] - @author: Kelvin - Khởi tạo Owl Carousel gọn nhẹ, bỏ click handler thừa
    jQuery(document).ready(function($) {
        var $carousel = $('#slider .owl-carousel');
        var itemCount = $carousel.children('.item').length;

        if ($carousel.length) {
            $carousel.owlCarousel({
                loop: (itemCount > 1),
                margin: 0,
                nav: false,
                autoplay: (itemCount > 1),
                autoplayTimeout: 4000,
                autoplaySpeed: 500,
                dots: (itemCount > 1),
                autoplayHoverPause: true,
                items: 1,
                onInitialized: function(event) {
                    $(event.target).find('.owl-dot').each(function(index) {
                        $(this).attr('aria-label', 'Slide ' + (index + 1));
                    });
                }
            });
        }
    });
</script>
