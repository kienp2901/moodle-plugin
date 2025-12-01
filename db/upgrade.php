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
 * Reading Flow module upgrade script
 *
 * @package    mod_readingflow
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_readingflow_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024121902) {
        // Add lumos_reading_id and lumos_reading_slug fields to readingflow table
        $table = new xmldb_table('readingflow');
        
        // Add lumos_reading_id field
        $field = new xmldb_field('lumos_reading_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'contentformat');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Add lumos_reading_slug field
        $field = new xmldb_field('lumos_reading_slug', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'lumos_reading_id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2024121902, 'readingflow');
    }

    if ($oldversion < 2024121903) {
        // Version bump to ensure all installations get the new fields
        upgrade_mod_savepoint(true, 2024121903, 'readingflow');
    }

    return true;
}

