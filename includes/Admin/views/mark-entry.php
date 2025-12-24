<?php
/**
 * Mark Entry Page Template
 *
 * @package Result_Spark_Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission (non-AJAX fallback)
if (isset($_POST['rse_marks_nonce']) && wp_verify_nonce($_POST['rse_marks_nonce'], 'rse_save_marks')) {
    if (current_user_can('manage_options')) {
        $exam_id = isset($_POST['exam_id']) ? absint($_POST['exam_id']) : 0;
        $subject_id = isset($_POST['subject_id']) ? absint($_POST['subject_id']) : 0;
        $marks = isset($_POST['marks']) ? $_POST['marks'] : [];
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : [];

        if ($exam_id > 0 && $subject_id > 0) {
            $marks_key = '_rse_marks_' . $exam_id . '_' . $subject_id;
            $saved_count = 0;

            foreach ($marks as $student_id => $mark_value) {
                $student_id = absint($student_id);
                $mark_value = sanitize_text_field($mark_value);
                $remark_value = isset($remarks[$student_id]) ? sanitize_text_field($remarks[$student_id]) : '';

                if ($student_id > 0) {
                    if (!empty($mark_value)) {
                        update_post_meta($student_id, $marks_key, $mark_value);
                    } else {
                        delete_post_meta($student_id, $marks_key);
                    }

                    if (!empty($remark_value)) {
                        update_post_meta($student_id, $marks_key . '_remarks', $remark_value);
                    } else {
                        delete_post_meta($student_id, $marks_key . '_remarks');
                    }

                    $saved_count++;
                }
            }

            if ($saved_count > 0) {
                echo '<div class="notice notice-success is-dismissible"><p>' . 
                     sprintf(esc_html__('Marks saved successfully for %d student(s).', 'result-spark-engine'), $saved_count) . 
                     '</p></div>';
            }
        }
    }
}

// Get filter values
$filter_exam = isset($_GET['filter_exam']) ? absint($_GET['filter_exam']) : 0;
$filter_subject = isset($_GET['filter_subject']) ? absint($_GET['filter_subject']) : 0;

// Get all exams for filter dropdown
$exams = get_posts([
    'post_type' => 'exam',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
]);

// Get subjects if exam is selected (using subject post type)
$subjects = [];
if ($filter_exam > 0) {
    // Get exam's class level
    $exam_class_terms = wp_get_post_terms($filter_exam, 'class_level', ['fields' => 'ids']);
    
    if (!empty($exam_class_terms)) {
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
        
        $subjects = $subject_posts;
    }
}

// Get students if both exam and subject are selected
$students = [];
if ($filter_exam > 0 && $filter_subject > 0) {
    // Get exam's class level
    $exam_class_terms = wp_get_post_terms($filter_exam, 'class_level', ['fields' => 'ids']);
    
    if (!empty($exam_class_terms)) {
        // Get subject post to check its class level
        $subject_post = get_post($filter_subject);
        
        if ($subject_post && 'subject' === $subject_post->post_type) {
            // Get subject's class level
            $subject_class_terms = wp_get_post_terms($filter_subject, 'class_level', ['fields' => 'ids']);
            
            // Get students with matching class level
            $args = [
                'post_type' => 'students',
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
            ];
            
            $students = get_posts($args);
        }
    }
}
?>

<div class="wrap rse-mark-entry">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Mark Entry', 'result-spark-engine'); ?></h1>
    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="rse-filters" style="background: #fff; padding: 15px; margin: 20px 0; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <form method="get" action="" id="rse-mark-entry-filters">
            <input type="hidden" name="page" value="rse-mark-entry">
            
            <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label for="filter_exam" style="display: block; margin-bottom: 5px; font-weight: 600;">
                        <?php echo esc_html__('Select Exam', 'result-spark-engine'); ?>
                    </label>
                    <select name="filter_exam" id="filter_exam" class="regular-text" required>
                        <option value="0"><?php echo esc_html__('— Select Exam —', 'result-spark-engine'); ?></option>
                        <?php foreach ($exams as $exam) : ?>
                            <option value="<?php echo esc_attr($exam->ID); ?>" <?php selected($filter_exam, $exam->ID); ?>>
                                <?php echo esc_html($exam->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="flex: 1; min-width: 200px;">
                    <label for="filter_subject" style="display: block; margin-bottom: 5px; font-weight: 600;">
                        <?php echo esc_html__('Select Subject', 'result-spark-engine'); ?>
                    </label>
                    <select name="filter_subject" id="filter_subject" class="regular-text" <?php echo $filter_exam > 0 ? '' : 'disabled'; ?>>
                        <option value="0"><?php echo esc_html__('— Select Subject —', 'result-spark-engine'); ?></option>
                        <?php if ($filter_exam > 0 && !empty($subjects)) : ?>
                            <?php foreach ($subjects as $subject) : ?>
                                <option value="<?php echo esc_attr($subject->ID); ?>" <?php selected($filter_subject, $subject->ID); ?>>
                                    <?php echo esc_html($subject->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if ($filter_exam > 0 && empty($subjects)) : ?>
                        <p class="description" style="color: #dc3232; margin-top: 5px;">
                            <?php echo esc_html__('No subjects found for this exam\'s class.', 'result-spark-engine'); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div>
                    <button type="submit" class="button button-primary">
                        <?php echo esc_html__('Load Students', 'result-spark-engine'); ?>
                    </button>
                    <?php if ($filter_exam > 0 || $filter_subject > 0) : ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=rse-mark-entry')); ?>" class="button">
                            <?php echo esc_html__('Clear', 'result-spark-engine'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Students Table for Mark Entry -->
    <?php if ($filter_exam > 0 && $filter_subject > 0 && !empty($students)) : ?>
        <div class="rse-mark-entry-table" style="background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <form method="post" action="" id="rse-mark-entry-form">
                <?php wp_nonce_field('rse_save_marks', 'rse_marks_nonce'); ?>
                <input type="hidden" name="action" value="rse_save_marks">
                <input type="hidden" name="exam_id" value="<?php echo esc_attr($filter_exam); ?>">
                <input type="hidden" name="subject_id" value="<?php echo esc_attr($filter_subject); ?>">
                
                <div style="padding: 15px; border-bottom: 1px solid #ddd;">
                    <h2 style="margin: 0;">
                        <?php 
                        $exam_title = get_the_title($filter_exam);
                        $subject_post = get_post($filter_subject);
                        $subject_name = $subject_post ? $subject_post->post_title : '';
                        printf(
                            esc_html__('Enter Marks: %s - %s', 'result-spark-engine'),
                            esc_html($exam_title),
                            esc_html($subject_name)
                        );
                        ?>
                    </h2>
                </div>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 60px;"><?php echo esc_html__('Photo', 'result-spark-engine'); ?></th>
                            <th><?php echo esc_html__('Student Name', 'result-spark-engine'); ?></th>
                            <th><?php echo esc_html__('Roll No.', 'result-spark-engine'); ?></th>
                            <th style="width: 150px;"><?php echo esc_html__('Marks', 'result-spark-engine'); ?></th>
                            <th style="width: 200px;"><?php echo esc_html__('Remarks', 'result-spark-engine'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student) : ?>
                            <?php
                            $student_id = $student->ID;
                            $roll_no = get_post_meta($student_id, 'roll_no', true);
                            $student_name = get_post_meta($student_id, 'student_name', true) ?: $student->post_title;
                            
                            // Get existing marks if any
                            $marks_key = '_rse_marks_' . $filter_exam . '_' . $filter_subject;
                            $existing_marks = get_post_meta($student_id, $marks_key, true);
                            $existing_remarks = get_post_meta($student_id, $marks_key . '_remarks', true);
                            
                            // Get featured image
                            $thumbnail_id = get_post_thumbnail_id($student_id);
                            $photo_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';
                            ?>
                            <tr>
                                <td>
                                    <?php if ($photo_url) : ?>
                                        <img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($student_name); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" />
                                    <?php else : ?>
                                        <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #999;">
                                            <span class="dashicons dashicons-admin-users"></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>
                                        <a href="<?php echo esc_url(get_edit_post_link($student_id)); ?>">
                                            <?php echo esc_html($student_name); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td><?php echo esc_html($roll_no ?: '-'); ?></td>
                                <td>
                                    <input 
                                        type="number" 
                                        name="marks[<?php echo esc_attr($student_id); ?>]" 
                                        value="<?php echo esc_attr($existing_marks); ?>"
                                        class="regular-text" 
                                        step="0.01"
                                        min="0"
                                        placeholder="<?php echo esc_attr__('Enter marks', 'result-spark-engine'); ?>"
                                    />
                                </td>
                                <td>
                                    <input 
                                        type="text" 
                                        name="remarks[<?php echo esc_attr($student_id); ?>]" 
                                        value="<?php echo esc_attr($existing_remarks); ?>"
                                        class="regular-text" 
                                        placeholder="<?php echo esc_attr__('Optional remarks', 'result-spark-engine'); ?>"
                                    />
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="padding: 15px; border-top: 1px solid #ddd; text-align: right;">
                    <button type="submit" class="button button-primary button-large">
                        <?php echo esc_html__('Save Marks', 'result-spark-engine'); ?>
                    </button>
                </div>
            </form>
        </div>
    <?php elseif ($filter_exam > 0 && $filter_subject > 0 && empty($students)) : ?>
        <div class="notice notice-info" style="margin: 20px 0;">
            <p><?php echo esc_html__('No students found for the selected exam and subject.', 'result-spark-engine'); ?></p>
        </div>
    <?php else : ?>
        <div class="notice notice-info" style="margin: 20px 0;">
            <p><?php echo esc_html__('Please select an exam and subject to load students for mark entry.', 'result-spark-engine'); ?></p>
        </div>
    <?php endif; ?>
</div>

