<?php

class Metabox_Service {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create() {
        $id = 'admin-metabox-service';
        $title = __('Services');
        $callback = array($this, 'display');
        add_meta_box($id, $title, $callback, array('services',));
    }

    public function display($post) {
        $action = 'admin-metabox-data';
        $name = 'admin-metabox-data-nonce';
        wp_nonce_field($action, $name);
        ?>

        <div class="clear"></div>

        <div id="tabs">
            <ul>
                <li><a href="#tabs-1"><?php _e('Chinese') ?></a></li>
                <li><a href="#tabs-2"><?php _e('Vietnamese') ?></a></li>
                <li><a href="#tabs-3"><?php _e('English') ?></a></li>
            </ul>
            <div id="tabs-1">
                <div class="row-two-column">
                    <div class="col">
                        <div class="cell-title">
                            <label><?php _e('Service Name') ?> (<?php _e('Chinese') ?>)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="txt-name-cn" id="txt-name-cn" class="my-input"
                                   value="<?php echo get_post_meta($post->ID, '_service_name_cn', true) ?>" />
                        </div>
                    </div>
                </div>
                <div class="row-one-column">
                    <div class="cell-title">
                        <label><?php _e('Service Content') ?> (<?php _e('Chinese') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta($post->ID, '_service_content_cn', true), 'txt-content-cn', array('wpautop' => false, 'editor_height' => '300px')) ?>
                    </div>
                </div>

            </div>

            <div id="tabs-2">
                <div class="row-two-column">
                    <div class="col">
                        <div class="cell-title">
                            <label><?php _e('Service Name') ?> (<?php _e('Vietnamese') ?>)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="txt-name-vn" id="txt-name-vn" class="my-input"
                                   value="<?php echo get_post_meta($post->ID, '_service_name_vn', true) ?>" />
                        </div>
                    </div>
                </div>
                <div class="row-one-column">
                    <div class="cell-title">
                        <label><?php _e('Service Content') ?> (<?php _e('Vietnamese') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta($post->ID, '_service_content_vn', true), 'txt-content-vn', array('wpautop' => false, 'editor_height' => '300px')) ?>
                    </div>
                </div>

            </div>

            <div id="tabs-3">
                <div class="row-two-column">
                    <div class="col">
                        <div class="cell-title">
                            <label><?php _e('Service Name') ?> (<?php _e('English') ?>)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="txt-name-en" id="txt-name-en" class="my-input"
                                   value="<?php echo get_post_meta($post->ID, '_service_name_en', true) ?>" />
                        </div>
                    </div>
                </div>
                <div class="row-one-column">
                    <div class="cell-title">
                        <label><?php _e('Service Content') ?> (<?php _e('English') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta($post->ID, '_service_content_en', true), 'txt-content-en', array('wpautop' => false, 'editor_height' => '300px')) ?>
                    </div>
                </div>

            </div>
        </div>

        <script>
            jQuery(function () {
                jQuery("#tabs").tabs();
            });
        </script>
        <?php
    }

    public function save($post_id) {

        // global $wpdb;
        //  $wpdb->update($wpdb->posts, array('post_title' => $_POST['txt-name-cn']), array('ID' => $post_id));
        // kiem thanh phan an bao mat cua wp
        // 4 BON PHAN TREN DUNG DE BAO MAT KHI LUU METABOX TRONG WP 

        update_post_meta($post_id, '_service_name_cn', $_POST['txt-name-cn']);
        update_post_meta($post_id, '_service_content_cn', $_POST['txt-content-cn']);

        update_post_meta($post_id, '_service_name_vn', $_POST['txt-name-vn']);
        update_post_meta($post_id, '_service_content_vn', $_POST['txt-content-vn']);

        update_post_meta($post_id, '_service_name_en', $_POST['txt-name-en']);
        update_post_meta($post_id, '_service_content_en', $_POST['txt-content-en']);
    }

}
