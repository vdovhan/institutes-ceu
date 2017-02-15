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

require_once($CFG->dirroot . '/theme/bootstrapbase/renderers.php');

/**
 * institutes_ceu core renderers.
 *
 * @package    theme_institutes_ceu
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_institutes_ceu_core_renderer extends theme_bootstrapbase_core_renderer {

    /**
     * Either returns the parent version of the header bar, or a version with the logo replacing the header.
     *
     * @since Moodle 2.9
     * @param array $headerinfo An array of header information, dependant on what type of header is being displayed. The following
     *                          array example is user specific.
     *                          heading => Override the page heading.
     *                          user => User object.
     *                          usercontext => user context.
     * @param int $headinglevel What level the 'h' tag will be.
     * @return string HTML for the header bar.
     */
    public function context_header($headerinfo = null, $headinglevel = 1) {

        if ($this->should_render_logo($headinglevel)) {
            return html_writer::tag('div', '', array('class' => 'logo'));
        }
        return parent::context_header($headerinfo, $headinglevel);
    }

    /**
     * Determines if we should render the logo.
     *
     * @param int $headinglevel What level the 'h' tag will be.
     * @return bool Should the logo be rendered.
     */
    protected function should_render_logo($headinglevel = 1) {
        global $PAGE;

        // Only render the logo if we're on the front page or login page
        // and the theme has a logo.
        if ($headinglevel == 1 && !empty($this->page->theme->settings->logo)) {
            if ($PAGE->pagelayout == 'frontpage' || $PAGE->pagelayout == 'login') {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the navigation bar home reference.
     *
     * The small logo is only rendered on pages where the logo is not displayed.
     *
     * @param bool $returnlink Whether to wrap the icon and the site name in links or not
     * @return string The site name, the small logo or both depending on the theme settings.
     */
    public function navbar_home($returnlink = true) {
        global $CFG;

        if ($this->should_render_logo() || empty($this->page->theme->settings->smalllogo)) {
            // If there is no small logo we always show the site name.
            return $this->get_home_ref($returnlink);
        }
        $imageurl = $this->page->theme->setting_file_url('smalllogo', 'smalllogo');
        $image = html_writer::img($imageurl, get_string('sitelogo', 'theme_' . $this->page->theme->name),
            array('class' => 'small-logo'));

        if ($returnlink) {
            $logocontainer = html_writer::link($CFG->wwwroot, $image,
                array('class' => 'small-logo-container', 'title' => get_string('home')));
        } else {
            $logocontainer = html_writer::tag('span', $image, array('class' => 'small-logo-container'));
        }

        // Sitename setting defaults to true.
        if (!isset($this->page->theme->settings->sitename) || !empty($this->page->theme->settings->sitename)) {
            return $logocontainer . $this->get_home_ref($returnlink);
        }

        return $logocontainer;
    }
    
    /**
     * Return the navbar content so that it can be echoed out by the layout
     *
     * @return string XHTML navbar
     */
    public function navbar() {
        global $DB, $PAGE;
        
        $items = $this->page->navbar->get_items();
        $itemcount = count($items);
        if ($itemcount === 0) {
            return '';
        }
        
        $course = $this->page->course;
        if ($course->id == 1) return '';
        
        $displaysection = optional_param('section', 0, PARAM_INT);
        if ($displaysection > 0){
            $c_section = $DB->get_record('course_sections', array('section'=>$displaysection, 'course'=>$course->id));
            $displaysection = (isset($c_section->id)) ? $c_section->id : 0;
        }
        $cm = (isset($this->page->cm->id)) ? $this->page->cm : null;
        $course_sections = $this->get_course_sections($course);
        
        $currentsectionid = (isset($cm->section)) ? $cm->section : $displaysection;
        if ($currentsectionid == 0 and !stristr($PAGE->url, '/local/gradebook_ceu') and !stristr($PAGE->url, '/format/institutes_ceu/')) return '';
        
        if ($currentsectionid > 0){
            $currentsection = $course_sections[$currentsectionid];
            if ($currentsection->level == 2){
                $child_section = 0;
                foreach ($course_sections as $section){
                    if ($section->parent == $currentsectionid){
                        $currentsectionid = $section->id;
                        break;
                    }
                }
            }
        }
        
        $htmlblocks = array();
        // Iterate the navarray and display each node
        $separator = ' > ';
        for ($i=0;$i < $itemcount;$i++) {
            $item = $items[$i];
            
            if ($i == 0) continue;
            
            if ($item->type != global_navigation::TYPE_COURSE and $item->type != global_navigation::TYPE_ACTIVITY and $item->type != global_navigation::TYPE_RESOURCE and $item->type != global_navigation::TYPE_CUSTOM) continue;
            $item->hideicon = true;
            
            if ($item->type == global_navigation::TYPE_COURSE){
                $item->text = $item->title = $item->shorttext = get_string('home', 'theme_institutes_ceu');
            }
            
            $options = ($item->type == global_navigation::TYPE_ACTIVITY or $item->type == global_navigation::TYPE_RESOURCE or $item->type == global_navigation::TYPE_CUSTOM) ? array('class'=>'active') : array();
            
            if ($i===0 or count($htmlblocks) == 0) {
                $content = html_writer::tag('li', $this->render($item), $options);
            } else {
                $content = html_writer::tag('li', $separator.$this->render($item), $options);
            }
            
            if ($item->type == global_navigation::TYPE_COURSE){
                
                if (isset($course_sections[$currentsectionid]) and $course_sections[$currentsectionid]->section > 0){
                    
                    $shortname = ($course_sections[$currentsectionid]->level > 1 and isset($cm->id));
                    
                    $parentid = 0;
                    if ($course_sections[$currentsectionid]->level > 0 and $course_sections[$currentsectionid]->parentssequence != ''){
                        $parents = explode(',',$course_sections[$currentsectionid]->parentssequence);
                        if (count($parents)){
                            foreach ($parents as $parent){
                                if (!isset($course_sections[$parent]) or $course_sections[$parent]->level == 1) continue;
                                
                                $item->title = get_section_name($course, $course_sections[$parent]);
                                $item->text = $item->shorttext = $item->title;
                                $item->type == global_navigation::TYPE_SECTION;    
                                $item->action = new moodle_url('/course/view.php', array('id'=>$course->id, 'section'=>$course_sections[$parent]->section));
                                $content .= html_writer::tag('li', $separator.$this->render($item));
                                
                                $parentid = $course_sections[$parent]->section;
                            }
                        }
                    }
                    
                    $item->title = get_section_name($course, $course_sections[$currentsectionid]);
                    $item->text = $item->shorttext = $item->title;
                    
                    $item->type == global_navigation::TYPE_SECTION;    
                    
                    if (!$PAGE->user_is_editing() and $course_sections[$currentsectionid]->level == 2 and $parentid > 0){
                        $item->action = new moodle_url('/course/view.php', array('id'=>$course->id, 'section'=>$parentid));
                    } else {
                        $item->action = new moodle_url('/course/view.php', array('id'=>$course->id, 'section'=>$course_sections[$currentsectionid]->section));
                    }
                    
                    $options = (isset($cm->section)) ? array() : array('class'=>'active');
                    $content .= html_writer::tag('li', $separator.$this->render($item), $options);
                }
            }
            
            $htmlblocks[] = $content;
        }

        //accessibility: heading for navbar list  (MDL-20446)
        $navbarcontent = html_writer::tag('span', get_string('pagepath'),
                array('class' => 'accesshide', 'id' => 'navbar-label'));
        $navbarcontent .= html_writer::tag('nav',
                html_writer::tag('ul', join('', $htmlblocks), array('class'=>'breadcrumb')),
                array('aria-labelledby' => 'navbar-label'));
        // XHTML
        return $navbarcontent;
    }
    
    public function get_course_sections($course) {
        global $PAGE, $DB;
        
        $sections = array();
        $allsections = $DB->get_records_sql("SELECT s.*, fs.parent, fs.level, fs.parentssequence, fs.sectiontype FROM {course_sections} s LEFT JOIN {course_format_sections} fs ON fs.sectionid = s.id AND fs.courseid = s.course AND fs.format = 'institutes_ceu' WHERE s.course = $course->id ORDER BY s.section");
        if (count($allsections)){
            foreach($allsections as $section){
                $sections[$section->id] = $section;
            }
        }
        
        return $sections;
    }

    /**
     * Returns a reference to the site home.
     *
     * It can be either a link or a span.
     *
     * @param bool $returnlink
     * @return string
     */
    protected function get_home_ref($returnlink = true) {
        global $CFG, $SITE;

        $sitename = format_string($SITE->shortname, true, array('context' => context_course::instance(SITEID)));

        if ($returnlink) {
            return html_writer::link($CFG->wwwroot, $sitename, array('class' => 'brand', 'title' => get_string('home')));
        }

        return html_writer::tag('span', $sitename, array('class' => 'brand'));
    }
    
    /**
     * Outputs a heading
     *
     * @param string $text The text of the heading
     * @param int $level The level of importance of the heading. Defaulting to 2
     * @param string $classes A space-separated list of CSS classes. Defaulting to null
     * @param string $id An optional ID
     * @return string the HTML to output.
     */
    public function heading($text, $level = 2, $classes = null, $id = null) {
        global $PAGE;
        
        if (isset($PAGE->cm->id)){
            $classes = ($classes) ? $classes.' activity-heading' : 'activity-heading';
        }
        $level = (integer) $level;
        if ($level < 1 or $level > 6) {
            throw new coding_exception('Heading level must be an integer between 1 and 6.');
        }
        return html_writer::tag('h' . $level, $text, array('id' => $id, 'class' => renderer_base::prepare_classes($classes)));
    }
}
