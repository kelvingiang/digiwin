<?php
declare(strict_types=1);

/**
 * [2026-11-20] - @author: Kelvin - Tối ưu Home Business: Không ghi đè global $wp_query, bổ sung wp_reset_postdata(), chống lỗi mảng bool trên PHP 8.3 và thêm thẻ <a> chuẩn SEO
 * [26/11/2026] - Chuyển từ while/the_post() sang foreach($businessQuery->posts) để không kích hoạt hook loop_start/loop_end của Divi Builder (tránh chèn #et-boc làm vỡ CSS Grid)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<h2 class="h2-home-title"><?php echo esc_html__('Corporate management focus', 'dgw'); ?></h2>
<div id="business-home">
    <?php
    $businessQuery = getCustomPostAtHome('resources', 4);
    $stt           = 1;

    if (!empty($businessQuery->posts)) :
        foreach ($businessQuery->posts as $postItem) :
            $postId     = (int) $postItem->ID;
            $permalink  = (string) get_permalink($postId);
            $title      = (string) get_the_title($postId);
            $thumbId    = (int) get_post_thumbnail_id($postId);
            $urlDesktop = $thumbId > 0 ? wp_get_attachment_image_src($thumbId, 'medium') : false;
            $urlMobile  = $thumbId > 0 ? wp_get_attachment_image_src($thumbId, 'thumbnail') : false;
    ?>
            <div class="item"
                data-id="<?php echo esc_attr((string) $stt); ?>"
                data-link="<?php echo esc_url($permalink); ?>"
                data-post="<?php echo esc_attr((string) $postId); ?>">
                <div class="item-img">
                    <?php if (is_array($urlDesktop) && !empty($urlDesktop[0])) :
                        $mobileSrc = (is_array($urlMobile) && !empty($urlMobile[0])) ? $urlMobile[0] : $urlDesktop[0];
                    ?>
                        <picture>
                            <source media="(max-width: 768px)" srcset="<?php echo esc_url((string) $mobileSrc); ?>">
                            <source media="(min-width: 769px)" srcset="<?php echo esc_url((string) $urlDesktop[0]); ?>">
                            <img alt="<?php echo esc_attr($title); ?>"
                                src="<?php echo esc_url((string) $urlDesktop[0]); ?>"
                                loading="lazy"
                                decoding="async"
                                width="<?php echo esc_attr((string) ($urlDesktop[1] ?? 400)); ?>"
                                height="<?php echo esc_attr((string) ($urlDesktop[2] ?? 380)); ?>" />
                        </picture>
                    <?php else : ?>
                        <img alt="<?php echo esc_attr($title !== '' ? $title : 'digiwin software'); ?>"
                            src="<?php echo esc_url(PART_IMAGES . 'no-image.jpg'); ?>"
                            loading="lazy"
                            decoding="async"
                            width="400"
                            height="380" />
                    <?php endif; ?>
                </div>

                <div class="item-content">
                    <h3>
                        <a href="<?php echo esc_url($permalink); ?>" style="color: inherit; text-decoration: none;">
                            <?php echo esc_html($title); ?>
                        </a>
                    </h3>
                </div>
            </div>
    <?php
            $stt++;
        endforeach;
    endif;
    ?>
</div>
