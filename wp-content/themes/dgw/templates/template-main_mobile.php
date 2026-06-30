<!-- [2026-06-30]: Thiết kế lại Menu Mobile (Modern Side Drawer) -->
<div class="menu-mobile">
    <!-- Overlay mờ khi mở menu -->
    <div class="menu-mobile-overlay"></div>

    <!-- Icon Hamburger (Mở) -->
    <div class="menu-mobile-icon">
        <svg class="svg-menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
            <path d="M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z"/>
        </svg>
    </div>

    <!-- Khu vực Menu Panel (Trượt ra) -->
    <div class="menu-mobile-ui">
        <div class="menu-mobile-header">
            <span class="menu-title">Menu</span>
            <!-- Icon Đóng (X) -->
            <div class="menu-mobile-close">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                    <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                </svg>
            </div>
        </div>
        
        <div class="menu-mobile-content">
            <?php foreach (menu_mobile_list() as $key => $val) { ?>
                <div class="menu-mobile-item">
                    <a href="<?php echo home_url($key) ?>"><?php _e($val); ?></a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script>
    // [2026-06-30]: JS xử lý bật tắt Menu (Dùng Class thay vì toggle inline)
    jQuery(document).ready(function($) {
        // Mở menu
        $('.menu-mobile-icon').click(function() {
            $('.menu-mobile').addClass('is-open');
            $('body').css('overflow', 'hidden'); // Ngăn cuộn trang
        });

        // Đóng menu khi bấm X hoặc Overlay
        $('.menu-mobile-close, .menu-mobile-overlay').click(function() {
            $('.menu-mobile').removeClass('is-open');
            $('body').css('overflow', ''); // Trả lại cuộn trang
        });
    });
</script>