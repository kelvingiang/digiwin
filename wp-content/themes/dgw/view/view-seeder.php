<?php
/**
 * [26/10/2026] - View hiển thị giao diện "Tạo Data Test (Dummy Data Seeder)" trong WP-Admin.
 * Hỗ trợ vừa chọn tự động Post Type & Category qua JS, vừa cho phép tự nhập tay nếu cần,
 * kèm bảng thống kê và nút dọn dẹp 1-click.
 *
 * @var array  $post_types_map Dữ liệu Post Types -> Taxonomies -> Terms từ Model_Seeder
 * @var array  $dummy_stats    Thống kê số lượng bài test đang có theo từng Post Type
 * @var string $notice_message Thông báo kết quả thực thi
 * @var string $notice_type    Loại thông báo ('success' | 'error')
 */

if (!defined('ABSPATH')) {
    exit;
}

$total_dummy = array_sum($dummy_stats);
?>
<div class="wrap dgw-seeder-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-database-add" style="font-size: 28px; width: 28px; height: 28px; margin-right: 6px; color: #2271b1;"></span>
        <?php echo esc_html__('Công cụ Tạo Dữ Liệu Test Nhanh (Dummy Data Seeder)', 'dgw'); ?>
    </h1>
    <hr class="wp-header-end">

    <?php if (!empty($notice_message)) : ?>
        <div class="notice notice-<?php echo esc_attr($notice_type); ?> is-dismissible" style="margin-top: 15px;">
            <p><strong><?php echo esc_html($notice_message); ?></strong></p>
        </div>
    <?php endif; ?>

    <div class="dgw-seeder-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 20px; align-items: start;">
        <!-- CỘT TRÁI: FORM TẠO DỮ LIỆU -->
        <div class="card" style="max-width: 100%; padding: 20px 24px; margin: 0;">
            <h2 style="margin-top: 0; border-bottom: 1px solid #dcdcde; padding-bottom: 12px;">
                <?php echo esc_html__('1. Cấu hình Sinh Dữ Liệu Tự Động', 'dgw'); ?>
            </h2>

            <form method="post" action="">
                <?php wp_nonce_field('dgw_seeder_nonce_action', 'dgw_seeder_nonce'); ?>
                <input type="hidden" name="dgw_seeder_action" value="generate">

                <table class="form-table" role="presentation">
                    <tbody>
                        <!-- Số dòng cần tạo -->
                        <tr>
                            <th scope="row">
                                <label for="seeder_count"><?php echo esc_html__('Số dòng cần tạo', 'dgw'); ?> <span style="color:#d63638;">*</span></label>
                            </th>
                            <td>
                                <input type="number" name="seeder_count" id="seeder_count" value="10" min="1" max="500" class="small-text" required style="width: 100px; font-weight: 600;">
                                <span class="description"><?php echo esc_html__('Nhập số lượng bài viết muốn tạo (Tối đa 500 dòng/lần để đảm bảo tốc độ).', 'dgw'); ?></span>
                            </td>
                        </tr>

                        <!-- Chọn hoặc Nhập Post Type -->
                        <tr>
                            <th scope="row">
                                <label for="seeder_post_type"><?php echo esc_html__('Post Type', 'dgw'); ?> <span style="color:#d63638;">*</span></label>
                            </th>
                            <td>
                                <select name="seeder_post_type" id="seeder_post_type" class="regular-text" style="min-width: 280px;">
                                    <?php foreach ($post_types_map as $pt_slug => $pt_info) : ?>
                                        <option value="<?php echo esc_attr($pt_slug); ?>" <?php selected($pt_slug, 'casestudies'); ?>>
                                            <?php echo esc_html($pt_info['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div style="margin-top: 8px;">
                                    <input type="text" name="seeder_custom_post_type" id="seeder_custom_post_type" value="" class="regular-text" placeholder="<?php echo esc_attr__('Hoặc tự nhập mã post_type khác (tùy chọn)...', 'dgw'); ?>">
                                    <p class="description"><?php echo esc_html__('Mặc định chọn từ danh sách. Nếu bạn nhập ô bên dưới, hệ thống sẽ ưu tiên lấy mã post_type bạn tự nhập.', 'dgw'); ?></p>
                                </div>
                            </td>
                        </tr>

                        <!-- Chọn hoặc Nhập Taxonomy -->
                        <tr>
                            <th scope="row">
                                <label for="seeder_taxonomy"><?php echo esc_html__('Taxonomy (Nhóm danh mục)', 'dgw'); ?></label>
                            </th>
                            <td>
                                <select name="seeder_taxonomy" id="seeder_taxonomy" class="regular-text" style="min-width: 280px;">
                                    <option value=""><?php echo esc_html__('-- Không gán Taxonomy --', 'dgw'); ?></option>
                                </select>
                                <div style="margin-top: 8px;">
                                    <input type="text" name="seeder_custom_taxonomy" id="seeder_custom_taxonomy" value="" class="regular-text" placeholder="<?php echo esc_attr__('Hoặc tự nhập slug taxonomy (VD: casestudies_category)...', 'dgw'); ?>">
                                </div>
                            </td>
                        </tr>

                        <!-- Chọn hoặc Nhập Category (Term) -->
                        <tr>
                            <th scope="row">
                                <label><?php echo esc_html__('Category (Danh mục con)', 'dgw'); ?></label>
                            </th>
                            <td>
                                <fieldset style="margin-bottom: 10px;">
                                    <label style="margin-right: 16px;">
                                        <input type="radio" name="seeder_term_mode" value="random" checked>
                                        <strong><?php echo esc_html__('Phân bổ ngẫu nhiên vào các Category đang có', 'dgw'); ?></strong>
                                    </label>
                                    <label style="margin-right: 16px;">
                                        <input type="radio" name="seeder_term_mode" value="existing">
                                        <?php echo esc_html__('Chọn Category cụ thể', 'dgw'); ?>
                                    </label>
                                    <label>
                                        <input type="radio" name="seeder_term_mode" value="custom">
                                        <?php echo esc_html__('Tự nhập tên/slug Category (Chưa có sẽ tự tạo)', 'dgw'); ?>
                                    </label>
                                </fieldset>

                                <!-- Khung chọn Category có sẵn -->
                                <div id="seeder_existing_terms_box" style="display: none; background: #f6f7f7; border: 1px solid #dcdcde; padding: 10px 14px; border-radius: 4px; max-height: 160px; overflow-y: auto;">
                                    <div id="seeder_terms_checklist"></div>
                                </div>

                                <!-- Khung tự nhập Category -->
                                <div id="seeder_custom_terms_box" style="display: none; margin-top: 8px;">
                                    <input type="text" name="seeder_custom_terms" id="seeder_custom_terms" value="" class="large-text" placeholder="<?php echo esc_attr__('Nhập tên hoặc slug Category, cách nhau bằng dấu phẩy (VD: ERP, MES, WMS)...', 'dgw'); ?>">
                                    <p class="description"><?php echo esc_html__('Nếu Category chưa tồn tại trong Taxonomy trên, hệ thống sẽ tự động tạo mới kèm cấu hình đa ngôn ngữ.', 'dgw'); ?></p>
                                </div>
                            </td>
                        </tr>

                        <!-- Ngôn ngữ hiển thị (_metabox_langguage) -->
                        <tr>
                            <th scope="row">
                                <label for="seeder_lang_mode"><?php echo esc_html__('Ngôn ngữ bài viết (_metabox_langguage)', 'dgw'); ?></label>
                            </th>
                            <td>
                                <select name="seeder_lang_mode" id="seeder_lang_mode">
                                    <option value="vn"><?php echo esc_html__('Tiếng Việt (vn)', 'dgw'); ?></option>
                                    <option value="cn"><?php echo esc_html__('Tiếng Trung Phồn Thể (cn)', 'dgw'); ?></option>
                                    <option value="random" selected><?php echo esc_html__('Xen kẽ cả Tiếng Việt & Tiếng Trung (50/50)', 'dgw'); ?></option>
                                </select>
                                <p class="description"><?php echo esc_html__('Theme DGW lọc bài viết ngoài Frontend theo meta _metabox_langguage. Chọn xen kẽ giúp bạn test được cả 2 ngôn ngữ.', 'dgw'); ?></p>
                            </td>
                        </tr>

                        <!-- Tùy chọn Title & Content -->
                        <tr>
                            <th scope="row">
                                <label for="seeder_title_prefix"><?php echo esc_html__('Tiền tố Title (Tùy chọn)', 'dgw'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="seeder_title_prefix" id="seeder_title_prefix" value="[TEST]" class="regular-text" placeholder="<?php echo esc_attr__('Để trống nếu muốn sinh hoàn toàn ngẫu nhiên...', 'dgw'); ?>">
                                <p class="description"><?php echo esc_html__('Tiêu đề sẽ được tự sinh ngẫu nhiên theo chủ đề ERP/MES kèm tiền tố này (Xóa chữ [TEST] nếu muốn tiêu đề giống thật 100%).', 'dgw'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="seeder_custom_content"><?php echo esc_html__('Nội dung Content mẫu (Tùy chọn)', 'dgw'); ?></label>
                            </th>
                            <td>
                                <textarea name="seeder_custom_content" id="seeder_custom_content" rows="3" class="large-text" placeholder="<?php echo esc_attr__('Để trống hệ thống sẽ tự động sinh đoạn văn HTML (H2, H3, danh sách, trích dẫn) chuẩn SEO ngẫu nhiên...', 'dgw'); ?>"></textarea>
                            </td>
                        </tr>

                        <!-- Tùy chọn bổ sung (Featured Image & Home Page) -->
                        <tr>
                            <th scope="row"><?php echo esc_html__('Tùy chọn hiển thị Frontend', 'dgw'); ?></th>
                            <td>
                                <label style="display: block; margin-bottom: 8px;">
                                    <input type="checkbox" name="seeder_with_thumbnail" value="1" checked>
                                    <?php echo esc_html__('Tự động lấy ngẫu nhiên ảnh có sẵn trong Media Library làm Ảnh đại diện (Featured Image)', 'dgw'); ?>
                                </label>
                                <label style="display: block;">
                                    <input type="checkbox" name="seeder_show_home" value="1" checked>
                                    <?php echo esc_html__('Bật tích chọn "Show In Home Page" (_metabox_home = 1)', 'dgw'); ?>
                                </label>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p class="submit" style="margin-bottom: 0;">
                    <button type="submit" class="button button-primary button-hero">
                        <span class="dashicons dashicons-controls-play" style="margin-top: 11px; margin-right: 4px;"></span>
                        <?php echo esc_html__('Bắt đầu Tạo Dữ Liệu Test Ngay', 'dgw'); ?>
                    </button>
                </p>
            </form>
        </div>

        <!-- CỘT PHẢI: QUẢN LÝ & DỌN DẸP DATA TEST -->
        <div class="card" style="max-width: 100%; padding: 20px 24px; margin: 0;">
            <h2 style="margin-top: 0; border-bottom: 1px solid #dcdcde; padding-bottom: 12px; color: #d63638;">
                <?php echo esc_html__('2. Dọn dẹp Data Test (1-Click)', 'dgw'); ?>
            </h2>
            <p class="description">
                <?php echo esc_html__('Mọi bài viết tạo bởi công cụ này đều được gắn cờ ngầm (_dgw_is_dummy_data = 1). Khi test xong giao diện/phân trang, bạn có thể xóa sạch 100% dữ liệu test tại đây mà KHÔNG ảnh hưởng tới bài viết thật.', 'dgw'); ?>
            </p>

            <table class="widefat striped" style="margin: 16px 0;">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Post Type', 'dgw'); ?></th>
                        <th style="text-align: right;"><?php echo esc_html__('Số bài Test đang có', 'dgw'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dummy_stats)) : ?>
                        <tr>
                            <td colspan="2" style="text-align: center; color: #646970;">
                                <?php echo esc_html__('Chưa có dữ liệu test nào.', 'dgw'); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($dummy_stats as $pt => $cnt) : ?>
                            <tr>
                                <td><strong><code><?php echo esc_html($pt); ?></code></strong></td>
                                <td style="text-align: right;"><strong><?php echo esc_html((string) $cnt); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: #f0f6fc;">
                            <td><strong><?php echo esc_html__('TỔNG CỘNG', 'dgw'); ?></strong></td>
                            <td style="text-align: right;"><strong style="color: #2271b1; font-size: 14px;"><?php echo esc_html((string) $total_dummy); ?></strong></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <form method="post" action="" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn các dữ liệu test đã chọn không?');">
                <?php wp_nonce_field('dgw_seeder_nonce_action', 'dgw_seeder_nonce'); ?>
                <input type="hidden" name="dgw_seeder_action" value="cleanup">

                <div style="margin-bottom: 12px;">
                    <label for="cleanup_post_type" style="display:block; margin-bottom: 6px; font-weight: 600;">
                        <?php echo esc_html__('Chọn phạm vi muốn xóa:', 'dgw'); ?>
                    </label>
                    <select name="cleanup_post_type" id="cleanup_post_type" style="width: 100%;">
                        <option value="all"><?php echo esc_html__('Xóa TẤT CẢ dữ liệu test (' . $total_dummy . ' bài)', 'dgw'); ?></option>
                        <?php foreach ($dummy_stats as $pt => $cnt) : ?>
                            <option value="<?php echo esc_attr($pt); ?>">
                                <?php echo esc_html(sprintf('Chỉ xóa Post Type: %s (%d bài)', $pt, $cnt)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="button button-secondary" style="width: 100%; color: #d63638; border-color: #d63638; text-align: center;" <?php disabled($total_dummy, 0); ?>>
                    <span class="dashicons dashicons-trash" style="margin-top: 4px;"></span>
                    <?php echo esc_html__('Xóa Dữ Liệu Test Đã Chọn', 'dgw'); ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * [26/10/2026] - Script đồng bộ tự động Post Type -> Taxonomy -> Danh sách Category (Terms) trên giao diện Admin
 */
(function() {
    const postTypesMap = <?php echo wp_json_encode($post_types_map); ?>;
    const ptSelect = document.getElementById('seeder_post_type');
    const taxSelect = document.getElementById('seeder_taxonomy');
    const termsChecklist = document.getElementById('seeder_terms_checklist');
    const existingBox = document.getElementById('seeder_existing_terms_box');
    const customBox = document.getElementById('seeder_custom_terms_box');
    const termModeRadios = document.querySelectorAll('input[name="seeder_term_mode"]');

    function updateTaxonomies() {
        const selectedPt = ptSelect.value;
        taxSelect.innerHTML = '<option value="">-- Không gán Taxonomy --</option>';
        termsChecklist.innerHTML = '';

        if (postTypesMap[selectedPt] && postTypesMap[selectedPt].taxonomies) {
            const taxes = postTypesMap[selectedPt].taxonomies;
            const taxKeys = Object.keys(taxes);
            taxKeys.forEach((taxSlug, idx) => {
                const opt = document.createElement('option');
                opt.value = taxSlug;
                opt.textContent = taxes[taxSlug].label;
                if (idx === 0) {
                    opt.selected = true;
                }
                taxSelect.appendChild(opt);
            });
        }
        updateTermsChecklist();
    }

    function updateTermsChecklist() {
        const selectedPt = ptSelect.value;
        const selectedTax = taxSelect.value;
        termsChecklist.innerHTML = '';

        if (!selectedTax || !postTypesMap[selectedPt] || !postTypesMap[selectedPt].taxonomies[selectedTax]) {
            termsChecklist.innerHTML = '<em>Post Type / Taxonomy này chưa có Category nào. Hãy chọn chế độ "Tự nhập tên Category" để hệ thống tự khởi tạo.</em>';
            return;
        }

        const terms = postTypesMap[selectedPt].taxonomies[selectedTax].terms || [];
        if (terms.length === 0) {
            termsChecklist.innerHTML = '<em>Chưa có Category nào trong Taxonomy này. Hãy chọn chế độ "Tự nhập tên Category" để hệ thống tự tạo mới.</em>';
            return;
        }

        terms.forEach((term, index) => {
            const label = document.createElement('label');
            label.style.display = 'block';
            label.style.marginBottom = '6px';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'seeder_term_ids[]';
            checkbox.value = term.term_id;
            if (index === 0) {
                checkbox.checked = true;
            }

            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(' ' + term.name + ' (slug: ' + term.slug + ')'));
            termsChecklist.appendChild(label);
        });
    }

    function updateTermModeUI() {
        let mode = 'random';
        termModeRadios.forEach(r => {
            if (r.checked) mode = r.value;
        });

        existingBox.style.display = (mode === 'existing') ? 'block' : 'none';
        customBox.style.display = (mode === 'custom') ? 'block' : 'none';
    }

    ptSelect.addEventListener('change', updateTaxonomies);
    taxSelect.addEventListener('change', updateTermsChecklist);
    termModeRadios.forEach(r => r.addEventListener('change', updateTermModeUI));

    updateTaxonomies();
    updateTermModeUI();
})();
</script>
