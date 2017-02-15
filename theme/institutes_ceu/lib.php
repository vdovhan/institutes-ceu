<?php
// This file is part of Moodle - http://moodle.org/
//
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
 * Moodle's institutes_ceu theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_institutes_ceu
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_institutes_ceu_process_css($css, $theme) {

    // Set the background image for the logo.
    $logo = $theme->setting_file_url('logo', 'logo');
    $css = theme_institutes_ceu_set_logo($css, $logo);

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = theme_institutes_ceu_set_customcss($css, $customcss);

    return $css;
}

/**
 * Adds the logo to CSS.
 *
 * @param string $css The CSS.
 * @param string $logo The URL of the logo.
 * @return string The parsed CSS
 */
function theme_institutes_ceu_set_logo($css, $logo) {
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_institutes_ceu_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM and ($filearea === 'logo' || $filearea === 'smalllogo')) {
        $theme = theme_config::load('institutes_ceu');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css The original CSS.
 * @param string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 */
function theme_institutes_ceu_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Returns an object containing HTML for the areas affected by settings.
 *
 * Do not add institutes_ceu specific logic in here, child themes should be able to
 * rely on that function just by declaring settings with similar names.
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 * @return stdClass An object with the following properties:
 *      - navbarclass A CSS class to use on the navbar. By default ''.
 *      - heading HTML to use for the heading. A logo if one is selected or the default heading.
 *      - footnote HTML to use as a footnote. By default ''.
 */
function theme_institutes_ceu_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG;
    $return = new stdClass;

    $return->navbarclass = '';
    if (!empty($page->theme->settings->invert)) {
        $return->navbarclass .= ' navbar-inverse';
    }

    $return->heading = html_writer::tag('div', '', array('class' => 'logo'));
    
    $return->footnote = '';
    if (!empty($page->theme->settings->footnote)) {
        $return->footnote = '<div class="footnote text-center">'.format_text($page->theme->settings->footnote).'</div>';
    }

    return $return;
}

function theme_institutes_ceu_get_sidebar_topmenu() {
    global $CFG, $DB, $PAGE, $USER, $PAGE, $OUTPUT;
    $output = '';
    
    if (!isloggedin()) return $output;
    
    $active = theme_institutes_ceu_get_menu_active_link();
    $mycourses = enrol_get_my_courses();
    
    $output .= html_writer::start_tag('nav', array('class' => 'main-navigation'));
    $output .= html_writer::start_tag('ul');
    
    if ($PAGE->course->id > 1) {
        $courseid = $PAGE->course->id;
        
        $output .= html_writer::start_tag('li', array('class' => 'activecourse'.(($active == 'activecourse') ? ' active' : '')));
            $output .= html_writer::link('javascript:void(0);', html_writer::tag('span', get_string('coursemenu', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('coursemenu', 'theme_institutes_ceu'), 'onclick'=>'toggleCourseMenu();'));
        
            $output .= theme_institutes_ceu_get_course_menu();
        
        $output .= html_writer::end_tag('li');
    
        $output .= html_writer::start_tag('li', array('class' => 'resources'.(($active == 'resources') ? ' active' : '')));
            $output .= html_writer::link(new moodle_url('/course/format/institutes_ceu/resources.php', array('id'=>$courseid)), html_writer::tag('span', get_string('resources', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('resources', 'theme_institutes_ceu')));
        $output .= html_writer::end_tag('li');

        $output .= html_writer::start_tag('li', array('class' => 'gradebook'.(($active == 'gradebook') ? ' active' : '')));
            $output .= html_writer::link(new moodle_url('/local/gradebook_ceu/index.php', array('id'=>$courseid)), html_writer::tag('span', get_string('gradebook', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('gradebook', 'theme_institutes_ceu')));
        $output .= html_writer::end_tag('li');
        
        $menu_settings = $DB->get_records_menu('course_format_settings',array('courseid'=>$courseid, 'type'=>'menu'),'name','name,value');
        
        if (isset($menu_settings['glossary']) && !empty($menu_settings['glossary'])) {
            $link = new moodle_url('/mod/glossary/view.php', array('id' => $menu_settings['glossary']));
            $output .= html_writer::start_tag('li', array('class' => 'glossary'.(($active == 'glossary') ? ' active' : '')));
                $output .= html_writer::link($link, html_writer::tag('span', get_string('glossary', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('glossary', 'theme_institutes_ceu')));
            $output .= html_writer::end_tag('li');
        }
        
        if (isset($menu_settings['faq']) && !empty($menu_settings['faq'])) {
            $link = new moodle_url('/mod/glossary/view.php', array('id' => $menu_settings['faq']));
            $output .= html_writer::start_tag('li', array('class' => 'faq'.(($active == 'faq') ? ' active' : '')));
                $output .= html_writer::link($link, html_writer::tag('span', get_string('faq', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('faq', 'theme_institutes_ceu')));
            $output .= html_writer::end_tag('li');
        }
        
    } else {
        
        if (isset($PAGE->theme->settings->glossary_course_id) && $PAGE->theme->settings->glossary_course_id) {
            $link = new moodle_url('/mod/glossary/view.php', array('id' => $PAGE->theme->settings->glossary_course_id));
            $output .= html_writer::start_tag('li', array('class' => 'glossary'.(($active == 'glossary') ? ' active' : '')));
                $output .= html_writer::link($link, html_writer::tag('span', get_string('glossary', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('glossary', 'theme_institutes_ceu')));
            $output .= html_writer::end_tag('li');
        }
        
        if (isset($PAGE->theme->settings->faq_course_id) && $PAGE->theme->settings->faq_course_id) {
            $link = new moodle_url('/mod/glossary/view.php', array('id' => $PAGE->theme->settings->faq_course_id));
            $output .= html_writer::start_tag('li', array('class' => 'faq'.(($active == 'faq') ? ' active' : '')));
                $output .= html_writer::link($link, html_writer::tag('span', get_string('faq', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('faq', 'theme_institutes_ceu')));
            $output .= html_writer::end_tag('li');
        }
        
    }
    
    if (theme_institutes_has_editing_capability()){
        $output .= html_writer::start_tag('li', array('class' => 'preferences'.(($active == 'settings') ? ' active' : '')));
            $output .= html_writer::link('javascript:void(0);', html_writer::tag('span', get_string('settings', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('settings', 'theme_institutes_ceu'), 'onclick'=>'toggleSidePre();'));
        $output .= html_writer::end_tag('li');
    }
    
    if($USER->id){
        
        $output .= html_writer::start_tag('li', array('class' => 'actions'.(($active == 'actions') ? ' active' : '')));
        $output .= html_writer::link( 'javascript:void(0);', html_writer::tag('span', get_string('actions', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('actions', 'theme_institutes_ceu')));
            // $output .= html_writer::tag('div', $OUTPUT->page_heading_button(), array('class'=>'actions-menu'));
        $output .= html_writer::end_tag('li');

        $output .= html_writer::start_tag('li', array('class' => 'bookmarks'.(($active == 'bookmarks') ? ' active' : '')));
        $output .= html_writer::link( 'javascript:void(0);', html_writer::tag('span', get_string('bookmarks', 'theme_institutes_ceu'), array('onclick'=>"bookmarksOpen();") ).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('bookmarks', 'theme_institutes_ceu')));
        $output .= html_writer::end_tag('li');

        $output .= html_writer::start_tag('li', array('class' => 'logout'.(($active == 'logout') ? ' active' : '')));
        $output .= html_writer::link( new moodle_url('/login/logout.php', array('sesskey'=>sesskey()))  , html_writer::tag('span', get_string('logout', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('logout', 'theme_institutes_ceu')));
        $output .= html_writer::end_tag('li');   

    }
    
    $output .= html_writer::start_tag('li', array('class' => 'toggler'));
        $output .= html_writer::link('javascript:void(0);', html_writer::tag('span', '').html_writer::tag('div', html_writer::tag('i', '', array('class' => 'fa fa-angle-right', 'title'=>get_string('open', 'theme_institutes_ceu'))).html_writer::tag('i', '', array('class' => 'fa fa-angle-left', 'title'=>get_string('close', 'theme_institutes_ceu'))), array('class'=>'menu-icon')), array('onclick'=>'toggleLeftSidebar();'));
    $output .= html_writer::end_tag('li');

    $output .= html_writer::end_tag('ul');
    $output .= html_writer::end_tag('nav');
    
    return $output;
}

function theme_institutes_ceu_get_course_menu(){
    global $CFG, $DB, $PAGE, $USER;
    $output = '';
    if (!isloggedin()) return $output;
    
    $course = $PAGE->course;
    $cm 	= $PAGE->cm;
    $issite = ($course->id == SITEID);
    $context = context_course::instance($course->id, MUST_EXIST);
        
    if ($course->id == 1) return $output;
    $is_enrolled = (is_enrolled($context, $USER));
    
    require_once($CFG->dirroot.'/course/lib.php');
    $course = course_get_format($course)->get_course();
    course_create_sections_if_missing($course, range(0, $course->numsections));
    
    if ($course->format != 'institutes_ceu') return $output;
    require_once($CFG->dirroot.'/course/format/'.$course->format.'/lib.php');
    
    $displaysection = optional_param('section', 0, PARAM_INT);
    $modinfo = get_fast_modinfo($course);
    $csections = format_institutes_ceu_get_course_sections($course);
    if (isset($cm->section)){
        $cm_section = $DB->get_record('course_sections', array('id'=>$cm->section));
    }

    $sections = array('root'=>array());
    $currentsectionid = (isset($cm_section->section)) ? $cm_section->section : $displaysection;
    $open_sections = array();
    if ($currentsectionid > 0){
        $currentsection = $modinfo->get_section_info($currentsectionid);
        $currentsection->parent = (isset($csections[$currentsection->id]->parent)) ? $csections[$currentsection->id]->parent : 0;
    }
    
    foreach ($modinfo->get_section_info_all() as $section => $thissection) {
        if ($section == 0) continue;
        $showsection = $thissection->uservisible ||
                ($thissection->visible && !$thissection->available &&
                !empty($thissection->availableinfo));
        if (!$showsection) continue;

        $thissection->parent = (isset($csections[$thissection->id]->parent)) ? $csections[$thissection->id]->parent : 0;
        if (intval($thissection->parent) > 0){
            $sections[$thissection->parent][$thissection->section] = $thissection;
        } else {
            $sections['root'][$thissection->section] = $thissection;
        }
    }
    
    $format_renderer = $PAGE->get_renderer('format_institutes_ceu');
    $parentsection = null;
    if (isset($currentsection->id)){
        $parentsection = $format_renderer->get_parent_section($course, $currentsection->id);
    }

    $output .= html_writer::start_tag('div', array('class'=>'course-content-box'));

    // root sections start
    $output .= html_writer::start_tag('ul', array('class'=>'course-root-sections'));
    
    if (count($sections['root'])){
        foreach ($sections['root'] as $section=>$thissection){
            $thissection->progress = $format_renderer->get_section_completion($course, $section);
            
            $output .= html_writer::start_tag('li', array('class'=>'course-section-box '.$thissection->progress['status'].((($currentsectionid > 0 and ($currentsectionid == $section or (isset($parentsection->section) and $parentsection->section == $section) )) ) ? ' active' : '')));

            $output .= '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'&section='.$section.'" title="'.get_section_name($course, $thissection).'" alt="'.get_section_name($course, $thissection).'" class="section-header'.(($currentsectionid > 0 and $currentsectionid == $section) ? ' current' : '').'">';
            if ($thissection->progress['status'] == 'completed'){
                $output .= html_writer::tag('i', '', array('class'=>'status ion-checkmark-round'));
            }
            $output .= get_section_name($course, $thissection);
            $output .= '</a>';

            $output .= html_writer::end_tag('li');
        }
    }

    $output .= html_writer::end_tag('ul');
    // root sections end

    $output .= html_writer::end_tag('div');

    return $output;
}

function theme_institutes_ceu_get_sidebar_bottommenu() {
    global $CFG, $DB, $PAGE, $USER;
    $output = '';
    
    if (!isloggedin()) return $output;
    $active = theme_institutes_ceu_get_menu_active_link();
    
    $output .= html_writer::start_tag('nav', array('class' => 'settings-navigation main-navigation'));
    $output .= html_writer::start_tag('ul');
    
    if (theme_institutes_has_editing_capability()){
        $output .= html_writer::start_tag('li', array('class' => 'preferences'));
            $output .= html_writer::link('javascript:void(0);', html_writer::tag('span', get_string('settings', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('settings', 'theme_institutes_ceu'), 'onclick'=>'toggleSidePre();'));
        $output .= html_writer::end_tag('li');
    }
    
    $output .= html_writer::start_tag('li', array('class' => 'profile'.(($active == 'profile') ? ' active' : '')));
        $output .= html_writer::link(new moodle_url('/user/profile.php', array('id'=>$USER->id)), html_writer::tag('span', get_string('profile', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('profile', 'theme_institutes_ceu')));
    $output .= html_writer::end_tag('li');
    
    $output .= html_writer::start_tag('li', array('class' => 'logout'));
        $output .= html_writer::link(new moodle_url('/login/logout.php', array('sesskey'=>sesskey())), html_writer::tag('span', get_string('logout', 'theme_institutes_ceu')).html_writer::tag('div', '', array('class'=>'menu-icon')), array('title'=>get_string('logout', 'theme_institutes_ceu')));
    $output .= html_writer::end_tag('li');
    
    
    $output .= html_writer::end_tag('ul');
    $output .= html_writer::end_tag('nav');
    
    return $output;
}

function theme_institutes_ceu_get_menu_active_link() {
    global $CFG, $DB, $PAGE, $USER;
    $activelink = '';
    
    $courseid = $PAGE->course->id;
    if ($courseid > 1){
        $menu_settings = $DB->get_records_menu('course_format_settings',array('courseid'=>$courseid, 'type'=>'menu'),'name','name,value');
    }
       
    if (strstr($PAGE->url, '/mod/glossary')){
        $activelink = 'activecourse';
        if ($PAGE->course->id > 1){
            if (isset($PAGE->cm->id) and (isset($menu_settings['glossary']) or isset($menu_settings['faq']))){
                if ($PAGE->cm->id == $menu_settings['glossary']){
                    $activelink = 'glossary';    
                } elseif ($PAGE->cm->id == $menu_settings['faq']){
                    $activelink = 'faq';
                }
            }    
        } else {
            if (isset($PAGE->cm->id) and (isset($PAGE->theme->settings->glossary_course_id) or isset($PAGE->theme->settings->faq_course_id))){
                if ($PAGE->cm->id == $PAGE->theme->settings->glossary_course_id){
                    $activelink = 'glossary';    
                } elseif ($PAGE->cm->id == $PAGE->theme->settings->faq_course_id){
                    $activelink = 'faq';
                }
            }
        }
    } elseif (strstr($PAGE->url, '/course/format/institutes_ceu/menusettings.php') or strstr($PAGE->url, '/course/format/institutes_ceu/resourcessettings.php') or strstr($PAGE->url, '/course/format/institutes_ceu/editcategory.php')){
        $activelink = '';
    } elseif (strstr($PAGE->url, '/course/format/institutes_ceu/resources.php')){
        $activelink = 'resources';
    } elseif (strstr($PAGE->url, '/local/gradebook/')){
        $activelink = 'gradebook';
    } elseif ($PAGE->course->id > 1){
        $activelink = 'activecourse';
    } elseif (strstr($PAGE->url, '/my/')){
        $activelink = 'dashboard';
    } elseif (strstr($PAGE->url, '/course/index.php')){
        $activelink = 'courses';
    } elseif (strstr($PAGE->url, '/user/preferences.php')){
        $activelink = 'preferences';
    } elseif (strstr($PAGE->url, '/user/profile.php?id='.$USER->id)){
        $activelink = 'profile';
    }
    
    return $activelink;
}

function theme_institutes_ceu_page_init(moodle_page $page) {
    global $USER;
    
    $page->requires->jquery();
    
    user_preference_allow_ajax_update('fix-sidebar', PARAM_INT);
    
    if (isset($page->cm->modname) and $page->cm->modname == 'quiz'){
        $page->blocks->show_only_fake_blocks(false);
    }
    
    $pagelayout = $page->__get('pagelayout');
    
    if ($pagelayout =='mydashboard' and !is_siteadmin()){
        $mycourses = enrol_get_my_courses();
        $redirect = 0;
        if (count($mycourses)){
            foreach ($mycourses as $course){
                if ($course->id > 0){
                    $context =  context_course::instance($course->id);

                    if ($roles = get_user_roles($context, $USER->id)) {
                        foreach ($roles as $role) {
                            if ($role->roleid == 5){
                                $redirect =  $course->id;
                                break;
                            }
                        }
                    }
                    if ($redirect > 0){
                        break;
                    }
                }
            }
        }
        if ($redirect > 0){
            redirect(new moodle_url('/course/view.php', array('id'=>$redirect)));
        }
    }
}

function theme_institutes_ceu_get_course_header() {
    global $CFG, $DB, $PAGE, $USER, $SITE;
    $output = '';
    $context = context_course::instance($PAGE->course->id);
    
    $output .= html_writer::start_tag('div', array('class' => 'small-logo-box clearfix'));
    
    $output .= html_writer::link(new moodle_url('/'), html_writer::tag('div', '', array('class' => 'small-logo')));
    $output .= html_writer::start_tag('div', array('class' => 'course-name-box'));
    $output .= html_writer::link(new moodle_url('/'), $SITE->shortname);
    $output .= html_writer::link(new moodle_url('/course/view.php', array('id'=>$PAGE->course->id)), $PAGE->course->fullname, array('class'=>'course-name'));
    $output .= html_writer::end_tag('div');
    
    $output .= html_writer::end_tag('div');
    $output .= html_writer::end_tag('div');
    return $output;
}

function theme_institutes_ceu_get_course_progress($course) {
    global $CFG, $PAGE, $DB, $USER, $OUTPUT;

    require_once("{$CFG->libdir}/completionlib.php");
    require_once($CFG->dirroot.'/course/lib.php');

    $result = new stdClass();
    $result->completion = 0;
    $result->status = 'notyetstarted';

    $context = context_course::instance($course->id);
    // Can edit settings?
    $can_edit = has_capability('moodle/course:update', $context);

    if ($can_edit){

        $completion = $DB->get_record_sql("SELECT c.id, sc.students_count, cc.completions_count
            FROM {course} c 
                LEFT JOIN (SELECT c.id, COUNT(ra.id) AS students_count FROM {course} c LEFT JOIN {context} ct ON c.id = ct.instanceid LEFT JOIN {role_assignments} ra ON ra.contextid = ct.id WHERE ra.roleid = 5 ) sc ON sc.id = c.id 
                LEFT JOIN (SELECT c.id, COUNT(ra.id) AS completions_count FROM {course} c LEFT JOIN {context} ct ON c.id = ct.instanceid LEFT JOIN {role_assignments} ra ON ra.contextid = ct.id AND ra.roleid = 5 LEFT JOIN {course_completions} cc ON cc.course = c.id AND cc.userid = ra.userid WHERE cc.timecompleted IS NOT NULL ) cc ON cc.id = c.id 
            WHERE c.id = $course->id");

        if ($completion->completions_count > 0 and $completion->students_count > 0){
            $result->completion = round(($completion->completions_count / $completion->students_count) * 100);
        }
        $result->status = 'pending';
    } else {

        // Get course completion data.
        $info = new completion_info($course);

        // Load criteria to display.
        $completions = $info->get_completions($USER->id);

        if ($info->is_tracked_user($USER->id)) {

            // For aggregating activity completion.
            $activities = array();
            $activities_complete = 0;
            $activities_viewed = 0;

            // For aggregating course prerequisites.
            $prerequisites = array();
            $prerequisites_complete = 0;

            // Flag to set if current completion data is inconsistent with what is stored in the database.
            $pending_update = false;

            // Loop through course criteria.
            foreach ($completions as $completion) {
                
                $criteria = $completion->get_criteria();
                $complete = $completion->is_complete();
                
                if (!$pending_update && $criteria->is_pending($completion)) {
                    $pending_update = true;
                }

                // Activities are a special case, so cache them and leave them till last.
                if ($criteria->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {
                    $activities[$criteria->moduleinstance] = $complete;

                    if ($complete) {
                        $activities_complete++;
                    }

                    continue;
                }

                // Prerequisites are also a special case, so cache them and leave them till last.
                if ($criteria->criteriatype == COMPLETION_CRITERIA_TYPE_COURSE) {
                    $prerequisites[$criteria->courseinstance] = $complete;

                    if ($complete) {
                        $prerequisites_complete++;
                    }

                    continue;
                }
            }

            $itemsCompleted  = $activities_complete + $prerequisites_complete;
            $itemsCount      = count($activities) + count($prerequisites);

            // Aggregate completion.
            if ($itemsCount > 0) {
                $result->completion = round(($itemsCompleted / $itemsCount) * 100);
            }

            // Is course complete?
            $coursecomplete = $info->is_course_complete($USER->id);

            // Load course completion.
            $params = array(
                'userid' => $USER->id,
                'course' => $course->id
            );
            $ccompletion = new completion_completion($params);

            // Has this user completed any criteria?
            $criteriacomplete = $info->count_course_user_data($USER->id);

            if ($pending_update) {
                $status = 'pending';
            } else if ($coursecomplete) {
                $status = 'completed';
            } else if (!$criteriacomplete && !$ccompletion->timestarted) {
                $status = 'notyetstarted';
            } else {
                $status = 'inprogress';
            }

            $result->status = $status;
        }
        if ($result->status == 'notyetstarted'){
            $viewed = $DB->get_record_sql("SELECT COUNT(cmc.id) as viewed 
                                            FROM {course_modules_completion} cmc
                                                WHERE cmc.coursemoduleid IN (SELECT id FROM {course_modules} WHERE course = $course->id) AND cmc.userid = $USER->id AND (cmc.viewed > 0 OR cmc.completionstate > 0)");
            if ($viewed->viewed){
                $result->status = 'inprogress';
            }
        }
    }

    return $result;
}

/**
 * All theme functions should start with theme_institutes_ceu_
 * @deprecated since 2.5.1
 */
function institutes_ceu_process_css() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_institutes_ceu_
 * @deprecated since 2.5.1
 */
function institutes_ceu_set_logo() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_institutes_ceu_
 * @deprecated since 2.5.1
 */
function institutes_ceu_set_customcss() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 *
 * @return array
 */
function theme_institutes_ceu_get_user_alerts() {

    global $DB, $USER;

    $alert_records = $DB->get_records('local_nots_alerts', ['userid' => $USER->id, 'new' => 1]);
    $list = html_writer::start_tag('ul', ['class' => 'alerts']);
    foreach ($alert_records as $record) {
        $list .= html_writer::tag('li',
            html_writer::tag('p',
                html_writer::tag('i', '', ['class' => 'fa fa-angle-double-right']) .
                ' ' . $record->title .
                html_writer::tag('i', '',  ['class' => 'fa fa-close text-danger pull-right'], ['class' => 'title']) .
            html_writer::tag('p', $record->body, ['class' => 'body']), ['class' => 'title']
        ), [
            'class' => 'item',
            'data-id' => $record->id
        ]);
    }
    $list .= html_writer::end_tag('ul');
    return [
        'count' => count($alert_records),
        'list' => $list
    ];
}

/**
 * @return array
 */
function theme_institutes_ceu_get_courses_list() {
    global $DB;
    $list = [0 => '-- Select Glossary --'];
    if($courses = $DB->get_records('course', ['id' => 1])) {
        $module = $DB->get_record_sql("SELECT m.id FROM {modules} m WHERE m.name = 'glossary' LIMIT 1");
        if(!$module) {
            return $list;
        }
        foreach ($courses as $course) {
            if($records = $DB->get_records_sql("SELECT cm.id, g.name FROM {course_modules} cm INNER JOIN {glossary} as g ON cm.instance = g.id WHERE cm.course = ? AND cm.module = ?", [$course->id, $module->id])) {
                foreach ($records as $record) {
                    $list[$record->id] = $course->shortname . ' | ' . $record->name . ' (cm_id: ' . $record->id . ')';
                }
            }
        }
    }
    return $list;
}

function theme_institutes_has_editing_capability() {
    global $USER, $PAGE;
    
    if (!isloggedin()){
        return false;
    }
    
    if(is_siteadmin()){
        return true;
    }
    
    $context = context_course::instance($PAGE->course->id);
    if(user_has_role_assignment($USER->id, 3) or user_has_role_assignment($USER->id, 3, $context->id)){
        return true;
    }
    
    return false;
}

/**
 *
 * @return array
 */
function theme_institutes_ceu_print_bookmarks() {
    global $DB, $CFG, $USER, $OUTPUT, $PAGE;
    
    $output = '';
    $bookmarks = $DB->get_records('theme_institutes_bookmarks', array('userid'=>$USER->id), 'timecreated DESC');
    
    $output .= html_writer::start_tag('div', array('class' => 'bookmarks-box'));
    
    $output .= html_writer::start_tag('div', array('class' => 'bookmarks-form'));
    $output .= html_writer::tag('div', get_string('addbookmarks', 'theme_institutes_ceu').'<i class="close fa fa-close" onclick="bookmarksClose();"></i>', array('class' => 'bookmarks-form-title', 'title'=>'Close'));
    $output .= html_writer::start_tag('form', array('action' => '', 'method'=>'POST', 'class'=>'clearfix'));
    $output .= html_writer::tag('label', get_string('name', 'theme_institutes_ceu'));
    $output .= html_writer::empty_tag('input', array('name'=>'bookmark_name', 'type'=>'text', 'value'=>$OUTPUT->page_title(), 'id'=>'bookmark_name'));
    $output .= html_writer::empty_tag('input', array('name'=>'bookmark_id', 'type'=>'hidden', 'value'=>'0', 'id'=>'bookmark_id'));
    $output .= html_writer::empty_tag('input', array('name'=>'bookmark_userid', 'type'=>'hidden', 'value'=>$USER->id, 'id'=>'bookmark_userid'));
    $output .= html_writer::link('javascript:void(0);', get_string('savebookmark', 'theme_institutes_ceu'), array('onclick'=>'bookmarkSave();'));
    $output .= html_writer::end_tag('form');
    
    $output .= html_writer::end_tag('div');
    
    $output .= html_writer::tag('div', get_string('savedbookmarks', 'theme_institutes_ceu'), array('class' => 'saved-bookmarks-title'.((count($bookmarks)) ? '' : ' hidden')));
    $output .= html_writer::start_tag('ul', array('class' => 'bookmarks-list'));
    
    if (count($bookmarks)){
        foreach ($bookmarks as $record) {
            $output .= html_writer::start_tag('li', array('class' => 'bookmark-item'.(($CFG->wwwroot.$record->url == $PAGE->url) ? ' current' : ''), 'data-id' => $record->id));
                $output .= html_writer::link($CFG->wwwroot.$record->url, $record->name, array('class'=>'bookmark-link'));
                $output .= html_writer::start_tag('div', array('class' => 'bookmark-actions'));
                    $output .= html_writer::link('javascript:void(0);', get_string('editname', 'theme_institutes_ceu'), array('onclick'=>'bookmarksEdit('.$record->id.');'));
                    $output .= html_writer::tag('i', '', array('class' => 'fa fa-circle'));
                    $output .= html_writer::link('javascript:void(0);', get_string('delete'), array('onclick'=>'bookmarksDelete('.$record->id.');'));
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('li');
        }
    }
    $output .= html_writer::end_tag('ul');
    
    $output .= html_writer::end_tag('div');
    
    return $output;
}

