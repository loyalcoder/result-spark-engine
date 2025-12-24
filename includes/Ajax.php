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
        
        // Mark Entry AJAX handlers
        add_action('wp_ajax_rse_get_subjects_by_exam', [$this, 'get_subjects_by_exam']);
        add_action('wp_ajax_rse_save_marks', [$this, 'save_marks']);
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
            'message' => esc_html__('Perform your operation', 'result-spark-engine'),
            'data'    => $_REQUEST,
        ]);
    }

    /**
     * Get subjects by exam (based on exam's class)
     *
     * @return void
     */
    public function get_subjects_by_exam()
    {
        check_ajax_referer('rse_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => esc_html__('You do not have permission to perform this action.', 'result-spark-engine'),
            ]);
        }

        $exam_id = isset($_POST['exam_id']) ? absint($_POST['exam_id']) : 0;

        if ($exam_id <= 0) {
            wp_send_json_error([
                'message' => esc_html__('Invalid exam ID.', 'result-spark-engine'),
            ]);
        }

        // Get exam's class level
        $exam_class_terms = wp_get_post_terms($exam_id, 'class_level', ['fields' => 'ids']);

        if (empty($exam_class_terms)) {
            wp_send_json_success([
                'subjects' => [],
                'message' => esc_html__('No class found for this exam.', 'result-spark-engine'),
            ]);
        }

        // Get subject posts that have this class level
        $subject_posts = get_posts([
            'post_type' => 'subject',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'tax_query' => [
                [
                    'taxonomy' => 'class_level',
                    'field' => 'term_id',
                    'terms' => $exam_class_terms,
                ],
            ],
        ]);

        $subjects_data = [];
        foreach ($subject_posts as $subject) {
            $subjects_data[] = [
                'id' => $subject->ID,
                'name' => $subject->post_title,
            ];
        }

        wp_send_json_success([
            'subjects' => $subjects_data,
        ]);
    }

    /**
     * Save marks for students
     *
     * @return void
     */
    public function save_marks()
    {
        // Check nonce - can be from AJAX or form submission
        if (isset($_POST['rse_marks_nonce'])) {
            if (!wp_verify_nonce($_POST['rse_marks_nonce'], 'rse_save_marks')) {
                wp_send_json_error([
                    'message' => esc_html__('Nonce verification failed.', 'result-spark-engine'),
                ]);
            }
        } else {
            check_ajax_referer('rse_dashboard_nonce', 'nonce');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => esc_html__('You do not have permission to perform this action.', 'result-spark-engine'),
            ]);
        }

        $exam_id = isset($_POST['exam_id']) ? absint($_POST['exam_id']) : 0;
        $subject_id = isset($_POST['subject_id']) ? absint($_POST['subject_id']) : 0;
        $marks = isset($_POST['marks']) ? $_POST['marks'] : [];
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : [];

        if ($exam_id <= 0 || $subject_id <= 0) {
            wp_send_json_error([
                'message' => esc_html__('Invalid exam or subject ID.', 'result-spark-engine'),
            ]);
        }

        $saved_count = 0;
        $marks_key = '_rse_marks_' . $exam_id . '_' . $subject_id;

        foreach ($marks as $student_id => $mark_value) {
            $student_id = absint($student_id);
            $mark_value = sanitize_text_field($mark_value);
            $remark_value = isset($remarks[$student_id]) ? sanitize_text_field($remarks[$student_id]) : '';

            if ($student_id > 0) {
                if (!empty($mark_value)) {
                    update_post_meta($student_id, $marks_key, $mark_value);
                } else {
                    delete_post_meta($student_id, $marks_key);
                }

                if (!empty($remark_value)) {
                    update_post_meta($student_id, $marks_key . '_remarks', $remark_value);
                } else {
                    delete_post_meta($student_id, $marks_key . '_remarks');
                }

                $saved_count++;
            }
        }

        wp_send_json_success([
            'message' => sprintf(
                esc_html__('Marks saved successfully for %d student(s).', 'result-spark-engine'),
                $saved_count
            ),
            'saved_count' => $saved_count,
        ]);
    }
}
