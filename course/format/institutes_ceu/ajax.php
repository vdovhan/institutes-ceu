<?php

require('../../../config.php');
require_once('lib.php');

$id     = optional_param('id', '', PARAM_INT);
$action = optional_param('action', '', PARAM_RAW);

if ($action == 'move_resource'){
    $course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
    
    $eid         = optional_param('eid', 0, PARAM_INT);
    $moveafter   = optional_param('moveafter', 0, PARAM_INT);
    
    $moving_field = $DB->get_record("course_format_resources", array('id'=>$eid));
    
    if ($moveafter == 0){
        
        $moving_field->categoryid = ($moving_field->categoryid > 0) ? 0 : $moving_field->categoryid;
        $moving_field->sortorder = 0;
        $DB->update_record("course_format_resources", $moving_field);
        
        $resources = $DB->get_records_sql("SELECT id, sortorder FROM {course_format_resources} WHERE id != $eid AND courseid = :courseid AND type = :type AND categoryid = :categoryid ORDER BY sortorder", array('courseid'=>$course->id, 'type'=>'module', 'categoryid'=>$moving_field->categoryid));
        
        $i = 1;
        foreach ($resources as $resource){
            $resource->sortorder = $i++;
            $DB->update_record("course_format_resources", $resource);
        }    
    } else {
        $moveafter_field = $DB->get_record("course_format_resources", array('id'=>$moveafter));
        $moveafter_fieldid = $moveafter_field->id;
        
        if ($moveafter_field->type == 'category'){
            $moving_field->categoryid = $moveafter_field->id;
            $moving_field->sortorder = 0;
        } else {
            $moving_field->categoryid = $moveafter_field->categoryid;
            $moving_field->sortorder = ++$moveafter_field->sortorder;
        }
        
        $DB->update_record("course_format_resources", $moving_field);
        
        $resources = $DB->get_records_sql("SELECT id, sortorder FROM {course_format_resources} WHERE id != $eid AND courseid = :courseid AND type = :type AND categoryid = :categoryid ORDER BY sortorder", array('courseid'=>$course->id, 'type'=>'module', 'categoryid'=>$moving_field->categoryid));
        
        $i = ($moving_field->sortorder) ? 0 : 1;
        foreach ($resources as $resource){
            $resource->sortorder = $i++;
            $DB->update_record("course_format_resources", $resource);
            if ($resource->id == $moveafter_fieldid) $i++;
        }    
    }
}

exit;