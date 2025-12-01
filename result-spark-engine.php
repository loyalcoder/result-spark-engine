<?php

/**
 * Plugin Name:       Result Spark Engine
 * Plugin URI:        https://marlink-checkout.com
 * Description:       Result Spark Engine - WordPress Plugin
 * Version:           1.0.0
 * Author:            WildRain
 * Author URI:        https://wildrain.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       result-spark-engine
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main plugin class
 */
final class Result_Spark_Engine
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
     * @return \Result_Spark_Engine
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
        define('RSE_VERSION', self::version);
        define('RSE_FILE', __FILE__);
        define('RSE_PATH', __DIR__);
        define('RSE_URL', plugins_url('', RSE_FILE));
        define('RSE_ASSETS', RSE_URL . '/assets');
        define('RSE_DIR_PATH', plugin_dir_path(__FILE__));
    }

    /**
     * Plugin information
     *
     * @return void
     */
    public function activate()
    {
        $installer = new Result_Spark_Engine\Installer();

        $installer->run();
    }

    /**
     * Load plugin files
     *
     * @return void
     */
    public function init_plugin()
    {
        new Result_Spark_Engine\Assets();
        new Result_Spark_Engine\RSE_Ajax();
        new Result_Spark_Engine\Generator();
        if (is_admin()) {
            new Result_Spark_Engine\Admin();
        } else {
            new Result_Spark_Engine\Frontend();
        }
    }
}

/**
 * Initialize main plugin
 *
 * @return \Result_Spark_Engine
 */
function result_spark_engine()
{
    return Result_Spark_Engine::init();
}

result_spark_engine();

