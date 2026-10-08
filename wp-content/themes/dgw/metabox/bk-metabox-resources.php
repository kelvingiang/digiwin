<?php

class Metabox_Resources {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create() {
        $id = 'admin-metabox-resourecs';
        $title = __('Resources', 'dgw');
        $callback = array($this, 'display');
        add_meta_box($id, $title, $callback, array('resources',));
    }

    public function display($post) {
        $action = 'admin-metabox-data';
        $name = 'admin-metabox-data-nonce';
        wp_nonce_field($action, $name);
        ?>

        <div class="clear"></div>

        <div id="tabs">
            <ul>
                <li><a href="#tabs-1"><?php __('Chinese', 'dgw') ?></a></li>
                <li><a href="#tabs-2"><?php __('Vietnamese', 'dgw') ?></a></li>
                <li><a href="#tabs-3"><?php __('English', 'dgw') ?></a></li>
            </ul>
            <div id="tabs-1">
                <div class="row-two-column">
                    <div class="col">
                        <div class="cell-title">
                            <label><?php __('Resource Title', 'dgw') ?> (<?php __('Chinese', 'dgw') ?>)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="resource-name-cn" id="resource-name-cn" class="my-input"
                                   value="<?php echo get_post_meta($post->ID, '_resource_name_cn', true) ?>" />
                        </div>
                    </div>
                </div>
                <div class="row-one-column">
                    <div class="cell-title">
                        <label><?php __('Resource Content', 'dgw') ?> (<?php __('Chinese', 'dgw') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta($post->ID, '_resource_content_cn', true), 'resource-content-cn', array('wpautop' => false, 'editor_height' => '300px')) ?>
                    </div>
                </div>

            </div>

            <div id="tabs-2">
                <div class="row-two-column">
                    <div class="col">
                        <div class="cell-title">
                            <label><?php __('Resource Title', 'dgw') ?> (<?php __('Vietnamese', 'dgw') ?>)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="resource-name-vn" id="resource-name-vn" class="my-input"
                                   value="<?php echo get_post_meta($post->ID, '_resource_name_vn', true) ?>" />
                        </div>
                    </div>
                </div>
                <div class="row-one-column">
                    <div class="cell-title">
                        <label><?php __('Resource Content', 'dgw') ?> (<?php __('Vietnamese', 'dgw') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta($post->ID, '_resource_content_vn', true), 'resource-content-vn', array('wpautop' => false, 'editor_height' => '300px')) ?>
                    </div>
                </div>

            </div>

            <div id="tabs-3">
                <div class="row-two-column">
                    <div class="col">
                        <div class="cell-title">
                            <label><?php __('Resource Title', 'dgw') ?> (<?php __('English', 'dgw') ?>)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="resource-name-en" id="resource-name-en" class="my-input"
                                   value="<?php echo get_post_meta($post->ID, '_resource_name_en', true) ?>" />
                        </div>
                    </div>
                </div>
                <div class="row-one-column">
                    <div class="cell-title">
                        <label><?php __('Resource Content', 'dgw') ?> (<?php __('English', 'dgw') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta($post->ID, '_resource_content_en', true), 'resource-content-en', array('wpautop' => false, 'editor_height' => '300px')) ?>
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

        global $wpdb;
        // kiem thanh phan an bao mat cua wp
        // 4 BON PHAN TREN DUNG DE BAO MAT KHI LUU METABOX TRONG WP 
        if (!empty($_POST['resource-name-cn'])) {
            $wpdb->update($wpdb->posts, array('post_title' => sanitize_text_field(wp_unslash($_POST['resource-name-cn']))), array('ID' => $post_id));
            update_post_meta($post_id, '_resource_name_cn', sanitize_text_field(wp_unslash($_POST['resource-name-cn'])));
        }

        if (!empty($_POST['resource-content-cn'])) {
            update_post_meta($post_id, '_resource_content_cn', sanitize_text_field(wp_unslash($_POST['resource-content-cn'])));
        }



        if (!empty($_POST['resource-name-vn'])) {
            update_post_meta($post_id, '_resource_name_vn', sanitize_text_field(wp_unslash($_POST['resource-name-vn'])));
        }

        if (!empty($_POST['resource-content-cn'])) {
            update_post_meta($post_id, '_resource_content_vn', sanitize_text_field(wp_unslash($_POST['resource-content-vn'])));
        }



        if (!empty($_POST['resource-name-en'])) {
            update_post_meta($post_id, '_resource_name_en', sanitize_text_field(wp_unslash($_POST['resource-name-en'])));
        }

        if (!empty($_POST['resource-content-en'])) {
            update_post_meta($post_id, '_resource_content_en', sanitize_text_field(wp_unslash($_POST['resource-content-en'])));
        }
    }

}
