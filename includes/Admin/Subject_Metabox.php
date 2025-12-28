<?php

namespace Result_Spark_Engine\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Subject Metabox Handler
 */
class Subject_Metabox
{
    /**
     * Initialize class
     */
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_subject', [$this, 'save_meta'], 10, 3);
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
            'rse_subject_details',
            esc_html__('Subject Details', 'result-spark-engine'),
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
        wp_nonce_field('rse_subject_meta_nonce', 'rse_subject_meta_nonce');

        // Get saved values
        $subject_code = get_post_meta($post->ID, '_rse_subject_code', true);
        $total_mark = get_post_meta($post->ID, '_rse_total_mark', true);
        $mark_breakdown = get_post_meta($post->ID, '_rse_mark_breakdown', true);
        $pass_mark = get_post_meta($post->ID, '_rse_pass_mark', true);
        $compulsory_subject = get_post_meta($post->ID, '_rse_compulsory_subject', true);

        if (!is_array($mark_breakdown)) {
            $mark_breakdown = [];
        }

        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="rse_subject_code"><?php echo esc_html__('Subject Code', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <input 
                        type="text" 
                        id="rse_subject_code" 
                        name="rse_subject_code" 
                        value="<?php echo esc_attr($subject_code); ?>" 
                        class="regular-text"
                        placeholder="<?php echo esc_attr__('e.g., MATH101', 'result-spark-engine'); ?>"
                    />
                    <p class="description"><?php echo esc_html__('Enter the subject code.', 'result-spark-engine'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="rse_total_mark"><?php echo esc_html__('Total Mark', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <input 
                        type="number" 
                        id="rse_total_mark" 
                        name="rse_total_mark" 
                        value="<?php echo esc_attr($total_mark); ?>" 
                        class="regular-text"
                        step="0.01"
                        min="0"
                        placeholder="<?php echo esc_attr__('e.g., 100', 'result-spark-engine'); ?>"
                    />
                    <p class="description"><?php echo esc_html__('Enter the total marks for this subject.', 'result-spark-engine'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label><?php echo esc_html__('Mark Breakdown', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <div id="rse-mark-breakdown-container">
                        <table class="widefat" id="rse-mark-breakdown-table" style="margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th style="width: 30%;"><?php echo esc_html__('Name', 'result-spark-engine'); ?></th>
                                    <th style="width: 20%;"><?php echo esc_html__('Mark', 'result-spark-engine'); ?></th>
                                    <th style="width: 20%;"><?php echo esc_html__('Pass Mark', 'result-spark-engine'); ?></th>
                                    <th style="width: 20%;"><?php echo esc_html__('Need to Pass', 'result-spark-engine'); ?></th>
                                    <th style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($mark_breakdown)) : ?>
                                    <?php foreach ($mark_breakdown as $index => $breakdown) : ?>
                                        <tr class="rse-breakdown-row">
                                            <td>
                                                <input 
                                                    type="text" 
                                                    name="rse_mark_breakdown[<?php echo esc_attr($index); ?>][name]" 
                                                    value="<?php echo esc_attr($breakdown['name'] ?? ''); ?>" 
                                                    class="regular-text"
                                                    placeholder="<?php echo esc_attr__('e.g., Written', 'result-spark-engine'); ?>"
                                                />
                                            </td>
                                            <td>
                                                <input 
                                                    type="number" 
                                                    name="rse_mark_breakdown[<?php echo esc_attr($index); ?>][mark]" 
                                                    value="<?php echo esc_attr($breakdown['mark'] ?? ''); ?>" 
                                                    class="regular-text"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="<?php echo esc_attr__('e.g., 70', 'result-spark-engine'); ?>"
                                                />
                                            </td>
                                            <td>
                                                <input 
                                                    type="number" 
                                                    name="rse_mark_breakdown[<?php echo esc_attr($index); ?>][pass_mark]" 
                                                    value="<?php echo esc_attr($breakdown['pass_mark'] ?? ''); ?>" 
                                                    class="regular-text"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="<?php echo esc_attr__('e.g., 35', 'result-spark-engine'); ?>"
                                                />
                                            </td>
                                            <td>
                                                <label>
                                                    <input 
                                                        type="checkbox" 
                                                        name="rse_mark_breakdown[<?php echo esc_attr($index); ?>][need_to_pass]" 
                                                        value="1" 
                                                        <?php checked(!empty($breakdown['need_to_pass']), true); ?>
                                                    />
                                                    <?php echo esc_html__('Required', 'result-spark-engine'); ?>
                                                </label>
                                            </td>
                                            <td>
                                                <button type="button" class="button rse-remove-breakdown" style="color: #a00;">
                                                    <?php echo esc_html__('Remove', 'result-spark-engine'); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <p style="margin-top: 10px;">
                            <button type="button" class="button" id="rse-add-breakdown">
                                <?php echo esc_html__('Add Breakdown', 'result-spark-engine'); ?>
                            </button>
                        </p>
                    </div>
                    <p class="description"><?php echo esc_html__('Add mark breakdowns (e.g., Written, MCQ, Practical). Check "Need to Pass" if students must pass this breakdown to pass the subject.', 'result-spark-engine'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="rse_pass_mark"><?php echo esc_html__('Overall Pass Mark', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <label>
                        <input 
                            type="checkbox" 
                            id="rse_pass_mark" 
                            name="rse_pass_mark" 
                            value="1" 
                            <?php checked($pass_mark, '1'); ?>
                        />
                        <?php echo esc_html__('Enable overall pass mark requirement', 'result-spark-engine'); ?>
                    </label>
                    <p class="description"><?php echo esc_html__('If checked, students must achieve the overall pass mark to pass this subject.', 'result-spark-engine'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="rse_compulsory_subject"><?php echo esc_html__('Compulsory Subjects', 'result-spark-engine'); ?></label>
                </th>
                <td>
                    <label>
                        <input 
                            type="checkbox" 
                            id="rse_compulsory_subject" 
                            name="rse_compulsory_subject" 
                            value="1" 
                            <?php checked($compulsory_subject, '1'); ?>
                        />
                        <?php echo esc_html__('This is a compulsory subject', 'result-spark-engine'); ?>
                    </label>
                    <p class="description"><?php echo esc_html__('If checked, this subject is marked as compulsory for students.', 'result-spark-engine'); ?></p>
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
        if (!isset($_POST['rse_subject_meta_nonce']) || !wp_verify_nonce($_POST['rse_subject_meta_nonce'], 'rse_subject_meta_nonce')) {
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

        // Save subject code
        if (isset($_POST['rse_subject_code'])) {
            update_post_meta($post_id, '_rse_subject_code', sanitize_text_field($_POST['rse_subject_code']));
        } else {
            delete_post_meta($post_id, '_rse_subject_code');
        }

        // Save total mark
        if (isset($_POST['rse_total_mark'])) {
            $total_mark = floatval($_POST['rse_total_mark']);
            if ($total_mark > 0) {
                update_post_meta($post_id, '_rse_total_mark', $total_mark);
            } else {
                delete_post_meta($post_id, '_rse_total_mark');
            }
        } else {
            delete_post_meta($post_id, '_rse_total_mark');
        }

        // Save mark breakdown
        if (isset($_POST['rse_mark_breakdown']) && is_array($_POST['rse_mark_breakdown'])) {
            $mark_breakdown = [];
            
            foreach ($_POST['rse_mark_breakdown'] as $breakdown) {
                $name = isset($breakdown['name']) ? sanitize_text_field($breakdown['name']) : '';
                $mark = isset($breakdown['mark']) ? floatval($breakdown['mark']) : 0;
                $pass_mark = isset($breakdown['pass_mark']) ? floatval($breakdown['pass_mark']) : 0;
                $need_to_pass = isset($breakdown['need_to_pass']) ? '1' : '0';

                // Only save if name is provided
                if (!empty($name)) {
                    $mark_breakdown[] = [
                        'name' => $name,
                        'mark' => $mark,
                        'pass_mark' => $pass_mark,
                        'need_to_pass' => $need_to_pass,
                    ];
                }
            }

            if (!empty($mark_breakdown)) {
                update_post_meta($post_id, '_rse_mark_breakdown', $mark_breakdown);
            } else {
                delete_post_meta($post_id, '_rse_mark_breakdown');
            }
        } else {
            delete_post_meta($post_id, '_rse_mark_breakdown');
        }

        // Save pass mark checkbox
        if (isset($_POST['rse_pass_mark'])) {
            update_post_meta($post_id, '_rse_pass_mark', '1');
        } else {
            delete_post_meta($post_id, '_rse_pass_mark');
        }

        // Save compulsory subject checkbox
        if (isset($_POST['rse_compulsory_subject'])) {
            update_post_meta($post_id, '_rse_compulsory_subject', '1');
        } else {
            delete_post_meta($post_id, '_rse_compulsory_subject');
        }
    }

    /**
     * Enqueue assets for subject metabox
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

        if ('subject' !== $post_type) {
            return;
        }

        wp_enqueue_style(
            'rse-subject-metabox',
            RSE_ASSETS . '/css/subject-metabox.css',
            [],
            RSE_VERSION
        );

        wp_enqueue_script(
            'rse-subject-metabox',
            RSE_ASSETS . '/js/subject-metabox.js',
            ['jquery'],
            RSE_VERSION,
            true
        );
    }
}

