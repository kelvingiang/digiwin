<?php
declare(strict_types=1);

namespace DGW\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Render giao diện chọn nhiều ảnh WP Media trong metabox (chuyển từ inc/code/code-add-media.php)
 */
class MediaMetaBox
{
    /**
     * [18/11/2026] - Render bộ chọn nhiều ảnh kèm script quản lý xóa/thêm ảnh
     */
    public static function render($item): void
    {
        ?>
        <hr>
        <div class="row">
            <div id="wp-txt_note-media-buttons" class="wp-media-buttons">
                <button id="insert-media-button_my" class="button insert-media add_media" type="button" data-editor="content">
                    <span class="wp-media-buttons-icon"></span> <?php esc_html_e('Add Product Images', 'dgw'); ?>
                </button>
            </div>
            <div><input type="hidden" id="hidden_img" name="hidden_img" value="<?php echo esc_attr((string) $item); ?>" /></div>
        </div>
        <div class="clear"></div>
        <div id="show-img" class="meta-row">
            <?php
            if (!empty($item)) {
                $dd              = substr((string) $item, 0, -1);
                $carouselImgArge = explode('|', $dd);

                foreach ($carouselImgArge as $val) {
                    $valId = absint($val);
                    if ($valId <= 0) {
                        continue;
                    }
                    $imgpost = get_post($valId);
                    if (!$imgpost) {
                        continue;
                    }
                    $imgUrl = wp_get_attachment_url($valId) ?: $imgpost->guid;
                    echo "<div class='item-img'>"
                        . "<img src='" . esc_url($imgUrl) . "' class='alignnone size-medium wp-image-" . esc_attr((string) $valId) . "' data-toggle='hinh' />"
                        . "<div class='del-item'><label>" . esc_html('刪除') . '</label></div>'
                        . '</div>';
                }
            }
            ?>
        </div>
        <div class="clear"></div>
        <hr>
        <style>
            #show-img .item-img {
                width: 15%;
                margin: 5px;
                float: left;
                position: relative;
            }
            #show-img .item-img img {
                width: 100%;
                border: 1px #ccc solid;
            }
            .del-item {
                width: 100%;
                height: 99%;
                background-color: #333;
                position: absolute;
                top: 0px;
                left: 1px;
                opacity: 0.7;
                cursor: pointer;
                display: none;
            }
            .del-item label {
                text-align: center;
                display: block;
                font-size: 20px;
                font-weight: bold;
                color: #fff;
                position: relative;
                top: 50%;
            }
        </style>
        <script>
            jQuery(document).ready(function() {
                jQuery(document).on('mouseenter', '.item-img', function() {
                    jQuery(this).children('.del-item').css('display', 'block');
                });
                jQuery(document).on('mouseleave', '.item-img', function() {
                    jQuery(this).children('.del-item').css('display', 'none');
                });

                var str = '';
                jQuery("#show-img").children('.item-img').each(function() {
                    var strClass = jQuery(this).children('img').attr('class');
                    var arge = strClass.split(" ");
                    var postID = arge[2].split("-");
                    str += postID[2] + '|';
                });
                jQuery("#hidden_img").val(str);

                var $state_manager = {
                    active_item: 'null',
                    default_send_to_editor: window.send_to_editor
                };

                jQuery('#insert-media-button_my').click(function() {
                    $state_manager.active_item = jQuery(this).attr('data-unqiue-id');
                });

                window.send_to_editor = function(html) {
                    if ($state_manager.active_item === 'null') {
                        $state_manager.default_send_to_editor(html);
                    } else {
                        jQuery("#show-img").append(html);
                        jQuery('#show-img').children('img').each(function() {
                            var src = jQuery(this).attr('src');
                            var imgclass = jQuery(this).attr('class');
                            jQuery("#show-img").append("<div class='item-img'><img class ='" + imgclass + " 'src=' " + src + " '/><div class='del-item'><label>刪除</label></div></div>");
                            jQuery(this).remove();
                        });

                        var str = '';
                        jQuery("#show-img").children('.item-img').each(function() {
                            var strClass = jQuery(this).children('img').attr('class');
                            var arge = strClass.split(" ");
                            var postID = arge[2].split("-");
                            str += postID[2] + '|';
                        });
                        jQuery("#hidden_img").val(str);
                        $state_manager.active_item = 'null';
                    }
                };

                jQuery(document).on('click', '.del-item', function() {
                    jQuery(this).parent().remove();
                    jQuery("#hidden_img").val(" ");
                    str = '';
                    jQuery("#show-img").children('.item-img').each(function() {
                        var strClass = jQuery(this).children('img').attr('class');
                        var arge = strClass.split(" ");
                        var postID = arge[2].split("-");
                        str += postID[2] + '|';
                    });
                    jQuery("#hidden_img").val(str);
                });
            });
        </script>
        <?php
    }
}
