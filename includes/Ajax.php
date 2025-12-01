<?php

namespace Pkun;

/**
 * Ajax class
 */
class Pkun_Ajax
{
    /**
     * Initialize ajax class
     */
    public function __construct()
    {
        add_action('wp_ajax_pkun_enquiry', [$this, 'pkun_enquiry']);
        add_action('wp_ajax_nopriv_pkun_enquiry', [$this, 'pkun_enquiry']);
    }

    /**
     * Perform enquiry operation
     *
     * @return array
     */
    public function pkun_enquiry()
    {
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'pkun-enquiry-form')) {
            wp_send_json_error([
                'message' => __('Nonce verification failed!', 'pkun')
            ]);
        }

        wp_send_json_success([
            'message' => __('Perform your operation', 'pkun'),
            'data'    => $_REQUEST,
        ]);
    }
}
