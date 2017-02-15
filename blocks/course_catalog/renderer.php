<?php
/**
 * course_catalog block rendrer
 *
 * @package    block_course_catalog
 * @copyright  2015 SEBALE (http://sebale.net)
 */
defined('MOODLE_INTERNAL') || die;

class block_course_catalog_renderer extends plugin_renderer_base {
    
    public $coursesperpage = 12;
    
    public function course_catalog() {
		global $USER, $OUTPUT, $CFG, $DB, $PAGE;
		
        $html = html_writer::tag('div', get_string('dashboard', 'block_course_catalog'), array('class'=>'course-catalog-title'));
        
        require_once($CFG->libdir. '/coursecatlib.php');
        
        $courserenderer = $PAGE->get_renderer('core', 'course');
        $mycourseshtml = $this->catalog_print_my_courses();
        $categories = coursecat::make_categories_list();
        $user_view = get_user_preferences('catalog-view-type');
        
        if (empty($mycourseshtml)) {
            $html .= html_writer::tag('div', get_string('nothavecoursesarray', 'block_course_catalog'), array('class'=>'alert alert-info'));
            return $html;
        }
        $PAGE->requires->jquery();
        $PAGE->requires->js('/blocks/course_catalog/javascript/script.js', true);
        
        /*$html .= html_writer::tag('div', get_string('pickup', 'block_course_catalog'), [
            'class' => 'pickup-block pickup'
        ]);*/
        
        $html .= html_writer::start_tag('div', array('class'=>'nav-tabs-header'));
            $html .= html_writer::start_tag('ul', array('class'=>'nav-tabs-simple clearfix'));
        
            $html .= html_writer::start_tag('li', array('class'=>'form-filter'));
                $html .= html_writer::start_tag('form', array('id' => 'catalog_coursesearch', 'class' => 'coursesearch clearfix', 'action'=>$CFG->wwwroot.'/blocks/course_catalog/ajax.php'));
        
                // course actions block
                $html .= html_writer::start_tag('fieldset', array('class' => 'courseactionsbox clearfix', 'id'=>'course_actions'));
                
                $html .= html_writer::start_tag('span', array('class' => 'action-filter', 'onclick'=>'toggleFilter();'));
                $html .= html_writer::tag('i', '', array('class'=>'fa fa-align-left ', 'title'=>'Filter'));
                $html .= html_writer::end_tag('span');
        
                $html .= html_writer::start_tag('span', array('class' => 'action-sort', 'onclick'=>'toggleSort();'));
                $html .= html_writer::tag('i', '', array('class'=>'fa fa-sort-alpha-asc', 'title'=>'Sort'));
                $html .= html_writer::end_tag('span');
        
                $html .= html_writer::start_tag('span', array('class' => 'action-view', 'onclick'=>'toggleView();'));
                $html .= html_writer::tag('i', '', array('class'=>'fa fa-th-list'.(($user_view != 'list') ? ' active' : ''), 'title'=>get_string('listview', 'block_course_catalog')));
                $html .= html_writer::tag('i', '', array('class'=>'fa fa-th-large'.(($user_view == 'list') ? ' active' : ''), 'title'=>get_string('gridview', 'block_course_catalog')));
                $html .= html_writer::end_tag('span');
        
                $html .= html_writer::end_tag('fieldset');
                
                // course filter block
                $user_filter = array('category'=>get_user_preferences('catalog-filter-category'), 'status'=>get_user_preferences('catalog-filter-status'), 'courses_status'=>get_user_preferences('catalog-filter-courses_status'), 'catalog'=>get_user_preferences('catalog-filter-catalog'));
                $complete_options = array(1=>get_string('completed', 'block_course_catalog'), 2=>get_string('notstarted', 'block_course_catalog'), 3=>get_string('inprogress', 'block_course_catalog'));
                $html .= html_writer::start_tag('fieldset', array('class' => 'coursfilterbox invisiblefieldset clearfix', 'id'=>'course_filter'));
                
                $html .= html_writer::tag('input', '', array('id'=>'catalog_search', 'type'=>'text', 'value'=>'', 'placeholder'=>get_string('search')));
                $html .= html_writer::tag('span', get_string('category'));
                $html .= html_writer::select($categories, 'courses_cat', $user_filter['category'], get_string('allcategories', 'block_course_catalog'),array('onchange' => 'catalogFilter("category");', 'id'=>'catalog_sort_category'));
                $html .= html_writer::tag('span', get_string('coursestatus', 'block_course_catalog'));
                $html .= html_writer::select($complete_options, 'courses_status', $user_filter['courses_status'], get_string('showall', 'block_course_catalog'),array('onchange' => 'catalogFilter("courses_status");', 'id'=>'catalog_sort_courses_status'));
                $html .= html_writer::end_tag('fieldset');
        
                // course sort block
                $user_sort = array(
                    'sort-field'=> (get_user_preferences('catalog-sort-field')) ? get_user_preferences('catalog-sort-field') : 'fullname', 
                    'sort-nav'=> (get_user_preferences('catalog-sort-nav')) ? get_user_preferences('catalog-sort-nav') : 'ASC'
                );
        
                $html .= html_writer::start_tag('fieldset', array('class' => 'coursesortbox invisiblefieldset clearfix', 'id'=>'course_sort'));
                
                $html .= html_writer::start_tag('span', array('class' => 'fullname'.(($user_sort['sort-field'] == 'fullname') ? ' active' : ''), 'onclick'=>'catalogSortCourses("fullname");'));
                $html .= html_writer::tag('i', '', array('class'=>'fa fa-sort-asc'.(($user_sort['sort-field'] == 'fullname' and $user_sort['sort-nav'] == 'ASC') ? ' active' : '')));
                $html .= html_writer::tag('i', '', array('class'=>'fa fa-sort-desc'.(($user_sort['sort-field'] == 'fullname' and $user_sort['sort-nav'] == 'DESC') ? ' active' : '')));
                $html .= get_string('coursename', 'block_course_catalog');
                $html .= html_writer::end_tag('span');
        
                $html .= html_writer::start_tag('span', array('class' => 'startdate'.(($user_sort['sort-field'] == 'startdate') ? ' active' : ''), 'onclick'=>'catalogSortCourses("startdate");'));
                $html .= html_writer::tag('i', '', array('class'=>'fa fa-sort-asc'.(($user_sort['sort-field'] == 'startdate' and $user_sort['sort-nav'] == 'ASC') ? ' active' : '')));
                $html .= html_writer::tag('i', '', array('class'=>'fa fa-sort-desc'.(($user_sort['sort-field'] == 'startdate' and $user_sort['sort-nav'] == 'DESC') ? ' active' : '')));
                $html .= get_string('startdate', 'block_course_catalog');
                $html .= html_writer::end_tag('span');
                $html .= html_writer::empty_tag('input', array('type'=>'hidden', 'id'=>'course_sort', 'name'=>'sort', 'value'=>'fullname'));
                $html .= html_writer::empty_tag('input', array('type'=>'hidden', 'id'=>'course_sortnav', 'name'=>'sortnav', 'value'=>'asc'));
        
                $html .= html_writer::end_tag('fieldset');
                
                $html .= html_writer::end_tag('form');
            $html .= html_writer::end_tag('li');
        
            $html .= html_writer::tag('li', '<a data-toggle="tab" href="#searchcourses">'.get_string('searchresults').'</a>', array('class'=>'header-searchcourses hidden'));
        
            $html .= html_writer::end_tag('ul');
        $html .= html_writer::end_tag('div');
        
        $user_view = get_user_preferences('catalog-view-type');
        $html .= html_writer::start_tag('div', array('class'=>'tab-content'.(($user_view == 'list') ? ' list-view' : ' grid-view')));
        
            if (!empty($mycourseshtml)) {
                $html .= html_writer::start_tag('div', array('id'=>'frontpage-course-enroll-list', 'class'=>'frontpage-courses-list'));
                    $html .= html_writer::start_tag('div', array('class' => 'courses clearfix frontpage-course-list-enrolled'));
                        $html .= $mycourseshtml;
                    $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('div');
            }
        
        $html .= html_writer::end_tag('div');
        
        $html .= html_writer::end_tag('div');
        
				
        return $html;
    }
    
    public function catalog_print_course($course){
        global $CFG, $DB, $PAGE, $OUTPUT, $USER;
        $output = '';
        
        $completion_info = $this->get_course_completion($course);
        
        $output .= html_writer::start_tag('div', array(
                    'class' => 'coursebox clearfix '.$completion_info->status,
                    'data-courseid' => $course->id,
                    'onclick' =>'location=\''.new moodle_url('/course/view.php', array('id' => $course->id)).'\''
                ));

            // course name
            $output .= html_writer::start_tag('div', array('class' => 'coursename'));
            $output .= html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $course->fullname);
            $output .= html_writer::end_tag('div');
        
            // course description
            $output .= html_writer::start_tag('div', array('class' => 'descrition'));
            $output .= $course->summary;
            $output .= html_writer::end_tag('div');
        
            // course progress
            $output .= html_writer::start_tag('div', array('class' => 'course-progress'));
            $output .= html_writer::tag('label', get_string('completed', 'block_course_catalog').': ').$completion_info->completion.'%';
            $output .= html_writer::end_tag('div');
            
        $output .= html_writer::end_tag('div');
        
        return $output;
    }

    public function get_course_completion($course) {
        global $CFG, $PAGE, $DB, $USER, $OUTPUT;				
        
        require_once("{$CFG->libdir}/completionlib.php");
        
        $result = new stdClass();
        $result->completion = 0;
        $result->status = 'notyetstarted';

        // Get course completion data.
        $info = new completion_info($course);

        // Load criteria to display.
        $completions = $info->get_completions($USER->id);

        if ($info->is_tracked_user($USER->id) and $course->startdate <= time()) {

            // For aggregating activity completion.
            $activities = array();
            $activities_complete = 0;

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
                $result->completion = 100;
            } else if ($criteriacomplete || $ccompletion->timestarted) {
                $status = 'inprogress';
            } else {
                $status = 'notyetstarted';
            }

            $result->status = $status;
        }
        
        return $result;
    }
    
    
    public function catalog_print_my_courses($search = '', $page = 0){
        global $CFG, $DB, $PAGE, $OUTPUT, $USER;
        $output = '';
        
        if (!isloggedin() or isguestuser()) {
            return $output;
        }
        
        $my_courses = $this->catalog_get_my_courses($search, $this->coursesperpage, $page);
        if (count($my_courses['courses'])){
            foreach($my_courses['courses'] as $course){
                $output .= $this->catalog_print_course($course);
            }
            if ($my_courses['total'] > $this->coursesperpage * ($page+1)){
                $output .= html_writer::start_tag('div', array('class'=>'load-more-box clearfix'));
                    $output .= html_writer::tag('button', get_string('loadmore', 'block_course_catalog'), array('class'=>'btn', 'onclick'=>'catalogLoadMore('.($page+1).', "enrolled");'));
                $output .= html_writer::end_tag('div');
            }
        } else {
            $output .= html_writer::tag('div', get_string('nocourses', 'block_course_catalog'), array('class'=>'alert alert-block alert-success'));
        }
        
        return $output;
    }
    
    function catalog_get_my_courses($search = '', $limit = 20, $page = 0) {
        global $DB, $USER, $CFG;
        
        $filter = array(
                            'category'=>get_user_preferences('catalog-filter-category'), 
                            'course_status'=>get_user_preferences('catalog-filter-courses_status')
                        );
        
        $sorting = array(
                    'field'=> (get_user_preferences('catalog-sort-field')) ? get_user_preferences('catalog-sort-field') : 'fullname', 
                    'sort_nav'=> (get_user_preferences('catalog-sort-nav')) ? get_user_preferences('catalog-sort-nav') : 'ASC'
                );
        
        // Guest account does not have any courses
        if (isguestuser() or !isloggedin()) {
            return(array());
        }
        
        $basefields = array('id', 'category', 'sortorder',
                            'shortname', 'fullname', 'idnumber',
                            'startdate', 'visible',
                            'groupmode', 'groupmodeforce', 'cacherev', 'summary', 'format');
        $coursefields = 'c.' .join(',c.', $basefields);
        
        // courses sorting
        $sjoin = ""; $orderby = "";
        if (count($sorting)) {
            $sort        = (isset($sorting['field'])) ? trim($sorting['field']) : 'fullname';
            $sort_nav    = (isset($sorting['sort_nav'])) ? trim($sorting['sort_nav']) : 'ASC';
            
            if (in_array($sort, $basefields)){
                $orderby = "ORDER BY c.$sort $sort_nav";
            }
        } else {
            $orderby = "ORDER BY c.fullname";
        }
        
        $fjoin = ""; $fwhere = "";
        
        // courses filter
        if (count($filter)) {
            
            if ($filter['category'] != ''){
                $fwhere .= " AND (c.category = '".$filter['category']."' OR cc.path = '/".$filter['category']."' OR cc.path LIKE '%/".$filter['category']."/%')";   
            }
            $now = time();
            if ($filter['course_status'] == 1){
                $fjoin = " LEFT JOIN {course_completions} ccom ON ccom.course = c.id AND ccom.userid = ".$USER->id;
                $fwhere .= " AND ccom.timecompleted IS NOT NULL ";
            }elseif($filter['course_status'] == 2){ //not started
                $fwhere .= " AND c.startdate > $now";
            }elseif($filter['course_status'] == 3){ // in progress
                $fwhere .= " AND c.startdate < $now ";
            }
        }
        
        // course search
        $swhere = '';
        if (!empty($search)){
            $search_words = explode(' ', $search);
            $search_pieces = array();
            if (count($search_words)){
                foreach ($search_words as $sword){
                    $search_pieces[] = "cc.name LIKE '%$sword%'";
                    $search_pieces[] = "cc.description LIKE '%$sword%'";
                    $search_pieces[] = "cfo.value LIKE '%$sword%'";
                    $search_pieces[] = "c.shortname LIKE '%$sword%'";
                    $search_pieces[] = "c.fullname LIKE '%$sword%'";
                    $search_pieces[] = "c.summary LIKE '%$sword%'";
                }
            }
            if (count($search_pieces)){
               $swhere = " AND (".implode(" OR ", $search_pieces).")";
            }
        }
        
        $wheres = array("c.id <> :siteid");
        $params = array('siteid'=>SITEID);
        $wheres[] = "c.visible > 0";
        $wheres[] = "cc.visible > 0";

        if (isset($USER->loginascontext) and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
            // list _only_ this course - anything else is asking for trouble...
            $wheres[] = "courseid = :loginas";
            $params['loginas'] = $USER->loginascontext->instanceid;
        }

        $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
        $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
        $params['contextlevel'] = CONTEXT_COURSE;
        $wheres = implode(" AND ", $wheres);

        //note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why we have the subselect there
        $sql = "SELECT $coursefields $ccselect, cc.name as categoryname
                  FROM {course} c
                  JOIN (SELECT DISTINCT e.courseid, e.enrol
                          FROM {enrol} e
                          JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                         WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                       ) en ON (en.courseid = c.id)
                  LEFT JOIN {course_categories} cc ON cc.id = c.category
               $ccjoin               
               $sjoin
               $fjoin
                 LEFT JOIN {course_format_options} cfo ON cfo.courseid = c.id
                 WHERE $wheres $fwhere $swhere
               GROUP BY c.id $orderby";
        $params['userid']  = $USER->id;
        $params['active']  = ENROL_USER_ACTIVE;
        $params['enabled'] = ENROL_INSTANCE_ENABLED;
        $params['now1']    = round(time(), -2); // improves db caching
        $params['now2']    = $params['now1'];
       
        $start = $limit * $page;        
        $courses = $DB->get_records_sql($sql, $params, $start, $limit);

        $sql = "SELECT c.id
                  FROM {course} c
                  JOIN (SELECT DISTINCT e.courseid, e.enrol
                          FROM {enrol} e
                          JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                         WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                       ) en ON (en.courseid = c.id)
                  LEFT JOIN {course_categories} cc ON cc.id = c.category
               $ccjoin               
               $sjoin
               $fjoin
                 LEFT JOIN {course_format_options} cfo ON cfo.courseid = c.id
                 WHERE $wheres $fwhere $swhere
               GROUP BY c.id $orderby";
        $courses_count = $DB->get_records_sql($sql, $params);
        $total = count($courses_count);
        
        // preload contexts and check visibility
        foreach ($courses as $id=>$course) {
            context_helper::preload_from_record($course);
            $context = context_course::instance($course->id, IGNORE_MISSING);
            if (!$course->visible) {
                if (!$context) {
                    unset($courses[$id]);
                    continue;
                }
                if (!has_capability('moodle/course:viewhiddencourses', $context)) {
                    unset($courses[$id]);
                    continue;
                }
            }
            
            $all_course = course_get_format($course)->get_course();
            $all_course->context = $context;
            $all_course->coursetype = 'enrolled';
            $all_course->categoryname = $course->categoryname;
            $courses[$id] = $all_course;
        }
       
        return array('total'=>$total, 'courses'=>$courses);
    }
    
}
