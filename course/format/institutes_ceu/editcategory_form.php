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
        $category       = $this->_customdata['category'];
        
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'cid', $instanceid);
        $mform->setType('cid', PARAM_INT);
        
        $mform->addElement('text','name', get_string('name', 'format_institutes_ceu'));
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);
        
        $this->add_action_buttons(get_string('cancel'), get_string('save', 'format_institutes_ceu'));

        // Finally set the current form data
        $this->set_data($category);
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

