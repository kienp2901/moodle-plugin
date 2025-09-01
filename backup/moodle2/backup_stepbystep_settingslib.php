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
 * Define all the backup steps that will be used by the backup_stepbystep_activity_task
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete stepbystep structure for backup, with file and id annotations
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_stepbystep_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define structure
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $stepbystep = new backup_nested_element('stepbystep', array('id'), array(
            'name', 'intro', 'introformat', 'timecreated', 'timemodified'));

        $steps = new backup_nested_element('steps');

        $step = new backup_nested_element('step', array('id'), array(
            'content', 'sortorder', 'timecreated'));

        // Build the tree.
        $stepbystep->add_child($steps);
        $steps->add_child($step);

        // Define sources.
        $stepbystep->set_source_table('stepbystep', array('id' => backup::VAR_ACTIVITYID));

        $step->set_source_table('stepbystep_content', array('stepbystep_id' => backup::VAR_PARENTID));

        // Define id annotations.
        $step->annotate_ids('stepbystep', 'stepbystep_id');

        // Define file annotations.
        $stepbystep->annotate_files('mod_stepbystep', 'intro', null);

        // Return the root element (stepbystep), wrapped into standard activity structure.
        return $this->prepare_activity_structure($stepbystep);
    }
}
