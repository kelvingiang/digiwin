<?php

class Metabox_Solution {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create() {
        $id = 'admin-metabox-solution';
        $title = __('Solutions');
        $callback = array($this, 'display');
        add_meta_box($id, $title, $callback, array('solutions',));
    }

    public function display($post) {
        $action = 'admin-metabox-data';
        $name = 'admin-metabox-data-nonce';
        wp_nonce_field($action, $name);
        ?>
        <div class="clear"></div>
        <div>
            <div class="row-one-column">
                <div class="cell-title">
                    <label><?php _e('Solution Value') ?></label>
                </div>
                <div class="cell-text">
                    <?php wp_editor(get_post_meta($post->ID, '_solution_value', true), 'txt-value', array('wpautop' => false, 'editor_height' => '300px')) ?>
                </div>
            </div>
            <div class="row-one-column">
                <div class="cell-title">
                    <label><?php _e('Solution Features') ?></label>
                </div>
                <div class="cell-text">
                    <?php wp_editor(get_post_meta($post->ID, '_solution_features', true), 'txt-features', array('wpautop' => false, 'editor_height' => '300px')) ?>
                </div>
            </div>
            <div class="row-one-column">
                <div class="cell-title">
                    <label><?php _e('Advisory Services') ?></label>
                </div>
                <div class="cell-text">
                    <?php wp_editor(get_post_meta($post->ID, '_solution_service', true), 'txt-service', array('wpautop' => false, 'editor_height' => '300px')) ?>
                </div>
            </div>
        </div>


        <?php
    }

    public function save($post_id) {

        // kiem thanh phan an bao mat cua wp
        // 4 BON PHAN TREN DUNG DE BAO MAT KHI LUU METABOX TRONG WP
        if (!empty($_POST['txt-value'])) {
            update_post_meta($post_id, '_solution_value', $_POST['txt-value']);
        }

        if (!empty($_POST['txt-features'])) {
            update_post_meta($post_id, '_solution_features', $_POST['txt-features']);
        }

        if (!empty($_POST['txt-service'])) {
            update_post_meta($post_id, '_solution_service', $_POST['txt-service']);
        }
    }

}
