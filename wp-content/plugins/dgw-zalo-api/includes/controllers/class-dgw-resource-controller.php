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
     * 21/10/2026: Xử lý request lấy danh sách Resources theo category và ngôn ngữ (vn/cn)
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_items( \WP_REST_Request $request ) {
        $category = $request->get_param( 'category' ) ?: 'download';
        $page     = $request->get_param( 'page' ) ?: 1;
        $per_page = $request->get_param( 'per_page' ) ?: 10;
        $lang     = $request->get_param( 'lang' ) ?: 'vn';

        $data = $this->model->get_resources_by_category( $category, $page, $per_page, $lang );

        return rest_ensure_response( [
            'status'     => 'success',
            'category'   => sanitize_title( $category ),
            'language'   => in_array( strtolower( $lang ), [ 'cn', 'zh', 'tw' ], true ) ? 'cn' : 'vn',
            'data'       => $data['items'],
            'pagination' => $data['pagination'],
        ] );
    }
}
