<?php

require_once("../../config.php");
require_once("locallib.php");
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/gradelib.php');

require_login();

$courseid = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
require_login($course);

$title = get_string('pluginname', 'local_gradebook_ceu');

$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url("/local/gradebook_ceu/index.php", array()));
$PAGE->requires->jquery();
$PAGE->navbar->add($title, new moodle_url('/local/gradebook_ceu/index.php', array('id'=>$course->id)));
$PAGE->set_title($title);
$PAGE->set_pagelayout('course');
$PAGE->set_heading($title);

echo $OUTPUT->header();

echo html_writer::start_tag('div', array('class'=>'gradebook_ceu'));
echo $OUTPUT->heading($title);

echo local_gradebook_ceu_display($course);

echo html_writer::end_tag('div');

echo $OUTPUT->footer();
