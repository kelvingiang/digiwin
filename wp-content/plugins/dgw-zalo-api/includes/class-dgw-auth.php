<?php
namespace DGW_API;

// 21/10/2026: Ngăn chặn truy cập trực tiếp bảo vệ an ninh hệ thống
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 21/10/2026: Lớp xử lý xác thực an toàn (Authentication) cho Zalo Mini App REST API
 */
class Auth {

    /**
     * Khóa bí mật mặc định (Có thể override tại wp-config.php bằng define('DGW_ZALO_API_KEY', '...'))
     */
    const DEFAULT_API_KEY = 'dgw_zalo_sec_2026_8f9a2b';

    /**
     * 21/10/2026: Lấy API Secret Key hợp lệ từ wp-config.php hoặc mặc định
     * 
     * @return string
     */
    public static function get_api_key() {
        if ( defined( 'DGW_ZALO_API_KEY' ) && ! empty( DGW_ZALO_API_KEY ) ) {
            return (string) DGW_ZALO_API_KEY;
        }
        return self::DEFAULT_API_KEY;
    }

    /**
     * 21/10/2026: Kiểm tra tính hợp lệ của Header X-DGW-Key trong Request bằng thuật toán hash_equals chống Timing Attack
     * 
     * @param \WP_REST_Request $request
     * @return bool|\WP_Error
     */
    public static function check_permission( \WP_REST_Request $request ) {
        // 21/10/2026: Lấy key từ Header X-DGW-Key hoặc Query Param api_key trên URL
        $client_key = $request->get_header( 'x_dgw_key' );
        if ( empty( $client_key ) ) {
            $client_key = $request->get_header( 'x-dgw-key' );
        }
        if ( empty( $client_key ) ) {
            $client_key = $request->get_param( 'api_key' );
        }
        if ( empty( $client_key ) && isset( $_GET['api_key'] ) ) {
            $client_key = sanitize_text_field( wp_unslash( $_GET['api_key'] ) );
        }

        $valid_key = self::get_api_key();

        // 21/10/2026: So sánh an toàn chống timing attack
        if ( empty( $client_key ) || ! hash_equals( $valid_key, (string) $client_key ) ) {
            return new \WP_Error(
                'rest_forbidden',
                'Truy cập bị từ chối: API Secret Key (X-DGW-Key) không hợp lệ hoặc bị thiếu.',
                [ 'status' => 401 ]
            );
        }

        return true;
    }
}
