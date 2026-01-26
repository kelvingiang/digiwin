<?php
require_once(DIR_MODEL . 'model-popup-function.php');
if (!empty(getParams('id'))) {
    $model = new Model_Popup_Function();
    $data = $model->getItem(getParams('id'));
    $id =  $data['ID'] ?? null;
    $title =  $data['title'] ?? null;
    $link_vn =  $data['link_vn'] ?? null;
    $link_cn =  $data['link_cn'] ?? null;
    $id_cn =  $data['id_cn'] ?? null;
    $id_vn =  $data['id_vn'] ?? null;
    $img_vn =  $data['img_vn'] ?? null;
    $img_cn =  $data['img_cn'] ?? null;
    $img_mobile_cn =  $data['img_mobile_cn'] ?? null;
    $img_mobile_vn =  $data['img_mobile_vn'] ?? null;
    $target = $data['target'] ?? 0;
}

?>
<?php if (!empty(getParams('e'))) :
    $raw = urldecode(getParams('e'));
    $raw = stripslashes($raw);   // 🔑 把多餘的反斜線去掉
    $ee = json_decode($raw, true);

    if (!empty($ee)) :
?>
        <div class="notice notice-error notice-alt is-dismissible">
            <?php foreach ($ee as $val) : ?>
                <p><?php echo $val ?></p>
            <?php endforeach ?>
        </div>
<?php
    endif;
endif
?>

<form name="f1" id="f1" method="post" enctype="multipart/form-data">
    <input type="hidden" name="hid-id" id="hid-id" value="<?php echo $id ?>" />
    <div>
        <div class="row-two-column">
            <div class="col">
                <div class="cell-title">
                    <label>標題</label>
                </div>
                <div class="cell-text">
                    <input type="text"
                        name="txt-title"
                        id="txt-title"
                        class="my-input"
                        value="<?php echo $title ?>" required />
                </div>
            </div>
            <div class="col">
                <div class="cell-title">
                    <labe>打開新 TAB</label>
                </div>
                <div class="cell-text">
                    <?php $checked = ($target == 1) ? 'checked' : '';?>

                    <input type="checkbox"
                        name="chk-target"
                        id="chk-target"
                        value="1"
                        <?php echo $checked; ?> >
                </div>
            </div>
        </div>

        <div class=" row-four-column" style="margin-top: 1.5rem;">
                    <div class="col">
                        <div class="cell-title">
                            <label>中文連接(CN)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="txt-link-cn" id="txt-link-cn" class="my-input" value="<?php echo $link_cn ?>" required />
                        </div>
                    </div>

                    <div class="col">
                        <div class="cell-title">
                            <label>中文文章-ID(CN)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="txt-id-cn" id="txt-id-cn" class="my-input" value="<?php echo $id_cn ?>"/>
                        </div>
                    </div>

                    <div class="col">
                        <div class="cell-title">
                            <label>越文連接(VN)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="txt-link-vn" id="txt-link-vn" class="my-input" value="<?php echo $link_vn ?>" />
                        </div>
                    </div>

                    <div class="col">
                        <div class="cell-title">
                            <label>越文文章-ID(VN)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="txt-id-vn" id="txt-id-vn" class="my-input" value="<?php echo $id_vn ?>" />
                        </div>
                    </div>
                </div>

                <div class="row-four-column" style="margin-top: 1.5rem;">
                    <div class="col">
                        <div class="cell-title">
                            <label>中文橫圖(CN)</label>
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
                            <label>中文豎圖(CN)</label>
                        </div>
                        <div class="cell-text">
                            <input type="file"
                                name="file-img-vertical-cn"
                                id="file-img-vertical-cn"
                                data-target="#show-img-vertical-cn"
                                accept=" image/*"
                                class="my-input" />
                        </div>
                    </div>

                    <div class="col">
                        <div class="cell-title">
                            <label>越文橫圖(VN)</label>
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
                            <label>越文豎圖(VN)</label>
                        </div>
                        <div class="cell-text">
                            <input type="file"
                                name="file-img-vertical-vn"
                                id="file-img-vertical-vn"
                                data-target="#show-img-vertical-vn"
                                accept=" image/*"
                                class="my-input" />
                        </div>
                    </div>
                </div>

                <div class="row-four-column" style="height: 200px;">
                    <div class="col show-img">
                        <div id="show-img-cn"
                            style=" background-image: url('<?php echo PART_IMAGES . 'pop-up/' . $img_cn ?>');">
                        </div>
                    </div>

                    <div class="col show-img">
                        <div id="show-img-vertical-cn"
                            style=" background-image: url('<?php echo PART_IMAGES . 'pop-up/' . $img_mobile_cn ?>');">
                        </div>
                    </div>

                    <div class="col show-img">
                        <div id="show-img-vn"
                            style=" background-image: url('<?php echo PART_IMAGES . 'pop-up/' . $img_vn ?>');">
                        </div>
                    </div>

                    <div class="col show-img">
                        <div id="show-img-vertical-vn"
                            style=" background-image: url('<?php echo PART_IMAGES . 'pop-up/' . $img_mobile_vn ?>');">
                        </div>
                    </div>
                </div>

                <div class="button-row" style="margin-top: 2rem;">
                    <button type="submit" name="btn-save" id="btn-save" class="button button-primary button-large"> 發佈</button>
                </div>
            </div>
</form>
<style type="text/css">
    .show-img {
        width: 90%;
        height: 400px;
    }

    #show-img-cn,
    #show-img-vn {
        width: 90%;
        height: 100%;
        background-repeat: no-repeat;
        background-size: contain;
    }

    #show-img-vertical-cn,
    #show-img-vertical-vn {
        width: 90%;
        height: 200px;
        background-repeat: no-repeat;
        background-size: contain;
    }
</style>
<script type="text/javascript">
    // show hinh anh truoc khi up len
    jQuery("input[type='file']").on("change", function() {
        var files = this.files || [];
        if (!files.length || !window.FileReader) return;

        if (/^image/.test(files[0].type)) {
            var target = jQuery(this).data("target"); // 找到要顯示的目標
            var reader = new FileReader();
            reader.readAsDataURL(files[0]);
            reader.onloadend = function() {
                jQuery(target).css("background-image", "url(" + this.result + ")");
            };
        }
    });
</script>