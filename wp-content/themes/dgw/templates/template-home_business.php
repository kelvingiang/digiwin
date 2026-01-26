<h2 class="h2-home-title"><?php _e('Corporate management focus') ?></h2>
<div id="business-home">
    <?php
    $wp_query = getCustomPostAtHome('resources', 4);
    $stt = 1;
    if ($wp_query->have_posts()) :
        while ($wp_query->have_posts()) :
            $wp_query->the_post();
            $thumb_id = get_post_thumbnail_id($post->ID);
            // Lấy thông tin ảnh gốc để có width/height chính xác
            $url = wp_get_attachment_image_src($thumb_id, 'medium');
            // Lấy danh sách srcset chuẩn từ WordPress
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
                            sizes="(max-width: 600px) 100vw, 300px"
                            fetchpriority="high"
                            width="<?php echo $url[1]; ?>"
                            height="<?php echo $url[2]; ?>" />
                    <?php } else { ?>
                        <img alt="digiwin software"
                            src="<?php echo PART_IMAGES . 'no-image.jpg' ?>"
                            fetchpriority="high"
                            width="1247"
                            height="831" />
                    <?php } ?>
                </div>

                <div class="item-content">
                    <?php the_title(); ?>
                </div>
            </div>
    <?php
            $stt++;
        endwhile;
    endif
    ?>

</div>