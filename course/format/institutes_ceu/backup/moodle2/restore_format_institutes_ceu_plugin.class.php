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
 * Defines restore_format_institutes_ceu_plugin class
 *
 * @package     format_institutes_ceu
 * @category    backup
 * @copyright   2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/institutes_ceu/lib.php');

/**
 * resource restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_format_institutes_ceu_plugin extends restore_format_plugin {

    /**
     * Returns the paths to be handled by the plugin at course level
     */
    protected function define_course_plugin_structure() {
        $paths = array();
        
        // Add own format stuff.
        $elename = 'institutes_ceu'; // This defines the postfix of 'process_*' below.
        
        /*
         * This is defines the nested tag within 'plugin_format_grid_course' to allow '/course/plugin_format_grid_course' in
         * the path therefore as a path structure representing the levels in section.xml in the backup file.
         */
        $elepath = $this->get_pathfor('/');
        $paths[] = new restore_path_element($elename, $elepath);
        
        $paths[] = new restore_path_element('resource', $this->get_pathfor('/resources/resource'));
        $paths[] = new restore_path_element('note', $this->get_pathfor('/notes/note'));
        $paths[] = new restore_path_element('instruction', $this->get_pathfor('/instructions/instruction'));
        
        return $paths;
    }
    
    /**
     * Process the 'plugin_format_institutes_ceu_course' element within the 'course' element in the 'course.xml' file in the '/course'
     * folder of the zipped backup 'mbz' file.
     */
    public function process_institutes_ceu($data) {
        global $DB;
        $data = (object) $data;
        /* We only process this information if the course we are restoring to
          has 'institutes_ceu' format (target format can change depending of restore options). */
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'institutes_ceu') {
            return;
        }
        
    }
    
    public function process_resource($data) {
        global $DB;
        $data = (object) $data;
        
        /* We only process this information if the course we are restoring to
          has 'institutes_ceu' format (target format can change depending of restore options). */
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'institutes_ceu') {
            return;
        }
        
        $oldid = $data->id;
        $data->courseid = $this->task->get_courseid();
        $newitemid = $DB->insert_record('course_format_resource', $data);
        if (!$newitemid) {
            throw new moodle_exception('invalidrecordid', 'format_institutes_ceu', '',
            'Could not set resource.');
        }
        
        $this->set_mapping('resource', $oldid, $newitemid, true, null);
    }
    
    
    public function process_note($data) {
        global $DB;
        $data = (object) $data;
        
        /* We only process this information if the course we are restoring to
          has 'institutes_ceu' format (target format can change depending of restore options). */
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'institutes_ceu') {
            return;
        }
        
        $oldid = $data->id;
        $data->courseid = $this->task->get_courseid();
        $newitemid = $DB->insert_record('course_format_notes', $data);
        if (!$newitemid) {
            throw new moodle_exception('invalidrecordid', 'format_institutes_ceu', '',
            'Could not set notes');
        }
        
        $this->set_mapping('note', $oldid, $newitemid, true, null);
    }
    
    public function process_instruction($data) {
        global $DB;
        $data = (object) $data;
        
        /* We only process this information if the course we are restoring to
          has 'institutes_ceu' format (target format can change depending of restore options). */
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'institutes_ceu') {
            return;
        }
        
        $oldid = $data->id;
        $data->courseid = $this->task->get_courseid();
        $newitemid = $DB->insert_record('course_format_instructions', $data);
        if (!$newitemid) {
            throw new moodle_exception('invalidrecordid', 'format_institutes_ceu', '',
            'Could not set notes');
        }
        
        $this->set_mapping('instruction', $oldid, $newitemid, true, null);
    }
    
    
   /**
     * Returns the paths to be handled by the plugin at section level
     */
    protected function define_section_plugin_structure() {
        $paths = array();
        // Add own format stuff.
        $elename = 'institutessection'; // This defines the postfix of 'process_*' below.
        /* This is defines the nested tag within 'plugin_format_institutes_section' to allow '/section/plugin_format_institutes_section' in
         * the path therefore as a path structure representing the levels in section.xml in the backup file.
         */
        $elepath = $this->get_pathfor('/formatsections/formatsection');
        $paths[] = new restore_path_element($elename, $elepath);
        
        return $paths; // And we return the interesting paths.
    }
    /**
     * Process the 'plugin_format_institutes_section' element within the 'section' element in the 'section.xml' file in the
     * '/sections/section_sectionid' folder of the zipped backup 'mbz' file.
     */
    public function process_institutessection($data) {
        global $DB, $backup_sections;
        $data = (object) $data;
        
        /* We only process this information if the course we are restoring to
           has 'institutes' format (target format can change depending of restore options). */
        $format = $DB->get_field('course', 'format', array('id' => $this->task->get_courseid()));
        if ($format != 'institutes_ceu') {
            return;
        }
        
        $this->set_mapping('backup_sections', $data->sectionid, $this->task->get_sectionid());
        $data->courseid = $this->task->get_courseid();
        $data->sectionid = $this->task->get_sectionid();
        
        if (!$DB->record_exists('course_format_sections', array('courseid' => $data->courseid, 'sectionid' => $data->sectionid))) {
            $data->timecreated = time();
            $data->timemodified = time();
            if (!$DB->insert_record('course_format_sections', $data, true)) {
                throw new moodle_exception('invalidrecordid', 'format_institutes_ceu', '',
                'Could not insert format institutes sections.');
            }
        }
        // No need to annotate anything here.
    }
    
    /**
     * Returns the paths to be handled by the plugin at section level
     */
    protected function define_module_plugin_structure() {
        $paths = array();
        
        $elepath = $this->get_pathfor('/formatsettings/formatsetting');
        $paths[] = new restore_path_element('institutessetting', $elepath);
        
        return $paths;
    }
   
    public function process_institutessetting($data) {
        global $DB;
        $data = (object) $data;
        
        if (empty($data->value)){
            return;
        }
       
        $data->courseid = $this->task->get_courseid();
        $data->value = $this->task->get_moduleid();
        
        if (!$DB->insert_record('course_format_settings', $data, true)) {
            throw new moodle_exception('invalidrecordid', 'format_institutes_ceu', '',
            'Could not insert format institutes sections.');
        }
    }
    
    public function process_institutesresource($data) {
        global $DB, $backup_sections;
        $data = (object) $data;
       
        if ($data->type != 'module'){
            return;
        }
        
        $data->courseid = $this->task->get_courseid();
        $data->cmid = $this->task->get_moduleid();
        
        $newitemid = $DB->insert_record('course_format_resource', $data);
        if (!$newitemid) {
            throw new moodle_exception('invalidrecordid', 'format_institutes_ceu', '',
            'Could not set resources module.');
        }
        $this->set_mapping('resource', $oldid, $newitemid, true, null);
    }
    
    protected function after_restore_course() {
        global $DB;
        
        // process format sections
        $sections = $DB->get_records_sql("SELECT * FROM {course_format_sections} WHERE courseid = :courseid AND format = :format ORDER BY section", array('courseid'=>$this->task->get_courseid(), 'format'=>'institutes_ceu'));
        if (count($sections)){
            foreach ($sections as $section){
                if ($section->parent == 0) continue;
                    
                $section->parent = $this->get_mappingid('backup_sections', $section->parent);
                
                $parentssequence = explode(',', $section->parentssequence);
                $newsequence = array();
                if (count($parentssequence)){
                    foreach($parentssequence as $parent){
                        $newsequence[] = $this->get_mappingid('backup_sections', $parent);
                    }
                    $section->parentssequence = implode(',', $newsequence);
                }
                
                $DB->update_record('course_format_sections', $section);
            }
        }
        
        $this->add_related_files('format_institutes_ceu', 'instructionfile', 'instruction');
        $this->add_related_files('format_institutes_ceu', 'resourcefile', 'resource');
        $this->add_related_files('format_institutes_ceu', 'resourcetext', 'resource');
        $this->add_related_files('format_institutes_ceu', 'notetext', 'note');
    }
    
}
