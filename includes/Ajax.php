<?php

namespace Result_Spark_Engine;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajax class
 */
class RSE_Ajax
{
    /**
     * Initialize ajax class
     */
    public function __construct()
    {
        add_action('wp_ajax_rse_enquiry', [$this, 'rse_enquiry']);
        add_action('wp_ajax_nopriv_rse_enquiry', [$this, 'rse_enquiry']);
    }

    /**
     * Perform enquiry operation
     *
     * @return array
     */
    public function rse_enquiry()
    {
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'rse-enquiry-form')) {
            wp_send_json_error([
                'message' => __('Nonce verification failed!', 'result-spark-engine')
            ]);
        }

        wp_send_json_success([
            'message' => __('Perform your operation', 'result-spark-engine'),
            'data'    => $_REQUEST,
        ]);
    }
}
