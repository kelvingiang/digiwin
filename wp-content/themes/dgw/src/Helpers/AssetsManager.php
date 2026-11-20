<?php
declare(strict_types=1);

namespace DGW\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Quản lý nạp CSS/JS cho Frontend và WP-Admin (chuyển từ helper/style.php)
 */
class AssetsManager
{
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
     */
    public static function enqueueFrontend(): void
    {
        if (in_array($GLOBALS['pagenow'] ?? '', ['wp-login.php', 'wp-register.php'], true)) {
            return;
        }

        if (!is_page('member')) {
            $owlJsPath = get_template_directory() . '/js/slider-owl/owl.carousel.min.js';
            $owlJsVer  = file_exists($owlJsPath) ? (string) filemtime($owlJsPath) : '2.3.4';
            wp_enqueue_style('owl-css', get_template_directory_uri() . '/js/slider-owl/css/owl.carousel.min.css', [], '2.3.4', 'all');
            wp_enqueue_style('owl.theme.default-css', get_template_directory_uri() . '/js/slider-owl/css/owl.theme.default.min.css', [], '2.3.4', 'all');
            wp_enqueue_script('owl.carousel-js', get_template_directory_uri() . '/js/slider-owl/owl.carousel.min.js', ['jquery'], $owlJsVer, false);
        }

        $cssFilePath = get_template_directory() . '/css/style/main.min.css';
        $cssVersion  = file_exists($cssFilePath) ? (string) filemtime($cssFilePath) : '1.0';
        wp_enqueue_style('my-main-css', get_template_directory_uri() . '/css/style/main.min.css', [], $cssVersion, 'all');

        $customJsPath  = get_template_directory() . '/js/custom.min.js';
        $customVersion = file_exists($customJsPath) ? (string) filemtime($customJsPath) : '1.0.0';
        wp_enqueue_script('jquery-custom-js', get_template_directory_uri() . '/js/custom.min.js', ['jquery'], $customVersion, true);

        $footerJsPath  = get_template_directory() . '/js/footer.min.js';
        $footerVersion = file_exists($footerJsPath) ? (string) filemtime($footerJsPath) : '1.0';
        wp_enqueue_script('my-footer-js', get_template_directory_uri() . '/js/footer.min.js', ['jquery'], $footerVersion, true);
    }

    /**
     * [18/11/2026] - Nạp CSS/JS cho WP-Admin
     */
    public static function enqueueAdmin(): void
    {
        $adminCssPath = get_template_directory() . '/css/admin/admin-style.css';
        $adminCssVer  = file_exists($adminCssPath) ? (string) filemtime($adminCssPath) : '1.0';
        wp_enqueue_style('admin-style', get_template_directory_uri() . '/css/admin/admin-style.css', [], $adminCssVer, 'all');

        if (get_current_user_id() !== 1) {
            wp_enqueue_style('admin-denied', get_template_directory_uri() . '/css/admin/admin-denied.css', [], '1.0', 'all');
        }

        wp_enqueue_script('jquery-ui-js', get_template_directory_uri() . '/js/jquery-ui.min.js', ['jquery'], '1.0.0', true);
        wp_enqueue_style('jquery-ui-css', get_template_directory_uri() . '/css/jquery-ui.min.css', [], '1.0', 'all');
        wp_enqueue_script('custom-admin-js', get_template_directory_uri() . '/js/custom-admin.js', ['jquery'], '1.0.0', true);

        $customAdminPath = get_template_directory() . '/js/custom.min.js';
        $customAdminVer  = file_exists($customAdminPath) ? (string) filemtime($customAdminPath) : '1.0.0';
        wp_enqueue_script('jquery-custom-js', get_template_directory_uri() . '/js/custom.min.js', ['jquery'], $customAdminVer, true);
    }
}
