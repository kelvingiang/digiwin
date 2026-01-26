<?php

class Metabox_Download {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create() {
        $id = 'admin-metabox-download';
        $title = __('Data Download');
        $callback = array($this, 'display');
        add_meta_box($id, $title, $callback, array('downloads',));
    }

    public function display($post) {
        $action = 'admin-metabox-data';
        $name = 'admin-metabox-data-nonce';
        wp_nonce_field($action, $name);
        ?>

        <div class="clear"></div>

        <div class="row-one-column">
            <div class="cell-title">
                <label><?php _e('Upload File') ?> <i style="margin-left: 3rem; font-size: 0.8rem; color: #ccc"> 目前檔案 ： <?php echo get_post_meta($post->ID, _download_file, true); ?></i></label>
            </div>
            <div class="cell-text" style="margin: 2rem">
                <input type="file" name="file_upload"  id="file_upload" />
            </div>
        </div>
        <?php
    }

    public function save($post_id) {

        global $wpdb;
        // $wpdb->update($wpdb->posts, array('post_title' => $_POST['txt-name-cn']), array('ID' => $post_id));
        // kiem thanh phan an bao mat cua wp
        // 4 BON PHAN TREN DUNG DE BAO MAT KHI LUU METABOX TRONG WP
        $old_file = get_post_meta($post_id, '_download_file', true);
        $upload = uploadFileDownLoad($_FILES, $old_file);

//        if (isset($_POST['language-show'])) {
//            update_post_meta($post_id, '_download_show', serialize($_POST['language-show']));
//        }

        if (!empty($_FILES['file_upload']['name'])) {
            update_post_meta($post_id, '_download_file', $upload);
        }
    }

}
