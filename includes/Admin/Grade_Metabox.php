<?php

namespace Result_Spark_Engine\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Grade Taxonomy Meta Box
 * 
 * Adds repeater fields for mark ranges, points, and grades
 */
class Grade_Metabox
{
    /**
     * Initialize
     */
    public function __construct()
    {
        // Add fields to grade taxonomy add/edit forms
        add_action('grade_add_form_fields', [$this, 'add_grade_fields']);
        add_action('grade_edit_form_fields', [$this, 'edit_grade_fields']);
        
        // Save grade fields
        add_action('created_grade', [$this, 'save_grade_fields']);
        add_action('edited_grade', [$this, 'save_grade_fields']);
        
        // Enqueue assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Add fields to grade taxonomy add form
     *
     * @return void
     */
    public function add_grade_fields()
    {
        ?>
        <div class="form-field term-grade-ranges-wrap">
            <label><?php echo esc_html__('Grade Ranges', 'result-spark-engine'); ?></label>
            <div id="rse-grade-ranges-container">
                <div class="rse-grade-range-item" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; border-radius: 4px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                <?php echo esc_html__('Min Mark', 'result-spark-engine'); ?>
                            </label>
                            <input type="number" name="rse_grade_ranges[0][min_mark]" value="" step="0.01" min="0" class="regular-text" placeholder="0" style="width: 100%;" />
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                <?php echo esc_html__('Max Mark', 'result-spark-engine'); ?>
                            </label>
                            <input type="number" name="rse_grade_ranges[0][max_mark]" value="" step="0.01" min="0" class="regular-text" placeholder="100" style="width: 100%;" />
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                <?php echo esc_html__('Point', 'result-spark-engine'); ?>
                            </label>
                            <input type="number" name="rse_grade_ranges[0][point]" value="" step="0.01" min="0" max="5" class="regular-text" placeholder="4.00" style="width: 100%;" />
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                <?php echo esc_html__('Grade', 'result-spark-engine'); ?>
                            </label>
                            <input type="text" name="rse_grade_ranges[0][grade]" value="" class="regular-text" placeholder="A+" style="width: 100%;" maxlength="10" />
                        </div>
                        <div>
                            <button type="button" class="button rse-remove-grade-range" style="height: 30px; display: none;">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="button button-secondary" id="rse-add-grade-range" style="margin-top: 10px;">
                <span class="dashicons dashicons-plus-alt2" style="vertical-align: middle;"></span>
                <?php echo esc_html__('Add Grade Range', 'result-spark-engine'); ?>
            </button>
            <p class="description">
                <?php echo esc_html__('Define mark ranges with corresponding points and grades. Example: 80-100 marks = 4.00 point = A+', 'result-spark-engine'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Edit fields on grade taxonomy edit form
     *
     * @param WP_Term $term The term object
     * @return void
     */
    public function edit_grade_fields($term)
    {
        $grade_ranges = get_term_meta($term->term_id, '_rse_grade_ranges', true);
        if (!is_array($grade_ranges)) {
            $grade_ranges = [];
        }

        // Ensure at least one empty row
        if (empty($grade_ranges)) {
            $grade_ranges = [
                [
                    'min_mark' => '',
                    'max_mark' => '',
                    'point' => '',
                    'grade' => '',
                ]
            ];
        }
        ?>
        <tr class="form-field term-grade-ranges-wrap">
            <th scope="row">
                <label><?php echo esc_html__('Grade Ranges', 'result-spark-engine'); ?></label>
            </th>
            <td>
                <div id="rse-grade-ranges-container">
                    <?php foreach ($grade_ranges as $index => $range) : ?>
                        <div class="rse-grade-range-item" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; border-radius: 4px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                        <?php echo esc_html__('Min Mark', 'result-spark-engine'); ?>
                                    </label>
                                    <input type="number" name="rse_grade_ranges[<?php echo esc_attr($index); ?>][min_mark]" value="<?php echo esc_attr($range['min_mark'] ?? ''); ?>" step="0.01" min="0" class="regular-text" placeholder="0" style="width: 100%;" />
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                        <?php echo esc_html__('Max Mark', 'result-spark-engine'); ?>
                                    </label>
                                    <input type="number" name="rse_grade_ranges[<?php echo esc_attr($index); ?>][max_mark]" value="<?php echo esc_attr($range['max_mark'] ?? ''); ?>" step="0.01" min="0" class="regular-text" placeholder="100" style="width: 100%;" />
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                        <?php echo esc_html__('Point', 'result-spark-engine'); ?>
                                    </label>
                                    <input type="number" name="rse_grade_ranges[<?php echo esc_attr($index); ?>][point]" value="<?php echo esc_attr($range['point'] ?? ''); ?>" step="0.01" min="0" max="5" class="regular-text" placeholder="4.00" style="width: 100%;" />
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                        <?php echo esc_html__('Grade', 'result-spark-engine'); ?>
                                    </label>
                                    <input type="text" name="rse_grade_ranges[<?php echo esc_attr($index); ?>][grade]" value="<?php echo esc_attr($range['grade'] ?? ''); ?>" class="regular-text" placeholder="A+" style="width: 100%;" maxlength="10" />
                                </div>
                                <div>
                                    <button type="button" class="button rse-remove-grade-range" style="height: 30px;">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button button-secondary" id="rse-add-grade-range" style="margin-top: 10px;">
                    <span class="dashicons dashicons-plus-alt2" style="vertical-align: middle;"></span>
                    <?php echo esc_html__('Add Grade Range', 'result-spark-engine'); ?>
                </button>
                <p class="description">
                    <?php echo esc_html__('Define mark ranges with corresponding points and grades. Example: 80-100 marks = 4.00 point = A+', 'result-spark-engine'); ?>
                </p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save grade fields
     *
     * @param int $term_id Term ID
     * @return void
     */
    public function save_grade_fields($term_id)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['rse_grade_ranges']) && is_array($_POST['rse_grade_ranges'])) {
            $grade_ranges = [];
            
            foreach ($_POST['rse_grade_ranges'] as $range) {
                $min_mark = isset($range['min_mark']) ? floatval($range['min_mark']) : 0;
                $max_mark = isset($range['max_mark']) ? floatval($range['max_mark']) : 0;
                $point = isset($range['point']) ? floatval($range['point']) : 0;
                $grade = isset($range['grade']) ? sanitize_text_field($range['grade']) : '';

                // Only save if grade is provided
                if (!empty($grade)) {
                    $grade_ranges[] = [
                        'min_mark' => $min_mark,
                        'max_mark' => $max_mark,
                        'point' => $point,
                        'grade' => $grade,
                    ];
                }
            }

            if (!empty($grade_ranges)) {
                update_term_meta($term_id, '_rse_grade_ranges', $grade_ranges);
            } else {
                delete_term_meta($term_id, '_rse_grade_ranges');
            }
        } else {
            delete_term_meta($term_id, '_rse_grade_ranges');
        }
    }

    /**
     * Enqueue assets for grade taxonomy
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_assets($hook)
    {
        // Check if we're on the grade taxonomy page
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Check if we're on edit-tags.php or term.php and the taxonomy is 'grade'
        if (($hook === 'edit-tags.php' || $hook === 'term.php') && $screen->taxonomy === 'grade') {
            wp_enqueue_script(
                'rse-grade-metabox',
                RSE_ASSETS . '/js/grade-metabox.js',
                ['jquery'],
                RSE_VERSION,
                true
            );

            wp_enqueue_style(
                'rse-grade-metabox',
                RSE_ASSETS . '/css/grade-metabox.css',
                [],
                RSE_VERSION
            );
        }
    }
}

