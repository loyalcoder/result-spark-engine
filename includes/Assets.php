<?php

namespace Result_Spark_Engine;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assets class handler
 */
class Assets
{
    /**
     * Initialize assets
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_action('admin_enqueue_scripts', [$this, 'register_admin_assets']);
    }

    /**
     * Result Spark Engine scripts
     *
     * @return array
     */
    public function get_scripts()
    {
        return [
            'rse-script' => [
                'src'     => RSE_ASSETS . '/js/frontend.js',
                'version' => filemtime(RSE_PATH . '/assets/js/frontend.js'),
                'deps'    => ['jquery']
            ],
            'rse-enquiry-script' => [
                'src'     => RSE_ASSETS . '/js/enquiry.js',
                'version' => filemtime(RSE_PATH . '/assets/js/enquiry.js'),
                'deps'    => ['jquery']
            ]
        ];
    }

    /**
     * Result Spark Engine styles
     *
     * @return array
     */
    public function get_styles()
    {
        return [
            'rse-style' => [
                'src'     => RSE_ASSETS . '/css/frontend.css',
                'version' => filemtime(RSE_PATH . '/assets/css/frontend.css'),
            ]
        ];
    }

    /**
     * Register assets
     */
    public function register_assets()
    {
        $scripts = $this->get_scripts();
        $styles = $this->get_styles();

        foreach ($scripts as $handle => $script) {
            $deps = isset($script['deps']) ? $script['deps'] : false;
            $version = isset($script['version']) ? $script['version'] : RSE_VERSION;

            wp_register_script($handle, $script['src'], $deps, $version, true);
        }

        wp_localize_script('rse-enquiry-script', 'rse_data', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'message' => __('Message from enquiry form', 'result-spark-engine'),
        ]);

        foreach ($styles as $handle => $style) {
            $deps = isset($style['deps']) ? $style['deps'] : false;
            $version = isset($style['version']) ? $style['version'] : RSE_VERSION;

            wp_register_style($handle, $style['src'], $deps, $version);
        }
    }

    /**
     * Register admin assets
     *
     * @param string $hook Current admin page hook.
     * @return void
     */
    public function register_admin_assets($hook)
    {
        // Enqueue mark entry assets
        // WordPress generates hook as: {parent_slug}_page_{submenu_slug}
        // For parent 'rse-dashboard' and submenu 'rse-mark-entry', it should be 'rse-dashboard_page_rse-mark-entry'
        if ('rse-dashboard_page_rse-mark-entry' === $hook || 
            (isset($_GET['page']) && $_GET['page'] === 'rse-mark-entry')) {
            wp_enqueue_style(
                'rse-mark-entry',
                RSE_ASSETS . '/css/mark-entry.css',
                [],
                RSE_VERSION
            );
            wp_enqueue_script(
                'rse-mark-entry',
                RSE_ASSETS . '/js/mark-entry.js',
                ['jquery'],
                RSE_VERSION,
                true
            );

            // Check if pro plugin is active
            $is_pro_active = class_exists('Result_Spark_Engine_Pro');

            wp_localize_script(
                'rse-mark-entry',
                'rseMarkEntry',
                [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('rse_dashboard_nonce'),
                    'loading_text' => esc_html__('Loading...', 'result-spark-engine'),
                    'select_required_text' => esc_html__('Please select exam and subject.', 'result-spark-engine'),
                    'is_pro_active' => $is_pro_active,
                ]
            );
        }

        // Enqueue view results assets
        if ('rse-dashboard_page_rse-view-results' === $hook || 
            (isset($_GET['page']) && $_GET['page'] === 'rse-view-results')) {
            wp_enqueue_style(
                'rse-view-results',
                RSE_ASSETS . '/css/view-results.css',
                [],
                RSE_VERSION
            );
            wp_enqueue_script(
                'rse-view-results',
                RSE_ASSETS . '/js/view-results.js',
                ['jquery'],
                RSE_VERSION,
                true
            );

            wp_localize_script(
                'rse-view-results',
                'rseViewResults',
                [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('rse_dashboard_nonce'),
                ]
            );
        }
    }
}
