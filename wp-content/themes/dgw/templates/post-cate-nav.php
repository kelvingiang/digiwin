<?php
declare(strict_types=1);

/**
 * [2026-11-19] - @author: Kelvin - View Template: Danh mục con điều hướng trang Case Studies (.menu-cate-list) tách từ inc/front/front-custom-post.php
 */

if (!defined('ABSPATH')) {
    exit;
}

$items      = isset($args['items']) && is_array($args['items']) ? $args['items'] : [];
$currentTag = isset($args['current_tag']) ? (string) $args['current_tag'] : '';
$pagename   = isset($args['pagename']) ? (string) $args['pagename'] : '';
$cateVal    = isset($args['cate_val']) ? (string) $args['cate_val'] : '';
?>
<nav class="menu-cate-list">
    <?php foreach ($items as $key => $val) :
        $isActive = ($currentTag === (string) $key);
        ?>
        <div class="<?php echo $isActive ? 'menu-cate-list-active' : ''; ?>">
            <?php if ($isActive) : ?>
                <label><?php echo esc_html((string) ($val['name'] ?? '')); ?></label>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url($pagename . '/cate/' . $cateVal . '/tag/' . (string) ($val['ID'] ?? ''))); ?>">
                    <?php echo esc_html((string) ($val['name'] ?? '')); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</nav>
