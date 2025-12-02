<?php

namespace Result_Spark_Engine\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode class
 */
class Shortcode
{
    /**
     * Initialize class
     */
    public function __construct()
    {
        add_shortcode('rse_shortcode', [$this, 'rse_shortcode']);
        add_shortcode('rse_enquiry', [$this, 'rse_enquiry']);
    }

    /**
     * Shortcode
     *
     * @param array $atts
     * @param string $content
     * @return string
     */
    public function rse_shortcode($atts, $content = null)
    {
        wp_enqueue_script('rse-script');
        wp_enqueue_style('rse-style');

        ob_start();

        include __DIR__ . '/views/shortcode.php';

        return ob_get_clean();
    }

    /**
     * Shortcode
     *
     * @param array $atts
     * @param string $content
     * @return string
     */
    public function rse_enquiry($atts, $content = null)
    {
        wp_enqueue_script('rse-enquiry-script');
        wp_enqueue_style('rse-style');

        // wp_localize_script('rse-enquiry-script', 'rse_data', [
        //     'ajax_url' => admin_url('admin-ajax.php'),
        //     'message' => __('Message from enquiry form', 'result-spark-engine'),
        // ]);

        ob_start();

        include __DIR__ . '/views/enquiry.php';

        return ob_get_clean();
    }
}
