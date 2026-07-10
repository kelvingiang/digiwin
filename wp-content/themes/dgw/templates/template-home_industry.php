<h2 class="h2-home-title"><?php _e('Specialize in the industry') ?></h2>
<div id="industry-home">
    <?php
    $stt = 1;
    $wp_query = getCustomPostAtHome('industries', 8);

    if ($wp_query->have_posts()) :
        while ($wp_query->have_posts()) :
            $wp_query->the_post();
            $post_id = get_the_ID();
            $permalink = get_the_permalink();
            $title = get_the_title();
            $thumb_id = get_post_thumbnail_id($post_id);
            $url_desktop = wp_get_attachment_image_src($thumb_id, 'medium');   // Desktop
            $url_mobile  = wp_get_attachment_image_src($thumb_id, 'thumbnail');  // Mobile
    ?>
            <div class="item" data-id="<?php echo $stt; ?>" data-post="<?php echo $post_id; ?>">
                <a href="<?php echo esc_url($permalink); ?>">
                    <div class="item-img">
                        <?php if (has_post_thumbnail()) :?>
                            <picture>
                                <!-- Mobile <= 768px: dùng thumbnail -->
                                <source
                                    media="(max-width: 768px)"
                                    srcset="<?php echo esc_url($url_mobile[0]); ?>">

                                <!-- Desktop > 768px: dùng medium -->
                                <source
                                    media="(min-width: 769px)"
                                    srcset="<?php echo esc_url($url_desktop[0]); ?>">

                                <!-- Fallback: width/height lấy từ ảnh desktop thực tế -->
                                <img
                                    alt="<?php the_title_attribute(); ?>"
                                    src="<?php echo esc_url($url_desktop[0]); ?>"
                                    loading="lazy"
                                    width="<?php echo $url_desktop[1]; ?>"
                                    height="<?php echo $url_desktop[2]; ?>" />
                            </picture>
                        <?php else : ?>
                            <img
                                src="<?php echo PART_IMAGES . 'no-image.jpg'; ?>"
                                alt="<?php echo esc_attr($title); ?>"
                                width="410"
                                height="270" />
                        <?php endif; ?>
                    </div>
                    <div class="item-content">
                       <h3> <?php echo $title; ?></h3>
                    </div>
                </a>
            </div>
    <?php
            $stt++;
        endwhile;
        wp_reset_postdata(); // 必加：重置全域 post 資料
    endif;
    ?>
</div>