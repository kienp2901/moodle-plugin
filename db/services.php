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
 * Web service definitions for Test Exam module
 *
 * @package mod_testexam
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'mod_testexam_create_testexam' => array(
        'classname'     => 'mod_testexam_external',
        'methodname'    => 'create_testexam',
        'classpath'     => 'mod/testexam/externallib.php',
        'description'   => 'Create a new test exam instance',
        'type'          => 'write',
        'capabilities'  => 'mod/testexam:addinstance',
        'ajax'          => true,
    ),

    'mod_testexam_update_testexam' => array(
        'classname'     => 'mod_testexam_external',
        'methodname'    => 'update_testexam',
        'classpath'     => 'mod/testexam/externallib.php',
        'description'   => 'Update an existing test exam instance',
        'type'          => 'write',
        'capabilities'  => 'mod/testexam:addinstance',
        'ajax'          => true,
    ),

    'mod_testexam_get_testexam' => array(
        'classname'     => 'mod_testexam_external',
        'methodname'    => 'get_testexam',
        'classpath'     => 'mod/testexam/externallib.php',
        'description'   => 'Get test exam instance details',
        'type'          => 'read',
        'capabilities'  => 'mod/testexam:view',
        'ajax'          => true,
    ),

    'mod_testexam_delete_testexam' => array(
        'classname'     => 'mod_testexam_external',
        'methodname'    => 'delete_testexam',
        'classpath'     => 'mod/testexam/externallib.php',
        'description'   => 'Delete a test exam instance',
        'type'          => 'write',
        'capabilities'  => 'mod/testexam:addinstance',
        'ajax'          => true,
    ),

);
