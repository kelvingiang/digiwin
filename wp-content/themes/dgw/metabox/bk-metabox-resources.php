<?php

class Metabox_Resources {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create() {
        $id = 'admin-metabox-resourecs';
        $title = __('Resources');
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
                <li><a href="#tabs-1"><?php _e('Chinese') ?></a></li>
                <li><a href="#tabs-2"><?php _e('Vietnamese') ?></a></li>
                <li><a href="#tabs-3"><?php _e('English') ?></a></li>
            </ul>
            <div id="tabs-1">
                <div class="row-two-column">
                    <div class="col">
                        <div class="cell-title">
                            <label><?php _e('Resource Title') ?> (<?php _e('Chinese') ?>)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="resource-name-cn" id="resource-name-cn" class="my-input"
                                   value="<?php echo get_post_meta($post->ID, '_resource_name_cn', true) ?>" />
                        </div>
                    </div>
                </div>
                <div class="row-one-column">
                    <div class="cell-title">
                        <label><?php _e('Resource Content') ?> (<?php _e('Chinese') ?>)</label>
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
                            <label><?php _e('Resource Title') ?> (<?php _e('Vietnamese') ?>)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="resource-name-vn" id="resource-name-vn" class="my-input"
                                   value="<?php echo get_post_meta($post->ID, '_resource_name_vn', true) ?>" />
                        </div>
                    </div>
                </div>
                <div class="row-one-column">
                    <div class="cell-title">
                        <label><?php _e('Resource Content') ?> (<?php _e('Vietnamese') ?>)</label>
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
                            <label><?php _e('Resource Title') ?> (<?php _e('English') ?>)</label>
                        </div>
                        <div class="cell-text">
                            <input type="text" name="resource-name-en" id="resource-name-en" class="my-input"
                                   value="<?php echo get_post_meta($post->ID, '_resource_name_en', true) ?>" />
                        </div>
                    </div>
                </div>
                <div class="row-one-column">
                    <div class="cell-title">
                        <label><?php _e('Resource Content') ?> (<?php _e('English') ?>)</label>
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
            $wpdb->update($wpdb->posts, array('post_title' => $_POST['resource-name-cn']), array('ID' => $post_id));
            update_post_meta($post_id, '_resource_name_cn', $_POST['resource-name-cn']);
        }

        if (!empty($_POST['resource-content-cn'])) {
            update_post_meta($post_id, '_resource_content_cn', $_POST['resource-content-cn']);
        }



        if (!empty($_POST['resource-name-vn'])) {
            update_post_meta($post_id, '_resource_name_vn', $_POST['resource-name-vn']);
        }

        if (!empty($_POST['resource-content-cn'])) {
            update_post_meta($post_id, '_resource_content_vn', $_POST['resource-content-vn']);
        }



        if (!empty($_POST['resource-name-en'])) {
            update_post_meta($post_id, '_resource_name_en', $_POST['resource-name-en']);
        }

        if (!empty($_POST['resource-content-en'])) {
            update_post_meta($post_id, '_resource_content_en', $_POST['resource-content-en']);
        }
    }

}
