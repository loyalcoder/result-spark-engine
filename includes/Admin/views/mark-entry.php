<?php
/**
 * Mark Entry Page Template
 *
 * @package Result_Spark_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get all exams for filter dropdown
$exams = get_posts([
    'post_type' => 'exam',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
]);
?>
<div class="wrap rse-mark-entry">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Mark Entry', 'result-spark-engine'); ?></h1>
    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="rse-filters" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 4px;">
        <form method="get" action="" id="rse-mark-entry-filters">
            <input type="hidden" name="page" value="rse-mark-entry">
            
            <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                <!-- Exam Selection -->
                <div style="flex: 1; min-width: 200px;">
                    <label for="filter_exam" style="display: block; margin-bottom: 8px; font-weight: 600; color: #23282d; font-size: 14px;">
                        <?php echo esc_html__('Select Exam', 'result-spark-engine'); ?>
                    </label>
                    <select name="filter_exam" id="filter_exam" class="regular-text" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;">
                        <option value="0"><?php echo esc_html__('— Select Exam —', 'result-spark-engine'); ?></option>
                        <?php if (!empty($exams)) : ?>
                            <?php foreach ($exams as $exam) : ?>
                                <option value="<?php echo esc_attr($exam->ID); ?>">
                                    <?php echo esc_html($exam->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <option value="0" disabled><?php echo esc_html__('No exams found. Please create an exam first.', 'result-spark-engine'); ?></option>
                        <?php endif; ?>
                    </select>
                    <?php if (empty($exams)) : ?>
                        <p class="description" style="color: #dc3232; margin-top: 5px; font-size: 13px;">
                            <?php echo esc_html__('No exams found. Please create an exam first.', 'result-spark-engine'); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Subject Type Selection -->
                <div style="flex: 1; min-width: 200px; display: none;" id="rse-subject-type-container">
                    <label for="filter_subject_type" style="display: block; margin-bottom: 8px; font-weight: 600; color: #23282d; font-size: 14px;">
                        <?php echo esc_html__('Subject Type', 'result-spark-engine'); ?>
                    </label>
                    <select name="filter_subject_type" id="filter_subject_type" class="regular-text" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;">
                        <option value=""><?php echo esc_html__('— Select Type —', 'result-spark-engine'); ?></option>
                        <option value="compulsory"><?php echo esc_html__('Compulsory', 'result-spark-engine'); ?></option>
                        <option value="departmental"><?php echo esc_html__('Departmental Subject', 'result-spark-engine'); ?></option>
                    </select>
                </div>

                <!-- Department Selection -->
                <div style="flex: 1; min-width: 200px; display: none;" id="rse-department-container">
                    <label for="filter_department" style="display: block; margin-bottom: 8px; font-weight: 600; color: #23282d; font-size: 14px;">
                        <?php echo esc_html__('Select Department', 'result-spark-engine'); ?>
                    </label>
                    <select name="filter_department" id="filter_department" class="regular-text" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;">
                        <option value="0"><?php echo esc_html__('— Select Department —', 'result-spark-engine'); ?></option>
                    </select>
                </div>

                <!-- Subject Selection -->
                <div style="flex: 1; min-width: 200px; display: none;" id="rse-subject-container">
                    <label for="filter_subject" style="display: block; margin-bottom: 8px; font-weight: 600; color: #23282d; font-size: 14px;">
                        <?php echo esc_html__('Select Subject', 'result-spark-engine'); ?>
                    </label>
                    <select name="filter_subject" id="filter_subject" class="regular-text" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;">
                        <option value="0"><?php echo esc_html__('— Select Subject —', 'result-spark-engine'); ?></option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div style="flex-shrink: 0;">
                    <button type="button" class="button button-primary" id="rse-load-students" style="margin-right: 10px; padding: 8px 16px; font-size: 14px;">
                        <?php echo esc_html__('Load Students', 'result-spark-engine'); ?>
                    </button>
                    <button type="button" class="button" id="rse-clear-filters" style="padding: 8px 16px; font-size: 14px;">
                        <?php echo esc_html__('Clear', 'result-spark-engine'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Students Table for Mark Entry -->
    <div id="rse-mark-entry-table-container" style="display: none; margin-top: 20px;">
        <div class="rse-mark-entry-table" style="background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 4px; overflow: hidden;">
            <form method="post" action="" id="rse-mark-entry-form">
                <?php wp_nonce_field('rse_save_marks', 'rse_marks_nonce'); ?>
                <input type="hidden" name="action" value="rse_save_marks">
                <input type="hidden" name="exam_id" id="rse-form-exam-id" value="">
                <input type="hidden" name="subject_id" id="rse-form-subject-id" value="">
                
                <div style="padding: 20px; border-bottom: 1px solid #ddd; background: #f9f9f9;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <h2 style="margin: 0; font-size: 18px; color: #23282d;" id="rse-form-title">
                            <?php echo esc_html__('Enter Marks', 'result-spark-engine'); ?>
                        </h2>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input 
                                type="text" 
                                id="rse-student-search" 
                                placeholder="<?php echo esc_attr__('Search by name or roll number...', 'result-spark-engine'); ?>" 
                                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px; min-width: 250px;"
                            />
                            <button type="button" class="button" id="rse-clear-search" style="padding: 8px 12px; font-size: 13px; display: none;">
                                <?php echo esc_html__('Clear', 'result-spark-engine'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="rse-students-table-container" style="padding: 20px;">
                    <!-- Students table will be loaded here via AJAX -->
                </div>

                <div id="rse-pagination-container" style="padding: 15px 20px; border-top: 1px solid #ddd; background: #f9f9f9; text-align: center;">
                    <!-- Pagination will be loaded here -->
                </div>

                <div style="padding: 20px; border-top: 1px solid #ddd; background: #f9f9f9; text-align: right;">
                    <button type="submit" class="button button-primary button-large" id="rse-save-marks-btn" style="padding: 10px 20px; font-size: 14px;">
                        <?php echo esc_html__('Save Marks', 'result-spark-engine'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Subject Status List -->
    <div id="rse-subjects-status-container" style="display: none; margin-top: 20px;">
        <div class="rse-subjects-status" style="background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 4px; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid #ddd; background: #f9f9f9; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0; font-size: 18px; color: #23282d;">
                    <?php echo esc_html__('Subject Mark Entry Status', 'result-spark-engine'); ?>
                </h2>
                <button type="button" class="button" id="rse-refresh-subjects-status" style="padding: 6px 12px; font-size: 13px;">
                    <?php echo esc_html__('Refresh', 'result-spark-engine'); ?>
                </button>
            </div>
            
            <div id="rse-subjects-status-content" style="padding: 20px;">
                <!-- Subject list will be loaded here -->
            </div>
            
            <div id="rse-generate-result-container" style="padding: 20px; border-top: 1px solid #ddd; background: #f0f8ff; text-align: center; display: none;">
                <p style="margin: 0 0 15px 0; font-size: 14px; color: #0073aa; font-weight: 600;">
                    <?php echo esc_html__('All subjects have been marked! You can now generate results.', 'result-spark-engine'); ?>
                </p>
                <div style="display: flex; gap: 15px; justify-content: center; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="button button-primary button-large" id="rse-generate-result-btn" style="padding: 12px 30px; font-size: 16px;">
                        <?php echo esc_html__('Generate Result', 'result-spark-engine'); ?>
                    </button>
                    <button type="button" class="button button-secondary button-large" id="rse-clear-result-btn" style="padding: 12px 30px; font-size: 16px; color: #dc3232; border-color: #dc3232;">
                        <?php echo esc_html__('Clear Results', 'result-spark-engine'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- No Data Message -->
    <div id="rse-no-data-message" class="notice notice-info" style="margin: 20px 0; padding: 12px; border-left: 4px solid #00a0d2; background: #fff; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <p style="margin: 0; font-size: 14px;"><?php echo esc_html__('Please select an exam to view subject status and start mark entry.', 'result-spark-engine'); ?></p>
    </div>
</div>

<script type="text/javascript">
// Ensure jQuery is loaded and script is available
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        console.log('Mark Entry page loaded');
        console.log('rseMarkEntry object:', typeof rseMarkEntry !== 'undefined' ? rseMarkEntry : 'NOT DEFINED');
        
        // Fallback: If rseMarkEntry is not defined, try to load it
        if (typeof rseMarkEntry === 'undefined') {
            console.error('rseMarkEntry is not defined. Scripts may not be loaded.');
            alert('Warning: Mark entry scripts may not be loaded. Please refresh the page.');
        }
    });
} else {
    console.error('jQuery is not loaded');
}
</script>
