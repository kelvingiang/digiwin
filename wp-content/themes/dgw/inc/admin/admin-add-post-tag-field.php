<?php
//====ADD NEW FIELD =================================================================

add_action('post_tag_add_form_fields', 'add_term_tag_fields');

function add_term_tag_fields($taxonomy) {
    ?>
    <div class="form-field">
        <label for="tag_vn"> <?php _e('Name') ?> ( <?php _e('Vietnamese') ?>)</label>
        <input  type="hidden" name="tag_cn" id="tag_cn"/>
        <input  type="text" name="tag_vn" id="tag_vn" />
    </div>
    <div class="form-field">
        <label for="tag_en"> <?php _e('Name') ?> ( <?php _e('English') ?>)</label>
        <input type="text" name="tag_en" id="tag_en"/>
    </div>
    <div class="form-field">
        <label for="tag_order"><?php _e('Show Order') ?></label>
        <input  type="text" name="tag_order" id="tag_order" />
    </div>
    <script>
        jQuery('#tag-name').focusout(function () {
            jQuery('#tag_cn').val(jQuery(this).val());
        });
    </script>
    <?php
}

//====EIDT NEW FIELD =================================================================
add_action('post_tag_edit_form_fields', 'edit_term_tag_fields', 10, 2);

function edit_term_tag_fields($term, $taxonomy) {
    $value = get_option('post_tag_' . $term->term_id);
    ?>

    <tr class="form-field">
        <th>
            <label for="cate_vn"> <?php _e('Name') ?> ( <?php _e('Vietnamese') ?>)</label>
        </th>
        <td>
            <input  type="hidden" name="tag_cn" id="tag_cn" value="<?php echo $value['tag_cn'] ?>" />
            <input  type="text" name="tag_vn" id="tag_vn" value="<?php echo $value['tag_vn'] ?>" />
        </td>
    </tr>
    <tr class="form-field">
        <th>
            <label for="cate_en"> <?php _e('Name') ?> ( <?php _e('English') ?>)</label>
        </th>
        <td>
            <input type="text" name="tag_en" id="tag_en" value="<?php echo $value['tag_en'] ?>" />
        </td>
    </tr>
    <tr class="form-field">
        <th>
            <label for="cate_order"><?php _e('Show Order') ?></label>
        </th>
        <td>
            <input  type="text" name="tag_order" id="tag_order" value="<?php echo $value['tag_order'] ?>" />
        </td>
    </tr>
    <script>
        jQuery('#name').focusout(function () {
            jQuery('#tag_cn').val(jQuery(this).val());
        });
    </script>
    <?php
}

//=== SAVE FIELD ================================================================
add_action('created_post_tag', 'save_tag_fields');
add_action('edited_post_tag', 'save_tag_fields');

function save_tag_fields($term_id) {
    $arr = array(
        'tag_cn' => $_POST['tag_cn'],
        'tag_vn' => $_POST['tag_vn'],
        'tag_en' => $_POST['tag_en'],
        'tag_order' => $_POST['tag_order'],
    );
    $option_name = 'post_tag_' . $term_id;
    $option_value = $arr;
    update_option($option_name, $option_value);
}

//=====DETELE TAG  FIELD =========================================================================
add_action('delete_post_tag', 'delete_tag_fields');

function delete_tag_fields() {
    $param = getParams();
    delete_option("post_tag_" . $param['tag_ID']);
}

//===== MANAGE  CATEGORY COLUMNS  ===============================================================
add_filter("manage_edit-post_tag_columns", 'tag_columns', 10, 3);
add_filter("manage_post_tag_custom_column", 'tag_columns_manage', 10, 3);

function tag_columns() {
    $new_columns = array(
        'cb' => '<input type="checkbox" />',
        'name' => __('Name'),
        'slug' => __('Slug'),
//            'description' => __('Description'),
        'vietnamese' => __('Vietnamese'),
        'english' => __('English'),
        'order' => __('Show Order'),
        'posts' => __('Count')
    );

    return $new_columns;
}

function tag_columns_manage($out, $column_name, $theme_id) {
    $theme = get_term($theme_id, 'post_tag');

    $val = get_option('post_tag_' . $theme->term_id);

    switch ($column_name) {
        case 'order':
            echo isset($val['tag_order']) ? $val['tag_order'] : '0';
            break;
        case 'vietnamese':
            echo isset($val['tag_vn']) ? $val['tag_vn'] : '';
            break;
        case 'english':
            echo isset($val['tag_en']) ? $val['tag_en'] : '';
            break;
        default:
            break;
    }
    return $out;
}
