<?php
namespace DGW_API\Controllers;

use DGW_API\Models\Resource_Model;

// 21/10/2026: Ngăn chặn truy cập trực tiếp bảo vệ an ninh hệ thống
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 21/10/2026: Controller tiếp nhận và xử lý REST API request cho Post Type resources
 */
class Resource_Controller {

    /**
     * @var Resource_Model
     */
    private $model;

    public function __construct() {
        $this->model = new Resource_Model();
    }

    /**
     * 21/10/2026: Kiểm tra quyền truy cập API bằng API Secret Key (X-DGW-Key)
     */
    public function check_permissions( \WP_REST_Request $request ) {
        return \DGW_API\Auth::check_permission( $request );
    }

    /**
     * 26/10/2026: Xử lý request lấy danh sách Resources hỗ trợ Infinite Scroll (mặc định 10 dòng đầu, các lần cuộn tiếp theo 5 dòng)
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_items( \WP_REST_Request $request ) {
        $category = $request->get_param( 'category' ) ?: 'download';
        $lang     = $request->get_param( 'lang' ) ?: 'vn';
        $offset   = $request->get_param( 'offset' );
        $page     = $request->get_param( 'page' ) ?: 1;

        $query_params = $request->get_query_params();
        $has_explicit_limit = isset( $query_params['per_page'] ) || isset( $query_params['limit'] );
        if ( $has_explicit_limit ) {
            $per_page = $request->get_param( 'limit' ) ?: ( $request->get_param( 'per_page' ) ?: 10 );
        } else {
            $per_page = ( null !== $offset && (int) $offset > 0 ) ? 5 : 10;
        }

        $data = $this->model->get_resources_by_category( $category, $page, $per_page, $lang, $offset );

        return rest_ensure_response( [
            'status'     => 'success',
            'category'   => sanitize_title( $category ),
            'language'   => in_array( strtolower( $lang ), [ 'cn', 'zh', 'tw' ], true ) ? 'cn' : 'vn',
            'has_more'   => $data['pagination']['has_more'],
            'data'       => $data['items'],
            'pagination' => $data['pagination'],
        ] );
    }
}
