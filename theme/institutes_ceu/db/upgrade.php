<?php
// IntelliBoard.net
//
// IntelliBoard.net is built to work with any LMS designed in Moodle
// with the goal to deliver educational data analytics to single dashboard instantly.
// With power to turn this analytical data into simple and easy to read reports,
// IntelliBoard.net will become your primary reporting tool.
//
// Moodle
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// IntelliBoard.net is built as a local plugin for Moodle.

/**
 * IntelliBoard.net
 *
 *
 * @package    theme_institutes_ceu
 * @copyright  2013 Moodle, moodle.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_theme_institutes_ceu_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

	// Define table theme_institutes_bookmarks to be created.
	$table = new xmldb_table('theme_institutes_bookmarks');

	// Adding fields to table theme_institutes_bookmarks.
	$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
	$table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('url', XMLDB_TYPE_CHAR, '255', null, null, null, null);
	$table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
	$table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
	
	// Adding keys to table theme_institutes_bookmarks.
	$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

	// Conditionally launch create table for theme_institutes_bookmarks.
	if (!$dbman->table_exists($table)) {
		$dbman->create_table($table);
	}

    return true;
}
