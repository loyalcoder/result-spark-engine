<?php

/**
 * Plugin Name:       Pkun - Boilerplate for WordPress Plugin
 * Plugin URI:        https://marlink-checkout.com
 * Description:       Boilerplate for WordPress Plugin
 * Version:           1.0.0
 * Author:            WildRain
 * Author URI:        https://wildrain.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pkun
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main plugin class
 */
final class Pkun
{
    /**
     * Plugin version
     * 
     * @var string
     */
    const version = '1.0.0';

    /**
     * contractor
     */
    private function __construct()
    {
        $this->define_constants();

        register_activation_hook(__FILE__, [$this, 'activate']);
        add_action('plugins_loaded', [$this, 'init_plugin']);
    }

    /**
     * Initialize singleton instance
     *
     * @return \Pkun
     */
    public static function init()
    {
        static $instance = false;

        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Define constants
     *
     * @return void
     */
    public function define_constants()
    {
        define('PKUN_VERSION', self::version);
        define('PKUN_FILE', __FILE__);
        define('PKUN_PATH', __DIR__);
        define('PKUN_URL', plugins_url('', PKUN_FILE));
        define('PKUN_ASSETS', PKUN_URL . '/assets');
        define('PKUN_DIR_PATH', plugin_dir_path(__FILE__));
        define('PKUN_ELEMENTOR', PKUN_DIR_PATH . 'includes/Elementor/');
    }

    /**
     * Plugin information
     *
     * @return void
     */
    public function activate()
    {
        $installer = new Pkun\Installer();

        $installer->run();
    }

    /**
     * Load plugin files
     *
     * @return void
     */
    public function init_plugin()
    {
        new Pkun\Assets();
        new Pkun\Pkun_Ajax();
        new Pkun\Load_Elementor();
        new Pkun\Generator();
        new Pkun\Customizer();
        if (is_admin()) {
            new Pkun\Admin();
        } else {
            new Pkun\Frontend();
        }
    }
}

/**
 * Initialize main plugin
 *
 * @return \Pkun
 */
function pkun()
{
    return Pkun::init();
}

pkun();
