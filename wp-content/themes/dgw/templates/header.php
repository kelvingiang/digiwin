<?php
// [2026-10-12] - @author: Kelvin - Phân loại class header-home và header-page để quản lý luồng hiển thị chuẩn xác, tránh đè menu
// [2026-11-20] - @author: Kelvin - Ngăn chặn render trùng lặp header và <main class="my-content"> khi cả header.php và page template cùng gọi
global $dgw_header_part_rendered;
if (!empty($dgw_header_part_rendered)) {
    return;
}
$dgw_header_part_rendered = true;
?>
<header class="<?php echo (is_front_page() || is_home()) ? 'header-home' : 'header-page'; ?>">
    <?php get_template_part('templates/main-menu'); ?>
</header>
<main class="my-content">
