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
 * Defines backup_format_institutes_ceu_plugin class
 *
 * @package     format_institutes_ceu
 * @category    backup
 * @copyright   2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Provides the steps to perform one complete backup of the format instance
 */
class backup_format_institutes_ceu_plugin extends backup_format_plugin {

    protected function define_course_plugin_structure() {
        
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '/course/format', 'institutes_ceu');
        
        // Create plugin container element with standard name
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Add wrapper to plugin
        $plugin->add_child($pluginwrapper);
        
        // course resources //
        $resources = new backup_nested_element('resources');
        $resource = new backup_nested_element('resource', array('id'),
                                            array('courseid',
                                                  'title',
                                                  'resourcetext',
                                                  'filename',
                                                  'popuptext',
                                                  'states',
                                                  'resourcefile',
                                                  'status',
                                                  'sortorder'));
        $pluginwrapper->add_child($resources);
        $resources->add_child($resource);
        $resource->set_source_table('course_format_resource', array('courseid' => backup::VAR_COURSEID));
        
        
        // course notes //
        $notes = new backup_nested_element('notes');
        $note = new backup_nested_element('note', array('id'),
                                            array('courseid',
                                                  'notetext',
                                                  'color',
                                                  'timestart',
                                                  'timeend',
                                                  'status',
                                                  'sortorder'));
        $pluginwrapper->add_child($notes);
        $notes->add_child($note);
        $note->set_source_table('course_format_notes', array('courseid' => backup::VAR_COURSEID));
        
        
        
        // course instructions //
        $instructions = new backup_nested_element('instructions');
        $instruction = new backup_nested_element('instruction', array('id'),
                                            array('courseid',
                                                  'title',
                                                  'message',
                                                  'attention',
                                                  'state',
                                                  'instructionfile',
                                                  'status',
                                                  'sortorder'));
        $pluginwrapper->add_child($instructions);
        $instructions->add_child($instruction);
        $instruction->set_source_table('course_format_instructions', array('courseid' => backup::VAR_COURSEID));
        
        
        // annotate files //
        $resource->annotate_files('format_institutes_ceu', 'resourcefile', 'id');
        $resource->annotate_files('format_institutes_ceu', 'resourcetext', 'id');
        $note->annotate_files('format_institutes_ceu', 'notetext', 'id');
        $instruction->annotate_files('format_institutes_ceu', 'instructionfile', 'id');

        return $plugin;
    }
    
    /**
     * Returns the format information to attach to section element
     */
    protected function define_section_plugin_structure() {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, $this->get_format_condition(), 'institutes_ceu');
        // Create one standard named plugin element (the visible container).
        // The sectionid and courseid not required as populated on restore.
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        
        // course_format_sections //
        $formatsections = new backup_nested_element('formatsections');
        $formatsection = new backup_nested_element('formatsection', array('id'),
                                            array('courseid',
                                                  'format',
                                                  'sectionid',
                                                  'section',
                                                  'parent',
                                                  'level',
                                                  'parentssequence',
                                                  'imageid',
                                                  'timecreated',
                                                  'timemodified',
                                                  'sectiontype'));

        $pluginwrapper->add_child($formatsections);
        $formatsections->add_child($formatsection);
        $formatsection->set_source_table('course_format_sections', array('courseid' => backup::VAR_COURSEID, 'sectionid' => backup::VAR_SECTIONID));
        
        return $plugin;
    }
    
    /**
     * Returns the format information to attach to section element
     */
    protected function define_module_plugin_structure() {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, $this->get_format_condition(), 'institutes');
        // Create one standard named plugin element (the visible container).
        // The sectionid and courseid not required as populated on restore.
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        
        // course_format_settings //
        $formatsettings = new backup_nested_element('formatsettings');

        $formatsetting = new backup_nested_element('formatsetting', array('id'),
                                            array('courseid',
                                                  'type',
                                                  'name',
                                                  'value'));

        $pluginwrapper->add_child($formatsettings);
        $formatsettings->add_child($formatsetting);

        $formatsetting->set_source_table('course_format_settings', array('value'=>backup::VAR_MODID, 'courseid' => backup::VAR_COURSEID));

        return $plugin;
    }
}
