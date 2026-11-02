<?php
namespace DGW_API\Helpers;

// 30/10/2026: Ngăn chặn truy cập trực tiếp bảo vệ an ninh hệ thống WordPress
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 30/10/2026: Helper xử lý làm sạch nội dung bài viết tạo bởi Divi Builder và bóc tách cấu trúc Tabs trước khi gửi qua API cho Zalo Mini App.
 * Các chức năng chính:
 * 1. Đảm bảo shortcode Divi được đăng ký và render thành HTML hoàn chỉnh trong môi trường REST API.
 * 2. Nhận diện và bóc tách Module Tabs của Divi ([et_pb_tabs] / .et_pb_tabs) thành mảng cấu trúc JSON (content_blocks) để Zalo Mini App dựng UI Tabs tương tác.
 * 3. Chuyển đổi các class Tab đặc thù của Divi sang class chuẩn BEM (dgw-tabs, dgw-tabs__nav, dgw-tabs__tab, dgw-tabs__panels, dgw-tabs__panel) trong HTML.
 * 4. Loại bỏ toàn bộ các CSS class layout dư thừa của Divi (et_pb_*, et_*, et-*, clearfix, et-last-child, v.v.).
 * 5. Giữ nguyên toàn bộ nội dung văn bản và các thẻ HTML chuẩn (<p>, <h1>-<h6>, <img>, <a>, <ul>, <ol>, <li>, <table>, <div>, <span>, <strong>, <em>, v.v.).
 */
class Content_Cleaner {

    /**
     * 30/10/2026: Bảng ánh xạ class Divi Tabs sang class chuẩn BEM cho Zalo Mini App
     */
    private const DIVI_TABS_CLASS_MAP = [
        'et_pb_tabs'          => 'dgw-tabs',
        'et_pb_tabs_controls' => 'dgw-tabs__nav',
        'et_pb_tab_active'    => 'dgw-tabs__tab--active',
        'et_pb_all_tabs'      => 'dgw-tabs__panels',
        'et_pb_tab'           => 'dgw-tabs__panel',
        'et_pb_tab_content'   => 'dgw-tabs__panel-content',
    ];

    /**
     * 30/10/2026: Khởi tạo các thành phần giao diện của Divi Builder trong môi trường REST API
     */
    public static function init_divi_in_rest() {
        if ( function_exists( 'et_builder_add_main_elements' ) && ! did_action( 'et_builder_ready' ) ) {
            et_builder_add_main_elements();
        }
    }

    /**
     * 30/10/2026: Xử lý và làm sạch HTML từ nội dung bài viết Divi, loại bỏ class Divi thừa và chuẩn hóa class BEM cho Tabs
     *
     * @param string $content Nội dung bài viết thô (chứa shortcode Divi hoặc HTML)
     * @return string Nội dung HTML sạch chuẩn cho Zalo Mini App
     */
    public static function clean_html( $content ) {
        if ( empty( $content ) ) {
            return '';
        }

        // Đảm bảo các thành phần builder được nạp nếu phát hiện shortcode Divi
        if ( false !== strpos( $content, '[et_pb_' ) ) {
            self::init_divi_in_rest();
        }

        // Sử dụng cờ tĩnh chống lặp vô hạn nếu gọi lồng bên trong filter the_content
        static $is_processing = false;
        if ( $is_processing ) {
            return self::strip_divi_markup( $content );
        }

        $is_processing = true;
        $html = apply_filters( 'the_content', $content );
        $html = self::strip_divi_markup( $html );
        $is_processing = false;

        return $html;
    }

    /**
     * 30/10/2026: Bóc tách nội dung bài viết thành mảng các khối (content_blocks: 'html' và 'tabs')
     * Giúp Zalo Mini App nhận diện chính xác vị trí và dữ liệu của từng Tab để render component <Tabs> chuẩn.
     *
     * @param string $content Nội dung bài viết thô (chứa shortcode [et_pb_tabs] hoặc HTML .et_pb_tabs)
     * @return array Danh sách các block nội dung theo đúng thứ tự xuất hiện trong bài viết
     */
    public static function parse_content_blocks( $content ) {
        if ( empty( $content ) ) {
            return [];
        }

        $blocks = [];

        // Trường hợp 1: Nội dung chứa shortcode [et_pb_tabs]...[/et_pb_tabs] của Divi
        if ( false !== stripos( $content, '[et_pb_tabs' ) ) {
            $parts = preg_split(
                '/(\[et_pb_tabs[^\]]*\].*?\[\/et_pb_tabs\])/is',
                $content,
                -1,
                PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
            );

            foreach ( $parts as $part ) {
                $trimmed_part = trim( $part );
                if ( '' === $trimmed_part ) {
                    continue;
                }

                // Nếu là khối shortcode [et_pb_tabs]
                if ( preg_match( '/^\[et_pb_tabs/i', $trimmed_part ) ) {
                    $tabs_items = self::extract_tabs_from_shortcode( $trimmed_part );
                    if ( ! empty( $tabs_items ) ) {
                        $blocks[] = [
                            'type'  => 'tabs',
                            'items' => $tabs_items,
                        ];
                    }
                } else {
                    // Nội dung văn bản/HTML bên ngoài khối Tabs
                    $cleaned_html = self::clean_html( $trimmed_part );
                    if ( '' !== $cleaned_html ) {
                        $blocks[] = [
                            'type'    => 'html',
                            'content' => $cleaned_html,
                        ];
                    }
                }
            }

            return $blocks;
        }

        // Trường hợp 2: Nội dung đã được render thành HTML và chứa cấu trúc DOM của Divi Tabs (.et_pb_tabs hoặc .dgw-tabs)
        $cleaned_full_html = self::clean_html( $content );
        if ( false !== strpos( $cleaned_full_html, 'dgw-tabs' ) ) {
            return self::extract_blocks_from_rendered_html( $cleaned_full_html );
        }

        // Trường hợp 3: Bài viết chỉ có nội dung HTML thông thường
        if ( '' !== $cleaned_full_html ) {
            $blocks[] = [
                'type'    => 'html',
                'content' => $cleaned_full_html,
            ];
        }

        return $blocks;
    }

    /**
     * 30/10/2026: Bóc tách danh sách các tab con từ chuỗi shortcode [et_pb_tabs]...[/et_pb_tabs]
     *
     * @param string $tabs_shortcode Chuỗi shortcode [et_pb_tabs]
     * @return array Danh sách từng tab gồm id, index, title, is_active, content
     */
    private static function extract_tabs_from_shortcode( $tabs_shortcode ) {
        $items = [];

        if ( preg_match_all( '/\[et_pb_tab([^\]]*)\](.*?)\[\/et_pb_tab\]/is', $tabs_shortcode, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $index => $match ) {
                $atts      = shortcode_parse_atts( $match[1] );
                $raw_title = isset( $atts['title'] ) ? $atts['title'] : 'Tab ' . ( $index + 1 );
                $tab_title = html_entity_decode( wp_strip_all_tags( $raw_title ), ENT_QUOTES, 'UTF-8' );
                $tab_body  = self::clean_html( $match[2] );

                $items[] = [
                    'id'        => 'tab-' . $index,
                    'index'     => $index,
                    'title'     => trim( $tab_title ),
                    'is_active' => ( 0 === $index ),
                    'content'   => $tab_body,
                ];
            }
        }

        return $items;
    }

    /**
     * 30/10/2026: Bóc tách các khối HTML và khối Tabs từ chuỗi HTML đã được render (dùng DOMDocument)
     *
     * @param string $html Chuỗi HTML đã qua xử lý clean_html
     * @return array Mảng các block 'html' và 'tabs'
     */
    private static function extract_blocks_from_rendered_html( $html ) {
        if ( ! class_exists( '\DOMDocument' ) ) {
            return [
                [
                    'type'    => 'html',
                    'content' => $html,
                ],
            ];
        }

        $blocks = [];
        $dom    = new \DOMDocument( '1.0', 'UTF-8' );
        libxml_use_internal_errors( true );
        $dom->loadHTML( '<?xml encoding="UTF-8"><div id="dgw-root-wrapper">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        libxml_clear_errors();

        $xpath = new \DOMXPath( $dom );
        $root  = $dom->getElementById( 'dgw-root-wrapper' );

        if ( ! $root ) {
            return [
                [
                    'type'    => 'html',
                    'content' => $html,
                ],
            ];
        }

        // Tìm tất cả các node .dgw-tabs
        $tab_nodes = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " dgw-tabs ")]', $root );
        if ( ! $tab_nodes || 0 === $tab_nodes->length ) {
            return [
                [
                    'type'    => 'html',
                    'content' => $html,
                ],
            ];
        }

        // Thay thế từng node .dgw-tabs bằng placeholder để cắt chuỗi HTML chính xác
        $parsed_tabs_map = [];
        foreach ( $tab_nodes as $idx => $tab_node ) {
            $titles   = [];
            $contents = [];

            $nav_items = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " dgw-tabs__nav ")]/li', $tab_node );
            if ( $nav_items ) {
                foreach ( $nav_items as $li ) {
                    $titles[] = trim( $li->textContent );
                }
            }

            $panel_items = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " dgw-tabs__panel ")]', $tab_node );
            if ( $panel_items ) {
                foreach ( $panel_items as $panel ) {
                    $content_node = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " dgw-tabs__panel-content ")]', $panel )->item( 0 );
                    $target_node  = $content_node ?: $panel;
                    $inner_html   = '';
                    foreach ( $target_node->childNodes as $child ) {
                        $inner_html .= $dom->saveHTML( $child );
                    }
                    $contents[] = trim( $inner_html );
                }
            }

            $tab_items = [];
            $count     = max( count( $titles ), count( $contents ) );
            for ( $i = 0; $i < $count; $i++ ) {
                $tab_items[] = [
                    'id'        => 'tab-' . $i,
                    'index'     => $i,
                    'title'     => $titles[ $i ] ?? ( 'Tab ' . ( $i + 1 ) ),
                    'is_active' => ( 0 === $i ),
                    'content'   => $contents[ $i ] ?? '',
                ];
            }

            $placeholder = "<!--DGW_TABS_PLACEHOLDER_{$idx}-->";
            $parsed_tabs_map[ $placeholder ] = $tab_items;

            $comment_node = $dom->createComment( "DGW_TABS_PLACEHOLDER_{$idx}" );
            $tab_node->parentNode->replaceChild( $comment_node, $tab_node );
        }

        $modified_html = '';
        foreach ( $root->childNodes as $child ) {
            $modified_html .= $dom->saveHTML( $child );
        }

        $parts = preg_split( '/(<!--DGW_TABS_PLACEHOLDER_\d+-->)/', $modified_html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
        foreach ( $parts as $part ) {
            $trimmed = trim( $part );
            if ( '' === $trimmed ) {
                continue;
            }
            if ( isset( $parsed_tabs_map[ $trimmed ] ) ) {
                $blocks[] = [
                    'type'  => 'tabs',
                    'items' => $parsed_tabs_map[ $trimmed ],
                ];
            } else {
                // Loại bỏ các thẻ div rỗng bao ngoài sau khi tách tab
                $clean_fragment = self::strip_divi_markup( $trimmed );
                if ( '' !== $clean_fragment ) {
                    $blocks[] = [
                        'type'    => 'html',
                        'content' => $clean_fragment,
                    ];
                }
            }
        }

        return $blocks;
    }

    /**
     * 30/10/2026: Tách bỏ toàn bộ CSS class layout dư thừa của Divi khỏi chuỗi HTML,
     * đồng thời giữ lại và chuyển đổi các class của Module Tabs sang chuẩn BEM (dgw-tabs*)
     *
     * @param string $html Chuỗi HTML đã được render
     * @return string Chuỗi HTML đã lọc sạch class Divi thừa và chuẩn hóa class BEM cho Tabs
     */
    public static function strip_divi_markup( $html ) {
        if ( empty( $html ) ) {
            return '';
        }

        // 1. Chuyển đổi class Tab của Divi sang class BEM chuẩn (dgw-tabs*) và loại bỏ các class layout của Divi
        $html = preg_replace_callback( '/\bclass=(["\'])(.*?)\1/i', function( $matches ) {
            $classes = preg_split( '/\s+/', trim( $matches[2] ) );
            $clean   = [];
            foreach ( $classes as $c ) {
                $c = trim( $c );
                if ( '' === $c ) {
                    continue;
                }

                $lower_c = strtolower( $c );

                // Ánh xạ các class đặc trưng của Divi Tabs sang class chuẩn BEM
                if ( isset( self::DIVI_TABS_CLASS_MAP[ $lower_c ] ) ) {
                    $clean[] = self::DIVI_TABS_CLASS_MAP[ $lower_c ];
                    continue;
                }

                // Nhận diện các item tab có số thứ tự (ví dụ: et_pb_tab_0, et_pb_tab_1...)
                if ( preg_match( '/^et_pb_tab_(\d+)$/i', $lower_c, $tab_match ) ) {
                    $clean[] = 'dgw-tabs__item';
                    $clean[] = 'dgw-tabs__item--' . $tab_match[1];
                    continue;
                }

                // Bỏ qua các class layout chung thuộc Divi Framework
                if ( preg_match( '/^et[-_]/i', $c ) || in_array( $lower_c, [ 'clearfix', 'et-last-child' ], true ) ) {
                    continue;
                }
                $clean[] = $c;
            }

            $clean = array_values( array_unique( $clean ) );

            // Nếu không còn class nào thì xóa luôn thuộc tính class
            if ( empty( $clean ) ) {
                return '';
            }
            return 'class="' . esc_attr( implode( ' ', $clean ) ) . '"';
        }, $html );

        // 2. Xóa inline style chiều cao cố định do Divi tự gán trên thẻ <li> của Tab (vd: style="height: 33.2px;")
        $html = preg_replace( '/(<li[^>]*dgw-tabs__item[^>]*)\s+style=(["\'])\s*height:\s*[^"\']+;\s*\2/i', '$1', $html );

        // 3. Xóa các thuộc tính class rỗng còn sót lại (ví dụ class="" hoặc class='')
        $html = preg_replace( '/\s+class=(["\'])\s*\1/i', '', $html );

        // 4. Xóa các thuộc tính data-* đặc thù của Divi
        $html = preg_replace( '/\s+data-et[-_][a-z0-9_-]*=(["\']).*?\1/i', '', $html );

        // 5. Chuẩn hóa khoảng trắng trong thẻ mở HTML (ví dụ: <div  > thành <div>)
        $html = preg_replace( '/<([a-z0-9]+)\s+>/i', '<$1>', $html );
        $html = preg_replace( '/<([a-z0-9]+)\s{2,}/i', '<$1 ', $html );

        // 6. Xóa các thẻ rỗng không mang nội dung do Divi sinh ra (div, span rỗng không có class)
        do {
            $prev = $html;
            $html = preg_replace( '/<(div|span)(?!\s+class=)\s*>\s*<\/\1>/i', '', $html );
        } while ( $prev !== $html );

        // 7. Xóa dự phòng các thẻ shortcode Divi chưa được render (nếu có lỗi sót)
        $html = preg_replace( '/\[\/?et_pb_[^\]]*\]/i', '', $html );

        // 8. Tối ưu khoảng trắng và ngắt dòng dư thừa để giảm dung lượng JSON response
        $html = preg_replace( '/[ \t]+/', ' ', $html );
        $html = preg_replace( '/\s*[\r\n]+\s*/', "\n", $html );

        return trim( $html );
    }

    /**
     * 30/10/2026: Tạo đoạn trích tóm tắt (excerpt) sạch từ nội dung bài viết Divi
     *
     * @param string $content   Nội dung bài viết
     * @param int    $num_words Số từ cần lấy
     * @return string Chuỗi văn bản thuần túy không chứa mã shortcode hay thẻ HTML
     */
    public static function clean_excerpt( $content, $num_words = 35 ) {
        if ( empty( $content ) ) {
            return '';
        }

        // Bỏ toàn bộ tag shortcode Divi
        $clean = preg_replace( '/\[\/?et_pb_[^\]]*\]/i', '', $content );
        // Bỏ shortcode WordPress chung
        $clean = strip_shortcodes( $clean );
        // Bỏ toàn bộ thẻ HTML
        $clean = wp_strip_all_tags( $clean );
        // Rút gọn theo số lượng từ
        return wp_trim_words( $clean, $num_words, '...' );
    }
}
