<!-- <h2 class="h2-home-title"><?php //_e('Enterprise model success case') 
                                ?>22</h2> -->
<div class="case-logo">
    <?php
    require_once(DIR_MODEL . 'model-logo-function.php');
    $model = new Model_Logo_Function();
    $data = $model->getAll(0);
    if (count($data) > 0) {
        foreach ($data as $key => $value) {
            // echo '<pre>'; print_r($value); echo '</pre>';
    ?>
            <div class="case-logo-item">
                <a href='<?php echo $value['link'] ?>' class="case-logo-card">
                    <?php
                    // Tạo đường dẫn vật lý đến file ảnh trên server
                    $image_path = DIR_IMAGES . 'logo/' . $value['img'];

                    // Mặc định nếu không lấy được ảnh
                    $width = 225;
                    $height = 225;

                    // Kiểm tra file có tồn tại không và lấy kích thước
                    if (file_exists($image_path)) {
                        $image_info = getimagesize($image_path);
                        $width = $image_info[0]; // Chiều rộng thực tế
                        $height = $image_info[1]; // Chiều cao thực tế
                    }
                    ?>
                    <img alt="digiwin partner"
                        src="<?php echo PART_IMAGES . 'logo/' . $value['img'] ?>"
                        fetchpriority="high"
                        width="<?php echo $width; ?>"
                        height="<?php echo $height; ?>" />
                </a>
            </div>
    <?php
        }
    }
    ?>
</div>
<script>
    jQuery(document).ready(function() {

    });
</script>