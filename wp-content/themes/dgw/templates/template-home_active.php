<div id="active-home">
    <?php
    $wp_query = getCustomPostAtHome('active', -1);

    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
    ?>
            <div class="active-item">

                <div class="active-item-img">
                    <?php if (has_post_thumbnail()) { ?>
                        <img src="<?php the_post_thumbnail_url() ?>" srcset="<?php the_post_thumbnail_url() ?>" fetchpriority="high" />
                    <?php } else { ?>
                        <img src="<?php echo PART_IMAGES . 'no-image.jpg' ?>" srcset="<?php echo PART_IMAGES . 'no-image.jpg' ?>" fetchpriority="high" />
                    <?php } ?>
                </div>

                <div class="active-item-content">
                    <a href="<?php echo get_the_permalink()  ?>"> <?php the_title(); ?></a>
                </div>
            </div>
    <?php
        }
    }
    wp_reset_postdata();
    wp_reset_query();
    ?>
</div>
<script>
    jQuery(document).ready(function() {

        jQuery('.active-item').mouseover(function() {
            jQuery(this).children('.active-item-img').css({
                'transition-delay': '0s',
                'transform': 'rotateY(-90deg)',
                '-webkit-transform': 'rotateY(-90deg)',
                '-moz-transform': 'rotateY(-90deg)',
                '-ms-transform': 'rotateY(-90deg)',
                '-o-transform': 'rotateY(-90deg)',
            });
            jQuery(this).children('.active-item-content').css({
                'transition-delay': '0.5s',
                'transform': 'rotateY(0deg)',
                '-webkit-transform': 'rotateY(0deg)',
                '-moz-transform': 'rotateY(0deg)',
                '-ms-transform': 'rotateY(0deg)',
                '-o-transform': 'rotateY(0deg)',
            });
        });

        jQuery('.active-item').mouseleave(function() {
            jQuery(this).children('.active-item-img').css({
                'transition-delay': '0.5s',
                'transform': 'rotateY(0deg)',
                '-webkit-transform': 'rotateY(0deg)',
                '-moz-transform': 'rotateY(0deg)',
                '-ms-transform': 'rotateY(0deg)',
                '-o-transform': 'rotateY(0deg)',
            });
            jQuery(this).children('.active-item-content').css({
                'transition-delay': '0s',
                'transform': 'rotateY(90deg)',
                '-webkit-transform': 'rotateY(90deg)',
                '-moz-transform': 'rotateY(90deg)',
                '-ms-transform': 'rotateY(90deg)',
                '-o-transform': 'rotateY(90deg)',
            });
        });
    });
</script>