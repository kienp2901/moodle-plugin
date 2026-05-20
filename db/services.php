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
 * @package mod_quizexercise
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'mod_quizexercise_create_quizexercise' => array(
        'classname'     => 'mod_quizexercise_external',
        'methodname'    => 'create_quizexercise',
        'classpath'     => 'mod/quizexercise/externallib.php',
        'description'   => 'Create a new test exam instance',
        'type'          => 'write',
        'capabilities'  => 'mod/quizexercise:addinstance',
        'ajax'          => true,
    ),

    'mod_quizexercise_update_quizexercise' => array(
        'classname'     => 'mod_quizexercise_external',
        'methodname'    => 'update_quizexercise',
        'classpath'     => 'mod/quizexercise/externallib.php',
        'description'   => 'Update an existing test exam instance',
        'type'          => 'write',
        'capabilities'  => 'mod/quizexercise:addinstance',
        'ajax'          => true,
    ),

    'mod_quizexercise_get_quizexercise' => array(
        'classname'     => 'mod_quizexercise_external',
        'methodname'    => 'get_quizexercise',
        'classpath'     => 'mod/quizexercise/externallib.php',
        'description'   => 'Get test exam instance details',
        'type'          => 'read',
        'capabilities'  => 'mod/quizexercise:view',
        'ajax'          => true,
    ),

    'mod_quizexercise_delete_quizexercise' => array(
        'classname'     => 'mod_quizexercise_external',
        'methodname'    => 'delete_quizexercise',
        'classpath'     => 'mod/quizexercise/externallib.php',
        'description'   => 'Delete a test exam instance',
        'type'          => 'write',
        'capabilities'  => 'mod/quizexercise:addinstance',
        'ajax'          => true,
    ),

);
