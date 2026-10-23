<?php
/**
 * Plugin Name: DGW Zalo API
 * Description: Custom REST API endpoints cho hệ thống DGW.
 * Version: 1.0.0
 * Author: Gia Minh
 * Text Domain: dgw-api
 */

namespace DGW_API;

// 21/10/2026: Ngăn chặn truy cập trực tiếp vào file bảo vệ an ninh hệ thống
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 21/10/2026: Lớp khởi tạo chính (Bootstrap) cho plugin theo mô hình Singleton
 */
class Bootstrap {
    
    // Khởi tạo instance duy nhất (Singleton)
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    // 21/10/2026: Nạp các dependency theo mô hình MVC (Models -> Controllers -> Router)
    private function load_dependencies() {
        $plugin_dir = plugin_dir_path( __FILE__ );

        // Models
        require_once $plugin_dir . 'includes/models/class-dgw-solution-model.php';
        require_once $plugin_dir . 'includes/models/class-dgw-resource-model.php';

        // 21/10/2026: Nạp Auth Middleware bảo mật API Key
        require_once $plugin_dir . 'includes/class-dgw-auth.php';

        // Controllers
        require_once $plugin_dir . 'includes/controllers/class-dgw-solution-controller.php';
        require_once $plugin_dir . 'includes/controllers/class-dgw-resource-controller.php';

        // Routes
        require_once $plugin_dir . 'includes/class-dgw-zalo-api-routes.php';
    }

    // 21/10/2026: Đăng ký hook tích hợp với WordPress Core
    private function init_hooks() {
        $api_routes = new Routes();
        // Hook khởi tạo REST API
        add_action( 'rest_api_init', [ $api_routes, 'register_endpoints' ] );

        // 22/10/2026: Cho phép header bảo mật X-DGW-Key trong CORS Preflight request cho Zalo Mini App
        add_filter( 'rest_allowed_cors_headers', function( $headers ) {
            $headers[] = 'X-DGW-Key';
            return $headers;
        } );

        // 22/10/2026: Tự động xóa cache transient khi bài viết Solutions hoặc Resources thay đổi
        add_action( 'save_post_solutions', [ '\\DGW_API\\Models\\Solution_Model', 'clear_cache' ] );
        add_action( 'save_post_resources', [ '\\DGW_API\\Models\\Resource_Model', 'clear_cache' ] );
        add_action( 'deleted_post', function( $post_id ) {
            \DGW_API\Models\Solution_Model::clear_cache();
            \DGW_API\Models\Resource_Model::clear_cache();
        } );
    }
}

// Khởi chạy plugin
Bootstrap::get_instance();