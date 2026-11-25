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

// [20/11/2026] - Chuẩn MVC: View chỉ nhận $sliderData từ PostViewHelper/CustomPostModel, không khởi tạo WP_Query trực tiếp
$sliderData     = (isset($args['slides']) && is_array($args['slides'])) ? $args : \DGW\Helpers\PostViewHelper::prepareSliderData();
$preloadDesktop = (string) ($sliderData['preload_desktop'] ?? '');
$preloadMobile  = (string) ($sliderData['preload_mobile'] ?? '');
$slides         = isset($sliderData['slides']) && is_array($sliderData['slides']) ? $sliderData['slides'] : [];

if ($preloadDesktop !== '') {
    echo '<link rel="preload" fetchpriority="high" as="image" href="' . esc_url($preloadDesktop) . '" media="(min-width: 769px)">' . "\n";
}
if ($preloadMobile !== '') {
    echo '<link rel="preload" fetchpriority="high" as="image" href="' . esc_url($preloadMobile) . '" media="(max-width: 768px)">' . "\n";
}
?>

<div id="slider">
    <div class="owl-carousel owl-theme">
        <?php
        $slide_index = 0;
        foreach ($slides as $slide) :
            $slide_index++;
            $has_link    = !empty($slide['has_link']);
            $link        = (string) ($slide['link'] ?? '');
            $slide_title = (string) ($slide['title'] ?? '');
            $url_desktop = (string) ($slide['url_desktop'] ?? '');
            $url_mobile  = (string) ($slide['url_mobile'] ?? $url_desktop);
            $width       = (int) ($slide['width'] ?? 0);
            $height      = (int) ($slide['height'] ?? 0);

            $tag_name = $has_link ? 'a' : 'div';
            $tag_attr = $has_link ? 'href="' . esc_url($link) . '" title="' . esc_attr($slide_title) . '"' : '';
        ?>
            <!-- [2026-10-09] - @author: Kelvin - Dùng thẻ <a> để chuẩn SEO thay vì bắt sự kiện JS -->
            <<?php echo $tag_name; ?> class="item" <?php echo $tag_attr; ?>>
                <picture>
                    <!-- Mobile <= 768px -->
                    <source
                        media="(max-width: 768px)"
                        srcset="<?php echo esc_url($url_mobile); ?>">

                    <!-- Desktop > 768px -->
                    <source
                        media="(min-width: 769px)"
                        srcset="<?php echo esc_url($url_desktop); ?>">

                    <!-- Fallback Image -->
                    <img
                        alt="<?php echo esc_attr($slide_title); ?>"
                        src="<?php echo esc_url($url_desktop); ?>"
                        decoding="<?php echo ($slide_index === 1) ? 'sync' : 'async'; ?>"
                        loading="<?php echo ($slide_index === 1) ? 'eager' : 'lazy'; ?>"
                        fetchpriority="<?php echo ($slide_index === 1) ? 'high' : 'low'; ?>"
                        width="<?php echo esc_attr((string) $width); ?>"
                        height="<?php echo esc_attr((string) $height); ?>">
                </picture>
            </<?php echo $tag_name; ?>>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // [2026-10-12] - @author: Kelvin - Khởi tạo Owl Carousel an toàn chống lỗi trên hosting khi bị cache/defer JS
    jQuery(document).ready(function($) {
        var $carousel = $('#slider .owl-carousel');
        if (!$carousel.length) return;

        function initMainSlider() {
            if (typeof $.fn.owlCarousel !== 'function') {
                setTimeout(initMainSlider, 100);
                return;
            }

            // [2026-10-13] - @author: Kelvin - Khắc phục lỗi tiếng Trung: giải phóng các thẻ .item nếu bị plugin (như Divi Builder #et-boc) bọc lại bên trong
            var $allSlides = $carousel.find('.item');
            if ($allSlides.length > 0 && $carousel.children('.item').length !== $allSlides.length) {
                $carousel.empty().append($allSlides);
            }

            var itemCount = $carousel.children('.item').length;
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

        initMainSlider();
    });
</script>
