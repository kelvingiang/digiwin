<div class="side-list">
    <div class="side-list-title">
        <h2><?php _e('cases') ?></h2>
    </div>
    <?php
    $wp_query = getCustomPostAtSide('casestudies', 5);
    if ($wp_query->have_posts()) :
        $stt = 1;
        while ($wp_query->have_posts()) :
            $wp_query->the_post();
    ?>
            <div class="item" data-id="<?php echo $stt ?>"
                data-link="<?php echo get_the_permalink(); ?>"
                data-post="<?php echo get_the_ID(); ?>">
                <?php the_title(); ?>
            </div>
    <?php
        endwhile;
    endif;
    wp_reset_postdata();
    wp_reset_query();
    ?>
</div>