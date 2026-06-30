<?php /*  Template Name: Download Page */ ?>
<?php get_header(); ?>

<div>
  <?php pageImg(get_the_ID()); ?>
</div>

<div class="container-fluid">
  <div class="page-col">
    <div>

      <div class='dowdload-list'>
        <?php
        die('down load paga');
        global $wp;
        $param = $wp->query_vars;
        $arr = array(
          'post_type' => 'downloads',
          'posts_per_page' => -1,
          'orderby' => 'meta_value_num',
          'order' => 'DESC',
          'meta_key' => '_metabox_order',
          // get cac bai trong category

          'meta_query'    => array(
            array(
              'key'       => '_metabox_langguage',
              'value'     =>  dgw_get_lang(),
              'compare'   => '=',
            ),
            /*
            array(
                'key'       => '_metabox_home',
                'value'     =>  true,
                'compare'   => '=',
            ),
            */
          ),
        );

        $wp_query = new WP_Query($arr);

        if ($wp_query->have_posts()) {
          $stt = 1;
          while ($wp_query->have_posts()) {
            $wp_query->the_post();
        ?>
            <div class="item">
              <?php if (has_post_thumbnail()) { ?>
                <img class="item-img" src="<?php the_post_thumbnail_url() ?>" srcset="<?php the_post_thumbnail_url() ?>" />
              <?php } ?>
              <div class="item-title">
                <a href="<?php echo PART_FILE . get_post_meta(get_the_ID(), '_download_file', true) ?>" download='<?php echo get_post_meta(get_the_ID(), '_download_file', true) ?>'>
                  <i class="fa fa-download" aria-hidden="true"></i> &nbsp; &nbsp;
                  <?php the_title() ?>
                </a>
              </div>
            </div>
        <?php  }
        }
        ?>
      </div>

    </div>
    <div>
        <?php get_template_part('templates/template', 'side_active');  ?>
    </div>
  </div>
</div>


<?php get_footer(); ?>