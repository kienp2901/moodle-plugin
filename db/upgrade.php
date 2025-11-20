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
 * Step by Step module upgrade script
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_stepbystep_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024083000) {
        // Initial version - no upgrade needed
        upgrade_mod_savepoint(true, 2024083000, 'stepbystep');
    }

    if ($oldversion < 2024121916) {
        // Add storage_path field to stepbystep_content table
        $table = new xmldb_table('stepbystep_content');
        $field = new xmldb_field('storage_path', XMLDB_TYPE_CHAR, '500', null, null, null, null, 'response_text');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2024121916, 'stepbystep');
    }

    if ($oldversion < 2024121983) {
        // Add phonetic field to stepbystep_content table for vocabulary pronunciation
        $table = new xmldb_table('stepbystep_content');
        $field = new xmldb_field('phonetic', XMLDB_TYPE_TEXT, null, null, null, null, null, 'term');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2024121983, 'stepbystep');
    }

    // if ($oldversion < 2024083030) {
    //     // Remove title field from stepbystep_content table
    //     $table = new xmldb_table('stepbystep_content');
    //     $field = new xmldb_field('title');
        
    //     if ($dbman->field_exists($table, $field)) {
    //         $dbman->drop_field($table, $field);
    //     }
        
    //     upgrade_mod_savepoint(true, 2024083030, 'stepbystep');
    // }

    // if ($oldversion < 2024083030) {
    //     // Add new fields for enhanced text type steps
    //     $table = new xmldb_table('stepbystep_content');
        
    //     // Add main_title field for text type steps
    //     $field = new xmldb_field('main_title', XMLDB_TYPE_TEXT, null, null, null, null, null, 'content');
    //     if (!$dbman->field_exists($table, $field)) {
    //         $dbman->add_field($table, $field);
    //     }
        
    //     // Add sub_heading field for text type steps
    //     $field = new xmldb_field('sub_heading', XMLDB_TYPE_TEXT, null, null, null, null, null, 'main_title');
    //     if (!$dbman->field_exists($table, $field)) {
    //         $dbman->add_field($table, $field);
    //     }
        
    //     // Add content_paragraphs field for text type steps (JSON format for multiple paragraphs)
    //     $field = new xmldb_field('content_paragraphs', XMLDB_TYPE_TEXT, null, null, null, null, null, 'sub_heading');
    //     if (!$dbman->field_exists($table, $field)) {
    //         $dbman->add_field($table, $field);
    //     }
        
    //     upgrade_mod_savepoint(true, 2024083030, 'stepbystep');
    // }

    return true;
}
