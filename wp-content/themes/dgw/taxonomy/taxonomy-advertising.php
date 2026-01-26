<?php

class Taxonomy_Advertising {

    private $prefix_name = 'option_category_advertising_';

    public function __construct() {
        add_action('init', array($this, 'create_taxonomy'));

        add_action('advertising_category_add_form_fields', array($this, 'add_form'));
        add_action('advertising_category_edit_form_fields', array($this, 'edit_form'));

        add_filter("manage_edit-advertising_category_columns", array($this, 'category_columns'), 10, 3);
        add_filter("manage_advertising_category_custom_column", array($this, 'category_columns_manage'), 10, 3);

        add_action('create_advertising_category', array($this, 'save_option'));
        add_action('edited_advertising_category', array($this, 'save_option'));
        add_action('delete_advertising_category', array($this, 'delete_option'));
    }

    public function create_taxonomy() {
        $labels = array(
            'name' => __('Area'),
            'singular_name' => __('Area'),
            'search_items' => __('Search Categories'),
            'all_items' => __('Categories'),
            'parent_item' => __('Parent Class'),
            'parent_item_colon' => __('Parent Class'),
            'edit_item' => __('Edit'),
            'update_item' => __('Update'),
            'add_new_item' => __('Add New'),
            'new_item_name' => __('Add New'),
            'menu_name' => __('Area')
        );

        register_taxonomy('advertising_category', 'advertising', array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'taxonomy' => 'category',
            'rewrite' => array(
                'slug' => 'advertising_category',
                'hierarchical' => true,
            )
        ));
    }

    public function add_form() {
        ?>
        <div class="form-field">
            <label for="cate_order"><?php _e('Show Order') ?></label>
            <input  type="text" name="cate_order" id="cate_order" value="" />
        </div>
        <script>
            jQuery('#tag-name').focusout(function () {
                jQuery('#cate_cn').val(jQuery(this).val());
            });
        </script>
        <style>
            .column-name {
                width: 20%;
            }
        </style>
        <?php
    }

    public function edit_form($term) {
        // LAY GIA TRI TRONG OPTION TABLE
        $arr_value = get_option($this->prefix_name . $term->term_id);
        ?>
        <input  type="hidden" name="cate_cn" id="cate_cn" value="<?php echo $arr_value['cate_advertising_cn']; ?>" />

        <tr class="form-field">
            <th scope="row" valign="top">   <label for="cate_en">   <?php _e('Show Order') ?></label> </th>
            <td>    <input type="text" name="cate_order" id="cate_order" value="<?php echo $arr_value['cate_advertising_order']; ?>" /></td>
        </tr>

        <script>
            jQuery('#name').focusout(function () {
                jQuery('#cate_cn').val(jQuery(this).val());
            });
        </script>
        <?php
    }

    public function save_option($term_id) {
        $arr = array(
            'cate_advertising_cn' => $_POST['cate_cn'],
            'cate_advertising_order' => $_POST['cate_order'],
        );
        $option_name = $this->prefix_name . $term_id;
        $option_value = $arr;
        update_option($option_name, $option_value);
    }

    public function delete_option() {
        $param = getParams();
        delete_option($this->prefix_name . $param['tag_ID']);
    }

    public function category_columns() {
        $new_columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Name'),
            'order' => __('Show Order'),
            'slug' => __('Slug'),
            'posts' => __('Count')
        );

        return $new_columns;
    }

    public function category_columns_manage($out, $column_name, $theme_id) {
        $theme = get_term($theme_id, 'advertising_category');

        $strOption = get_option($this->prefix_name . $theme->term_id);


        switch ($column_name) {
            case 'order':
                echo isset($strOption['cate_advertising_order']) ? $strOption['cate_advertising_order'] : '0';
                break;
            default:
                break;
        }
        return $out;
    }

}
