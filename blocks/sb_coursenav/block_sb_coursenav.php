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
 * Handles displaying the sb coursenav.
 *
 * @package    block_sb_coursenav
 * @copyright  2016 sebale.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_sb_coursenav extends block_base {

    /**
     * Initialise the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_sb_coursenav');
    }
	function hide_header() {
        return true;
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
        
        $is_enrolled = (is_enrolled($context, $USER));
        
        if ($course->id == 1) return $this->content;
		require_once($CFG->dirroot.'/course/lib.php');
        $course = course_get_format($course)->get_course();
        course_create_sections_if_missing($course, range(0, $course->numsections));
        
		$modfullnames = array(); $archetypes = array();
		
		$displaysection = optional_param('section', 0, PARAM_INT);
		$modinfo = get_fast_modinfo($course);
        $csections = $this->get_course_sections($course);
        if (isset($cm->section)){
            $cm_section = $DB->get_record('course_sections', array('id'=>$cm->section));
        }
        
        $sections = array('root'=>array());
        $currentsectionid = (isset($cm_section->section)) ? $cm_section->section : $displaysection;
        $open_sections = array();
        if ($currentsectionid > 0){
            $currentsection = $modinfo->get_section_info($currentsectionid);
            $currentsection->parent = (isset($csections[$currentsection->id]->parent)) ? $csections[$currentsection->id]->parent : 0;
            if (isset($csections[$currentsection->id]->parentssequence) and !empty($csections[$currentsection->id]->parentssequence)){
                $open_sections = explode(',',$csections[$currentsection->id]->parentssequence);
            }
        }
        
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) continue;
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));
            if (!$showsection) continue;
            
            $thissection->parent = (isset($csections[$thissection->id]->parent)) ? $csections[$thissection->id]->parent : 0;
            if (intval($thissection->parent) > 0){
                $sections[$thissection->parent][$thissection->section] = $thissection;
            } else {
                $sections['root'][$thissection->section] = $thissection;
            }
        }
        
        $this->content->text .= html_writer::start_tag('div', array('class'=>'course-navigation-block'));
        
        $this->content->text .= html_writer::start_tag('div', array('class'=>'course-content-box'));
        
        // root sections start
        $this->content->text .= html_writer::start_tag('ul', array('class'=>'course-root-sections'));
        
        if (count($sections['root'])){
            $format_renderer = $this->page->get_renderer('format_institutes');
            $j = 1; 
            foreach ($sections['root'] as $section=>$thissection){
                $thissection->progress = $format_renderer->get_section_completion($course, $section);
                
                $this->content->text .= html_writer::start_tag('li', array('class'=>'course-section-box '.$thissection->progress['status'].((($currentsectionid > 0 and ($currentsectionid == $section or in_array($thissection->id, $open_sections))) ) ? ' open' : '')));
                
                $counter = html_writer::tag('span', ((strlen($j) > 1) ? $j : '0'.$j), array('class'=>'section-counter'));
                if (isset($sections[$thissection->id]) and count($sections[$thissection->id])){
                    $this->content->text .= html_writer::tag('span', '', array('class'=>'toggler'));    
                }
                
                $this->content->text .= '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'&section='.$section.'" title="'.get_section_name($course, $thissection).'" alt="'.get_section_name($course, $thissection).'" class="section-header'.(($currentsectionid > 0 and $currentsectionid == $section) ? ' current' : '').'">';
                if ($thissection->progress['status'] == 'completed'){
                    $this->content->text .= html_writer::tag('i', '', array('class'=>'status ion-checkmark-round'));
                }
                $this->content->text .= $counter.get_section_name($course, $thissection);
                $this->content->text .= '</a>';
                
                // level1 sections start
                if (isset($sections[$thissection->id]) and count($sections[$thissection->id])){
                    $this->content->text .= html_writer::start_tag('ul', array('class'=>'course-level1-sections'));
                        foreach ($sections[$thissection->id] as $section1=>$thissection1){
                            $thissection1->progress = $format_renderer->get_section_completion($course, $section1);
                                        
                            $this->content->text .= html_writer::start_tag('li', array('class'=>'course-section-box '.$thissection1->progress['status']));
                            if ($this->page->user_is_editing()){
                                $this->content->text .= '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'&section='.$section1.'" title="'.get_section_name($course, $thissection1).'" alt="'.get_section_name($course, $thissection1).'" class="section-header'.(($currentsectionid > 0 and $currentsectionid == $section1) ? ' current' : '').'">'.get_section_name($course, $thissection1).'</a>';
                            } else {
                                $this->content->text .= '<a href="javascript:void(0);" title="'.get_section_name($course, $thissection1).'" alt="'.get_section_name($course, $thissection1).'" class="section-header without-link'.(($currentsectionid > 0 and $currentsectionid == $section1) ? ' current' : '').'">'.get_section_name($course, $thissection1).'</a>';
                            }
                            
                            // level2 sections start
                            if (isset($sections[$thissection1->id]) and count($sections[$thissection1->id])){
                                $this->content->text .= html_writer::start_tag('ul', array('class'=>'course-level2-sections'));
                                    foreach ($sections[$thissection1->id] as $section2=>$thissection2){
                                        $thissection2->progress = $format_renderer->get_section_completion($course, $section2);
                                        
                                        $this->content->text .= html_writer::start_tag('li', array('class'=>'course-section-box '.$thissection2->progress['status'].(((isset($cm_section->section) and $cm_section->section == $section2) || ($displaysection > 0 and $displaysection == $section2)) ? ' active' : '')));
                                            $this->content->text .= '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'&section='.$section2.'" title="'.get_section_name($course, $thissection2).'" alt="'.get_section_name($course, $thissection2).'" class="section-header'.(($currentsectionid > 0 and $currentsectionid == $section2 || $currentsection->parent == $thissection2->id) ? ' current' : '').'">';
                                            if ($thissection2->progress['status'] == 'completed'){
                                                $this->content->text .= html_writer::tag('i', '', array('class'=>'status ion-checkmark-round'));
                                            }
                                            $this->content->text .= get_section_name($course, $thissection2);
                                            $this->content->text .= '</a>';
                                        $this->content->text .= html_writer::end_tag('li');            
                                    }
                                $this->content->text .= html_writer::end_tag('ul');
                            }
                            // level2 sections end
                            
                            $this->content->text .= html_writer::end_tag('li');            
                        }
                    $this->content->text .= html_writer::end_tag('ul');
                }
                // level1 sections start

                $this->content->text .= html_writer::end_tag('li');
                $j++;
            }
        }
        
        $this->content->text .= html_writer::end_tag('ul');
        // root sections end
        
        $this->content->text .= html_writer::end_tag('div');
        
        // end block wrapper
        $this->content->text .= html_writer::end_tag('div');
        
		$this->content->text .= '<script>
                                jQuery(".course-section-box .toggler").click(function(e){
                                    jQuery(this).parent().toggleClass("open");
                                });
								</script>';
		
        return $this->content;
    }
    
    public function get_course_sections($course) {
        global $PAGE, $DB;
        
        $sections = array();
        $allsections = $DB->get_records_sql("SELECT s.*, fs.parent, fs.level, fs.parentssequence FROM {course_sections} s LEFT JOIN {course_format_sections} fs ON fs.sectionid = s.id AND fs.courseid = s.course AND fs.format = 'institutes_ceu' WHERE s.course = $course->id");
        if (count($allsections)){
            foreach($allsections as $section){
                $sections[$section->id] = $section;
            }
        }
        
        return $this->_sections = $sections;
    }
}


