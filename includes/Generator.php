<?php

namespace Result_Spark_Engine;

if (!defined('ABSPATH')) {
    exit;
}

use Result_Spark_Engine\Traits\Post_Type_Taxonomy;

class Generator
{
    use Post_Type_Taxonomy;

    public function __construct()
    {
        add_action('init', [$this, 'init_generator']);

        // Generate shortcode meta on save
        add_action('save_post_form_builder', [$this, 'generate_form_builder_shortcode'], 10, 3);

        // Admin column
        add_filter('manage_form_builder_posts_columns', [$this, 'add_shortcode_column']);
        add_action('manage_form_builder_posts_custom_column', [$this, 'show_shortcode_column'], 10, 2);

        // Register shortcode
        add_shortcode('form_builder', [$this, 'render_form_builder_shortcode']);
    }

    /**
     * Register CPTs & taxonomies
     */
    public function init_generator()
    {
        $this->register_post_type('students');
        $this->register_post_type('exam');
        $this->register_post_type('form_builder');

        $this->register_taxonomy('class_level', ['students', 'exam']);
        $this->register_taxonomy('session', ['students', 'exam']);
        $this->register_taxonomy('academic_year', ['students', 'exam']);
        $this->register_taxonomy('department', 'students');
        $this->register_taxonomy('section', 'students');
        $this->register_taxonomy('shift', 'students');
        $this->register_taxonomy('subject', 'students');
        $this->register_taxonomy('grade', 'students');
    }

    /**
     * Generate shortcode meta
     */
    public function generate_form_builder_shortcode($post_id, $post, $update)
    {
        if ($post->post_status !== 'publish') {
            return;
        }

        update_post_meta(
            $post_id,
            '_form_builder_shortcode',
            '[form_builder id="' . $post_id . '"]'
        );
    }

    /**
     * Admin list column
     */
    public function add_shortcode_column($columns)
    {
        $columns['form_builder_shortcode'] = __('Shortcode', 'result-spark-engine');
        return $columns;
    }

    public function show_shortcode_column($column, $post_id)
    {
        if ($column === 'form_builder_shortcode') {
            echo esc_html(
                get_post_meta($post_id, '_form_builder_shortcode', true) ?: __('No shortcode', 'result-spark-engine')
            );
        }
    }

    /**
     * 🔥 SHORTCODE RENDERER (THIS FIXES GUTENBERG)
     */
    public function render_form_builder_shortcode($atts)
    {
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts);

        $post_id = (int) $atts['id'];

        if (!$post_id) {
            return '';
        }

        $post = get_post($post_id);

        if (!$post || $post->post_status !== 'publish') {
            return '';
        }

        // Prevent infinite loop
        remove_filter('the_content', 'do_shortcode', 11);

        // 🔑 GUTENBERG FIX
        $content = $post->post_content;
        $content = do_blocks($content);
        $content = apply_filters('the_content', $content);

        return $content;
    }
}
