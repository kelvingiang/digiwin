<?php
declare(strict_types=1);

namespace DGW\Taxonomies;

use WP_Term;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Base Class cho các Tag Taxonomy lưu cấu hình thứ tự vào wp_options (sửa lỗi lệch tên hook & lệch key option ở tag-*.php)
 */
class BaseTagOptionTaxonomy
{
    private string $taxonomy;
    private string $postType;
    private string $labelName;
    private string $optionPrefix;
    private string $fieldKeyPrefix;
    private bool $hierarchicalRewrite;
    private string $nonceAction;
    private string $nonceName;

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo hooks đăng ký Tag Taxonomy và các trường quản trị trong WP-Admin
     */
    public function __construct(
        string $taxonomy,
        string $postType,
        string $labelName,
        string $optionPrefix,
        string $fieldKeyPrefix,
        bool $hierarchicalRewrite = false
    ) {
        $this->taxonomy            = $taxonomy;
        $this->postType            = $postType;
        $this->labelName           = $labelName;
        $this->optionPrefix        = $optionPrefix;
        $this->fieldKeyPrefix      = $fieldKeyPrefix;
        $this->hierarchicalRewrite = $hierarchicalRewrite;
        $this->nonceAction         = "dgw_{$taxonomy}_option_action";
        $this->nonceName           = "dgw_{$taxonomy}_option_nonce";

        add_action('init', [$this, 'registerTaxonomy']);

        if (is_admin()) {
            add_action("{$this->taxonomy}_add_form_fields", [$this, 'renderAddForm']);
            add_action("{$this->taxonomy}_edit_form_fields", [$this, 'renderEditForm']);

            add_filter("manage_edit-{$this->taxonomy}_columns", [$this, 'customizeColumns']);
            add_filter("manage_{$this->taxonomy}_custom_column", [$this, 'renderColumn'], 10, 3);

            add_action("create_{$this->taxonomy}", [$this, 'saveOption']);
            add_action("edited_{$this->taxonomy}", [$this, 'saveOption']);
            add_action("delete_{$this->taxonomy}", [$this, 'deleteOption']);
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký Tag Taxonomy không phân cấp
     */
    public function registerTaxonomy(): void
    {
        $labels = [
            'name'              => __($this->labelName, 'dgw'),
            'singular_name'     => __($this->labelName, 'dgw'),
            'search_items'      => __('Search Categories', 'dgw'),
            'all_items'         => __('Categories', 'dgw'),
            'parent_item'       => __('Parent Class', 'dgw'),
            'parent_item_colon' => __('Parent Class', 'dgw'),
            'edit_item'         => __('Edit', 'dgw'),
            'update_item'       => __('Update', 'dgw'),
            'add_new_item'      => __('Add New', 'dgw'),
            'new_item_name'     => __('Add New', 'dgw'),
            'menu_name'         => __($this->labelName, 'dgw'),
        ];

        register_taxonomy($this->taxonomy, $this->postType, [
            'hierarchical' => false,
            'labels'       => $labels,
            'show_ui'      => true,
            'query_var'    => true,
            'rewrite'      => [
                'slug'         => $this->taxonomy,
                'hierarchical' => $this->hierarchicalRewrite,
            ],
        ]);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render form thêm mới Tag kèm Nonce
     */
    public function renderAddForm(): void
    {
        wp_nonce_field($this->nonceAction, $this->nonceName);
        ?>
        <div class="form-field">
            <label for="cate_order"><?php esc_html_e('Show Order', 'dgw'); ?></label>
            <input type="hidden" name="cate_cn" id="cate_cn" value="" />
            <input type="text" name="cate_order" id="cate_order" value="" />
        </div>
        <script>
            jQuery('#tag-name').on('focusout', function() {
                jQuery('#cate_cn').val(jQuery(this).val());
            });
        </script>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render form chỉnh sửa Tag, hỗ trợ fallback các key option cũ
     */
    public function renderEditForm(WP_Term $term): void
    {
        wp_nonce_field($this->nonceAction, $this->nonceName);
        $raw   = get_option($this->optionPrefix . $term->term_id, []);
        $data  = is_array($raw) ? $raw : [];
        $cnVal = (string) ($data[$this->fieldKeyPrefix . '_cn'] ?? $data['cate_advertising_cn'] ?? $data['cate_solution_cn'] ?? $term->name);
        $order = (string) ($data[$this->fieldKeyPrefix . '_order'] ?? $data['cate_order'] ?? $data['cate_advertising_order'] ?? $data['cate_solution_order'] ?? '0');
        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="cate_order"><?php esc_html_e('Show Order', 'dgw'); ?></label>
            </th>
            <td>
                <input type="hidden" name="cate_cn" id="cate_cn" value="<?php echo esc_attr($cnVal); ?>" />
                <input type="text" name="cate_order" id="cate_order" value="<?php echo esc_attr($order); ?>" />
            </td>
        </tr>
        <script>
            jQuery('#name').on('focusout', function() {
                jQuery('#cate_cn').val(jQuery(this).val());
            });
        </script>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lưu tùy chọn thứ tự của Tag vào wp_options an toàn
     */
    public function saveOption(int $termId): void
    {
        if (
            !isset($_POST[$this->nonceName]) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST[$this->nonceName])), $this->nonceAction) ||
            !current_user_can('manage_categories')
        ) {
            return;
        }

        $cn    = sanitize_text_field(wp_unslash((string) ($_POST['cate_cn'] ?? '')));
        $order = sanitize_text_field(wp_unslash((string) ($_POST['cate_order'] ?? '0')));

        $payload = [
            $this->fieldKeyPrefix . '_cn'    => $cn,
            $this->fieldKeyPrefix . '_order' => $order,
            'cate_order'                     => $order,
        ];

        update_option($this->optionPrefix . $termId, $payload);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Xóa option tương ứng khi xóa Tag
     */
    public function deleteOption(int $termId = 0): void
    {
        $id = $termId > 0 ? $termId : absint(getParams('tag_ID', 0));
        if ($id > 0) {
            delete_option($this->optionPrefix . $id);
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Cấu hình cột hiển thị bảng Tag
     */
    public function customizeColumns(): array
    {
        return [
            'cb'    => '<input type="checkbox" />',
            'name'  => __('Name', 'dgw'),
            'order' => __('Show Order', 'dgw'),
            'slug'  => __('Slug', 'dgw'),
            'posts' => __('Count', 'dgw'),
        ];
    }

    /**
     * [2026-11-20] - @author: Kelvin - Hiển thị giá trị cột Show Order
     */
    public function renderColumn($out, string $columnName, int $termId)
    {
        if ($columnName !== 'order') {
            return $out;
        }

        $raw   = get_option($this->optionPrefix . $termId, []);
        $data  = is_array($raw) ? $raw : [];
        $order = (string) ($data[$this->fieldKeyPrefix . '_order'] ?? $data['cate_order'] ?? $data['cate_advertising_order'] ?? $data['cate_solution_order'] ?? '0');

        return esc_html($order);
    }
}
