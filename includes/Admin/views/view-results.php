<?php
/**
 * View Results Page Template
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
    'order' => 'DESC',
]);
?>
<div class="wrap rse-view-results">
    <h1 class="wp-heading-inline"><?php echo esc_html__('View Results', 'result-spark-engine'); ?></h1>
    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="rse-filters" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 4px;">
        <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label for="rse-exam-select" style="display: block; margin-bottom: 5px; font-weight: 600;">
                    <?php echo esc_html__('Select Exam', 'result-spark-engine'); ?>
                </label>
                <select id="rse-exam-select" style="width: 100%; padding: 8px;">
                    <option value=""><?php echo esc_html__('-- Select Exam --', 'result-spark-engine'); ?></option>
                    <?php foreach ($exams as $exam) : ?>
                        <option value="<?php echo esc_attr($exam->ID); ?>">
                            <?php echo esc_html($exam->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <label for="rse-department-filter" style="display: block; margin-bottom: 5px; font-weight: 600;">
                    <?php echo esc_html__('Filter by Department', 'result-spark-engine'); ?>
                </label>
                <select id="rse-department-filter" style="width: 100%; padding: 8px;">
                    <option value=""><?php echo esc_html__('All Departments', 'result-spark-engine'); ?></option>
                </select>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <label for="rse-sort-by" style="display: block; margin-bottom: 5px; font-weight: 600;">
                    <?php echo esc_html__('Sort By', 'result-spark-engine'); ?>
                </label>
                <select id="rse-sort-by" style="width: 100%; padding: 8px;">
                    <option value="default"><?php echo esc_html__('Default', 'result-spark-engine'); ?></option>
                    <option value="mark_desc"><?php echo esc_html__('Highest by Mark', 'result-spark-engine'); ?></option>
                    <option value="mark_asc"><?php echo esc_html__('Lowest by Mark', 'result-spark-engine'); ?></option>
                    <option value="gpa_desc"><?php echo esc_html__('Highest by GPA', 'result-spark-engine'); ?></option>
                    <option value="gpa_asc"><?php echo esc_html__('Lowest by GPA', 'result-spark-engine'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Results Container -->
    <div id="rse-results-container" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 4px;">
        <div id="rse-results-loading" style="text-align: center; padding: 40px; display: none;">
            <span class="spinner is-active" style="float: none;"></span>
            <p><?php echo esc_html__('Loading results...', 'result-spark-engine'); ?></p>
        </div>
        <div id="rse-results-content" style="display: none;">
            <!-- Results table will be loaded here -->
        </div>
        <div id="rse-results-empty" style="text-align: center; padding: 40px; display: none;">
            <p><?php echo esc_html__('Please select an exam to view results.', 'result-spark-engine'); ?></p>
        </div>
    </div>

    <!-- Pagination -->
    <div id="rse-results-pagination" style="margin-top: 20px; text-align: center; display: none;">
        <!-- Pagination will be loaded here -->
    </div>
</div>

<?php
/**
 * Action hook for pro version to enqueue scripts/styles for view results page
 * 
 * This hook is fired when the view results page is loaded.
 * Pro version can use this to enqueue additional assets.
 * 
 * @hooked Result_Spark_Engine_Pro\Admin\View_Results - enqueue_assets
 */
do_action('rse_view_results_page_loaded');
?>

<?php
/**
 * Action hook for pro version to enqueue scripts/styles for view results page
 * 
 * @hooked Result_Spark_Engine_Pro\Admin\View_Results - enqueue_assets
 */
do_action('rse_view_results_page_loaded');
?>

