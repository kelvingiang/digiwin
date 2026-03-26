<div class="side-tab">
    <?php
    $cate = 'active_category';
    $page = 'actives';
    $data = getAllCategories($cate, 0, $page);
    $stt = 1;
    foreach ($data as $val) : ?>
        <div class="tab-title title-tab-<?php echo $val['ID'] ?> 
    <?php echo ($stt == 1 ? 'active' : ''); ?>"
            data-target="content-tab-<?php echo $val['ID']; ?>">
            <h3><?php echo $val['name']; ?></h3>
        </div>
    <?php
        $stt = $stt + 1;
    endforeach ?>
</div>

<div class="side-tab-content">
    <?php foreach ($data as $index => $val) : ?>
        <div class="card-list content-tab-<?php echo $val['ID']; ?> 
        <?php echo ($index == 0 ? 'active' : ''); ?>">
            <?php
            $tax = 'active_category';
            $tax_id = $val['ID'];
            $wp_query = getCustomPostAtSideCate('active', -1, $tax, $tax_id);
            while ($wp_query->have_posts()) :
                $wp_query->the_post();
            ?>
                <a class="card-item" href="<?php the_permalink(); ?>">
                    <div class="card-title"><?php the_title(); ?>dd</div>
                </a>
            <?php endwhile;
            wp_reset_postdata(); ?>
        </div>
    <?php endforeach ?>
</div>

<script>
    jQuery(document).ready(function() {

        jQuery('.tab-title').click(function() {
            let target = jQuery(this).data('target');

            // tab active
            jQuery('.tab-title').removeClass('active');
            jQuery(this).addClass('active');

            // content active
            jQuery('.card-list').removeClass('active');
            jQuery('.' + target).addClass('active');

        });


    });
</script>