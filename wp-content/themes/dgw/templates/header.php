<!-- [2026-10-12] - @author: Kelvin - Phân loại class header-home và header-page để quản lý luồng hiển thị chuẩn xác, tránh đè menu -->
<!-- <div id="wrapper" class="hfeed"> -->
    <header class="<?php echo (is_front_page() || is_home()) ? 'header-home' : 'header-page'; ?>">
        <?php get_template_part('templates/main-menu'); ?>
    </header>
    <main class="my-content">