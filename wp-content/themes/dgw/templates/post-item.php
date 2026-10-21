<?php
declare(strict_types=1);

// [2026-10-05] - @author: Kelvin - View Template: Hiển thị thẻ bài viết (Post Item Card) chuẩn WordPress Late Escaping

if (!defined('ABSPATH')) {
    exit;
}

$stt       = isset($args['stt']) ? (int) $args['stt'] : 1;
$post_id   = get_the_ID();
$permalink = get_the_permalink($post_id);
$title     = get_the_title($post_id);

$thumb_id   = get_post_thumbnail_id($post_id);
$has_thumb  = has_post_thumbnail($post_id);
$image_src  = PART_IMAGES . 'no-image.jpg';
$srcset     = '';
$width      = 410;
$height     = 270;
$is_lazy    = true;

if ($has_thumb && $thumb_id) {
    $img_data = wp_get_attachment_image_src($thumb_id, 'medium');
    if ($img_data) {
        $image_src = $img_data[0];
        $width     = $img_data[1];
        $height    = $img_data[2];
        $srcset    = (string) wp_get_attachment_image_srcset($thumb_id, 'medium');
    }
}
?>
<div class="item" 
     data-id="<?php echo esc_attr((string) $stt); ?>"
     data-link="<?php echo esc_url($permalink); ?>"
     data-post="<?php echo esc_attr((string) $post_id); ?>">
    <div>
        <img class="item-img"
             alt="<?php echo esc_attr($title); ?>"
             src="<?php echo esc_url($image_src); ?>"
             <?php if ($srcset !== ''): ?>srcset="<?php echo esc_attr($srcset); ?>" sizes="(max-width: 400px) 100vw, 300px"<?php endif; ?>
             loading="lazy"
             width="<?php echo esc_attr((string) $width); ?>"
             height="<?php echo esc_attr((string) $height); ?>" />

        <?php
        get_template_part('templates/view-comment');
        ?>
    </div>

    <div class="item-title">
        <h3><?php echo esc_html($title); ?></h3>
    </div>
</div>
