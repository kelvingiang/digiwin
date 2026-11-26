<?php
declare(strict_types=1);

/**
 * [2026-11-20] - @author: Kelvin - Tối ưu Home News: Gom 5 truy vấn tab vào Transient Cache theo ngôn ngữ (giảm TTFB), bổ sung thẻ <a> chuẩn SEO và Late Escaping
 * [2026-11-26] - @author: Kelvin - Tách truy vấn & cấu hình tab đa ngôn ngữ sang PostViewHelper/CustomPostModel chuẩn MVC; View chỉ nhận dữ liệu và render
 */

if (!defined('ABSPATH')) {
    exit;
}

$homeNewsData = (isset($args['tab_configs'], $args['tabs_data']) && is_array($args['tab_configs']) && is_array($args['tabs_data']))
    ? $args
    : \DGW\Helpers\PostViewHelper::prepareHomeNewsData();

$tabConfigs   = $homeNewsData['tab_configs'];
$newsTabsData = $homeNewsData['tabs_data'];
?>
<h2 class="h2-home-title"><?php echo esc_html__('Latest News', 'dgw'); ?></h2>
<div id="news-home">
    <div class="news-home-title">
        <?php
        $isFirstTab = true;
        foreach ($tabConfigs as $tabKey => $cfg) :
            $titleCls   = $cfg['title_class'] . ($isFirstTab ? ' title-select' : '');
            $onclickStr = sprintf("ChangSelect('.%s', '.%s')", $cfg['title_class'], $cfg['content_class']);
            $isFirstTab = false;
        ?>
            <div class="<?php echo esc_attr($titleCls); ?>" onclick="<?php echo esc_attr($onclickStr); ?>">
                <h3><?php echo esc_html__($cfg['label'], 'dgw'); ?></h3>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="news-home-content">
        <?php
        $isFirstContent = true;
        foreach ($tabConfigs as $tabKey => $cfg) :
            $contentCls     = $cfg['content_class'] . ($isFirstContent ? ' content-select' : '');
            $isFirstContent = false;
            $tabItems       = $newsTabsData[$tabKey] ?? [];
        ?>
            <div class="<?php echo esc_attr($contentCls); ?>">
                <div class="content-list">
                    <?php foreach ($tabItems as $item) : ?>
                        <div class="item"
                            data-id="1"
                            data-link="<?php echo esc_url((string) $item['permalink']); ?>"
                            data-post="<?php echo esc_attr((string) $item['id']); ?>">
                            <div>
                                <a href="<?php echo esc_url((string) $item['permalink']); ?>" style="color: inherit; text-decoration: none;">
                                    <?php echo esc_html((string) $item['title']); ?>
                                </a>
                            </div>
                            <div><?php echo esc_html((string) $item['date']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a class="content-more my-link" href="<?php echo esc_url($cfg['more_url']); ?>">
                    <i class="fas fa-chevron-circle-right"></i> <?php echo esc_html__('Read More', 'dgw'); ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
    function ChangSelect(titleSelect, contentSelect) {
        jQuery(titleSelect).parents().siblings('.news-home-content').children().removeClass('content-select');
        jQuery(titleSelect).siblings().removeClass('title-select');
        jQuery(titleSelect).addClass('title-select');
        jQuery(titleSelect).parents().siblings('.news-home-content').children(contentSelect).addClass('content-select');
    }
</script>
