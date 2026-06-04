<?php
// QUAN LY CAC PHAN CSS VA JS CHO ADMIN VA CLINET
function style_header_scripts()
{
        if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
                //======SLICK=================================================
                wp_register_style('slick-theme-css', get_template_directory_uri() . '/js/slick/slick-theme.css', array(), '1.0', 'all');
                wp_enqueue_style('slick-theme-css');

                wp_register_script('slick-js', get_template_directory_uri() . '/js/slick/slick.min.js', array('jquery'), '1.0.0'); // Custom scripts
                wp_enqueue_script('slick-js');

                //==== SLIDER ============================================
                wp_register_style('owl-css', get_template_directory_uri() . '/js/slider-owl/css/owl.carousel.css', array(), '1.0', 'all');
                wp_enqueue_style('owl-css');

                wp_register_style('owl.theme.default-css', get_template_directory_uri() . '/js/slider-owl/css/owl.theme.default.min.css', array(), '1.0', 'all');
                wp_enqueue_style('owl.theme.default-css');

                wp_register_script('owl.carousel-js', get_template_directory_uri() . '/js/slider-owl/owl.carousel.js', array('jquery'), '1.0.0'); // Custom scripts
                wp_enqueue_script('owl.carousel-js');

                //==== MULTY SLIDER============================================
                wp_register_style('flexisel-style', get_template_directory_uri() . '/js/slider-multi/flexisel.css', array(), '1.0', 'all');
                wp_enqueue_style('flexisel-style');

                wp_register_script('flexisel-js', get_template_directory_uri() . '/js/slider-multi/jquery.flexisel.js', array('jquery'), '1.0.0'); // Custom scripts
                wp_enqueue_script('flexisel-js');

                wp_register_script('jcarousel-js', get_template_directory_uri() . '/js/jquery.jcarousellite-1.0.1.js', array('jquery'), '1.0.0'); // Custom scripts
                wp_enqueue_script('jcarousel-js');

                //====== MY STYLE ==================================================================
                // wp_register_style('my-main-css', get_template_directory_uri() . '/css/style/main.min.css', array(), '1.0', 'all');
                // wp_enqueue_style('my-main-css');

                // 1. Lấy đường dẫn vật lý của file trên server để kiểm tra thời gian sửa đổi
                $css_file_path = get_template_directory() . '/css/style/main.min.css';

                // 2. Nếu file tồn tại thì lấy timestamp làm version, nếu không thì dùng mặc định '1.0'
                $css_version = file_exists($css_file_path) ? filemtime($css_file_path) : '1.0';

                //====== MY STYLE ==================================================================
                wp_register_style(
                        'my-main-css',
                        get_template_directory_uri() . '/css/style/main.min.css',
                        array(),
                        $css_version, // Sử dụng biến thời gian tự động ở đây thay vì '1.0'
                        'all'
                );
                wp_enqueue_style('my-main-css');
        } else {

                //====PHAN ADMIN=========================================================
                wp_register_style('admin-style', get_template_directory_uri() . '/css/admin/admin-style.css', array(), '1.0', 'all');
                wp_enqueue_style('admin-style');
                if (get_current_user_id() != 1) {
                        wp_register_style('admin-denied', get_template_directory_uri() . '/css/admin/admin-denied.css', array(), '1.0', 'all');
                        wp_enqueue_style('admin-denied');
                }

                wp_register_script('jquery-ui-js', get_template_directory_uri() . '/js/jquery-ui.min.js', array('jquery'), '1.0.0'); // Custom scripts
                wp_enqueue_script('jquery-ui-js');

                wp_register_style('jquery-ui-css', get_template_directory_uri() . '/css/jquery-ui.min.css', array(), '1.0', 'all');
                wp_enqueue_style('jquery-ui-css');

                wp_register_script('custom-admin-js', get_template_directory_uri() . '/js/custom-admin.js', array('jquery'), '1.0.0'); // Custom scripts
                wp_enqueue_script('custom-admin-js');
        }

        // ==ADD CHO CA ADMIN VA CLIENT=========================================================

        wp_register_script('jquery-custom-js', get_template_directory_uri() . '/js/custom.js', array('jquery'), '1.0.0'); // Custom scripts
        wp_enqueue_script('jquery-custom-js');
}

add_action('init', 'style_header_scripts');


// them cac file js và css vao phan footer
function style_footer_scripts()
{
        wp_enqueue_script(
                'my-footer-js',
                get_template_directory_uri() . '/js/footer.js',
                array('jquery'),
                time(),
                true   // <--- Footer
        );
}
add_action('wp_enqueue_scripts', 'style_footer_scripts');
