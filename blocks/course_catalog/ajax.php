<?php
global $CFG, $OUTPUT,$PAGE;		
require_once('../../config.php');

$action = required_param('action', PARAM_TEXT);

if($action == 'set_user_preferences'){
	$name = required_param('name', PARAM_TEXT);
	$value = required_param('value', PARAM_TEXT);
	
    set_user_preference($name, $value);
    echo '1';
	
}elseif($action == 'catalog-load-courses'){
	
	$page = optional_param('page', 0, PARAM_INT);
	$search = optional_param('search', '', PARAM_TEXT);
    
    require_once($CFG->dirroot.'/course/lib.php');
    
    $PAGE->set_pagelayout('course');
	$PAGE->set_course($SITE);

	$renderer = $PAGE->get_renderer('block_course_catalog');
    
    if ($search != ''){
        $search = str_replace('__', ' ', $search);
    }

	$output = $renderer->catalog_print_my_courses($search, $page);

    echo $output;
}
exit;
?>