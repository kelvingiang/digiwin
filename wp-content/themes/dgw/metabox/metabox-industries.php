<?php

class Metabox_Industries
{

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create()
    {
        $id = 'admin-metabox-service';
        $title = __('Industries');
        $callback = array($this, 'display');
        add_meta_box($id, $title, $callback, array('industries',));
    }

    public function display($post)
    {
        $action = 'admin-metabox-data';
        $name = 'admin-metabox-data-nonce';
        wp_nonce_field($action, $name);
?>

        <div class="clear"></div>
        <div class="row-one-column">
            <div class="cell-title">
                <label><?php _e('管理挑戰') ?></label>
            </div>
            <div class="cell-text">
                <?php wp_editor(get_post_meta($post->ID, '_industry_challenge', true), 'txt-challenge', array('wpautop' => false, 'editor_height' => '300px')) ?>
            </div>
        </div>

        <div class="row-one-column">
            <div class="cell-title">
                <label><?php _e('解決方案') ?></label>
            </div>
            <div class="cell-text">
                <?php wp_editor(get_post_meta($post->ID, '_industry_solution', true), 'txt-solution', array('wpautop' => false, 'editor_height' => '300px')) ?>
            </div>
        </div>


<?php
    }

    public function save($post_id)
    {

        // $wpdb->update($wpdb->posts, array('post_title' => $_POST['txt-name-cn']), array('ID' => $post_id));
        // kiem thanh phan an bao mat cua wp
        // 4 BON PHAN TREN DUNG DE BAO MAT KHI LUU METABOX TRONG WP 

        update_post_meta($post_id, '_industry_challenge', $_POST['txt-challenge']);
        update_post_meta($post_id, '_industry_solution', $_POST['txt-solution']);
    }
}
