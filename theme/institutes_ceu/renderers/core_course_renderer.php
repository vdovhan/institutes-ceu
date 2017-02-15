<?php
require_once($CFG->libdir. "/../course/renderer.php");
class theme_institutes_ceu_core_course_renderer extends core_course_renderer{
	
    /**
     * Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm_name()}
     * {@link core_course_renderer::course_section_cm_text()}
     * {@link core_course_renderer::course_section_cm_availability()}
     * {@link core_course_renderer::course_section_cm_completion()}
     * {@link course_get_cm_edit_actions()}
     * {@link core_course_renderer::course_section_cm_edit_actions()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';
        // We return empty string (because course module will not be displayed at all)
        // if:
        // 1) The activity is not visible to users
        // and
        // 2) The 'availableinfo' is empty, i.e. the activity was
        //     hidden in a way that leaves no info, such as using the
        //     eye icon.
        if (!$mod->uservisible && empty($mod->availableinfo)) {
            return $output;
        }
        
        if ($completioninfo === null) {
            $completioninfo = new completion_info($course);
        }

        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }
        
        if ($this->page->user_is_editing()) {
        
            $output .= html_writer::start_tag('div');

            if ($this->page->user_is_editing()) {
                $output .= course_get_cm_move($mod, $sectionreturn);
            }

            $output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer'));

            // This div is used to indent the content.
            $output .= html_writer::div('', $indentclasses);

            // Start a wrapper for the actual content to keep the indentation consistent
            $output .= html_writer::start_tag('div');

            // Display the link to the module (or do nothing if module has no url)
            $cmname = $this->course_section_cm_name($mod, $displayoptions);

            if (!empty($cmname)) {
                // Start the div for the activity title, excluding the edit icons.
                $output .= html_writer::start_tag('div', array('class' => 'activityinstance'));
                $output .= $cmname;

                // Module can put text after the link (e.g. forum unread)
                $output .= $mod->afterlink;

                // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
                $output .= html_writer::end_tag('div'); // .activityinstance
            }

            // If there is content but NO link (eg label), then display the
            // content here (BEFORE any icons). In this case cons must be
            // displayed after the content so that it makes more sense visually
            // and for accessibility reasons, e.g. if you have a one-line label
            // it should work similarly (at least in terms of ordering) to an
            // activity.
            $contentpart = $this->course_section_cm_text($mod, $displayoptions);
            $url = $mod->url;
            if (empty($url)) {
                $output .= $contentpart;
            }

            $modicons = '';
            if ($this->page->user_is_editing()) {
                $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
                $modicons .= ' '. $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
                $modicons .= $mod->afterediticons;
            }

            $modicons .= $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);

            if (!empty($modicons)) {
                $output .= html_writer::span($modicons, 'actions');
            }

            // If there is content AND a link, then display the content here
            // (AFTER any icons). Otherwise it was displayed before
            if (!empty($url)) {
                $output .= $contentpart;
            }

            // show availability info (if module is not available)
            $output .= $this->course_section_cm_availability($mod, $displayoptions);

            $output .= html_writer::end_tag('div'); // $indentclasses

            // End of indentation div.
            $output .= html_writer::end_tag('div');

            $output .= html_writer::end_tag('div');
        
        } else {
            
            $output .= html_writer::start_tag('div', array('class'=>'activity-box clearfix'));

            $output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer activityname-box'));

            // This div is used to indent the content.
            $output .= html_writer::div('', $indentclasses);

            // Start a wrapper for the actual content to keep the indentation consistent
            $output .= html_writer::start_tag('div');

            // Display the link to the module (or do nothing if module has no url)
            $cmname = $this->course_section_cm_name($mod, $displayoptions);

            if (!empty($cmname)) {
                // Start the div for the activity title, excluding the edit icons.
                $output .= html_writer::start_tag('div', array('class' => 'activityinstance'));
                $output .= $cmname;

                // Module can put text after the link (e.g. forum unread)
                $output .= $mod->afterlink;
                
                // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
                $output .= html_writer::end_tag('div'); // .activityinstance
            }
            
            // If there is content but NO link (eg label), then display the
            // content here (BEFORE any icons). In this case cons must be
            // displayed after the content so that it makes more sense visually
            // and for accessibility reasons, e.g. if you have a one-line label
            // it should work similarly (at least in terms of ordering) to an
            // activity.
            $contentpart = $this->course_section_cm_text($mod, $displayoptions);
            
            if (!empty($contentpart)){
                if (empty($url)) {
                    $output .= html_writer::tag('div', $contentpart, array('class'=>'mod-text'));
                } else {
                    $output .= html_writer::tag('div', $contentpart, array('class'=>'mod-description'));
                }
            }

            // show availability info (if module is not available)
            $output .= $this->course_section_cm_availability($mod, $displayoptions);
            
            $output .= html_writer::end_tag('div'); // $indentclasses

            // End of indentation div.
            $output .= html_writer::end_tag('div');
            
            $output .= html_writer::start_tag('div', array('class'=>'activity-buttons'));

                if ($mod->modname != 'label') {
                    $viewbtn = get_string('view', 'format_institutes_ceu').' ' . get_string('pluginname', 'mod_' . $mod->modname);
                    switch ($mod->modname) {
                        case 'quiz':
                            $viewbtn = html_writer::tag('span', get_string('take', 'format_institutes_ceu').' ' . get_string('pluginname', 'mod_' . $mod->modname), array('class'=>'take-quiz'));
                            $viewbtn .= html_writer::tag('span', get_string('takeexam', 'format_institutes_ceu'), array('class'=>'take-exam'));
                            break;
                        case 'scorm':
                            $viewbtn = get_string('viewmodule', 'format_institutes_ceu');
                            break;
                        case 'url':
                            $viewbtn = get_string('viewwebsite', 'format_institutes_ceu');
                            break;
                        case 'lti':
                            $viewbtn = get_string('viewlink', 'format_institutes_ceu');
                            break;
                    }

                    $link = '';
                    if ($mod->uservisible and $mod->url) {
                        $onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);
                        $link = html_writer::link($mod->url, $viewbtn, array('class' => 'btn', 'onclick' => $onclick));
                    } else {
                        $link = html_writer::link('javascript:void(0)', $viewbtn, array('class' => 'btn btn-disabled'));
                    }
                    $output .= $link;
                }
            
            $output .= html_writer::end_tag('div');
            
            $output .= html_writer::start_tag('div', array('class'=>'completion-buttons'));
            $output .= $this->course_section_cm_completion_button($course, $completioninfo, $mod, $displayoptions);
            $output .= html_writer::end_tag('div');
            
            $output .= html_writer::end_tag('div');
        }
        return $output;
    }
    
    /**
     * Renders HTML to display a list of course modules in a course section
     * Also displays "move here" controls in Javascript-disabled mode
     *
     * This function calls {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course course object
     * @param int|stdClass|section_info $section relative section number or section object
     * @param int $sectionreturn section number to return to
     * @param int $displayoptions
     * @return void
     */
    public function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $USER, $CFG;

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // check if we are currently in the process of moving a module with JavaScript disabled
        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }
        
        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($mod->modname == 'resource' and !$this->page->user_is_editing()){
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
                
                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }

                if ($modulehtml = $this->course_section_cm_list_item($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }

        $sectionoutput = '';
        if (!empty($moduleshtml) || $ismoving) {
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $sectionoutput .= html_writer::tag('li',
                            html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                            array('class' => 'movehere'));
                }

                $sectionoutput .= $modulehtml;
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $sectionoutput .= html_writer::tag('li',
                        html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                        array('class' => 'movehere'));
            }
        }

        // Always output the section module list.
        $output .= html_writer::tag('ul', $sectionoutput, array('class' => 'section img-text'));

        return $output;
    }
    
    /**
     * Renders html for completion box on course page
     *
     * If completion is disabled, returns empty string
     * If completion is automatic, returns an icon of the current completion state
     * If completion is manual, returns a form (with an icon inside) that allows user to
     * toggle completion
     *
     * @param stdClass $course course object
     * @param completion_info $completioninfo completion info for the course, it is recommended
     *     to fetch once for all modules in course/section for performance
     * @param cm_info $mod module to show completion for
     * @param array $displayoptions display options, not used in core
     * @return string
     */
    public function course_section_cm_completion_button($course, &$completioninfo, cm_info $mod, $displayoptions = array()) {
        global $CFG;
        $output = '';
        if (!empty($displayoptions['hidecompletion']) || !isloggedin() || isguestuser() || (!$mod->uservisible && empty($mod->availableinfo))) {
            return $output;
        }
        if ($completioninfo === null) {
            $completioninfo = new completion_info($course);
        }
        $completion = $completioninfo->is_enabled($mod);
        if ($completion == COMPLETION_TRACKING_NONE) {
            return $output;
        }

        $completiondata = $completioninfo->get_data($mod, true);
        $completed = false; $completiontype = '';
        $completionclass = ' notstarted';
        $completiontext = $completiontitle = get_string('notstarted', 'format_institutes_ceu');
        $onclick = "";
        
        if ($completion == COMPLETION_TRACKING_MANUAL) {

            $newstate = $completiondata->completionstate == COMPLETION_COMPLETE
                    ? COMPLETION_INCOMPLETE
                    : COMPLETION_COMPLETE;
            
            $completionclass = ($completiondata->completionstate == COMPLETION_COMPLETE) ? ' completed' : ' notcompleted';
            $completiontitle = ($completiondata->completionstate == COMPLETION_COMPLETE) ? get_string('completed', 'format_institutes_ceu') : get_string('notcompleted', 'format_institutes_ceu');
            $newcompletiontitle = ($completiondata->completionstate == COMPLETION_COMPLETE) ? get_string('notcompleted', 'format_institutes_ceu') : get_string('completed', 'format_institutes_ceu');
            $completiontext = ($completiondata->completionstate == COMPLETION_COMPLETE) ? get_string('markincomplete', 'format_institutes_ceu') : get_string('markcomplete', 'format_institutes_ceu');
            $newcompletiontext = ($completiondata->completionstate == COMPLETION_COMPLETE) ? get_string('markcomplete', 'format_institutes_ceu') : get_string('markincomplete', 'format_institutes_ceu');
            $onclick = '_toggleModuleCompletion(\''.$mod->id.'\');';
            $completiontype = ' manual';
            
            $output .= html_writer::start_tag('form', array('method' => 'post',
                'action'=> new moodle_url('/course/togglecompletion.php'),
                'class' => 'togglecompletion',
                'id'    => 'toggle_module_completion_' . $mod->id));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'id', 'value' => $mod->id));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'modulename', 'value' => $mod->name));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'completionstate', 'value' => $newstate));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'state', 'value' => $completiondata->completionstate));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'title', 'value' => $completiontext));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'newtitle', 'value' => $newcompletiontext));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'icontitle', 'value' => $newcompletiontitle));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'newicontitle', 'value' => $completiontitle));
            $output .= html_writer::end_tag('form');
        } else {
            switch($completiondata->completionstate) {
                case COMPLETION_COMPLETE:
                    $completed = true; break;
                case COMPLETION_COMPLETE_PASS:
                    $completed = true; break;
            }
            
            if ($completiondata->viewed and !$completed){
                $completionclass = ' inprogress';
                $completiontext = $completiontitle = get_string('inprogress', 'format_institutes_ceu');
            } elseif ($completed) {
                $completionclass = ' completed';
                $completiontext = $completiontitle = get_string('completed', 'format_institutes_ceu');
            }
            $completiontype = ' auto';
        }
        
        $output .= html_writer::start_tag('div', array('class'=>'clearfix completion-actions-box'.$completiontype.$completionclass, 'id'=>'toggle_module_completion_box_' . $mod->id));
            if (!empty($onclick)){
                $output .= html_writer::tag('span', '', array('class'=>'completion-actions-icon', 'title'=>$completiontitle, 'onclick'=>$onclick));
                $output .= html_writer::link('javascript:void(0);', $completiontext, array('class' => 'completion-actions-text', 'onclick'=>$onclick));
            } else {
                $output .= html_writer::tag('span', '', array('class'=>'completion-actions-icon', 'title'=>$completiontitle));
                $output .= html_writer::tag('span', $completiontext, array('class' => 'completion-actions-text'));
            }
        $output .= html_writer::end_tag('div');
        
        return $output;
    }
    
    public function course_section_cm_quiz_button($course, &$completioninfo, cm_info $mod, $displayoptions = array()) {
        global $CFG;
        $output = '';
        if (!$mod->uservisible) return $output;
        
        $divider = '<span class="btn-divider"></span>';
        
        $output .= $divider.html_writer::link('#', 'AVG: N/A', array('class' => 'btn btn-empty'));
        $output .= $divider.html_writer::link('#', 'Attempts', array('class' => 'btn btn-empty'));
        
        return $output;
    }
	  
}

?>