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

defined('MOODLE_INTERNAL') || die();

/**
 * Event handler for category enrolment plugin.
 *
 * We try to keep everything in sync via listening to events,
 * it may fail sometimes, so we always do a full sync in cron too.
 */
class format_institutes_ceu_observer {

   /**
     * Triggered when 'course_deleted' event is triggered.
     *
     * @param core\event\core\event\course_deleted $event
     */
    public static function institutes_course_deleted(core\event\course_deleted $event) {
        global $DB;
        
        $courseid = $event->objectid;
        
        $DB->delete_records('course_format_sections', array('courseid'=>$courseid));
        $DB->delete_records('course_format_settings', array('courseid'=>$courseid));
        $DB->delete_records('course_format_resource', array('courseid'=>$courseid));
        $DB->delete_records('course_format_notes', array('courseid'=>$courseid));
        $DB->delete_records('course_format_instructions', array('courseid'=>$courseid));
    }
    
    /**
     * Triggered when 'course_module_completion_updated' event is triggered.
     *
     * @param \core\event\course_module_completion_updated $event
     */
    public static function institutes_course_module_deleted(core\event\course_module_deleted $event) {
        global $DB;
        
        $modid = $event->objectid;
        $cmid = $event->contextinstanceid;
        
        $DB->delete_records('course_format_settings', array('value'=>$cmid, 'type'=>'menu'));
    }

}
