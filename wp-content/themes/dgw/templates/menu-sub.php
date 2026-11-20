<?php
declare(strict_types=1);

/**
 * [2026-11-19] - @author: Kelvin - View Template: Submenu điều hướng danh mục (.menu-sub) tách từ inc/front/front-menu-sub.php
 */

if (!defined('ABSPATH')) {
    exit;
}

$items           = isset($args['items']) && is_array($args['items']) ? $args['items'] : [];
$currentCateSlug = isset($args['current_cate_slug']) ? (string) $args['current_cate_slug'] : '';
$isSingle        = !empty($args['is_single']);
$tag             = $isSingle ? 'span' : 'h2';

foreach ($items as $val) {
    $valId     = (string) ($val['ID'] ?? '');
    $slugOrId  = !empty($val['slug']) ? (string) $val['slug'] : $valId;
    $isActive  = ($currentCateSlug !== '' && ($currentCateSlug === $slugOrId || $currentCateSlug === $valId));
    $activeCls = $isActive ? ' active' : '';
    $pageSlug  = (string) ($val['page'] ?? '');
    $url       = home_url($pageSlug . '/cate/' . $slugOrId . '/');
    ?>
    <div class="menu-sub-item<?php echo esc_attr($activeCls); ?>"
         data-id="<?php echo esc_attr($slugOrId); ?>"
         data-term-id="<?php echo esc_attr($valId); ?>">
        <a href="<?php echo esc_url($url); ?>" class="<?php echo esc_attr(trim($activeCls)); ?>">
            <<?php echo $tag; ?> class="menu-title"><?php echo esc_html((string) ($val['name'] ?? '')); ?></<?php echo $tag; ?>>
        </a>
    </div>
    <?php
}
