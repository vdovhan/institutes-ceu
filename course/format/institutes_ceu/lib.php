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
 * This file contains main class for the course format Topic
 *
 * @since     Moodle 2.0
 * @package   format_institutes_ceu
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/course/format/lib.php');

/**
 * Main class for the institutes course format
 *
 * @package    format_institutes_ceu
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_institutes_ceu extends format_base {

    
    public function course_content_header() {
        global $CFG;
        
        require_once($CFG->dirroot. '/course/format/institutes_ceu/renderer.php');
        return new format_institutes_ceu_course_content_header;
    }
    
    public function course_content_footer() {
        global $CFG;
        
        require_once($CFG->dirroot. '/course/format/institutes_ceu/renderer.php');
        return new format_institutes_ceu_course_content_footer;
    }
    
    /**
     * Returns true if this course format uses sections
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns the display name of the given section that the course prefers.
     *
     * Use section name is specified by user. Otherwise use default ("Topic #")
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            return format_string($section->name, true,
                    array('context' => context_course::instance($this->courseid)));
        } else {
            return $this->get_default_section_name($section);
        }
    }

    /**
     * Returns the default section name for the institutes_ceu course format.
     *
     * If the section number is 0, it will use the string with key = section0name from the course format's lang file.
     * If the section number is not 0, the base implementation of format_base::get_default_section_name which uses
     * the string with the key = 'sectionname' from the course format's lang file + the section number will be used.
     *
     * @param stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        if ($section->section == 0) {
            // Return the general section.
            return get_string('section0name', 'format_institutes_ceu');
        } else {
            // Use format_base::get_default_section_name implementation which
            // will display the section name in "Topic n" format.
            return parent::get_default_section_name($section);
        }
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        global $CFG;
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                $usercoursedisplay = $course->coursedisplay;
            }
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else {
                if (empty($CFG->linkcoursesections) && !empty($options['navigation'])) {
                    return null;
                }
                $url->set_anchor('section-'.$sectionno);
            }
        }
        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        global $PAGE;
        // if section is specified in course/view.php, make sure it is expanded in navigation
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('section', null, PARAM_INT);
            if ($selectedsection !== null && (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                    $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                $navigation->includesectionnum = $selectedsection;
            }
        }

        // check if there are callbacks to extend course navigation
        parent::extend_course_navigation($navigation, $node);

        // We want to remove the general section if it is empty.
        $modinfo = get_fast_modinfo($this->get_course());
        $sections = $modinfo->get_sections();
        if (!isset($sections[0])) {
            // The general section is empty to find the navigation node for it we need to get its ID.
            $section = $modinfo->get_section_info(0);
            $generalsection = $node->get($section->id, navigation_node::TYPE_SECTION);
            if ($generalsection) {
                // We found the node - now remove it.
                $generalsection->remove();
            }
        }
    }

    /**
     * Custom action after section has been moved in AJAX mode
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    function ajax_section_move() {
        global $PAGE;
        $titles = array();
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return array('sectiontitles' => $titles, 'action' => 'move');
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => array()
        );
    }
    
    public function generateSectionList($level = 3){
        global $DB, $COURSE;
        $course = $this->get_course();
        
        $sectionlist = array();
        $allsections = array();
        
        $course_sections = $DB->get_records('course_sections', array('course'=>$course->id));
        if (count($course_sections)){
            foreach ($course_sections as $s){
                $allsections[$s->id] = ($s->name) ? $s->name : get_string('sectionname', 'format_institutes_ceu').' '.$s->section;
            }
        }
        
        $sections = $DB->get_records_sql("SELECT s.*, fs.parent, fs.level, fs.parentssequence FROM {course_sections} s LEFT JOIN {course_format_sections} fs ON fs.sectionid = s.id AND fs.courseid = s.course WHERE s.course = $course->id AND s.visible = 1 AND fs.level <= $level ORDER BY s.section");
        
        if (count($sections)){
            foreach ($sections as $section){
                if($section->section == 0) continue;
                if(!isset($allsections[$section->id])) continue;
                
                if ($section->parent > 0 and $section->level == 1){
                    $sectionlist[$section->id] = ((isset($allsections[$section->parent])) ? $allsections[$section->parent].' / ' : '').$allsections[$section->id];
                } elseif ($section->parent > 0 and $section->level > 1){
                    $sequence = explode(',', $section->parentssequence);
                    if (count($sequence)){
                        $sectionlist[$section->id] = '';   
                        foreach($sequence as $s){
                            $sectionlist[$section->id] .= ((isset($allsections[$s])) ? $allsections[$s].' / ' : '');    
                        }
                        $sectionlist[$section->id] .= $allsections[$section->id];
                    } else {
                        $sectionlist[$section->id] = ((isset($allsections[$section->parent])) ? $allsections[$section->parent].' / ' : '').$allsections[$section->id];
                    }
                } else {
                    $sectionlist[$section->id] = $allsections[$section->id];
                }
            }
        }
        
        return $sectionlist;
    }
    
    public function get_sections_sequense(){
        global $DB;
        $course = $this->get_course();
        
        $sectionlist = array();
        $allsections = array();
        
        $course_sections = $DB->get_records('course_sections', array('course'=>$course->id));
        
        $format_sections = $DB->get_records_menu('course_format_sections', array('courseid'=>$course->id, 'format'=>'institutes_ceu'), 'sectionid', 'sectionid,parent');
        
        if (count($course_sections)){
            foreach ($course_sections as $section){
                if($section->section == 0) continue;
                if(!isset($format_sections[$section->id])) continue;
                
                $sectionlist[$format_sections[$section->id]][$section->id] = $section;   
            }
        }
        
        return $sectionlist;
    }
    
    /**
     * Updates format options for a section
     *
     * Section id is expected in $data->id (or $data['id'])
     * If $data does not contain property with the option name, the option will not be updated
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @return bool whether there were any changes to the options values
     */
    public function update_section_format_options($data) {
        global $DB, $CFG, $PAGE;
        $data = (array)$data;
        
        require_once($CFG->dirroot.'/course/lib.php');
        
        $course = $this->get_course();
        $section = $DB->get_record('course_sections', array('id'=>$data['id']));
        $format_renderer = $PAGE->get_renderer('format_institutes_ceu');
        
        $params = new stdClass();
        if ($data['parent'] > 0){
            $parent = $DB->get_record('course_format_sections', array('sectionid' => $data['parent'], 'courseid'=>$course->id, 'format'=>'institutes_ceu'));
            
            $params->level = $parent->level+1;
            $params->parentssequence = $data['parent'];
            if ($parent->parent > 0){
                $params->parentssequence = $parent->parentssequence.','.$data['parent'];                    
            }
            $params->parent = $data['parent'];
            $params->sectiontype = 0;
        } else {            
            $params->parent = 0;
            $params->level = 0;
            $params->parentssequence = '';
            $params->sectiontype = (isset($data['sectiontype'])) ? $data['sectiontype'] : 0;
        }
        
        $this->course_save_format_section($section, $params);

        rebuild_course_cache($course->id, true);
        
        return $this->update_format_options($data, $data['id']);
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * institutes_ceu format uses the following options:
     * - parent
     * - sectiontype
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function section_format_options($foreditform = false) {
        global $DB, $COURSE;

        $parent = optional_param('parent', 0, PARAM_INT);
        
        $fields = array();
        $fields['parent'] = array(
                'default' => $parent,
                 'type' => PARAM_INT,
                 'label' => 'Parent',
                 'element_type' => 'hidden',
             );
        
        if ($parent > 0){
            $fields['sectiontype'] = array(
                 'default' => 0,
                 'type' => PARAM_INT,
                 'label' => 'Section Type',
                 'element_type' => 'hidden',
             );
        } else {
            $fields['sectiontype'] = array(
                 'default' => 0,
                 'type' => PARAM_INT,
                 'label' => 'Section Type',
                 'element_type' => 'select',
                 'element_attributes' => array(array(0 => 'Modules', 1 => 'Exams')),
             );
        }
                    
        return $fields;
    }
    
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = array(
                'numsections' => array(
                    'default' => 2,
                    'type' => PARAM_INT,
                ),
                'hiddensections' => array(
                    'default' => 1,
                    'type' => PARAM_INT,
                ),
                'coursedisplay' => array(
                    'default' => 1,
                    'type' => PARAM_INT,
                ),
            );
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseconfig = get_config('moodlecourse');
            $max = $courseconfig->maxsections;
            if (!isset($max) || !is_numeric($max)) {
                $max = 52;
            }
            $sectionmenu = array();
            for ($i = 0; $i <= $max; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseformatoptionsedit = array(
                'numsections' => array(
                    'label' => new lang_string('rootsections', 'format_institutes_ceu'),
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                ),
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'element_type' => 'hidden',
                ),
                'coursedisplay' => array(
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'hidden',
                ),
            );
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@link course_edit_form::definition_after_data()}.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $DB, $CFG;
        $elements = parent::create_edit_form_elements($mform, $forsection);
        $id = optional_param('id', 0, PARAM_INT);

        // Increase the number of sections combo box values if the user has increased the number of sections
        // using the icon on the course page beyond course 'maxsections' or course 'maxsections' has been
        // reduced below the number of sections already set for the course on the site administration course
        // defaults page.  This is so that the number of sections is not reduced leaving unintended orphaned
        // activities / resources.
        if (!$forsection) {
            $maxsections = get_config('moodlecourse', 'maxsections');
            $numsections = $mform->getElementValue('numsections');
            $numsections = $numsections[0];
            if ($numsections > $maxsections) {
                $element = $mform->getElement('numsections');
                for ($i = $maxsections+1; $i <= $numsections; $i++) {
                    $element->addOption("$i", $i);
                }
            }
        }
        
        $course = $DB->get_record('course', array('id'=>$id));

        if (isset($course->id)){
            $course = get_course($id);
            $coursecontext = context_course::instance($course->id);
            $course_format_data = $this->get_course_format_data($course->id);
        } else {
            $course = new stdClass();
            $coursecontext = null;
            $course_format_data = null;
        }
        
        return $elements;
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'institutes_ceu', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * If previous course format did not have 'numsections' option, we populate it with the
     * current number of sections
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB, $CFG;
        $data = (array)$data;
        
        if ($oldcourse !== null) {
            $oldcourse = (array)$oldcourse;
            $options = $this->course_format_options();
            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        $data[$key] = $oldcourse[$key];
                    } else if ($key === 'numsections') {
                        // If previous format does not have the field 'numsections'
                        // and $data['numsections'] is not set,
                        // we fill it with the maximum section number from the DB
                        $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                            WHERE course = ?', array($this->courseid));
                        if ($maxsection) {
                            // If there are no sections, or just default 0-section, 'numsections' will be set to default
                            $data['numsections'] = $maxsection;
                        }
                    }
                }
            }
        }
        
        $course = get_course($this->courseid);
        $coursecontext = context_course::instance($course->id);
        $editoroptions = array('maxfiles' => 1, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true, 'context'=>$coursecontext, 'subdirs'=>0);
        
        $changed = $this->update_format_options($data);
        if ($changed && array_key_exists('numsections', $data)) {
            // If the numsections was decreased, try to completely delete the orphaned sections (unless they are not empty).
            $numsections = (int)$data['numsections'];
            $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                        WHERE course = ?', array($this->courseid));
            for ($sectionnum = $maxsection; $sectionnum > $numsections; $sectionnum--) {
                if (!$this->delete_section($sectionnum, false)) {
                    break;
                }
            }
        }
        
        return $changed;
    }

    /**
     * Whether this format allows to delete sections
     *
     * Do not call this function directly, instead use {@link course_can_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Prepares the templateable object to display section name
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true,
                                                         $editable = null, $edithint = null, $editlabel = null) {
        if (empty($edithint)) {
            $edithint = new lang_string('editsectionname', 'format_institutes_ceu');
        }
        if (empty($editlabel)) {
            $title = get_section_name($section->course, $section);
            $editlabel = new lang_string('newsectionname', 'format_institutes_ceu', $title);
        }
        return parent::inplace_editable_render_section_name($section, $linkifneeded, $editable, $edithint, $editlabel);
    }
    
    public function get_course_format_data($courseid) {
        global $DB;
        $data = array();
        $course_data = $DB->get_records('course_format_options', array('courseid'=>$courseid, 'format'=>'institutes_ceu'));
        if (count($course_data)){
            foreach ($course_data as $item){
                $data[$item->name] = $item->value;
            }
        }

        return $data;
    }
    
    /**
     * Deletes a section
     *
     * Do not call this function directly, instead call {@link course_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @param bool $forcedeleteifnotempty if set to false section will not be deleted if it has modules in it.
     * @return bool whether section was deleted
     */
    public function delete_section($section, $forcedeleteifnotempty = false) {
        global $DB;
        if (!$this->uses_sections()) {
            // Not possible to delete section if sections are not used.
            return false;
        }
        if (!is_object($section)) {
            $section = $DB->get_record('course_sections', array('course' => $this->get_courseid(), 'section' => $section),
                'id,section,sequence,summary');
        }
        if (!$section || !$section->section) {
            // Not possible to delete 0-section.
            return false;
        }

        if (!$forcedeleteifnotempty && (!empty($section->sequence) || !empty($section->summary))) {
            return false;
        }

        $course = $this->get_course();
        
        $this->delete_child_sections($course, $section);
        
        // Remove the marker if it points to this section.
        if ($section->section == $course->marker) {
            course_set_marker($course->id, 0);
        }

        $lastsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                            WHERE course = ?', array($course->id));

        // Find out if we need to descrease the 'numsections' property later.
        $courseformathasnumsections = array_key_exists('numsections',
            $this->get_format_options());
        $decreasenumsections = $courseformathasnumsections && ($section->section <= $course->numsections);

        // Move the section to the end.
        move_section_to($course, $section->section, $lastsection, true);

        // Delete all modules from the section.
        foreach (preg_split('/,/', $section->sequence, -1, PREG_SPLIT_NO_EMPTY) as $cmid) {
            course_delete_module($cmid);
        }

        // Delete section and it's format options.
        $DB->delete_records('course_format_sections', array('sectionid' => $section->id, 'courseid'=>$course->id, 'format'=>'institutes_ceu'));
        $DB->delete_records('course_format_options', array('sectionid' => $section->id, 'courseid'=>$course->id, 'format'=>'institutes_ceu'));
        $DB->delete_records('course_sections', array('id' => $section->id));
        
        $course = $this->get_course();
        $this->update_format_options(array('id' => $course->id, 'numsections' => $course->numsections - 1));
        $this->sort_root_sections($course);
        
        return true;
    }
    
    public function delete_child_sections($course, $section) {
        global $DB, $CFG, $PAGE;
        require_once($CFG->dirroot.'/course/lib.php');
        
        $format_renderer = $PAGE->get_renderer('format_institutes_ceu');
        $modinfo = get_fast_modinfo($course);
        $sections_sequense = $format_renderer->get_sections_sequense($course, $modinfo, $section->section);
        $sections_to_delete = array();
        
        if (isset($sections_sequense[$section->id]['childs'])){
            foreach($sections_sequense[$section->id]['childs'] as $section){
                $sections_to_delete[$section->section] = $section;
                if (isset($sections_sequense[$section->id]['childs']) and count($sections_sequense[$section->id]['childs'])){
                    foreach($sections_sequense[$section->id]['childs'] as $section1){
                        $sections_to_delete[$section1->section] = $section1;
                        // LEVEL 2
                        if (isset($sections_sequense[$section1->id]['childs']) and count($sections_sequense[$section1->id]['childs'])){
                            foreach($sections_sequense[$section1->id]['childs'] as $section2){
                                $sections_to_delete[$section2->section] = $section2;
                                // LEVEL 3
                                if (isset($sections_sequense[$section2->id]['childs']) and count($sections_sequense[$section2->id]['childs'])){
                                    foreach($sections_sequense[$section2->id]['childs'] as $section3){
                                        $sections_to_delete[$section3->section] = $section3;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $numsections = $course->numsections;
        if (count($sections_to_delete)){
            foreach ($sections_to_delete as $thissection){
                // Remove the marker if it points to this section.
                if ($thissection->section == $course->marker) {
                    course_set_marker($course->id, 0);
                }

                $lastsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                                    WHERE course = ?', array($course->id));

                // Move the section to the end.
                move_section_to($course, $thissection->section, $lastsection, true);

                // Delete all modules from the section.
                foreach (preg_split('/,/', $thissection->sequence, -1, PREG_SPLIT_NO_EMPTY) as $cmid) {
                    course_delete_module($cmid);
                }

                // Delete section and it's format options.
                $DB->delete_records('course_format_sections', array('sectionid' => $thissection->id, 'courseid'=>$course->id, 'format'=>'institutes_ceu'));
                $DB->delete_records('course_format_options', array('sectionid' => $thissection->id, 'courseid'=>$course->id, 'format'=>'institutes_ceu'));
                $DB->delete_records('course_sections', array('id' => $thissection->id));
                $numsections = $numsections-1;
                $this->update_format_options(array('id' => $course->id, 'numsections' => $numsections));
                rebuild_course_cache($course->id, true);
            }
        }
        
        return true;
    }
    
    function sort_root_sections($course, $modinfo = null, $format_renderer = null){
        global $DB, $CFG, $PAGE;
        require_once($CFG->dirroot.'/course/lib.php');
        
        if (!$format_renderer){
            $format_renderer = $PAGE->get_renderer('format_institutes_ceu');
        }
        if (!$modinfo){
            $modinfo = get_fast_modinfo($course);
        }
        
        $sections_sequense = $format_renderer->get_sections_sequense($course, $modinfo, 0);
        $sectionlist = array();

        // ROOT LEVEL
        if (isset($sections_sequense[0]['childs'])){
            $i = 1;
            foreach($sections_sequense[0]['childs'] as $section){
                $sectionlist[$section->id] = $i++;
                if (isset($sections_sequense[$section->id]['childs']) and count($sections_sequense[$section->id]['childs'])){
                    foreach($sections_sequense[$section->id]['childs'] as $section1){
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
                $DB->set_field('course_sections', 'section', -$position, array('id' => $id));
            }
            foreach ($sectionlist as $id => $position) {
                $DB->set_field('course_sections', 'section', $position, array('id' => $id));
                $DB->set_field('course_format_sections', 'section', $position, array('sectionid' => $id, 'courseid'=>$course->id, 'format'=>'institutes_ceu'));
            }
            $transaction->allow_commit();
        }

        rebuild_course_cache($course->id, true);
    }
    
    function course_create_format_sections_if_missing(){
        global $DB;
        $course = $this->get_course();
        
        $course_sections = $DB->get_records('course_sections', array('course'=>$course->id));
        $format_sections = $DB->get_records_menu('course_format_sections', array('courseid'=>$course->id, 'format'=>'institutes_ceu'), 'sectionid', 'sectionid,id');
        
        if (count($course_sections)){
            foreach ($course_sections as $sections){
                if (!isset($format_sections[$sections->id])){
                    $new_format_section = new stdClass();
                    $new_format_section->courseid = $course->id;
                    $new_format_section->format = 'institutes_ceu';
                    $new_format_section->sectionid = $sections->id;
                    $new_format_section->section = $sections->section;
                    $new_format_section->parent = 0;
                    $new_format_section->level = 0;
                    $new_format_section->timecreated = time();
                    $new_format_section->timemodified = time();
                    
                    $DB->insert_record('course_format_sections', $new_format_section);
                }
            }
        }
    }
    
    function course_save_format_section($section, $params = array()){
        global $DB;
        $course = $this->get_course();
        $format_section = $DB->get_record('course_format_sections', array('courseid'=>$course->id, 'format'=>'institutes_ceu', 'sectionid'=>$section->id));
        
        if ($format_section){
            $format_section->section = $section->section;
            $format_section->parent = $params->parent;
            $format_section->level = $params->level;
            $format_section->parentssequence = $params->parentssequence;
            $format_section->timemodified = time();
            $format_section->sectiontype = (isset($params->sectiontype)) ? $params->sectiontype : 0;
            $DB->update_record('course_format_sections', $format_section);
        } else {
            $format_section = new stdClass();
            $format_section->courseid = $course->id;
            $format_section->format = 'institutes_ceu';
            $format_section->sectionid = $section->id;
            $format_section->section = $section->section;
            $format_section->parent = $params->parent;
            $format_section->level = $params->level;
            $format_section->parentssequence = $params->parentssequence;
            $format_section->timecreated = time();
            $format_section->timemodified = time();
            $format_section->sectiontype = (isset($params->sectiontype)) ? $params->sectiontype : 0;
            
            $DB->insert_record('course_format_sections', $format_section);
        }
    }
}


/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_institutes_ceu_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            array($itemid, 'institutes_ceu'), MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}

function format_institutes_ceu_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG;
    require_once($CFG->dirroot . '/repository/lib.php');
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }
    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'format_institutes_ceu', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

function format_institutes_ceu_get_modules_list($courseid = 0, $modname = '') {
    global $DB;
    
    if (empty($modname)) return array();
    
    $list = array('' => get_string('selectoption', 'format_institutes_ceu', get_string('pluginname', 'mod_'.$modname)));
    
    if($records = $DB->get_records_sql("SELECT cm.id, i.name FROM {course_modules} cm LEFT JOIN {".$modname."} as i ON cm.instance = i.id LEFT JOIN {modules} m ON m.id = cm.module LEFT JOIN {course_sections} cs ON cs.id = cm.section WHERE cm.course = :courseid AND m.name = :modname AND cs.section = 0", array('courseid'=>$courseid, 'modname'=>$modname))) {
        foreach ($records as $record) {
            $list[$record->id] = $record->name;
        }
    }
    
    return $list;
}

function format_institutes_ceu_get_course_sections($course) {
    global $PAGE, $DB;

    $sections = array();
    $allsections = $DB->get_records_sql("SELECT s.*, fs.parent, fs.level, fs.parentssequence FROM {course_sections} s LEFT JOIN {course_format_sections} fs ON fs.sectionid = s.id AND fs.courseid = s.course AND fs.format = :format WHERE s.course = :courseid", array('courseid'=>$course->id, 'format'=>'institutes_ceu'));
    if (count($allsections)){
        foreach($allsections as $section){
            $sections[$section->id] = $section;
        }
    }

    return $sections;
}

function format_institutes_ceu_get_course_resources($course) {
    global $PAGE, $DB;
    $resources = array();
    
    $resources = $DB->get_records_sql("SELECT * FROM {course_format_resource} WHERE courseid = :courseid AND status = :status ORDER BY sortorder", array('courseid'=>$course->id, 'status'=>1));

    return $resources;
}

function format_institutes_ceu_get_course_hiddenresources($course) {
    global $PAGE, $DB;

    $resources = $DB->get_records_sql_menu("SELECT cmid, coursestate FROM {course_format_resources} WHERE courseid = :courseid AND type = :type AND coursestate = 0", array('courseid'=>$course->id, 'type'=>'module'));
    
    return $resources;
}

function format_institutes_ceu_get_course_cmresources($course){
    global $PAGE, $DB;
    $resources = array();
    
    $allresources = $DB->get_records('resource', array('course'=>$course->id));
    if (count($allresources)){
        foreach ($allresources as $resource){
            $resources[$resource->id] = $resource;
        }
    }
    return $resources;
}

function format_institutes_ceu_get_states_list(){
    global $DB;

    $states = $DB->get_records_sql_menu("SELECT abbr, name FROM {course_format_usstates} ORDER BY name");
    
    return array(''=>'') + $states;
}

function format_institutes_ceu_instructionsfiles_options($courseid = 0) {
    global $CFG;
    
    $options = array(
        'maxfiles' => 1,
        'maxbytes' => $CFG->maxbytes,
        'subdirs' => 0,
        'accepted_types' => '.pdf'
    );
    if (!empty($courseid)) {
        $options['context'] = context_course::instance($courseid);
    }
    return $options;
}

function format_institutes_ceu_noteseditor_options($courseid = 0) {
    global $CFG;
    
    $options = array(
        'maxfiles' => EDITOR_UNLIMITED_FILES, 
        'maxbytes'=>$CFG->maxbytes, 
        'trusttext'=>false, 
        'noclean'=>true,
        'subdirs' => 0,
        'accepted_types' => '*'
    );
    if (!empty($courseid)) {
        $options['context'] = context_course::instance($courseid);
    }
    return $options;
}

function format_institutes_ceu_resourcesfiles_options($courseid = 0) {
    global $CFG;
    
    $options = array(
        'maxfiles' => 1,
        'maxbytes' => $CFG->maxbytes,
        'subdirs' => 0,
        'accepted_types' => '*'
    );
    if (!empty($courseid)) {
        $options['context'] = context_course::instance($courseid);
    }
    return $options;
}

function format_institutes_ceu_resourceseditor_options($courseid = 0) {
    global $CFG;
    
    $options = array(
        'maxfiles' => EDITOR_UNLIMITED_FILES, 
        'maxbytes'=>$CFG->maxbytes, 
        'trusttext'=>false, 
        'noclean'=>true,
        'subdirs' => 0,
        'accepted_types' => '*'
    );
    if (!empty($courseid)) {
        $options['context'] = context_course::instance($courseid);
    }
    return $options;
}

function format_institutes_ceu_get_course_notifications($course) {
    global $DB;
    
    $notifications = $DB->get_records_sql("SELECT n.*   FROM {course_format_notes} n 
                                                        WHERE n.courseid = :courseid 
                                                            AND n.status > 0
                                                            AND n.timestart < :timestart
                                                            AND (n.timeend = 0 OR n.timeend > :timeend)
                                                        ORDER BY n.sortorder ASC",
                                         array('courseid'=>$course->id, 'timestart'=>time(), 'timeend'=>time()));
    
    return $notifications;
}

function format_institutes_ceu_get_course_instructions($course) {
    global $DB;
    
    $instructions = $DB->get_records_sql("SELECT i.*, s.name as statename
                                                        FROM {course_format_instructions} i
                                                        LEFT JOIN {course_format_usstates} s ON s.abbr = i.state
                                                        WHERE i.courseid = :courseid 
                                                            AND i.status > 0
                                                        ORDER BY i.sortorder ASC",
                                         array('courseid'=>$course->id));
    
    return $instructions;
}

function format_institutes_ceu_get_mystate($deep_detect = TRUE) {
    $state = '';
    
    $ip = $_SERVER["REMOTE_ADDR"];
    if ($deep_detect) {
        if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
            $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    
    $purpose = 'state';
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            $state = @$ipdat->geoplugin_regionCode;
        }
    }
    
    return $state;
}


