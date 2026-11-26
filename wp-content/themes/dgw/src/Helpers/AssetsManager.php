<?php
declare(strict_types=1);

namespace DGW\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Quản lý nạp CSS/JS cho Frontend và WP-Admin (chuyển từ helper/style.php)
 * [25/11/2026] - Tối ưu hiệu suất: đưa owl.carousel.min.js xuống footer và chỉ nạp trên trang có slider; chuẩn hóa capability manage_options và filemtime cache busting cho admin assets
 */
class AssetsManager
{
    /**
     * [25/11/2026] - Lấy phiên bản asset dựa theo filemtime() để cache-busting tự động
     */
    private static function getAssetVersion(string $relativePath, string $fallback = '1.0.0'): string
    {
        $fullPath = get_template_directory() . $relativePath;
        return file_exists($fullPath) ? (string) filemtime($fullPath) : $fallback;
    }

    /**
     * [25/11/2026] - Kiểm tra trang hiện tại có sử dụng Owl Carousel slider hay không
     */
    private static function shouldLoadSliderAssets(): bool
    {
        return is_front_page()
            || is_home()
            || is_page('solution')
            || is_page_template('page/solution.php');
    }

    /**
     * [18/11/2026] - Đăng ký hook nạp tài nguyên Frontend và Admin
     */
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueueFrontend']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdmin']);
    }

    /**
     * [18/11/2026] - Nạp CSS/JS cho Frontend với filemtime cache busting
     * [25/11/2026] - Đăng ký sẵn Owl Carousel ở footer ($in_footer = true) và chỉ enqueue trên trang có slider
     */
    public static function enqueueFrontend(): void
    {
        if (in_array($GLOBALS['pagenow'] ?? '', ['wp-login.php', 'wp-register.php'], true)) {
            return;
        }

        $themeUri = get_template_directory_uri();

        wp_register_style('owl-css', $themeUri . '/js/slider-owl/css/owl.carousel.min.css', [], self::getAssetVersion('/js/slider-owl/css/owl.carousel.min.css', '2.3.4'), 'all');
        wp_register_style('owl.theme.default-css', $themeUri . '/js/slider-owl/css/owl.theme.default.min.css', ['owl-css'], self::getAssetVersion('/js/slider-owl/css/owl.theme.default.min.css', '2.3.4'), 'all');
        wp_register_script('owl.carousel-js', $themeUri . '/js/slider-owl/owl.carousel.min.js', ['jquery'], self::getAssetVersion('/js/slider-owl/owl.carousel.min.js', '2.3.4'), true);

        if (self::shouldLoadSliderAssets()) {
            wp_enqueue_style('owl-css');
            wp_enqueue_style('owl.theme.default-css');
            wp_enqueue_script('owl.carousel-js');
        }

        wp_enqueue_style('my-main-css', $themeUri . '/css/style/main.min.css', [], self::getAssetVersion('/css/style/main.min.css', '1.0'), 'all');
        wp_enqueue_script('jquery-custom-js', $themeUri . '/js/custom.min.js', ['jquery'], self::getAssetVersion('/js/custom.min.js'), true);
        wp_enqueue_script('my-footer-js', $themeUri . '/js/footer.min.js', ['jquery'], self::getAssetVersion('/js/footer.min.js', '1.0'), true);
    }

    /**
     * [18/11/2026] - Nạp CSS/JS cho WP-Admin
     * [25/11/2026] - Kiểm tra quyền bằng capability manage_options thay cho user ID === 1 và dùng filemtime() cho custom-admin.js
     */
    public static function enqueueAdmin(): void
    {
        $themeUri = get_template_directory_uri();

        wp_enqueue_style('admin-style', $themeUri . '/css/admin/admin-style.css', [], self::getAssetVersion('/css/admin/admin-style.css', '1.0'), 'all');

        if (!current_user_can('manage_options')) {
            wp_enqueue_style('admin-denied', $themeUri . '/css/admin/admin-denied.css', [], self::getAssetVersion('/css/admin/admin-denied.css', '1.0'), 'all');
        }

        wp_enqueue_script('jquery-ui-js', $themeUri . '/js/jquery-ui.min.js', ['jquery'], self::getAssetVersion('/js/jquery-ui.min.js'), true);
        wp_enqueue_style('jquery-ui-css', $themeUri . '/css/jquery-ui.min.css', [], self::getAssetVersion('/css/jquery-ui.min.css', '1.0'), 'all');
        wp_enqueue_script('custom-admin-js', $themeUri . '/js/custom-admin.js', ['jquery'], self::getAssetVersion('/js/custom-admin.js'), true);
        wp_enqueue_script('jquery-custom-js', $themeUri . '/js/custom.min.js', ['jquery'], self::getAssetVersion('/js/custom.min.js'), true);
    }
}
