<?php
/**
 * [2026-10-12] - @author: Kelvin
 * Description: Tối ưu hoá hiển thị Hoạt động tại Trang chủ.
 * - Technical SEO & CLS: Alt tag, width/height, decoding="async", loading="lazy", escaped URLs/titles.
 * - Performance: Thay thế jQuery inline styles nặng nề bằng CSS 3D GPU-Accelerated 60fps mượt mà.
 * - UX & Touch: Mở rộng hitbox thẻ <a> bao trọn card, hỗ trợ cảm ứng lật thẻ trên thiết bị di động.
 */
?>
<div id="active-home">
    <?php
    $wp_query = getCustomPostAtHome('active', -1);

    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $post_title = get_the_title();
            $post_url   = get_permalink();
            $thumb_url  = has_post_thumbnail() 
                ? get_the_post_thumbnail_url(get_the_ID(), 'medium') 
                : (PART_IMAGES . 'no-image.jpg');
    ?>
            <div class="active-item">
                <div class="active-item-img">
                    <a href="<?php echo esc_url($post_url); ?>" title="<?php the_title_attribute(); ?>" tabindex="-1" aria-hidden="true">
                        <img src="<?php echo esc_url($thumb_url); ?>" 
                             alt="<?php echo esc_attr($post_title); ?>" 
                             loading="lazy" 
                             decoding="async" 
                             width="300" 
                             height="300" />
                    </a>
                </div>

                <div class="active-item-content">
                    <a href="<?php echo esc_url($post_url); ?>" title="<?php the_title_attribute(); ?>">
                        <span><?php echo esc_html($post_title); ?></span>
                    </a>
                </div>
            </div>
    <?php
        }
    }
    wp_reset_postdata();
    ?>
</div>

<script>
    // [2026-10-12] - @author: Kelvin - Hỗ trợ chạm (tap/touch) để lật thẻ trên thiết bị di động không có hover
    jQuery(document).ready(function($) {
        $('#active-home').on('touchstart', '.active-item', function() {
            if (!$(this).hasClass('is-flipped')) {
                $('#active-home .active-item').not(this).removeClass('is-flipped');
                $(this).addClass('is-flipped');
            }
        });
    });
</script>