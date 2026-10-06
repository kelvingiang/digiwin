<div id="article-side-list">
    <?php
    // Danh sách các loại sidebar bạn muốn hiển thị
    $sidebar_types = ['resources', 'active', 'casestudies', 'joinus', 'solutions'];

    foreach ($sidebar_types as $type) :
        $wp_query = getCustomPostShowSidebar($type);
        
        if ($wp_query->have_posts()) :
            while ($wp_query->have_posts()) : $wp_query->the_post(); 
                // Lấy Alt Text
                $thumb_id = get_post_thumbnail_id();
                $alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
                if (empty($alt)) {
                    $alt = get_the_title();
                }
                
                // Xác định ảnh hiển thị
                $img_url = has_post_thumbnail() ? get_the_post_thumbnail_url() : PART_IMAGES . 'no-image.jpg';
                // Nếu là ảnh mặc định, đổi alt text theo ý bạn
                if (!has_post_thumbnail()) $alt = "digiwin software";
                ?>
                
                <div class="item" data-id="<?php the_ID(); ?>" 
                     data-link="<?php the_permalink(); ?>" 
                     data-post="<?php the_ID(); ?>">
                    <img src="<?php echo $img_url; ?>" 
                         srcset="<?php echo $img_url; ?>" 
                         alt="<?php echo esc_attr($alt); ?>" />
                </div>

            <?php 
            endwhile;
            wp_reset_postdata();
        endif;
    endforeach; 
    ?>
</div>