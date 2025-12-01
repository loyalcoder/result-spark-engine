<?php

namespace Pkun;

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
     * Pkun scripts
     *
     * @return array
     */
    public function get_scripts()
    {
        return [
            'pkun-script' => [
                'src'     => PKUN_ASSETS . '/js/frontend.js',
                'version' => filemtime(PKUN_PATH . '/assets/js/frontend.js'),
                'deps'    => ['jquery']
            ],
            'pkun-enquiry-script' => [
                'src'     => PKUN_ASSETS . '/js/enquiry.js',
                'version' => filemtime(PKUN_PATH . '/assets/js/enquiry.js'),
                'deps'    => ['jquery']
            ]
        ];
    }

    /**
     * Pkun styles
     *
     * @return array
     */
    public function get_styles()
    {
        return [
            'pkun-style' => [
                'src'     => PKUN_ASSETS . '/css/frontend.css',
                'version' => filemtime(PKUN_PATH . '/assets/css/frontend.css'),
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
            $version = isset($script['version']) ? $script['version'] : PKUN_VERSION;

            wp_register_script($handle, $script['src'], $deps, $version, true);
        }

        wp_localize_script('pkun-enquiry-script', 'pkun_data', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'message' => __('Message from enquiry form', 'pkun'),
        ]);

        foreach ($styles as $handle => $style) {
            $deps = isset($style['deps']) ? $style['deps'] : false;
            $version = isset($style['version']) ? $style['version'] : PKUN_VERSION;

            wp_register_style($handle, $style['src'], $deps, $version);
        }
    }
}
