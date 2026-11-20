<?php
declare(strict_types=1);

/**
 * [2026-11-20] - @author: Kelvin - Tối ưu Home News: Gom 5 truy vấn tab vào Transient Cache theo ngôn ngữ (giảm TTFB), bổ sung thẻ <a> chuẩn SEO và Late Escaping
 */

if (!defined('ABSPATH')) {
    exit;
}

$currentLang  = function_exists('dgw_get_lang') ? (string) dgw_get_lang() : 'vn';
$transientKey = 'dgw_home_news_tabs_' . $currentLang;
$newsTabsData = get_transient($transientKey);

$tabConfigs = [
    'event' => [
        'title_class'   => 'title-event',
        'content_class' => 'content-event',
        'label'         => 'Latest Event',
        'more_url'      => home_url('activities'),
        'query_type'    => 'side',
        'post_type'     => 'active',
        'cate_slug'     => '',
    ],
    'news' => [
        'title_class'   => 'title-news',
        'content_class' => 'content-news',
        'label'         => 'News Center',
        'more_url'      => home_url('resource/cate/69/tag/'),
        'query_type'    => 'cate',
        'post_type'     => 'resources',
        'cate_slug'     => 'news',
    ],
    'article' => [
        'title_class'   => 'title-article',
        'content_class' => 'content-article',
        'label'         => 'Column Article',
        'more_url'      => home_url('resource/cate/105/tag/'),
        'query_type'    => 'cate',
        'post_type'     => 'resources',
        'cate_slug'     => 'digi_know',
    ],
    'cases' => [
        'title_class'   => 'title-cases',
        'content_class' => 'content-cases',
        'label'         => 'Classic Case',
        'more_url'      => home_url('cases'),
        'query_type'    => 'side',
        'post_type'     => 'casestudies',
        'cate_slug'     => '',
    ],
    'download' => [
        'title_class'   => 'title-download',
        'content_class' => 'content-download',
        'label'         => 'Information Request',
        'more_url'      => home_url('resource/cate/104/tag/'),
        'query_type'    => 'cate',
        'post_type'     => 'resources',
        'cate_slug'     => 'download',
    ],
];

if (!is_array($newsTabsData)) {
    $newsTabsData = [];
    foreach ($tabConfigs as $tabKey => $cfg) {
        $tabQuery = ($cfg['query_type'] === 'cate')
            ? getCustomPostCateAtHome($cfg['post_type'], $cfg['cate_slug'], 5)
            : getCustomPostAtSide($cfg['post_type'], 5);

        $items = [];
        if ($tabQuery && $tabQuery->have_posts()) {
            while ($tabQuery->have_posts()) {
                $tabQuery->the_post();
                $pid     = (int) get_the_ID();
                $items[] = [
                    'id'        => $pid,
                    'title'     => (string) get_the_title($pid),
                    'permalink' => (string) get_the_permalink($pid),
                    'date'      => (string) get_the_date('Y-m-d', $pid),
                ];
            }
            wp_reset_postdata();
        }
        $newsTabsData[$tabKey] = $items;
    }

    set_transient($transientKey, $newsTabsData, 30 * MINUTE_IN_SECONDS);
}
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
