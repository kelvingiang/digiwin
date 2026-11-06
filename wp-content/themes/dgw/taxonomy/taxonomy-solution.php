<?php
declare(strict_types=1);

/**
 * Ngày thực hiện: 05/11/2026
 * Chức năng: Đăng ký 1 Taxonomy duy nhất 'solutions_category' theo cơ chế Translation Group
 * Kiến trúc: Các term đa ngôn ngữ (CN, VN) lưu chung bảng wp_terms, liên kết với nhau bằng 'translation_group' trong wp_termmeta
 * Tối ưu: Ngăn chặn khởi tạo lặp (chỉ đăng ký hook 1 lần duy nhất) để không bị trùng lặp trường trên giao diện
 */

if (!defined('ABSPATH')) {
    exit;
}

class Taxonomy_Solution
{
    public const TAXONOMY     = 'solutions_category';
    public const POST_TYPE    = 'solutions';
    private const NONCE_ACTION = 'dgw_solution_group_action';
    private const NONCE_NAME   = 'dgw_solution_group_nonce';

    /**
     * @var bool Cờ kiểm tra ngăn chặn đăng ký hook trùng lặp khi class bị khởi tạo nhiều lần
     */
    private static bool $is_initialized = false;

    public function __construct()
    {
        // Ngăn chặn khởi tạo lặp lại gây nhân đôi trường trên giao diện
        if (self::$is_initialized) {
            return;
        }
        self::$is_initialized = true;

        // 1. Đăng ký Taxonomy duy nhất
        add_action('init', [$this, 'register_taxonomy']);

        // 2. Form fields: Ngôn ngữ, Translation Group, Order
        add_action(self::TAXONOMY . '_add_form_fields', [$this, 'render_add_fields']);
        add_action(self::TAXONOMY . '_edit_form_fields', [$this, 'render_edit_fields']);

        // 3. Lưu Term Meta
        add_action('create_' . self::TAXONOMY, [$this, 'save_term_meta']);
        add_action('edited_' . self::TAXONOMY, [$this, 'save_term_meta']);

        // 4. Quản lý Columns trong wp-admin
        add_filter('manage_edit-' . self::TAXONOMY . '_columns', [$this, 'customize_columns']);
        add_filter('manage_' . self::TAXONOMY . '_custom_column', [$this, 'render_column_content'], 10, 3);
        add_filter('manage_edit-' . self::TAXONOMY . '_sortable_columns', [$this, 'register_sortable_columns']);
        add_action('pre_get_terms', [$this, 'handle_term_query_filters']);

        // 5. Thêm Dropdown lọc theo Ngôn ngữ trên trang danh sách Term
        add_action('restrict_manage_posts', [$this, 'render_admin_language_filter']);

        // 6. Đồng bộ lọc checkbox term theo ngôn ngữ bài viết trong màn hình soạn thảo
        if (is_admin()) {
            add_action('admin_footer-post.php', [$this, 'render_post_screen_sync_script']);
            add_action('admin_footer-post-new.php', [$this, 'render_post_screen_sync_script']);
        }
    }

    /**
     * Ngày thực hiện: 05/11/2026
     * Đăng ký 1 taxonomy duy nhất cho Solutions
     */
    public function register_taxonomy(): void
    {
        $labels = [
            'name'              => __('Chuyên mục Giải pháp', 'dgw'),
            'singular_name'     => __('Chuyên mục Giải pháp', 'dgw'),
            'search_items'      => __('Tìm chuyên mục', 'dgw'),
            'all_items'         => __('Tất cả chuyên mục', 'dgw'),
            'parent_item'       => __('Chuyên mục cha', 'dgw'),
            'parent_item_colon' => __('Chuyên mục cha:', 'dgw'),
            'edit_item'         => __('Chỉnh sửa chuyên mục', 'dgw'),
            'update_item'       => __('Cập nhật chuyên mục', 'dgw'),
            'add_new_item'      => __('Thêm chuyên mục mới', 'dgw'),
            'new_item_name'     => __('Tên chuyên mục mới', 'dgw'),
            'menu_name'         => __('Chuyên mục Giải pháp', 'dgw'),
        ];

        register_taxonomy(self::TAXONOMY, self::POST_TYPE, [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => [
                'slug'         => 'solutions-category',
                'hierarchical' => true,
                'with_front'   => false,
            ],
        ]);
    }

    /**
     * Ngày thực hiện: 05/11/2026
     * Render các trường khi Thêm mới Term: Ngôn ngữ, Translation Group, Order
     */
    public function render_add_fields(): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        // Lấy danh sách term hiện có để cho phép chọn liên kết nhanh
        $existing_terms = get_terms([
            'taxonomy'   => self::TAXONOMY,
            'hide_empty' => false,
        ]);
        ?>
        <div class="form-field">
            <label for="term_lang"><strong><?php esc_html_e('Ngôn ngữ của Term', 'dgw'); ?></strong></label>
            <select name="term_lang" id="term_lang">
                <option value="cn"><?php esc_html_e('Tiếng Trung (CN)', 'dgw'); ?></option>
                <option value="vn" selected><?php esc_html_e('Tiếng Việt (VN)', 'dgw'); ?></option>
            </select>
            <p><?php esc_html_e('Chọn ngôn ngữ mà term này đại diện.', 'dgw'); ?></p>
        </div>

        <div class="form-field">
            <label for="translation_group"><strong><?php esc_html_e('Translation Group ID', 'dgw'); ?></strong></label>
            <input type="number" name="translation_group" id="translation_group" value="" placeholder="Ví dụ: 10 (Để trống sẽ tự tạo nhóm mới)" />
            <p><?php esc_html_e('Nhập ID nhóm dịch chung, hoặc chọn term đối ứng bên dưới để tự động liên kết.', 'dgw'); ?></p>
        </div>

        <?php if (!empty($existing_terms) && !is_wp_error($existing_terms)) : ?>
            <div class="form-field">
                <label for="link_with_term"><?php esc_html_e('Hoặc liên kết bản dịch với Term:', 'dgw'); ?></label>
                <select name="link_with_term" id="link_with_term">
                    <option value=""><?php esc_html_e('-- Không liên kết (Tạo nhóm mới) --', 'dgw'); ?></option>
                    <?php foreach ($existing_terms as $t) : 
                        $t_lang  = get_term_meta($t->term_id, 'term_lang', true) ?: 'cn';
                        $t_group = get_term_meta($t->term_id, 'translation_group', true) ?: $t->term_id;
                    ?>
                        <option value="<?php echo esc_attr((string)$t->term_id); ?>" data-group="<?php echo esc_attr((string)$t_group); ?>">
                            [<?php echo strtoupper(esc_html($t_lang)); ?>] <?php echo esc_html($t->name); ?> (Group #<?php echo esc_html((string)$t_group); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <script>
                jQuery(function($) {
                    $('#link_with_term').on('change', function() {
                        var group = $(this).find(':selected').data('group');
                        if (group) {
                            $('#translation_group').val(group);
                        }
                    });
                });
            </script>
        <?php endif; ?>

        <div class="form-field">
            <label for="cate_order"><?php esc_html_e('Thứ tự hiển thị', 'dgw'); ?></label>
            <input type="number" name="cate_order" id="cate_order" value="0" />
        </div>
        <?php
    }

    /**
     * Ngày thực hiện: 05/11/2026
     * Render các trường khi Chỉnh sửa Term
     */
    public function render_edit_fields(WP_Term $term): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        $term_lang         = get_term_meta($term->term_id, 'term_lang', true) ?: 'cn';
        $translation_group = get_term_meta($term->term_id, 'translation_group', true) ?: $term->term_id;
        $cate_order        = get_term_meta($term->term_id, 'cate_order', true);

        // Tìm các term khác đang cùng nằm chung Translation Group
        $linked_terms = get_terms([
            'taxonomy'   => self::TAXONOMY,
            'hide_empty' => false,
            'exclude'    => [$term->term_id],
            'meta_query' => [
                [
                    'key'     => 'translation_group',
                    'value'   => $translation_group,
                    'compare' => '=',
                ],
            ],
        ]);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="term_lang"><strong><?php esc_html_e('Ngôn ngữ của Term', 'dgw'); ?></strong></label></th>
            <td>
                <select name="term_lang" id="term_lang">
                    <option value="cn" <?php selected($term_lang, 'cn'); ?>><?php esc_html_e('Tiếng Trung (CN)', 'dgw'); ?></option>
                    <option value="vn" <?php selected($term_lang, 'vn'); ?>><?php esc_html_e('Tiếng Việt (VN)', 'dgw'); ?></option>
                </select>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="translation_group"><strong><?php esc_html_e('Translation Group ID', 'dgw'); ?></strong></label></th>
            <td>
                <input type="number" name="translation_group" id="translation_group" value="<?php echo esc_attr((string)$translation_group); ?>" />
                <p class="description">
                    <?php esc_html_e('Các term có cùng ID này sẽ được liên kết bản dịch với nhau.', 'dgw'); ?>
                    <?php if (!empty($linked_terms) && !is_wp_error($linked_terms)) : ?>
                        <br><strong><?php esc_html_e('Bản dịch liên kết hiện tại:', 'dgw'); ?></strong>
                        <?php foreach ($linked_terms as $lt) : 
                            $lt_lang = get_term_meta($lt->term_id, 'term_lang', true) ?: 'cn';
                        ?>
                            <a href="<?php echo esc_url(get_edit_term_link($lt->term_id, self::TAXONOMY)); ?>">
                                [<?php echo strtoupper(esc_html($lt_lang)); ?>] <?php echo esc_html($lt->name); ?>
                            </a>&nbsp;
                        <?php endforeach; ?>
                    <?php endif; ?>
                </p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="cate_order"><?php esc_html_e('Thứ tự hiển thị', 'dgw'); ?></label></th>
            <td>
                <input type="number" name="cate_order" id="cate_order" value="<?php echo esc_attr($cate_order !== '' ? $cate_order : 0); ?>" />
            </td>
        </tr>
        <?php
    }

    /**
     * Ngày thực hiện: 05/11/2026
     * Lưu Term Meta an toàn: term_lang, translation_group, cate_order
     */
    public function save_term_meta(int $term_id): void
    {
        if (
            !isset($_POST[self::NONCE_NAME]) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION) ||
            !current_user_can('edit_term', $term_id)
        ) {
            return;
        }

        // 1. Lưu Ngôn ngữ Term
        if (isset($_POST['term_lang'])) {
            $lang = sanitize_text_field(wp_unslash($_POST['term_lang']));
            update_term_meta($term_id, 'term_lang', in_array($lang, ['cn', 'vn'], true) ? $lang : 'cn');
        }

        // 2. Lưu Translation Group ID
        if (!empty($_POST['translation_group'])) {
            $group_id = absint($_POST['translation_group']);
            update_term_meta($term_id, 'translation_group', $group_id);
        } else {
            // Nếu để trống, tự động cấp ID nhóm bằng chính ID của term này
            $existing_group = get_term_meta($term_id, 'translation_group', true);
            if (empty($existing_group)) {
                update_term_meta($term_id, 'translation_group', $term_id);
            }
        }

        // 3. Lưu Order
        if (isset($_POST['cate_order'])) {
            update_term_meta($term_id, 'cate_order', absint($_POST['cate_order']));
        }
    }

    /**
     * Ngày thực hiện: 05/11/2026
     * Khai báo cấu trúc các cột hiển thị trong danh sách Term
     */
    public function customize_columns(array $columns): array
    {
        return [
            'cb'                => '<input type="checkbox" />',
            'name'              => __('Tên chuyên mục', 'dgw'),
            'slug'              => __('Slug', 'dgw'),
            'term_lang'         => __('Ngôn ngữ', 'dgw'),
            'translation_group' => __('Translation Group', 'dgw'),
            'cate_order'        => __('Thứ tự', 'dgw'),
            'posts'             => __('Số bài viết', 'dgw'),
        ];
    }

    /**
     * Ngày thực hiện: 05/11/2026
     * Render nội dung hiển thị cho các custom columns
     */
    public function render_column_content(string $content, string $column_name, int $term_id): string
    {
        switch ($column_name) {
            case 'term_lang':
                $lang = get_term_meta($term_id, 'term_lang', true) ?: 'cn';
                $badge_color = ($lang === 'vn') ? '#0073aa' : '#d63638';
                $badge_label = ($lang === 'vn') ? 'VN' : 'CN';
                return sprintf(
                    '<span style="background:%s;color:#fff;padding:2px 8px;border-radius:3px;font-weight:bold;font-size:11px;">%s</span>',
                    esc_attr($badge_color),
                    esc_html($badge_label)
                );

            case 'translation_group':
                $group_id = get_term_meta($term_id, 'translation_group', true) ?: $term_id;
                
                // Lấy các term dịch liên kết
                $linked = get_terms([
                    'taxonomy'   => self::TAXONOMY,
                    'hide_empty' => false,
                    'exclude'    => [$term_id],
                    'meta_query' => [
                        [
                            'key'     => 'translation_group',
                            'value'   => $group_id,
                            'compare' => '=',
                        ],
                    ],
                ]);

                $html = '<strong>#' . esc_html((string)$group_id) . '</strong>';
                if (!empty($linked) && !is_wp_error($linked)) {
                    $links = [];
                    foreach ($linked as $lt) {
                        $lt_lang = get_term_meta($lt->term_id, 'term_lang', true) ?: 'cn';
                        $links[] = sprintf(
                            '<a href="%s" title="%s">[%s] %s</a>',
                            esc_url(get_edit_term_link($lt->term_id, self::TAXONOMY)),
                            esc_attr($lt->name),
                            strtoupper(esc_html($lt_lang)),
                            esc_html($lt->name)
                        );
                    }
                    $html .= '<div style="font-size:11px;color:#666;margin-top:2px;">' . implode(', ', $links) . '</div>';
                }
                return $html;

            case 'cate_order':
                $order = get_term_meta($term_id, 'cate_order', true);
                return esc_html($order !== '' ? (string)$order : '0');

            default:
                return $content;
        }
    }

    /**
     * Ngày thực hiện: 05/11/2026
     * Khai báo các cột có thể click sắp xếp (Sortable)
     */
    public function register_sortable_columns(array $columns): array
    {
        unset($columns['slug']);
        $columns['cate_order']        = 'cate_order';
        $columns['translation_group'] = 'translation_group';
        return $columns;
    }

    /**
     * Ngày thực hiện: 05/11/2026
     * Xử lý sort và lọc theo ngôn ngữ qua WP_Term_Query
     */
    public function handle_term_query_filters(WP_Term_Query $query): void
    {
        if (!is_admin() || empty($query->query_vars['taxonomy'])) {
            return;
        }

        if (in_array(self::TAXONOMY, (array)$query->query_vars['taxonomy'], true)) {
            // Sort theo order
            if (isset($_GET['orderby']) && $_GET['orderby'] === 'cate_order') {
                $query->query_vars['meta_key'] = 'cate_order';
                $query->query_vars['orderby']  = 'meta_value_num';
            }

            // Lọc theo ngôn ngữ trên admin URL: ?taxonomy=solutions_category&term_lang_filter=cn
            if (!empty($_GET['term_lang_filter']) && in_array($_GET['term_lang_filter'], ['cn', 'vn'], true)) {
                $meta_query   = $query->query_vars['meta_query'] ?? [];
                $meta_query[] = [
                    'key'     => 'term_lang',
                    'value'   => sanitize_text_field($_GET['term_lang_filter']),
                    'compare' => '=',
                ];
                $query->query_vars['meta_query'] = $meta_query;
            }
        }
    }

    /**
     * Ngày thực hiện: 05/11/2026
     * Dropdown lọc danh sách term theo ngôn ngữ trên trang edit-tags.php
     */
    public function render_admin_language_filter(string $post_type): void
    {
        global $current_screen;
        if (!$current_screen || $current_screen->taxonomy !== self::TAXONOMY) {
            return;
        }

        $selected = isset($_GET['term_lang_filter']) ? sanitize_text_field($_GET['term_lang_filter']) : '';
        ?>
        <select name="term_lang_filter">
            <option value=""><?php esc_html_e('-- Tất cả ngôn ngữ --', 'dgw'); ?></option>
            <option value="cn" <?php selected($selected, 'cn'); ?>><?php esc_html_e('Tiếng Trung (CN)', 'dgw'); ?></option>
            <option value="vn" <?php selected($selected, 'vn'); ?>><?php esc_html_e('Tiếng Việt (VN)', 'dgw'); ?></option>
        </select>
        <?php
    }

    /**
     * Ngày thực hiện: 05/11/2026
     * Tự động lọc các checkbox category trong màn hình viết bài solutions theo radio language
     */
    public function render_post_screen_sync_script(): void
    {
        global $post;
        if (!$post || $post->post_type !== self::POST_TYPE) {
            return;
        }

        // Lấy mapping [term_id => lang] để truyền sang JS
        $terms = get_terms([
            'taxonomy'   => self::TAXONOMY,
            'hide_empty' => false,
        ]);

        $term_lang_map = [];
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $t) {
                $term_lang_map[$t->term_id] = get_term_meta($t->term_id, 'term_lang', true) ?: 'cn';
            }
        }
        ?>
        <script>
            jQuery(function($) {
                var termLangMap = <?php echo json_encode($term_lang_map); ?>;

                function filterTaxonomyCheckboxes() {
                    var selectedLang = $('input[name="radio-lang"]:checked').val() || 'cn';
                    $('#taxonomy-solutions_category li').each(function() {
                        var $li = $(this);
                        var termId = $li.attr('id') ? $li.attr('id').replace('solutions_category-', '') : null;
                        if (termId && termLangMap[termId]) {
                            if (termLangMap[termId] === selectedLang) {
                                $li.show();
                            } else {
                                $li.hide();
                            }
                        }
                    });
                }

                filterTaxonomyCheckboxes();
                $(document).on('change', 'input[name="radio-lang"]', filterTaxonomyCheckboxes);
            });
        </script>
        <?php
    }
}
