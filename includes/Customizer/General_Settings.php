<?php

namespace Pkun\Customizer;

use Kirki;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class General_Settings
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->init_general_settings();
        $this->general_settings();
    }

    /**
     * init_general_settings
     *
     * @return void
     */
    public function init_general_settings()
    {
        Kirki::add_section('chawkbazar_general_section', [
            'title'       => esc_html__('General', 'pkun'),
            'description' => esc_html__('General theme settings', 'pkun'),
            'panel'       => 'pkun_config_panel',
            'priority'    => 160,
        ]);
    }

    /**
     * general_settings
     *
     * @return void
     */
    public function general_settings()
    {
        // section choosing key : chawkbazar_general_section
        Kirki::add_field('pkun_config', [
            'type'        => 'select',
            'settings'    => 'site_loader',
            'label'       => esc_html__('Site loader', 'pkun'),
            'description' => esc_html__('Choose either site loader is On/Off through out the site', 'pkun'),
            'section'     => 'chawkbazar_general_section',
            'default'     => 'off',
            'priority'    => 10,
            'multiple'    => 1,
            'choices'     => [
                'on' => esc_html__('On', 'pkun'),
                'off' => esc_html__('Off', 'pkun'),
            ],
        ]);
    }
}
