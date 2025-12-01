<?php

namespace Pkun\Frontend;

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
        add_shortcode('pkun_shortcode', [$this, 'pkun_shortcode']);
        add_shortcode('pkun_enquiry', [$this, 'pkun_enquiry']);
    }

    /**
     * Shortcode
     *
     * @param array $atts
     * @param string $content
     * @return string
     */
    public function pkun_shortcode($atts, $content = null)
    {
        wp_enqueue_script('pkun-script');
        wp_enqueue_style('pkun-style');

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
    public function pkun_enquiry($atts, $content = null)
    {
        wp_enqueue_script('pkun-enquiry-script');
        wp_enqueue_style('pkun-style');

        // wp_localize_script('pkun-enquiry-script', 'pkun_data', [
        //     'ajax_url' => admin_url('admin-ajax.php'),
        //     'message' => __('Message from enquiry form', 'pkun'),
        // ]);

        ob_start();

        include __DIR__ . '/views/enquiry.php';

        return ob_get_clean();
    }
}
