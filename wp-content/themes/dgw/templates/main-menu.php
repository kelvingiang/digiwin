<?php
declare(strict_types=1);

/**
 * [2026-11-20] - @author: Kelvin - Tối ưu Main Menu: Gom chung hàm render cho #header-space và #header-scroll, sửa lỗi trùng 2 thuộc tính alt và bổ sung Late Escaping
 */

if (!defined('ABSPATH')) {
    exit;
}

$menuItems     = menu_main_list();
$homeUrl       = home_url();
$logoDesktop   = PART_IMAGES . 'logo.webp';
$logoMobile    = PART_IMAGES . 'logo-mobile.webp';
$logoMobileSet = $logoMobile . ' 1x, ' . $logoDesktop . ' 2x';

$renderHeaderBar = static function (string $containerId) use ($menuItems, $homeUrl, $logoDesktop, $logoMobile, $logoMobileSet): void {
    ?>
    <div id="<?php echo esc_attr($containerId); ?>">
        <a class="logo-desktop" href="<?php echo esc_url($homeUrl); ?>">
            <img class="company-logo"
                alt="digiwin company"
                src="<?php echo esc_url($logoDesktop); ?>"
                width="150"
                height="42" />
        </a>
        <a class="logo-mobile" href="<?php echo esc_url($homeUrl); ?>">
            <img class="company-logo"
                alt="digiwin company"
                src="<?php echo esc_url($logoMobile); ?>"
                srcset="<?php echo esc_attr($logoMobileSet); ?>"
                width="100"
                height="28" />
        </a>
        <nav class="menu-main">
            <?php foreach ($menuItems as $keyMain => $valMain) :
                $classData = (string) ($valMain['data'] ?? '');
                $className = (string) ($valMain['name'] ?? '');
                $itemClass = (string) ($valMain['class'] ?? 'menu-main-item');
                $hasSub    = !empty($valMain['sub']) && is_array($valMain['sub']);
            ?>
                <div class="<?php echo esc_attr($itemClass); ?>" data-name="<?php echo esc_attr($classData); ?>">
                    <?php if ($hasSub) : ?>
                        <label class="menu-main-item-link has-sub">
                            <?php echo esc_html($className); ?>
                        </label>
                    <?php else : ?>
                        <a href="<?php echo esc_url(home_url((string) $keyMain)); ?>" class="menu-main-item-link">
                            <?php echo esc_html($className); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($hasSub) :
                        $subClass = (string) ($valMain['subClass'] ?? 'menu-main-sub-1');
                    ?>
                        <div class="<?php echo esc_attr($subClass); ?>">
                            <?php foreach ($valMain['sub'] as $valSub) :
                                $subSlugOrId = !empty($valSub['slug']) ? (string) $valSub['slug'] : (string) ($valSub['ID'] ?? '');
                                $subUrl      = home_url((string) $keyMain . '/cate/' . $subSlugOrId . '/');
                                $subLinkCls  = 'menu-main-sub-1-item-link' . (!empty($valSub['sub']) ? ' has-sub' : '');
                            ?>
                                <div class="<?php echo esc_attr((string) ($valSub['class'] ?? '')); ?>">
                                    <a href="<?php echo esc_url($subUrl); ?>" class="<?php echo esc_attr($subLinkCls); ?>">
                                        <?php echo esc_html((string) ($valSub['name'] ?? '')); ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </nav>
        <div>
            <?php get_template_part('templates/languages'); ?>
        </div>
    </div>
    <?php
};

$renderHeaderBar('header-space');
$renderHeaderBar('header-scroll');
?>
<script>
    const animationElements = document.querySelector("#header-space");

    function myCheck(element) {
        if (!element) return;
        var rect = element.getClientRects()[0];
        var headerScroll = document.querySelector('#header-scroll');
        if (!rect || !headerScroll) return;

        if (rect.bottom < 0) {
            headerScroll.classList.add("start");
            headerScroll.classList.remove("close");
        } else {
            if (headerScroll.classList.contains("start")) {
                headerScroll.classList.add("close");
            }
            headerScroll.classList.remove("start");
        }
    }

    function menuAnimation() {
        myCheck(animationElements);
    }
</script>
