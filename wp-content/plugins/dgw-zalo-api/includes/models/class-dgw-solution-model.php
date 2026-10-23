<?php
namespace DGW_API\Models;

// 21/10/2026: Ngăn chặn truy cập trực tiếp bảo vệ an ninh hệ thống
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 21/10/2026: Model xử lý truy vấn dữ liệu Post Type solutions theo chuẩn MVC
 */
class Solution_Model {

    const POST_TYPE = 'solutions';
    const TAXONOMY  = 'solutions_category';

    /**
     * 21/10/2026: Lấy danh sách Solutions theo Category Slug và Ngôn ngữ (vn/cn) với Caching tối ưu hiệu năng
     * 
     * @param string $category_slug Slug danh mục (mặc định: 'tiptop')
     * @param int    $page          Trang hiện tại
     * @param int    $per_page      Số lượng bài viết mỗi trang
     * @param string $lang          Ngôn ngữ ('vn': Tiếng Việt, 'cn': Tiếng Trung)
     * @return array Dữ liệu bài viết và thông tin phân trang
     */
    public function get_solutions_by_category( $category_slug = 'tiptop', $page = 1, $per_page = 10, $lang = 'vn' ) {
        $category_slug = sanitize_title( $category_slug );
        $page          = max( 1, absint( $page ) );
        $per_page      = max( 1, min( 50, absint( $per_page ) ) );
        
        // 21/10/2026: Chuẩn hoá mã ngôn ngữ (hỗ trợ vn/vi -> vn, cn/zh/tw -> cn)
        $lang = in_array( strtolower( $lang ), [ 'cn', 'zh', 'tw' ], true ) ? 'cn' : 'vn';

        // 21/10/2026: Tối ưu hiệu suất bằng Transient Cache theo từng ngôn ngữ
        $cache_key   = 'dgw_api_sol_' . md5( "{$category_slug}_{$lang}_{$page}_{$per_page}" );
        $cached_data = get_transient( $cache_key );

        if ( false !== $cached_data ) {
            return $cached_data;
        }

        $query_args = [
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => [
                [
                    'taxonomy' => self::TAXONOMY,
                    'field'    => 'slug',
                    'terms'    => $category_slug,
                ],
            ],
            // 21/10/2026: Lọc chính xác theo trường meta ngôn ngữ _metabox_langguage
            'meta_query'     => [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => $lang,
                    'compare' => '=',
                ],
            ],
        ];

        $query = new \WP_Query( $query_args );
        $items = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();

                // Lấy danh mục taxonomy
                $terms = get_the_terms( $post_id, self::TAXONOMY );
                $categories = [];
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    foreach ( $terms as $term ) {
                        $categories[] = [
                            'id'   => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug,
                        ];
                    }
                }

                // 21/10/2026: Format dữ liệu đầu ra chuẩn hoá và an toàn (escape/sanitize)
                $items[] = [
                    'id'         => $post_id,
                    'title'      => get_the_title(),
                    'slug'       => get_post_field( 'post_name', $post_id ),
                    'excerpt'    => wp_strip_all_tags( get_the_excerpt() ),
                    'content'    => apply_filters( 'the_content', get_the_content() ),
                    'thumbnail'  => get_the_post_thumbnail_url( $post_id, 'full' ) ?: null,
                    'language'   => $lang,
                    'permalink'  => get_permalink( $post_id ),
                    'date'       => get_the_date( 'c', $post_id ),
                    'categories' => $categories,
                ];
            }
            wp_reset_postdata();
        }

        $result = [
            'items'      => $items,
            'pagination' => [
                'total_items'  => (int) $query->found_posts,
                'total_pages'  => (int) $query->max_num_pages,
                'current_page' => $page,
                'per_page'     => $per_page,
            ],
        ];

        // Lưu cache 30 phút (1800 giây)
        set_transient( $cache_key, $result, 30 * MINUTE_IN_SECONDS );

        return $result;
    }

    /**
     * 22/10/2026: Xóa toàn bộ transient cache của Solutions khi có thay đổi bài viết
     */
    public static function clear_cache() {
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_dgw_api_sol_%' OR option_name LIKE '_transient_timeout_dgw_api_sol_%'" );
    }
}
