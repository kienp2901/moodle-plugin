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
 * checkmatepdf external functions and service definitions.
 *
 * @package    mod_checkmatepdf
 * @category   external
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_checkmatepdf_create_checkmatepdf' => array(
        'classname' => 'mod_checkmatepdf_external',
        'methodname' => 'create_checkmatepdf',
        'classpath' => 'mod/checkmatepdf/externallib.php',
        'description' => 'Create a new checkmatepdf activity instance',
        'type' => 'write',
        'capabilities' => 'mod/checkmatepdf:addinstance',
        'ajax' => true,
    ),

    'mod_checkmatepdf_get_checkmatepdf' => array(
        'classname' => 'mod_checkmatepdf_external',
        'methodname' => 'get_checkmatepdf',
        'classpath' => 'mod/checkmatepdf/externallib.php',
        'description' => 'Get checkmatepdf instance details',
        'type' => 'read',
        'capabilities' => 'mod/checkmatepdf:view',
        'ajax' => true,
    ),

    'mod_checkmatepdf_update_checkmatepdf' => array(
        'classname' => 'mod_checkmatepdf_external',
        'methodname' => 'update_checkmatepdf',
        'classpath' => 'mod/checkmatepdf/externallib.php',
        'description' => 'Update an existing checkmatepdf instance',
        'type' => 'write',
        'capabilities' => 'mod/checkmatepdf:addinstance',
        'ajax' => true,
    ),

    'mod_checkmatepdf_delete_checkmatepdf' => array(
        'classname' => 'mod_checkmatepdf_external',
        'methodname' => 'delete_checkmatepdf',
        'classpath' => 'mod/checkmatepdf/externallib.php',
        'description' => 'Delete a checkmatepdf instance',
        'type' => 'write',
        'capabilities' => 'mod/checkmatepdf:addinstance',
        'ajax' => true,
    ),

);
