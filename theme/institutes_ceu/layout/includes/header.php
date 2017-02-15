<header role="banner" class="navbar navbar-fixed-top<?php echo $html->navbarclass ?> moodle-has-zindex clearfix">
    <?php if (isloggedin()) : ?>
        <div class="header-notifications-menu">
            <ul class="clearfix<?php echo (empty($actions_buttons) ? ' two-items' : ''); ?>">
                <?php if (get_config('local_notifications', 'enabled')) : ?>
                    <?php
                        global $OUTPUT, $CFG, $USER;
                        $alerts = ($USER->id) ? theme_institutes_ceu_get_user_alerts() : array();
                    ?>
                    <li>
                        <a href="<?php echo $CFG->wwwroot; ?>" title="<?php echo get_string('alerts', 'theme_institutes_ceu'); ?>">
                            <span class="menu-icon icon-alert"></span>
                            <span class="menu-text"><?php echo get_string('alerts', 'theme_institutes_ceu'); ?></span>

                            <?php if($alerts['count']): ?>
                                <i class="alert notification"><?= $alerts['count'] ?></i>
                            <?php endif; ?>
                        </a>
                        <?php echo $alerts['list'] ?>
                    </li>
                    <li>
                        <a href="<?php echo $CFG->wwwroot; ?>/message/index.php">
                            <span class="menu-icon icon-messages" title="<?php echo get_string('messages', 'theme_institutes_ceu'); ?>"></span>
                            <span class="menu-text"><?php echo get_string('messages', 'theme_institutes_ceu'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php $actions_buttons = $OUTPUT->page_heading_button(); ?>
                <li>
                    <a href="<?php echo new moodle_url('/login/logout.php', array('sesskey'=>sesskey())); ?>">
                        <span class="menu-icon" title="<?php echo get_string('logout', 'theme_institutes_ceu'); ?>"><img src="<?php echo $OUTPUT->pix_url('logout_small', 'theme'); ?>" alt="<?php echo get_string('logout', 'theme_institutes_ceu'); ?>" /></span>
                        <span class="menu-text"><?php echo get_string('logout', 'theme_institutes_ceu'); ?></span>
                    </a>
                </li>
                <?php if (!empty($actions_buttons)) : ?>
                    <li class="moodle-editing-btn">
                        <a href="javascript:void(0);" title="<?php echo get_string('actions', 'theme_institutes_ceu'); ?>">
                        <span class="menu-icon" title="<?php echo get_string('actions', 'theme_institutes_ceu'); ?>"><img src="<?php echo $OUTPUT->pix_url('settings_small', 'theme'); ?>" alt="<?php echo get_string('actions', 'theme_institutes_ceu'); ?>" /></span>
                        <span class="menu-text"><?php echo get_string('actions', 'theme_institutes_ceu'); ?></span></a>
                        <div class="actions-menu">    
                            <?php echo $OUTPUT->page_heading_button(); ?>
                        </div>
                    </li>
                <?php endif; ?>
                <li class="bookmarks-menu">
                    <a href="javascript:void(0);" onclick="bookmarksOpen();">
                        <span class="menu-icon" title="<?php echo get_string('bookmarks', 'theme_institutes_ceu'); ?>"><img src="<?php echo $OUTPUT->pix_url('bookmarks_small', 'theme'); ?>" alt="<?php echo get_string('bookmarks', 'theme_institutes_ceu'); ?>" /></span>
                        <span class="menu-text"><?php echo get_string('bookmarks', 'theme_institutes_ceu'); ?></span>
                    </a>
                    <?php echo theme_institutes_ceu_print_bookmarks(); ?>
                </li>
            </ul>
        </div>
    <?php endif; ?>
    <div class="header-logo">

        <div class="mobile-nav"></div>
        <?php if ($PAGE->course->id > 1) : ?>
            <?php echo theme_institutes_ceu_get_course_header(); ?>
        <?php else: ?>
            <a href="<?php echo $CFG->wwwroot;?>"><div class="logo"></div></a>
            <span class="header_course_name"> <?php echo $PAGE->course->fullname ?> </span>
        <?php endif; ?>
    </div>
    <?php if (isloggedin()) : ?>
        <div class="header-navbar">
            <div class="container-fluid header-search-panel clearfix">
                <div class="nav-collapse collapse">
                    <?php echo $OUTPUT->custom_menu(); ?>
                    <ul class="nav pull-right">
                        <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
                    </ul>
                </div>
            </div>
            <div class="container-fluid header-path-panel clearfix">
                <?php echo html_writer::tag('nav', $OUTPUT->navbar(), array('class' => 'breadcrumb-nav'.(($PAGE->course->id > 1) ? '' : ' hidden'))); ?>
                <?php echo html_writer::tag('div', $this->course_header(), array('id' => 'course-header')); ?>
            </div>
        </div>
    <?php endif; ?>
</header>
