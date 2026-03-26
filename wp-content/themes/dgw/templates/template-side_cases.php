<div class="side-list-title">
    <?php _e('cases') ?>
</div>
<div class="side-list">
    <?php
    $wp_query = getCustomPostAtSide('casestudies', 5);
    if ($wp_query->have_posts()) :
        $stt = 1;
        while ($wp_query->have_posts()) :
            $wp_query->the_post();
    ?>
            <a class="card-item" href="<?php the_permalink(); ?>">
                <div class="card-title"><?php the_title(); ?></div>
            </a>
    <?php
        endwhile;
    endif;
    wp_reset_postdata();

    ?>
</div>