<?php
/**
 * File: view/form-pop-up.php
 * Date: 2026-10-07
 * Author: Digiwin Team
 * Description: View hiển thị form Thêm/Chỉnh sửa Pop-up trong WordPress Admin Dashboard.
 *              Tối ưu bảo mật (XSS escaping, Nonce), fix lỗi cú pháp HTML (<labe>),
 *              khởi tạo biến an toàn và tối ưu JS Preview ảnh.
 */

require_once(DIR_MODEL . 'model-popup-function.php');

// [2026-10-07] Khởi tạo các biến mặc định tránh lỗi PHP Warning/Notice Undefined Variable
$id             = '';
$title          = '';
$link_vn        = '';
$link_cn        = '';
$id_cn          = '';
$id_vn          = '';
$img_vn         = '';
$img_cn         = '';
$img_mobile_cn  = '';
$img_mobile_vn  = '';
$target         = 0;

// [2026-10-07] Lấy dữ liệu item khi ở chế độ edit
$param_id = getParams('id');
if (!empty($param_id)) {
    $model = new Model_Popup_Function();
    $data  = $model->getItem(absint($param_id));

    if (!empty($data)) {
        $id             = $data['ID'] ?? '';
        $title          = $data['title'] ?? '';
        $link_vn        = $data['link_vn'] ?? '';
        $link_cn        = $data['link_cn'] ?? '';
        $id_cn          = $data['id_cn'] ?? '';
        $id_vn          = $data['id_vn'] ?? '';
        $img_vn         = $data['img_vn'] ?? '';
        $img_cn         = $data['img_cn'] ?? '';
        $img_mobile_cn  = $data['img_mobile_cn'] ?? '';
        $img_mobile_vn  = $data['img_mobile_vn'] ?? '';
        $target         = isset($data['target']) ? (int) $data['target'] : 0;
    }
}

// [2026-10-07] Xử lý hiển thị thông báo lỗi từ URL an toàn, chống XSS
$param_error = getParams('e');
if (!empty($param_error)) :
    $raw_error   = stripslashes(urldecode($param_error));
    $error_items = json_decode($raw_error, true);

    if (!empty($error_items) && is_array($error_items)) :
?>
        <div class="notice notice-error notice-alt is-dismissible">
            <?php foreach ($error_items as $error_msg) : ?>
                <p><?php echo esc_html($error_msg); ?></p>
            <?php endforeach; ?>
        </div>
<?php
    endif;
endif;
?>

<form name="f1" id="f1" method="post" enctype="multipart/form-data">
    <?php // [2026-10-07] Bổ sung nonce field bảo vệ form chống CSRF ?>
    <?php wp_nonce_field('dgw_popup_save_action', 'dgw_popup_nonce'); ?>
    <div><h2><?php echo __('Pop-up','dgw') ?></h2></div>
    <input type="hidden" name="hid-id" id="hid-id" value="<?php echo esc_attr($id); ?>" />

    <div>
        <div class="row-two-column">
            <div class="col">
                <div class="cell-title">
                    <label for="txt-title"><?php echo __('Title', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="text"
                        name="txt-title"
                        id="txt-title"
                        class="my-input"
                        value="<?php echo esc_attr($title); ?>"
                        required />
                </div>
            </div>
            <div class="col">
                <div class="cell-title">
                    <?php // [2026-10-07] Fix lỗi cú pháp thẻ <labe> thành <label> ?>
                    <label for="chk-target"><?php echo __('Open a New Tab', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="checkbox"
                        name="chk-target"
                        id="chk-target"
                        value="1"
                        <?php checked($target, 1); ?> />
                </div>
            </div>
        </div>

        <div class="row-four-column" style="margin-top: 1.5rem;">
            <div class="col">
                <div class="cell-title">
                    <label for="txt-link-cn"><?php echo __('Chinese Link (CN)', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="text"
                        name="txt-link-cn"
                        id="txt-link-cn"
                        class="my-input"
                        value="<?php echo esc_attr($link_cn); ?>"
                        required />
                </div>
            </div>

            <div class="col">
                <!-- <div class="cell-title">
                    <label for="txt-id-cn"><?php // echo __('Chinese Article - ID (CN)', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="text"
                        name="txt-id-cn"
                        id="txt-id-cn"
                        class="my-input"
                        value="<?php // echo esc_attr($id_cn); ?>" />
                </div> -->
            </div>

            <div class="col">
                <div class="cell-title">
                    <label for="txt-link-vn"><?php echo __('Vietnamese Link (VN)', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="text"
                        name="txt-link-vn"
                        id="txt-link-vn"
                        class="my-input"
                        value="<?php echo esc_attr($link_vn); ?>" />
                </div>
            </div>

            <div class="col">
                <!-- <div class="cell-title">
                    <label for="txt-id-vn"><?php // echo __('Vietnamese Article - ID (VN)', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="text"
                        name="txt-id-vn"
                        id="txt-id-vn"
                        class="my-input"
                        value="<?php // echo esc_attr($id_vn); ?>" />
                </div> -->
            </div>
        </div>

        <div class="row-four-column" style="margin-top: 1.5rem;">
            <div class="col">
                <div class="cell-title">
                    <label for="file-img-cn"><?php echo __('Chinese Horizontal Image (CN)', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="file"
                        name="file-img-cn"
                        id="file-img-cn"
                        data-target="#show-img-cn"
                        accept="image/*"
                        class="my-input" />
                </div>
            </div>

            <div class="col">
                <div class="cell-title">
                    <label for="file-img-vertical-cn"><?php echo __('Chinese Vertical Image (CN)', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="file"
                        name="file-img-vertical-cn"
                        id="file-img-vertical-cn"
                        data-target="#show-img-vertical-cn"
                        accept="image/*"
                        class="my-input" />
                </div>
            </div>

            <div class="col">
                <div class="cell-title">
                    <label for="file-img-vn"><?php echo __('Vietnamese Horizontal Image (VN)', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="file"
                        name="file-img-vn"
                        id="file-img-vn"
                        data-target="#show-img-vn"
                        accept="image/*"
                        class="my-input" />
                </div>
            </div>

            <div class="col">
                <div class="cell-title">
                    <label for="file-img-vertical-vn"><?php echo __('Vietnamese Vertical Image (VN)', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="file"
                        name="file-img-vertical-vn"
                        id="file-img-vertical-vn"
                        data-target="#show-img-vertical-vn"
                        accept="image/*"
                        class="my-input" />
                </div>
            </div>
        </div>

        <div class="row-four-column" style="height: 200px;">
            <div class="col show-img">
                <div id="show-img-cn"
                    class="img-preview"
                    <?php if (!empty($img_cn)) : ?>
                        style="background-image: url('<?php echo esc_url(PART_IMAGES . 'pop-up/' . $img_cn); ?>');"
                    <?php endif; ?>>
                </div>
            </div>

            <div class="col show-img">
                <div id="show-img-vertical-cn"
                    class="img-preview img-preview--vertical"
                    <?php if (!empty($img_mobile_cn)) : ?>
                        style="background-image: url('<?php echo esc_url(PART_IMAGES . 'pop-up/' . $img_mobile_cn); ?>');"
                    <?php endif; ?>>
                </div>
            </div>

            <div class="col show-img">
                <div id="show-img-vn"
                    class="img-preview"
                    <?php if (!empty($img_vn)) : ?>
                        style="background-image: url('<?php echo esc_url(PART_IMAGES . 'pop-up/' . $img_vn); ?>');"
                    <?php endif; ?>>
                </div>
            </div>

            <div class="col show-img">
                <div id="show-img-vertical-vn"
                    class="img-preview img-preview--vertical"
                    <?php if (!empty($img_mobile_vn)) : ?>
                        style="background-image: url('<?php echo esc_url(PART_IMAGES . 'pop-up/' . $img_mobile_vn); ?>');"
                    <?php endif; ?>>
                </div>
            </div>
        </div>

        <div class="button-row" style="margin-top: 2rem;">
            <button type="submit" name="btn-save" id="btn-save" class="button button-primary button-large">
                <?php echo __('Submit', 'dgw'); ?>
            </button>
        </div>
    </div>
</form>

<style type="text/css">
    /* [2026-10-07] Tối ưu hóa CSS khung hiển thị preview ảnh */
    .show-img {
        width: 90%;
        height: 200px;
    }

    .img-preview {
        width: 90%;
        height: 100%;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center;
        border: 1px dashed #ccd0d4;
        border-radius: 4px;
        background-color: #f6f7f7;
    }

    .img-preview--vertical {
        height: 200px;
    }
</style>

<script type="text/javascript">
    /**
     * [2026-10-07] Xử lý preview ảnh trước khi upload bằng FileReader,
     * kiểm tra định dạng và giải phóng bộ nhớ.
     */
    jQuery(document).ready(function($) {
        $("input[type='file']").on("change", function() {
            var files = this.files || [];
            if (!files.length || !window.FileReader) return;

            var file = files[0];
            if (/^image\//.test(file.type)) {
                var target = $(this).data("target");
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    $(target).css("background-image", "url(" + e.target.result + ")");
                };
                
                reader.readAsDataURL(file);
            }
        });
    });
</script>