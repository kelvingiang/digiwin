<?php
namespace DGW_API;

// 21/10/2026: Ngăn chặn truy cập trực tiếp bảo vệ an ninh
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 21/10/2026: Lớp định nghĩa và điều hướng REST API endpoints cho Zalo Mini App
 */
class Routes {
    
    private $namespace = 'dgw/v1';

    /**
     * 21/10/2026: Đăng ký các endpoint custom với schema validation và sanitization chuẩn WP REST API
     */
    public function register_endpoints() {
        // Endpoint mẫu /data
        register_rest_route( $this->namespace, '/data', [
            'methods'             => \WP_REST_Server::READABLE, // GET method
            'callback'            => [ $this, 'get_data_response' ],
            'permission_callback' => [ $this, 'check_permissions' ],
            'args'                => [
                'limit' => [
                    'required'          => false,
                    'default'           => 5,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param ) && $param > 0;
                    },
                    'sanitize_callback' => 'absint',
                ],
            ],
        ] );

        // 26/10/2026: Tham số phân trang & Infinite Scroll (Load More) dùng chung cho các endpoint danh sách
        $pagination_scroll_args = [
            'offset'   => [
                'required'          => false,
                'default'           => null,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param ) && $param >= 0;
                },
                'sanitize_callback' => 'absint',
            ],
            'page'     => [
                'required'          => false,
                'default'           => 1,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param ) && $param > 0;
                },
                'sanitize_callback' => 'absint',
            ],
            'per_page' => [
                'required'          => false,
                'default'           => 10,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param ) && $param > 0;
                },
                'sanitize_callback' => 'absint',
            ],
            'limit'    => [
                'required'          => false,
                'default'           => null,
                'validate_callback' => function( $param ) {
                    return is_numeric( $param ) && $param > 0;
                },
                'sanitize_callback' => 'absint',
            ],
            'lang'     => [
                'required'          => false,
                'default'           => 'vn',
                'sanitize_callback' => function( $param ) {
                    return sanitize_key( (string) $param );
                },
            ],
        ];

        // 21/10/2026: Đăng ký endpoint lấy danh sách solutions theo category cho Zalo Mini App
        $solution_controller = new \DGW_API\Controllers\Solution_Controller();

        $solutions_args = array_merge(
            [
                'category' => [
                    'required'          => false,
                    'default'           => 'tiptop',
                    'sanitize_callback' => function( $param ) {
                        return sanitize_title( (string) $param );
                    },
                ],
            ],
            $pagination_scroll_args
        );

        // 1. GET /wp-json/dgw/v1/solutions (Mặc định category=tiptop hoặc truyền qua query string ?category=tiptop)
        register_rest_route( $this->namespace, '/solutions', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $solution_controller, 'get_items' ],
            'permission_callback' => [ $solution_controller, 'check_permissions' ],
            'args'                => $solutions_args,
        ] );

        // 2. GET /wp-json/dgw/v1/solutions/{category} (Ví dụ trực tiếp: /dgw/v1/solutions/tiptop)
        register_rest_route( $this->namespace, '/solutions/(?P<category>[a-zA-Z0-9_-]+)', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $solution_controller, 'get_items' ],
            'permission_callback' => [ $solution_controller, 'check_permissions' ],
            'args'                => $solutions_args,
        ] );

        // 21/10/2026: Đăng ký endpoint lấy danh sách resources theo category cho Zalo Mini App
        $resource_controller = new \DGW_API\Controllers\Resource_Controller();

        $resources_args = array_merge(
            [
                'category' => [
                    'required'          => false,
                    'default'           => 'download',
                    'sanitize_callback' => function( $param ) {
                        return sanitize_title( (string) $param );
                    },
                ],
            ],
            $pagination_scroll_args
        );

        // 3. GET /wp-json/dgw/v1/resources (Mặc định category=download hoặc truyền qua query string ?category=download)
        register_rest_route( $this->namespace, '/resources', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $resource_controller, 'get_items' ],
            'permission_callback' => [ $resource_controller, 'check_permissions' ],
            'args'                => $resources_args,
        ] );

        // 4. GET /wp-json/dgw/v1/resources/{category} (Ví dụ trực tiếp: /dgw/v1/resources/download)
        register_rest_route( $this->namespace, '/resources/(?P<category>[a-zA-Z0-9_-]+)', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $resource_controller, 'get_items' ],
            'permission_callback' => [ $resource_controller, 'check_permissions' ],
            'args'                => $resources_args,
        ] );

        // 26/10/2026: Đăng ký endpoint lấy danh sách post_type 'active' (hoạt động/sự kiện) cho Zalo Mini App
        $active_controller = new \DGW_API\Controllers\Active_Controller();

        $actives_args = array_merge(
            [
                'category' => [
                    'required'          => false,
                    'default'           => '',
                    'sanitize_callback' => function( $param ) {
                        return sanitize_title( (string) $param );
                    },
                ],
            ],
            $pagination_scroll_args
        );

        // 5. GET /wp-json/dgw/v1/actives hoặc /wp-json/dgw/v1/active (Lấy tất cả hoặc lọc theo query params ?category=...&lang=...&page=...)
        register_rest_route( $this->namespace, '/actives', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $active_controller, 'get_items' ],
            'permission_callback' => [ $active_controller, 'check_permissions' ],
            'args'                => $actives_args,
        ] );

        register_rest_route( $this->namespace, '/active', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $active_controller, 'get_items' ],
            'permission_callback' => [ $active_controller, 'check_permissions' ],
            'args'                => $actives_args,
        ] );

        // 6. GET /wp-json/dgw/v1/actives/{category} (Ví dụ: /dgw/v1/actives/event, /dgw/v1/actives/webinar)
        register_rest_route( $this->namespace, '/actives/(?P<category>[a-zA-Z0-9_-]+)', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $active_controller, 'get_items' ],
            'permission_callback' => [ $active_controller, 'check_permissions' ],
            'args'                => $actives_args,
        ] );

        // 7. GET /wp-json/dgw/v1/active/(?P<id>\d+) (Chi tiết 1 bài viết hoạt động theo ID)
        register_rest_route( $this->namespace, '/active/(?P<id>\d+)', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $active_controller, 'get_item' ],
            'permission_callback' => [ $active_controller, 'check_permissions' ],
            'args'                => [
                'id' => [
                    'required'          => true,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param ) && $param > 0;
                    },
                    'sanitize_callback' => 'absint',
                ],
            ],
        ] );
    }

    /**
     * 21/10/2026: Kiểm tra quyền truy cập API bằng API Secret Key (X-DGW-Key)
     */
    public function check_permissions( \WP_REST_Request $request ) {
        return \DGW_API\Auth::check_permission( $request );
    }

    /**
     * 21/10/2026: Xử lý logic nghiệp vụ, tối ưu hiệu suất response time với Transient Cache
     */
    public function get_data_response( \WP_REST_Request $request ) {
        // Lấy tham số đã được sanitize tự động bởi sanitize_callback
        $limit = $request->get_param( 'limit' );

        // Xử lý Caching bằng Transient để tối ưu hiệu suất / Technical SEO cho API response time
        $transient_key = 'dgw_api_data_' . $limit;
        $data = get_transient( $transient_key );

        if ( false === $data ) {
            // Mockup truy vấn DB hoặc logic phức tạp
            $data = [
                'status' => 'success',
                'message'=> 'Dữ liệu được tạo thành công.',
                'limit'  => $limit,
                'items'  => [
                    ['id' => 1, 'title' => 'Item 1'],
                    ['id' => 2, 'title' => 'Item 2'],
                ]
            ];

            // Lưu cache trong 1 giờ (3600 giây)
            set_transient( $transient_key, $data, HOUR_IN_SECONDS );
        }

        // Trả về JSON response đúng chuẩn của WP REST API
        return rest_ensure_response( $data );
    }
}
