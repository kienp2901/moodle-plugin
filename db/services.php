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
 * Step by Step module services
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'mod_stepbystep_handle_completion' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'handle_completion',
        'description' => 'Handle step completion based on activity settings',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/stepbystep:view'
    ),
    
    // CRUD services for stepbystep
    'mod_stepbystep_create_stepbystep' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'create_stepbystep',
        'classpath' => 'mod/stepbystep/externallib.php',
        'description' => 'Create a new stepbystep instance',
        'type' => 'write',
        'capabilities' => 'mod/stepbystep:addinstance',
        'ajax' => true,
    ),
    
    'mod_stepbystep_get_stepbystep' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'get_stepbystep',
        'classpath' => 'mod/stepbystep/externallib.php',
        'description' => 'Get stepbystep instance details',
        'type' => 'read',
        'capabilities' => 'mod/stepbystep:view',
        'ajax' => true,
    ),
    
    'mod_stepbystep_update_stepbystep' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'update_stepbystep',
        'classpath' => 'mod/stepbystep/externallib.php',
        'description' => 'Update an existing stepbystep instance',
        'type' => 'write',
        'capabilities' => 'mod/stepbystep:addinstance',
        'ajax' => true,
    ),
    
    'mod_stepbystep_delete_stepbystep' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'delete_stepbystep',
        'classpath' => 'mod/stepbystep/externallib.php',
        'description' => 'Delete a stepbystep instance',
        'type' => 'write',
        'capabilities' => 'mod/stepbystep:addinstance',
        'ajax' => true,
    ),
    
    'mod_stepbystep_list_stepbystep' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'list_stepbystep',
        'classpath' => 'mod/stepbystep/externallib.php',
        'description' => 'List all stepbystep instances in a course',
        'type' => 'read',
        'capabilities' => 'mod/stepbystep:view',
        'ajax' => true,
    ),
    
    // CRUD services for stepbystep_content
    'mod_stepbystep_create_content' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'create_content',
        'classpath' => 'mod/stepbystep/externallib.php',
        'description' => 'Create a new stepbystep content step',
        'type' => 'write',
        'capabilities' => 'mod/stepbystep:addinstance',
        'ajax' => true,
    ),
    
    'mod_stepbystep_get_content' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'get_content',
        'classpath' => 'mod/stepbystep/externallib.php',
        'description' => 'Get stepbystep content step details',
        'type' => 'read',
        'capabilities' => 'mod/stepbystep:view',
        'ajax' => true,
    ),
    
    'mod_stepbystep_update_content' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'update_content',
        'classpath' => 'mod/stepbystep/externallib.php',
        'description' => 'Update an existing stepbystep content step',
        'type' => 'write',
        'capabilities' => 'mod/stepbystep:addinstance',
        'ajax' => true,
    ),
    
    'mod_stepbystep_delete_content' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'delete_content',
        'classpath' => 'mod/stepbystep/externallib.php',
        'description' => 'Delete a stepbystep content step',
        'type' => 'write',
        'capabilities' => 'mod/stepbystep:addinstance',
        'ajax' => true,
    ),
    
    'mod_stepbystep_list_contents' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'list_contents',
        'classpath' => 'mod/stepbystep/externallib.php',
        'description' => 'List all content steps for a stepbystep instance',
        'type' => 'read',
        'capabilities' => 'mod/stepbystep:view',
        'ajax' => true,
    ),
    
    'mod_stepbystep_create_stepbystep_with_contents' => array(
        'classname' => 'mod_stepbystep_external',
        'methodname' => 'create_stepbystep_with_contents',
        'classpath' => 'mod/stepbystep/externallib.php',
        'description' => 'Create a new stepbystep instance with multiple content steps in one call',
        'type' => 'write',
        'capabilities' => 'mod/stepbystep:addinstance',
        'ajax' => true,
    ),
);
