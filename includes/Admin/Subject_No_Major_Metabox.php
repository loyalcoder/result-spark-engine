<?php

namespace Result_Spark_Engine\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Subject No Major / 4th Subject Metabox Handler
 * 
 * Adds fields for marking subject as no major or 4th subject
 * with minimum addable mark and minimum point settings
 */
class Subject_No_Major_Metabox
{
    /**
     * Initialize class
     */
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_subject', [$this, 'save_meta'], 10, 3);
    }

    /**
     * Add meta boxes
     *
     * @return void
     */
    public function add_meta_boxes()
    {
        add_meta_box(
            'rse_subject_no_major',
            esc_html__('No Major / 4th Subject Settings', 'result-spark-engine'),
            [$this, 'render_meta_box'],
            'subject',
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
        wp_nonce_field('rse_subject_no_major_nonce', 'rse_subject_no_major_nonce');

        // Get saved values
        $min_addable_mark = get_post_meta($post->ID, '_rse_min_addable_mark', true);
        $min_point = get_post_meta($post->ID, '_rse_min_point', true);

        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="rse_min_addable_mark"><?php echo esc_html__('Addable Mark Threshold', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <input 
                        type="number" 
                        id="rse_min_addable_mark" 
                        name="rse_min_addable_mark" 
                        value="<?php echo esc_attr($min_addable_mark); ?>" 
                        class="regular-text"
                        step="0.01"
                        min="0"
                        placeholder="<?php echo esc_attr__('e.g., 20', 'result-spark-engine'); ?>"
                    />
                    <p class="description">
                        <?php echo esc_html__('Marks will be added to the total only if the student achieves marks above this threshold. If a student has this subject as no major or 4th subject, marks equal to or above this value will be added to the total marks.', 'result-spark-engine'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="rse_min_point"><?php echo esc_html__('Addable Point Threshold', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <input 
                        type="number" 
                        id="rse_min_point" 
                        name="rse_min_point" 
                        value="<?php echo esc_attr($min_point); ?>" 
                        class="regular-text"
                        step="0.01"
                        min="0"
                        max="5"
                        placeholder="<?php echo esc_attr__('e.g., 2.00', 'result-spark-engine'); ?>"
                    />
                    <p class="description">
                        <?php echo esc_html__('Points/GPA will be added to the total only if the student achieves points above this threshold. If a student has this subject as no major or 4th subject, points equal to or above this value will be added to the total GPA.', 'result-spark-engine'); ?>
                    </p>
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
        if (!isset($_POST['rse_subject_no_major_nonce']) || !wp_verify_nonce($_POST['rse_subject_no_major_nonce'], 'rse_subject_no_major_nonce')) {
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
        if ('subject' !== $post->post_type) {
            return;
        }

        // Save minimum addable mark
        if (isset($_POST['rse_min_addable_mark'])) {
            $min_addable_mark = floatval($_POST['rse_min_addable_mark']);
            if ($min_addable_mark > 0) {
                update_post_meta($post_id, '_rse_min_addable_mark', $min_addable_mark);
            } else {
                delete_post_meta($post_id, '_rse_min_addable_mark');
            }
        } else {
            delete_post_meta($post_id, '_rse_min_addable_mark');
        }

        // Save minimum point
        if (isset($_POST['rse_min_point'])) {
            $min_point = floatval($_POST['rse_min_point']);
            if ($min_point > 0) {
                update_post_meta($post_id, '_rse_min_point', $min_point);
            } else {
                delete_post_meta($post_id, '_rse_min_point');
            }
        } else {
            delete_post_meta($post_id, '_rse_min_point');
        }
    }
}

