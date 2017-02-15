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
 * Announcements version file.
 *
 * @package    format_institutes_ceu
 * @author     SEBALE
 * @copyright  2016 sebale.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

class instrutions_table extends table_sql
{
    
    public $arrows_params = array();
    
	function __construct($uniqueid, $courseid)
	{
		global $CFG, $USER, $PAGE;

		parent::__construct($uniqueid);

        $this->initialize_table($courseid);

		$columns = array('title', 'state', 'file', 'actions', 'sortorder');

		$this->define_columns($columns);
		$headers = array(
			get_string('title', 'format_institutes_ceu'),
			get_string('state', 'format_institutes_ceu'),
			get_string('instructionfile', 'format_institutes_ceu'),
			get_string('actions', 'format_institutes_ceu'),
           'sortorder');

		$this->define_headers($headers);
		
		$fields = "i.id, i.title, i.instructionfile, f.filename as file, s.name as state, i.id as actions, i.courseid, i.status , i.sortorder";
		$from = "{course_format_instructions} i
                    LEFT JOIN {course_format_usstates} s ON s.abbr = i.state
                    LEFT JOIN {files} f ON f.itemid = i.id AND f.component = 'format_institutes_ceu' AND f.filearea = 'instructionfile' AND f.filesize > 0";
		$where = "i.id > 0 AND i.courseid = :courseid";

		$this->set_sql($fields, $from, $where, array('courseid'=>$courseid));
		$this->define_baseurl($PAGE->url);
	}
    
    private function initialize_table ($courseid){
        global $DB;
        
        $first = $DB->get_record_sql("SELECT MIN(sortorder) as ord FROM {course_format_instructions} WHERE courseid = :courseid", array('courseid'=>$courseid));
        $last = $DB->get_record_sql("SELECT MAX(sortorder) as ord FROM {course_format_instructions} WHERE courseid = :courseid", array('courseid'=>$courseid));
        
        $params = array();
        $params['first'] = (isset($first->ord)) ? $first->ord : 0;
        $params['last'] = (isset($last->ord)) ? $last->ord : 0;
        
        $this->arrows_params = $params;
    }

	function col_type($values)
	{
		return ($values->type) ? ucfirst($values->type) : '-';
	}

	function col_actions($values)
	{
		global $OUTPUT, $PAGE;

		if ($this->is_downloading()) {
			return '';
		}

		$strdelete = get_string('delete');
		$stredit = get_string('edit');
		$strshow = get_string('show');
		$strhide = get_string('hide');
		$strmoveup = get_string('moveup');
		$strmovedown = get_string('movedown');

		$edit = array();
		$aurl = new moodle_url('/course/format/institutes_ceu/instructions/edit.php', array('id' => $values->courseid, 'instanceid' => $values->id));
		$edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/edit', $stredit, 'core', array('class' => 'iconsmall')));

		$aurl = new moodle_url('/course/format/institutes_ceu/instructions/edit.php', array('action' => 'delete', 'id' => $values->courseid, 'instanceid' => $values->id));
		$edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/delete', $strdelete, 'core', array('class' => 'iconsmall')));

		if ($values->status > 0) {
			$aurl = new moodle_url('/course/format/institutes_ceu/instructions/edit.php', array('action' => 'hide', 'id' => $values->courseid, 'instanceid' => $values->id));
			$edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/hide', $strhide, 'core', array('class' => 'iconsmall')));
		} else {
			$aurl = new moodle_url('/course/format/institutes_ceu/instructions/edit.php', array('action' => 'show', 'id' => $values->courseid, 'instanceid' => $values->id));
			$edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/show', $strshow, 'core', array('class' => 'iconsmall')));
		}
        
        if ($this->arrows_params['first'] != $values->sortorder){
            $aurl = new moodle_url('/course/format/institutes_ceu/instructions/edit.php', array('action' => 'moveup', 'id' => $values->courseid, 'instanceid' => $values->id));
            $edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/up', $strmoveup, 'core', array('class' => 'iconsmall')));
        }
        if ($this->arrows_params['last'] != $values->sortorder){
            $aurl = new moodle_url('/course/format/institutes_ceu/instructions/edit.php', array('action' => 'movedown', 'id' => $values->courseid, 'instanceid' => $values->id));
            $edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/down', $strmovedown, 'core', array('class' => 'iconsmall')));
        }

		return implode(' ', $edit);
	}
}
