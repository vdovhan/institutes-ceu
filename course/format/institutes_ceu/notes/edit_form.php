<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

/**
 * The form for handling editing a course.
 */
class edit_form extends moodleform {
    protected $id;
    
    /**
     * Form definition.
     */
    function definition() {
        global $CFG, $PAGE;

        $mform          = $this->_form;
        $id             = $this->_customdata['id'];
        $instanceid     = $this->_customdata['instanceid'];
        $instance       = $this->_customdata['instance'];
        $editoroptions  = $this->_customdata['editoroptions'];
        
        $this->id  = $id;
        if (isset($instance->id)){
            $instance->instanceid = $instance->id;
            $instance->id = $id;
        }
        
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'instanceid', $instanceid);
        $mform->setType('instanceid', PARAM_INT);

        $mform->addElement('editor','notetext_editor', get_string('notetext', 'format_institutes_ceu'), null, $editoroptions);
        $mform->addRule('notetext_editor', get_string('fieldrequired', 'format_institutes_ceu'), 'required', null, 'server');
        $mform->setType('notetext_editor', PARAM_RAW);
        
        $mform->addElement('date_time_selector', 'timestart', get_string('timestart', 'format_institutes_ceu'));
		$mform->setDefault('timestart', time());
		$mform->addRule('timestart', get_string('fieldrequired', 'format_institutes_ceu'), 'required', null, 'client');

		$mform->addElement('date_time_selector', 'timeend', get_string('timeend', 'format_institutes_ceu'), array('optional' => true));
		
        $choices = array('info'=>get_string('bluecolor', 'format_institutes_ceu'), 'warning'=>get_string('yellowcolor', 'format_institutes_ceu'), 'danger'=>get_string('redcolor', 'format_institutes_ceu'), 'success'=>get_string('greencolor', 'format_institutes_ceu'));
        $mform->addElement('select', 'color', get_string('notecolor', 'format_institutes_ceu'), $choices);
        $mform->setType('color', PARAM_TEXT);
        
        $this->add_action_buttons(get_string('cancel'), get_string('save', 'format_institutes_ceu'));

        // Finally set the current form data
        $this->set_data($instance);
    }

    /**
     * Fill in the current page data for this course.
     */
    function definition_after_data() {
        global $DB;

        $mform = $this->_form;
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        return $errors;
    }
}

