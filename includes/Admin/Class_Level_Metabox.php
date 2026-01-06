<?php

namespace Result_Spark_Engine\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Level Taxonomy Meta Box
 * 
 * Adds repeater fields for final grade thresholds based on GPA
 */
class Class_Level_Metabox
{
    /**
     * Initialize
     */
    public function __construct()
    {
        // Add fields to class_level taxonomy add/edit forms
        add_action('class_level_add_form_fields', [$this, 'add_class_level_fields']);
        add_action('class_level_edit_form_fields', [$this, 'edit_class_level_fields']);
        
        // Save class_level fields
        add_action('created_class_level', [$this, 'save_class_level_fields']);
        add_action('edited_class_level', [$this, 'save_class_level_fields']);
        
        // Enqueue assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Add fields to class_level taxonomy add form
     *
     * @return void
     */
    public function add_class_level_fields()
    {
        ?>
        <div class="form-field term-final-grade-thresholds-wrap">
            <label><?php echo esc_html__('Final Grade Thresholds', 'result-spark-engine'); ?></label>
            <div id="rse-final-grade-thresholds-container">
                <div class="rse-final-grade-threshold-item" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; border-radius: 4px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: end;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                <?php echo esc_html__('Min GPA', 'result-spark-engine'); ?>
                            </label>
                            <input type="number" name="rse_final_grade_thresholds[0][min_gpa]" value="" step="0.01" min="0" max="5" class="regular-text" placeholder="5.00" style="width: 100%;" />
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                <?php echo esc_html__('Grade', 'result-spark-engine'); ?>
                            </label>
                            <input type="text" name="rse_final_grade_thresholds[0][grade]" value="" class="regular-text" placeholder="A+" style="width: 100%;" maxlength="10" />
                        </div>
                        <div>
                            <button type="button" class="button rse-remove-final-grade-threshold" style="height: 30px; display: none;">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="button button-secondary" id="rse-add-final-grade-threshold" style="margin-top: 10px;">
                <span class="dashicons dashicons-plus-alt2" style="vertical-align: middle;"></span>
                <?php echo esc_html__('Add Grade Threshold', 'result-spark-engine'); ?>
            </button>
            <p class="description">
                <?php echo esc_html__('Define GPA thresholds for final grades. Example: GPA >= 5.00 = A+, GPA >= 4.00 = A. Grades should be ordered from highest to lowest GPA.', 'result-spark-engine'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Edit fields on class_level taxonomy edit form
     *
     * @param \WP_Term $term The term object
     * @return void
     */
    public function edit_class_level_fields($term)
    {
        $thresholds = get_term_meta($term->term_id, '_rse_final_grade_thresholds', true);
        if (!is_array($thresholds)) {
            $thresholds = [];
        }

        // Ensure at least one empty row
        if (empty($thresholds)) {
            $thresholds = [
                [
                    'min_gpa' => '',
                    'grade' => '',
                ]
            ];
        }

        // Sort by min_gpa descending (highest first)
        usort($thresholds, function($a, $b) {
            $gpa_a = floatval($a['min_gpa'] ?? 0);
            $gpa_b = floatval($b['min_gpa'] ?? 0);
            return $gpa_b <=> $gpa_a;
        });
        ?>
        <tr class="form-field term-final-grade-thresholds-wrap">
            <th scope="row">
                <label><?php echo esc_html__('Final Grade Thresholds', 'result-spark-engine'); ?></label>
            </th>
            <td>
                <div id="rse-final-grade-thresholds-container">
                    <?php foreach ($thresholds as $index => $threshold) : ?>
                        <div class="rse-final-grade-threshold-item" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; border-radius: 4px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: end;">
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                        <?php echo esc_html__('Min GPA', 'result-spark-engine'); ?>
                                    </label>
                                    <input type="number" name="rse_final_grade_thresholds[<?php echo esc_attr($index); ?>][min_gpa]" value="<?php echo esc_attr($threshold['min_gpa'] ?? ''); ?>" step="0.01" min="0" max="5" class="regular-text" placeholder="5.00" style="width: 100%;" />
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px;">
                                        <?php echo esc_html__('Grade', 'result-spark-engine'); ?>
                                    </label>
                                    <input type="text" name="rse_final_grade_thresholds[<?php echo esc_attr($index); ?>][grade]" value="<?php echo esc_attr($threshold['grade'] ?? ''); ?>" class="regular-text" placeholder="A+" style="width: 100%;" maxlength="10" />
                                </div>
                                <div>
                                    <button type="button" class="button rse-remove-final-grade-threshold" style="height: 30px;">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button button-secondary" id="rse-add-final-grade-threshold" style="margin-top: 10px;">
                    <span class="dashicons dashicons-plus-alt2" style="vertical-align: middle;"></span>
                    <?php echo esc_html__('Add Grade Threshold', 'result-spark-engine'); ?>
                </button>
                <p class="description">
                    <?php echo esc_html__('Define GPA thresholds for final grades. Example: GPA >= 5.00 = A+, GPA >= 4.00 = A. Grades should be ordered from highest to lowest GPA.', 'result-spark-engine'); ?>
                </p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save class_level fields
     *
     * @param int $term_id Term ID
     * @return void
     */
    public function save_class_level_fields($term_id)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['rse_final_grade_thresholds']) && is_array($_POST['rse_final_grade_thresholds'])) {
            $thresholds = [];
            
            foreach ($_POST['rse_final_grade_thresholds'] as $threshold) {
                $min_gpa = isset($threshold['min_gpa']) ? floatval($threshold['min_gpa']) : 0;
                $grade = isset($threshold['grade']) ? sanitize_text_field($threshold['grade']) : '';

                // Only save if grade is provided
                if (!empty($grade)) {
                    $thresholds[] = [
                        'min_gpa' => $min_gpa,
                        'grade' => $grade,
                    ];
                }
            }

            // Sort by min_gpa descending (highest first)
            usort($thresholds, function($a, $b) {
                return $b['min_gpa'] <=> $a['min_gpa'];
            });

            if (!empty($thresholds)) {
                update_term_meta($term_id, '_rse_final_grade_thresholds', $thresholds);
            } else {
                delete_term_meta($term_id, '_rse_final_grade_thresholds');
            }
        } else {
            delete_term_meta($term_id, '_rse_final_grade_thresholds');
        }
    }

    /**
     * Enqueue assets for class_level taxonomy
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_assets($hook)
    {
        // Check if we're on the class_level taxonomy page
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Check if we're on edit-tags.php or term.php and the taxonomy is 'class_level'
        if (($hook === 'edit-tags.php' || $hook === 'term.php') && $screen->taxonomy === 'class_level') {
            wp_enqueue_script(
                'rse-class-level-metabox',
                RSE_ASSETS . '/js/class-level-metabox.js',
                ['jquery'],
                RSE_VERSION,
                true
            );

            wp_enqueue_style(
                'rse-class-level-metabox',
                RSE_ASSETS . '/css/class-level-metabox.css',
                [],
                RSE_VERSION
            );
        }
    }
}

