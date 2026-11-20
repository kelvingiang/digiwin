<?php
declare(strict_types=1);

/**
 * [2026-11-19] - @author: Kelvin - View Template: Sidebar menu danh mục nhiều cấp (.menu-side) tách từ inc/front/front-menu-side.php
 */

if (!defined('ABSPATH')) {
    exit;
}

$items       = isset($args['items']) && is_array($args['items']) ? $args['items'] : [];
$currentCate = isset($args['current_cate']) ? (string) $args['current_cate'] : '';
$currentTag  = isset($args['current_tag']) ? (string) $args['current_tag'] : '';
?>
<div class="menu-side">
    <?php foreach ($items as $val) :
        $valId   = (string) ($val['ID'] ?? '');
        $valPage = (string) ($val['page'] ?? '');
        $valSlug = !empty($val['slug']) ? (string) $val['slug'] : $valId;
        $valName = (string) ($val['name'] ?? '');
        $sub     = isset($val['children']) && is_array($val['children']) ? $val['children'] : [];
        ?>
        <div class="item has-sub">
            <div class="menu-side-main">
                <a id="<?php echo esc_attr($valId); ?>"
                   class="menu-side-main-link"
                   href="<?php echo esc_url(home_url($valPage . '/cate/' . $valSlug . '/')); ?>">
                    <?php echo esc_html($valName); ?>
                    <?php if (!empty($sub)) : ?>
                        <i style="margin-left: 1rem" class="fas fa-angle-down"></i>
                    <?php endif; ?>
                </a>
                <?php if (!empty($sub)) : ?>
                    <div class="menu-side-sub">
                        <?php foreach ($sub as $sval) :
                            $svalId   = (string) ($sval['ID'] ?? '');
                            $svalName = (string) ($sval['name'] ?? '');
                            ?>
                            <div>
                                <a id="<?php echo esc_attr($svalId); ?>"
                                   href="<?php echo esc_url(home_url($valPage . '/cate/' . $valId . '/tag/' . $svalId)); ?>">
                                    <?php echo esc_html($svalName); ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<script>
    jQuery(document).ready(function ($) {
        var cate = <?php echo wp_json_encode($currentCate); ?>;
        var tag  = <?php echo wp_json_encode($currentTag); ?>;

        if (cate !== '') {
            var cates = '#' + cate;
            $(cates).addClass('linked');
            $(cates).parent('.menu-side-main').addClass('active-item');
            $(cates).parent().parent('.item').removeClass('has-sub');
            if (tag !== '') {
                var tags = '#' + tag;
                $(tags).addClass('linked');
                $(tags).parent().parent('.menu-side-sub').css('display', 'block');
                $(tags).parent().addClass('active-item-sub');
            }
        }

        $('.item').on('mouseover', function () {
            var $sub = $(this).children().children('.menu-side-sub');
            if ($sub.css('display') === 'none' && !$(this).find('.linked').length) {
                $sub.slideDown('slow');
            }
        });

        $('.item').on('mouseleave', function () {
            var $sub = $(this).children().children('.menu-side-sub');
            if ($sub.css('display') === 'block' && !$(this).find('.linked').length) {
                $sub.slideUp('slow');
            }
        });
    });
</script>
