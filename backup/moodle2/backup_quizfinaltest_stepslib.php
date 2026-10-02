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
 * Quiz Final Test backup stepslib
 *
 * @package mod_quizfinaltest
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the backup steps that will be used by the backup_quizfinaltest_activity_task
 */

/**
 * Define the complete quizfinaltest structure for backup, with file and id annotations
 */
class backup_quizfinaltest_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $quizfinaltest = new backup_nested_element('quizfinaltest', array('id'), array(
            'name', 'intro', 'introformat', 'timecreated', 'timemodified'));

        // Build the tree
        // (no special order, they are not exported yet)

        // Define sources
        $quizfinaltest->set_source_table('quizfinaltest', array('id' => backup::VAR_ACTIVITYID));

        // Define id annotations
        // (none)

        // Define file annotations
        $quizfinaltest->annotate_files('mod_quizfinaltest', 'intro', null); // This file area hasn't itemid

        // Return the root element (quizfinaltest), wrapped into standard activity structure
        return $this->prepare_activity_structure($quizfinaltest);
    }
}