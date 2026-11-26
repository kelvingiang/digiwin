<?php
declare(strict_types=1);

/**
 * [2026-11-20] - @author: Kelvin - Tối ưu Home Industry: Không ghi đè global $wp_query, chống lỗi offset bool trên PHP 8.3, bổ sung alt/decoding="async" và Late Escaping
 * [26/11/2026] - Chuyển từ while/the_post() sang foreach($industryQuery->posts) để không kích hoạt hook loop_start/loop_end của Divi Builder (tránh chèn #et-boc làm vỡ Flexbox)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<h2 class="h2-home-title"><?php echo esc_html__('Specialize in the industry', 'dgw'); ?></h2>
<div id="industry-home">
    <?php
    $stt           = 1;
    $industryQuery = getCustomPostAtHome('industries', 8);

    if (!empty($industryQuery->posts)) :
        foreach ($industryQuery->posts as $postItem) :
            $postId     = (int) $postItem->ID;
            $permalink  = (string) get_permalink($postId);
            $title      = (string) get_the_title($postId);
            $thumbId    = (int) get_post_thumbnail_id($postId);
            $urlDesktop = $thumbId > 0 ? wp_get_attachment_image_src($thumbId, 'medium') : false;
            $urlMobile  = $thumbId > 0 ? wp_get_attachment_image_src($thumbId, 'thumbnail') : false;
    ?>
            <div class="item"
                data-id="<?php echo esc_attr((string) $stt); ?>"
                data-post="<?php echo esc_attr((string) $postId); ?>">
                <a href="<?php echo esc_url($permalink); ?>" title="<?php echo esc_attr($title); ?>">
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
                                    width="<?php echo esc_attr((string) ($urlDesktop[1] ?? 410)); ?>"
                                    height="<?php echo esc_attr((string) ($urlDesktop[2] ?? 270)); ?>" />
                            </picture>
                        <?php else : ?>
                            <img src="<?php echo esc_url(PART_IMAGES . 'no-image.jpg'); ?>"
                                alt="<?php echo esc_attr($title !== '' ? $title : 'digiwin industry'); ?>"
                                loading="lazy"
                                decoding="async"
                                width="410"
                                height="270" />
                        <?php endif; ?>
                    </div>
                    <div class="item-content">
                        <h3><?php echo esc_html($title); ?></h3>
                    </div>
                </a>
            </div>
    <?php
            $stt++;
        endforeach;
    endif;
    ?>
</div>
