<?php

namespace Result_Spark_Engine\Traits;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Post Type and Taxonomy Trait
 * 
 * Easy way to register custom post types and taxonomies
 * Just provide the name and it will auto-generate labels and register
 */
trait Post_Type_Taxonomy
{
    /**
     * Register a custom post type
     * 
     * @param string $post_type Post type name (e.g., 'book', 'product')
     * @param array $args Optional arguments to override defaults
     * @return void
     */
    public function register_post_type($post_type, $args = [])
    {
        $singular = $this->get_singular_name($post_type);
        $plural = $this->get_plural_name($post_type);

        $labels = [
            'name'                  => $plural,
            'singular_name'         => $singular,
            'menu_name'             => $plural,
            'name_admin_bar'        => $singular,
            'add_new'               => esc_html__('Add New', 'result-spark-engine'),
            'add_new_item'          => sprintf(esc_html__('Add New %s', 'result-spark-engine'), $singular),
            'new_item'              => sprintf(esc_html__('New %s', 'result-spark-engine'), $singular),
            'edit_item'             => sprintf(esc_html__('Edit %s', 'result-spark-engine'), $singular),
            'view_item'             => sprintf(esc_html__('View %s', 'result-spark-engine'), $singular),
            'all_items'             => sprintf(esc_html__('All %s', 'result-spark-engine'), $plural),
            'search_items'          => sprintf(esc_html__('Search %s', 'result-spark-engine'), $plural),
            'parent_item_colon'     => sprintf(esc_html__('Parent %s:', 'result-spark-engine'), $plural),
            'not_found'             => sprintf(esc_html__('No %s found', 'result-spark-engine'), strtolower($plural)),
            'not_found_in_trash'    => sprintf(esc_html__('No %s found in Trash', 'result-spark-engine'), strtolower($plural)),
            'featured_image'        => sprintf(esc_html__('%s Featured Image', 'result-spark-engine'), $singular),
            'set_featured_image'    => sprintf(esc_html__('Set %s featured image', 'result-spark-engine'), strtolower($singular)),
            'remove_featured_image' => sprintf(esc_html__('Remove %s featured image', 'result-spark-engine'), strtolower($singular)),
            'use_featured_image'    => sprintf(esc_html__('Use as %s featured image', 'result-spark-engine'), strtolower($singular)),
        ];

        $defaults = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => $post_type],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-admin-post',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest'       => true,
        ];

        $args = wp_parse_args($args, $defaults);

        register_post_type($post_type, $args);
    }

    /**
     * Register a custom taxonomy
     * 
     * @param string $taxonomy Taxonomy name (e.g., 'genre', 'category')
     * @param string|array $post_types Post type(s) to attach this taxonomy to
     * @param array $args Optional arguments to override defaults
     * @return void
     */
    public function register_taxonomy($taxonomy, $post_types = [], $args = [])
    {
        $singular = $this->get_singular_name($taxonomy);
        $plural = $this->get_plural_name($taxonomy);

        $labels = [
            'name'                       => $plural,
            'singular_name'              => $singular,
            'menu_name'                  => $plural,
            'all_items'                  => sprintf(esc_html__('All %s', 'result-spark-engine'), $plural),
            'parent_item'                => sprintf(esc_html__('Parent %s', 'result-spark-engine'), $singular),
            'parent_item_colon'          => sprintf(esc_html__('Parent %s:', 'result-spark-engine'), $singular),
            'new_item_name'              => sprintf(esc_html__('New %s Name', 'result-spark-engine'), $singular),
            'add_new_item'               => sprintf(esc_html__('Add New %s', 'result-spark-engine'), $singular),
            'edit_item'                  => sprintf(esc_html__('Edit %s', 'result-spark-engine'), $singular),
            'update_item'                => sprintf(esc_html__('Update %s', 'result-spark-engine'), $singular),
            'view_item'                  => sprintf(esc_html__('View %s', 'result-spark-engine'), $singular),
            'separate_items_with_commas' => sprintf(esc_html__('Separate %s with commas', 'result-spark-engine'), strtolower($plural)),
            'add_or_remove_items'        => sprintf(esc_html__('Add or remove %s', 'result-spark-engine'), strtolower($plural)),
            'choose_from_most_used'      => sprintf(esc_html__('Choose from the most used %s', 'result-spark-engine'), strtolower($plural)),
            'popular_items'              => sprintf(esc_html__('Popular %s', 'result-spark-engine'), $plural),
            'search_items'               => sprintf(esc_html__('Search %s', 'result-spark-engine'), $plural),
            'not_found'                  => sprintf(esc_html__('Not Found %s', 'result-spark-engine'), $singular),
            'no_terms'                   => sprintf(esc_html__('No %s', 'result-spark-engine'), strtolower($plural)),
            'items_list'                 => sprintf(esc_html__('%s list', 'result-spark-engine'), $plural),
            'items_list_navigation'      => sprintf(esc_html__('%s list navigation', 'result-spark-engine'), $plural),
        ];

        $defaults = [
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
            'rewrite'           => ['slug' => $taxonomy],
            'show_in_rest'      => true,
        ];

        $args = wp_parse_args($args, $defaults);

        // If post_types is a string, convert to array
        if (is_string($post_types)) {
            $post_types = [$post_types];
        }

        // If post_types is empty, don't register
        if (empty($post_types)) {
            return;
        }

        register_taxonomy($taxonomy, $post_types, $args);
    }

    /**
     * Get singular name from post type/taxonomy name
     * 
     * @param string $name Post type or taxonomy name
     * @return string Singular name
     */
    private function get_singular_name($name)
    {
        // Remove underscores and hyphens, capitalize first letter
        $name = str_replace(['_', '-'], ' ', $name);
        $name = ucwords($name);
        
        // Handle common plural endings
        if (substr($name, -1) === 's') {
            // Remove trailing 's' for singular
            $name = rtrim($name, 's');
        }
        
        return $name;
    }

    /**
     * Get plural name from post type/taxonomy name
     * 
     * @param string $name Post type or taxonomy name
     * @return string Plural name
     */
    private function get_plural_name($name)
    {
        $singular = $this->get_singular_name($name);
        
        // Simple pluralization rules
        $last_char = substr($singular, -1);
        $last_two_chars = substr($singular, -2);
        
        if ($last_char === 'y' && !in_array($last_two_chars, ['ay', 'ey', 'iy', 'oy', 'uy'])) {
            // Replace 'y' with 'ies'
            return substr($singular, 0, -1) . 'ies';
        } elseif (in_array($last_char, ['s', 'x', 'z', 'ch', 'sh'])) {
            // Add 'es'
            return $singular . 'es';
        } else {
            // Just add 's'
            return $singular . 's';
        }
    }
}

