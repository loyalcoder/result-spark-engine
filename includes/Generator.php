<?php

namespace Result_Spark_Engine;

if (!defined('ABSPATH')) {
    exit;
}

use Result_Spark_Engine\Traits\Post_Type_Taxonomy;

/**
 * Generator class
 *
 * @description: Easy way to register custom post types and taxonomies.
 * Just use the trait methods with the name and it will auto-generate everything.
 */
class Generator
{
    use Post_Type_Taxonomy;

    /**
     * Class initialize
     */
    function __construct()
    {
        add_action('init', [$this, 'init_generator']);
    }

    /**
     * Initialize generator - Register your post types and taxonomies here
     */
    public function init_generator()
    {
        // Register students post type
        $this->register_post_type('students');
        $this->register_post_type('exam');
        
        // Register subject post type
        $this->register_post_type('subject', [
            'menu_icon' => 'dashicons-book-alt',
        ]);
        $this->register_post_type('form_builder');

        // Register taxonomies for students
        $this->register_taxonomy('class_level', ['students', 'exam']);
        $this->register_taxonomy('session', ['students', 'exam']);
        $this->register_taxonomy('academic_year', ['students', 'exam']);
        $this->register_taxonomy('department', 'students');
        $this->register_taxonomy('section', 'students');
        $this->register_taxonomy('shift', 'students');
        
        // Subject and grade taxonomies - moved to subject post type only
        $this->register_taxonomy('subject', 'subject');
        $this->register_taxonomy('grade', 'subject');
    }
}
