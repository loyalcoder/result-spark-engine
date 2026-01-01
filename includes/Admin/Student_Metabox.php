<?php

namespace Result_Spark_Engine\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Student Metabox Handler
 * 
 * Adds Major Subject and Non Major Subject fields
 */
class Student_Metabox
{
    /**
     * Initialize class
     */
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_students', [$this, 'save_meta'], 10, 3);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Add meta boxes
     *
     * @return void
     */
    public function add_meta_boxes()
    {
        add_meta_box(
            'rse_student_subjects',
            esc_html__('Student Subjects', 'result-spark-engine'),
            [$this, 'render_meta_box'],
            'students',
            'normal',
            'high'
        );
    }

    /**
     * Render meta box
     *
     * @param \WP_Post $post Post object.
     * @return void
     */
    public function render_meta_box($post)
    {
        wp_nonce_field('rse_student_subjects_nonce', 'rse_student_subjects_nonce');

        // Get saved values
        $major_subjects = get_post_meta($post->ID, '_rse_major_subjects', true);
        $non_major_subjects = get_post_meta($post->ID, '_rse_non_major_subjects', true);

        if (!is_array($major_subjects)) {
            $major_subjects = [];
        }
        if (!is_array($non_major_subjects)) {
            $non_major_subjects = [];
        }

        // Get student's class and department
        $student_class = wp_get_post_terms($post->ID, 'class_level', ['fields' => 'ids']);
        $student_department = wp_get_post_terms($post->ID, 'department', ['fields' => 'ids']);

        $class_id = !empty($student_class) ? $student_class[0] : 0;
        $department_id = !empty($student_department) ? $student_department[0] : 0;

        // Get available subjects based on class and department
        $available_subjects = $this->get_available_subjects($class_id, $department_id);
        
        // Sort subjects alphabetically
        usort($available_subjects, function($a, $b) {
            return strcmp($a->post_title, $b->post_title);
        });

        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label><?php echo esc_html__('Class & Department', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <div id="rse-student-class-dept-info">
                        <p class="description">
                            <?php 
                            if ($class_id > 0) {
                                $class_term = get_term($class_id, 'class_level');
                                echo esc_html__('Class: ', 'result-spark-engine') . '<strong>' . esc_html($class_term ? $class_term->name : '') . '</strong>';
                            } else {
                                echo '<span style="color: #dc3232;">' . esc_html__('Please assign a class to this student first.', 'result-spark-engine') . '</span>';
                            }
                            ?>
                            <?php if ($department_id > 0) : ?>
                                <?php 
                                $dept_term = get_term($department_id, 'department');
                                echo ' | ' . esc_html__('Department: ', 'result-spark-engine') . '<strong>' . esc_html($dept_term ? $dept_term->name : '') . '</strong>';
                                ?>
                            <?php endif; ?>
                        </p>
                        <p class="description">
                            <?php echo esc_html__('Subjects will be filtered based on the student\'s class and department.', 'result-spark-engine'); ?>
                        </p>
                        <button type="button" class="button button-secondary" id="rse-reload-subjects" style="margin-top: 5px;">
                            <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
                            <?php echo esc_html__('Reload Subjects', 'result-spark-engine'); ?>
                        </button>
                    </div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="rse_major_subjects"><?php echo esc_html__('Major Subjects', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <select 
                        id="rse_major_subjects" 
                        name="rse_major_subjects[]" 
                        multiple 
                        class="rse-select2 regular-text"
                        style="width: 100%;"
                    >
                        <?php if (!empty($available_subjects)) : ?>
                            <?php foreach ($available_subjects as $subject) : 
                                $subject_depts = wp_get_post_terms($subject->ID, 'department', ['fields' => 'ids']);
                                $has_department = !is_wp_error($subject_depts) && !empty($subject_depts);
                                $subject_type = $has_department ? 'Departmental' : 'Compulsory';
                                $subject_type_class = $has_department ? 'departmental' : 'compulsory';
                            ?>
                                <option 
                                    value="<?php echo esc_attr($subject->ID); ?>"
                                    data-type="<?php echo esc_attr($subject_type_class); ?>"
                                    <?php selected(in_array($subject->ID, $major_subjects)); ?>
                                >
                                    <?php echo esc_html($subject->post_title); ?> (<?php echo esc_html($subject_type); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <option value="0" disabled>
                                <?php echo esc_html__('No subjects available. Please assign class and department to the student first.', 'result-spark-engine'); ?>
                            </option>
                        <?php endif; ?>
                    </select>
                    <p class="description">
                        <?php echo esc_html__('Search and select multiple subjects.', 'result-spark-engine'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="rse_non_major_subjects"><?php echo esc_html__('Non Major Subjects', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <select 
                        id="rse_non_major_subjects" 
                        name="rse_non_major_subjects[]" 
                        multiple 
                        class="rse-select2 regular-text"
                        style="width: 100%;"
                    >
                        <?php if (!empty($available_subjects)) : ?>
                            <?php foreach ($available_subjects as $subject) : 
                                $subject_depts = wp_get_post_terms($subject->ID, 'department', ['fields' => 'ids']);
                                $has_department = !is_wp_error($subject_depts) && !empty($subject_depts);
                                $subject_type = $has_department ? 'Departmental' : 'Compulsory';
                                $subject_type_class = $has_department ? 'departmental' : 'compulsory';
                            ?>
                                <option 
                                    value="<?php echo esc_attr($subject->ID); ?>"
                                    data-type="<?php echo esc_attr($subject_type_class); ?>"
                                    <?php selected(in_array($subject->ID, $non_major_subjects)); ?>
                                >
                                    <?php echo esc_html($subject->post_title); ?> (<?php echo esc_html($subject_type); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <option value="0" disabled>
                                <?php echo esc_html__('No subjects available. Please assign class and department to the student first.', 'result-spark-engine'); ?>
                            </option>
                        <?php endif; ?>
                    </select>
                    <p class="description">
                        <?php echo esc_html__('Search and select multiple subjects.', 'result-spark-engine'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <input type="hidden" id="rse-student-class-id" value="<?php echo esc_attr($class_id); ?>" />
        <input type="hidden" id="rse-student-dept-id" value="<?php echo esc_attr($department_id); ?>" />
        <input type="hidden" id="rse-student-post-id" value="<?php echo esc_attr($post->ID); ?>" />
        <input type="hidden" id="rse-ajax-url" value="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" />
        <input type="hidden" id="rse-nonce" value="<?php echo wp_create_nonce('rse_dashboard_nonce'); ?>" />
        <?php
    }

    /**
     * Get available subjects based on class and department
     *
     * @param int $class_id Class term ID
     * @param int $department_id Department term ID
     * @return array Array of subject post objects
     */
    private function get_available_subjects($class_id, $department_id)
    {
        if ($class_id <= 0) {
            return [];
        }

        // Get all subjects for this class
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
                    'terms' => $class_id,
                ],
            ],
        ]);

        // Filter subjects based on department
        // Compulsory = no department taxonomy
        // Departmental = has department taxonomy matching student's department
        $subjects = [];
        foreach ($all_subjects as $subject) {
            $subject_depts = wp_get_post_terms($subject->ID, 'department', ['fields' => 'ids']);
            $has_department = !is_wp_error($subject_depts) && !empty($subject_depts);

            if ($department_id > 0) {
                // Include both compulsory (no dept) and departmental (matching dept)
                if (!$has_department) {
                    // Compulsory subject
                    $subjects[] = $subject;
                } elseif (in_array($department_id, $subject_depts)) {
                    // Departmental subject matching student's department
                    $subjects[] = $subject;
                }
            } else {
                // No department assigned to student - only show compulsory subjects
                if (!$has_department) {
                    $subjects[] = $subject;
                }
            }
        }

        return $subjects;
    }

    /**
     * Save meta box data
     *
     * @param int     $post_id Post ID.
     * @param \WP_Post $post    Post object.
     * @param bool    $update  Whether updating an existing post.
     * @return void
     */
    public function save_meta($post_id, $post, $update)
    {
        // Check nonce
        if (!isset($_POST['rse_student_subjects_nonce']) || !wp_verify_nonce($_POST['rse_student_subjects_nonce'], 'rse_student_subjects_nonce')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check post type
        if ('students' !== $post->post_type) {
            return;
        }

        // Save major subjects
        if (isset($_POST['rse_major_subjects']) && is_array($_POST['rse_major_subjects'])) {
            $major_subjects = array_map('absint', $_POST['rse_major_subjects']);
            $major_subjects = array_filter($major_subjects); // Remove empty values
            update_post_meta($post_id, '_rse_major_subjects', $major_subjects);
        } else {
            delete_post_meta($post_id, '_rse_major_subjects');
        }

        // Save non major subjects
        if (isset($_POST['rse_non_major_subjects']) && is_array($_POST['rse_non_major_subjects'])) {
            $non_major_subjects = array_map('absint', $_POST['rse_non_major_subjects']);
            $non_major_subjects = array_filter($non_major_subjects); // Remove empty values
            update_post_meta($post_id, '_rse_non_major_subjects', $non_major_subjects);
        } else {
            delete_post_meta($post_id, '_rse_non_major_subjects');
        }
    }

    /**
     * Enqueue assets for student metabox
     *
     * @param string $hook Current admin page hook.
     * @return void
     */
    public function enqueue_assets($hook)
    {
        global $post_type;

        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }

        if ('students' !== $post_type) {
            return;
        }

        // Enqueue Select2 CSS and JS from CDN
        wp_enqueue_style(
            'select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
            [],
            '4.1.0'
        );

        wp_enqueue_style(
            'rse-student-metabox',
            RSE_ASSETS . '/css/student-metabox.css',
            ['select2'],
            RSE_VERSION
        );

        wp_enqueue_script(
            'select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
            ['jquery'],
            '4.1.0',
            true
        );

        wp_enqueue_script(
            'rse-student-metabox',
            RSE_ASSETS . '/js/student-metabox.js',
            ['jquery', 'select2'],
            RSE_VERSION,
            true
        );

        wp_localize_script(
            'rse-student-metabox',
            'rseStudentMetabox',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rse_dashboard_nonce'),
                'loading_text' => esc_html__('Loading subjects...', 'result-spark-engine'),
            ]
        );
    }
}

