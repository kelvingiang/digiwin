<h1 class="h2-home-title"><?php _e('Corporate management focus') ?></h1>
<div id="business-home">
    <?php
    $wp_query = getCustomPostAtHome('resources', 4);
    $stt = 1;
    if ($wp_query->have_posts()) :
        while ($wp_query->have_posts()) :
            $wp_query->the_post();
            $thumb_id = get_post_thumbnail_id($post->ID);
            $url_desktop = wp_get_attachment_image_src($thumb_id, 'medium');   // Desktop
            $url_mobile  = wp_get_attachment_image_src($thumb_id, 'thumbnail');  // Mobile
    ?>
            <div class="item" data-id="<?php echo $stt ?>"
                data-link="<?php echo get_the_permalink(); ?>"
                data-post="<?php echo get_the_ID(); ?>">
                <div class="item-img">
                    <?php if (has_post_thumbnail()) { ?>
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
                                fetchpriority="high"
                                width="<?php echo $url_desktop[1]; ?>"
                                height="<?php echo $url_desktop[2]; ?>" />
                        </picture>
                    <?php } else { ?>
                        <img alt="digiwin software"
                            src="<?php echo PART_IMAGES . 'no-image.jpg' ?>"
                            fetchpriority="high"
                            width="400"
                            height="380" />
                    <?php } ?>
                </div>

                <div class="item-content">
                    <h3><?php the_title(); ?></h3>
                </div>
            </div>
    <?php
            $stt++;
        endwhile;
    endif
    ?>

</div>