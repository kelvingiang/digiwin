<?php

class Metabox_Seo {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create() {
        $id = 'tw-metabox-seo';
        $title = translate('Seo');
        $callback = array($this, 'display');
        $screen = array('post', 'slider'); // CAC POST VA CUSTOMER POST CHO PHEP METABOX NAY HIEN THI
        add_meta_box($id, $title, $callback, $screen);
        // FUNCTION NAY DE O DAY, DE KHI NAO DUNG DE METABOX THI TA MOI GOI FILE CSS NAY VO 
        //  add_action('admin_enqueue_scripts', array($this, 'add_css_file'));
    }

    public function display($post) {
        //        echo __METHOD__;
        // thanh an nham bao mat trong wp
        $action = 'dn-metabox-data';
        $name = 'dn-metabox-data-nonce';
        wp_nonce_field($action, $name);

        // TAO TEXTBOX TITLE
        $seo_title = get_post_meta($post->ID, '_seo_title', true);
        $seo_key = get_post_meta($post->ID, '_seo_key', true);
        $seo_description = get_post_meta($post->ID, '_seo_description', true);
        ?>
        <div class="row-two-column">
            <div class="col">
                <div class="cell-title">
                    <label style="font-size: 13px"><?php _e('Title SEO') ?> </label> 
                </div>
                <div class="cell-text">
                    <input type="text" id="txt_title" name="txt_title" class="my-input" value="<?php echo $seo_title ?>" />
                </div>
            </div>
        </div>

        <div class="row-two-column">
            <div class="col">
                <div class="cell-title">
                    <label style="font-size: 13px"><?php _e('Key SEO') ?> </label> 
                </div>
                <div class="cell-text">
                    <input type="text" id="txt_key" name="txt_key" class="my-input" value="<?php echo $seo_key ?>" />
                </div>
            </div>
        </div>

        <div class="row-one-column">
            <div class="cell-title">
                <label style="font-size: 13px"><?php _e('Description SEO') ?> </label> 
            </div>
            <div class="cell-text">
                <textarea id="txt_description" name="txt_description" rows="3" cols="117%" maxlength="80"><?php echo $seo_description ?></textarea>
            </div>
        </div>
        <?php
    }

    public function save($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (is_admin()) {
            if (!empty($_POST['txt_title'])) {
                update_post_meta($post_id, '_seo_title', $_POST['txt_title']);
            }
            if (!empty($_POST['txt_key'])) {
                update_post_meta($post_id, '_seo_key', $_POST['txt_key']);
            }
            if (!empty($_POST['txt_description'])) {
                update_post_meta($post_id, '_seo_description', $_POST['txt_description']);
            }
        }
    }

}
