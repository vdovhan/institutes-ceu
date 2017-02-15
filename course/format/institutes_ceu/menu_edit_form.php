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
        $settings       = $this->_customdata['settings'];
        
        $this->id  = $id;
        
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        
        $choices = format_institutes_ceu_get_modules_list($id, 'glossary');
        $mform->addElement('select', 'glossary', get_string('glossary', 'format_institutes_ceu'), $choices);
        $mform->setType('glossary', PARAM_TEXT);

        $choices = format_institutes_ceu_get_modules_list($id, 'glossary');
        $mform->addElement('select', 'faq', get_string('faq', 'format_institutes_ceu'), $choices);
        $mform->setType('faq', PARAM_TEXT);
        
        $this->add_action_buttons(get_string('cancel'), get_string('save', 'format_institutes_ceu'));

        // Finally set the current form data
        $this->set_data($settings);
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

