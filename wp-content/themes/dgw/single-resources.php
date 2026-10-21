<?php
/**
 * [2026-10-12] - @author: Kelvin - Đồng bộ cấu trúc chuẩn với single-casestudies.php: Technical SEO, Semantic HTML, download button và sidebar
 */
get_header(); ?>
<div class="container-fluid">
    <!-- <div class="page-title-h1">
        <h1><?php //echo esc_html(get_the_title()); ?></h1>
    </div> -->
    <div class="menu-sub">
        <?php
        $menu_category = 'resources_category';
        $menu_page     = 'resource';
        menuSub($menu_category, $menu_page);
        ?>
    </div>
    <div id="single-two-row">
        <div class="single-content">
            <?php if (have_posts()) : while (have_posts()) : the_post();
                    $source = get_post_meta(get_the_ID(), '_metabox_source', true);
            ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('single-space'); ?>>
                        <!-- <h2 class="single-space-title">
                            <?php // echo esc_html(get_the_title()); ?>
                        </h2> -->
                        <div class="single-space-content entry-content">
                            <?php the_content(); ?>
                        </div>
                        <?php if ($source) : ?>
                            <!-- [2026-10-14] - @author: Kelvin - Gán trực tiếp post ID và Title vào nút tải, tránh bị sai lệch ID từ query sidebar -->
                            <div class="single-space-download-btn">
                                <a class="btn-download" 
                                   id="my-load-data" 
                                   href="<?php echo esc_url($source); ?>" 
                                   data-post-id="<?php the_ID(); ?>" 
                                   data-post-title="<?php echo esc_attr(get_the_title()); ?>" 
                                   download>
                                    <svg class="icon" width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    <?php echo esc_html__('Download File digiwin', 'dgw'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php get_template_part('templates/view-like'); ?>
                    </article>
            <?php
                endwhile;
            endif;

            if (comments_open() || get_comments_number()) {
                comments_template();
            }
            ?>
        </div>
        <aside class="single-sidebar" aria-label="<?php echo esc_attr__('Sidebar Content', 'dgw'); ?>">
            <?php get_template_part('templates/side-active'); ?>
            <?php get_template_part('templates/side-cases');  ?>
            <?php get_template_part('templates/side-articles'); ?>
        </aside>
    </div>
</div>
<?php get_footer(); ?>
