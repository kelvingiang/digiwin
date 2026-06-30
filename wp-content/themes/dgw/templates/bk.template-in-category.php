    <?php
    global $post;
    $postID = $post->ID; //get/put your post ID here
    $getProductCat = get_the_terms($postID, 'casestudies_category'); //as it's returning an array
    ?>
    <div class="group-list">
        <?php
        global $wp;
        $param = $wp->query_vars;
        $postType = 'casestudies';
        $postCount = get_option('first_load');
        $tax = 'casestudies_category';
        $wp_query = getCustomsPostByCate($postType, $getProductCat[0]->parent, $postCount, $tax);

        if ($wp_query->have_posts()) {
            $stt = 1;
            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                // kiem tra slug trung voi slug url khong hien thi
                if ($post->post_name == $param['name']) {
                    continue;
                }
        ?>
        <div class="item row" data-id="<?php echo $stt ?>">
            <div class="col-lg-2">
                <?php if (has_post_thumbnail()) { ?>
                <img class="item-img" src="<?php the_post_thumbnail_url() ?>"
                    srcset="<?php the_post_thumbnail_url() ?>" />
                <?php } else { ?>
                <img class="item-img" src="<?php echo PART_IMAGES . 'no-image.jpg' ?>"
                    srcset="<?php echo PART_IMAGES . 'no-image.jpg' ?>" />
                <?php } ?>

            </div>
            <div class="col-lg-10">
                <a class="my-link " href="<?php echo get_the_permalink() ?>">
                    <h3><?php the_title() ?></h3>
                </a>
                <?php the_content(); ?>
            </div>
        </div>
        <?php
                $stt++;
            }
        }
        ?>
    </div>
    <div id="load-more">
        <i class="fa fa-angle-double-down" aria-hidden="true"></i>
    </div>

    <script>
jQuery(document).ready(function() {
    jQuery('#load-more').click(function() {
        var lastID = jQuery(".group-list > div:last-child").attr("data-id");
        var cate = 'casestudies';
        var count = '<?php get_option('more_load') ?>';
        var slug = '<?php echo $param['name'] ?>';
        // var count = jQuery("#member-list > div").length;
        jQuery.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>', // lay doi tuong chuyen sang dang array
            type: 'post', //                data: $(this).serialize(),
            data: {
                action: 'load_more_in_group',
                lastID: lastID,
                cate: cate,
                slug: slug,
                count: count,
            },
            dataType: 'json',
            success: function(
                data) { // set ket qua tra ve  data tra ve co thanh phan status va message
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