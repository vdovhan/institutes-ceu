<?php
/**
 * course catalog
 *
 * @package    block_course_catalog
 * @copyright  2015 SEBALE (http://sebale.net)
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'block/course_catalog:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/site:config'
    ),

    'block/course_catalog:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' => 'moodle/site:config'
    )
);
