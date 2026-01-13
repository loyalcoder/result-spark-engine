<?php

namespace Result_Spark_Engine\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Student Information Metabox Handler
 * 
 * Adds Roll Number, Registration Number, and Additional Information repeater
 */
class Student_Info_Metabox
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
            'rse_student_info',
            esc_html__('Student Information', 'result-spark-engine'),
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
        wp_nonce_field('rse_student_info_nonce', 'rse_student_info_nonce');

        // Get saved values
        $roll_no = get_post_meta($post->ID, 'roll_no', true);
        $registration_no = get_post_meta($post->ID, 'registration_no', true);
        $additional_info = get_post_meta($post->ID, '_rse_additional_info', true);

        if (!is_array($additional_info)) {
            $additional_info = [];
        }

        // If empty, add one empty row
        if (empty($additional_info)) {
            $additional_info = [
                [
                    'label' => '',
                    'value' => '',
                ],
            ];
        }

        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="roll_no"><?php echo esc_html__('Roll Number', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <input 
                        type="text" 
                        id="roll_no" 
                        name="roll_no" 
                        value="<?php echo esc_attr($roll_no); ?>" 
                        class="regular-text"
                        placeholder="<?php echo esc_attr__('Enter roll number', 'result-spark-engine'); ?>"
                    />
                    <p class="description">
                        <?php echo esc_html__('Unique roll number for the student.', 'result-spark-engine'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="registration_no"><?php echo esc_html__('Registration Number', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <input 
                        type="text" 
                        id="registration_no" 
                        name="registration_no" 
                        value="<?php echo esc_attr($registration_no); ?>" 
                        class="regular-text"
                        placeholder="<?php echo esc_attr__('Enter registration number', 'result-spark-engine'); ?>"
                    />
                    <p class="description">
                        <?php echo esc_html__('Student registration number.', 'result-spark-engine'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label><?php echo esc_html__('Additional Information', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <div id="rse-additional-info-repeater">
                        <table class="widefat" style="margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th style="width: 40%;"><?php echo esc_html__('Label', 'result-spark-engine'); ?></th>
                                    <th style="width: 50%;"><?php echo esc_html__('Value', 'result-spark-engine'); ?></th>
                                    <th style="width: 10%;"><?php echo esc_html__('Action', 'result-spark-engine'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="rse-additional-info-tbody">
                                <?php foreach ($additional_info as $index => $info) : ?>
                                    <tr class="rse-info-row">
                                        <td>
                                            <input 
                                                type="text" 
                                                name="additional_info[<?php echo esc_attr($index); ?>][label]" 
                                                value="<?php echo esc_attr($info['label'] ?? ''); ?>" 
                                                class="regular-text" 
                                                placeholder="<?php echo esc_attr__('e.g., Phone, Email, Address', 'result-spark-engine'); ?>"
                                            />
                                        </td>
                                        <td>
                                            <input 
                                                type="text" 
                                                name="additional_info[<?php echo esc_attr($index); ?>][value]" 
                                                value="<?php echo esc_attr($info['value'] ?? ''); ?>" 
                                                class="regular-text" 
                                                placeholder="<?php echo esc_attr__('Enter value', 'result-spark-engine'); ?>"
                                            />
                                        </td>
                                        <td>
                                            <button type="button" class="button button-small rse-remove-row" style="color: #dc3232;">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="button" class="button button-secondary rse-add-row" style="margin-top: 10px;">
                            <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                            <?php echo esc_html__('Add New Field', 'result-spark-engine'); ?>
                        </button>
                        <p class="description">
                            <?php echo esc_html__('Add any additional information fields for this student (e.g., Phone, Email, Address, Guardian Name, etc.).', 'result-spark-engine'); ?>
                        </p>
                    </div>
                </td>
            </tr>
        </table>
        <?php
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
        if (!isset($_POST['rse_student_info_nonce']) || !wp_verify_nonce($_POST['rse_student_info_nonce'], 'rse_student_info_nonce')) {
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

        // Save roll number
        if (isset($_POST['roll_no'])) {
            $roll_no = sanitize_text_field($_POST['roll_no']);
            update_post_meta($post_id, 'roll_no', $roll_no);
        } else {
            delete_post_meta($post_id, 'roll_no');
        }

        // Save registration number
        if (isset($_POST['registration_no'])) {
            $registration_no = sanitize_text_field($_POST['registration_no']);
            update_post_meta($post_id, 'registration_no', $registration_no);
        } else {
            delete_post_meta($post_id, 'registration_no');
        }

        // Save additional information
        if (isset($_POST['additional_info']) && is_array($_POST['additional_info'])) {
            $additional_info = [];
            
            foreach ($_POST['additional_info'] as $info) {
                $label = isset($info['label']) ? sanitize_text_field($info['label']) : '';
                $value = isset($info['value']) ? sanitize_text_field($info['value']) : '';
                
                // Only save if both label and value are not empty
                if (!empty($label) || !empty($value)) {
                    $additional_info[] = [
                        'label' => $label,
                        'value' => $value,
                    ];
                }
            }
            
            if (!empty($additional_info)) {
                update_post_meta($post_id, '_rse_additional_info', $additional_info);
            } else {
                delete_post_meta($post_id, '_rse_additional_info');
            }
        } else {
            delete_post_meta($post_id, '_rse_additional_info');
        }
    }

    /**
     * Enqueue assets for student info metabox
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

        wp_enqueue_style(
            'rse-student-info-metabox',
            RSE_ASSETS . '/css/student-info-metabox.css',
            [],
            RSE_VERSION
        );

        wp_enqueue_script(
            'rse-student-info-metabox',
            RSE_ASSETS . '/js/student-info-metabox.js',
            ['jquery'],
            RSE_VERSION,
            true
        );

        wp_localize_script(
            'rse-student-info-metabox',
            'rseStudentInfoMetabox',
            [
                'label_placeholder' => esc_html__('e.g., Phone, Email, Address', 'result-spark-engine'),
                'value_placeholder' => esc_html__('Enter value', 'result-spark-engine'),
            ]
        );
    }
}
