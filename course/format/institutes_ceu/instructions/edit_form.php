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
        $filesoptions   = $this->_customdata['filesoptions'];
        
        $this->id  = $id;
        if (isset($instance->id)){
            $instance->instanceid = $instance->id;
            $instance->id = $id;
        }
        
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'instanceid', $instanceid);
        $mform->setType('instanceid', PARAM_INT);
        
        $mform->addElement('text', 'title', get_string('title', 'format_institutes_ceu'),'maxlength="254" size="80"');
        $mform->addRule('title', get_string('fieldrequired', 'format_institutes_ceu'), 'required', null, 'client');
        $mform->setType('title', PARAM_TEXT);
        
        $mform->addElement('text', 'message', get_string('message', 'format_institutes_ceu'),'maxlength="254" size="80"');
        $mform->addRule('message', get_string('fieldrequired', 'format_institutes_ceu'), 'required', null, 'client');
        $mform->setType('message', PARAM_TEXT);
        
        $mform->addElement('text', 'attention', get_string('attentionmsg', 'format_institutes_ceu'),'maxlength="254" size="80"');
        $mform->setType('attention', PARAM_TEXT);
        
        $choices = format_institutes_ceu_get_states_list();
        $mform->addElement('select', 'state', get_string('state', 'format_institutes_ceu'), $choices);
        $mform->addRule('state', get_string('fieldrequired', 'format_institutes_ceu'), 'required', null, 'client');
        $mform->setType('state', PARAM_TEXT);
        
        $mform->addElement('filemanager', 'instructionfile_filemanager', get_string('instructionfile', 'format_institutes_ceu'), null, $filesoptions);
        $mform->addRule('instructionfile_filemanager', get_string('filerequired', 'format_institutes_ceu'), 'required', null, 'server');
        
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

