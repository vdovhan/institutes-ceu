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
 * Renderer for outputting the institutes course format.
 *
 * @package format_institutes_ceu
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

class format_institutes_ceu_course_content_header implements renderable {}
class format_institutes_ceu_course_content_footer implements renderable {}


/**
 * Basic renderer for institutes_ceu format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_institutes_ceu_renderer extends format_section_renderer_base {
    
    public $_sections = array();
    public $_sections_initialized = false;
    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_institutes_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list($sectiontype = 0) {
        return html_writer::start_tag('ul', array('class' => 'institutes_ceu'.(($sectiontype > 0) ? ' exams-view' : ' modules-view')));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course, $status = '', $courseview = 'institutes') {
        //return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
        $title = get_section_name($course, $section);
        $url = new moodle_url('/course/view.php',
                                array('id' => $course->id,
                                      'section' => $section->section));
        if ($section->level != 3 and $courseview == 'institutes'){
            if ($status == 'completed'){
                $title = html_writer::tag('i', '', array('class'=>'status ion-checkmark-round')).$title;
            }
            $title = html_writer::link($url, $title);   
        }
        return $title;
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        //return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
        return get_section_name($course, $section);
    }
    
    public function get_course_formatted_summary($course, $options = array()) {
        global $CFG;
        require_once($CFG->libdir. '/filelib.php');
        if (empty($course->summary)) {
            return '';
        }
        $options = (array)$options;
        $context = context_course::instance($course->id);
        if (!isset($options['context'])) {
            // TODO see MDL-38521
            // option 1 (current), page context - no code required
            // option 2, system context
            // $options['context'] = context_system::instance();
            // option 3, course context:
            // $options['context'] = $context;
            // option 4, course category context:
            // $options['context'] = $context->get_parent_context();
        }
        $summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $context->id, 'course', 'summary', null);
        $summary = format_text($summary, $course->summaryformat, $options, $course->id);
        
        return $summary;
    }
    
    
    public function print_mainlevel_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $csections = $this->get_course_sections($course);
        
        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        
        // course notifications
        echo $this->course_notifications($course);
        
        echo $this->output->heading($this->page_title(), 2, 'accesshide');
        
        // display course summary
        echo html_writer::start_tag('div', array('class' => 'courseformat-summary'));
        echo $this->get_course_formatted_summary($course,
						  array('overflowdiv' => true, 'noclean' => false, 'para' => false));
        
        // course started/process button
        echo $this->course_start_buttons($course); 
        
        // course started/process button
        echo $this->course_instructions($course);
        
        echo html_writer::end_tag('div');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        echo html_writer::start_tag('div', array('class' => 'sections-containter'));

        // Now the list of sections..
        echo $this->start_section_list();
        
        $j = 1;
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            $thissection->parent = (isset($this->_sections[$thissection->id]->parent)) ? $this->_sections[$thissection->id]->parent : 0;
            $thissection->level = (isset($this->_sections[$thissection->id]->level)) ? $this->_sections[$thissection->id]->level : 0;
            $thissection->parentssequence = (isset($this->_sections[$thissection->id]->parentssequence)) ? $this->_sections[$thissection->id]->parentssequence : '';
            
            if(intval($thissection->parent) > 0 or $section == 0){
                continue;
            }
            if ($section > $course->numsections) {
                // activities inside this section are 'orphaned', this section will be printed as 'stealth' below
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));
            if (!$showsection) {
                // If the hiddensections option is set to 'show hidden sections in collapsed
                // form', then display the hidden section message - UNLESS the section is
                // hidden by the availability system, which is set to hide the reason.
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $course->id);
                }

                continue;
            }

            echo $this->section_header($thissection, $course, false, 0, $j);
            echo html_writer::tag('div', $this->format_summary_text($thissection), array('class' => 'section-summary'));
            echo $this->section_footer();
            $j++;
        }

        echo $this->end_section_list();
        
        echo html_writer::end_tag('div');

        //echo $this->schedule_menu($course);

    }
    
    public function print_innerlevel_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        global $PAGE;
        
        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $csections = $this->get_course_sections($course);
        
        $parentsection = 0; $childsections = array();
        
        $currentsection = $modinfo->get_section_info($displaysection);
        $currentsection->parent = (isset($this->_sections[$currentsection->id]->parent)) ? $this->_sections[$currentsection->id]->parent : 0;
        $currentsection->sectiontype = (isset($this->_sections[$currentsection->id]->sectiontype)) ? $this->_sections[$currentsection->id]->sectiontype : 0;
        if ($currentsection->parent > 0 and isset($this->_sections[$currentsection->parent]->section)){
            $parentsection = $modinfo->get_section_info($this->_sections[$currentsection->parent]->section);
        } else {
            $parentsection = $currentsection;
        }
        
        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        // Now the list of sections..
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if($section == 0){
                continue;
            }
            
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));
            if (!$showsection) {
                // If the hiddensections option is set to 'show hidden sections in collapsed
                // form', then display the hidden section message - UNLESS the section is
                // hidden by the availability system, which is set to hide the reason.
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $course->id);
                }

                continue;
            }
            
            $thissection->parent = (isset($this->_sections[$thissection->id]->parent)) ? $this->_sections[$thissection->id]->parent : 0;
            if ($thissection->parent == $parentsection->id){
                $childsections[$section] = $thissection;
            }
        }
        
        echo $this->start_section_list($currentsection->sectiontype);
        
        if ($parentsection){
            echo $this->section_header($parentsection, $course, false, $parentsection->section, 0);
            echo $this->section_footer();
            
            echo $this->section_progress($course, $parentsection);
            
            if (count($childsections)){
                foreach ($childsections as $section){
                    echo $this->section_header($section, $course, false, $currentsection->section, 0);
                        $innersections = $this->get_section_childs($course, $modinfo, $section->section);
                        if (count($innersections)){
                            echo $this->print_inner_sections($course, $innersections);
                        }
                    echo $this->section_footer();
                }
            }
        }
        
        echo $this->end_section_list();
        
    }
    
    public function section_progress($course, $section) {
        global $USER;
        $output = '';
        
        $context = context_course::instance($course->id);
        
        //if(is_enrolled($context, $USER)) {
            $progress = $this->get_section_completion($course, $section->section);
            
            $output .= html_writer::start_tag('div', array('class' => 'section-progress-box '.$progress['status']));
                $output .= html_writer::tag('label', $progress['progress'].'% '.get_string('complete', 'format_institutes_ceu'));

                $output .= html_writer::start_tag('div', array('class' => 'section-progress'));
                    $output .= html_writer::tag('div', '', array('class'=>'section-progress-percentage', 'style'=>'width:'.$progress['progress'].'%;'));
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');                    
        //}
        
        return $output;
    }
    
    public function get_section_childs($course, $modinfo, $displaysection) {
        
        $parentsection = $modinfo->get_section_info($displaysection);
        $childsections = array();
        
        if (!$this->_sections_initialized){
            $csections = $this->get_course_sections($course);
        }
        
        // Now the list of sections..
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if($section == 0){
                continue;
            }
            
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));
            if (!$showsection) {
                // If the hiddensections option is set to 'show hidden sections in collapsed
                // form', then display the hidden section message - UNLESS the section is
                // hidden by the availability system, which is set to hide the reason.
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $course->id);
                }

                continue;
            }
            
            
            $thissection->parent = (isset($this->_sections[$thissection->id]->parent)) ? $this->_sections[$thissection->id]->parent : 0;
            if ($thissection->parent == $parentsection->id){
                $childsections[$section] = $thissection;
            }
        }
        
        return $childsections;
    }
    
    public function get_all_section_childs($course, $modinfo, $displaysection, $sections = array()) {
        
        $parentsection = $modinfo->get_section_info($displaysection);
        $childsections = array();
        
        if (!$this->_sections_initialized){
            $csections = $this->get_course_sections($course);
        }
        
        // Now the list of sections..
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if($section == 0){
                continue;
            }
            
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));
            if (!$showsection) {
                continue;
            }
            
            $thissection->parent = (isset($this->_sections[$thissection->id]->parent)) ? $this->_sections[$thissection->id]->parent : 0;
            if ($thissection->parent == $parentsection->id){
                $childsections[$section] = $thissection;
            }
        }
        if (count($childsections)){
            foreach ($childsections as $child){
                $sections[$child->section] = $child;
                $sections = $this->get_all_section_childs($course, $modinfo, $child->section, $sections);
            }
        }
        
        return $sections;
    }
    
    public function get_section_completion($course, $sectionid) {
        global $CFG, $DB, $PAGE;
        
        $progress = array('status'=>'notstarted', 'progress'=>0);

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $currentsection = $modinfo->get_section_info($sectionid);
        $completioninfo = new completion_info($course);
        
        if (!$this->_sections_initialized){
            $csections = $this->get_course_sections($course);
        }
        
        $sections = array($currentsection->section=>$currentsection) + $this->get_all_section_childs($course, $modinfo, $currentsection->section, array());
        
        $modules = 0; $completed = 0; $viewed = 0;
        
        if (count($sections)){
            foreach ($sections as $section){
                if ($section->section == 0) continue;
                $section->level = (isset($this->_sections[$section->id]->level)) ? $this->_sections[$section->id]->level : 0;
                if ($section->level != 3) continue;
                if (!isset($modinfo->sections[$section->section])) continue;
                
                foreach ($modinfo->sections[$section->section] as $modnumber) {
                    
                    $mod = $modinfo->cms[$modnumber];
                    if (!$mod->uservisible) continue;
                    
                    // modules
                    $completion = $completioninfo->is_enabled($mod);
                    if ($completion == COMPLETION_TRACKING_NONE) {
                        continue;
                    }
                    $modules++;
                    
                    $completiondata = $completioninfo->get_data($mod, true);                    
                    // viewed
                    if ($completiondata->viewed){
                        $viewed++;
                    }

                    // completed
                    if ($completiondata->completionstate == COMPLETION_COMPLETE OR $completiondata->completionstate == COMPLETION_COMPLETE_PASS){
                        $completed++;
                    }

                }
                
            }
        }
        
        if ($modules > 0){
            if ($viewed > 0 or $completed > 0) $progress['status'] = 'inprogress';
            if ($completed > 0){
                if ($modules == $completed){
                    $progress['status'] = 'completed';
                    $progress['progress'] = 100;
                } else {
                    $progress['progress'] = round(($completed/$modules) * 100);
                }
            }
        }
        
        
        return $progress;
    }
    
    public function print_inner_sections($course, $sections){
        global $CFG, $OUTPUT;
        $o = '';
        
        if (count($sections)){
            $o .= html_writer::start_tag('ul', array('class' => 'inner-sections'));
                $i = 1;
                foreach ($sections as $section){ 
                   echo $this->section_header($section, $course, false, $section->section, 0, $i++);
                }
            $o .= html_writer::end_tag('ul');
        }
        
        return $o;
    }
    
    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_editing_sections_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $csections = $this->get_course_sections($course);
        
        $currentsection = null;
        if (!empty($displaysection)) {
            $currentsection = $modinfo->get_section_info($displaysection);
            $currentsection->parent = (isset($this->_sections[$currentsection->id]->parent)) ? $this->_sections[$currentsection->id]->parent : 0;
            $currentsection->level = (isset($this->_sections[$currentsection->id]->level)) ? $this->_sections[$currentsection->id]->level : 0;    
        }


        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);
        
        /*if (isset($currentsection->parent) and $currentsection->parent > 0){
            $parentsection = $modinfo->get_section_info($this->_sections[$currentsection->parent]->section);
            if ($parentsection){
                echo $this->section_header($parentsection, $course, false, $parentsection->section, 0);
                echo $this->section_footer();    
            }
        }*/

        // Now the list of sections..
        echo $this->start_section_list();

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            
            $thissection->parent = (isset($this->_sections[$thissection->id]->parent)) ? $this->_sections[$thissection->id]->parent : 0;
            $thissection->level = (isset($this->_sections[$thissection->id]->level)) ? $this->_sections[$thissection->id]->level : 0;
            $thissection->parentssequence = (isset($this->_sections[$thissection->id]->parentssequence)) ? $this->_sections[$thissection->id]->parentssequence : '';
            
            if (empty($displaysection)) {
                if(intval($thissection->parent) > 0 or $thissection->level > 0) continue;
            } else {
                if(isset($currentsection->id) and $thissection->parent != $currentsection->id) continue;
            }
            
            if ($section > $course->numsections) {
                // activities inside this section are 'orphaned', this section will be printed as 'stealth' below
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));
            if (!$showsection) {
                // If the hiddensections option is set to 'show hidden sections in collapsed
                // form', then display the hidden section message - UNLESS the section is
                // hidden by the availability system, which is set to hide the reason.
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $course->id);
                }

                continue;
            }

            echo $this->section_header($thissection, $course, false, 0);
            if ($thissection->uservisible) {
                if ((isset($currentsection->level) and $thissection->level == 2) or $thissection->section == 0){
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                }
                if ($thissection->level == 2 or $thissection->section == 0){
                    echo $this->courserenderer->course_section_add_cm_control($course, $section, 0);
                }
            }
            echo $this->section_footer();
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
                    // this is not stealth section or it is empty
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            
            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));

            // Increase number of sections.
            $straddsection = get_string('createnewsection', 'format_institutes_ceu');
            $url = new moodle_url('/course/format/institutes_ceu/addsection.php',
                array('courseid' => $course->id,
                      'parent' => ((isset($currentsection->id)) ? $currentsection->id : 0)));
            
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $straddsection, array('class' => 'increase-sections btn', 'style'=>'margin-top:15px;'));

            echo html_writer::end_tag('div');
            
        }

    }

    
    /**
     * Output the html for a multiple section page with topic view
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_editing_topicview_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $csections = $this->get_course_sections($course);
        
        $currentsection = null;
        if (!empty($displaysection)) {
            $currentsection = $modinfo->get_section_info($displaysection);
            $currentsection->parent = (isset($this->_sections[$currentsection->id]->parent)) ? $this->_sections[$currentsection->id]->parent : 0;
            $currentsection->level = (isset($this->_sections[$currentsection->id]->level)) ? $this->_sections[$currentsection->id]->level : 0;    
        }


        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);
        
        /*if (isset($currentsection->parent) and $currentsection->parent > 0){
            $parentsection = $modinfo->get_section_info($this->_sections[$currentsection->parent]->section);
            if ($parentsection){
                echo $this->section_header($parentsection, $course, false, $parentsection->section, 0);
                echo $this->section_footer();    
            }
        }*/

        // Now the list of sections..
        echo $this->start_section_list();

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
           
            $thissection->parent = (isset($this->_sections[$thissection->id]->parent)) ? $this->_sections[$thissection->id]->parent : 0;
            $thissection->level = (isset($this->_sections[$thissection->id]->level)) ? $this->_sections[$thissection->id]->level : 0;
            $thissection->parentssequence = (isset($this->_sections[$thissection->id]->parentssequence)) ? $this->_sections[$thissection->id]->parentssequence : '';
            
            if ($section > $course->numsections) {
                // activities inside this section are 'orphaned', this section will be printed as 'stealth' below
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));
            if (!$showsection) {
                // If the hiddensections option is set to 'show hidden sections in collapsed
                // form', then display the hidden section message - UNLESS the section is
                // hidden by the availability system, which is set to hide the reason.
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $course->id);
                }

                continue;
            }

            
            echo $this->section_header($thissection, $course, false, 0);
            if ($thissection->uservisible) {
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                if ($thissection->level == 2){
                    echo $this->courserenderer->course_section_add_cm_control($course, $section, 0);
                }
            }
            echo $this->section_footer();
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
                    // this is not stealth section or it is empty
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();
            
        }

    }
 
    public function get_course_sections($course) {
        global $PAGE, $DB;
        
        $sections = array();
        $allsections = $DB->get_records_sql("SELECT s.*, fs.parent, fs.level, fs.parentssequence, fs.sectiontype FROM {course_sections} s LEFT JOIN {course_format_sections} fs ON fs.sectionid = s.id AND fs.courseid = s.course AND fs.format = 'institutes_ceu' WHERE s.course = $course->id ORDER BY s.section");
        if (count($allsections)){
            foreach($allsections as $section){
                $sections[$section->id] = $section;
            }
        }
        $this->_sections = $sections;
        $this->_sections_initialized = true;
        
        return $this->_sections;
    }
    
    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $csections = $this->get_course_sections($course);

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);
        
        //echo html_writer::start_tag('div', array('class' => 'sections-containter'));
        
        echo $this->course_print_coursefile($course);

        // Now the list of sections..
        echo $this->start_section_list();

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                // 0-section is displayed a little different then the others
                /*if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                    echo $this->section_header($thissection, $course, false, 0);
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);
                    echo $this->section_footer();
                }*/
                continue;
            }
            if ($section > $course->numsections) {
                // activities inside this section are 'orphaned', this section will be printed as 'stealth' below
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));
            if (!$showsection) {
                // If the hiddensections option is set to 'show hidden sections in collapsed
                // form', then display the hidden section message - UNLESS the section is
                // hidden by the availability system, which is set to hide the reason.
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $course->id);
                }

                continue;
            }

            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $course, null);
            } else {
                echo $this->section_header($thissection, $course, false, 0);
                if ($thissection->uservisible) {
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    
                    $thissection->parent = (isset($this->_sections[$thissection->id]->parent)) ? $this->_sections[$thissection->id]->parent : 0;
                    if(intval($thissection->parent) > 0 and intval($this->_sections[$thissection->id]->level) == 3){
                        echo $this->courserenderer->course_section_add_cm_control($course, $section, 0);   
                    }
                }
                echo $this->section_footer();
            }
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
                    // this is not stealth section or it is empty
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));

            // Increase number of sections.
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php',
                array('courseid' => $course->id,
                      'increase' => true,
                      'sesskey' => sesskey()));
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon.get_accesshide($straddsection), array('class' => 'increase-sections'));

            if ($course->numsections > 0) {
                // Reduce number of sections sections.
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php',
                    array('courseid' => $course->id,
                          'increase' => false,
                          'sesskey' => sesskey()));
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link($url, $icon.get_accesshide($strremovesection), array('class' => 'reduce-sections'));
            }

            echo html_writer::end_tag('div');
        } else {
            echo $this->end_section_list();
        }
        
        //echo html_writer::end_tag('div');
        //echo $this->schedule_menu($course);

    }
    
    public function course_notifications($course) {
        $output = '';
        $notifications = format_institutes_ceu_get_course_notifications($course);
        $context = context_course::instance($course->id);
        
        if (count($notifications)){
            foreach ($notifications as $note){
                $output .= html_writer::start_tag('div', array('class' => 'course-notifications '.$note->color, 'id'=>'note_'.$note->id));
                $output .= html_writer::tag('i', '', array('class' => 'ion-alert course-notifications-alert'));
                
                $notetext = file_rewrite_pluginfile_urls($note->notetext, 'pluginfile.php', $context->id, 'format_intitutes_ceu', 'notetext', $note->id);

                $options = new stdClass();
                $options->noclean = true;
                $options->overflowdiv = true;
                $content = format_text($notetext, 1, $options);
                
                $output .=  html_writer::tag('div', $content, array('class' => 'course-notifications-content'));
                $output .=  html_writer::tag('i', '', array('class' => 'ion-close course-notifications-close', 'title'=>get_string('close', 'format_institutes_ceu'), 'onclick'=>'jQuery("#note_'.$note->id.'").remove();'));
                $output .=  html_writer::end_tag('div');
            }
        }
        
        return $output;
    }
    
    public function course_start_buttons($course) {
        $output = '';
        $course_completion = $this->get_course_completion($course);
        
        if ($course_completion->status == 'completed'){
            return $output;
        }
        
        $modinfo = get_fast_modinfo($course);
        $url = '';
        
        if ($course_completion->status == 'notyetstarted' or $course_completion->status == 'pending'){
            
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if (!$thissection->visible or $section < 1) continue;
                $url = new moodle_url('/course/view.php',
                                array('id' => $course->id,
                                      'section' => $section));
                $title = get_string('get_started', 'format_institutes_ceu');
                break;
            }

        } else {
            
            $completioninfo = new completion_info($course);
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if (!$thissection->visible) continue;
                if (!empty($url)) break;

                if (isset($modinfo->sections[$thissection->section])) {
                    foreach ($modinfo->sections[$thissection->section] as $modnumber) {
                        if (!empty($url)) break;
                        
                        $mod = $modinfo->cms[$modnumber];
                        
                        if (!$this->has_nav_mod($mod) or $section < 1){
                            continue;
                        }

                        $completion = $completioninfo->is_enabled($mod);
                        if ($completion == COMPLETION_TRACKING_NONE) {
                            continue;
                        }
                        $completiondata = $completioninfo->get_data($mod, true);                    
                        
                        // completed
                        if ($completiondata->completionstate == COMPLETION_COMPLETE OR $completiondata->completionstate == COMPLETION_COMPLETE_PASS){
                            continue;
                        } else {
                            $url = new moodle_url('/mod/'.$mod->modname.'/view.php',
                                        array('id' => $mod->id));
                            $title = get_string('continue', 'format_institutes_ceu');
                            break;
                        }

                    }
                }
            }
            
        }
        if (!empty($url)){
            $output .= html_writer::start_tag('div', array('class' => 'course-startbutton-box'));
                $output .= html_writer::link($url, $title, array('class'=>'btn'));
            $output .=  html_writer::end_tag('div');
        }
        
        return $output;
    }
    
    public function get_course_completion($course) {
        global $CFG, $PAGE, $DB, $USER, $OUTPUT;				
        
        require_once("{$CFG->libdir}/completionlib.php");
        
        $result = new stdClass();
        $result->completion = 0;
        $result->status = 'notyetstarted';

        // Get course completion data.
        $info = new completion_info($course);

        // Load criteria to display.
        $completions = $info->get_completions($USER->id);

        if ($info->is_tracked_user($USER->id) and $course->startdate <= time()) {

            // For aggregating activity completion.
            $activities = array();
            $activities_complete = 0;

            // For aggregating course prerequisites.
            $prerequisites = array();
            $prerequisites_complete = 0;

            // Flag to set if current completion data is inconsistent with what is stored in the database.
            $pending_update = false;

            // Loop through course criteria.
            foreach ($completions as $completion) {
                $criteria = $completion->get_criteria();
                $complete = $completion->is_complete();

                if (!$pending_update && $criteria->is_pending($completion)) {
                    $pending_update = true;
                }

                // Activities are a special case, so cache them and leave them till last.
                if ($criteria->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {
                    $activities[$criteria->moduleinstance] = $complete;

                    if ($complete) {
                        $activities_complete++;
                    }

                    continue;
                }

                // Prerequisites are also a special case, so cache them and leave them till last.
                if ($criteria->criteriatype == COMPLETION_CRITERIA_TYPE_COURSE) {
                    $prerequisites[$criteria->courseinstance] = $complete;

                    if ($complete) {
                        $prerequisites_complete++;
                    }

                    continue;
                }
            }

            $itemsCompleted  = $activities_complete + $prerequisites_complete;
            $itemsCount      = count($activities) + count($prerequisites);

            // Aggregate completion.
            if ($itemsCount > 0) {
                $result->completion = round(($itemsCompleted / $itemsCount) * 100);
            }

            // Is course complete?
            $coursecomplete = $info->is_course_complete($USER->id);
            
            // Load course completion.
            $params = array(
                'userid' => $USER->id,
                'course' => $course->id
            );
            $ccompletion = new completion_completion($params);

            // Has this user completed any criteria?
            $criteriacomplete = $info->count_course_user_data($USER->id);

            if ($pending_update) {
                $status = 'pending';
            } else if ($coursecomplete) {
                $status = 'completed';
                $result->completion = 100;
            } else if ($criteriacomplete || $ccompletion->timestarted) {
                $status = 'inprogress';
            } else {
                $status = 'notyetstarted';
            }

            $result->status = $status;
        }
        
        return $result;
    }
    
    public function course_instructions($course) {
        $output = '';
        
        $instructions = format_institutes_ceu_get_course_instructions($course);
        $context = context_course::instance($course->id);
        
        if (count($instructions)){
            $output .= html_writer::start_tag('div', array('class' => 'course-instruction-box'));
            $output .= html_writer::tag('div', get_string('instructionsdescription', 'format_institutes_ceu'), array('class' => 'course-instruction-info'));

            $output .= html_writer::start_tag('ul');
                foreach ($instructions as $instruction){
                    $output .= html_writer::start_tag('li');
                        $output .= html_writer::link("javascript:void(0)", get_string('viewinstructions', 'format_institutes_ceu', $instruction->title), array('onclick'=>'instructionsPopupOpen('.$instruction->id.')'));
                        // popup box
                        $output .= html_writer::start_tag('div', array('id'=>'instruction_'.$instruction->id, 'class'=>'instructions-popup-box'));
                            $output .= html_writer::start_tag('div', array('class' => 'course-notifications-title'));
                                $output .=  html_writer::tag('i', '', array('class' => 'ion-close course-notifications-close', 'title'=>get_string('close', 'format_institutes_ceu'), 'onclick'=>'instructionsPopupClose();'));
                            $output .= $instruction->title;
                            $output .= html_writer::end_tag('div');
                            $output .= html_writer::tag('div', $instruction->message, array('class' => 'course-notifications-message'));
                            if ($instruction->attention){
                                $output .= html_writer::start_tag('div', array('class' => 'course-notifications-attention'));
                                    $output .= html_writer::tag('span', '', array('class' => 'icon-alert'));
                                    $output .= $instruction->attention;
                                $output .= html_writer::end_tag('div');
                            }
                            $output .= html_writer::start_tag('div', array('id'=>'instruction_'.$instruction->id, 'class'=>'instructions-popup-buttons'));
                                $output .= html_writer::link("javascript:void(0)", get_string('cancel'), array('onclick'=>'instructionsPopupClose()', 'class'=>'btn btn-cancel'));
                    
                                $fs = get_file_storage();
                                $files = $fs->get_area_files($context->id, 'format_institutes_ceu', 'instructionfile', $instruction->id);
                                if (count($files) > 0) {
                                    foreach ($files as $file){
                                        if ($file->get_filename() == '.') continue;
                                        
                                        $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());

                                        $output .= html_writer::link($url, get_string('viewpdf', 'format_institutes_ceu'), array('onclick'=>'instructionsPopupClose()', 'class'=>'btn', 'target'=>'_blank'));
                                    }
                                }
                    
                            $output .=  html_writer::end_tag('div');
                        $output .=  html_writer::end_tag('div');
                    $output .= html_writer::end_tag('li');  
                }
            $output .=  html_writer::end_tag('ul');
            $output .=  html_writer::end_tag('div');
        }
        
        return $output;
    }
    
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null, $sectionid = 0, $smodule = 0) {
        global $PAGE, $CFG, $DB;

        $o = '';
        $currenttext = '';
        $sectionstyle = '';
        
        if ($section->section > 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }
        
        $courseview = get_user_preferences('courseview_'.$course->id, 'institutes');
        
        $slevel = (isset($this->_sections[$section->id]->level)) ? ' section-level-'.intval($this->_sections[$section->id]->level) : '';
        $section->level = (isset($this->_sections[$section->id]->level)) ? intval($this->_sections[$section->id]->level) : 0;
        $section->parentssequence = (isset($this->_sections[$section->id]->parentssequence)) ? $this->_sections[$section->id]->parentssequence : '';
        $section->sectiontype = (isset($this->_sections[$section->id]->sectiontype)) ? $this->_sections[$section->id]->sectiontype : '';
        
        $params = array(
            'id' => 'section-'.$section->section,
            'class' => 'section main clearfix'.$sectionstyle.$slevel.' '.$courseview.'-view sectiontype-'.(($section->sectiontype > 0) ? 'exams' : 'modules'), 'role'=>'region',
            'aria-label'=> get_section_name($course, $section)
        );
        
        if ($section->section == $sectionreturn and $section->section > 0){
            $params['class'] .= ' current';
        }
        if (!$PAGE->user_is_editing()){
            $section->progress = $this->get_section_completion($course, $section->section);
            $params['class'] .= ' '.$section->progress['status'];
        
            if (($section->level == 0 and $section->section != $sectionreturn)){
                $params['onclick'] = "location='".$CFG->wwwroot.'/course/view.php?id='.$course->id.'&section='.$section->section."'";
            } elseif (($section->level == 0 and $section->section == $sectionreturn)){
                $params['class'] .= ' main-section';
            }
        }
        
        $o.= html_writer::start_tag('li', $params);

        // Create a span that contains the section title to be used to create the keyboard section move menu.
        $o .= html_writer::tag('span', get_section_name($course, $section), array('class' => 'hidden sectionname'));
        
        if (!$PAGE->user_is_editing()){
            $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
            $o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));
        } elseif ($courseview == 'institutes' and $section->section > 0) {
            $url = new moodle_url('/course/format/institutes_ceu/movesection.php',
                                array('courseid' => $course->id,
                                      'section' => $section->id,
                                      'action' => 'up'));
            $moveupicon = '<span class="fa fa-angle-up"></span>';
            $leftcontent = html_writer::link($url, $moveupicon, array('class' => 'section-move move-up', 'title'=>'Move section up'));

            $url = new moodle_url('/course/format/institutes_ceu/movesection.php',
                                array('courseid' => $course->id,
                                      'section' => $section->id,
                                      'action' => 'down'));
            $movedownicon = '<span class="fa fa-angle-down"></span>';
            $leftcontent .= html_writer::link($url, $movedownicon, array('class' => 'section-move move-down', 'title'=>'Move section down'));
            $o.= html_writer::tag('div', $leftcontent, array('class' => 'left-side'));
        }

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content clearfix'));

        // When not on a section page, we display the section titles except the general section if null
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $classes = ' accesshide';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }
        
        if (!$PAGE->user_is_editing() and $section->level == 2){
            
            // section name
            $o .= html_writer::start_tag('div', array('class' => 'section-name-box'));
            
            if ($smodule > 0){
                $o .= html_writer::tag('div', get_string('sectionnumber', 'format_institutes_ceu', $smodule), array('class' => 'smodule-title'));
            }
            
            $title = html_writer::tag('span', get_section_name($course, $section), array('class' => 'title'));
            $o .= $this->output->heading($title, 4);
            
            $o .= html_writer::start_tag('div', array('class' => 'summary'));
            $o .= $this->format_summary_text($section);
            $o .= html_writer::end_tag('div');
            
            $o .= html_writer::end_tag('div');
            
            $o .= html_writer::start_tag('div', array('class' => 'section-modules-box'));
            $o .= $this->courserenderer->course_section_cm_list($course, $section, 0);
            $o .= html_writer::end_tag('div');
            
            $o .= html_writer::start_tag('div', array('class' => 'section-audio-box'));
            $o .= $this->course_section_audio($course, $section, 0);
            $o .= html_writer::end_tag('div');
            
        } else {
            if (!$PAGE->user_is_editing() and $section->level == 0) {
                $title = html_writer::tag('span', get_section_name($course, $section), array('class' => 'title'));
                $o .= $this->output->heading($title, 3);
            } elseif (!$PAGE->user_is_editing() and $section->level > 0) {
                $sectionname = html_writer::tag('span', get_section_name($course, $section));
                $o .= $this->output->heading($sectionname, 3, 'sectionname' . $classes);
            } elseif ($PAGE->user_is_editing() and $section->level == 2) {
                $sectionname = html_writer::tag('span', get_section_name($course, $section));
                $o .= $this->output->heading($sectionname, 3, 'sectionname' . $classes);
            } else {
                $sectionname = html_writer::tag('span', $this->section_title($section, $course, '', $courseview));
                $o .= $this->output->heading($sectionname, 3, 'sectionname' . $classes);
            }

            $o .= html_writer::start_tag('div', array('class' => 'summary'));
            $o .= $this->format_summary_text($section);
            $o .= html_writer::end_tag('div');

            $context = context_course::instance($course->id);
            $o .= $this->section_availability_message($section,
                    has_capability('moodle/course:viewhiddensections', $context));

            if (($section->level == 0 and !$sectionreturn) and !$PAGE->user_is_editing()){
                $o .= $this->section_completion_status($course, $section);
            }
        }

        return $o;
    }
    
    public function course_section_audio($course, $section){
        global $USER, $CFG, $DB;

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array(); $resources = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($mod->modname != 'resource'){
                    continue;
                }
                
                $resources[$modnumber] = $mod;
            }
        }

        if (count($resources) > 0){
            foreach ($resources as $cm_resource){
                
                $resource = $DB->get_record('resource', array('id'=>$cm_resource->instance), '*', MUST_EXIST);
                $context = context_module::instance($cm_resource->id);
                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
                if (count($files) > 0) {
                    $file = reset($files);
                    unset($files);
                    
                    if (stristr($file->get_mimetype(), 'audio')){
                        $path = '/'.$context->id.'/mod_resource/content/'.$resource->revision.$file->get_filepath().$file->get_filename();
                        $fullurl = moodle_url::make_file_url('/pluginfile.php', $path, 1);
    
                        $output .= html_writer::link($fullurl, html_writer::tag('span', '', array('class' => 'audio-icon')).get_string('downloadaudio', 'format_institutes_ceu'), array('class'=>'course-section-audio', 'title'=>get_string('downloadaudio', 'format_institutes_ceu')));
                    }
                }
            }
        }

        return $output;
    }
    
    protected function get_sectionid($section) {
        $sectionid = 0;
        
        $level_sections = array();
        if (count($this->_sections)){
            foreach ($this->_sections as $ssection){
                $level_sections[$ssection->level][$ssection->section] = $ssection;
            }
        }
        
        if (count($level_sections[$section->level])){
            $i = 0;
            foreach ($level_sections[$section->level] as $ls){
                if ($ls->id == $section->id) {
                    $sectionid = $i;
                    break;
                }
                $i++;
            }
        }
        
        return $sectionid;
    }
    
    protected function section_completion_status($course, $section) {
        $o = '';
        
        $o .= html_writer::start_tag('div', array('class'=>'section-completion-status'));
            $o .= html_writer::tag('label', get_string('completed', 'format_institutes_ceu').':');
            $o .= $section->progress['progress'].'%';
        $o .= html_writer::end_tag('div');
        
        return $o;
    }

    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing() or $section->section == 0) {
            return array();
        }
        
        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $isstealth = $section->section > $course->numsections;
        $controls = array();


        if (!isset($this->_sections[$section->id]->level) or (isset($this->_sections[$section->id]->level) and $this->_sections[$section->id]->level != 2)){
            $editeurl = new moodle_url('/course/format/institutes_ceu/addsection.php', array("courseid"=>$course->id, "parent"=>$section->id));

            $controls['addsection'] = array('url' => $editeurl, "icon" => 'i/manual_item',
                                                   'name' => get_string('addsection', 'format_institutes_ceu'),
                                                   'pixattr' => array('class' => '', 'alt' => get_string('addsection', 'format_institutes_ceu')),
                                                   'attr' => array('class' => 'editing_addsection', 'title' => get_string('addsection', 'format_institutes_ceu')));
        }


        if (!$isstealth && $section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $markedthistopic = get_string('markedthistopic');
                $highlightoff = get_string('highlightoff');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marked',
                                               'name' => $highlightoff,
                                               'pixattr' => array('class' => '', 'alt' => $markedthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markedthistopic));




            } else {
                $url->param('marker', $section->section);
                $markthistopic = get_string('markthistopic');
                $highlight = get_string('highlight');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marker',
                                               'name' => $highlight,
                                               'pixattr' => array('class' => '', 'alt' => $markthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markthistopic));
            }
        }

        $parentcontrols = parent::section_edit_control_items($course, $section, $onsectionpage);
        
        if (array_key_exists("moveup", $parentcontrols)) {
            unset($parentcontrols['moveup']);
        }
        if (array_key_exists("movedown", $parentcontrols)) {
            unset($parentcontrols['movedown']);
        }
        
        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            $merged = array();
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                if ($key == "edit") {
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }

            return $merged;
        } else {
            return array_merge($controls, $parentcontrols);
        }
    }


    public function course_print_coursefile($course)
    {
        global $PAGE, $CFG;

        require_once($CFG->dirroot . '/filter/mediaplugin/filter.php');
        $filterplugin = new filter_mediaplugin(null, array());

        $context = context_course::instance($course->id);
        $contentimages = '';
        if ($course->infotext) {
            $contentimages .= html_writer::tag('div', $course->infotext, array('class' => 'course-file-box embed'));
        } else {
            $fs = get_file_storage();
            $image_files = array();
            $imgfiles = $fs->get_area_files($context->id, 'format_institutes_ceu', 'thumbnail', $course->id);
            $j = 1;
            foreach ($imgfiles as $file) {
                $filename = $file->get_filename();
                $filetype = $file->get_mimetype();
                $itemid = $course->id . "_" . $j++;
                if ($filename == '.' or !$filetype) continue;

                if (stripos($filetype, 'video') !== FALSE) {
                    $url = moodle_url::make_pluginfile_url($context->id, 'format_institutes_ceu', 'thumbnail', $course->id, '/', $filename);
                    $contentimages .= $filterplugin->filter('<div class="course-info-playerbox" id="' . $itemid . '"><a href="' . $url . '">' . $filename . '</a></div>');
                } else if (stripos($filetype, 'image') !== FALSE) {
                    $url = moodle_url::make_pluginfile_url($context->id, 'format_institutes_ceu', 'thumbnail', $course->id, '/', $filename);
                    $contentimages .= html_writer::empty_tag('img', array('src' => $url->out()));
                }
            }

            if (!empty($contentimages)) {
                $contentimages = html_writer::tag('div', $contentimages, array('class' => 'course-file-box'));
            }
        }

        return $contentimages;

    }
    
    public function get_parent_section($course, $sectionid){
        global $PAGE, $USER, $DB;
        $section = null;
        
        if (!$this->_sections_initialized){
            $csections = $this->get_course_sections($course);
        }
        
        if (isset($this->_sections[$sectionid]) and $this->_sections[$sectionid]->level > 0){
            $sequense = explode(',', $this->_sections[$sectionid]->parentssequence);
            if (count($sequense)){
                $parentid = $sequense[0];
                if (isset($this->_sections[$parentid])){
                    $section = $this->_sections[$parentid];
                }
            }
        } elseif (isset($this->_sections[$sectionid]) and $this->_sections[$sectionid]->level == 0){
            $section = $this->_sections[$sectionid];
        }
        
        return $section;
    }
    
    public function get_section_number($course, $sectionid){
        global $PAGE, $USER, $DB;
        $snumber = 1; $sections = array();
        
        if (!$this->_sections_initialized){
            $csections = $this->get_course_sections($course);
        }
        
        if (isset($this->_sections[$sectionid]) and $this->_sections[$sectionid]->parent > 0){
            foreach ($this->_sections as $section){
                if ($section->parent == $this->_sections[$sectionid]->parent){
                    $sections[$section->id] = $snumber++;
                }
            }
        }
        
        return (isset($sections[$sectionid])) ? $sections[$sectionid] : $snumber;
    }
    
    protected function render_format_institutes_ceu_course_content_header(format_institutes_ceu_course_content_header $a) {
        global $PAGE, $USER, $DB;
        
        $o = '';
        $currentsection = null;
        $course = $PAGE->course;
        
        if ($PAGE->user_is_editing() or !isset($PAGE->cm->id)) return $o;
        
        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        
        $menu_settings = $DB->get_records_menu('course_format_settings',array('courseid'=>$course->id, 'type'=>'menu'),'name','name,value');
        
        if (isset($menu_settings['glossary']) and $menu_settings['glossary'] == $PAGE->cm->id) {
            return $o;
        } elseif (isset($menu_settings['faq']) and $menu_settings['faq'] == $PAGE->cm->id){
            return $o;
        }
        
        
        $parentsection = $this->get_parent_section($course, $PAGE->cm->section);
        if (isset($parentsection->sectiontype) and $parentsection->sectiontype > 0) return $o;
        
        $smodule = $this->get_section_number($course, $PAGE->cm->section);
        $o .= html_writer::tag('h2', get_string('sectionnumber', 'format_institutes_ceu', $smodule), array('class' => 'section-title'));
        
        return $o;
    }
    
    protected function render_format_institutes_ceu_course_content_footer(format_institutes_ceu_course_content_footer $a) {
        global $PAGE, $USER, $DB, $CFG;
        
        $o = '';
        $status = 'notstarted'; $type = '';
        $course = $PAGE->course;
        
        if ($PAGE->user_is_editing()) return $o;
        if (!$this->page->cm) return $o;
        
        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $completioninfo = new completion_info($course);
        if (!$this->_sections_initialized){
            $csections = $this->get_course_sections($course);
        }
        
        if ($this->_sections[$this->page->cm->section]->section == 0){
            return $o;
        }
        
        if (isset($PAGE->cm->id)){
            $mod = $modinfo->cms[$PAGE->cm->id];
            
            if (!$this->_sections_initialized){
                $csections = $this->get_course_sections($course);
            }
            $displaysection = (isset($this->_sections[$mod->section])) ? $this->_sections[$mod->section] : 0;
            $parentsection = $this->get_parent_section($course, $displaysection->id);
            
            $sectionnavlinks = $this->get_nav_links($course, $modinfo->get_section_info_all(), $parentsection->section);
            $activitynavlinks = $this->get_activity_nav_links($course, $mod);
            
            $o .= html_writer::start_tag('div', array('class' => 'course-content-footer-navbuttons'));
                if (!empty($activitynavlinks['previous'])){
                    $o .= html_writer::tag('span', $activitynavlinks['previous'], array('class' => 'btn prev-button'));
                } elseif (!empty($sectionnavlinks['previous'])){
                    $o .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'btn prev-button'));
                }
                if (!empty($activitynavlinks['next'])){
                    $o .= html_writer::tag('span', $activitynavlinks['next'], array('class' => 'btn next-button'));
                } elseif (!empty($sectionnavlinks['next'])){
                    $o .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'btn next-button'));
                }
            $o .= html_writer::end_tag('div');
            
        }
        
        return $o;
    }
    
    
    
    /**
     * Generate next/previous section links for naviation
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param int $sectionno The section number in the coruse which is being dsiplayed
     * @return array associative array with previous and next section link
     */
    protected function get_nav_links($course, $sections, $sectionno) {
        // FIXME: This is really evil and should by using the navigation API.
        $course = course_get_format($course)->get_course();
        $canviewhidden = has_capability('moodle/course:viewhiddensections', context_course::instance($course->id))
            or !$course->hiddensections;

        $links = array('previous' => '', 'next' => '');
        $back = $sectionno - 1;
        if ($back > 1){
            while ($back > 0 and empty($links['previous'])) {
                $sections[$back]->level = (isset($this->_sections[$sections[$back]->id]->level)) ? $this->_sections[$sections[$back]->id]->level : 0;
                if (($canviewhidden || $sections[$back]->uservisible) and $sections[$back]->level == 0) {
                    $params = array();
                    if (!$sections[$back]->visible) {
                        $params = array('class' => 'dimmed_text');
                    }
                    $previouslink = html_writer::tag('span', $this->output->larrow(), array('class' => 'larrow'));
                    $previouslink .= get_string('previous');
                    $links['previous'] = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id,
                                          'section' => $back)), $previouslink, $params);
                }
                $back--;
            }
        }
        
        $forward = $sectionno + 1;
        while ($forward <= $course->numsections and empty($links['next'])) {
            $sections[$forward]->level = (isset($this->_sections[$sections[$forward]->id]->level)) ? $this->_sections[$sections[$forward]->id]->level : 0;
            if (($canviewhidden || $sections[$forward]->uservisible) and $sections[$forward]->level == 0) {
                $params = array();
                if (!$sections[$forward]->visible) {
                    $params = array('class' => 'dimmed_text');
                }
                $nextlink = get_string('next');
                $nextlink .= html_writer::tag('span', $this->output->rarrow(), array('class' => 'rarrow'));
                $links['next'] = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id,
                                      'section' => $forward)), $nextlink, $params);
            }
            $forward++;
        }
        
        return $links;
    }

    protected function has_nav_mod($mod) {
        if ((!$mod->uservisible || !$mod->has_view()) && !has_capability('moodle/course:viewhiddenactivities', $mod->context)) {
            return false;
        }

        if($mod->modname == 'label') {
            return false;
        }

        if(!$mod->url) {
            return false;
        }

        return true;
    }
    
    protected function get_activity_nav_links($course, $cm) {
        $course = course_get_format($course)->get_course();
        $modinfo = get_fast_modinfo($course);

        $links = [
            'previous' => '',
            'next' => ''
        ];
        
        $result = [
            'firstmodules' => false,
            'prev_mod' => 0,
            'next_mod' => 0,
            'break_prev' => false,
            'break_next' => false
        ];
        
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if (!$thissection->visible) continue;

            if (isset($modinfo->sections[$thissection->section])) {
                foreach ($modinfo->sections[$thissection->section] as $modnumber) {
                    $mod = $modinfo->cms[$modnumber];

                    if(!$this->has_nav_mod($mod) or $section < 1) {
                        continue;
                    }
                    
                    // check if it is audio file and ignore
                    if ($mod->modname == 'resource'){
                        $context = context_module::instance($mod->id);
                        $fs = get_file_storage();
                        $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
                        if (count($files) > 0) {
                            $file = reset($files);
                            unset($files);
                            if (stristr($file->get_mimetype(), 'audio')){
                                continue;
                            }
                        }
                    }
                    
                    $modparentsection = $this->get_parent_section($course, $mod->section);
                    $cmparentsection = $this->get_parent_section($course, $cm->section);
                    
                    if(isset($modparentsection->id) and isset($cmparentsection->id) and $modparentsection->id != $cmparentsection->id) {
                        continue;
                    }
                    
                    if ($cm->id == $mod->id) {
                        if (!$result['break_prev']) {
                            $result['firstmodules'] = true;
                        }
                        $result['break_prev'] = true;
                        $result['break_next'] = true;
                    } else {
                        if (!$result['break_prev']) {
                            $result['prev_mod'] = $mod;
                        }
                        if ($result['break_next']) {
                            $result['next_mod'] = $mod;
                            break;
                        }
                    }
                }
            }

            if (($result['prev_mod'] || $result['firstmodules']) && $result['next_mod']){
                break;
            }
        }
        
        if($result['prev_mod']) {
            $onclick = htmlspecialchars_decode($result['prev_mod']->onclick, ENT_QUOTES);
            $links['previous'] = html_writer::link($result['prev_mod']->url, get_string('previous'), [
                'onclick' => $onclick
            ]);
        }

        if($result['next_mod']) {
            $onclick = htmlspecialchars_decode($result['next_mod']->onclick, ENT_QUOTES);
            $links['next'] = html_writer::link($result['next_mod']->url, get_string('next'), [
                'onclick' => $onclick
            ]);
        }
        return $links;
    }
    
    public function get_sections_sequense($course, $modinfo, $rootsection = 0) {
        
        if ($rootsection > 0){
            $parentsection = $modinfo->get_section_info($rootsection);
        }
        
        $sectionlist = array();
        
        if (!$this->_sections_initialized){
            $csections = $this->get_course_sections($course);
        }
        
        // Now the list of sections..
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if($section == 0){
                continue;
            }
            
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));
            if (!$showsection) {
                continue;
            }
            
            $thissection->parent = (isset($this->_sections[$thissection->id]->parent)) ? $this->_sections[$thissection->id]->parent : 0;
            
            $sectionlist[$thissection->parent]['childs'][$thissection->id] = $thissection;   
        }
        
        return $sectionlist;
    }
    
}



