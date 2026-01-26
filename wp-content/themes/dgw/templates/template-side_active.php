<div class="side-tab">
    <?php
    $cate = 'active_category';
    $page = 'actives';
    $data = getAllCategories($cate, 0, $page);
    $stt = 1;
    foreach ($data as $val) { ?>
        <div class="title-tab-<?php echo $val['ID'] ?> <?php echo  $stt == 1 ? 'tab-select"' : ''; ?> " onclick=" ChangSelect('.title-tab-<?php echo $val['ID'] ?>', '.content-tab-<?php echo $val['ID'] ?>' )">
            <h3><?php echo $val['name']; ?></h3>
        </div>
    <?php
        $stt = $stt + 1;
    } ?>
</div>

<div class="side-tab-content">
    <div class="side-list content-tab-5">
        <?php
        $tax = 'active_category';
        $tax_id = 5;
        $wp_query = getCustomPostAtSideCate('active', -1, $tax, $tax_id);
        while ($wp_query->have_posts()) :
            $wp_query->the_post();
        ?>
            <div class="item" data-id="<?php echo get_the_ID(); ?>"
                data-link="<?php echo get_the_permalink(); ?>"
                data-post="<?php echo get_the_ID(); ?>">
                <?php the_title(); ?>
            </div>
        <?php
        endwhile;
        wp_reset_postdata();
        wp_reset_query();
        ?>
    </div>

    <!-- // ===========================================================  -->

    <div class="side-list content-tab-6 content-select">
        <?php
        $tax = 'active_category';
        $tax_id = 6;
        $wp_query = getCustomPostAtSideCate('active', 5, $tax, $tax_id);
        while ($wp_query->have_posts()) :
            $wp_query->the_post();
        ?>
            <div class="item" data-id="<?php echo get_the_ID(); ?>"
                data-link="<?php echo get_the_permalink(); ?>"
                data-post="<?php echo get_the_ID(); ?>">
                <?php the_title(); ?>
            </div>
        <?php
        endwhile;
        wp_reset_postdata();
        wp_reset_query();
        ?>
    </div>
</div>

<script>
    function ChangSelect(titleSelect, contentSelect) {

        jQuery(titleSelect).siblings().removeClass('tab-select');
        jQuery(titleSelect).addClass('tab-select');

        jQuery('.side-tab-content').children().removeClass('content-select');
        jQuery(contentSelect).addClass('content-select');

    }
</script>