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
        add_action('wp_ajax_rse_get_compulsory_subjects', [$this, 'get_compulsory_subjects']);
        add_action('wp_ajax_rse_get_departments', [$this, 'get_departments']);
        add_action('wp_ajax_rse_get_departmental_subjects', [$this, 'get_departmental_subjects']);
        add_action('wp_ajax_rse_get_students_for_mark_entry', [$this, 'get_students_for_mark_entry']);
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
     * Get compulsory subjects for exam
     *
     * @return void
     */
    public function get_compulsory_subjects()
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

        // Get compulsory subjects (with _rse_compulsory_subject = '1') from exam's class
        // NO department taxonomy involved
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
            'meta_query' => [
                [
                    'key' => '_rse_compulsory_subject',
                    'value' => '1',
                    'compare' => '=',
                ],
            ],
        ]);

        $subjects_data = [];
        foreach ($subject_posts as $subject) {
            // Get mark breakdown for this subject
            $mark_breakdown = get_post_meta($subject->ID, '_rse_mark_breakdown', true);
            if (!is_array($mark_breakdown)) {
                $mark_breakdown = [];
            }

            $subjects_data[] = [
                'id' => $subject->ID,
                'name' => $subject->post_title,
                'breakdown' => $mark_breakdown,
            ];
        }

        wp_send_json_success([
            'subjects' => $subjects_data,
        ]);
    }

    /**
     * Get departments for exam
     *
     * @return void
     */
    public function get_departments()
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
                'departments' => [],
                'message' => esc_html__('No class found for this exam.', 'result-spark-engine'),
            ]);
        }

        // Get departments from students in this class
        $students = get_posts([
            'post_type' => 'students',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'class_level',
                    'field' => 'term_id',
                    'terms' => $exam_class_terms,
                ],
            ],
        ]);

        $department_ids = [];
        foreach ($students as $student) {
            $student_depts = wp_get_post_terms($student->ID, 'department', ['fields' => 'ids']);
            $department_ids = array_merge($department_ids, $student_depts);
        }

        $department_ids = array_unique($department_ids);

        $departments_data = [];
        foreach ($department_ids as $dept_id) {
            $dept = get_term($dept_id, 'department');
            if ($dept && !is_wp_error($dept)) {
                $departments_data[] = [
                    'id' => $dept->term_id,
                    'name' => $dept->name,
                ];
            }
        }

        wp_send_json_success([
            'departments' => $departments_data,
        ]);
    }

    /**
     * Get departmental subjects for exam and department
     *
     * @return void
     */
    public function get_departmental_subjects()
    {
        check_ajax_referer('rse_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => esc_html__('You do not have permission to perform this action.', 'result-spark-engine'),
            ]);
        }

        $exam_id = isset($_POST['exam_id']) ? absint($_POST['exam_id']) : 0;
        $department_id = isset($_POST['department_id']) ? absint($_POST['department_id']) : 0;

        if ($exam_id <= 0 || $department_id <= 0) {
            wp_send_json_error([
                'message' => esc_html__('Invalid exam or department ID.', 'result-spark-engine'),
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

        // Get subjects with this class level and department
        $subject_posts = get_posts([
            'post_type' => 'subject',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'tax_query' => [
                'relation' => 'AND',
                [
                    'taxonomy' => 'class_level',
                    'field' => 'term_id',
                    'terms' => $exam_class_terms,
                ],
                [
                    'taxonomy' => 'department',
                    'field' => 'term_id',
                    'terms' => $department_id,
                ],
            ],
        ]);

        $subjects_data = [];
        foreach ($subject_posts as $subject) {
            // Get mark breakdown for this subject
            $mark_breakdown = get_post_meta($subject->ID, '_rse_mark_breakdown', true);
            if (!is_array($mark_breakdown)) {
                $mark_breakdown = [];
            }

            $subjects_data[] = [
                'id' => $subject->ID,
                'name' => $subject->post_title,
                'breakdown' => $mark_breakdown,
            ];
        }

        wp_send_json_success([
            'subjects' => $subjects_data,
        ]);
    }

    /**
     * Get students for mark entry with pagination
     *
     * @return void
     */
    public function get_students_for_mark_entry()
    {
        check_ajax_referer('rse_dashboard_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => esc_html__('You do not have permission to perform this action.', 'result-spark-engine'),
            ]);
        }

        $exam_id = isset($_POST['exam_id']) ? absint($_POST['exam_id']) : 0;
        $subject_id = isset($_POST['subject_id']) ? absint($_POST['subject_id']) : 0;
        $department_id = isset($_POST['department_id']) ? absint($_POST['department_id']) : 0;
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 20;

        if ($exam_id <= 0) {
            wp_send_json_error([
                'message' => esc_html__('Invalid exam ID.', 'result-spark-engine'),
            ]);
        }

        // Get exam's class level
        $exam_class_terms = wp_get_post_terms($exam_id, 'class_level', ['fields' => 'ids']);

        if (empty($exam_class_terms)) {
            wp_send_json_success([
                'students' => [],
                'total' => 0,
                'pages' => 0,
                'current_page' => 1,
            ]);
        }

        // Build query args
        $args = [
            'post_type' => 'students',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'title',
            'order' => 'ASC',
            'tax_query' => [
                [
                    'taxonomy' => 'class_level',
                    'field' => 'term_id',
                    'terms' => $exam_class_terms,
                ],
            ],
        ];

        // Add department filter if provided
        if ($department_id > 0) {
            $args['tax_query'][] = [
                'taxonomy' => 'department',
                'field' => 'term_id',
                'terms' => $department_id,
            ];
            $args['tax_query']['relation'] = 'AND';
        }

        $students_query = new \WP_Query($args);

        $students_data = [];
        $marks_key = '_rse_marks_' . $exam_id . '_' . $subject_id;

        foreach ($students_query->posts as $student) {
            $roll_no = get_post_meta($student->ID, 'roll_no', true);
            $student_name = get_post_meta($student->ID, 'student_name', true) ?: $student->post_title;
            $existing_marks = get_post_meta($student->ID, $marks_key, true);
            $existing_remarks = get_post_meta($student->ID, $marks_key . '_remarks', true);
            
            // Get existing breakdown marks
            $existing_breakdown = [];
            if ($subject_id > 0) {
                $mark_breakdown = get_post_meta($subject_id, '_rse_mark_breakdown', true);
                if (is_array($mark_breakdown)) {
                    foreach ($mark_breakdown as $index => $breakdown) {
                        $breakdown_key = $marks_key . '_breakdown_' . $index;
                        $existing_breakdown[$index] = get_post_meta($student->ID, $breakdown_key, true);
                    }
                }
            }

            $thumbnail_id = get_post_thumbnail_id($student->ID);
            $photo_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';

            $students_data[] = [
                'id' => $student->ID,
                'name' => $student_name,
                'roll_no' => $roll_no ?: '-',
                'photo_url' => $photo_url,
                'existing_marks' => $existing_marks,
                'existing_remarks' => $existing_remarks,
                'existing_breakdown' => $existing_breakdown,
            ];
        }

        wp_send_json_success([
            'students' => $students_data,
            'total' => $students_query->found_posts,
            'pages' => $students_query->max_num_pages,
            'current_page' => $page,
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
        $breakdown_marks = isset($_POST['breakdown_marks']) ? $_POST['breakdown_marks'] : [];

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
                // Save total marks
                if (!empty($mark_value)) {
                    update_post_meta($student_id, $marks_key, $mark_value);
                } else {
                    delete_post_meta($student_id, $marks_key);
                }

                // Save remarks
                if (!empty($remark_value)) {
                    update_post_meta($student_id, $marks_key . '_remarks', $remark_value);
                } else {
                    delete_post_meta($student_id, $marks_key . '_remarks');
                }

                // Save breakdown marks
                if (isset($breakdown_marks[$student_id]) && is_array($breakdown_marks[$student_id])) {
                    foreach ($breakdown_marks[$student_id] as $index => $breakdown_value) {
                        $breakdown_key = $marks_key . '_breakdown_' . absint($index);
                        $breakdown_value = sanitize_text_field($breakdown_value);
                        
                        if (!empty($breakdown_value)) {
                            update_post_meta($student_id, $breakdown_key, $breakdown_value);
                        } else {
                            delete_post_meta($student_id, $breakdown_key);
                        }
                    }
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
