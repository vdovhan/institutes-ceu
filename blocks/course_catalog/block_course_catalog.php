<?php
/**
 * Schools statistic
 *
 * @package    block_course_catalog
 * @copyright  2015 SEBALE (http://sebale.net)
 */
require_once($CFG->dirroot.'/blocks/course_catalog/locallib.php');

class block_course_catalog extends block_base {

    public function init() {
        $this->title   = get_string('pluginname', 'block_course_catalog');
    }

    /**
     * Return contents of course_catalog block
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $USER, $CFG, $DB;		
		
        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $renderer = $this->page->get_renderer('block_course_catalog');
		
        $this->content->text .= $renderer->course_catalog();

        return $this->content;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
	   return array('all' => true);
    }
	
	function instance_allow_multiple() {
        return false;
    }
	
	function instance_create() {
		//global $PAGE;
		//$PAGE->set_context(context_course::instance(SITEID));
		return true;
	}

    /**
     * Sets block header to be hidden or visible
     *
     * @return bool if true then header will be visible.
     */
    public function hide_header() {
        return true;
    }
}