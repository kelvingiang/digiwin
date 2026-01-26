<h2 class="h2-home-title"><?php _e('Enterprise model success case') ?></h2>
<div id="casestudies-slider">
    <div class="owl-carousel owl-theme">
        <?php
        $wp_query = getCustomPostAtHome('casestudies', -1);

        if ($wp_query->have_posts()) :
            while ($wp_query->have_posts()) :
                $wp_query->the_post();
                $thumb_id = get_post_thumbnail_id($post->ID);
                $url = wp_get_attachment_image_src($thumb_id, 'medium');
                $srcset = wp_get_attachment_image_srcset($thumb_id, 'medium');
        ?>
                <div class="item" data-id="<?php echo $stt ?>"
                    data-link="<?php echo get_the_permalink(); ?>"
                    data-post="<?php echo get_the_ID(); ?>">
                    <div class="item-img">
                        <?php if (has_post_thumbnail()) { ?>
                            <img alt="<?php the_title_attribute(); ?>"
                                src="<?php echo esc_url($url[0]); ?>"
                                srcset="<?php echo esc_attr($srcset); ?>"
                                sizes="(max-width: 400px) 100vw, 300px"
                                fetchpriority="high"
                                width="<?php echo $url[1]; ?>"
                                height="<?php echo $url[2]; ?>" />
                        <?php } else { ?>
                            <img alt="<?php echo get_the_title(); ?>"
                                src="<?php echo PART_IMAGES . 'no-image.jpg' ?>"
                                fetchpriority="high"
                                  width="410" 
                                height="270"  />
                        <?php } ?>
                    </div>
                    <div class="item-title">
                        <h2><?php the_title(); ?></h2>
                    </div>
                </div>
        <?php
            endwhile;
        endif;
        wp_reset_postdata();
        wp_reset_query();
        ?>
    </div>
</div>
<style>
    #casestudies-slider .owl-dots {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
    }
</style>
<script>
    jQuery(document).ready(function() {
        // set so luong hien thi thong qua responsive
        var count = 0;
        bsContainerWidth = jQuery("body").width()
        if (bsContainerWidth <= 500) {
            count = 1;
        } else if (bsContainerWidth <= 950) {
            count = 2;
        } else if (bsContainerWidth <= 1170) {
            count = 3;
        } else {
            count = 3;
        }


        jQuery('#casestudies-slider .owl-carousel').owlCarousel({

            loop: true,
            margin: 10,
            nav: false,
            autoplay: true,
            autoplayTimeout: 30000,
            dots: true,
            autoplayHoverPause: true,
            items: count,
            nav: true,
            navText: ['<svg class="multi-slider-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M201.4 297.4C188.9 309.9 188.9 330.2 201.4 342.7L361.4 502.7C373.9 515.2 394.2 515.2 406.7 502.7C419.2 490.2 419.2 469.9 406.7 457.4L269.3 320L406.6 182.6C419.1 170.1 419.1 149.8 406.6 137.3C394.1 124.8 373.8 124.8 361.3 137.3L201.3 297.3z"/></svg>',
                '<svg class="multi-slider-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M439.1 297.4C451.6 309.9 451.6 330.2 439.1 342.7L279.1 502.7C266.6 515.2 246.3 515.2 233.8 502.7C221.3 490.2 221.3 469.9 233.8 457.4L371.2 320L233.9 182.6C221.4 170.1 221.4 149.8 233.9 137.3C246.4 124.8 266.7 124.8 279.2 137.3L439.2 297.3z"/></svg>'
            ],
            // onInitialized: setEqualHeight, // ← Owl 初始化後
            onInitialized: function() {
                jQuery('#casestudies-slider .owl-dot').each(function(index) {
                    jQuery(this).attr('aria-label', '切換到第 ' + (index + 1) + ' 張');
                });

                // nav 按鈕也一起補（加分）
                jQuery('#casestudies-slider .owl-prev')
                    .attr('aria-label', '上一張');
                jQuery('#casestudies-slider .owl-next')
                    .attr('aria-label', '下一張');
            },
            onResized: setEqualHeight, // ← Owl resize 後
            onTranslated: setEqualHeight // ← Owl 動畫切換後

        });

        function setEqualHeight() {
            var maxHeight = 0;

            // 先清除高度，避免之前套過的造成錯誤
            jQuery('#casestudies-slider .item').css('height', 'auto');

            // 找出最高 item
            jQuery('#casestudies-slider .item').each(function() {
                var h = jQuery(this).outerHeight();
                if (h > maxHeight) maxHeight = h;
            });

            // 套用到所有 item
            jQuery('#casestudies-slider .item').css('height', maxHeight + 'px');
        }
    });
</script>