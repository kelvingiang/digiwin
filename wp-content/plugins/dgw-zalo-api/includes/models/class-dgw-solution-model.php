<?php
namespace DGW_API\Models;

use DGW_API\Helpers\Content_Cleaner;

// 21/10/2026: Ngăn chặn truy cập trực tiếp bảo vệ an ninh hệ thống
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 21/10/2026: Model xử lý truy vấn dữ liệu Post Type solutions theo chuẩn MVC
 * 30/10/2026: Tích hợp Content_Cleaner để lọc sạch class Divi cho Zalo Mini App
 */
class Solution_Model {

    const POST_TYPE = 'solutions';
    const TAXONOMY  = 'solutions_category';

    /**
     * 26/10/2026: Lấy danh sách Solutions theo Category Slug và Ngôn ngữ (vn/cn) hỗ trợ Infinite Scroll (offset/limit) & Transient Cache
     * 30/10/2026: Lọc sạch class Divi trong content và excerpt
     * 
     * @param string   $category_slug Slug danh mục (mặc định: 'tiptop')
     * @param int      $page          Trang hiện tại (fallback nếu không truyền offset)
     * @param int      $per_page      Số lượng bài viết cần lấy (10 cho lần đầu, 5 cho các lần cuộn tiếp theo)
     * @param string   $lang          Ngôn ngữ ('vn': Tiếng Việt, 'cn': Tiếng Trung)
     * @param int|null $offset        Vị trí bắt đầu lấy dữ liệu cho Infinite Scroll (0, 10, 15, 20...)
     * @return array Dữ liệu bài viết và thông tin phân trang / Infinite Scroll
     */
    public function get_solutions_by_category( $category_slug = 'tiptop', $page = 1, $per_page = 10, $lang = 'vn', $offset = null ) {
        $category_slug = sanitize_title( $category_slug );
        $page          = max( 1, absint( $page ) );
        $per_page      = max( 1, min( 50, absint( $per_page ) ) );
        $actual_offset = ( null !== $offset ) ? max( 0, absint( $offset ) ) : ( ( $page - 1 ) * $per_page );
        
        // 21/10/2026: Chuẩn hoá mã ngôn ngữ (hỗ trợ vn/vi -> vn, cn/zh/tw -> cn)
        $lang = in_array( strtolower( $lang ), [ 'cn', 'zh', 'tw' ], true ) ? 'cn' : 'vn';

        // 30/10/2026: Tối ưu hiệu suất bằng Transient Cache theo offset + per_page (v2 hỗ trợ content_blocks)
        $cache_key   = 'dgw_api_sol_v2_' . md5( "{$category_slug}_{$lang}_{$actual_offset}_{$per_page}" );
        $cached_data = get_transient( $cache_key );

        if ( false !== $cached_data ) {
            return $cached_data;
        }

        $query_args = [
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'offset'         => $actual_offset,
            'orderby'        => [
                'meta_value_num' => 'DESC',
                'date'           => 'DESC',
                'ID'             => 'DESC',
            ],
            'meta_key'       => '_metabox_order',
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

                $raw_content = get_the_content();
                $post_excerpt = get_post_field( 'post_excerpt', $post_id );
                $excerpt      = ! empty( $post_excerpt ) 
                    ? wp_strip_all_tags( $post_excerpt ) 
                    : Content_Cleaner::clean_excerpt( $raw_content, 35 );

                // 21/10/2026 & 30/10/2026: Format dữ liệu đầu ra chuẩn hoá và an toàn (lọc sạch class Divi, giữ HTML chuẩn + content_blocks)
                $items[] = [
                    'id'             => $post_id,
                    'title'          => get_the_title(),
                    'slug'           => get_post_field( 'post_name', $post_id ),
                    'excerpt'        => $excerpt,
                    'content'        => Content_Cleaner::clean_html( $raw_content ),
                    'content_blocks' => Content_Cleaner::parse_content_blocks( $raw_content ),
                    'thumbnail'      => get_the_post_thumbnail_url( $post_id, 'full' ) ?: null,
                    'order'          => (int) get_post_meta( $post_id, '_metabox_order', true ),
                    '_metabox_order' => (int) get_post_meta( $post_id, '_metabox_order', true ),
                    'language'       => $lang,
                    'permalink'      => get_permalink( $post_id ),
                    'date'           => get_the_date( 'c', $post_id ),
                    'categories'     => $categories,
                ];
            }
            wp_reset_postdata();
        }

        $total_items    = (int) $query->found_posts;
        $returned_count = count( $items );
        $loaded_total   = $actual_offset + $returned_count;
        $has_more       = $returned_count > 0 && $loaded_total < $total_items;

        $result = [
            'items'      => $items,
            'pagination' => [
                'total_items'    => $total_items,
                'offset'         => $actual_offset,
                'per_page'       => $per_page,
                'returned_count' => $returned_count,
                'next_offset'    => $has_more ? $loaded_total : null,
                'has_more'       => $has_more,
                'current_page'   => $page,
                'total_pages'    => (int) ceil( $total_items / max( 1, $per_page ) ),
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
