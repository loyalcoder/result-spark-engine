<?php

namespace Result_Spark_Engine\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Department Taxonomy Meta Box
 * 
 * Adds department code field
 */
class Department_Metabox
{
    /**
     * Initialize
     */
    public function __construct()
    {
        // Add fields to department taxonomy add/edit forms
        add_action('department_add_form_fields', [$this, 'add_department_fields']);
        add_action('department_edit_form_fields', [$this, 'edit_department_fields']);
        
        // Save department fields
        add_action('created_department', [$this, 'save_department_fields']);
        add_action('edited_department', [$this, 'save_department_fields']);
    }

    /**
     * Add fields to department taxonomy add form
     *
     * @return void
     */
    public function add_department_fields()
    {
        ?>
        <div class="form-field term-department-code-wrap">
            <label for="rse_department_code"><?php echo esc_html__('Department Code', 'result-spark-engine'); ?></label>
            <input 
                type="text" 
                id="rse_department_code" 
                name="rse_department_code" 
                value="" 
                class="regular-text"
                placeholder="<?php echo esc_attr__('e.g., CSE, EEE, BBA', 'result-spark-engine'); ?>"
                maxlength="50"
            />
            <p class="description">
                <?php echo esc_html__('Enter a unique code for this department (e.g., CSE, EEE, BBA).', 'result-spark-engine'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Edit fields on department taxonomy edit form
     *
     * @param \WP_Term $term The term object
     * @return void
     */
    public function edit_department_fields($term)
    {
        $department_code = get_term_meta($term->term_id, '_rse_department_code', true);
        ?>
        <tr class="form-field term-department-code-wrap">
            <th scope="row">
                <label for="rse_department_code"><?php echo esc_html__('Department Code', 'result-spark-engine'); ?></label>
            </th>
            <td>
                <input 
                    type="text" 
                    id="rse_department_code" 
                    name="rse_department_code" 
                    value="<?php echo esc_attr($department_code); ?>" 
                    class="regular-text"
                    placeholder="<?php echo esc_attr__('e.g., CSE, EEE, BBA', 'result-spark-engine'); ?>"
                    maxlength="50"
                />
                <p class="description">
                    <?php echo esc_html__('Enter a unique code for this department (e.g., CSE, EEE, BBA).', 'result-spark-engine'); ?>
                </p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save department fields
     *
     * @param int $term_id Term ID
     * @return void
     */
    public function save_department_fields($term_id)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['rse_department_code'])) {
            $department_code = sanitize_text_field($_POST['rse_department_code']);
            
            if (!empty($department_code)) {
                update_term_meta($term_id, '_rse_department_code', $department_code);
            } else {
                delete_term_meta($term_id, '_rse_department_code');
            }
        } else {
            delete_term_meta($term_id, '_rse_department_code');
        }
    }
}

