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

$courseid  = required_param('courseid', PARAM_INT);
$sectionid = required_param('section', PARAM_INT);
$action    = optional_param('action', '', PARAM_RAW);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$courseformatoptions = course_get_format($course)->get_format_options();
$PAGE->set_url('/course/format/institutes_ceu/movesection.php', array('courseid' => $courseid));

require_login($course);
require_capability('moodle/course:update', context_course::instance($course->id));

$parentid = 0;
if ($sectionid > 0){
    $currentsection = $DB->get_record('course_sections', array('id'=>$sectionid));
    $format_section = $DB->get_record('course_format_sections', array('sectionid'=>$sectionid, 'courseid'=>$course->id));
    $parentid = (isset($format_section->parent)) ? $format_section->parent : 0;
    $parent = $DB->get_record('course_sections', array('id'=>$parentid));
}

$format_renderer = $PAGE->get_renderer('format_institutes_ceu');
$modinfo = get_fast_modinfo($course);
if (isset($parent->id)){
    course_get_format($course)->sort_root_sections($course, $modinfo, $format_renderer);
}

$sections_sequense = $format_renderer->get_sections_sequense($course, $modinfo, $parentid);
$previoussection = 0; $nextsection = 0;

$sectionlist = array(); $currindex = 0;
// ROOT LEVEL
if (isset($sections_sequense[$parentid]['childs'])){
    $i = 1; $sortedsections = array();
    foreach($sections_sequense[$parentid]['childs'] as $section){
        $sortedsections[$i] = $section->id;
        if (isset($currentsection->id) and $currentsection->id == $section->id){
            $currindex = $i;
        }
        $i++;
    }
    
    if (!empty($action)){
        if ($action == 'down'){
            $newindex = $currindex+1;
        } else {
            $newindex = $currindex-1;
        }
        $temp = (isset($sortedsections[$newindex])) ? $sortedsections[$newindex] : 0;
        $sortedsections[$newindex] = $sortedsections[$currindex];
        $sortedsections[$currindex] = $temp;
    }
    
    $i = (isset($parent->section)) ? $parent->section+1 : 1;
    foreach($sortedsections as $section){
        //echo 'LEVEL 1 -- '.$section.'<hr />';
        $sectionlist[$section] = $i++;
        if (isset($sections_sequense[$section]['childs']) and count($sections_sequense[$section]['childs'])){
            foreach($sections_sequense[$section]['childs'] as $section1){
                //echo 'LEVEL 2 -- '.$section1->id.'<br />';   
                $sectionlist[$section1->id] = $i++;
                // LEVEL 2
                if (isset($sections_sequense[$section1->id]['childs']) and count($sections_sequense[$section1->id]['childs'])){
                    foreach($sections_sequense[$section1->id]['childs'] as $section2){
                        $sectionlist[$section2->id] = $i++;
                        //echo 'LEVEl 3 --'.$section2->id.'<br />';
                        // LEVEL 3
                        if (isset($sections_sequense[$section2->id]['childs']) and count($sections_sequense[$section2->id]['childs'])){
                            foreach($sections_sequense[$section2->id]['childs'] as $section3){
                                $sectionlist[$section3->id] = $i++;
                                //echo 'LEVEl 3 --'.$section3->id.'<br />';
                            }
                        }
                    }
                }
            }
        }
    }
}

if (count($sectionlist)){
    $transaction = $DB->start_delegated_transaction();
    foreach ($sectionlist as $id => $position) {
        //$DB->set_field('course_sections', 'section', -$position, array('id' => $id));
        $r = new stdClass();
        $r->id = $id;
        $r->section = -$position;
        $DB->update_record('course_sections', $r);
    }
    
    foreach ($sectionlist as $id => $position) {
        $r = new stdClass();
        $r->id = $id;
        $r->section = $position;
        $DB->update_record('course_sections', $r);
        $DB->set_field('course_sections', 'section', $position, array('id' => $id));
        $DB->set_field('course_format_sections', 'section', $position, array('sectionid' => $id, 'courseid'=>$course->id));
    }
    $transaction->allow_commit();
}

rebuild_course_cache($course->id, true);

$params = array("id"=>$course->id);
if (isset($parent->id)) {
    $params['section'] = $parent->section;
}
$url = new moodle_url('/course/view.php', $params);

redirect($url);




