<?php

namespace Result_Spark_Engine;

if (!defined('ABSPATH')) {
    exit;
}

class Installer
{
    /**
     * Initialize class functions
     *
     * @return void
     */
    public function run()
    {
        $this->add_version();
        $this->create_tables();
    }

    /**
     * Store plugin information
     *
     * @return void
     */
    public function add_version()
    {
        $installed = get_option('rse_installed');

        if (!$installed) {
            update_option('rse_installed', time());
        }

        update_option('rse_version', RSE_VERSION);
    }

    /**
     * Create custom tables
     *
     * @return void
     */
    public function create_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $checkout_scheme = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}rse_boilerplate` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(250) DEFAULT NULL,
            `value` varchar(250) DEFAULT NULL,
            `create_at` datetime NOT NULL,
            PRIMARY KEY (`id`)
          ) $charset_collate";

        dbDelta($checkout_scheme);

        // Create mark_entry table
        $mark_entry_scheme = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}rse_mark_entry` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `student_id` bigint(20) unsigned NOT NULL,
            `exam_id` bigint(20) unsigned NOT NULL,
            `subject_id` bigint(20) unsigned NOT NULL,
            `breakdown_marks` longtext DEFAULT NULL COMMENT 'JSON encoded breakdown marks',
            `remarks` text DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_entry` (`student_id`, `exam_id`, `subject_id`),
            KEY `student_id` (`student_id`),
            KEY `exam_id` (`exam_id`),
            KEY `subject_id` (`subject_id`),
            KEY `exam_subject` (`exam_id`, `subject_id`)
          ) $charset_collate";

        dbDelta($mark_entry_scheme);
        
        // Migrate: Remove total_mark column if it exists (for existing installations)
        $this->migrate_remove_total_mark();

        // Create result table
        $result_scheme = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}rse_result` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `student_id` bigint(20) unsigned NOT NULL,
            `roll` varchar(50) DEFAULT NULL,
            `total_mark` decimal(10,2) DEFAULT 0.00,
            `grade_point` decimal(5,2) DEFAULT 0.00,
            `exam_id` bigint(20) unsigned NOT NULL,
            `grade` varchar(10) DEFAULT NULL,
            `mark_in_details` longtext DEFAULT NULL COMMENT 'JSON encoded subject-wise marks',
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_result` (`student_id`, `exam_id`),
            KEY `student_id` (`student_id`),
            KEY `exam_id` (`exam_id`),
            KEY `roll` (`roll`)
          ) $charset_collate";

        dbDelta($result_scheme);
    }
    
    /**
     * Migrate: Remove total_mark column from existing tables
     *
     * @return void
     */
    private function migrate_remove_total_mark()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rse_mark_entry';
        
        // Check if table exists and has total_mark column
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s 
             AND TABLE_NAME = %s 
             AND COLUMN_NAME = 'total_mark'",
            DB_NAME,
            $table_name
        ));
        
        if (!empty($column_exists)) {
            // Drop the column
            $wpdb->query("ALTER TABLE `{$table_name}` DROP COLUMN `total_mark`");
        }
    }
}
