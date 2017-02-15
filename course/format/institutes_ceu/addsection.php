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
 * This script allows the number of sections in a course to be increased
 * or decreased, redirecting to the course page.
 *
 * @package core_course
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');

$courseid = required_param('courseid', PARAM_INT);
$parentid = optional_param('parent', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$parent = $DB->get_record('course_format_sections', array('sectionid' => $parentid, 'courseid'=>$course->id, 'format'=>'institutes_ceu'));
$courseformatoptions = course_get_format($course)->get_format_options();
$index = $courseformatoptions['numsections'] + 1;
$PAGE->set_url('/course/format/institutes_ceu/addsection.php', array('courseid' => $courseid));

require_login($course);
require_capability('moodle/course:update', context_course::instance($course->id));

$format_renderer = $PAGE->get_renderer('format_institutes_ceu');

update_course((object)array('id' => $course->id, 'numsections' => $index));

$section = new stdClass();
$section->course = $courseid;
$section->section = $index;
$section->name = get_string('sectionname', 'format_institutes_ceu').' '.$index;
$section->visible = 1;
$section->summary = '';
$section->summaryformat = 1;
$section->sequence = '';

$section->id = $DB->insert_record('course_sections', $section);

$params = new stdClass();
$params->parent = $parentid;
if ($params->parent > 0){
    $params->level = $parent->level+1;
    $params->parentssequence = $parentid;
    if ($parent->parent > 0){
        $params->parentssequence = $parent->parentssequence.','.$parentid;                    
    }
} else {
    $params->parentssequence = '';
    $params->level = 0;
}
course_get_format($course)->course_save_format_section($section, $params);

if (isset($parent->sectionid)){
    $modinfo = get_fast_modinfo($course);
    course_get_format($course)->sort_root_sections($course, $modinfo, $format_renderer);
}
rebuild_course_cache($courseid, true);

if (isset($parent->sectionid)){
    $url = new moodle_url('/course/editsection.php', array("id"=>$section->id, "sr"=>$parent->section, "parent"=>$parent->sectionid));
} else {
    $url = new moodle_url('/course/editsection.php', array("id"=>$section->id));
}

redirect($url);
