<?php get_header(); ?>

<!-- [2026-06-30]: Thêm thẻ H1 tàng hình (Screen Reader Only) để tối ưu Technical SEO -->
<h1 style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0;">
    <?php echo get_bloginfo('name') . ' - ' . get_bloginfo('description'); ?>
</h1>

<?php get_template_part('templates/template', 'home_popup'); ?>
<div class="index-space">
    <!-- [2026-06-30]: Tối ưu Semantic HTML (Dùng thẻ section và aria-label thay cho div vô danh) -->
    <section aria-label="Hero Slider" style="position: relative;">
        <?php
        if (!is_single()) {
             get_template_part('templates/template', 'slider_owl');
        }
        ?>
    </section>



    <!-- [2026-06-30]: Thêm khối Thống kê con số -->
    <section aria-label="Our Achievements">
        <?php get_template_part('templates/template', 'home_count'); ?>
    </section>

    <section aria-label="Business Focus">
        <?php get_template_part('templates/template', 'home_business'); ?>
    </section>

    <section aria-label="Industries">
        <?php get_template_part('templates/template', 'home_industry'); ?>
    </section>

    <section aria-label="Success Cases">
        <?php get_template_part('templates/template', 'home_cases'); ?>
    </section>
        <!-- [2026-06-30]: Chuyển phần Logo Khách hàng lên đầu để tăng độ uy tín (Social Proof) -->
    <section aria-label="Partners and Clients">
        <?php get_template_part('templates/template', 'home_cases-logo'); ?>
    </section>

    <section aria-label="Latest News">
        <?php get_template_part('templates/template', 'home_news'); ?>
    </section>
</div>

<?php
get_footer();
