<?php
namespace DGW_API\Models;

// 26/10/2026: Ngăn chặn truy cập trực tiếp bảo vệ an ninh hệ thống WordPress
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 26/10/2026: Model xử lý truy vấn dữ liệu Post Type active (hoạt động/sự kiện) theo mô hình MVC
 */
class Active_Model {

    const POST_TYPE = 'active';
    const TAXONOMY  = 'active_category';

    /**
     * 26/10/2026: Lấy danh sách Active theo danh mục (active_category) và ngôn ngữ (vn/cn/en) hỗ trợ Infinite Scroll (offset/limit) & Transient Cache
     *
     * @param string   $category_slug Slug danh mục (ví dụ: 'event', 'webinar', hoặc rỗng/all để lấy tất cả)
     * @param int      $page          Trang hiện tại (fallback nếu không truyền offset)
     * @param int      $per_page      Số lượng bài viết cần lấy (10 cho lần đầu, 5 cho các lần cuộn tiếp theo)
     * @param string   $lang          Ngôn ngữ lọc ('vn', 'cn', 'en' hoặc 'all')
     * @param int|null $offset        Vị trí bắt đầu lấy dữ liệu cho Infinite Scroll (0, 10, 15, 20...)
     * @return array Dữ liệu danh sách hoạt động và meta Infinite Scroll / phân trang
     */
    public function get_actives( $category_slug = '', $page = 1, $per_page = 10, $lang = 'vn', $offset = null ) {
        $category_slug = ! empty( $category_slug ) ? sanitize_title( $category_slug ) : '';
        $page          = max( 1, absint( $page ) );
        $per_page      = max( 1, min( 50, absint( $per_page ) ) );
        $actual_offset = ( null !== $offset ) ? max( 0, absint( $offset ) ) : ( ( $page - 1 ) * $per_page );

        // 26/10/2026: Chuẩn hoá mã ngôn ngữ hợp lệ (hỗ trợ vn/vi, cn/zh/tw, en, hoặc all)
        $lang_raw = strtolower( trim( (string) $lang ) );
        if ( in_array( $lang_raw, [ 'cn', 'zh', 'tw' ], true ) ) {
            $lang = 'cn';
        } elseif ( in_array( $lang_raw, [ 'en' ], true ) ) {
            $lang = 'en';
        } elseif ( $lang_raw === 'all' ) {
            $lang = 'all';
        } else {
            $lang = 'vn';
        }

        // 26/10/2026: Tối ưu hoá hiệu suất và giảm tải DB bằng Transient Cache theo offset + per_page
        $cache_key   = 'dgw_api_act_' . md5( "{$category_slug}_{$lang}_{$actual_offset}_{$per_page}" );
        $cached_data = get_transient( $cache_key );

        if ( false !== $cached_data ) {
            return $cached_data;
        }

        // 26/10/2026: Thiết lập tham số truy vấn WP_Query đồng bộ thứ tự với Frontend (_metabox_order DESC, ID DESC chống trùng lặp khi cuộn)
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
        ];

        // 26/10/2026: Lọc theo taxonomy active_category nếu có danh mục cụ thể (khác rỗng và khác 'all')
        if ( ! empty( $category_slug ) && 'all' !== $category_slug ) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => self::TAXONOMY,
                    'field'    => 'slug',
                    'terms'    => $category_slug,
                ],
            ];
        }

        // 26/10/2026: Lọc bài viết theo meta ngôn ngữ _metabox_langguage nếu không phải 'all'
        if ( 'all' !== $lang ) {
            $query_args['meta_query'] = [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => $lang,
                    'compare' => '=',
                ],
            ];
        }

        $query = new \WP_Query( $query_args );
        $items = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();

                // 26/10/2026: Lấy danh mục taxonomy của bài viết
                $terms      = get_the_terms( $post_id, self::TAXONOMY );
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

                // 26/10/2026: Lấy các custom meta box đa ngôn ngữ và cấu hình hiển thị
                $post_lang   = get_post_meta( $post_id, '_metabox_langguage', true ) ?: $lang;
                $active_name = get_post_meta( $post_id, "_active_name_{$post_lang}", true );
                $active_desc = get_post_meta( $post_id, "_active_content_{$post_lang}", true );

                // Tiêu đề & nội dung: ưu tiên nội dung theo ngôn ngữ nếu có, fallback về WordPress core
                $title   = ! empty( $active_name ) ? $active_name : get_the_title( $post_id );
                $content = ! empty( $active_desc ) ? $active_desc : get_post_field( 'post_content', $post_id );

                // Tạo excerpt an toàn, không bị lỗi filter đối tượng
                $post_excerpt = get_post_field( 'post_excerpt', $post_id );
                $excerpt      = ! empty( $post_excerpt ) 
                    ? wp_strip_all_tags( $post_excerpt ) 
                    : wp_trim_words( wp_strip_all_tags( $content ), 35, '...' );

                $view_count = get_post_meta( $post_id, '_metabox_view', true );
                $like_count = get_post_meta( $post_id, '_metabox_like', true );
                $order      = get_post_meta( $post_id, '_metabox_order', true );
                $is_home    = get_post_meta( $post_id, '_metabox_home', true );

                $items[] = [
                    'id'          => $post_id,
                    'title'       => html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ),
                    'slug'        => get_post_field( 'post_name', $post_id ),
                    'excerpt'     => $excerpt,
                    'content'     => apply_filters( 'the_content', $content ),
                    'thumbnail'   => get_the_post_thumbnail_url( $post_id, 'full' ) ?: null,
                    'language'    => sanitize_text_field( $post_lang ),
                    'views'       => ! empty( $view_count ) ? absint( $view_count ) : 0,
                    'likes'       => ! empty( $like_count ) ? absint( $like_count ) : 0,
                    'order'       => ! empty( $order ) ? absint( $order ) : 0,
                    'is_home'     => ! empty( $is_home ) ? (bool) $is_home : false,
                    'permalink'   => get_permalink( $post_id ),
                    'date'        => get_the_date( 'c', $post_id ),
                    'categories'  => $categories,
                    'translations'=> [
                        'vn' => [
                            'name'    => get_post_meta( $post_id, '_active_name_vn', true ) ?: '',
                            'content' => get_post_meta( $post_id, '_active_content_vn', true ) ?: '',
                        ],
                        'cn' => [
                            'name'    => get_post_meta( $post_id, '_active_name_cn', true ) ?: '',
                            'content' => get_post_meta( $post_id, '_active_content_cn', true ) ?: '',
                        ],
                        'en' => [
                            'name'    => get_post_meta( $post_id, '_active_name_en', true ) ?: '',
                            'content' => get_post_meta( $post_id, '_active_content_en', true ) ?: '',
                        ],
                    ],
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

        // 26/10/2026: Lưu cache transient trong 30 phút để tăng tốc độ phản hồi API
        set_transient( $cache_key, $result, 30 * MINUTE_IN_SECONDS );

        return $result;
    }

    /**
     * 26/10/2026: Lấy chi tiết một bài viết active theo ID
     *
     * @param int $post_id ID bài viết
     * @return array|null Dữ liệu chi tiết hoặc null nếu không tồn tại
     */
    public function get_active_by_id( $post_id ) {
        $post_id = absint( $post_id );
        if ( ! $post_id ) {
            return null;
        }

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== self::POST_TYPE || $post->post_status !== 'publish' ) {
            return null;
        }

        $terms      = get_the_terms( $post_id, self::TAXONOMY );
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

        $post_excerpt = get_post_field( 'post_excerpt', $post_id );
        $excerpt      = ! empty( $post_excerpt ) 
            ? wp_strip_all_tags( $post_excerpt ) 
            : wp_trim_words( wp_strip_all_tags( $post->post_content ), 35, '...' );

        return [
            'id'          => $post_id,
            'title'       => html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' ),
            'slug'        => $post->post_name,
            'excerpt'     => $excerpt,
            'content'     => apply_filters( 'the_content', $post->post_content ),
            'thumbnail'   => get_the_post_thumbnail_url( $post_id, 'full' ) ?: null,
            'language'    => get_post_meta( $post_id, '_metabox_langguage', true ) ?: 'vn',
            'views'       => absint( get_post_meta( $post_id, '_metabox_view', true ) ),
            'likes'       => absint( get_post_meta( $post_id, '_metabox_like', true ) ),
            'order'       => absint( get_post_meta( $post_id, '_metabox_order', true ) ),
            'is_home'     => (bool) get_post_meta( $post_id, '_metabox_home', true ),
            'permalink'   => get_permalink( $post_id ),
            'date'        => get_the_date( 'c', $post_id ),
            'categories'  => $categories,
            'translations'=> [
                'vn' => [
                    'name'    => get_post_meta( $post_id, '_active_name_vn', true ) ?: '',
                    'content' => get_post_meta( $post_id, '_active_content_vn', true ) ?: '',
                ],
                'cn' => [
                    'name'    => get_post_meta( $post_id, '_active_name_cn', true ) ?: '',
                    'content' => get_post_meta( $post_id, '_active_content_cn', true ) ?: '',
                ],
                'en' => [
                    'name'    => get_post_meta( $post_id, '_active_name_en', true ) ?: '',
                    'content' => get_post_meta( $post_id, '_active_content_en', true ) ?: '',
                ],
            ],
        ];
    }

    /**
     * 26/10/2026: Xóa toàn bộ transient cache của CPT Active khi có cập nhật hoặc xóa bài viết
     */
    public static function clear_cache() {
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_dgw_api_act_%' OR option_name LIKE '_transient_timeout_dgw_api_act_%'" );
    }
}
