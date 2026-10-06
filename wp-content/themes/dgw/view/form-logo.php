<?php
require_once(DIR_MODEL . 'model-logo-function.php');
if (!empty(getParams('id'))) {
    $model = new Model_Logo_Function();
    $data = $model->getItem(getParams('id'));
    $company =  $data['company'] ?? null;
    $link =  $data['link'] ?? null;
}
?>
<form name="f1" id="f1" method="post" enctype="multipart/form-data">
    <input type="hidden" name="hid-id" id="hid-id" value="<?php echo $data['ID'] ?>" />
    <input type="hidden" name="hid-img" id="hid-img" value="<?php echo $data['img'] ?>" />
    <div>
        <div class="row-two-column">

            <div class="col">
                <div class="cell-title">
                    <label> 公司名稱 </label>
                </div>
                <div class="cell-text">
                    <input type="text" name="txt-company" id="txt-company" class="my-input" value="<?php echo $company ?>" required />
                </div>
            </div>

            <div class="col">
                <div class="cell-title">
                    <label> 文件連接 </label>
                </div>
                <div class="cell-text">
                    <input type="text" name="txt-link" id="txt-link" class="my-input" value="<?php echo $link ?>" />
                </div>
            </div>
        </div>

        <div class="row-four-column" style="height: 250px;">
            <div class="col">
                <div class="cell-title">
                    <label>商標 <i class="error"> <?php echo getParams('e') ?> </i></label>
                </div>
                <div class="cell-text">
                    <input type="file" name="file-logo" id="file-logo" accept="image/*" class="my-input" />
                </div>
            </div>
            <div class="col">
                <div id="show-img" style=" background-image: url('<?php echo PART_IMAGES . 'logo/' . $data['img'] ?>');">
                </div>
            </div>
            <div class="col"></div>
        </div>
         
        <div class="button-row">
            <button type="submit" name="btn-save" id="btn-save" class="button button-primary button-large"> 發佈</button>
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
                console.log(result);
            }
        });
    });
</script>