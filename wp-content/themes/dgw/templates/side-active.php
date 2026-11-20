<?php
declare(strict_types=1);

/**
 * [10/11/2026] - Template hiển thị danh mục hoạt động dạng Tab Widget 1 khối đồng nhất
 * [2026-11-20] - @author: Kelvin - Tối ưu TTFB: Cache danh mục + danh sách bài viết từng tab bằng Transient API theo ngôn ngữ, loại bỏ 1 + N queries trên mọi trang bài viết
 */

if (!defined('ABSPATH')) {
    exit;
}

$currentLang  = function_exists('dgw_get_lang') ? (string) dgw_get_lang() : 'vn';
$transientKey = 'dgw_side_active_tabs_' . $currentLang;
$sideTabData  = get_transient($transientKey);

if (!is_array($sideTabData)) {
    $categories  = getAllCategories('active_category', 0, 'actives');
    $sideTabData = [];

    if (is_array($categories)) {
        foreach ($categories as $val) {
            $taxId          = (int) ($val['ID'] ?? 0);
            $sideActiveQry  = getCustomPostAtSideCate('active', 5, 'active_category', $taxId);
            $posts          = [];

            if ($sideActiveQry && $sideActiveQry->have_posts()) {
                while ($sideActiveQry->have_posts()) {
                    $sideActiveQry->the_post();
                    $posts[] = [
                        'title'     => (string) get_the_title(),
                        'permalink' => (string) get_the_permalink(),
                    ];
                }
                wp_reset_postdata();
            }

            $sideTabData[] = [
                'ID'    => $taxId,
                'name'  => (string) ($val['name'] ?? ''),
                'posts' => $posts,
            ];
        }
    }

    set_transient($transientKey, $sideTabData, HOUR_IN_SECONDS);
}
?>
<div class="side-space">
    <div class="side-tab">
        <?php foreach ($sideTabData as $index => $val) : ?>
            <div class="tab-title title-tab-<?php echo esc_attr((string) $val['ID']); ?> <?php echo ($index === 0 ? 'active' : ''); ?>"
                data-target="content-tab-<?php echo esc_attr((string) $val['ID']); ?>">
                <label><?php echo esc_html((string) $val['name']); ?></label>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="side-tab-content">
        <?php foreach ($sideTabData as $index => $val) : ?>
            <div class="card-list content-tab-<?php echo esc_attr((string) $val['ID']); ?> <?php echo ($index === 0 ? 'active' : ''); ?>">
                <?php foreach ($val['posts'] as $postItem) : ?>
                    <a class="card-item" href="<?php echo esc_url((string) $postItem['permalink']); ?>">
                        <div class="card-title"><?php echo esc_html((string) $postItem['title']); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        $('.side-space .tab-title').on('click', function() {
            var target = $(this).data('target');
            $(this).closest('.side-space').find('.tab-title').removeClass('active');
            $(this).addClass('active');
            $(this).closest('.side-space').find('.card-list').removeClass('active');
            $(this).closest('.side-space').find('.' + target).addClass('active');
        });
    });
</script>
