<?php

require_once($CFG->dirroot. "/mod/quiz/renderer.php");

class theme_institutes_ceu_mod_quiz_renderer extends mod_quiz_renderer{
    
    protected $attemptobj;
    
     /**
     * Builds the review page
     *
     * @param quiz_attempt $attemptobj an instance of quiz_attempt.
     * @param array $slots an array of intgers relating to questions.
     * @param int $page the current page number
     * @param bool $showall whether to show entire attempt on one page.
     * @param bool $lastpage if true the current page is the last page.
     * @param mod_quiz_display_options $displayoptions instance of mod_quiz_display_options.
     * @param array $summarydata contains all table data
     * @return $output containing html data.
     */
    public function review_page(quiz_attempt $attemptobj, $slots, $page, $showall,
                                $lastpage, mod_quiz_display_options $displayoptions,
                                $summarydata) {

        $output = '';
        $output .= $this->header();
        $output .= $this->heading(format_string($attemptobj->get_quiz_name()));
        $output .= html_writer::start_tag('div', array('class'=>'quiz-box'));
        $output .= html_writer::tag('h3', get_string('quizreview', 'theme_institutes_ceu'), array('class'=>'quiz-page-title'));
        $output .= $this->review_summary_table($summarydata, $page);
        $output .= html_writer::div($this->finish_review_link($attemptobj), 'finish-review-wrapper');
        $output .= $this->review_form($page, $showall, $displayoptions,
                $this->questions($attemptobj, true, $slots, $page, $showall, $displayoptions),
                $attemptobj);

        $output .= html_writer::end_tag('div');
        $output .= $this->review_next_navigation($attemptobj, $page, $lastpage, $showall);
        $output .= $this->footer();
        return $output;
    }
    
    /**
     * Outputs the table containing data from summary data array
     *
     * @param array $summarydata contains row data for table
     * @param int $page contains the current page number
     */
    public function review_summary_table($summarydata, $page) {
        $summarydata = $this->filter_review_summary_table($summarydata, $page);
        if (empty($summarydata)) {
            return '';
        }

        $output = '';
        $output .= html_writer::start_tag('table', array(
                'class' => 'generaltable generalbox quizreviewsummary'));
        $output .= html_writer::start_tag('tbody');
        foreach ($summarydata as $key=>$rowdata) {
            if ($key == 'marks') continue; 
            if ($key == 'state') continue;
            /*if ($key == 'state' and $rowdata['content'] == 'Finished'){
                $rowdata['content'] = get_string('completed', 'theme_institutes_ceu');
            }*/
            if ($rowdata['title'] instanceof renderable) {
                $title = $this->render($rowdata['title']);
            } else {
                $title = $rowdata['title'];
            }

            if ($rowdata['content'] instanceof renderable) {
                $content = $this->render($rowdata['content']);
            } else {
                $content = $rowdata['content'];
            }

            $output .= html_writer::tag('tr',
                html_writer::tag('th', $title, array('class' => 'cell', 'scope' => 'row')) .
                        html_writer::tag('td', $content, array('class' => 'cell'))
            );
        }

        $output .= html_writer::end_tag('tbody');
        $output .= html_writer::end_tag('table');
        return $output;
    }

    
    /*
     * View Page
     */
    /**
     * Generates the view page
     *
     * @param int $course The id of the course
     * @param array $quiz Array conting quiz data
     * @param int $cm Course Module ID
     * @param int $context The page context ID
     * @param array $infomessages information about this quiz
     * @param mod_quiz_view_object $viewobj
     * @param string $buttontext text for the start/continue attempt button, if
     *      it should be shown.
     * @param array $infomessages further information about why the student cannot
     *      attempt this quiz now, if appicable this quiz
     */
    public function view_page($course, $quiz, $cm, $context, $viewobj) {
        $output = '';
        $output .= $this->view_information($quiz, $cm, $context, $viewobj->infomessages);
        $output .= $this->view_table($quiz, $context, $viewobj);
        $output .= $this->box($this->view_page_buttons($viewobj), 'quizattempt');
        return $output;
    }
    
    /**
     * Output the page information
     *
     * @param object $quiz the quiz settings.
     * @param object $cm the course_module object.
     * @param object $context the quiz context.
     * @param array $messages any access messages that should be described.
     * @return string HTML to output.
     */
    public function view_information($quiz, $cm, $context, $messages) {
        global $CFG;

        $output = '';

        // Print quiz name and description.
        $output .= $this->heading(format_string($quiz->name));
        
        // Output any access messages.
        if ($messages) {
            $output .= $this->box($this->access_messages($messages), 'quizinfo');
        }

        // Show number of attempts summary to those who can view reports.
        if (has_capability('mod/quiz:viewreports', $context)) {
            if ($strattemptnum = $this->quiz_attempt_summary_link_to_reports($quiz, $cm,
                    $context)) {
                $output .= html_writer::tag('div', $strattemptnum,
                        array('class' => 'quizattemptcounts'));
            }
        }
        return $output;
    }
  
    /**
     * Generates the table heading.
     */
    public function view_table_heading() {
        return $this->heading(get_string('quizcompletion', 'theme_institutes_ceu'), 3);
    }

    /**
     * Generates the table of data
     *
     * @param array $quiz Array contining quiz data
     * @param int $context The page context ID
     * @param mod_quiz_view_object $viewobj
     */
    public function view_table($quiz, $context, $viewobj) {
        if (!$viewobj->attempts) {
            return '';
        }

        // Prepare table header.
        $table = new html_table();
        $table->attributes['class'] = 'generaltable quizattemptsummary';
        $table->head = array();
        $table->align = array();
        $table->size = array();
        if ($viewobj->attemptcolumn) {
            $table->head[] = get_string('attemptnumber', 'quiz');
            $table->align[] = 'center';
            $table->size[] = '';
        }
        /*$table->head[] = get_string('attemptstate', 'theme_institutes_ceu');
        $table->align[] = 'center';
        $table->size[] = '';*/
        
        $table->head[] = get_string('attemptdate', 'theme_institutes_ceu');
        $table->align[] = 'center';
        $table->size[] = '';
        
        if ($viewobj->gradecolumn) {
            $table->head[] = get_string('grade');
            $table->align[] = 'center';
            $table->size[] = '';
        }
        if ($viewobj->canreviewmine) {
            $table->head[] = get_string('reviewattempt', 'theme_institutes_ceu');
            $table->align[] = 'center';
            $table->size[] = '';
        }
        if ($viewobj->feedbackcolumn) {
            $table->head[] = get_string('feedback', 'quiz');
            $table->align[] = 'left';
            $table->size[] = '';
        }

        // One row for each attempt.
        foreach ($viewobj->attemptobjs as $attemptobj) {
            $attemptoptions = $attemptobj->get_display_options(true);
            $row = array();

            // Add the attempt number.
            if ($viewobj->attemptcolumn) {
                if ($attemptobj->is_preview()) {
                    $row[] = html_writer::div(get_string('preview', 'quiz'), 'attempt-number');
                } else {
                    $row[] = html_writer::div($attemptobj->get_attempt_number(), 'attempt-number');
                }
            }

            //$row[] = $this->attempt_status($attemptobj);
            
            $row[] = $this->attempt_datetime($attemptobj);

            // Ouside the if because we may be showing feedback but not grades.
            $attemptgrade = quiz_rescale_grade($attemptobj->get_sum_marks(), $quiz, false);

            if ($viewobj->gradecolumn) {
                if ($attemptoptions->marks >= question_display_options::MARK_AND_MAX &&
                        $attemptobj->is_finished()) {

                    // Highlight the highest grade if appropriate.
                    if ($viewobj->overallstats && !$attemptobj->is_preview()
                            && $viewobj->numattempts > 1 && !is_null($viewobj->mygrade)
                            && $attemptobj->get_state() == quiz_attempt::FINISHED
                            && $attemptgrade == $viewobj->mygrade
                            && $quiz->grademethod == QUIZ_GRADEHIGHEST) {
                        $table->rowclasses[$attemptobj->get_attempt_number()] = 'bestrow';
                    }

                    $row[] = html_writer::div(round(quiz_format_grade($quiz, $attemptgrade)).'/'.round(quiz_format_grade($quiz, $quiz->grade)).': '.((quiz_format_grade($quiz, $attemptgrade)/quiz_format_grade($quiz, $quiz->grade) * 100)).'%', 'grade-result');
                } else {
                    $row[] = '';
                }
            }

            if ($viewobj->canreviewmine) {
                $row[] = html_writer::div($viewobj->accessmanager->make_review_link($attemptobj->get_attempt(),
                        $attemptoptions, $this), 'btn-a');
            }

            if ($viewobj->feedbackcolumn && $attemptobj->is_finished()) {
                if ($attemptoptions->overallfeedback) {
                    $row[] = html_writer::div(quiz_feedback_for_grade($attemptgrade, $quiz, $context), 'btn-a');
                } else {
                    $row[] = '';
                }
            }

            if ($attemptobj->is_preview()) {
                $table->data['preview'] = $row;
            } else {
                $table->data[$attemptobj->get_attempt_number()] = $row;
            }
        } // End of loop over attempts.

        $output = '';
        $output .= $this->view_table_heading();
        $output .= html_writer::table($table);
        return $output;
    }
    
    /**
     * Generate a brief textual desciption of the current state of an attempt.
     * @param quiz_attempt $attemptobj the attempt
     * @param int $timenow the time to use as 'now'.
     * @return string the appropriate lang string to describe the state.
     */
    public function attempt_status($attemptobj) {
        switch ($attemptobj->get_state()) {
            case quiz_attempt::IN_PROGRESS:
                return get_string('stateinprogress', 'quiz');

            case quiz_attempt::OVERDUE:
                return get_string('stateoverdue', 'quiz');

            case quiz_attempt::FINISHED:
                return get_string('statefinished', 'theme_institutes_ceu');

            case quiz_attempt::ABANDONED:
                return get_string('stateabandoned', 'quiz');
        }
    }

    /**
     * Generate a brief textual desciption of the current state of an attempt.
     * @param quiz_attempt $attemptobj the attempt
     * @param int $timenow the time to use as 'now'.
     * @return string the appropriate lang string to describe the state.
     */
    public function attempt_datetime($attemptobj) {
        switch ($attemptobj->get_state()) {
            case quiz_attempt::IN_PROGRESS:
                return '';

            case quiz_attempt::OVERDUE:
                return userdate($attemptobj->get_due_date(), '%d %b %Y at %I:%M:%S');

            case quiz_attempt::FINISHED:
                return userdate($attemptobj->get_submitted_date(), '%d %b %Y at %I:%M:%S');
                
            case quiz_attempt::ABANDONED:
                return '';
        }
    }
    
    /**
     * Returns either a liink or button
     *
     * @param quiz_attempt $attemptobj instance of quiz_attempt
     */
    public function finish_review_link(quiz_attempt $attemptobj) {
        $url = $attemptobj->view_url();

        if ($attemptobj->get_access_manager(time())->attempt_must_be_in_popup()) {
            $this->page->requires->js_init_call('M.mod_quiz.secure_window.init_close_button',
                    array($url), false, quiz_get_js_module());
            return html_writer::empty_tag('input', array('type' => 'button',
                    'value' => get_string('finishreview', 'quiz'),
                    'id' => 'secureclosebutton',
                    'class' => 'mod_quiz-next-nav mod_quiz-review-btn'));

        } else {
            return html_writer::link($url, get_string('finishreview', 'quiz'),
                    array('class' => 'mod_quiz-next-nav mod_quiz-review-btn'));
        }
    }
    
    
    /*
     * Summary Page
     */
    /**
     * Create the summary page
     *
     * @param quiz_attempt $attemptobj
     * @param mod_quiz_display_options $displayoptions
     */
    public function summary_page($attemptobj, $displayoptions) {
        $output = '';
        $output .= $this->header();
        $output .= $this->heading(format_string($attemptobj->get_quiz_name()));
        $output .= html_writer::start_tag('div', array('class'=>'quiz-summary-page'));
        $output .= $this->heading(get_string('quizsubmittionreview', 'theme_institutes_ceu'), 3);
        $output .= html_writer::start_tag('div',  array('class'=>'quiz-summary-attention'));
        $output .= html_writer::empty_tag('img', array('src' => $this->pix_url('i/qattention'),
                        'alt' => get_string('attention', 'theme_institutes_ceu'), 'class' => 'quiz-attention-icon')).get_string('quizsummaryattention', 'theme_institutes_ceu');
        $output .= html_writer::end_tag('div');
        $output .= $this->summary_table($attemptobj, $displayoptions);
        $output .= html_writer::end_tag('div');
        $output .= $this->summary_page_controls($attemptobj);
        $output .= $this->footer();
        return $output;
    }

    /**
     * Generates the table of summarydata
     *
     * @param quiz_attempt $attemptobj
     * @param mod_quiz_display_options $displayoptions
     */
    public function summary_table($attemptobj, $displayoptions) {
        // Prepare the summary table header.
        $table = new html_table();
        $table->attributes['class'] = 'generaltable quizsummaryofattempt boxaligncenter';
        $table->head = array(get_string('question', 'quiz'), get_string('status', 'quiz'), '');
        $table->align = array('center', 'center', 'center');
        $table->size = array('', '', '');
        $tablewidth = count($table->align);
        $table->data = array();

        // Get the summary info for each question.
        $slots = $attemptobj->get_slots();
        foreach ($slots as $slot) {
            // Add a section headings if we need one here.
            $heading = $attemptobj->get_heading_before_slot($slot);
            if ($heading) {
                $cell = new html_table_cell(format_string($heading));
                $cell->header = true;
                $cell->colspan = $tablewidth;
                $table->data[] = array($cell);
                $table->rowclasses[] = 'quizsummaryheading';
            }

            // Don't display information items.
            if (!$attemptobj->is_real_question($slot)) {
                continue;
            }

            // Real question, show it.
            $flag = '';
            if ($attemptobj->is_question_flagged($slot)) {
                $flag = html_writer::empty_tag('img', array('src' => $this->pix_url('i/flagged'),
                        'alt' => get_string('flagged', 'question'), 'class' => 'questionflag icon-post'));
            }
            if ($attemptobj->can_navigate_to($slot)) {
                $row = array(html_writer::link($attemptobj->attempt_url($slot),
                        $flag . $attemptobj->get_question_number($slot)),
                        $attemptobj->get_question_status($slot, $displayoptions->correctness), '');
            } else {
                $row = array($flag . $attemptobj->get_question_number($slot),
                                $attemptobj->get_question_status($slot, $displayoptions->correctness), '');
            }
            
            $table->data[] = $row;
            $table->rowclasses[] = 'quizsummary' . $slot . ' ' . $attemptobj->get_question_state_class(
                    $slot, $displayoptions->correctness);
        }

        // Print the summary table.
        $output = html_writer::table($table);

        return $output;
    }
    
    /**
     * Creates any controls a the page should have.
     *
     * @param quiz_attempt $attemptobj
     */
    public function summary_page_controls($attemptobj) {
        $output = html_writer::start_tag('div', array('class'=>'summary-box-control clearfix'));

        // Return to place button.
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button = new single_button(
                    new moodle_url($attemptobj->attempt_url(null, $attemptobj->get_currentpage())),
                    get_string('returnquiz', 'theme_institutes_ceu'));
            $output .= $this->container($this->container($this->render($button),
                    'controls'), 'submitbtns back-button');
        }

        // Finish attempt button.
        $options = array(
            'attempt' => $attemptobj->get_attemptid(),
            'finishattempt' => 1,
            'timeup' => 0,
            'slots' => '',
            'sesskey' => sesskey(),
        );

        $button = new single_button(
                new moodle_url($attemptobj->processattempt_url(), $options),
                get_string('submitallandfinish', 'theme_institutes_ceu'));
        $button->id = 'responseform';
        /*if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button->add_action(new confirm_action(get_string('confirmclose', 'quiz'), null,
                    get_string('submitallandfinish', 'theme_institutes_ceu')));
        }*/
        
        $output .= $this->container($this->container(
                $this->render($button), 'controls'), 'submitbtns submit-button');
        
        $output .= html_writer::end_tag('div');

        return $output;
    }
    
    /**
     * Attempt Page
     *
     * @param quiz_attempt $attemptobj Instance of quiz_attempt
     * @param int $page Current page number
     * @param quiz_access_manager $accessmanager Instance of quiz_access_manager
     * @param array $messages An array of messages
     * @param array $slots Contains an array of integers that relate to questions
     * @param int $id The ID of an attempt
     * @param int $nextpage The number of the next page
     */
    public function attempt_page($attemptobj, $page, $accessmanager, $messages, $slots, $id,
            $nextpage) {
        $output = '';
        $output .= $this->header();
        $output .= $this->heading(format_string($attemptobj->get_quiz_name()));
        $output .= $this->quiz_notices($messages);
        $output .= $this->attempt_form($attemptobj, $page, $slots, $id, $nextpage);
        $output .= $this->footer();
        return $output;
    }
    
    /**
     * Return the HTML of the quiz timer.
     * @return string HTML content.
     */
    public function countdown_timer(quiz_attempt $attemptobj, $timenow, $block = true) {

        if ($block){
            return '';
        }
        
        $timeleft = $attemptobj->get_time_left_display($timenow);
        if ($timeleft !== false) {
            $ispreview = $attemptobj->is_preview();
            $timerstartvalue = $timeleft;
            if (!$ispreview) {
                // Make sure the timer starts just above zero. If $timeleft was <= 0, then
                // this will just have the effect of causing the quiz to be submitted immediately.
                $timerstartvalue = max($timerstartvalue, 1);
            }
            $this->initialise_timer($timerstartvalue, $ispreview);
        }

        return html_writer::tag('div', get_string('timeleft', 'quiz') . ' ' .
                html_writer::tag('span', '', array('id' => 'quiz-time-left')),
                array('id' => 'quiz-timer', 'role' => 'timer',
                    'aria-atomic' => 'true', 'aria-relevant' => 'text'));
    }
    
    public function attempt_flags($attemptobj, $page, $slots, $id) {
        $output = '';
        $output .= html_writer::start_tag('div', array('class' => "flagged-box"));
        $output .= html_writer::tag('div', get_string('flaggedquestions', 'theme_institutes_ceu'), array('class' => "flagged-box-header"));
        
        $is_flagger = false; $questions = '';
        $output .= html_writer::start_tag('ul', array('class' => "qn_buttons"));
        if ($attemptobj->get_slots()){
            foreach ($attemptobj->get_slots() as $slot) {
                $qa = $attemptobj->get_question_attempt($slot);
                
                $url = '#';
                if ($attemptobj->can_navigate_to($slot)) {
                    $url = $attemptobj->attempt_url($slot, -1, $page);    
                }
                
                $questions .= html_writer::start_tag('li', array('data-id'=>'q'.$slot, 'class' => 'qid '.(($qa->is_flagged()) ? '' : 'hidden')));
                $questions .= html_writer::link($url, get_string('question').' '.$slot);
                $questions .= html_writer::end_tag('li');
                
                if ($qa->is_flagged()){
                    $is_flagger = true;
                }
            }
        }
        $output .= html_writer::tag('li', '<i>'.get_string('noflaggedquestions', 'theme_institutes_ceu').'</i>', array('data-id'=>'q0', 'class' => 'noq '.(($is_flagger) ? 'hidden' : '')));
        $output .= $questions;
        $output .= html_writer::end_tag('ul');
        
        $output .= $this->countdown_timer($attemptobj, time(), false);
        $output .= html_writer::end_tag('div');
        
        return $output;
    }
    
    /**
     * Ouputs the form for making an attempt
     *
     * @param quiz_attempt $attemptobj
     * @param int $page Current page number
     * @param array $slots Array of integers relating to questions
     * @param int $id ID of the attempt
     * @param int $nextpage Next page number
     */
    public function attempt_form($attemptobj, $page, $slots, $id, $nextpage) {
        $output = '';

        // Start the form.
        $output .= html_writer::start_tag('form',
                array('action' => $attemptobj->processattempt_url(), 'method' => 'post',
                'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8',
                'id' => 'responseform'));
        $output .= html_writer::start_tag('div');
        $output .= html_writer::start_tag('div', array('class'=>'quiz-attempt-page'));
        $output .= $this->attempt_flags($attemptobj, $page, $slots, $id);
        $output .= html_writer::start_tag('div', array('class'=>'quiz-attempt-form'));
        
        // Print all the questions.
        foreach ($slots as $slot) {
            $output .= $attemptobj->render_question($slot, false, $this,
                    $attemptobj->attempt_url($slot, $page), $this);
        }
        
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        $navmethod = $attemptobj->get_quiz()->navmethod;
        $this->attemptobj = $attemptobj;
        $output .= $this->attempt_navigation_buttons($page, $attemptobj->is_last_page($page), $navmethod);

        // Some hidden fields to trach what is going on.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'attempt',
                'value' => $attemptobj->get_attemptid()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'thispage',
                'value' => $page, 'id' => 'followingpage'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'nextpage',
                'value' => $nextpage));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'timeup',
                'value' => '0', 'id' => 'timeup'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey',
                'value' => sesskey()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'scrollpos',
                'value' => '', 'id' => 'scrollpos'));

        // Add a hidden field with questionids. Do this at the end of the form, so
        // if you navigate before the form has finished loading, it does not wipe all
        // the student's answers.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'slots',
                'value' => implode(',', $attemptobj->get_active_slots($page))));

        // Finish the form.
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('form');

        $output .= $this->connection_warning();

        return $output;
    }

    /**
     * Display the prev/next buttons that go at the bottom of each page of the attempt.
     *
     * @param int $page the page number. Starts at 0 for the first page.
     * @param bool $lastpage is this the last page in the quiz?
     * @param string $navmethod Optional quiz attribute, 'free' (default) or 'sequential'
     * @return string HTML fragment.
     */
    protected function attempt_navigation_buttons($page, $lastpage, $navmethod = 'free') {
        $output = '';
        
        $output .= html_writer::start_tag('div', array('class' => 'submitbtns quiz-attempt-buttons'));
        $page += 1;
        if ($page > 1 && $navmethod == 'free') {
            $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'previous',
                    'value' => get_string('previous', 'theme_institutes_ceu'), 'class' => 'mod_quiz-prev-nav'));
        }
        
        $pages = $this->attemptobj->get_num_pages();
        
        if ($pages > 0){
            $output .= html_writer::start_tag('div', array('class' => 'mod_quiz-navigation-links'));
            $output .= html_writer::tag('span', '<', array('class' => 'first'));
            
            $period = 5; $j = 0;
            $period = ($pages <= $period) ? $pages : $period;
            $menu = array();
            
            if ($page <= $period-1 or $page > $pages-$period+1 or $pages < 7){
                for ($i = 1; $i <= $period; $i++){
                    $menu[$i] = html_writer::tag('span', $i, array('class'=>(($i == $page) ? 'current' : ''), 'onclick'=>(($i != $page) ? 'pageSubmit("'.($i-1).'", "'.(($i < $page) ? 'previous' : 'next').'");' : ''))); 
                }
            } elseif ($pages > $period) {
                $menu[0] = html_writer::tag('span', '...'); 
            }
            
            if ($pages > 7 and ($page >= $period and $page <= $pages-$period+1)){
                for ($i = ($page > 1) ? $page-2 : 1; $i <= $page+2; $i++){
                    $menu[$i] = html_writer::tag('span', $i, array('class'=>(($i == $page) ? 'current' : ''), 'onclick'=>(($i != $page) ? 'pageSubmit("'.($i-1).'", "'.(($i < $page) ? 'previous' : 'next').'");' : ''))); 
                }
            } elseif ($pages > 10) {
                $menu[0] = html_writer::tag('span', '...'); 
            }
            
            if (($page <= $period-1 or $page > $pages-$period+1 or $pages < 7) and $pages > $period){
                for ($i = $pages-$period+1; $i <= $pages; $i++){
                    $menu[$i] = html_writer::tag('span', $i, array('class'=>(($i == $page) ? 'current' : ''), 'onclick'=>(($i != $page) ? 'pageSubmit("'.($i-1).'", "'.(($i < $page) ? 'previous' : 'next').'");' : ''))); 
                }
            } elseif ($pages > $period) {
                $menu[$pages] = html_writer::tag('span', '...'); 
            }
            
            $output .= implode('', $menu);
            $output .= html_writer::tag('span', '>', array('class' => 'last'));
            $output .= html_writer::end_tag('div');
        }
        
        if ($lastpage) {
        } else {
            $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'next',
                'value' => get_string('next', 'theme_institutes_ceu'), 'class' => 'mod_quiz-next-nav'));
        }
        
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'page_id',
                'value' => '-1', 'id' => 'quiz_page_id'));
        
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'next',
                'value' => get_string('endtest', 'theme_institutes_ceu'), 'class' => 'mod_quiz-next-nav quiz-submit-btn', 'onclick'=>'quizSubmit();'));
        
        $output .= html_writer::end_tag('div');

        return $output;
    }

}

?>
