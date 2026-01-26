<?php

class Controller_Services
{

    public function __construct()
    {
        add_action('init', array($this, 'register_custom_post'));
        add_action('manage_edit-services_columns', array($this, 'manage_columns'));
        add_action('manage_services_posts_custom_column', array($this, 'render_columns'));

        add_filter('manage_edit-services_sortable_columns', array($this, 'sortable_views_column'));
        add_filter('request', array($this, 'sort_views_column'));
    }

    public function register_custom_post()
    {
        $labels = array(
            'name' => __('Services'),
            'singular_name' => __('Services'),
            'add_new' => __('Add New'),
            'add_new_item' => __('Add Item'),
            'edit_item' => __('Edit'),
            'new_item' => __('Add Item'),
            'all_items' => __('All Item'),
            'view_item' => __('View Item'),
            'search_items' => __('Search'),
            'not_found' => __('No slides found.'),
            'not_found_in_trash' => __('No found in Trash.'),
            'parent_item_colon' => '',
            'menu_name' => __('Services')
        );
        $args = array(
            'labels' => $labels,
            'public' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => TRUE,
            'menu_icon' => PART_ICON . 'icon-link.png',
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 7,
            'supports' => array('thumbnail', 'editor', 'title'),
        );
        register_post_type('services', $args);
    }

    //==== QUAN LY COT HIEN THI TRON BANG   
    public function manage_columns($columns)
    {
        unset($columns['create-date']); // an cot ngay mac dinh
        unset($columns['categories']);
        unset($columns['home']);
        unset($columns['language']);
        unset($columns['order']);
        //==== THEM COT VA BAN
        $columns['category'] = __('Category');
        $columns['home'] = __('首頁');
        $columns['language'] = __('Language');
        $columns['order'] = __('Show Order');
        $columns['create-date'] = __('Create Date');
        return $columns;
    }

    //==== HIEN THI NOI DUNG TRONG COT
    public function render_columns($columns)
    {
        global $post;

        switch ($columns) {

            case 'category':
                $terms = wp_get_post_terms($post->ID, 'services_category');
                if (count($terms) > 0) {
                    foreach ($terms as $key => $term) {
                        echo '<a href=' . custom_redirect($term->slug) . '&' . $term->taxonomy . '=' . $term->slug . '>' . $term->name . '</a></br>';
                    }
                }
                break;
        }
    }

    //====== SAP SEP THEO TRINH TU
    public function sortable_views_column($columns)
    {
        $columns['order'] = 'order';
        $columns['create-date'] = 'create-date';
        return $columns;
    }

    public function sort_views_column($vars)
    {
        if (isset($vars['orderby']) && '_metabox_order' == $vars['orderby']) {
            $vars = array_merge(
                $vars,
                array(
                    'meta_key' => '_metabox_order', //Custom field key
                    'orderby' => 'meta_value_num' //Custom field value (number)
                )
            );
        }

        return $vars;
    }
}
