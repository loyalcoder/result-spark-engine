<?php

namespace Result_Spark_Engine\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use Result_Spark_Engine\Traits\Test;

/**
 * Handle settings
 */
class Settings
{
    use Test;

    /**
     * Setting page template handle
     *
     * @return void
     */
    public function settings_page()
    {
        $template = __DIR__ . '/views/settings.php';

        if (file_exists($template)) {
            include $template;
        }
    }

    /**
     * Mark Entry page handler
     *
     * @return void
     */
    public function mark_entry_page()
    {
        $template = __DIR__ . '/views/mark-entry.php';

        if (file_exists($template)) {
            include $template;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__('Mark Entry', 'result-spark-engine') . '</h1><p>' . esc_html__('Mark entry template not found.', 'result-spark-engine') . '</p></div>';
        }
    }

    /**
     * View Results page handler
     *
     * @return void
     */
    public function view_results_page()
    {
        $template = __DIR__ . '/views/view-results.php';

        if (file_exists($template)) {
            include $template;
        } else {
            echo '<div class="wrap"><h1>' . esc_html__('View Results', 'result-spark-engine') . '</h1><p>' . esc_html__('View results template not found.', 'result-spark-engine') . '</p></div>';
        }
    }

    /**
     * Report handler
     *
     * @return void
     */
    public function report_page()
    {
        $template = __DIR__ . '/views/report.php';

        if (file_exists($template)) {
            include $template;
        }
    }
}
