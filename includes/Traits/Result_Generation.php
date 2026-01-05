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

    protected function calculate_grade_point(float $marks): array
    {
        if ($marks >= 80) return ['grade' => 'A+', 'gpa' => 5.00];
        if ($marks >= 70) return ['grade' => 'A',  'gpa' => 4.00];
        if ($marks >= 60) return ['grade' => 'A-', 'gpa' => 3.50];
        if ($marks >= 50) return ['grade' => 'B',  'gpa' => 3.00];
        if ($marks >= 40) return ['grade' => 'C',  'gpa' => 2.00];
        if ($marks >= 33) return ['grade' => 'D',  'gpa' => 1.00];

        return ['grade' => 'F', 'gpa' => 0.00];
    }

    /* ---------------------------------
     * FINAL GPA → FINAL GRADE
     * --------------------------------- */

    protected function calculate_final_grade(float $gpa): string
    {
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

    protected function generate_final_result(array $subjects): array
    {
        $total_marks  = 0;
        $total_gpa    = 0;
        $subject_count = count($subjects);

        foreach ($subjects as $key => $subject) {

            $subject_total = array_sum($subject['marks']);
            $gradeData     = $this->calculate_grade_point($subject_total);

            $subjects[$key]['subject_name'] = $this->get_subject_name_by_id($subject['subject_id']);
            $subjects[$key]['total']        = $subject_total;
            $subjects[$key]['grade']        = $gradeData['grade'];
            $subjects[$key]['gpa']          = $gradeData['gpa'];

            $total_marks += $subject_total;
            $total_gpa   += $gradeData['gpa'];
        }

        $final_gpa = $subject_count > 0 ? round($total_gpa / $subject_count, 2) : 0;

        return [
            'total_mark' => $total_marks,
            'gpa'        => $final_gpa,
            'grade'      => $this->calculate_final_grade($final_gpa),
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

}
