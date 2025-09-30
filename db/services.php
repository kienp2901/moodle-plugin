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
 * @package mod_quickquiz
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'mod_quickquiz_create_quickquiz' => array(
        'classname'     => 'mod_quickquiz_external',
        'methodname'    => 'create_quickquiz',
        'classpath'     => 'mod/quickquiz/externallib.php',
        'description'   => 'Create a new test exam instance',
        'type'          => 'write',
        'capabilities'  => 'mod/quickquiz:addinstance',
        'ajax'          => true,
    ),

    'mod_quickquiz_update_quickquiz' => array(
        'classname'     => 'mod_quickquiz_external',
        'methodname'    => 'update_quickquiz',
        'classpath'     => 'mod/quickquiz/externallib.php',
        'description'   => 'Update an existing test exam instance',
        'type'          => 'write',
        'capabilities'  => 'mod/quickquiz:addinstance',
        'ajax'          => true,
    ),

    'mod_quickquiz_get_quickquiz' => array(
        'classname'     => 'mod_quickquiz_external',
        'methodname'    => 'get_quickquiz',
        'classpath'     => 'mod/quickquiz/externallib.php',
        'description'   => 'Get test exam instance details',
        'type'          => 'read',
        'capabilities'  => 'mod/quickquiz:view',
        'ajax'          => true,
    ),

    'mod_quickquiz_delete_quickquiz' => array(
        'classname'     => 'mod_quickquiz_external',
        'methodname'    => 'delete_quickquiz',
        'classpath'     => 'mod/quickquiz/externallib.php',
        'description'   => 'Delete a test exam instance',
        'type'          => 'write',
        'capabilities'  => 'mod/quickquiz:addinstance',
        'ajax'          => true,
    ),

);
