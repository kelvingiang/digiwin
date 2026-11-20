<?php
declare(strict_types=1);

namespace DGW\Taxonomies;

use WP_Term;
use WP_Term_Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Base Class dùng chung cho 8 Taxonomy đa ngôn ngữ theo cơ chế Translation Group (loại bỏ ~160KB code trùng lặp và N+1 query)
 */
class BaseTranslationTaxonomy
{
    private string $taxonomy;
    private string $postType;
    private string $rewriteSlug;
    private string $nonceAction;
    private string $nonceName;

    /**
     * Ngăn khởi tạo trùng lặp theo từng taxonomy
     *
     * @var array<string, bool>
     */
    private static array $initialized = [];

    /**
     * Cache danh sách term và bản đồ translation_group theo từng taxonomy trong một request (chống N+1 query)
     *
     * @var array<string, array{terms: WP_Term[], by_group: array<int, WP_Term[]>, lang_map: array<int, string>}>
     */
    private static array $termGroupCache = [];

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo hooks cho Taxonomy (chỉ gắn các hook quản trị khi is_admin())
     */
    public function __construct(string $taxonomy, string $postType, ?string $rewriteSlug = null)
    {
        if (!empty(self::$initialized[$taxonomy])) {
            return;
        }
        self::$initialized[$taxonomy] = true;

        $this->taxonomy    = $taxonomy;
        $this->postType    = $postType;
        $this->rewriteSlug = $rewriteSlug ?? $taxonomy;
        $this->nonceAction = "dgw_{$taxonomy}_group_action";
        $this->nonceName   = "dgw_{$taxonomy}_group_nonce";

        add_action('init', [$this, 'registerTaxonomy']);

        if (is_admin()) {
            add_action("{$this->taxonomy}_add_form_fields", [$this, 'renderAddFields']);
            add_action("{$this->taxonomy}_edit_form_fields", [$this, 'renderEditFields']);

            add_action("create_{$this->taxonomy}", [$this, 'saveTermMeta']);
            add_action("edited_{$this->taxonomy}", [$this, 'saveTermMeta']);

            add_filter("manage_edit-{$this->taxonomy}_columns", [$this, 'customizeColumns']);
            add_filter("manage_{$this->taxonomy}_custom_column", [$this, 'renderColumnContent'], 10, 3);
            add_filter("manage_edit-{$this->taxonomy}_sortable_columns", [$this, 'registerSortableColumns']);
            add_action('pre_get_terms', [$this, 'handleTermQueryFilters']);
            add_action('restrict_manage_posts', [$this, 'renderAdminLanguageFilter']);

            add_action('admin_footer-post.php', [$this, 'renderPostScreenSyncScript']);
            add_action('admin_footer-post-new.php', [$this, 'renderPostScreenSyncScript']);
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký Taxonomy chuẩn WordPress hỗ trợ REST API & Admin Column native
     */
    public function registerTaxonomy(): void
    {
        $labels = [
            'name'              => __('Category', 'dgw'),
            'singular_name'     => __('Category', 'dgw'),
            'search_items'      => __('Search Categories', 'dgw'),
            'all_items'         => __('All Categories', 'dgw'),
            'parent_item'       => __('Parent Category', 'dgw'),
            'parent_item_colon' => __('Parent Category:', 'dgw'),
            'edit_item'         => __('Edit Category', 'dgw'),
            'update_item'       => __('Update Category', 'dgw'),
            'add_new_item'      => __('Add New Category', 'dgw'),
            'new_item_name'     => __('New Category Name', 'dgw'),
            'menu_name'         => __('Category', 'dgw'),
        ];

        register_taxonomy($this->taxonomy, $this->postType, [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => [
                'slug'         => $this->rewriteSlug,
                'hierarchical' => true,
                'with_front'   => false,
            ],
        ]);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Nạp và cache toàn bộ terms kèm meta 1 lần duy nhất cho mỗi taxonomy (tránh N+1 query trên bảng WP-Admin)
     *
     * @return array{terms: WP_Term[], by_group: array<int, WP_Term[]>, lang_map: array<int, string>}
     */
    private function getCachedTermsData(): array
    {
        if (isset(self::$termGroupCache[$this->taxonomy])) {
            return self::$termGroupCache[$this->taxonomy];
        }

        $rawTerms = get_terms([
            'taxonomy'   => $this->taxonomy,
            'hide_empty' => false,
        ]);

        $terms   = (!empty($rawTerms) && !is_wp_error($rawTerms)) ? $rawTerms : [];
        $termIds = array_map(static function (WP_Term $t): int {
            return (int) $t->term_id;
        }, $terms);

        if (!empty($termIds)) {
            update_termmeta_cache($termIds);
        }

        $byGroup = [];
        $langMap = [];
        foreach ($terms as $t) {
            $tid           = (int) $t->term_id;
            $lang          = (string) (get_term_meta($tid, 'term_lang', true) ?: 'cn');
            $groupId       = (int) (get_term_meta($tid, 'translation_group', true) ?: $tid);
            $langMap[$tid] = $lang;

            if (!isset($byGroup[$groupId])) {
                $byGroup[$groupId] = [];
            }
            $byGroup[$groupId][] = $t;
        }

        return self::$termGroupCache[$this->taxonomy] = [
            'terms'    => $terms,
            'by_group' => $byGroup,
            'lang_map' => $langMap,
        ];
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render các trường khi Thêm mới Term: Ngôn ngữ, Translation Group, Order
     */
    public function renderAddFields(): void
    {
        wp_nonce_field($this->nonceAction, $this->nonceName);
        $cached        = $this->getCachedTermsData();
        $existingTerms = $cached['terms'];
        $langMap       = $cached['lang_map'];
        ?>
        <div class="form-field">
            <label for="term_lang"><strong><?php esc_html_e('Term Language', 'dgw'); ?></strong></label>
            <select name="term_lang" id="term_lang">
                <option value="cn"><?php esc_html_e('Chinese (CN)', 'dgw'); ?></option>
                <option value="vn" selected><?php esc_html_e('Vietnamese (VN)', 'dgw'); ?></option>
            </select>
            <p><?php esc_html_e('Select the language this term represents.', 'dgw'); ?></p>
        </div>

        <div class="form-field">
            <label for="translation_group"><strong><?php esc_html_e('Translation Group ID', 'dgw'); ?></strong></label>
            <input type="number" name="translation_group" id="translation_group" value="" placeholder="Ví dụ: 10" />
            <p><?php esc_html_e('Enter the shared group ID, or select the corresponding term below to link automatically.', 'dgw'); ?></p>
        </div>

        <?php if (!empty($existingTerms)) : ?>
            <div class="form-field">
                <label for="link_with_term"><?php esc_html_e('Or link translation with Term:', 'dgw'); ?></label>
                <select name="link_with_term" id="link_with_term">
                    <option value=""><?php esc_html_e('-- Do not link (Create new group) --', 'dgw'); ?></option>
                    <?php foreach ($existingTerms as $t) :
                        $tid    = (int) $t->term_id;
                        $tLang  = $langMap[$tid] ?? 'cn';
                        $tGroup = (int) (get_term_meta($tid, 'translation_group', true) ?: $tid);
                    ?>
                        <option value="<?php echo esc_attr((string) $tid); ?>" data-group="<?php echo esc_attr((string) $tGroup); ?>">
                            [<?php echo esc_html(strtoupper($tLang)); ?>] <?php echo esc_html($t->name); ?> (Group #<?php echo esc_html((string) $tGroup); ?>)
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
            <label for="cate_order"><?php esc_html_e('Display Order', 'dgw'); ?></label>
            <input type="number" name="cate_order" id="cate_order" value="0" />
        </div>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render các trường khi Chỉnh sửa Term
     */
    public function renderEditFields(WP_Term $term): void
    {
        wp_nonce_field($this->nonceAction, $this->nonceName);

        $tid              = (int) $term->term_id;
        $cached           = $this->getCachedTermsData();
        $termLang         = $cached['lang_map'][$tid] ?? 'cn';
        $translationGroup = (int) (get_term_meta($tid, 'translation_group', true) ?: $tid);
        $cateOrder        = get_term_meta($tid, 'cate_order', true);
        $groupTerms       = $cached['by_group'][$translationGroup] ?? [];
        $linkedTerms      = array_filter($groupTerms, static function (WP_Term $lt) use ($tid): bool {
            return (int) $lt->term_id !== $tid;
        });
        ?>
        <tr class="form-field">
            <th scope="row"><label for="term_lang"><strong><?php esc_html_e('Term Language', 'dgw'); ?></strong></label></th>
            <td>
                <select name="term_lang" id="term_lang">
                    <option value="cn" <?php selected($termLang, 'cn'); ?>><?php esc_html_e('Chinese (CN)', 'dgw'); ?></option>
                    <option value="vn" <?php selected($termLang, 'vn'); ?>><?php esc_html_e('Vietnamese (VN)', 'dgw'); ?></option>
                </select>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="translation_group"><strong><?php esc_html_e('Translation Group ID', 'dgw'); ?></strong></label></th>
            <td>
                <input type="number" name="translation_group" id="translation_group" value="<?php echo esc_attr((string) $translationGroup); ?>" />
                <p class="description">
                    <?php esc_html_e('Terms with the same ID will be linked together as translations.', 'dgw'); ?>
                    <?php if (!empty($linkedTerms)) : ?>
                        <br><strong><?php esc_html_e('Current linked translations:', 'dgw'); ?></strong>
                        <?php foreach ($linkedTerms as $lt) :
                            $ltId   = (int) $lt->term_id;
                            $ltLang = $cached['lang_map'][$ltId] ?? 'cn';
                        ?>
                            <a href="<?php echo esc_url((string) get_edit_term_link($ltId, $this->taxonomy)); ?>">
                                [<?php echo esc_html(strtoupper($ltLang)); ?>] <?php echo esc_html($lt->name); ?>
                            </a>&nbsp;
                        <?php endforeach; ?>
                    <?php endif; ?>
                </p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row"><label for="cate_order"><?php esc_html_e('Display Order', 'dgw'); ?></label></th>
            <td>
                <input type="number" name="cate_order" id="cate_order" value="<?php echo esc_attr($cateOrder !== '' ? (string) $cateOrder : '0'); ?>" />
            </td>
        </tr>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lưu Term Meta an toàn: term_lang, translation_group, cate_order
     */
    public function saveTermMeta(int $termId): void
    {
        if (
            !isset($_POST[$this->nonceName]) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST[$this->nonceName])), $this->nonceAction) ||
            !current_user_can('edit_term', $termId)
        ) {
            return;
        }

        if (isset($_POST['term_lang'])) {
            $lang = sanitize_text_field(wp_unslash((string) $_POST['term_lang']));
            update_term_meta($termId, 'term_lang', in_array($lang, ['cn', 'vn'], true) ? $lang : 'cn');
        }

        if (!empty($_POST['translation_group'])) {
            $groupId = absint($_POST['translation_group']);
            update_term_meta($termId, 'translation_group', $groupId);
        } else {
            $existingGroup = get_term_meta($termId, 'translation_group', true);
            if (empty($existingGroup)) {
                update_term_meta($termId, 'translation_group', $termId);
            }
        }

        if (isset($_POST['cate_order'])) {
            update_term_meta($termId, 'cate_order', absint($_POST['cate_order']));
        }

        unset(self::$termGroupCache[$this->taxonomy]);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Khai báo cấu trúc các cột hiển thị trong danh sách Term
     */
    public function customizeColumns(array $columns): array
    {
        return [
            'cb'                => '<input type="checkbox" />',
            'name'              => __('Category Name', 'dgw'),
            'slug'              => __('Slug', 'dgw'),
            'term_lang'         => __('Language', 'dgw'),
            'translation_group' => __('Translation Group', 'dgw'),
            'cate_order'        => __('Order', 'dgw'),
            'posts'             => __('Posts', 'dgw'),
        ];
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render nội dung cột custom sử dụng cache nhóm dịch (không phát sinh N+1 query)
     */
    public function renderColumnContent(string $content, string $columnName, int $termId): string
    {
        $cached = $this->getCachedTermsData();

        switch ($columnName) {
            case 'term_lang':
                $lang       = $cached['lang_map'][$termId] ?? 'cn';
                $badgeColor = ($lang === 'vn') ? '#0073aa' : '#d63638';
                $badgeLabel = ($lang === 'vn') ? 'VN' : 'CN';
                return sprintf(
                    '<span style="background:%s;color:#fff;padding:2px 8px;border-radius:3px;font-weight:bold;font-size:11px;">%s</span>',
                    esc_attr($badgeColor),
                    esc_html($badgeLabel)
                );

            case 'translation_group':
                $groupId    = (int) (get_term_meta($termId, 'translation_group', true) ?: $termId);
                $groupTerms = $cached['by_group'][$groupId] ?? [];

                $html  = '<strong>#' . esc_html((string) $groupId) . '</strong>';
                $links = [];
                foreach ($groupTerms as $lt) {
                    $ltId = (int) $lt->term_id;
                    if ($ltId === $termId) {
                        continue;
                    }
                    $ltLang  = $cached['lang_map'][$ltId] ?? 'cn';
                    $links[] = sprintf(
                        '<a href="%s" title="%s">[%s] %s</a>',
                        esc_url((string) get_edit_term_link($ltId, $this->taxonomy)),
                        esc_attr($lt->name),
                        esc_html(strtoupper($ltLang)),
                        esc_html($lt->name)
                    );
                }

                if (!empty($links)) {
                    $html .= '<div style="font-size:11px;color:#666;margin-top:2px;">' . implode(', ', $links) . '</div>';
                }

                return $html;

            case 'cate_order':
                $order = get_term_meta($termId, 'cate_order', true);
                return esc_html($order !== '' ? (string) $order : '0');

            default:
                return $content;
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Khai báo các cột có thể sắp xếp
     */
    public function registerSortableColumns(array $columns): array
    {
        unset($columns['slug']);
        $columns['cate_order']        = 'cate_order';
        $columns['translation_group'] = 'translation_group';
        return $columns;
    }

    /**
     * [2026-11-20] - @author: Kelvin - Xử lý sort theo cate_order và lọc theo ngôn ngữ qua WP_Term_Query
     */
    public function handleTermQueryFilters(WP_Term_Query $query): void
    {
        if (!is_admin() || empty($query->query_vars['taxonomy'])) {
            return;
        }

        if (!in_array($this->taxonomy, (array) $query->query_vars['taxonomy'], true)) {
            return;
        }

        if (isset($_GET['orderby']) && $_GET['orderby'] === 'cate_order') {
            $query->query_vars['meta_key'] = 'cate_order';
            $query->query_vars['orderby']  = 'meta_value_num';
        }

        if (!empty($_GET['term_lang_filter'])) {
            $filterLang = sanitize_text_field(wp_unslash((string) $_GET['term_lang_filter']));
            if (in_array($filterLang, ['cn', 'vn'], true)) {
                $metaQuery   = is_array($query->query_vars['meta_query'] ?? null) ? $query->query_vars['meta_query'] : [];
                $metaQuery[] = [
                    'key'     => 'term_lang',
                    'value'   => $filterLang,
                    'compare' => '=',
                ];
                $query->query_vars['meta_query'] = $metaQuery;
            }
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Dropdown lọc danh sách term theo ngôn ngữ trên trang edit-tags.php
     */
    public function renderAdminLanguageFilter(): void
    {
        global $current_screen;
        if (!$current_screen || $current_screen->taxonomy !== $this->taxonomy) {
            return;
        }

        $selected = isset($_GET['term_lang_filter']) ? sanitize_text_field(wp_unslash((string) $_GET['term_lang_filter'])) : '';
        ?>
        <select name="term_lang_filter">
            <option value=""><?php esc_html_e('-- All Languages --', 'dgw'); ?></option>
            <option value="cn" <?php selected($selected, 'cn'); ?>><?php esc_html_e('Chinese (CN)', 'dgw'); ?></option>
            <option value="vn" <?php selected($selected, 'vn'); ?>><?php esc_html_e('Vietnamese (VN)', 'dgw'); ?></option>
        </select>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Tự động lọc checkbox category trong màn hình soạn thảo bài viết theo radio-lang
     */
    public function renderPostScreenSyncScript(): void
    {
        global $post;
        if (!$post || $post->post_type !== $this->postType) {
            return;
        }

        $cached      = $this->getCachedTermsData();
        $termLangMap = $cached['lang_map'];
        $taxSlug     = esc_js($this->taxonomy);
        ?>
        <script>
            jQuery(function($) {
                var termLangMap = <?php echo wp_json_encode($termLangMap); ?>;
                var taxSlug = '<?php echo $taxSlug; ?>';

                function filterTaxonomyCheckboxes() {
                    var selectedLang = $('input[name="radio-lang"]:checked').val() || 'cn';
                    $('#taxonomy-' + taxSlug + ' li').each(function() {
                        var $li = $(this);
                        var termId = $li.attr('id') ? $li.attr('id').replace(taxSlug + '-', '') : null;
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
