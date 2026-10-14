<div class="side-list-title">
    <?php echo esc_html__('cases', 'dgw'); ?>
</div>
<div class="side-list">
    <?php
    // [2026-10-14] - @author: Kelvin - Đổi tên biến sang $side_cases_query để tránh ghi đè biến toàn cục $wp_query
    $side_cases_query = getCustomPostAtSide('casestudies', 5);
    if ($side_cases_query->have_posts()) :
        $stt = 1;
        while ($side_cases_query->have_posts()) :
            $side_cases_query->the_post();
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