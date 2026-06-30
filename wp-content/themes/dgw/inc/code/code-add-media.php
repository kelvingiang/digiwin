<?php

function add_wp_media($item)
{
?>
    <hr>
    <div class="row">
        <div id="wp-txt_note-media-buttons" class="wp-media-buttons">
            <button id="insert-media-button_my" class="button insert-media add_media " type="button" data-editor="content">
                <span class="wp-media-buttons-icon"></span> <?php _e('Add Product Images') ?>
            </button>
        </div>
        <div><input type="hidden" id="hidden_img" name="hidden_img" value="<?php echo $item ?>" /></div>
    </div>
    <div class="clear"></div>
    <div id="show-img" class="meta-row">
        <?php
        if (!empty($item)) {
            $dd = substr($item, 0, -1);
            $carouselImgArge = explode('|', $dd);

            foreach ($carouselImgArge as $val) {
                $imgpost = get_post($val);
                echo "<div class='item-img'>"
                    . "<img src = '$imgpost->guid' class='alignnone size-medium wp-image-$val' data-toggle='hinh' />"
                    . "<div class='del-item'><label>刪除</label></div>"
                    . "</div>";
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

            // KHI MOI LOAD QUET 1  img TRONG #show-img LAY GIA TRI id CAC HINH DUA VAO #hidden_img 
            var str = '';
            jQuery("#show-img").children('.item-img').each(function() {
                var strClass = jQuery(this).children('img').attr('class');
                var arge = strClass.split(" ");
                var postID = arge[2].split("-");
                str += postID[2] + '|';
            });
            jQuery("#hidden_img").val(str);

            //  START     CAC TAC MOI CUA SO HINH CUA WORDPRESS
            var $state_manager = {
                active_item: 'null',
                default_send_to_editor: window.send_to_editor
            };
            // click moi cua so chua hinh cua wordpress
            jQuery('#insert-media-button_my').click(function() {
                $state_manager.active_item = jQuery(this).attr('data-unqiue-id');
                // open the window and do whatever else you need
            });
            // sao khi chon hinh gia tri tra ve la html trong do the <img
            window.send_to_editor = function(html) {
                if ($state_manager.active_item === 'null') {
                    //call the default
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
                        // jQuery(this).attr("data-toggle", "tooltip");
                        //                            jQuery(this).attr("data-original-title", "Click Remove it");
                        //                            jQuery(this).attr("title", "Click Remove it");
                        // quet cac class mac dinh va tach class co chua ID cua hinh anh 
                        var strClass = jQuery(this).children('img').attr('class');
                        // console.log(strClass);
                        var arge = strClass.split(" ");
                        var postID = arge[2].split("-");
                        str += postID[2] + '|';
                    });
                    jQuery("#hidden_img").val(str);
                    //do some custom stuff here being sure to reset
                    // active_item to 'null' once completed
                    $state_manager.active_item = 'null';
                }
            };

            // click vao hinh remove hin    h do 
            jQuery(document).on('click', '.del-item', function() {
                jQuery(this).parent().remove();
                jQuery("#hidden_img").val(" ");
                // remove hinh xong set la gia tri cho #hidden-img
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
