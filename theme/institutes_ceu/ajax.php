<?php

require_once('../../config.php');

require_login();

$action = required_param('action',PARAM_TEXT);

if ($action == 'save-bookmark'){
	$name      = required_param('name', PARAM_TEXT);
	$userid    = required_param('userid', PARAM_INT);
	$id        = required_param('id', PARAM_INT);
	$url       = required_param('url', PARAM_TEXT);

	$record = new stdClass();
	$record->userid = $userid;
	$record->name = $name;
	$record->timemodified = time();
    
    if ($id > 0){
        $record->id = $id;
        $DB->update_record('theme_institutes_bookmarks', $record);
    } else {
        $record->url = str_replace($CFG->wwwroot, '', $url);
        $record->timecreated = time();
        $record->id = $DB->insert_record('theme_institutes_bookmarks', $record);    
    }
    
    $output = 'ok';
    if ($id == 0){
        $output = html_writer::start_tag('li', array('class' => 'bookmark-item active', 'data-id' => $record->id));
        $output .= html_writer::link($CFG->wwwroot.$record->url, $record->name, array('class'=>'bookmark-link'));
        $output .= html_writer::start_tag('div', array('class' => 'bookmark-actions'));
            $output .= html_writer::link('javascript:void(0);', get_string('editname', 'theme_institutes_ceu'), array('onclick'=>'bookmarksEdit('.$record->id.');'));
            $output .= html_writer::tag('i', '', array('class' => 'fa fa-circle'));
            $output .= html_writer::link('javascript:void(0);', get_string('delete'), array('onclick'=>'bookmarksDelete('.$record->id.');'));
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('li');
    }
    
    echo $output;
    
} elseif($action == 'delete-bookmark'){
    $id = required_param('id',PARAM_TEXT);
	$DB->delete_records('theme_institutes_bookmarks', array('id'=>$id));
}

exit;
