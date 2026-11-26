<?php get_header(); ?>
<main id="content">
<header class="header">
<!-- [25/11/2026] - Đồng bộ chuẩn hóa get_the_archive_description() + wp_kses_post() và Late Escaping cho trang tag -->
<h1 class="entry-title"><?php echo esc_html((string) single_term_title('', false)); ?></h1>
<?php
$archiveDescription = get_the_archive_description();
if ($archiveDescription !== '') :
?>
<div class="archive-meta"><?php echo wp_kses_post($archiveDescription); ?></div>
<?php endif; ?>
</header>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<?php get_template_part( 'entry' ); ?>
<?php endwhile; endif; ?>
<?php get_template_part( 'nav', 'below' ); ?>
</main>
<?php get_sidebar(); ?>
<?php get_footer(); ?>