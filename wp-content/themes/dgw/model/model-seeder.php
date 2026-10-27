<?php
/**
 * [26/10/2026] - Model xử lý logic tạo dữ liệu mẫu (Dummy Data Seeder) và dọn dẹp dữ liệu test theo chuẩn MVC.
 * Hỗ trợ tự động ánh xạ Post Type -> Taxonomy -> Category, tự sinh Title/Content chuẩn ERP B2B,
 * gắn Meta đa ngôn ngữ (_metabox_langguage), thứ tự (_metabox_order), trang chủ (_metabox_home) và ảnh đại diện.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Model_Seeder
{
    /**
     * [26/10/2026] - Meta key đánh dấu bài viết được sinh tự động để có thể xóa sạch chỉ với 1 click
     */
    const DUMMY_META_KEY = '_dgw_is_dummy_data';

    /**
     * [26/10/2026] - Lấy danh sách tất cả Post Type hợp lệ kèm theo Taxonomy và Terms tương ứng
     *
     * @return array Mảng cấu trúc Post Type, Taxonomies và danh sách Terms
     */
    public function getPostTypesWithTaxonomies(): array
    {
        $excluded = array(
            'attachment',
            'revision',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'wp_block',
            'wp_template',
            'wp_template_part',
            'wp_global_styles',
            'wp_navigation',
            'project',
        );

        $post_types = get_post_types(array('show_ui' => true), 'objects');
        $result = array();

        foreach ($post_types as $slug => $pt_obj) {
            if (in_array($slug, $excluded, true)) {
                continue;
            }

            $tax_objects = get_object_taxonomies($slug, 'objects');
            $taxonomies = array();

            foreach ($tax_objects as $tax_slug => $tax_obj) {
                if (!$tax_obj->show_ui) {
                    continue;
                }

                $terms = get_terms(array(
                    'taxonomy'   => $tax_slug,
                    'hide_empty' => false,
                ));

                $term_list = array();
                if (!is_wp_error($terms) && !empty($terms)) {
                    foreach ($terms as $term) {
                        // [26/10/2026] - Lấy thêm tên tiếng Việt lưu trong wp_options của theme dgw (nếu có)
                        $opt_vn = get_option('option_' . $tax_slug . '_' . $term->term_id);
                        $vn_label = (!empty($opt_vn['cate_vn'])) ? ' (' . $opt_vn['cate_vn'] . ')' : '';

                        $term_list[] = array(
                            'term_id' => (int) $term->term_id,
                            'name'    => $term->name . $vn_label,
                            'slug'    => $term->slug,
                        );
                    }
                }

                $taxonomies[$tax_slug] = array(
                    'slug'         => $tax_slug,
                    'label'        => $tax_obj->labels->name . ' (' . $tax_slug . ')',
                    'hierarchical' => (bool) $tax_obj->hierarchical,
                    'terms'        => $term_list,
                );
            }

            $result[$slug] = array(
                'slug'       => $slug,
                'label'      => $pt_obj->labels->name . ' (' . $slug . ')',
                'taxonomies' => $taxonomies,
            );
        }

        return $result;
    }

    /**
     * [26/10/2026] - Lấy danh sách ID ảnh có sẵn trong Media Library để gắn ngẫu nhiên Featured Image
     *
     * @return array Danh sách attachment ID
     */
    private function getRandomAttachmentIds(): array
    {
        global $wpdb;
        // [26/10/2026] - Sử dụng prepared statement truy vấn nhanh các attachment là hình ảnh
        $sql = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_mime_type LIKE %s ORDER BY ID DESC LIMIT 50",
            'attachment',
            'image/%'
        );
        $ids = $wpdb->get_col($sql);
        return is_array($ids) ? array_map('intval', $ids) : array();
    }

    /**
     * [26/10/2026] - Sinh tiêu đề và nội dung ngẫu nhiên theo ngữ cảnh B2B / ERP Digiwin (Tiếng Việt hoặc Tiếng Trung)
     *
     * @param string $lang         Ngôn ngữ ('vn' hoặc 'cn')
     * @param string $post_type    Loại bài viết
     * @param int    $index        Số thứ tự dòng đang tạo
     * @param string $title_prefix Tiền tố tiêu đề tùy chọn
     * @param string $custom_body  Nội dung tùy chọn
     * @return array ['title' => string, 'content' => string, 'excerpt' => string]
     */
    private function generateRandomPostData(string $lang, string $post_type, int $index, string $title_prefix = '', string $custom_body = ''): array
    {
        if ($lang === 'cn') {
            $topics = array(
                '智慧製造與MES系統整合方案',
                'ERP企業資源規劃數位轉型實踐',
                'AIoT工業物聯網提升產線稼動率',
                '電子組裝業供應鏈協同管理案例',
                '五金加工與模具業智慧工廠升級指南',
                'WMS智慧倉儲條碼自動化管理系統',
                'ESG永續經營與碳盤查數據追蹤平台',
                '集團財務與成本精算一體化解決方案',
            );
            $paragraphs = array(
                '在數位轉型浪潮下，製造業面臨訂單少量多樣、交期縮短與成本上升的多重挑戰。透過導入鼎捷智慧製造系統，企業能即時掌握生產進度與設備狀態。',
                '本方案結合ERP與MES核心模組，打通從業務接單、物料需求規劃（MRP）、現場派工到品質檢驗的完整數據鏈，消除資訊孤島並提升決策效率。',
                '實際導入數據顯示，產線在製品（WIP）庫存降低25%，訂單準時達交率提升至96%以上，財務結帳週期縮短50%，全面強化企業市場競爭力。',
                '系統支援多語系、多幣別與跨國集團帳務合併，特別適合在越南設廠的台資與外資製造企業快速落地應用。',
            );
        } else {
            $topics = array(
                'Giải pháp chuyển đổi số nhà máy thông minh với hệ thống MES',
                'Tối ưu hóa quy trình vận hành doanh nghiệp sản xuất cùng ERP Digiwin',
                'Ứng dụng AIoT giám sát thiết bị và nâng cao hiệu suất OEE toàn diện',
                'Case Study: Quản trị chuỗi cung ứng ngành điện tử và linh kiện bán dẫn',
                'Tự động hóa kho thông minh WMS bằng mã vạch QR/Barcode thời gian thực',
                'Giải pháp tính giá thành sản xuất thực tế và hợp nhất báo cáo tài chính',
                'Lộ trình nâng cấp nhà máy sản xuất cơ khí - khuôn mẫu lên chuẩn 4.0',
                'Kiểm soát chất lượng toàn diện IQC - PQC - FQC trên nền tảng số hóa',
            );
            $paragraphs = array(
                'Trong bối cảnh chuỗi cung ứng toàn cầu dịch chuyển mạnh mẽ, các doanh nghiệp sản xuất tại Việt Nam đang cần một nền tảng quản trị đồng bộ từ tầng hoạch định (ERP) đến tầng thực thi sản xuất (MES).',
                'Hệ thống cho phép theo dõi tiến độ lệnh sản xuất theo thời gian thực, tự động cảnh báoคอ chai tại từng công đoạn và truy xuất nguồn gốc nguyên vật liệu chính xác theo từng lô (Batch/Lot).',
                'Sau 6 tháng triển khai thực tế, doanh nghiệp đã giảm 30% lượng tồn kho bán thành phẩm (WIP), nâng tỷ lệ giao hàng đúng hạn (OTD) lên 98% và rút ngắn 60% thời gian chốt số liệu kế toán cuối tháng.',
                'Giao diện hỗ trợ song ngữ Việt - Trung trực quan, đáp ứng đầy đủ chuẩn mực kế toán Việt Nam (VAS) và báo cáo quản trị tập đoàn đa quốc gia.',
            );
        }

        $random_topic = $topics[array_rand($topics)];
        $code_suffix = strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 5));

        $final_title = ($title_prefix !== '' ? $title_prefix . ' - ' : '')
            . $random_topic . ' [#' . $index . '-' . $code_suffix . ']';

        if ($custom_body !== '') {
            $final_content = wp_kses_post($custom_body) . "\n\n<p><em>Mã dữ liệu test: #{$index}-{$code_suffix} ({$post_type})</em></p>";
            $final_excerpt = wp_trim_words(wp_strip_all_tags($custom_body), 30);
        } else {
            shuffle($paragraphs);
            $final_content = '<h2>' . esc_html($random_topic) . '</h2>'
                . '<p>' . esc_html($paragraphs[0]) . '</p>'
                . '<h3>Điểm nổi bật của giải pháp (' . esc_html(strtoupper($post_type)) . ')</h3>'
                . '<ul>'
                . '<li>' . esc_html($paragraphs[1]) . '</li>'
                . '<li>' . esc_html($paragraphs[2]) . '</li>'
                . '</ul>'
                . '<blockquote><p>' . esc_html($paragraphs[3]) . '</p></blockquote>';
            $final_excerpt = $paragraphs[0];
        }

        return array(
            'title'   => $final_title,
            'content' => $final_content,
            'excerpt' => $final_excerpt,
        );
    }

    /**
     * [26/10/2026] - Thực thi tạo hàng loạt bài viết test (Bulk Insert) tối ưu hiệu năng
     *
     * @param array $params Tham số từ form quản trị
     * @return array Kết quả thực thi (số dòng tạo thành công, danh sách ID, lỗi nếu có)
     */
    public function generateDummyPosts(array $params): array
    {
        $count           = isset($params['count']) ? max(1, min(500, (int) $params['count'])) : 10;
        $post_type       = !empty($params['custom_post_type'])
            ? sanitize_key($params['custom_post_type'])
            : sanitize_key($params['post_type'] ?? 'post');
        $taxonomy        = !empty($params['custom_taxonomy'])
            ? sanitize_key($params['custom_taxonomy'])
            : sanitize_key($params['taxonomy'] ?? '');
        $term_mode       = sanitize_text_field($params['term_mode'] ?? 'existing');
        $term_ids        = isset($params['term_ids']) && is_array($params['term_ids'])
            ? array_map('intval', $params['term_ids'])
            : array();
        $custom_terms    = sanitize_text_field($params['custom_terms'] ?? '');
        $lang_mode       = sanitize_text_field($params['lang_mode'] ?? 'vn');
        $title_prefix    = sanitize_text_field($params['title_prefix'] ?? '');
        $custom_content  = isset($params['custom_content']) ? wp_kses_post($params['custom_content']) : '';
        $show_home       = !empty($params['show_home']) ? '1' : '0';
        $with_thumbnail  = !empty($params['with_thumbnail']);

        // [26/10/2026] - Tạm ngắt đếm term/comment tự động trong lúc chạy vòng lặp để tăng tốc độ insert
        wp_defer_term_counting(true);
        wp_defer_comment_counting(true);

        // [26/10/2026] - Chuẩn bị danh sách Term ID nếu người dùng nhập tên Category mới hoặc chọn Random
        $resolved_term_ids = array();
        if ($taxonomy !== '' && taxonomy_exists($taxonomy)) {
            if ($term_mode === 'custom' && $custom_terms !== '') {
                $raw_names = array_filter(array_map('trim', explode(',', $custom_terms)));
                foreach ($raw_names as $t_name) {
                    $existing = term_exists($t_name, $taxonomy);
                    if ($existing) {
                        $t_id = is_array($existing) ? (int) $existing['term_id'] : (int) $existing;
                        $resolved_term_ids[] = $t_id;
                    } else {
                        $created = wp_insert_term($t_name, $taxonomy);
                        if (!is_wp_error($created) && isset($created['term_id'])) {
                            $t_id = (int) $created['term_id'];
                            $resolved_term_ids[] = $t_id;
                            // Lưu thêm option đa ngôn ngữ của theme dgw cho category mới
                            update_option('option_' . $taxonomy . '_' . $t_id, array(
                                'cate_cn'    => $t_name,
                                'cate_vn'    => $t_name,
                                'cate_en'    => $t_name,
                                'cate_order' => '1',
                            ));
                        }
                    }
                }
            } elseif ($term_mode === 'random') {
                $all_terms = get_terms(array(
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => false,
                    'fields'     => 'ids',
                ));
                if (!is_wp_error($all_terms) && !empty($all_terms)) {
                    $resolved_term_ids = array_map('intval', $all_terms);
                }
            } else {
                $resolved_term_ids = array_filter($term_ids);
            }
        }

        $attachment_ids = $with_thumbnail ? $this->getRandomAttachmentIds() : array();
        $inserted_ids   = array();

        for ($i = 1; $i <= $count; $i++) {
            $post_lang = $lang_mode;
            if ($lang_mode === 'random') {
                $post_lang = ($i % 2 === 0) ? 'cn' : 'vn';
            }

            $generated = $this->generateRandomPostData($post_lang, $post_type, $i, $title_prefix, $custom_content);

            // [26/10/2026] - Dùng gmdate() kết hợp current_time('timestamp') và time() để tránh lỗi cộng dồn múi giờ (date_default_timezone_set)
            // khiến post_date bị đẩy sang tương lai và bị WordPress tự động chuyển post_status từ 'publish' thành 'future' (Scheduled)
            $offset_seconds = ($count - $i + 1) * 120;
            $post_date      = gmdate('Y-m-d H:i:s', current_time('timestamp') - $offset_seconds);
            $post_date_gmt  = gmdate('Y-m-d H:i:s', time() - $offset_seconds);

            $post_Id = wp_insert_post(array(
                'post_type'     => $post_type,
                'post_title'    => $generated['title'],
                'post_content'  => $generated['content'],
                'post_excerpt'  => $generated['excerpt'],
                'post_status'   => 'publish',
                'post_date'     => $post_date,
                'post_date_gmt' => $post_date_gmt,
                'meta_input'    => array(
                    self::DUMMY_META_KEY => '1',
                    '_metabox_langguage' => $post_lang,
                    '_metabox_order'     => (string) $i,
                    '_metabox_home'      => $show_home,
                    '_metabox_view'      => (string) mt_rand(50, 1500),
                ),
            ), true);

            if (!is_wp_error($post_Id) && $post_Id > 0) {
                $inserted_ids[] = $post_Id;

                // [26/10/2026] - Gán Category / Taxonomy cho bài viết vừa tạo
                if ($taxonomy !== '' && taxonomy_exists($taxonomy) && !empty($resolved_term_ids)) {
                    $terms_to_assign = ($term_mode === 'random')
                        ? array($resolved_term_ids[array_rand($resolved_term_ids)])
                        : $resolved_term_ids;
                    wp_set_object_terms($post_Id, $terms_to_assign, $taxonomy, false);
                }

                // [26/10/2026] - Gắn ảnh đại diện ngẫu nhiên nếu được bật và có sẵn ảnh trong Media Library
                if (!empty($attachment_ids)) {
                    $random_thumb_id = $attachment_ids[array_rand($attachment_ids)];
                    set_post_thumbnail($post_Id, $random_thumb_id);
                }
            }
        }

        wp_defer_term_counting(false);
        wp_defer_comment_counting(false);

        if (function_exists('dgw_clear_casestudies_transients')) {
            dgw_clear_casestudies_transients();
        }

        return array(
            'inserted_count' => count($inserted_ids),
            'inserted_ids'   => $inserted_ids,
            'post_type'      => $post_type,
            'taxonomy'       => $taxonomy,
        );
    }

    /**
     * [26/10/2026] - Thống kê số lượng bài viết Dummy Data hiện có theo từng Post Type
     *
     * @return array Mảng ['post_type' => count]
     */
    public function getDummyCountsByPostType(): array
    {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT p.post_type, COUNT(p.ID) AS total
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE pm.meta_key = %s AND pm.meta_value = '1'
             GROUP BY p.post_type",
            self::DUMMY_META_KEY
        );
        $rows = $wpdb->get_results($sql);
        $stats = array();
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $stats[$row->post_type] = (int) $row->total;
            }
        }
        return $stats;
    }

    /**
     * [26/10/2026] - Tự động chuyển toàn bộ bài viết Dummy Data đang bị kẹt ở trạng thái 'future' (Đã lên lịch do lệch múi giờ) sang 'publish'
     *
     * @return int Số bài viết đã được kích hoạt publish
     */
    public function publishScheduledDummyPosts(): int
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT p.ID
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE pm.meta_key = %s AND pm.meta_value = '1' AND p.post_status = 'future'",
            self::DUMMY_META_KEY
        );

        $future_ids = $wpdb->get_col($sql);
        if (empty($future_ids)) {
            return 0;
        }

        $now_local = current_time('mysql');
        $now_gmt   = current_time('mysql', true);
        $fixed     = 0;

        foreach ($future_ids as $pid) {
            $pid = (int) $pid;
            $wpdb->update(
                $wpdb->posts,
                array(
                    'post_status'   => 'publish',
                    'post_date'     => $now_local,
                    'post_date_gmt' => $now_gmt,
                ),
                array('ID' => $pid),
                array('%s', '%s', '%s'),
                array('%d')
            );
            clean_post_cache($pid);
            wp_transition_post_status('publish', 'future', get_post($pid));
            $fixed++;
        }

        if ($fixed > 0 && class_exists('\DGW_API\Models\Active_Model')) {
            \DGW_API\Models\Active_Model::clear_cache();
        }

        return $fixed;
    }

    /**
     * [26/10/2026] - Xóa vĩnh viễn toàn bộ bài viết Dummy Data (có gắn cờ _dgw_is_dummy_data = 1)
     *
     * @param string $post_type Tên post_type cần xóa hoặc 'all' để xóa tất cả
     * @return int Số bài viết đã xóa
     */
    public function deleteDummyPosts(string $post_type = 'all'): int
    {
        global $wpdb;

        if ($post_type !== 'all' && $post_type !== '') {
            $sql = $wpdb->prepare(
                "SELECT p.ID
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 WHERE pm.meta_key = %s AND pm.meta_value = '1' AND p.post_type = %s",
                self::DUMMY_META_KEY,
                sanitize_key($post_type)
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT p.ID
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 WHERE pm.meta_key = %s AND pm.meta_value = '1'",
                self::DUMMY_META_KEY
            );
        }

        $post_ids = $wpdb->get_col($sql);
        $deleted  = 0;

        if (!empty($post_ids)) {
            wp_defer_term_counting(true);
            foreach ($post_ids as $pid) {
                if (wp_delete_post((int) $pid, true)) {
                    $deleted++;
                }
            }
            wp_defer_term_counting(false);
        }

        if (function_exists('dgw_clear_casestudies_transients')) {
            dgw_clear_casestudies_transients();
        }

        return $deleted;
    }
}
