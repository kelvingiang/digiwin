<?php

class Tags_Casestudies
{

    private $prefix_name = 'option_casestudies_tag_';

    public function __construct()
    {
        add_action('init', array($this, 'create'));

        add_action('casestudies_tag_add_form_fields', array($this, 'add_form'));
        add_action('casestudies_tag_edit_form_fields', array($this, 'edit_form'));

        add_filter("manage_edit-casestudies_tag_columns", array($this, 'category_columns'), 10, 3);
        add_filter("manage_casestudies_tag_custom_column", array($this, 'category_columns_manage'), 10, 3);

        add_action('create_casestudies_tag', array($this, 'save_option'));
        add_action('edited_casestudies_tag', array($this, 'save_option'));
        add_action('delete_casestudies_tag', array($this, 'delete_option'));
    }

    public function create()
    {
        $labels = array(
            'name' => __('Product', 'dgw'),
            'singular_name' => __('Product', 'dgw'),
            'search_items' => __('Search Categories', 'dgw'),
            'all_items' => __('Categories', 'dgw'),
            'parent_item' => __('Parent Class', 'dgw'),
            'parent_item_colon' => __('Parent Class', 'dgw'),
            'edit_item' => __('Edit', 'dgw'),
            'update_item' => __('Update', 'dgw'),
            'add_new_item' => __('Add New', 'dgw'),
            'new_item_name' => __('Add New', 'dgw'),
            'menu_name' => __('Product', 'dgw')
        );

        register_taxonomy('casestudies_tags', 'casestudies', array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'taxonomy' => 'category',
            'rewrite' => array(
                'slug' => 'casestudies_tags',
                'hierarchical' => false,
            )
        ));
    }

    public function add_form()
    {
?>
        <div class="form-field">
            <label for="cate_order"><?php __('Show Order', 'dgw') ?></label>
            <input type="text" name="cate_order" id="cate_order" value="" />
        </div>

    <?php
    }

    public function edit_form($term)
    {
        // LAY GIA TRI TRONG OPTION TABLE
        $arr_value = get_option($this->prefix_name . $term->term_id);
    ?>
        <input type="hidden" name="cate_cn" id="cate_cn" value="<?php echo $arr_value['cate_advertising_cn']; ?>" />

        <tr class="form-field">
            <th scope="row" valign="top"> <label for="cate_en"> <?php __('Show Order', 'dgw') ?></label> </th>
            <td> <input type="text" name="cate_order" id="cate_order" value="<?php echo $arr_value['cate_advertising_order']; ?>" /></td>
        </tr>
<?php
    }

    public function save_option($term_id)
    {
        $arr = array(
            'cate_solution_cn' => sanitize_text_field(wp_unslash($_POST['cate_cn'])),
            'cate_solution_order' => sanitize_text_field(wp_unslash($_POST['cate_order'])),
        );
        $option_name = $this->prefix_name . $term_id;
        $option_value = $arr;
        update_option($option_name, $option_value);
    }

    public function delete_option()
    {
        $param = getParams();
        delete_option($this->prefix_name . $param['tag_ID']);
    }

    public function category_columns()
    {
        $new_columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Name', 'dgw'),
            'order' => __('Show Order', 'dgw'),
            'slug' => __('Slug', 'dgw'),
            'posts' => __('Count', 'dgw')
        );

        return $new_columns;
    }

    public function category_columns_manage($out, $column_name, $theme_id)
    {
        $theme = get_term($theme_id, 'solution_category');

        $strOption = get_option($this->prefix_name . $theme->term_id);


        switch ($column_name) {
            case 'order':
                echo isset($strOption['cate_order']) ? $strOption['cate_order'] : '0';
                break;
            default:
                break;
        }
        return $out;
    }
}
