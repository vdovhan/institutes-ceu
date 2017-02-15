<?php
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
 * institutes_ceu version file.
 *
 * @package    format_institutes_ceu
 * @author     institutes_ceu
 * @copyright  2016 sebale, sebale.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */


require('../../../config.php');
require_once('lib.php');
require_once('editcategory_form.php');

$systemcontext   = context_system::instance();
require_capability('format/institutes_ceu:manageresources', $systemcontext);

$id             = required_param('id', PARAM_INT);
$instanceid     = optional_param('cid', 0, PARAM_INT);
$action         = optional_param('action', '', PARAM_RAW);
$delete         = optional_param('delete', 0, PARAM_BOOL);

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);

if ($action == 'moveup' and $instanceid){
    $category = $DB->get_record('course_format_resources', array('courseid'=>$course->id, 'id'=>$instanceid, 'type'=>'category'));
    $pre_categoty = $DB->get_record_sql("SELECT * FROM {course_format_resources} WHERE courseid = $course->id AND type = 'category' AND sortorder < $category->sortorder ORDER BY sortorder DESC LIMIT 1");
    if (isset($pre_categoty->sortorder)){
        $pre_categoty->sortorder += 1;
        $DB->update_record('course_format_resources', $pre_categoty);
    }
    $category->sortorder -= 1;
    $DB->update_record('course_format_resources', $category);
    redirect(new moodle_url('/course/format/institutes_ceu/resources.php', array('id'=>$course->id)));
} elseif ($action == 'movedown' and $instanceid){
    $category = $DB->get_record('course_format_resources', array('courseid'=>$course->id, 'id'=>$instanceid, 'type'=>'category'));
    $post_categoty = $DB->get_record_sql("SELECT * FROM {course_format_resources} WHERE courseid = $course->id AND type = 'category' AND sortorder > $category->sortorder ORDER BY sortorder ASC LIMIT 1");
    if (isset($post_categoty->sortorder)){
        $post_categoty->sortorder -= 1;
        $DB->update_record('course_format_resources', $post_categoty);
    }
    $category->sortorder += 1;
    $DB->update_record('course_format_resources', $category);
    redirect(new moodle_url('/course/format/institutes_ceu/resources.php', array('id'=>$course->id)));
} elseif ($action == 'delete' and $instanceid){
    $category = $DB->get_record('course_format_resources', array('courseid'=>$course->id, 'id'=>$instanceid, 'type'=>'category'));
    $returnurl = new moodle_url($CFG->wwwroot.'/course/format/institutes_ceu/resources.php', array('id'=>$course->id));
    
    if ($delete and confirm_sesskey()) {
        $DB->delete_records('course_format_resources', array('courseid'=>$course->id, 'categoryid'=>$category->id, 'type'=>'module'));
        $DB->delete_records('course_format_resources', array('courseid'=>$course->id, 'id'=>$category->id, 'type'=>'category'));
        redirect($returnurl);
    }
    
    $strheading = get_string('deletecategory', 'format_institutes_ceu');
    
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('course');
    $pageparams = array('id' => $id, 'cid'=>$instanceid, 'action'=>'delete');
    $PAGE->set_url('/course/format/institutes_ceu/editcategory.php', $pageparams);
    
    $PAGE->set_title($strheading);
    $PAGE->set_heading($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url($CFG->wwwroot.'/course/format/institutes_ceu/editcategory.php', array('id' => $course->id, 'delete' => 1, 'cid'=>$instanceid, 'action'=>'delete', 'sesskey' => sesskey()));
    $message = get_string('confirmcategorydelete', 'format_institutes_ceu', format_string($category->name));
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}
    

$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$pageparams = array('id' => $id, 'cid'=>$instanceid);
$PAGE->set_url('/course/format/institutes_ceu/editcategory.php', $pageparams);

$category = null;
if($instanceid){
    $category = $DB->get_record('course_format_resources', array('courseid'=>$course->id, 'id'=>$instanceid, 'type'=>'category'));
    $category->id = $course->id;
    $category->cid = $instanceid;
}

// First create the form.
$args = array(
    'id' => $course->id,
    'instanceid' => $instanceid,
    'category' => $category
);
$editform = new edit_form(null, $args);

if ($editform->is_cancelled()) {
    // The form has been cancelled, take them back to what ever the return to is.
    redirect(new moodle_url('/course/format/institutes_ceu/resources.php', $pageparams));
} else if ($data = $editform->get_data()) {
    
    if ($category){
        $category->name = $data->name;
        $DB->update_record('course_format_resources', $category);
    } else {
        $last_sortorder = $DB->get_record_sql("SELECT * FROM {course_format_resources} WHERE courseid = :courseid AND type = :type ORDER BY sortorder DESC LIMIT 1", array('courseid'=>$course->id, 'type'=>'category'));
        
        $category = new stdClass();
        $category->name = $data->name;
        $category->type = 'category';
        $category->courseid = $course->id;
        $category->state = 1;
        $category->sortorder = (isset($last_sortorder->sortorder)) ? $last_sortorder->sortorder+1 : 0;
        $DB->insert_record('course_format_resources', $category);
    }
    
    redirect(new moodle_url('/course/format/institutes_ceu/resources.php', $pageparams));
}

$title = ($category) ? get_string('editcategory', 'format_institutes_ceu') : get_string('createcategory', 'format_institutes_ceu');

$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$editform->display();

echo $OUTPUT->footer();
