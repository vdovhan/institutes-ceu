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

// Get the HTML for the settings bits.
$html = theme_institutes_ceu_get_html_for_settings($OUTPUT, $PAGE);

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$hassideinner = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-inner', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));

$showsidepre =($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));
$showsideinner = ($hassideinner && !$PAGE->blocks->region_completely_docked('side-inner', $OUTPUT));

$showsidepost = false;
$showsidebar = (get_user_preferences('fix-sidebar', 1));


$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

// Set default (LTR) layout mark-up for a three column page.
$regionmainbox = 'span9';
$regionmain = 'span12 pull-right';
$sidepre = 'desktop-first-column';
$sidepost = 'span3';
$active_menu = '';

if ($showsidepost){
    $active_menu = theme_institutes_ceu_get_menu_active_link();
}

if (!$showsidepost or $active_menu != 'activecourse'){
    $regionmainbox = 'span12';
}

if ($showsideinner){
    $regionmain .= ' with-inner';
}

// Reset layout mark-up for RTL languages.
if (right_to_left()) {
    $regionmainbox .= ' pull-right';
    $regionmain = str_replace('pull-right', '', $regionmain);
    $sidepre .= ' pull-right';
    $sidepost .= ' desktop-first-column';
}


echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <link rel="apple-touch-icon" sizes="48x48" href="<?php echo $CFG->wwwroot; ?>/theme/institutes_ceu/pix/favicon_48x48.png">
    <link rel="icon" type="image/png" href="<?php echo $CFG->wwwroot; ?>/theme/institutes_ceu/pix/favicon_32x32.png" sizes="32x32">
    <?php echo $OUTPUT->standard_head_html() ?>
    <link href='//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
    <link href="//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php require_once('includes/header.php'); ?>
    
<div id="page" class="container-fluid <?php echo ($showsidebar) ? '' : 'full-width' ?>">
    <?php require_once('includes/sidebar.php'); ?>
    <div id="page-content" class="row-fluid">
        <?php if ($showsidepost) : ?>
            <div class="<?php echo $sidepost; ?> side-post <?php echo ($active_menu == 'activecourse') ? 'open' : ''?>">
                <?php echo $OUTPUT->blocks('side-post'); ?>
            </div>
        <?php endif; ?>
        <div id="region-main-box" class="<?php echo $regionmainbox; ?>">
            <div class="row-fluid clearfix">
                <section id="region-main" class="<?php echo $regionmain; ?>">
                    <?php
                    echo $OUTPUT->course_content_header();
                    echo $OUTPUT->main_content();
                    echo $OUTPUT->course_content_footer();
                    ?>
                </section>
                <?php if ($showsideinner) : ?>
                    <div class="side-inner">
                        <?php echo $OUTPUT->blocks('side-inner'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
    
<?php require_once('includes/footer.php'); ?>
    
<?php echo $OUTPUT->standard_end_of_body_html() ?>
    
</body>
</html>
