<?php
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * institutes version file.
 *
 * @package    format_institutes_ceu
 * @author     institutes
 * @copyright  2016 sebale, sebale.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */


require('../../../config.php');
require_once('lib.php');

$systemcontext   = context_system::instance();

$id = required_param('id', PARAM_INT); // Option id.

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);

$resources = format_institutes_ceu_get_course_resources($course);
$fs = get_file_storage();
$usstates = format_institutes_ceu_get_states_list();
$mystate = format_institutes_ceu_get_mystate();

$title = get_string('resources', 'format_institutes_ceu');

$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$pageparams = array('id' => $id);
$PAGE->set_url('/course/format/institutes_ceu/resources.php', $pageparams);
$PAGE->navbar->add($title, new moodle_url('/course/format/institutes_ceu/resources.php', $pageparams));

$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title, 2, 'resources-heading');

echo html_writer::start_tag('div', array('class' => 'resources-box ceu-resources'));

    echo html_writer::start_tag('ul', array('class'=>'sorting'));

    if (count($resources)){
        foreach ($resources as $resource){
            $str_download = get_string('download', 'format_institutes_ceu');
            
            $can_view = true; $states = array();
            if (!empty($resource->states)){
                $states = explode(',', $resource->states);
            }
            if (count($states) and !empty($mystate) and !in_array($mystate, $states)){
                $can_view = false;
            }

            $resource->resourcetext = file_rewrite_pluginfile_urls($resource->resourcetext, 'pluginfile.php', $context->id, 'format_intitutes_ceu', 'resourcetext', $resource->id);

            $options = new stdClass();
            $options->noclean = true;
            $options->overflowdiv = true;
            $description = format_text($resource->resourcetext, 1, $options);
            
            $files = $fs->get_area_files($context->id, 'format_institutes_ceu', 'resourcefile', $resource->id);
            if (count($files) > 0) {
                foreach ($files as $file){
                    if ($file->get_filename() == '.') continue;

                    $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
                }
            }

            echo html_writer::start_tag('li', array('class'=>'clearfix', 'data-id'=>$resource->id));
                echo html_writer::tag('h3', $resource->title, array('class'=>'resource-title'));
                echo html_writer::start_tag('div', array('class'=>'resource-description'));
                    echo $description;
                echo html_writer::end_tag('div');
                echo html_writer::start_tag('div', array('class'=>'resource-link'));
                    echo html_writer::link("javascript:void(0)", (!empty($resource->filename)) ? $resource->filename : $str_download, array('onclick'=>'resourcePopupOpen('.$resource->id.')', 'class'=>'btn'));
                echo html_writer::end_tag('div');
                
                // popup box
                    echo html_writer::start_tag('div', array('id'=>'resource_'.$resource->id, 'class'=>'instructions-popup-box'));
                        echo html_writer::start_tag('div', array('class' => 'course-notifications-title'.(($can_view) ? '' : ' not-allowed')));
                            echo  html_writer::tag('i', '', array('class' => 'ion-close course-notifications-close', 'title'=>get_string('close', 'format_institutes_ceu'), 'onclick'=>'resourcePopupClose();'));
                        
                            if ($can_view){
                                echo (!empty($resource->filename)) ? $resource->filename : $str_download;
                            } else {
                                echo  html_writer::tag('span', '', array('class' => 'icon-alert'));
                                echo get_string('printingrestrictions', 'format_institutes_ceu');
                            }
            
                        echo html_writer::end_tag('div');
                        
                        echo html_writer::start_tag('div', array('class' => 'course-notifications-message'));
                            if (!$can_view){
                                if (!empty($resource->popuptext)){
                                    echo $resource->popuptext;   
                                } elseif (is_array($states) and count($states)){
                                    $str_states = array();
                                    foreach ($states as $st){
                                        $str_states[] = $usstates[$st];
                                    }
                                    echo get_string('notpermitted', 'format_institutes_ceu', implode(', ', $str_states));
                                }
                            }
                        echo html_writer::end_tag('div');
                        
                        echo html_writer::start_tag('div', array('class'=>'instructions-popup-buttons'));
                            echo html_writer::link("javascript:void(0)", get_string('close', 'format_institutes_ceu'), array('onclick'=>'resourcePopupClose()', 'class'=>'btn btn-cancel'));

                            if (isset($url) and !empty($url) and $can_view){
                                echo html_writer::link($url, get_string('viewfile', 'format_institutes_ceu'), array('onclick'=>'resourcePopupClose()', 'class'=>'btn', 'target'=>'_blank'));
                            }

                        echo html_writer::end_tag('div');
                    echo html_writer::end_tag('div');
                // popup end

            echo html_writer::end_tag('li');
        }
    }
    
    echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');

echo $OUTPUT->footer();
