<?php
// institutes.net
//
// institutes.net is built to work with any LMS designed in Moodle
// with the goal to deliver educational data analytics to single dashboard instantly.
// With power to turn this analytical data into simple and easy to read reports,
// institutes.net will become your primary reporting tool.
//
// Moodle
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//

function xmldb_format_institutes_ceu_install($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

	// Define table course_format_sections to be created.
	$table = new xmldb_table('course_format_sections');

	// Adding fields to table course_format_sections.
	$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
	$table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('format', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('section', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('parent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('level', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('parentssequence', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('imageid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
	$table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
	$table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
	$table->add_field('sectiontype', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    
	// Adding keys to table course_format_sections.
	$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

	// Conditionally launch create table for course_format_sections.
	if (!$dbman->table_exists($table)) {
		$dbman->create_table($table);
	}
    
    
    // Define table course_format_settings to be created.
	$table = new xmldb_table('course_format_settings');

	// Adding fields to table course_format_settings.
	$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
	$table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('type', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('value', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    
	// Adding keys to table course_format_settings.
	$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

	// Conditionally launch create table for course_format_settings.
	if (!$dbman->table_exists($table)) {
		$dbman->create_table($table);
	}
    
    
    // Define table course_format_resources to be created.
	$table = new xmldb_table('course_format_resources');

	// Adding fields to table course_format_resources.
	$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
	$table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
	$table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
	$table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
	$table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('type', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('state', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
    $table->add_field('coursestate', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
    
	// Adding keys to table course_format_resources.
	$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

	// Conditionally launch create table for course_format_resources.
	if (!$dbman->table_exists($table)) {
		$dbman->create_table($table);
	}
    
    
    // Course format resource //
    $table = new xmldb_table('course_format_resource');
    
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('resourcetext', XMLDB_TYPE_TEXT, '255', null, null, null, null);
    $table->add_field('filename', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('popuptext', XMLDB_TYPE_TEXT, '255', null, null, null, null);
    $table->add_field('states', XMLDB_TYPE_TEXT, '255', null, null, null, null);
    $table->add_field('resourcefile', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
    $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

    // Adding keys to table course_format_resource.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    
    if ($dbman->table_exists($table)) {
        !$dbman->create_table($table);    
    }
    
    
    // Course instructions //
    $table = new xmldb_table('course_format_instructions');

    // Adding fields to table course_format_instructions.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('message', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('attention', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('state', XMLDB_TYPE_CHAR, '20', null, null, null, null);
    $table->add_field('instructionfile', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
    $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

    // Adding keys to table course_format_instructions.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    // Conditionally launch create table for course_format_instructions.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    
    
    // Course notes //
    // Define table course_format_notes to be created.
    $table = new xmldb_table('course_format_notes');
    
    // Adding fields to table course_format_notes.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('notetext', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('color', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('timestart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('timeend', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
    $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

    // Adding keys to table course_format_notes.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    // Conditionally launch create table for course_format_notes.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    
    
    // Course format ussates //
    // Define table course_format_usstates to be created.
    $table = new xmldb_table('course_format_usstates');
    if ($dbman->table_exists($table)) {
        $dbman->drop_table($table, true, true);
    }

    // Adding fields to table course_format_usstates.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('abbr', XMLDB_TYPE_CHAR, '5', null, null, null, null);
    $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    
    // Adding keys to table course_format_usstates.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    // Conditionally launch create table for course_format_usstates.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
    
    $usstates = array(
        array('id' => '2', 'abbr' => 'AL', 'name' => 'Alabama'),
        array('id' => '3', 'abbr' => 'AK', 'name' => 'Alaska'),
        array('id' => '4', 'abbr' => 'AZ', 'name' => 'Arizona'),
        array('id' => '5', 'abbr' => 'AR', 'name' => 'Arkansas'),
        array('id' => '6', 'abbr' => 'CA', 'name' => 'California'),
        array('id' => '7', 'abbr' => 'CO', 'name' => 'Colorado'),
        array('id' => '8', 'abbr' => 'CT', 'name' => 'Connecticut'),
        array('id' => '9', 'abbr' => 'DE', 'name' => 'Delaware'),
        array('id' => '10', 'abbr' => 'DC', 'name' => 'District of Columbia'),
        array('id' => '11', 'abbr' => 'FL', 'name' => 'Florida'),
        array('id' => '12', 'abbr' => 'GA', 'name' => 'Georgia'),
        array('id' => '13', 'abbr' => 'HI', 'name' => 'Hawaii'),
        array('id' => '14', 'abbr' => 'ID', 'name' => 'Idaho'),
        array('id' => '15', 'abbr' => 'IL', 'name' => 'Illinois'),
        array('id' => '16', 'abbr' => 'IN', 'name' => 'Indiana'),
        array('id' => '17', 'abbr' => 'IA', 'name' => 'Iowa'),
        array('id' => '18', 'abbr' => 'KS', 'name' => 'Kansas'),
        array('id' => '19', 'abbr' => 'KY', 'name' => 'Kentucky'),
        array('id' => '20', 'abbr' => 'LA', 'name' => 'Louisiana'),
        array('id' => '21', 'abbr' => 'ME', 'name' => 'Maine'),
        array('id' => '22', 'abbr' => 'MD', 'name' => 'Maryland'),
        array('id' => '23', 'abbr' => 'MA', 'name' => 'Massachusetts'),
        array('id' => '24', 'abbr' => 'MI', 'name' => 'Michigan'),
        array('id' => '25', 'abbr' => 'MN', 'name' => 'Minnesota'),
        array('id' => '26', 'abbr' => 'MS', 'name' => 'Mississippi'),
        array('id' => '27', 'abbr' => 'MO', 'name' => 'Missouri'),
        array('id' => '28', 'abbr' => 'MT', 'name' => 'Montana'),
        array('id' => '29', 'abbr' => 'NE', 'name' => 'Nebraska'),
        array('id' => '30', 'abbr' => 'NV', 'name' => 'Nevada'),
        array('id' => '31', 'abbr' => 'NH', 'name' => 'New Hampshire'),
        array('id' => '32', 'abbr' => 'NJ', 'name' => 'New Jersey'),
        array('id' => '33', 'abbr' => 'NM', 'name' => 'New Mexico'),
        array('id' => '34', 'abbr' => 'NY', 'name' => 'New York'),
        array('id' => '35', 'abbr' => 'NC', 'name' => 'North Carolina'),
        array('id' => '36', 'abbr' => 'ND', 'name' => 'North Dakota'),
        array('id' => '37', 'abbr' => 'OH', 'name' => 'Ohio'),
        array('id' => '38', 'abbr' => 'OK', 'name' => 'Oklahoma'),
        array('id' => '39', 'abbr' => 'OR', 'name' => 'Oregon'),
        array('id' => '40', 'abbr' => 'PA', 'name' => 'Pennsylvania'),
        array('id' => '41', 'abbr' => 'RI', 'name' => 'Rhode Island'),
        array('id' => '42', 'abbr' => 'SC', 'name' => 'South Carolina'),
        array('id' => '43', 'abbr' => 'SD', 'name' => 'South Dakota'),
        array('id' => '44', 'abbr' => 'TN', 'name' => 'Tennessee'),
        array('id' => '45', 'abbr' => 'TX', 'name' => 'Texas'),
        array('id' => '46', 'abbr' => 'UT', 'name' => 'Utah'),
        array('id' => '47', 'abbr' => 'VT', 'name' => 'Vermont'),
        array('id' => '48', 'abbr' => 'VA', 'name' => 'Virginia'),
        array('id' => '49', 'abbr' => 'WA', 'name' => 'Washington'),
        array('id' => '50', 'abbr' => 'WV', 'name' => 'West Virginia'),
        array('id' => '51', 'abbr' => 'WI', 'name' => 'Wisconsin'),
        array('id' => '52', 'abbr' => 'WY', 'name' => 'Wyoming')
    );

    foreach ($usstates as $state){
        $ins_state = (object)$state;
        if (!$DB->get_record('course_format_usstates', array('name'=>$ins_state->abbr))){
            $DB->insert_record('course_format_usstates', $ins_state, false);
        }
    }
    
    return true;
}
