<?php
// QUAN LY CAC PHAN CSS VA JS CHO ADMIN VA CLINET

// 1. FRONTEND SCRIPTS & STYLES (Dùng đúng hook wp_enqueue_scripts)
function dgw_frontend_scripts()
{
    // Bỏ qua trang đăng nhập
    if (in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php'])) {
        return;
    }

    // Chỉ load slider/carousel khi KHÔNG phải trang member
    // Lưu ý: is_page() chỉ hoạt động đúng khi dùng hook wp_enqueue_scripts, không hoạt động ở init
    if (!is_page('member')) {
        //====== OWL SLIDER (Thư viện duy nhất được sử dụng trong Theme) ======
        // [2026-10-12] - @author: Kelvin - Nạp trong Header ($in_footer = false) để script inline trên trang chủ không bị lỗi "$slider.owlCarousel is not a function" khi lên hosting
        $owl_js_path = get_template_directory() . '/js/slider-owl/owl.carousel.min.js';
        $owl_js_ver  = file_exists($owl_js_path) ? filemtime($owl_js_path) : '2.3.4';
        wp_enqueue_style('owl-css', get_template_directory_uri() . '/js/slider-owl/css/owl.carousel.min.css', array(), '2.3.4', 'all');
        wp_enqueue_style('owl.theme.default-css', get_template_directory_uri() . '/js/slider-owl/css/owl.theme.default.min.css', array(), '2.3.4', 'all');
        wp_enqueue_script('owl.carousel-js', get_template_directory_uri() . '/js/slider-owl/owl.carousel.min.js', array('jquery'), $owl_js_ver, false);
    }

    //====== MY STYLE ======
    // Lấy timestamp của file CSS để tự động cập nhật version, tránh cache trình duyệt khi có file thay đổi
    $css_file_path = get_template_directory() . '/css/style/main.min.css';
    $css_version = file_exists($css_file_path) ? filemtime($css_file_path) : '1.0';
    wp_enqueue_style('my-main-css', get_template_directory_uri() . '/css/style/main.min.css', array(), $css_version, 'all');

    //====== CHUNG (CLIENT) ======
    // [20/10/2026] - Nạp bản custom.min.js tối ưu hóa tốc độ tải trang
    $custom_js_path = get_template_directory() . '/js/custom.min.js';
    $custom_version = file_exists($custom_js_path) ? filemtime($custom_js_path) : '1.0.0';
    wp_enqueue_script('jquery-custom-js', get_template_directory_uri() . '/js/custom.min.js', array('jquery'), $custom_version, true);
    
    //====== FOOTER SCRIPT ======
    // [20/10/2026] - Nạp bản footer.min.js đã nén dung lượng
    $footer_js_path = get_template_directory() . '/js/footer.min.js';
    $footer_version = file_exists($footer_js_path) ? filemtime($footer_js_path) : '1.0';
    wp_enqueue_script('my-footer-js', get_template_directory_uri() . '/js/footer.min.js', array('jquery'), $footer_version, true);
}
add_action('wp_enqueue_scripts', 'dgw_frontend_scripts');

function dgw_admin_scripts()
{
    // [2026-10-05] - @author: Kelvin - Thêm filemtime tự động cập nhật version CSS tránh cache trình duyệt trong WP-Admin
    $admin_css_path = get_template_directory() . '/css/admin/admin-style.css';
    $admin_css_ver  = file_exists($admin_css_path) ? (string) filemtime($admin_css_path) : '1.0';
    wp_enqueue_style('admin-style', get_template_directory_uri() . '/css/admin/admin-style.css', array(), $admin_css_ver, 'all');
    
    // Nếu không phải là admin tối cao (ID = 1), add file CSS hạn chế quyền
    if (get_current_user_id() != 1) {
        wp_enqueue_style('admin-denied', get_template_directory_uri() . '/css/admin/admin-denied.css', array(), '1.0', 'all');
    }

    wp_enqueue_script('jquery-ui-js', get_template_directory_uri() . '/js/jquery-ui.min.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style('jquery-ui-css', get_template_directory_uri() . '/css/jquery-ui.min.css', array(), '1.0', 'all');
    wp_enqueue_script('custom-admin-js', get_template_directory_uri() . '/js/custom-admin.js', array('jquery'), '1.0.0', true);
    
    // [20/10/2026] - Add custom.min.js cho cả admin
    $custom_admin_path = get_template_directory() . '/js/custom.min.js';
    $custom_admin_ver  = file_exists($custom_admin_path) ? filemtime($custom_admin_path) : '1.0.0';
    wp_enqueue_script('jquery-custom-js', get_template_directory_uri() . '/js/custom.min.js', array('jquery'), $custom_admin_ver, true);
}
add_action('admin_enqueue_scripts', 'dgw_admin_scripts');

