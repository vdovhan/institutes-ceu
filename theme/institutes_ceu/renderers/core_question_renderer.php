<?php 

require_once($CFG->dirroot. "/question/engine/renderer.php");

class theme_institutes_ceu_core_question_renderer extends core_question_renderer{
    
    /**
     * Generate the information bit of the question display that contains the
     * metadata like the question number, current state, and mark.
     * @param question_attempt $qa the question attempt to display.
     * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
     *      specific parts.
     * @param qtype_renderer $qtoutput the renderer to output the question type
     *      specific parts.
     * @param question_display_options $options controls what should and should not be displayed.
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @return HTML fragment.
     */
    protected function info(question_attempt $qa, qbehaviour_renderer $behaviouroutput,
            qtype_renderer $qtoutput, question_display_options $options, $number) {
        $output = '';
        $output .= $this->number($number);
        $output .= html_writer::start_tag('div', array('class'=>'question-idnumber'));
        $output .= 'ID# '.$number;
        $output .= get_string('questionpoints', 'theme_institutes_ceu', $qa->format_max_mark(0));
        $output .= html_writer::end_tag('div');
        $output .= $this->question_flag($qa, $options->flags);
        
        return $output;
    }
    
}

?>
