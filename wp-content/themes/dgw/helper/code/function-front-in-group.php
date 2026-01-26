<?php
function inGroup($postName, $tax, $postMun)
{
    global $post;
    $postID = $post->ID; //get/put your post ID here
    $getCat = get_the_terms($postID, $tax); //as it's returning an array

    if ($getCat[0]->parent == 0) {
        $cat = $getCat[0]->term_id;
    } else {
        $cat = $getCat[0]->parent;
    }
?>
    <div class="group-list">
        <?php
        global $wp;
        $param = $wp->query_vars;
        $postType = $postName;
        $postCount = $postMun;
        $tax = $tax;
        $data = getCustomsPostByCate($postType, $cat, $postCount, $tax);

        if ($data->have_posts()) {
            $stt = 1;
            while ($data->have_posts()) {
                $data->the_post();

                // kiem tra slug trung voi slug url khong hien thi
                if ($post->post_name == $param['name']) {
                    continue;
                }
        ?>
                <div class="item row" data-id="<?php echo $stt ?>">

                    <div class="col-lg-2">
                        <?php if (has_post_thumbnail()) { ?>
                            <img class="item-img" src="<?php the_post_thumbnail_url() ?>" srcset="<?php the_post_thumbnail_url() ?>" />
                        <?php } else { ?>
                            <img class="item-img" src="<?php echo PART_IMAGES . 'no-image.jpg' ?>" srcset="<?php echo PART_IMAGES . 'no-image.jpg' ?>" />
                        <?php } ?>
                    </div>
                    <div class="col-lg-10">
                        <div class="group-list-item-title">
                            <a class="my-link " href="<?php echo get_the_permalink() ?>">
                                <h3><?php the_title() ?></h3>
                            </a>
                        </div>
                        <div>
                            <?php
                            if ($postName == 'solutions') {
                                echo mySubContent(get_post_meta($post->ID, '_solution_value', true));
                            } else {
                                the_content();
                            }
                            ?>
                        </div>
                    </div>
                </div>
        <?php
                $stt++;
            }
        }
        ?>
    </div>

    <?php if ($postCount != -1) { ?>
        <div id="load-more">
             <svg class="load-more-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                <path d="M342.6 534.6C330.1 547.1 309.8 547.1 297.3 534.6L137.3 374.6C124.8 362.1 124.8 341.8 137.3 329.3C149.8 316.8 170.1 316.8 182.6 329.3L320 466.7L457.4 329.4C469.9 316.9 490.2 316.9 502.7 329.4C515.2 341.9 515.2 362.2 502.7 374.7L342.7 534.7zM502.6 182.6L342.6 342.6C330.1 355.1 309.8 355.1 297.3 342.6L137.3 182.6C124.8 170.1 124.8 149.8 137.3 137.3C149.8 124.8 170.1 124.8 182.6 137.3L320 274.7L457.4 137.4C469.9 124.9 490.2 124.9 502.7 137.4C515.2 149.9 515.2 170.2 502.7 182.7z"/></svg>
        </div>
    <?php  } ?>



    <script>
        jQuery(document).ready(function() {
            jQuery('#load-more').click(function() {

                var lastID = jQuery(".group-list > div:last-child").attr("data-id");
                var cate = '<?php echo $postName ?>';
                var count = '<?php echo get_option('more_load') ?>';
                var slug = '<?php echo $param['name'] ?>';
                // var count = jQuery("#member-list > div").length;
                jQuery.ajax({
                    url: '<?php echo get_template_directory_uri() . '/ajax/load-more-in-group.php' ?>', // lay doi tuong chuyen sang dang array
                    type: 'post', //                data: $(this).serialize(),
                    data: {
                        lastID: lastID,
                        cate: cate,
                        slug: slug,
                        count: count,
                    },
                    dataType: 'json',
                    success: function(data) { // set ket qua tra ve  data tra ve co thanh phan status va message
                        if (data.status === 'done') {
                            jQuery(".group-list").append(data.html);
                            var $target = jQuery('html,body');
                            $target.animate({
                                scrollTop: $target.height()
                            }, 2000);
                            if (data.html === null) {
                                jQuery("#load-more").hide();
                            }
                        } else if (data.status === 'empty') {
                            jQuery("#load-more").hide();
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.reponseText);
                        //console.log(data.status);
                    }
                });
            });
        });
    </script>
<?php
}
?>