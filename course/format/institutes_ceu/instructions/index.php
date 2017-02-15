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


require('../../../../config.php');
require_once('../lib.php');
require_once('instrutions_table.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('format/institutes_ceu:manageinstructions', $context);

$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$pageparams = array('id' => $id);
$PAGE->set_url('/course/format/institutes_ceu/instructions/index.php', $pageparams);

$title = get_string('courseinstructions', 'format_institutes_ceu');
$PAGE->navbar->add($title, new moodle_url('/course/format/institutes_ceu/instructions/index.php', $pageparams));

$table = new instrutions_table('instrutions_table', $id);
$table->is_collapsible = false;
$table->no_sorting('title');
$table->no_sorting('state');
$table->no_sorting('file');
$table->no_sorting('actions');
$table->column_class('actions', 'actions');
$table->column_class('sortorder', 'hidden');
$table->sortable(true, 'sortorder', SORT_ASC);

$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

echo html_writer::start_tag('div', array('class' => 'instructions-table-box'));

    echo html_writer::start_tag('div', array('class'=>'action-buttons'));    
        echo html_writer::link(new moodle_url('/course/format/institutes_ceu/instructions/edit.php', array('id'=>$course->id)), get_string('createinstruction', 'format_institutes_ceu'), array('title' => get_string('createinstruction', 'format_institutes_ceu'),  'class'=>'btn btn-create'));
    echo html_writer::end_tag('div');    
    
    $table->out(20, true);
   
echo html_writer::end_tag('div');

echo $OUTPUT->footer();
