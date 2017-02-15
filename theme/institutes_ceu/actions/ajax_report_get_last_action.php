<?php

require_once('../../../config.php');
require_once('../../../report/log/lib.php');
require_once('../../../lib/completionlib.php');

require_login();

global $DB, $CFG, $USER;

$url = $CFG->wwwroot . '/';
$log_target = ['course', 'course_module', 'submission_status'];
$condition = 'userid = ?';
$param = [
    $USER->id
];

if($course_id = optional_param('course_id', null, PARAM_INT)) {
    $condition .= ' AND courseid = ? ';
    $param[] = $course_id;

    // if use $course_id unset core course; use course_module
    unset($log_target[0]);
}

$condition .= ' AND target IN ("' . implode('", "', $log_target) . '")';

$record = $DB->get_record_sql('SELECT * FROM {logstore_standard_log} WHERE ' . $condition . ' ORDER BY timecreated DESC', $param);

if($record) {

    if($context = context::instance_by_id($record->contextid, IGNORE_MISSING)) {
        $url = $context
            ->get_url()
            ->__toString();

        if($record->target == 'course' && $record->action == 'viewed') {
            $other_params = unserialize($record->other);
            if(isset($other_params['coursesectionnumber'])) {
                $url .= '&section=' . $other_params['coursesectionnumber'];
            }
        }

        if($record->target == 'course_module' && $record->action == 'viewed' && $record->contextinstanceid) {

            // Get course-modules entry
            $cm = get_coursemodule_from_id(null, $record->contextinstanceid, null, true, MUST_EXIST);
            $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

            // Set up completion object and check it is enabled.
            $completion = new completion_info($course);

            // Get completion status
            $current = $completion->get_data($cm, false, $USER->id);
            if($current->completionstate) {

                $result = [
                    'firstmodules' => false,
                    'prev_mod' => 0,
                    'next_mod' => 0,
                    'break_prev' => false,
                    'break_next' => false
                ];

                $modinfo = get_fast_modinfo($course);
                foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                    if (!$thissection->visible) {
                        continue;
                    }

                    if (!empty($modinfo->sections[$thissection->section])) {
                        foreach ($modinfo->sections[$thissection->section] as $modnumber) {
                            $mod = $modinfo->cms[$modnumber];

                            if ((!$mod->uservisible or !$mod->has_view()) and !has_capability('moodle/course:viewhiddenactivities', $mod->context)) {
                                continue;
                            }

                            if ($cm->id == $mod->id){
                                if (!$result['break_prev']) {
                                    $result['firstmodules'] = true;
                                }
                                $result['break_prev'] = true;
                                $result['break_next'] = true;
                            } else {
                                if (!$result['break_prev']) {
                                    $result['prev_mod'] = $mod;
                                }

                                if ($result['break_next']) {
                                    $result['next_mod'] = $mod;
                                    break;
                                }
                            }
                        }
                    }
                    if (($result['prev_mod'] or $result['firstmodules']) and $result['next_mod']){
                        break;
                    }
                }

                if($result['next_mod']) {
                    $url = $result['next_mod']->url->__toString();;
                }
            }
        }
    }
}

die(json_encode([
    'url' => $url,
    'code' => $url ? 200 : 301
]));

