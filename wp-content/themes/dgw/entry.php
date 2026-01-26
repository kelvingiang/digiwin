
        <?php if (is_singular()) : ?>
            <h1 class="entry-title">
                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?>s</a>
            </h1>
        <?php else : ?>
            <?php 
                $stt = $args['stt'] ?? ''; ?>
                        <div class="item" data-id="<?php echo $stt ?>"
                            data-link="<?php echo get_the_permalink(); ?>"
                            data-post="<?php echo get_the_ID(); ?>">
                            <div>
                                <?php if (has_post_thumbnail()) { ?>
                                    <img class="item-img" src="<?php the_post_thumbnail_url() ?>" srcset="<?php the_post_thumbnail_url() ?>" />
                                <?php } else { ?>
                                    <img class="item-img" src="<?php echo PART_IMAGES . 'no-image.jpg' ?>" srcset="<?php echo PART_IMAGES . 'no-image.jpg' ?>" />
                                <?php } ?>
                                <?php
                                get_template_part('templates/template', 'view_comment');
                                ?>
                            </div>
                            <div class="item-title">
                                <?php the_title() ?>
                            </div>
                        </div>
        <?php endif ?>



