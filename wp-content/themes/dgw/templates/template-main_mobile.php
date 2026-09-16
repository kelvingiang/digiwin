<!-- [2026-09-16]: Kelvin - Cập nhật Menu Mobile UI: Premium Design, chuẩn BEM, micro-animations (CSS in SCSS) -->
<div class="menu-mobile">
    <!-- Overlay mờ khi mở menu -->
    <div class="menu-mobile__overlay"></div>

    <!-- Icon Hamburger (Mở) -->
    <div class="menu-mobile__icon">
        <svg class="menu-mobile__svg-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
            <path d="M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z" />
        </svg>
    </div>

    <!-- Khu vực Menu Panel (Trượt ra) -->
    <div class="menu-mobile__ui">
        <div class="menu-mobile__header">
            <span class="menu-mobile__title">Menu</span>
            <!-- Icon Đóng (X) -->
            <div class="menu-mobile__close">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                    <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z" />
                </svg>
            </div>
        </div>

        <div class="menu-mobile__content">
            <?php foreach (menu_main_list() as $key => $item) :
                $has_sub = isset($item['sub']) && !empty($item['sub']);
            ?>
                <div class="menu-mobile__item <?php echo $has_sub ? 'menu-mobile__item--has-submenu' : ''; ?>">
                    <div class="menu-mobile__item-main">
                        <a href="<?php echo $has_sub ? 'javascript:void(0)' : esc_url(home_url($key)); ?>" class="menu-mobile__link">
                             <?php _e($item['name'], 'dgw'); ?>
                        </a>

                        <?php if ($has_sub) : ?>
                            <span class="menu-mobile__toggle">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="14" height="14">
                                    <path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z" />
                                </svg>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($has_sub) : ?>
                        <div class="menu-mobile__sub" style="display: none;">
                            <?php foreach ($item['sub'] as $sub_item) : ?>
                                <a href="<?php echo esc_url(home_url($key) . '/cate/' . $sub_item['ID'] . '/tag/'); ?>" class="menu-mobile__sub-link">
                                    <?php echo esc_html($sub_item['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    // [2026-09-16]: JS xử lý bật tắt Menu (BEM selectors)
    jQuery(document).ready(function($) {
        // Mở menu
        $('.menu-mobile__icon').click(function() {
            $('.menu-mobile').addClass('menu-mobile--open');
            $('body').css('overflow', 'hidden'); 
        });

        // Đóng menu
        $('.menu-mobile__close, .menu-mobile__overlay').click(function() {
            $('.menu-mobile').removeClass('menu-mobile--open');
            $('body').css('overflow', ''); 
            
            // Reset accordion state khi thoát
            setTimeout(function() {
                $('.menu-mobile__sub').slideUp();
                $('.menu-mobile__toggle').removeClass('menu-mobile__toggle--active');
            }, 300);
        });

        // Toggle Submenu (Accordion)
        $('.menu-mobile__item--has-submenu .menu-mobile__item-main').click(function(e) {
            e.preventDefault();
            var $this = $(this);
            var $toggleIcon = $this.find('.menu-mobile__toggle');
            var $submenu = $this.siblings('.menu-mobile__sub');

            $toggleIcon.toggleClass('menu-mobile__toggle--active');
            $submenu.slideToggle(300);
        });
    });
</script>