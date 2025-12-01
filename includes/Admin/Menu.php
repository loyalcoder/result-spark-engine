<?php

namespace Pkun\Admin;

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
        $parent_slug = 'pkun-dashboard';
        $capability = 'manage_options';

        add_menu_page(__('Pkun Dashboard', 'pkun'), __('Pkun', 'pkun'), $capability, $parent_slug, [$this, 'dashboard_page'], 'dashicons-buddicons-groups');
        add_submenu_page($parent_slug, __('Settings', 'pkun'), __('Settings', 'pkun'), $capability, $parent_slug, [$this, 'dashboard_page']);
        add_submenu_page($parent_slug, __('Report', 'pkun'), __('Report', 'pkun'), $capability, 'pkun-report', [$this, 'report_page']);
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
     * Pkun report page
     *
     * @return void
     */
    public function report_page()
    {
        $settings = new Settings();
        $settings->report_page();
    }
}
