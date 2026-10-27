<?php
namespace DGW_API\Controllers;

use DGW_API\Models\Active_Model;

// 26/10/2026: Ngăn chặn truy cập trực tiếp bảo vệ an ninh hệ thống
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 26/10/2026: Controller tiếp nhận và xử lý REST API request cho Post Type active theo chuẩn MVC
 */
class Active_Controller {

    /**
     * @var Active_Model
     */
    private $model;

    public function __construct() {
        $this->model = new Active_Model();
    }

    /**
     * 26/10/2026: Kiểm tra quyền truy cập API bằng API Secret Key (X-DGW-Key)
     *
     * @param \WP_REST_Request $request
     * @return bool|\WP_Error
     */
    public function check_permissions( \WP_REST_Request $request ) {
        return \DGW_API\Auth::check_permission( $request );
    }

    /**
     * 26/10/2026: Xử lý request lấy danh sách Active (hoạt động) hỗ trợ Infinite Scroll (mặc định 10 dòng đầu, các lần cuộn tiếp theo 5 dòng)
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_items( \WP_REST_Request $request ) {
        $category = $request->get_param( 'category' ) ?: '';
        $lang     = $request->get_param( 'lang' ) ?: 'vn';
        $offset   = $request->get_param( 'offset' );
        $page     = $request->get_param( 'page' ) ?: 1;

        // 26/10/2026: Nếu client truyền offset > 0 mà không truyền per_page/limit riêng, tự động lấy 5 dòng tiếp theo; lần đầu (offset=0) lấy 10 dòng
        $query_params = $request->get_query_params();
        $has_explicit_limit = isset( $query_params['per_page'] ) || isset( $query_params['limit'] );
        if ( $has_explicit_limit ) {
            $per_page = $request->get_param( 'limit' ) ?: ( $request->get_param( 'per_page' ) ?: 10 );
        } else {
            $per_page = ( null !== $offset && (int) $offset > 0 ) ? 5 : 10;
        }

        $data = $this->model->get_actives( $category, $page, $per_page, $lang, $offset );

        return rest_ensure_response( [
            'status'     => 'success',
            'post_type'  => 'active',
            'category'   => ! empty( $category ) ? sanitize_title( $category ) : 'all',
            'language'   => sanitize_key( $lang ),
            'has_more'   => $data['pagination']['has_more'],
            'data'       => $data['items'],
            'pagination' => $data['pagination'],
        ] );
    }

    /**
     * 26/10/2026: Xử lý request lấy chi tiết một bài viết Active theo ID
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_item( \WP_REST_Request $request ) {
        $id = absint( $request->get_param( 'id' ) );
        if ( ! $id ) {
            return new \WP_Error(
                'rest_invalid_id',
                'ID bài viết không hợp lệ.',
                [ 'status' => 400 ]
            );
        }

        $item = $this->model->get_active_by_id( $id );
        if ( ! $item ) {
            return new \WP_Error(
                'rest_post_not_found',
                'Không tìm thấy bài viết hoạt động với ID đã cung cấp.',
                [ 'status' => 404 ]
            );
        }

        return rest_ensure_response( [
            'status' => 'success',
            'data'   => $item,
        ] );
    }
}
