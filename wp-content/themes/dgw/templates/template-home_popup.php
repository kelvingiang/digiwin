<?php
require_once DIR_MODEL . 'model-popup-function.php';
$model = new Model_Popup_Function();
$data = $model->getActive();

if (!empty($data)) :
    $lang = dgw_get_lang();
?>
    <div class="dgw-popup">
        <div class="dgw-popup-space">
            <div class="dgw-popup-close">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                    <path d="M320 112C434.9 112 528 205.1 528 320C528 434.9 434.9 528 320 528C205.1 528 112 434.9 112 320C112 205.1 205.1 112 320 112zM320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 461.4 178.6 576 320 576zM231 231C221.6 240.4 221.6 255.6 231 264.9L286 319.9L231 374.9C221.6 384.3 221.6 399.5 231 408.8C240.4 418.1 255.6 418.2 264.9 408.8L319.9 353.8L374.9 408.8C384.3 418.2 399.5 418.2 408.8 408.8C418.1 399.4 418.2 384.2 408.8 374.9L353.8 319.9L408.8 264.9C418.2 255.5 418.2 240.3 408.8 231C399.4 221.7 384.2 221.6 374.9 231L319.9 286L264.9 231C255.5 221.6 240.3 221.6 231 231z" />
                </svg>
            </div>
            <div class="popup-link item"
                data-id="<?php echo $data['id_' . $lang] ?>"
                data-link="<?php echo $data['link_' . $lang] ?>"
                data-post="<?php echo $data['id_' . $lang] ?>"
                data-target="<?php echo $data['target'] ?>">

                <?php
                // 取得電腦版圖片路徑與尺寸
                $img_url_pc = PART_IMAGES . 'pop-up/' . $data['img_' . $lang];
                $img_path_pc = $_SERVER['DOCUMENT_ROOT'] . parse_url($img_url_pc, PHP_URL_PATH);
                $size_pc = @getimagesize($img_path_pc);

                // 取得手機版圖片路徑與尺寸
                $img_url_mb = PART_IMAGES . 'pop-up/' . $data['img_mobile_' . $lang];
                $img_path_mb = $_SERVER['DOCUMENT_ROOT'] . parse_url($img_url_mb, PHP_URL_PATH);
                $size_mb = @getimagesize($img_path_mb);
                ?>

                <picture>
                    <?php if ($size_pc): ?>
                        <source media="(min-width: 768px)"
                            srcset="<?php echo $img_url_pc; ?>"
                            width="<?php echo $size_pc[0]; ?>"
                            height="<?php echo $size_pc[1]; ?>">
                    <?php endif; ?>

                    <img class="img-mobile"
                        fetchpriority="high"
                        alt="digiwin software"
                        src="<?php echo $img_url_mb; ?>"
                        <?php // 移除原本 srcset 混用電腦圖的邏輯，避免比例變形 
                        ?>
                        <?php echo $size_mb ? "width='{$size_mb[0]}' height='{$size_mb[1]}'" : ""; ?>
                        style="width: 100%; height: auto; display: block;">
                </picture>
            </div>
            
        </div>
    </div>
    <script>
        // [2026-07-08] - @author: Kelvin - Dùng sessionStorage để chỉ hiện popup 1 lần trên mỗi tab
        if (sessionStorage.getItem('dgwPopupShown')) {
            document.querySelector(".dgw-popup").style.display = "none";
        } else {
            sessionStorage.setItem('dgwPopupShown', 'true');
            document.body.style.overflowY = "hidden";
            document.body.style.overflowX = "hidden";
        }

        jQuery(document).ready(function() {
            jQuery(".dgw-popup-close, .dgw-popup").on("click", function(e) {
                // Tránh lỗi khi click vào chính nội dung ảnh bên trong (nếu có thẻ con)
                if (e.target !== this && !jQuery(this).hasClass('dgw-popup-close')) {
                    if (jQuery(e.target).closest('.popup-link').length) return; 
                }
                
                jQuery(".dgw-popup").css("display", "none");
                document.body.style.overflowY = "auto";
                document.body.style.overflowX = "auto";
            });
        });
    </script>
<?php endif ?>