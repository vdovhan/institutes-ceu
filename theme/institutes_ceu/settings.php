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
 * Moodle's institutes theme, an example of how to make a Bootstrap theme
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

require_once("$CFG->dirroot/theme/institutes_ceu/lib.php");

defined('MOODLE_INTERNAL') || die;



if ($ADMIN->fulltree) {

    // Logo file setting.
    $name = 'theme_institutes_ceu/logo';
    $title = get_string('logo','theme_institutes_ceu');
    $description = get_string('logodesc', 'theme_institutes_ceu');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Small logo file setting.
    $name = 'theme_institutes_ceu/smalllogo';
    $title = get_string('smalllogo', 'theme_institutes_ceu');
    $description = get_string('smalllogodesc', 'theme_institutes_ceu');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'smalllogo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Show site name along with small logo.
    $name = 'theme_institutes_ceu/sitename';
    $title = get_string('sitename', 'theme_institutes_ceu');
    $description = get_string('sitenamedesc', 'theme_institutes_ceu');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 1);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Custom CSS file.
    $name = 'theme_institutes_ceu/customcss';
    $title = get_string('customcss', 'theme_institutes_ceu');
    $description = get_string('customcssdesc', 'theme_institutes_ceu');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Footnote setting.
    $name = 'theme_institutes_ceu/footnote';
    $title = get_string('footnote', 'theme_institutes_ceu');
    $description = get_string('footnotedesc', 'theme_institutes_ceu');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Glossary
    $name = 'theme_institutes_ceu/glossary_course_id';
    $title = get_string('glossary_course_title', 'theme_institutes_ceu');
    $description = get_string('glossary_course_description', 'theme_institutes_ceu');
    $default = null;
    $setting = new admin_setting_configselect($name, $title, $description, $default, theme_institutes_ceu_get_courses_list());
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
    
    // FAQ
    $name = 'theme_institutes_ceu/faq_course_id';
    $title = get_string('faq_course_title', 'theme_institutes_ceu');
    $description = get_string('faq_course_description', 'theme_institutes_ceu');
    $default = null;
    $setting = new admin_setting_configselect($name, $title, $description, $default, theme_institutes_ceu_get_courses_list());
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
