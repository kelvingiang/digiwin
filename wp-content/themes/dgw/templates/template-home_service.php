<div id="service-home">
    <?php
    $wp_query = getCustomPostAtHome('services', -1);

    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
    ?>
            <div class="service-item">

                <div class="service-item-title">
                    <a href='<?php echo get_the_permalink(); ?>'>
                        <h2><?php //the_title(); 
                            ?></h2>
                    </a>
                </div>
                <div class="service-item-img">
                    <?php if (has_post_thumbnail()) { ?>
                        <img class="llimg" loading="lazy" src="<?php the_post_thumbnail_url() ?>" srcset="<?php the_post_thumbnail_url() ?>" />
                    <?php } else { ?>
                        <img class="llimg" loading="lazy" src="<?php echo PART_IMAGES . 'no-image.jpg' ?>" srcset="<?php echo PART_IMAGES . 'no-image.jpg' ?>" />
                    <?php } ?>
                    <a class="service-item-link" href='<?php echo get_the_permalink(); ?>'>
                        <div class="service-item-content">
                            <div class="service-item-content-lable">
                                <lable><?php the_title(); ?></lable>
                            </div>
                            <div class="service-item-content-text">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
    <?php
        }
    }
    wp_reset_postdata();
    wp_reset_query();
    ?>

</div>