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
     * Current search term for student search filter
     *
     * @var string
     */
    private $current_search_term = '';

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
        add_action('wp_ajax_rse_get_subjects_status', [$this, 'get_subjects_status']);
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
        // Also include subjects that might not have department taxonomy but are associated with students in this department
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

        // If no subjects found with department taxonomy, try to get subjects that are NOT compulsory
        // and might be used by students in this department
        if (empty($subject_posts)) {
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
                        'compare' => 'NOT EXISTS',
                    ],
                ],
            ]);
        }

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
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

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

        // Add search filter if provided
        if (!empty($search)) {
            // Use a custom filter to search in post title, student_name, and roll_no
            // WordPress doesn't natively support OR between s and meta_query, so we use a filter
            add_filter('posts_where', [$this, 'filter_students_search'], 10, 1);
            $this->current_search_term = $search;
            
            // Search in post title
            $args['s'] = $search;
        }

        $students_query = new \WP_Query($args);
        
        // Remove the filter after query to avoid affecting other queries
        if (!empty($search)) {
            remove_filter('posts_where', [$this, 'filter_students_search'], 10);
            $this->current_search_term = '';
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'rse_mark_entry';
        
        // Check if table exists
        $marks_data = [];
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            // Get all marks for this exam and subject in one query
            $student_ids = wp_list_pluck($students_query->posts, 'ID');
            
            if (!empty($student_ids) && $subject_id > 0) {
                $placeholders = implode(',', array_fill(0, count($student_ids), '%d'));
                $prepared_query = $wpdb->prepare(
                    "SELECT student_id, breakdown_marks, remarks 
                     FROM {$table_name} 
                     WHERE student_id IN ($placeholders) AND exam_id = %d AND subject_id = %d",
                    array_merge($student_ids, [$exam_id, $subject_id])
                );
                
                $marks_results = $wpdb->get_results($prepared_query);
                
                if ($marks_results) {
                    foreach ($marks_results as $mark_row) {
                        $marks_data[$mark_row->student_id] = [
                            'breakdown_marks' => !empty($mark_row->breakdown_marks) ? json_decode($mark_row->breakdown_marks, true) : [],
                            'remarks' => $mark_row->remarks,
                        ];
                    }
                }
            }
        }

        $students_data = [];
        foreach ($students_query->posts as $student) {
            $roll_no = get_post_meta($student->ID, 'roll_no', true);
            $student_name = get_post_meta($student->ID, 'student_name', true) ?: $student->post_title;
            
            // Get marks from custom table
            $mark_data = isset($marks_data[$student->ID]) ? $marks_data[$student->ID] : [
                'breakdown_marks' => [],
                'remarks' => '',
            ];

            $thumbnail_id = get_post_thumbnail_id($student->ID);
            $photo_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';

            $students_data[] = [
                'id' => $student->ID,
                'name' => $student_name,
                'roll_no' => $roll_no ?: '-',
                'photo_url' => $photo_url,
                'existing_remarks' => $mark_data['remarks'],
                'existing_breakdown' => $mark_data['breakdown_marks'],
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
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : [];
        $breakdown_marks = isset($_POST['breakdown_marks']) ? $_POST['breakdown_marks'] : [];

        if ($exam_id <= 0 || $subject_id <= 0) {
            wp_send_json_error([
                'message' => esc_html__('Invalid exam or subject ID.', 'result-spark-engine'),
            ]);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'rse_mark_entry';
        
        // Check if table exists, if not create it
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $installer = new \Result_Spark_Engine\Installer();
            $installer->create_tables();
        }
        
        $saved_count = 0;
        $current_time = current_time('mysql');

        // Get all student IDs from breakdown_marks or remarks
        $all_student_ids = array_unique(array_merge(
            array_keys($breakdown_marks),
            array_keys($remarks)
        ));

        foreach ($all_student_ids as $student_id) {
            $student_id = absint($student_id);
            $remark_value = isset($remarks[$student_id]) ? sanitize_text_field($remarks[$student_id]) : '';
            
            // Prepare breakdown marks as JSON
            $breakdown_json = null;
            if (isset($breakdown_marks[$student_id]) && is_array($breakdown_marks[$student_id])) {
                $breakdown_data = [];
                foreach ($breakdown_marks[$student_id] as $index => $breakdown_value) {
                    if (!empty($breakdown_value)) {
                        $breakdown_data[absint($index)] = floatval($breakdown_value);
                    }
                }
                if (!empty($breakdown_data)) {
                    $breakdown_json = wp_json_encode($breakdown_data);
                }
            }

            if ($student_id > 0) {
                // Check if entry already exists
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE student_id = %d AND exam_id = %d AND subject_id = %d",
                    $student_id,
                    $exam_id,
                    $subject_id
                ));

                if ($existing) {
                    // Update existing entry
                    $update_data = [
                        'breakdown_marks' => $breakdown_json,
                        'remarks' => $remark_value,
                        'updated_at' => $current_time,
                    ];
                    
                    $update_result = $wpdb->update(
                        $table_name,
                        $update_data,
                        [
                            'student_id' => $student_id,
                            'exam_id' => $exam_id,
                            'subject_id' => $subject_id,
                        ],
                        ['%s', '%s', '%s'],
                        ['%d', '%d', '%d']
                    );
                    
                    if ($update_result === false && !empty($wpdb->last_error)) {
                        error_log('RSE Mark Entry Update Error: ' . $wpdb->last_error);
                    }
                } else {
                    // Insert new entry
                    if ($breakdown_json !== null || !empty($remark_value)) {
                        $insert_result = $wpdb->insert(
                            $table_name,
                            [
                                'student_id' => $student_id,
                                'exam_id' => $exam_id,
                                'subject_id' => $subject_id,
                                'breakdown_marks' => $breakdown_json,
                                'remarks' => $remark_value,
                                'created_at' => $current_time,
                                'updated_at' => $current_time,
                            ],
                            ['%d', '%d', '%d', '%s', '%s', '%s', '%s']
                        );
                        
                        if ($insert_result === false && !empty($wpdb->last_error)) {
                            error_log('RSE Mark Entry Insert Error: ' . $wpdb->last_error);
                            wp_send_json_error([
                                'message' => esc_html__('Error saving marks: ', 'result-spark-engine') . $wpdb->last_error,
                            ]);
                            return;
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

    /**
     * Get all subjects with mark entry status for an exam
     *
     * @return void
     */
    public function get_subjects_status()
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
                'all_done' => false,
                'message' => esc_html__('No class found for this exam.', 'result-spark-engine'),
            ]);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'rse_mark_entry';

        // Get all students in this exam's class
        $students = get_posts([
            'post_type' => 'students',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => 'class_level',
                    'field' => 'term_id',
                    'terms' => $exam_class_terms,
                ],
            ],
        ]);

        $total_students = count($students);
        $student_ids = $students;

        // Get ALL subjects for this exam's class level (not just compulsory or departmental)
        // This ensures we don't miss any subjects
        $all_subjects = get_posts([
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

        // Remove duplicates (in case there are any)
        $unique_subjects = [];
        $seen_ids = [];
        foreach ($all_subjects as $subject) {
            if (!in_array($subject->ID, $seen_ids)) {
                $unique_subjects[] = $subject;
                $seen_ids[] = $subject->ID;
            }
        }

        $subjects_status = [];
        $all_done = true;

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name && !empty($student_ids)) {
            foreach ($unique_subjects as $subject) {
                // Count how many students have marks for this subject
                // A subject is considered "marked" if breakdown_marks exists and is not empty/null
                $placeholders = implode(',', array_fill(0, count($student_ids), '%d'));
                $marked_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT student_id) 
                     FROM {$table_name} 
                     WHERE student_id IN ($placeholders) AND exam_id = %d AND subject_id = %d 
                     AND breakdown_marks IS NOT NULL 
                     AND breakdown_marks != '' 
                     AND breakdown_marks != 'null'
                     AND breakdown_marks != '[]'
                     AND LENGTH(breakdown_marks) > 2",
                    array_merge($student_ids, [$exam_id, $subject->ID])
                ));

                $is_compulsory = get_post_meta($subject->ID, '_rse_compulsory_subject', true) === '1';
                $subject_depts = wp_get_post_terms($subject->ID, 'department', ['fields' => 'names']);
                $department_name = !empty($subject_depts) ? implode(', ', $subject_depts) : '';
                
                // Determine subject type: If it has department taxonomy, it's departmental (even if also compulsory)
                // Otherwise, if it's marked as compulsory, it's compulsory
                $subject_type = !empty($subject_depts) ? 'departmental' : ($is_compulsory ? 'compulsory' : 'departmental');

                $is_done = ($marked_count >= $total_students && $total_students > 0);
                if (!$is_done) {
                    $all_done = false;
                }

                $subjects_status[] = [
                    'id' => $subject->ID,
                    'name' => $subject->post_title,
                    'type' => $subject_type,
                    'department' => $department_name,
                    'marked_count' => (int) $marked_count,
                    'total_students' => $total_students,
                    'is_done' => $is_done,
                    'progress' => $total_students > 0 ? round(($marked_count / $total_students) * 100, 1) : 0,
                ];
            }
        } else {
            // If no table or no students, mark all as not done
            foreach ($unique_subjects as $subject) {
                $is_compulsory = get_post_meta($subject->ID, '_rse_compulsory_subject', true) === '1';
                $subject_depts = wp_get_post_terms($subject->ID, 'department', ['fields' => 'names']);
                $department_name = !empty($subject_depts) ? implode(', ', $subject_depts) : '';
                
                // Determine subject type: If it has department taxonomy, it's departmental (even if also compulsory)
                // Otherwise, if it's marked as compulsory, it's compulsory
                $subject_type = !empty($subject_depts) ? 'departmental' : ($is_compulsory ? 'compulsory' : 'departmental');

                $subjects_status[] = [
                    'id' => $subject->ID,
                    'name' => $subject->post_title,
                    'type' => $subject_type,
                    'department' => $department_name,
                    'marked_count' => 0,
                    'total_students' => $total_students,
                    'is_done' => false,
                    'progress' => 0,
                ];
                $all_done = false;
            }
        }

        wp_send_json_success([
            'subjects' => $subjects_status,
            'all_done' => $all_done,
            'total_subjects' => count($subjects_status),
            'done_count' => count(array_filter($subjects_status, function($s) { return $s['is_done']; })),
        ]);
    }

    /**
     * Filter students search to include meta fields (student_name and roll_no)
     *
     * @param string $where WHERE clause
     * @return string Modified WHERE clause
     */
    public function filter_students_search($where)
    {
        if (empty($this->current_search_term)) {
            return $where;
        }

        global $wpdb;
        $search_like = '%' . $wpdb->esc_like($this->current_search_term) . '%';
        $where .= $wpdb->prepare(
            " OR (
                EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} pm1 
                    WHERE pm1.post_id = {$wpdb->posts}.ID 
                    AND pm1.meta_key = 'student_name' 
                    AND pm1.meta_value LIKE %s
                )
            ) OR (
                EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} pm2 
                    WHERE pm2.post_id = {$wpdb->posts}.ID 
                    AND pm2.meta_key = 'roll_no' 
                    AND pm2.meta_value LIKE %s
                )
            )",
            $search_like,
            $search_like
        );
        return $where;
    }
}
