<?php
// [20/11/2026] - Chuẩn MVC: View chỉ nhận biến $data từ LogoController, không khởi tạo Model trực tiếp
$data    = isset($data) && is_array($data) ? $data : [];
$ID      = $data['ID'] ?? null;
$img     = $data['img'] ?? null;
$company = $data['company'] ?? null;
$link    = $data['link'] ?? null;
?>
<form name="f1" id="f1" method="post" enctype="multipart/form-data">
    <?php // [2026-11-20] - @author: Kelvin - Bổ sung Nonce bảo vệ form logo và Late Escaping ?>
    <?php wp_nonce_field('dgw_logo_save_action', 'dgw_logo_nonce'); ?>
    <input type="hidden" name="hid-id" id="hid-id" value="<?php echo esc_attr((string) $ID); ?>" />
    <input type="hidden" name="hid-img" id="hid-img" value="<?php echo esc_attr((string) $img); ?>" />
    <div style="margin-top:1rem">
        <div class="row-two-column">

            <div class="col">
                <div class="cell-title">
                    <label> <?php echo esc_html__('Company Name', 'dgw'); ?> </label>
                </div>
                <div class="cell-text">
                    <input type="text" name="txt-company"
                        id="txt-company"
                        class="my-input"
                        value="<?php echo esc_attr((string) $company); ?>"
                        required />
                </div>
            </div>

            <div class="col">
                <div class="cell-title">
                    <label> <?php echo esc_html__('Link', 'dgw'); ?> </label>
                </div>
                <div class="cell-text">
                    <input type="text" name="txt-link"
                        id="txt-link"
                        class="my-input"
                        value="<?php echo esc_attr((string) $link); ?>" />
                </div>
            </div>
        </div>

        <div class="row-four-column" style="height: 250px; margin-top:2rem">
            <div class="col">
                <div class="cell-title">
                    <label><?php echo esc_html__('Trademark', 'dgw'); ?> <i class="error"> <?php echo esc_html((string) getParams('e')); ?> </i></label>
                </div>
                <div class="cell-text">
                    <input type="file" name="file-logo" id="file-logo" accept="image/*" class="my-input" />
                </div>
            </div>
            <div class="col">
                <div id="show-img" style=" background-image: url('<?php echo esc_url(PART_IMAGES . 'logo/' . $img); ?>');">
                </div>
            </div>
            <div class="col"></div>
        </div>

        <div class="button-row">
            <button type="submit" name="btn-save" id="btn-save" class="button button-primary button-large">
                <?php echo __('Submit', 'dgw') ?>
            </button>
        </div>
    </div>
</form>
<style type="text/css">
    #show-img {
        min-width: 200px;
        min-height: 200px;
        background-repeat: no-repeat;
        background-size: contain;
    }
</style>
<script type="text/javascript">
    // show hinh anh truoc khi up len
    jQuery(function() {
        jQuery("#file-logo").on("change", function() {
            var files = !!this.files ? this.files : [];
            if (!files.length || !window.FileReader)
                return; // no file selected, or no FileReader support

            if (/^image/.test(files[0].type)) { // only image file
                var reader = new FileReader(); // instance of the FileReader
                reader.readAsDataURL(files[0]); // read the local file

                reader.onloadend = function() { // set image data as background of div
                    jQuery("#show-img").css("background-image", "url(" + this.result + ")");
                };
            }
        });
    });
</script>