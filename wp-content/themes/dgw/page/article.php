<?php
declare(strict_types=1);

/**
 * [2026-10-12] - @author: Kelvin
 * Template Name: Article Page
 * Description: Không ghi đè global $wp_query, bổ sung width/height chống CLS và tái sử dụng component templates/load-more.php
 */
get_header(); ?>
<div>
    <?php pageImg(get_the_ID()); ?>
</div>
<div style="height: 3rem;"></div>
<div class="container-fluid">
    <div class="page-col">
        <div>
            <div class="page-title">
                <h1><?php echo esc_html__('article', 'dgw'); ?></h1>
            </div>

            <div class="data-list">
                <?php
                $postCount    = (int) get_option('posts_per_page', 10);
                $articleQuery = getPostCategory('article', $postCount);

                if ($articleQuery->have_posts()) {
                    $stt = 1;
                    while ($articleQuery->have_posts()) {
                        $articleQuery->the_post();
                        $post_title = (string) get_the_title();
                        $post_url   = (string) get_permalink();
                        $thumb_url  = has_post_thumbnail()
                            ? (string) get_the_post_thumbnail_url(get_the_ID(), 'medium')
                            : (PART_IMAGES . 'no-image.jpg');
                ?>
                        <div class="item" data-id="<?php echo esc_attr((string) $stt); ?>">
                            <img class="item-img"
                                 src="<?php echo esc_url($thumb_url); ?>"
                                 alt="<?php echo esc_attr($post_title); ?>"
                                 loading="lazy"
                                 decoding="async"
                                 width="410"
                                 height="270" />
                            <div class="item-title">
                                <a href="<?php echo esc_url($post_url); ?>" title="<?php the_title_attribute(); ?>">
                                    <?php echo esc_html($post_title); ?>
                                </a>
                            </div>
                        </div>
                <?php
                        $stt++;
                    }
                }
                wp_reset_postdata();
                ?>
            </div>

            <?php
            get_template_part('templates/load-more', null, [
                'action'    => 'load_more_post',
                'post_type' => 'post',
                'taxonomy'  => 'article',
                'term_id'   => '',
            ]);
            ?>
        </div>

        <div>
            <?php get_template_part('templates/side-active'); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
