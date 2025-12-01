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
}
