<?php
declare(strict_types=1);

/**
 * [25/11/2026] - View Template: Thẻ bài viết trong cùng nhóm (LoadMoreAjax::handleLoadMoreInGroup) chuẩn MVC, Late Escaping & Web Vitals
 */

if (!defined('ABSPATH')) {
    exit;
}

$stt       = isset($args['stt']) ? (int) $args['stt'] : 1;
$postId    = (int) get_the_ID();
$permalink = (string) get_the_permalink($postId);
$title     = (string) get_the_title($postId);

$thumbId  = (int) get_post_thumbnail_id($postId);
$imageSrc = PART_IMAGES . 'no-image.jpg';
$srcset   = '';
$width    = 410;
$height   = 270;

if (has_post_thumbnail($postId) && $thumbId > 0) {
    $imgData = wp_get_attachment_image_src($thumbId, 'medium');
    if (is_array($imgData) && !empty($imgData[0])) {
        $imageSrc = (string) $imgData[0];
        $width    = (int) ($imgData[1] ?: $width);
        $height   = (int) ($imgData[2] ?: $height);
        $srcset   = (string) wp_get_attachment_image_srcset($thumbId, 'medium');
    }
}
?>
<div class="item row" data-id="<?php echo esc_attr((string) $stt); ?>">
    <div class="col-lg-2">
        <img class="item-img"
             alt="<?php echo esc_attr($title); ?>"
             src="<?php echo esc_url($imageSrc); ?>"
             <?php if ($srcset !== '') : ?>srcset="<?php echo esc_attr($srcset); ?>" sizes="(max-width: 400px) 100vw, 300px"<?php endif; ?>
             loading="lazy"
             decoding="async"
             width="<?php echo esc_attr((string) $width); ?>"
             height="<?php echo esc_attr((string) $height); ?>" />
    </div>
    <div class="col-lg-10">
        <div class="group-list-item-title">
            <a class="my-link" href="<?php echo esc_url($permalink); ?>" title="<?php echo esc_attr($title); ?>">
                <h3><?php echo esc_html($title); ?></h3>
            </a>
        </div>
        <div><?php echo wp_kses_post((string) get_the_content()); ?></div>
    </div>
</div>
