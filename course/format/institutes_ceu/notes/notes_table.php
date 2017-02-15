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

class notes_table extends table_sql
{
    
    public $arrows_params = array();
    
	function __construct($uniqueid, $courseid)
	{
		global $CFG, $USER, $PAGE;

		parent::__construct($uniqueid);

        $this->initialize_table($courseid);

		$columns = array('text', 'color', 'timestart', 'timeend', 'actions', 'sortorder');

		$this->define_columns($columns);
		$headers = array(
			get_string('notetext', 'format_institutes_ceu'),
			get_string('notecolor', 'format_institutes_ceu'),
			get_string('timestart', 'format_institutes_ceu'),
			get_string('timeend', 'format_institutes_ceu'),
			get_string('actions', 'format_institutes_ceu'),
           'sortorder');

		$this->define_headers($headers);
		
		$fields = "n.id, n.text, n.color, n.timestart, n.timeend, n.id as actions, n.courseid, n.status, n.sortorder";
		$from = "{course_format_notes} n";
		$where = "n.id > 0 AND n.courseid = :courseid";

		$this->set_sql($fields, $from, $where, array('courseid'=>$courseid));
		$this->define_baseurl($PAGE->url);
	}
    
    private function initialize_table ($courseid){
        global $DB;
        
        $first = $DB->get_record_sql("SELECT MIN(sortorder) as ord FROM {course_format_notes} WHERE courseid = :courseid", array('courseid'=>$courseid));
        $last = $DB->get_record_sql("SELECT MAX(sortorder) as ord FROM {course_format_notes} WHERE courseid = :courseid", array('courseid'=>$courseid));
        
        $params = array();
        $params['first'] = (isset($first->ord)) ? $first->ord : 0;
        $params['last'] = (isset($last->ord)) ? $last->ord : 0;
        
        $this->arrows_params = $params;
    }

	function col_timestart($values)
	{
		return ($values->timestart) ? userdate($values->timestart, '%b %d %Y, %I:%M %p') : '-';
	}

    function col_timeend($values)
	{
		return ($values->timeend) ? userdate($values->timeend, '%b %d %Y, %I:%M %p') : '-';
	}
    
    function col_color($values)
	{
        $options = array('info'=>get_string('bluecolor', 'format_institutes_ceu'), 'warning'=>get_string('yellowcolor', 'format_institutes_ceu'), 'danger'=>get_string('redcolor', 'format_institutes_ceu'), 'success'=>get_string('greencolor', 'format_institutes_ceu'));
		return $options[$values->color];
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
		$aurl = new moodle_url('/course/format/institutes_ceu/notes/edit.php', array('id' => $values->courseid, 'instanceid' => $values->id));
		$edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/edit', $stredit, 'core', array('class' => 'iconsmall')));

		$aurl = new moodle_url('/course/format/institutes_ceu/notes/edit.php', array('action' => 'delete', 'id' => $values->courseid, 'instanceid' => $values->id));
		$edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/delete', $strdelete, 'core', array('class' => 'iconsmall')));

		if ($values->status > 0) {
			$aurl = new moodle_url('/course/format/institutes_ceu/notes/edit.php', array('action' => 'hide', 'id' => $values->courseid, 'instanceid' => $values->id));
			$edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/hide', $strhide, 'core', array('class' => 'iconsmall')));
		} else {
			$aurl = new moodle_url('/course/format/institutes_ceu/notes/edit.php', array('action' => 'show', 'id' => $values->courseid, 'instanceid' => $values->id));
			$edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/show', $strshow, 'core', array('class' => 'iconsmall')));
		}
        
        if ($this->arrows_params['first'] != $values->sortorder){
            $aurl = new moodle_url('/course/format/institutes_ceu/notes/edit.php', array('action' => 'moveup', 'id' => $values->courseid, 'instanceid' => $values->id));
            $edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/up', $strmoveup, 'core', array('class' => 'iconsmall')));
        }
        if ($this->arrows_params['last'] != $values->sortorder){
            $aurl = new moodle_url('/course/format/institutes_ceu/notes/edit.php', array('action' => 'movedown', 'id' => $values->courseid, 'instanceid' => $values->id));
            $edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/down', $strmovedown, 'core', array('class' => 'iconsmall')));
        }

		return implode(' ', $edit);
	}
}
