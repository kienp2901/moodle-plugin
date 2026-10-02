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
 * Web service definitions for Quiz Final Test module
 *
 * @package mod_quizfinaltest
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'mod_quizfinaltest_create_quizfinaltest' => array(
        'classname'     => 'mod_quizfinaltest_external',
        'methodname'    => 'create_quizfinaltest',
        'classpath'     => 'mod/quizfinaltest/externallib.php',
        'description'   => 'Create a new quiz final test instance',
        'type'          => 'write',
        'capabilities'  => 'mod/quizfinaltest:addinstance',
        'ajax'          => true,
    ),

    'mod_quizfinaltest_update_quizfinaltest' => array(
        'classname'     => 'mod_quizfinaltest_external',
        'methodname'    => 'update_quizfinaltest',
        'classpath'     => 'mod/quizfinaltest/externallib.php',
        'description'   => 'Update an existing quiz final test instance',
        'type'          => 'write',
        'capabilities'  => 'mod/quizfinaltest:addinstance',
        'ajax'          => true,
    ),

    'mod_quizfinaltest_get_quizfinaltest' => array(
        'classname'     => 'mod_quizfinaltest_external',
        'methodname'    => 'get_quizfinaltest',
        'classpath'     => 'mod/quizfinaltest/externallib.php',
        'description'   => 'Get quiz final test instance details',
        'type'          => 'read',
        'capabilities'  => 'mod/quizfinaltest:view',
        'ajax'          => true,
    ),

    'mod_quizfinaltest_delete_quizfinaltest' => array(
        'classname'     => 'mod_quizfinaltest_external',
        'methodname'    => 'delete_quizfinaltest',
        'classpath'     => 'mod/quizfinaltest/externallib.php',
        'description'   => 'Delete a quiz final test instance',
        'type'          => 'write',
        'capabilities'  => 'mod/quizfinaltest:addinstance',
        'ajax'          => true,
    ),

);
