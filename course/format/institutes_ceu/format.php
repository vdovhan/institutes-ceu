<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * institutes_ceu course format.  Display the whole course as "institutes_ceu" made of modules.
 *
 * @package format_institutes_ceu
 * @copyright 2006 The Open University
 * @author N.D.Freear@open.ac.uk, and others.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

// Horrible backwards compatible parameter aliasing..
if ($topic = optional_param('topic', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing..

$context = context_course::instance($course->id);

if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// make sure all sections are created
$format = course_get_format($course);
$course = $format->get_course();
course_create_sections_if_missing($course, range(0, $course->numsections));
$format->course_create_format_sections_if_missing();

$renderer = $PAGE->get_renderer('format_institutes_ceu');
$course_sections = $renderer->get_course_sections($course);
$parent = optional_param('parent', 0, PARAM_INT);
if (!empty($displaysection)) {
    $currentsection = $DB->get_record('course_sections', array('section'=>$displaysection, 'course'=>$course->id));
}

if ($PAGE->user_is_editing()){
    $courseview = get_user_preferences('courseview_'.$course->id, 'institutes');
    if ($courseview == 'institutes') {
        $renderer->print_editing_sections_page($course, null, null, null, null, $displaysection);   
    } else {
        $renderer->print_editing_topicview_page($course, null, null, null, null, $displaysection);   
    }
} else {
    if (!empty($displaysection)) {
        $currentsection->level = (isset($course_sections[$currentsection->id]->level)) ? $course_sections[$currentsection->id]->level : 0;
        echo html_writer::start_tag('div', array('class' => 'course-level-3'));
            $renderer->print_innerlevel_section_page($course, null, null, null, null, $displaysection);
        echo html_writer::end_tag('div');
    } else {
        echo html_writer::start_tag('div', array('class' => 'course-level-0'));
            $renderer->print_mainlevel_section_page($course, null, null, null, null);
        echo html_writer::end_tag('div');
    }
}

// Include course format js module
$PAGE->requires->js('/course/format/institutes_ceu/format.js');
