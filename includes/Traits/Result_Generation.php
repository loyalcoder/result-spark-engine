<?php

namespace Result_Spark_Engine\Traits;

if (!defined('ABSPATH')) {
    exit;
}

trait Result_Generation
{
    /* ---------------------------------
     * BASIC HELPERS
     * --------------------------------- */

    protected function get_subject_name_by_id(int $subject_id): string
    {
        $post = get_post($subject_id);
        return $post ? $post->post_title : '';
    }

    protected function get_student_name_by_id(int $student_id): string
    {
        $post = get_post($student_id);
        return $post ? $post->post_title : '';
    }

    /* ---------------------------------
     * SUBJECT GRADE & GPA
     * --------------------------------- */

     protected function calculate_grade_point(float $marks, int $subject_id): array
     {
         $grade_terms = get_the_terms($subject_id, 'grade');
     
         if (empty($grade_terms) || is_wp_error($grade_terms)) {
             return ['grade' => 'F', 'gpa' => 0.00];
         }
     
         $grade_ranges = get_term_meta(
             $grade_terms[0]->term_id,
             '_rse_grade_ranges',
             true
         );
     
         if (empty($grade_ranges) || !is_array($grade_ranges)) {
             return ['grade' => 'F', 'gpa' => 0.00];
         }
     
         foreach ($grade_ranges as $range) {
             $min = (float) $range['min_mark'];
             $max = (float) $range['max_mark'];
     
             if ($marks >= $min && $marks <= $max) {
                 return [
                     'grade' => sanitize_text_field($range['grade']),
                     'gpa'   => (float) $range['point'],
                 ];
             }
         }
     
         // Fallback (safety net)
         return [
             'grade' => 'F',
             'gpa'   => 0.00,
         ];
     }
     

    /* ---------------------------------
     * FINAL GPA → FINAL GRADE
     * --------------------------------- */

    protected function calculate_final_grade(float $gpa, int $student_id = 0): string
    {
        // Get student's class_level to use custom thresholds
        $class_level_id = 0;
        if ($student_id > 0) {
            $class_level_terms = wp_get_post_terms($student_id, 'class_level', ['fields' => 'ids']);
            if (!empty($class_level_terms) && !is_wp_error($class_level_terms)) {
                $class_level_id = $class_level_terms[0];
            }
        }

        // Get custom thresholds from class_level term meta
        $thresholds = [];
        if ($class_level_id > 0) {
            $thresholds = get_term_meta($class_level_id, '_rse_final_grade_thresholds', true);
        }

        // If custom thresholds exist, use them
        if (!empty($thresholds) && is_array($thresholds)) {
            // Thresholds should be sorted by min_gpa descending (highest first)
            foreach ($thresholds as $threshold) {
                $min_gpa = floatval($threshold['min_gpa'] ?? 0);
                $grade = sanitize_text_field($threshold['grade'] ?? '');
                
                if (!empty($grade) && $gpa >= $min_gpa) {
                    return $grade;
                }
            }
        }

        // Fallback to default thresholds if no custom thresholds found
        if ($gpa >= 5)   return 'A+';
        if ($gpa >= 4)   return 'A';
        if ($gpa >= 3.5) return 'A-';
        if ($gpa >= 3)   return 'B';
        if ($gpa >= 2)   return 'C';
        if ($gpa >= 1)   return 'D';

        return 'F';
    }

    /* ---------------------------------
     * TOTAL MARKS (STUDENT)
     * --------------------------------- */

    protected function calculate_total_marks(array $subjects): int
    {
        $total = 0;

        foreach ($subjects as $subject) {
            $total += array_sum($subject['marks']);
        }

        return $total;
    }

    /* ---------------------------------
     * FINAL RESULT BUILDER
     * --------------------------------- */

    protected function generate_final_result(array $subjects, int $student_id = 0): array
    {
        $total_marks  = 0;
        $total_gpa    = 0;
        $subject_count = 0; // Count only regular subjects for GPA calculation
        $has_failed_subject = false; // Track if any regular subject failed
        
        // Get student's non-major subjects (includes both non-major and potentially 4th subjects)
        $non_major_subjects = [];
        if ($student_id > 0) {
            $non_major_subjects = get_post_meta($student_id, '_rse_non_major_subjects', true);
            if (!is_array($non_major_subjects)) {
                $non_major_subjects = [];
            }
        }
        
        // Separate no major subjects from regular subjects
        $no_major_marks = 0;
        $no_major_gpa = 0;

        foreach ($subjects as $key => $subject) {

            $subject_total = array_sum($subject['marks']);
            $subject_id    = $subject['subject_id'];
            
            // Check if this subject is in student's non-major subjects list
            $is_no_major_type = in_array($subject_id, $non_major_subjects);
            
            // Calculate grade and GPA for this subject FIRST based on total marks
            $gradeData = $this->calculate_grade_point($subject_total, $subject_id);
            
            // Check if grade is F or GPA is 0.00 (this is the primary failure indicator)
            $grade_letter = strtoupper(trim($gradeData['grade']));
            $is_grade_f = ($grade_letter === 'F' || $grade_letter === 'FAILED');
            $is_gpa_zero = ($gradeData['gpa'] == 0.00);
            
            // If grade is F or GPA is 0, subject has failed (based on total marks)
            // Only check mandatory part failures if the grade is NOT F (i.e., total marks passed)
            $subject_is_failed = $is_grade_f || $is_gpa_zero;
            
            // If grade is not F, check if mandatory parts failed
            // But only fail if the total marks are also below the minimum passing grade range
            if (!$subject_is_failed) {
                $mandatory_part_failed = $this->check_subject_failed_checker($subject['marks'], $subject_id);
                
                // If mandatory part failed, check if total marks are still in a passing range
                // Get the minimum passing mark from grade ranges
                $min_passing_mark = $this->get_minimum_passing_mark($subject_id);
                
                if ($mandatory_part_failed && $min_passing_mark > 0 && $subject_total < $min_passing_mark) {
                    // Mandatory part failed AND total is below minimum passing mark
                    $subject_is_failed = true;
                }
                // If mandatory part failed but total is above minimum passing mark, subject still passes
            }
            
            // For no major subjects: failure doesn't cause overall failure
            if ($subject_is_failed && !$is_no_major_type) {
                $has_failed_subject = true;
                // Mark subject as failed
                $gradeData['grade'] = 'F';
                $gradeData['gpa'] = 0.00;
            } elseif ($subject_is_failed && $is_no_major_type) {
                // No major subject failed - mark as F but don't cause overall failure
                $gradeData['grade'] = 'F';
                $gradeData['gpa'] = 0.00;
            }

            $subjects[$key]['subject_name'] = $this->get_subject_name_by_id($subject['subject_id']);
            $subjects[$key]['total']        = $subject_total;
            $subjects[$key]['grade']        = $gradeData['grade'];
            $subjects[$key]['gpa']          = $gradeData['gpa'];
            $subjects[$key]['is_no_major']  = $is_no_major_type;

            // Handle no major subjects separately
            if ($is_no_major_type) {
                // Get minimum addable mark and point from subject settings
                $min_addable_mark = floatval(get_post_meta($subject_id, '_rse_min_addable_mark', true) ?: 0);
                $min_point = floatval(get_post_meta($subject_id, '_rse_min_point', true) ?: 0);
                
                // Only add if marks meet minimum requirement
                if ($subject_total >= $min_addable_mark) {
                    $no_major_marks += $subject_total;
                }
                
                // Only add GPA if it meets minimum point requirement
                if ($gradeData['gpa'] >= $min_point) {
                    $no_major_gpa += $gradeData['gpa'];
                }
            } else {
                // Regular subjects - count for GPA calculation
                $total_marks += $subject_total;
                $total_gpa   += $gradeData['gpa'];
                $subject_count++;
            }
        }

        // Calculate final GPA from regular subjects only
        $final_gpa = $subject_count > 0 ? round($total_gpa / $subject_count, 2) : 0;

        // Add no major subject marks and points after main calculation
        $total_marks += $no_major_marks;
        $total_gpa += $no_major_gpa;

        // If any regular subject failed, mark overall result as "F" with 0.00 GPA
        // No major subject failures don't cause overall failure
        if ($has_failed_subject) {
            $final_gpa = 0.00;
            $final_grade = 'F';
        } else {
            $final_grade = $this->calculate_final_grade($final_gpa, $student_id);
        }

        return [
            'total_mark' => $total_marks,
            'gpa'        => $final_gpa,
            'grade'      => $final_grade,
            'subjects'   => $subjects,
        ];
    }

    protected function save_student_result(array $data, int $exam_id): bool
{
    global $wpdb;

    $table = $wpdb->prefix . 'rse_result';

    $now = current_time('mysql');

    $insert_data = [
        'student_id'       => (int) $data['student_id'],
        'roll'             => $data['roll'] ?? null,
        'total_mark'       => (float) $data['total_mark'],
        'grade_point'      => (float) $data['gpa'],
        'grade'            => $data['grade'],
        'exam_id'          => (int) $exam_id,
        'mark_in_details'  => wp_json_encode($data['subjects']),
        'updated_at'       => $now,
    ];

    $formats = [
        '%d', // student_id
        '%s', // roll
        '%f', // total_mark
        '%f', // grade_point
        '%s', // grade
        '%d', // exam_id
        '%s', // mark_in_details
        '%s', // updated_at
    ];

    // 🔁 UPSERT (because of UNIQUE(student_id, exam_id))
    $existing_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$table} WHERE student_id = %d AND exam_id = %d",
            $data['student_id'],
            $exam_id
        )
    );

    if ($existing_id) {

        return (bool) $wpdb->update(
            $table,
            $insert_data,
            ['id' => $existing_id],
            $formats,
            ['%d']
        );

    } else {

        $insert_data['created_at'] = $now;
        $formats[] = '%s';

        return (bool) $wpdb->insert($table, $insert_data, $formats);
    }
}
    /**
     * Get minimum passing mark from grade ranges
     * 
     * @param int $subject_id Subject ID
     * @return float Minimum passing mark (0 if not found)
     */
    protected function get_minimum_passing_mark(int $subject_id): float
    {
        $grade_terms = get_the_terms($subject_id, 'grade');
        
        if (empty($grade_terms) || is_wp_error($grade_terms)) {
            return 0;
        }
        
        $grade_ranges = get_term_meta(
            $grade_terms[0]->term_id,
            '_rse_grade_ranges',
            true
        );
        
        if (empty($grade_ranges) || !is_array($grade_ranges)) {
            return 0;
        }
        
        // Find the minimum mark that gives a passing grade (not F)
        $min_passing = null;
        foreach ($grade_ranges as $range) {
            $grade = strtoupper(trim($range['grade'] ?? ''));
            if ($grade !== 'F' && $grade !== 'FAILED') {
                $min_mark = (float) ($range['min_mark'] ?? 0);
                if ($min_passing === null || $min_mark < $min_passing) {
                    $min_passing = $min_mark;
                }
            }
        }
        
        return $min_passing !== null ? (float) $min_passing : 0;
    }

    protected function check_subject_failed_checker(array $marks, int $subject_id): bool
    {
        $mark_breakdown = get_post_meta($subject_id, '_rse_mark_breakdown', true);

        // If no breakdown found, assume not failed
        if (empty($mark_breakdown) || !is_array($mark_breakdown)) {
            return false;
        }

        foreach ($mark_breakdown as $index => $part) {

            // Skip if mark not provided for this part
            if (!isset($marks[$index])) {
                continue;
            }

            // Check only mandatory pass components
            if (!empty($part['need_to_pass'])) {

                $obtained_mark = (float) $marks[$index];
                $pass_mark     = (float) ($part['pass_mark'] ?? 0);

                if ($obtained_mark < $pass_mark) {
                    // Mandatory part failed
                    return true;
                }
            }
        }

        // No mandatory part failed
        return false;
    }

}
