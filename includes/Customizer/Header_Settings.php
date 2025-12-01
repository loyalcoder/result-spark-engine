<?php

namespace Pkun\Customizer;

use Kirki;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Header_Settings
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->initHeaderSettings();
        $this->HeaderSettings();
    }

    /**
     * initHeaderSettings
     *
     * @return void
     */
    public function initHeaderSettings()
    {
        Kirki::add_section('chawkbazar_header_section', [
            'title'       => esc_html__('Header', 'pkun'),
            'description' => esc_html__('Global settings for header located here', 'pkun'),
            'panel'       => 'pkun_config_panel',
            'priority'    => 160,
        ]);
    }

    /**
     * HeaderSettings
     *
     * @return void
     */
    public function HeaderSettings()
    { // section choosing key : chawkbazar_header_section

        Kirki::add_field('pkun_config', [
            'type'        => 'image',
            'settings'    => 'pkun_header_logo',
            'label'       => esc_html__('Main Logo', 'pkun'),
            'section'     => 'chawkbazar_header_section',
            'default'     => '',
        ]);
    }
}
