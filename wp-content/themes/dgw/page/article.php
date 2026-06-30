<?php /*  Template Name: Article Page */ ?>
<?php get_header(); ?>
<div>
    <?php pageImg(get_the_ID()); ?>
</div>
<div style="height: 3rem;"></div>
<div class="container-fluid">
    <div class="page-col">
        <div>
            <div class="page-title">
                <h1><?php // _e('News') 
                    ?> </h1>
            </div>

            <div class='data-list'>
                <?php
                global $wp;

                $wp_query = getPostCategory('article', get_option('first_load'));

                if ($wp_query->have_posts()) {
                    $stt = 1;
                    while ($wp_query->have_posts()) {
                        $wp_query->the_post();
                ?>
                        <div class="item" data-id="<?php echo $stt ?>">
                            <?php if (has_post_thumbnail()) { ?>
                                <img class="item-img" src="<?php the_post_thumbnail_url() ?>" srcset="<?php the_post_thumbnail_url() ?>" />
                            <?php } else { ?>
                                <img class="item-img" src="<?php echo PART_IMAGES . 'no-image.jpg' ?>" srcset="<?php echo PART_IMAGES . 'no-image.jpg' ?>" />
                            <?php } ?>
                            <div class="item-title">
                                <a href="<?php echo get_the_permalink() ?>">
                                    <?php the_title() ?>
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
            <div id="load-more">
                <svg class="load-more-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"> <path d = "M342.6 534.6C330.1 547.1 309.8 547.1 297.3 534.6L137.3 374.6C124.8 362.1 124.8 341.8 137.3 329.3C149.8 316.8 170.1 316.8 182.6 329.3L320 466.7L457.4 329.4C469.9 316.9 490.2 316.9 502.7 329.4C515.2 341.9 515.2 362.2 502.7 374.7L342.7 534.7zM502.6 182.6L342.6 342.6C330.1 355.1 309.8 355.1 297.3 342.6L137.3 182.6C124.8 170.1 124.8 149.8 137.3 137.3C149.8 124.8 170.1 124.8 182.6 137.3L320 274.7L457.4 137.4C469.9 124.9 490.2 124.9 502.7 137.4C515.2 149.9 515.2 170.2 502.7 182.7z" /> </svg>
            </div>
        </div>

        <div>
            <?php get_template_part('templates/template', 'side_active');  ?>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function() {
        var isLoading = false;
        var hasMore = true;

        jQuery('#load-more').hide();

        function loadMoreCases() {
            if (isLoading || !hasMore) return;

            var lastItem = jQuery(".data-list > div:last-child");
            if (lastItem.length === 0) return;

            var scrollBottom = jQuery(window).scrollTop() + jQuery(window).height();
            var lastItemBottom = lastItem.offset().top + lastItem.outerHeight();

            if (scrollBottom >= lastItemBottom - 50) {
                isLoading = true;

                var lastID = lastItem.attr("data-id");
                var post = 'post';
                var count = <?php echo get_option('more_load') ?>;
                var cate = 'article';

                jQuery.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'post',
                    data: {
                        action: 'load_more_post',
                        lastID: lastID,
                        post: post,
                        cate: cate,
                        count: count,
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        var spinner = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite; display: inline-block;"><style>@keyframes spin { 100% { transform: rotate(360deg); } }</style><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>';
                        jQuery('#load-more').show().html('<p style="text-align:center; margin:10px 0;">' + spinner + '</p>');
                    },
                    success: function(data) {
                        if (data.status === 'done') {
                            jQuery(".data-list").append(data.html);
                            isLoading = false;
                            jQuery('#load-more').hide();
                        } else if (data.status === 'empty') {
                            hasMore = false;
                            jQuery("#load-more").hide();
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        isLoading = false;
                        jQuery('#load-more').hide();
                    }
                });
            }
        }

        jQuery(window).scroll(function() {
            loadMoreCases();
        });
    });
</script>

<?php get_footer(); ?>