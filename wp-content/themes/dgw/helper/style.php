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
        wp_enqueue_style('owl-css', get_template_directory_uri() . '/js/slider-owl/css/owl.carousel.css', array(), '1.0', 'all');
        wp_enqueue_style('owl.theme.default-css', get_template_directory_uri() . '/js/slider-owl/css/owl.theme.default.min.css', array(), '1.0', 'all');
        wp_enqueue_script('owl.carousel-js', get_template_directory_uri() . '/js/slider-owl/owl.carousel.js', array('jquery'), '1.0.0', true);
    }

    //====== MY STYLE ======
    // Lấy timestamp của file CSS để tự động cập nhật version, tránh cache trình duyệt khi có file thay đổi
    $css_file_path = get_template_directory() . '/css/style/main.min.css';
    $css_version = file_exists($css_file_path) ? filemtime($css_file_path) : '1.0';
    wp_enqueue_style('my-main-css', get_template_directory_uri() . '/css/style/main.min.css', array(), $css_version, 'all');

    //====== CHUNG (CLIENT) ======
    wp_enqueue_script('jquery-custom-js', get_template_directory_uri() . '/js/custom.js', array('jquery'), '1.0.0', true);
    
    //====== FOOTER SCRIPT ======
    // KHÔNG dùng time() làm version vì sẽ phá vỡ cache trình duyệt, gây chậm website. Chuyển sang dùng filemtime.
    $footer_js_path = get_template_directory() . '/js/footer.js';
    $footer_version = file_exists($footer_js_path) ? filemtime($footer_js_path) : '1.0';
    wp_enqueue_script('my-footer-js', get_template_directory_uri() . '/js/footer.js', array('jquery'), $footer_version, true);
}
add_action('wp_enqueue_scripts', 'dgw_frontend_scripts');

// 2. ADMIN SCRIPTS & STYLES (Dùng đúng hook admin_enqueue_scripts)
function dgw_admin_scripts()
{
    wp_enqueue_style('admin-style', get_template_directory_uri() . '/css/admin/admin-style.css', array(), '1.0', 'all');
    
    // Nếu không phải là admin tối cao (ID = 1), add file CSS hạn chế quyền
    if (get_current_user_id() != 1) {
        wp_enqueue_style('admin-denied', get_template_directory_uri() . '/css/admin/admin-denied.css', array(), '1.0', 'all');
    }

    wp_enqueue_script('jquery-ui-js', get_template_directory_uri() . '/js/jquery-ui.min.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style('jquery-ui-css', get_template_directory_uri() . '/css/jquery-ui.min.css', array(), '1.0', 'all');
    wp_enqueue_script('custom-admin-js', get_template_directory_uri() . '/js/custom-admin.js', array('jquery'), '1.0.0', true);
    
    // Add custom.js cho cả admin (như code cũ của bạn)
    wp_enqueue_script('jquery-custom-js', get_template_directory_uri() . '/js/custom.js', array('jquery'), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'dgw_admin_scripts');

