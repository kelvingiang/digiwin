<?php
declare(strict_types=1);

/**
 * [2026-11-20] - @author: Kelvin - Tối ưu Side Articles: Gộp 5 truy vấn CPT và lưu Transient Cache theo ngôn ngữ (giảm TTFB), bổ sung lazy/async/width/height chống CLS
 */

if (!defined('ABSPATH')) {
    exit;
}

$currentLang  = function_exists('dgw_get_lang') ? (string) dgw_get_lang() : 'vn';
$transientKey = 'dgw_side_articles_' . $currentLang;
$sideItems    = get_transient($transientKey);

if (!is_array($sideItems)) {
    $sidebarTypes = ['resources', 'active', 'casestudies', 'joinus', 'solutions'];
    $sideItems    = [];

    foreach ($sidebarTypes as $type) {
        $sideQuery = getCustomPostShowSidebar($type);
        if ($sideQuery && $sideQuery->have_posts()) {
            while ($sideQuery->have_posts()) {
                $sideQuery->the_post();
                $pid     = (int) get_the_ID();
                $thumbId = (int) get_post_thumbnail_id($pid);
                $alt     = $thumbId > 0 ? (string) get_post_meta($thumbId, '_wp_attachment_image_alt', true) : '';
                if ($alt === '') {
                    $alt = (string) get_the_title($pid);
                }

                $imgData = $thumbId > 0 ? wp_get_attachment_image_src($thumbId, 'medium') : false;
                if (is_array($imgData) && !empty($imgData[0])) {
                    $imgUrl = (string) $imgData[0];
                    $width  = (int) ($imgData[1] ?? 300);
                    $height = (int) ($imgData[2] ?? 200);
                } else {
                    $imgUrl = PART_IMAGES . 'no-image.jpg';
                    $alt    = 'digiwin software';
                    $width  = 300;
                    $height = 200;
                }

                $sideItems[] = [
                    'id'        => $pid,
                    'permalink' => (string) get_the_permalink($pid),
                    'img_url'   => $imgUrl,
                    'alt'       => $alt,
                    'width'     => $width,
                    'height'    => $height,
                ];
            }
            wp_reset_postdata();
        }
    }

    set_transient($transientKey, $sideItems, HOUR_IN_SECONDS);
}
?>
<div id="article-side-list">
    <?php foreach ($sideItems as $item) : ?>
        <div class="item"
             data-id="<?php echo esc_attr((string) $item['id']); ?>"
             data-link="<?php echo esc_url((string) $item['permalink']); ?>"
             data-post="<?php echo esc_attr((string) $item['id']); ?>">
            <a href="<?php echo esc_url((string) $item['permalink']); ?>" aria-label="<?php echo esc_attr((string) $item['alt']); ?>">
                <img src="<?php echo esc_url((string) $item['img_url']); ?>"
                     alt="<?php echo esc_attr((string) $item['alt']); ?>"
                     loading="lazy"
                     decoding="async"
                     width="<?php echo esc_attr((string) $item['width']); ?>"
                     height="<?php echo esc_attr((string) $item['height']); ?>" />
            </a>
        </div>
    <?php endforeach; ?>
</div>
