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
 * Handles displaying the sb coursesettings.
 *
 * @package    block_sb_coursesettings
 * @copyright  2016 sebale.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_sb_coursesettings extends block_base {

    /**
     * Initialise the block.
     */
    public function init() {
        $this->title = get_string('blocktitle', 'block_sb_coursesettings');
    }
	function hide_header() {
        return false;
    }
    function html_attributes() {
		$attributes = parent::html_attributes();
		$attributes['class'] .= ' no_border_block block_' . $this->name();
        return $attributes;
    }
    /**
     * Return preferred_width.
     *
     * @return int
     */
    public function preferred_width() {
        return 210;
    }

    /**
     * Return the content of this block.
     *
     * @return stdClass the content
     */
    public function get_content() {
        global $CFG, $DB, $USER, $OUTPUT;
		
		if ($this->content !== null) {
            return $this->content;
        }
		
		$this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
		
		$course = $this->page->course;
		$cm 	= $this->page->cm;
		$issite = ($course->id == SITEID);
		$context = context_course::instance($course->id, MUST_EXIST);
        
        if ($issite) return '';
        
        $this->content->text .= html_writer::start_tag('ul');
        
            
            if (has_capability('format/institutes_ceu:manageresources', $context)){
                $this->content->text .= html_writer::start_tag('li');
                    $this->content->text .= html_writer::link(new moodle_url('/course/format/'.$course->format.'/resources/index.php', array('id'=>$course->id)), get_string('resourcessettings', 'block_sb_coursesettings'));
                $this->content->text .= html_writer::end_tag('li');
            }
        
            if ($course->format == 'institutes_ceu'){
                if (has_capability('format/institutes_ceu:managenotes', $context)){
                    $this->content->text .= html_writer::start_tag('li');
                        $this->content->text .= html_writer::link(new moodle_url('/course/format/'.$course->format.'/notes/index.php', array('id'=>$course->id)), get_string('coursenotes', 'format_institutes_ceu'));
                    $this->content->text .= html_writer::end_tag('li');
                }
                if (has_capability('format/institutes_ceu:manageinstructions', $context)){
                    $this->content->text .= html_writer::start_tag('li');
                        $this->content->text .= html_writer::link(new moodle_url('/course/format/'.$course->format.'/instructions/index.php', array('id'=>$course->id)), get_string('courseinstructions', 'format_institutes_ceu'));
                    $this->content->text .= html_writer::end_tag('li');
                }
            }
            
            $this->content->text .= html_writer::start_tag('li');
                $this->content->text .= html_writer::link(new moodle_url('/course/format/'.$course->format.'/menusettings.php', array('id'=>$course->id)), get_string('menusettings', 'block_sb_coursesettings'));
            $this->content->text .= html_writer::end_tag('li');
        
            if ($this->page->user_is_editing() and has_capability('moodle/course:update', $context) and stristr($this->page->url, '/course/view.php') and ($course->format == 'institutes' or $course->format == 'institutes_ceu')) {
                $courseview = get_user_preferences('courseview_'.$course->id, 'institutes');
                $this->content->text .= html_writer::start_tag('li');
                    if ($courseview == 'institutes'){
                        $this->content->text .= html_writer::link(new moodle_url('/course/format/'.$course->format.'/toggleview.php', array('id'=>$course->id, 'view'=>'topic')), get_string('topicview', 'block_sb_coursesettings'));   
                    } else {
                        $this->content->text .= html_writer::link(new moodle_url('/course/format/'.$course->format.'/toggleview.php', array('id'=>$course->id, 'view'=>'institutes')), get_string('institutesview', 'block_sb_coursesettings'));
                    }
                $this->content->text .= html_writer::end_tag('li');
            }
        
        $this->content->text .= html_writer::end_tag('ul');
        
        return $this->content;
    }
    
}


