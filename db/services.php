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
 * Web service definitions for Reading Flow module
 *
 * @package mod_readingflow
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'mod_readingflow_create_readingflow' => array(
        'classname'     => 'mod_readingflow_external',
        'methodname'    => 'create_readingflow',
        'classpath'     => 'mod/readingflow/externallib.php',
        'description'   => 'Create a new reading flow instance',
        'type'          => 'write',
        'capabilities'  => 'mod/readingflow:addinstance',
        'ajax'          => true,
    ),

    'mod_readingflow_update_readingflow' => array(
        'classname'     => 'mod_readingflow_external',
        'methodname'    => 'update_readingflow',
        'classpath'     => 'mod/readingflow/externallib.php',
        'description'   => 'Update an existing reading flow instance',
        'type'          => 'write',
        'capabilities'  => 'mod/readingflow:addinstance',
        'ajax'          => true,
    ),

    'mod_readingflow_get_readingflow' => array(
        'classname'     => 'mod_readingflow_external',
        'methodname'    => 'get_readingflow',
        'classpath'     => 'mod/readingflow/externallib.php',
        'description'   => 'Get reading flow instance details',
        'type'          => 'read',
        'capabilities'  => 'mod/readingflow:view',
        'ajax'          => true,
    ),

    'mod_readingflow_delete_readingflow' => array(
        'classname'     => 'mod_readingflow_external',
        'methodname'    => 'delete_readingflow',
        'classpath'     => 'mod/readingflow/externallib.php',
        'description'   => 'Delete a reading flow instance',
        'type'          => 'write',
        'capabilities'  => 'mod/readingflow:addinstance',
        'ajax'          => true,
    ),

);

