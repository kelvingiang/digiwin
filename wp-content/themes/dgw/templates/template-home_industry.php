<h2 class="h2-home-title"><?php _e('Specialize in the industry') ?></h2>
<div id="industry-home">
    <?php
    $stt = 1;
    $wp_query = getCustomPostAtHome('industries', 3);
    
    if ($wp_query->have_posts()) :
        while ($wp_query->have_posts()) :
            $wp_query->the_post();
            $post_id = get_the_ID();
            $thumb_id = get_post_thumbnail_id($post_id);
            $permalink = get_the_permalink();
            $title = get_the_title();
    ?>
            <div class="item" data-id="<?php echo $stt; ?>" data-post="<?php echo $post_id; ?>">
                <a href="<?php echo esc_url($permalink); ?>">
                    <div class="item-img">
                        <?php 
                        if ($thumb_id) : 
                            // 建議改用 'large' 或自訂尺寸以符合 410px 需求
                            $img_data = wp_get_attachment_image_src($thumb_id, 'full'); 
                            $srcset   = wp_get_attachment_image_srcset($thumb_id, 'full');
                            $alt      = get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: $title;
                        ?>
                            <img
                                src="<?php echo esc_url($img_data[0]); ?>"
                                <?php if ($srcset) : ?>srcset="<?php echo esc_attr($srcset); ?>"<?php endif; ?>
                                alt="<?php echo esc_attr($alt); ?>"
                                sizes="(max-width: 600px) 100vw, 410px"
                                width="<?php echo $img_data[1]; ?>"
                                height="<?php echo $img_data[2]; ?>"
                                <?php if ($stt === 1) : ?>
                                    fetchpriority="high"
                                    loading="eager"
                                <?php else : ?>
                                    loading="lazy"
                                <?php endif; ?>
                            />
                        <?php else : ?>
                            <img 
                                src="<?php echo PART_IMAGES . 'no-image.jpg'; ?>" 
                                alt="<?php echo esc_attr($title); ?>" 
                                width="410" 
                                height="270" 
                            />
                        <?php endif; ?>
                    </div>
                    <div class="item-content">
                        <?php echo $title; ?>
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