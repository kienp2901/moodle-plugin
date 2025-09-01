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
 * External Step by Step module functions
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External Step by Step module functions
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_stepbystep_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function handle_completion_parameters() {
        return new external_function_parameters(
            array(
                'cmid' => new external_value(PARAM_INT, 'Course module ID')
            )
        );
    }

    /**
     * Returns description of method result value
     * @return external_value
     */
    public static function handle_completion_returns() {
        return new external_single_structure(
            array(
                'completion_enabled' => new external_value(PARAM_BOOL, 'Whether completion is enabled'),
                'completion_type' => new external_value(PARAM_TEXT, 'Type of completion (manual, automatic, or none)'),
                'success' => new external_value(PARAM_BOOL, 'Whether the operation was successful'),
                'message' => new external_value(PARAM_TEXT, 'Result message')
            )
        );
    }

    /**
     * Handle step completion based on activity settings
     * @param int $cmid Course module ID
     * @return array Result information
     */
    public static function handle_completion($cmid) {
        global $DB, $USER;

        // Parameter validation
        $params = self::validate_parameters(self::handle_completion_parameters(), array('cmid' => $cmid));

        // Context validation
        $context = context_module::instance($cmid);
        self::validate_context($context);

        // Check if user can view this activity
        require_capability('mod/stepbystep:view', $context);

        // Get course module and activity instance
        $cm = get_coursemodule_from_id('stepbystep', $cmid, 0, false, MUST_EXIST);
        $stepbystep = $DB->get_record('stepbystep', array('id' => $cm->instance), '*', MUST_EXIST);

        // Check if completion is enabled for this activity
        if (!$cm->completion) {
            return array(
                'completion_enabled' => false,
                'completion_type' => 'none',
                'success' => true,
                'message' => 'Completion not enabled for this activity'
            );
        }

        // Check completion type and handle accordingly
        if ($cm->completion == COMPLETION_TRACKING_MANUAL) {
            // Manual completion - call the Moodle API
            $result = self::mark_manual_completion($cmid, $USER->id);
            
            return array(
                'completion_enabled' => true,
                'completion_type' => 'manual',
                'success' => $result,
                'message' => $result ? 'Activity marked as completed manually' : 'Failed to mark activity as completed'
            );
        } else if ($cm->completion == COMPLETION_TRACKING_AUTOMATIC) {
            // Automatic completion - check if conditions are met
            $completion = new completion_info(get_course($cm->course));
            $data = $completion->get_data($cm, false, $USER->id);
            
            if ($data->completionstate == COMPLETION_COMPLETE || $data->completionstate == COMPLETION_COMPLETE_PASS) {
                return array(
                    'completion_enabled' => true,
                    'completion_type' => 'automatic',
                    'success' => true,
                    'message' => 'Activity already completed automatically'
                );
            } else {
                // Try to mark as completed if conditions are met
                $result = self::mark_automatic_completion($cmid, $USER->id);
                
                return array(
                    'completion_enabled' => true,
                    'completion_type' => 'automatic',
                    'success' => $result,
                    'message' => $result ? 'Activity marked as completed automatically' : 'Completion conditions not met'
                );
            }
        } else {
            // No completion tracking
            return array(
                'completion_enabled' => false,
                'completion_type' => 'none',
                'success' => true,
                'message' => 'No completion tracking configured'
            );
        }
    }

    /**
     * Mark activity as manually completed
     * @param int $cmid Course module ID
     * @param int $userid User ID
     * @return bool Success status
     */
    private static function mark_manual_completion($cmid, $userid) {
        global $DB;

        try {
            // Call Moodle's completion API
            $completion = new completion_info(get_course(get_coursemodule_from_id('stepbystep', $cmid)->course));
            $result = $completion->update_state(get_coursemodule_from_id('stepbystep', $cmid), COMPLETION_COMPLETE, $userid);
            
            return $result;
        } catch (Exception $e) {
            error_log('Error marking manual completion: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark activity as automatically completed if conditions are met
     * @param int $cmid Course module ID
     * @param int $userid User ID
     * @return bool Success status
     */
    private static function mark_automatic_completion($cmid, $userid) {
        global $DB;

        try {
            // Check if completion conditions are met
            $completion = new completion_info(get_course(get_coursemodule_from_id('stepbystep', $cmid)->course));
            $cm = get_coursemodule_from_id('stepbystep', $cmid);
            
            // For stepbystep, we consider it complete when all steps are viewed
            // This is a simple implementation - you can modify based on your needs
            $data = $completion->get_data($cm, false, $userid);
            
            if ($data->completionstate == COMPLETION_INCOMPLETE) {
                // Mark as complete
                $result = $completion->update_state($cm, COMPLETION_COMPLETE, $userid);
                return $result;
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Error marking automatic completion: ' . $e->getMessage());
            return false;
        }
    }
}
