<?php

function local_gradebook_ceu_display($course){
    global $CFG, $USER;
    
    $output = '';
    
    $course = course_get_format($course)->get_course();
    
    $modinfo = get_fast_modinfo($course);
    $course_quizzes = local_gradebook_ceu_get_course_quizzes($course);
    
    if (count($course_quizzes)){
        $output .= html_writer::start_tag('div', array('class'=>'course-sections'));

            $output .= html_writer::start_tag('table', array('class'=>'course-sections'));
                $output .= html_writer::start_tag('thead');
                    $output .= html_writer::start_tag('tr');
                        $output .= html_writer::tag('th', get_string('quizname', 'local_gradebook_ceu'), array('class'=>'name'));
                        $output .= html_writer::tag('th', get_string('status', 'local_gradebook_ceu'), array('class'=>'hidden_mobile'));
                        $output .= html_writer::tag('th', get_string('attempts', 'local_gradebook_ceu'), array('class'=>'hidden_mobile'));
                        $output .= html_writer::tag('th', get_string('grade', 'local_gradebook_ceu'), array('class'=>'grade'));
                    $output .= html_writer::end_tag('tr');
                $output .= html_writer::end_tag('thead');


                $output .= html_writer::start_tag('tbody');
                    $output .= local_gradebook_ceu_get_quizzes_list($course, $course_quizzes);
                $output .= html_writer::end_tag('tbody');            

            $output .= html_writer::end_tag('table');
        $output .= html_writer::end_tag('div');
    } else {
        $output = html_writer::tag('div', get_string('nothingtodisplay', 'local_gradebook_ceu'), array('class'=>'alert alert-success'));
    }
    
    return $output;
}

function local_gradebook_ceu_get_quizzes_list($course, $course_quizzes){
    global $DB, $CFG, $USER, $PAGE;
    $output = '';
    
    $renderer = $PAGE->get_renderer('format_institutes_ceu');
    
    if (isset($course_quizzes)){
        $i = 1;
        foreach($course_quizzes as $quiz){
            $parentsection = $renderer->get_parent_section($course, $quiz->section);
            
            $output .= html_writer::start_tag('tr', array('class'=>'quiz-row'));
                $sectionname = html_writer::tag('strong', ($parentsection->sectiontype > 0) ? $quiz->sectionname : get_string('sectionnumber', 'format_institutes_ceu', $renderer->get_section_number($course, $quiz->section)));
                $output .= html_writer::tag('td', html_writer::link(new moodle_url('/mod/quiz/view.php',              array('id' => $quiz->cmid)), $sectionname.': '.$quiz->name), array('class'=>'name'));
                $output .= html_writer::tag('td', $quiz->status, array('class'=>'hidden_mobile'));
                $output .= html_writer::tag('td', $quiz->attempts, array('class'=>'hidden_mobile'));
                $output .= html_writer::tag('td', $quiz->grade, array('class'=>'grade'));
            $output .= html_writer::end_tag('tr');
            $i++;
        }
    }
    
    return $output;
}

function local_gradebook_ceu_get_course_quizzes($course){
    global $DB, $CFG, $USER;
    
    $items = array();
    $quizzes = $DB->get_records_sql("SELECT q.id, q.name, cm.section, cm.id as cmid, a.userattempts, cmc.completionstate, g.grade as quizgrade, cs.name as sectionname 
                                            FROM {course_modules} cm 
                                                LEFT JOIN {modules} m ON m.id = cm.module 
                                                LEFT JOIN {quiz} q ON q.id = cm.instance 
                                                LEFT JOIN (SELECT quiz, COUNT(id) as userattempts FROM {quiz_attempts} WHERE userid = :userid1 GROUP BY quiz) a ON a.quiz = q.id 
                                                LEFT JOIN {course_modules_completion} cmc ON cmc.coursemoduleid = cm.id AND cmc.userid = :userid2
                                                LEFT JOIN {quiz_grades} g ON g.quiz = q.id AND g.userid = :userid3
                                                LEFT JOIN {course_sections} cs ON cs.id = cm.section
                                        WHERE   cm.course = :course 
                                                AND m.name = :modname
                                            ORDER BY cs.section", 
                                    array('userid1'=>$USER->id, 'userid2'=>$USER->id, 'userid3'=>$USER->id, 'course'=>$course->id, 'modname'=>'quiz'));
    if (count($quizzes)){
        foreach ($quizzes as $quiz){
           
            $quiz->attempts = ($quiz->userattempts) ? $quiz->userattempts : 0;
            $quiz->status = ($quiz->completionstate) ? get_string('complete', 'local_gradebook_ceu') : get_string('incomplete', 'local_gradebook_ceu');
            
            $mygrade = $quiz->quizgrade;
            $grading_info = grade_get_grades($course->id, 'mod', 'quiz', $quiz->id, $USER->id);
            $grade_item = $grading_info->items[0];
            if (isset($grade_item->grades[$USER->id])) {
                $grade = $grade_item->grades[$USER->id];
                if ($grade->overridden) {
                    $mygrade = $grade->grade;
                }
            }
            $quiz->grade = local_gradebook_ceu_format_gradevalue($mygrade, $grade_item);
            
            $items[$quiz->id] = $quiz;
        }
    }
    
    return $items;
}

/**
 * Returns a percentage representation of a grade value
 *
 * @param float $value The grade value
 * @param object $grade_item Grade item object
 * @param int $decimals The number of decimal places 
 * @return string
 */
function local_gradebook_ceu_format_gradevalue($value, $grade_item) {
    if ($value === null){
        return '-';
    }
    
    $min = $grade_item->grademin;
    $max = $grade_item->grademax;
    if ($min == $max) {
        return '-';
    }
    $value = $value + 0;
    $percentage = (($value-$min)*100)/($max-$min);
    return format_float($percentage, 0).'%';
}

function local_gradebook_ceu_get_course_sections($course) {
    global $PAGE, $DB;

    $sections = array();
    $allsections = $DB->get_records_sql("SELECT s.*, fs.parent, fs.level, fs.parentssequence FROM {course_sections} s LEFT JOIN {course_format_sections} fs ON fs.sectionid = s.id AND fs.courseid = s.course WHERE s.course = $course->id AND s.section > 0");
    if (count($allsections)){
        foreach($allsections as $section){
            $sections[$section->parent][$section->id] = $section;
        }
    }

    return $sections;
}

