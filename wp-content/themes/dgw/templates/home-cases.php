<?php

/**
 * [2026-10-08] - @author: Kelvin
 * Chức năng: Template hiển thị Slider Khách hàng tiêu biểu / Case Studies tại trang chủ (Home Cases).
 * Mục đích tối ưu Technical SEO & Performance:
 * 1. Chấm dứt Unbounded Query (TTFB): Thay thế posts_per_page = -1 bằng giới hạn 9 bài viết nổi bật, loại bỏ nguy cơ nghẽn CSDL khi số lượng bài tăng cao.
 * 2. Transient Cache: Cache dữ liệu case studies theo từng ngôn ngữ trong 12 giờ, giảm tối đa truy vấn SQL lặp lại ở trang chủ.
 * 3. Crawlability & Internal Link: Chuyển div.item thành thẻ <a> chuẩn HTML giúp Googlebot cào liên kết tới bài viết chi tiết, truyền PageRank.
 * 4. Tối ưu Hiệu suất Slider & Loại bỏ Layout Thrashing (INP/CLS): 
 *    - Dùng CSS Flexbox thay thế hoàn toàn hàm setEqualHeight (vốn gây Forced Reflow liên tục trên mỗi lần chuyển slide).
 *    - Tắt dots: false trực tiếp trong cấu hình để không sinh mã DOM vô nghĩa (thay vì sinh ra rồi dùng CSS ẩn).
 * 5. Accessibility & i18n: Đa ngôn ngữ hóa nhãn điều hướng navText/aria-label theo ngôn ngữ hiện tại thay vì hardcode tiếng Trung.
 */

$current_lang  = dgw_get_lang();
$transient_key = 'dgw_home_casestudies_' . $current_lang;
$case_items    = get_transient($transient_key);

if (false === $case_items) {
    // [2026-10-08] - Giới hạn 9 bài (bội số của 3 cột desktop) thay vì -1 unbounded
    $query = getCustomPostAtHome('casestudies', 9);
    $case_items = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id     = get_the_ID();
            $thumb_id    = get_post_thumbnail_id($post_id);
            $url_desktop = wp_get_attachment_image_src($thumb_id, 'medium');
            $url_mobile  = wp_get_attachment_image_src($thumb_id, 'thumbnail');

            $case_items[] = array(
                'id'          => $post_id,
                'title'       => get_the_title(),
                'permalink'   => get_the_permalink(),
                'has_thumb'   => has_post_thumbnail(),
                'url_desktop' => $url_desktop ?: array(PART_IMAGES . 'no-image.jpg', 410, 270),
                'url_mobile'  => $url_mobile ?: $url_desktop,
            );
        }
    }
    wp_reset_postdata();

    set_transient($transient_key, $case_items, 12 * HOUR_IN_SECONDS);
}
?>

<h2 class="h2-home-title"><?php echo esc_html__('Enterprise model success case', 'dgw'); ?></h2>

<?php if (!empty($case_items)) : ?>
    <div id="casestudies-slider">
        <div class="owl-carousel owl-theme">
            <?php foreach ($case_items as $item) :
                $title_attr = esc_attr($item['title']);
            ?>
                <!-- [2026-10-08] - Thẻ <a> chuẩn SEO hỗ trợ Googlebot cào liên kết nội bộ -->
                <a class="item"
                    href="<?php echo esc_url($item['permalink']); ?>"
                    data-post="<?php echo esc_attr((string) $item['id']); ?>"
                    data-link="<?php echo esc_url($item['permalink']); ?>"
                    aria-label="<?php echo $title_attr; ?>">

                    <div class="item-img">
                        <?php if (!empty($item['has_thumb'])) : ?>
                            <picture>
                                <!-- Mobile <= 768px -->
                                <source media="(max-width: 768px)"
                                    srcset="<?php echo esc_url($item['url_mobile'][0]); ?>">
                                <!-- Desktop > 768px -->
                                <source media="(min-width: 769px)"
                                    srcset="<?php echo esc_url($item['url_desktop'][0]); ?>">
                                <img alt="<?php echo 'digiwin case studies '.  $title_attr; ?>"
                                    src="<?php echo esc_url($item['url_desktop'][0]); ?>"
                                    loading="lazy"
                                    decoding="async"
                                    width="<?php echo esc_attr((string) $item['url_desktop'][1]); ?>"
                                    height="<?php echo esc_attr((string) $item['url_desktop'][2]); ?>" />
                            </picture>
                        <?php else : ?>
                            <img alt="<?php echo 'digiwin case studies' . $title_attr; ?>"
                                src="<?php echo esc_url(PART_IMAGES . 'no-image.jpg'); ?>"
                                loading="lazy"
                                decoding="async"
                                width="410"
                                height="270" />
                        <?php endif; ?>
                    </div>

                    <div class="item-title">
                        <h3><?php echo esc_html($item['title']); ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // [2026-10-08] - @author: Kelvin - Khởi tạo Owl Carousel mượt mà, tắt dots dư thừa, ngăn reflow
        jQuery(document).ready(function($) {
            var $slider = $('#casestudies-slider .owl-carousel');
            if ($slider.length) {
                $slider.owlCarousel({
                    loop: true,
                    margin: 15,
                    nav: true,
                    dots: false, // [2026-10-08] - Tắt dots trực tiếp, không tạo DOM rác
                    autoplay: true,
                    autoplayTimeout: 6000,
                    autoplaySpeed: 600,
                    autoplayHoverPause: true,
                    navText: [
                        '<svg class="multi-slider-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true" focusable="false"><path d="M201.4 297.4C188.9 309.9 188.9 330.2 201.4 342.7L361.4 502.7C373.9 515.2 394.2 515.2 406.7 502.7C419.2 490.2 419.2 469.9 406.7 457.4L269.3 320L406.6 182.6C419.1 170.1 419.1 149.8 406.6 137.3C394.1 124.8 373.8 124.8 361.3 137.3L201.3 297.3z"/></svg>',
                        '<svg class="multi-slider-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true" focusable="false"><path d="M439.1 297.4C451.6 309.9 451.6 330.2 439.1 342.7L279.1 502.7C266.6 515.2 246.3 515.2 233.8 502.7C221.3 490.2 221.3 469.9 233.8 457.4L371.2 320L233.9 182.6C221.4 170.1 221.4 149.8 233.9 137.3C246.4 124.8 266.7 124.8 279.2 137.3L439.2 297.3z"/></svg>'
                    ],
                    responsive: {
                        0: {
                            items: 1
                        },
                        501: {
                            items: 2
                        },
                        951: {
                            items: 3
                        }
                    },
                    onInitialized: function(event) {
                        $('#casestudies-slider .owl-prev').attr('aria-label', '<?php echo esc_attr__('Bài trước', 'dgw'); ?>');
                        $('#casestudies-slider .owl-next').attr('aria-label', '<?php echo esc_attr__('Bài tiếp theo', 'dgw'); ?>');
                    }
                });

                // [2026-10-08] - Để footer.php xử lý plus_one_view ajax mà không mở tab trùng lặp
                $('#casestudies-slider').on('click', 'a.item', function(e) {
                    e.preventDefault();
                });
            }
        });
    </script>
<?php endif; ?>