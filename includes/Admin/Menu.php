<?php

namespace Result_Spark_Engine\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin menu class
 */
class Menu
{
    /**
     * Initialize menu
     */
    function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    /**
     * Handle plugin menu
     *
     * @return void
     */
    public function admin_menu()
    {
        $parent_slug = 'rse-dashboard';
        $capability = 'manage_options';

        add_menu_page(__('Result Spark Engine Dashboard', 'result-spark-engine'), __('Result Spark Engine', 'result-spark-engine'), $capability, $parent_slug, [$this, 'dashboard_page'], 'dashicons-buddicons-groups');
        add_submenu_page($parent_slug, __('Settings', 'result-spark-engine'), __('Settings', 'result-spark-engine'), $capability, $parent_slug, [$this, 'dashboard_page']);
        add_submenu_page($parent_slug, __('Report', 'result-spark-engine'), __('Report', 'result-spark-engine'), $capability, 'rse-report', [$this, 'report_page']);
    }

    /**
     * Handle menu page
     *
     * @return void
     */
    public function dashboard_page()
    {
        $settings = new Settings();
        $settings->settings_page();
    }

    /**
     * Result Spark Engine report page
     *
     * @return void
     */
    public function report_page()
    {
        $settings = new Settings();
        $settings->report_page();
    }
}
