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


require('../../../../config.php');
require_once('../lib.php');
require_once('edit_form.php');

$id          = required_param('id', PARAM_INT); // course id
$instanceid  = optional_param('instanceid', 0, PARAM_INT); // instruction id
$action      = optional_param('action', '', PARAM_RAW); // action
$delete      = optional_param('delete', 0, PARAM_BOOL); // delete

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('format/institutes_ceu:manageinstructions', $context);

if ($instanceid){
    $instance = $DB->get_record('course_format_instructions', array('id'=>$instanceid));
} else {
    $instance = new stdClass();
}


if ($action == 'delete' and isset($instance->id)){
    $returnurl = new moodle_url($CFG->wwwroot.'/course/format/institutes_ceu/instructions/index.php', array('id'=>$course->id));
    
    if ($delete and confirm_sesskey()) {
        $DB->delete_records('course_format_instructions', array('courseid'=>$course->id, 'id'=>$instance->id));
        redirect($returnurl);
    }
    
    $strheading = get_string('deleteintruction', 'format_institutes_ceu');
    
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('course');
    $pageparams = array('id' => $id, 'instanceid'=>$instance->id, 'action'=>'delete');
    $PAGE->set_url('/course/format/institutes_ceu/instructions/edit.php', $pageparams);
    $PAGE->navbar->add(get_string('courseinstructions', 'format_institutes_ceu'), new moodle_url('/course/format/institutes_ceu/instructions/index.php', array('id'=>$id)));
    
    $PAGE->set_title($strheading);
    $PAGE->set_heading($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url($CFG->wwwroot.'/course/format/institutes_ceu/instructions/edit.php', array('id' => $course->id, 'delete' => 1, 'instanceid'=>$instanceid, 'action'=>'delete', 'sesskey' => sesskey()));
    $message = get_string('confirmintructiondelete', 'format_institutes_ceu', format_string($instance->title));
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
} elseif ($action == 'moveup' and isset($instance->id)){
    $pre_instance = $DB->get_record_sql("SELECT * FROM {course_format_instructions} WHERE courseid = :courseid AND sortorder <  :sortorder ORDER BY sortorder DESC LIMIT 1", array('courseid'=>$course->id, 'sortorder'=>$instance->sortorder));
    if (isset($pre_instance->sortorder)){
        $pre_instance->sortorder += 1;
        $DB->update_record('course_format_instructions', $pre_instance);
    }
    $instance->sortorder -= 1;
    $DB->update_record('course_format_instructions', $instance);
    redirect(new moodle_url($CFG->wwwroot.'/course/format/institutes_ceu/instructions/index.php', array('id'=>$course->id)));
} elseif ($action == 'movedown' and isset($instance->id)){
    $post_instance = $DB->get_record_sql("SELECT * FROM {course_format_instructions} WHERE courseid = :courseid AND sortorder >  :sortorder ORDER BY sortorder ASC LIMIT 1", array('courseid'=>$course->id, 'sortorder'=>$instance->sortorder));
    if (isset($post_instance->sortorder)){
        $post_instance->sortorder -= 1;
        $DB->update_record('course_format_instructions', $post_instance);
    }
    $instance->sortorder += 1;
    $DB->update_record('course_format_instructions', $instance);
    redirect(new moodle_url($CFG->wwwroot.'/course/format/institutes_ceu/instructions/index.php', array('id'=>$course->id)));
} elseif ($action == 'show' and isset($instance->id)){
    $instance->status = 1;
    $DB->update_record('course_format_instructions', $instance);
    redirect(new moodle_url($CFG->wwwroot.'/course/format/institutes_ceu/instructions/index.php', array('id'=>$course->id)));
} elseif ($action == 'hide' and isset($instance->id)){
    $instance->status = 0;
    $DB->update_record('course_format_instructions', $instance);
    redirect(new moodle_url($CFG->wwwroot.'/course/format/institutes_ceu/instructions/index.php', array('id'=>$course->id)));
}
    
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$pageparams = array('id' => $id, 'instanceid'=>$instanceid);
$PAGE->set_url('/course/format/institutes_ceu/instructions/edit.php', $pageparams);
$PAGE->navbar->add(get_string('courseinstructions', 'format_institutes_ceu'), new moodle_url('/course/format/institutes_ceu/instructions/index.php', array('id'=>$id)));

$filesoptions = format_institutes_ceu_instructionsfiles_options($id);
$instance = file_prepare_standard_filemanager($instance, 'instructionfile', $filesoptions, $context,
                                           'format_institutes_ceu', 'instructionfile', (isset($instance->id)) ? $instance->id : 0);

// First create the form.
$args = array(
    'id' => $course->id,
    'instanceid' => $instanceid,
    'filesoptions' => $filesoptions,
    'instance' => $instance
);
$editform = new edit_form(null, $args);

if ($editform->is_cancelled()) {
    // The form has been cancelled, take them back to what ever the return to is.
    redirect(new moodle_url('/course/format/institutes_ceu/instructions/index.php', array('id'=>$course->id)));
} else if ($data = $editform->get_data()) {
    
    if (isset($instance->id)){
        $instance->title = $data->title;
        $instance->message = $data->message;
        $instance->attention = $data->attention;
        $instance->state = $data->state;
        $instance->id = $instance->instanceid;
        $DB->update_record('course_format_instructions', $instance);
    } else {
        $last_sortorder = $DB->get_record_sql("SELECT * FROM {course_format_instructions} WHERE courseid = :courseid ORDER BY sortorder DESC LIMIT 1", array('courseid'=>$course->id));
        
        $instance = new stdClass();
        $instance->courseid = $course->id;
        $instance->title = $data->title;
        $instance->message = $data->message;
        $instance->attention = $data->attention;
        $instance->state = $data->state;
        $instance->status = 1;
        $instance->instructionfile = 0;
        $instance->sortorder = (isset($last_sortorder->sortorder)) ? $last_sortorder->sortorder+1 : 0;
        $instance->id = $DB->insert_record('course_format_instructions', $instance);
    }
    
    $instructionfile = file_postupdate_standard_filemanager($data, 'instructionfile', $filesoptions, $context,
                                              'format_institutes_ceu', 'instructionfile', $instance->id);
    
    if (isset($instructionfile->instructionfile_filemanager)){
        $instance->instructionfile = $instructionfile->instructionfile_filemanager;
        $DB->update_record('course_format_instructions', $instance);   
    }
    
    redirect(new moodle_url('/course/format/institutes_ceu/instructions/index.php', array('id'=>$course->id)));
}

$title = (isset($instance->id)) ? get_string('editinstruction', 'format_institutes_ceu') : get_string('createnewinstruction', 'format_institutes_ceu');

$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$editform->display();

echo $OUTPUT->footer();
