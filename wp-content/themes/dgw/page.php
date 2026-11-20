<?php get_header(); ?>
<main id="content">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <?php
                // [17/11/2026] - Không render <h1 class="entry-title"> trên các trang about-* và contact-*; các trang khác giữ nguyên
                $dgw_post_slug        = sanitize_title((string) get_post_field('post_name', get_the_ID()));
                $dgw_hide_entry_title = (bool) preg_match('/^(about|contact)-/', $dgw_post_slug);
                if (!$dgw_hide_entry_title) :
                ?>
                    <header class="header">
                        <h1 class="entry-title"><?php the_title(); ?></h1> <?php edit_post_link(); ?>
                    </header>
                <?php endif; ?>
                <div class="entry-content">
                    <?php if (has_post_thumbnail()) {
                        the_post_thumbnail();
                    } ?>
                    <?php the_content(); ?>
                    <div class="entry-links"><?php wp_link_pages(); ?></div>
                </div>
            </article>
            <?php if (comments_open() && !post_password_required()) {
                comments_template('', true);
            } ?>
    <?php endwhile;
    endif; ?>
</main>
<?php //get_sidebar(); ?>
<?php get_footer(); ?>