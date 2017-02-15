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
 * institutes version file.
 *
 * @package    format_institutes_ceu
 * @author     institutes
 * @copyright  2016 sebale, sebale.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */


require('../../../config.php');
require_once('lib.php');
require_once('menu_edit_form.php');

$systemcontext   = context_system::instance();
require_capability('format/institutes_ceu:settings', $systemcontext);

$id = required_param('id', PARAM_INT); // Option id.

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);

$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$pageparams = array('id' => $id);
$PAGE->set_url('/course/format/institutes_ceu/menusettings.php', $pageparams);

$settings = array();
$menu_settings = $DB->get_records_menu('course_format_settings',array('courseid'=>$id, 'type'=>'menu'),'name','name,value');
if (count($menu_settings)){
    $settings = (array)$menu_settings;
}

// First create the form.
$args = array(
    'id' => $id,
    'settings' => $settings
);
$editform = new edit_form(null, $args);

if ($editform->is_cancelled()) {
    // The form has been cancelled, take them back to what ever the return to is.
    redirect(new moodle_url('/course/view.php', $pageparams));
} else if ($data = $editform->get_data()) {
    
    $fields = array('glossary', 'faq');
    
    foreach ($data as $key=>$val){
        if (in_array($key, $fields)){
            $option = $DB->get_record('course_format_settings', array('courseid'=>$id, 'type'=>'menu', 'name'=>$key));
            if ($option){
                $option->value = $val;
                $DB->update_record('course_format_settings', $option);
            } else {
                $option = new stdClass();
                $option->name = $key;
                $option->type = 'menu';
                $option->courseid = $course->id;
                $option->value = $val;
                $DB->insert_record('course_format_settings', $option);
            }
        }
    }
    
    redirect(new moodle_url('/course/view.php', array('id'=>$id)));
}

$title = get_string('coursemenusettings', 'format_institutes_ceu', $course->fullname);

$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$editform->display();

echo $OUTPUT->footer();
