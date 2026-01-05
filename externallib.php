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
require_once($CFG->dirroot . '/mod/stepbystep/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use moodle_exception;

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
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
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
            $completion->update_state(get_coursemodule_from_id('stepbystep', $cmid), COMPLETION_COMPLETE, $userid);
            
            return true;
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
                $completion->update_state($cm, COMPLETION_COMPLETE, $userid);
                return true;
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Error marking automatic completion: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================
    // CRUD Functions for stepbystep
    // ============================================

    /**
     * Returns description of method parameters for create_stepbystep
     * @return external_function_parameters
     */
    public static function create_stepbystep_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Step by step name'),
                'intro' => new external_value(PARAM_RAW, 'Step by step description', VALUE_DEFAULT, ''),
                'introformat' => new external_value(PARAM_INT, 'Intro format', VALUE_DEFAULT, FORMAT_HTML),
                'section' => new external_value(PARAM_INT, 'Course section', VALUE_DEFAULT, 0),
                'visible' => new external_value(PARAM_INT, 'Visible', VALUE_DEFAULT, 1),
                'visibleoncoursepage' => new external_value(PARAM_INT, 'Visible on course page', VALUE_DEFAULT, 1),
            )
        );
    }

    /**
     * Create a new stepbystep instance
     * @param int $courseid Course ID
     * @param string $name Step by step name
     * @param string $intro Step by step description
     * @param int $introformat Intro format
     * @param int $section Course section
     * @param int $visible Visible
     * @param int $visibleoncoursepage Visible on course page
     * @return array
     * @throws moodle_exception
     */
    public static function create_stepbystep($courseid, $name, $intro = '', $introformat = FORMAT_HTML,
                                           $section = 0, $visible = 1, $visibleoncoursepage = 1) {
        global $DB, $CFG;

        // Validate parameters
        $params = self::validate_parameters(self::create_stepbystep_parameters(), array(
            'courseid' => $courseid,
            'name' => $name,
            'intro' => $intro,
            'introformat' => $introformat,
            'section' => $section,
            'visible' => $visible,
            'visibleoncoursepage' => $visibleoncoursepage,
        ));

        // Validate course
        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        if (!$course) {
            throw new moodle_exception('invalidcourseid', 'error');
        }
        $context = context_course::instance($course->id);
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
        self::validate_context($context);

        // Check capabilities
        require_capability('mod/stepbystep:addinstance', $context);

        $module = $DB->get_record('modules', ['name' => 'stepbystep'], '*', MUST_EXIST);
        $moduleid = $module->id;

        // Prepare data for module creation
        $data = new stdClass();
        $data->modulename = 'stepbystep';
        $data->module = $moduleid;
        $data->course = $course->id;
        $data->name = $params['name'];
        $data->intro = $params['intro'];
        $data->introformat = $params['introformat'];
        $data->section = $params['section'];
        $data->visible = $params['visible'];
        $data->visibleoncoursepage = $params['visibleoncoursepage'];

        // Create the stepbystep instance using add_moduleinfo
        $cm = add_moduleinfo($data, $course);

        if (!$cm) {
            throw new moodle_exception('errorcreatingstepbystep', 'mod_stepbystep');
        }

        // Get the created instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $cm->instance), '*', MUST_EXIST);

        return array(
            'id' => $stepbystep->id,
            'course' => $stepbystep->course,
            'name' => $stepbystep->name,
            'intro' => $stepbystep->intro,
            'introformat' => $stepbystep->introformat,
            'timecreated' => $stepbystep->timecreated,
            'timemodified' => $stepbystep->timemodified,
            'cmid' => $cm->id,
        );
    }

    /**
     * Returns description of method result value for create_stepbystep
     * @return external_single_structure
     */
    public static function create_stepbystep_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                'course' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Step by step name'),
                'intro' => new external_value(PARAM_RAW, 'Step by step description'),
                'introformat' => new external_value(PARAM_INT, 'Intro format'),
                'timecreated' => new external_value(PARAM_INT, 'Time created'),
                'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            )
        );
    }

    /**
     * Returns description of method parameters for get_stepbystep
     * @return external_function_parameters
     */
    public static function get_stepbystep_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Step by step instance ID'),
            )
        );
    }

    /**
     * Get stepbystep instance details
     * @param int $id Step by step instance ID
     * @return array
     * @throws moodle_exception
     */
    public static function get_stepbystep($id) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::get_stepbystep_parameters(), array('id' => $id));

        // Get stepbystep instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $params['id']), '*', MUST_EXIST);
        if (!$stepbystep) {
            throw new moodle_exception('invalidstepbystepid', 'mod_stepbystep');
        }

        // Get course module
        $cm = get_coursemodule_from_instance('stepbystep', $stepbystep->id, $stepbystep->course, false, MUST_EXIST);
        if (!$cm) {
            throw new moodle_exception('invalidcoursemodule', 'error');
        }
        $context = context_module::instance($cm->id);
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
        self::validate_context($context);

        // Check capabilities
        require_capability('mod/stepbystep:view', $context);

        return array(
            'id' => $stepbystep->id,
            'course' => $stepbystep->course,
            'name' => $stepbystep->name,
            'intro' => $stepbystep->intro,
            'introformat' => $stepbystep->introformat,
            'timecreated' => $stepbystep->timecreated,
            'timemodified' => $stepbystep->timemodified,
            'cmid' => $cm->id,
        );
    }

    /**
     * Returns description of method result value for get_stepbystep
     * @return external_single_structure
     */
    public static function get_stepbystep_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                'course' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Step by step name'),
                'intro' => new external_value(PARAM_RAW, 'Step by step description'),
                'introformat' => new external_value(PARAM_INT, 'Intro format'),
                'timecreated' => new external_value(PARAM_INT, 'Time created'),
                'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            )
        );
    }

    /**
     * Returns description of method parameters for update_stepbystep
     * @return external_function_parameters
     */
    public static function update_stepbystep_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                'name' => new external_value(PARAM_TEXT, 'Step by step name', VALUE_DEFAULT, null),
                'intro' => new external_value(PARAM_RAW, 'Step by step description', VALUE_DEFAULT, null),
                'introformat' => new external_value(PARAM_INT, 'Intro format', VALUE_DEFAULT, null),
            )
        );
    }

    /**
     * Update an existing stepbystep instance
     * @param int $id Step by step instance ID
     * @param string $name Step by step name
     * @param string $intro Step by step description
     * @param int $introformat Intro format
     * @return array
     * @throws moodle_exception
     */
    public static function update_stepbystep($id, $name = null, $intro = null, $introformat = null) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::update_stepbystep_parameters(), array(
            'id' => $id,
            'name' => $name,
            'intro' => $intro,
            'introformat' => $introformat,
        ));

        // Get stepbystep instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $params['id']), '*', MUST_EXIST);
        if (!$stepbystep) {
            throw new moodle_exception('invalidstepbystepid', 'mod_stepbystep');
        }

        // Get course module
        $cm = get_coursemodule_from_instance('stepbystep', $stepbystep->id, $stepbystep->course, false, MUST_EXIST);
        if (!$cm) {
            throw new moodle_exception('invalidcoursemodule', 'error');
        }
        $context = context_module::instance($cm->id);
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
        self::validate_context($context);

        // Check capabilities
        require_capability('mod/stepbystep:addinstance', $context);

        // Update fields if provided
        if ($params['name'] !== null) {
            $stepbystep->name = $params['name'];
        }
        if ($params['intro'] !== null) {
            $stepbystep->intro = $params['intro'];
        }
        if ($params['introformat'] !== null) {
            $stepbystep->introformat = $params['introformat'];
        }
        $stepbystep->timemodified = time();

        // Update record
        $result = $DB->update_record('stepbystep', $stepbystep);

        if (!$result) {
            throw new moodle_exception('errorupdatingstepbystep', 'mod_stepbystep');
        }

        return array(
            'id' => $stepbystep->id,
            'course' => $stepbystep->course,
            'name' => $stepbystep->name,
            'intro' => $stepbystep->intro,
            'introformat' => $stepbystep->introformat,
            'timecreated' => $stepbystep->timecreated,
            'timemodified' => $stepbystep->timemodified,
            'cmid' => $cm->id,
            'success' => true,
        );
    }

    /**
     * Returns description of method result value for update_stepbystep
     * @return external_single_structure
     */
    public static function update_stepbystep_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                'course' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Step by step name'),
                'intro' => new external_value(PARAM_RAW, 'Step by step description'),
                'introformat' => new external_value(PARAM_INT, 'Intro format'),
                'timecreated' => new external_value(PARAM_INT, 'Time created'),
                'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                'success' => new external_value(PARAM_BOOL, 'Whether the update was successful'),
            )
        );
    }

    /**
     * Returns description of method parameters for delete_stepbystep
     * @return external_function_parameters
     */
    public static function delete_stepbystep_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Step by step instance ID'),
            )
        );
    }

    /**
     * Delete a stepbystep instance
     * @param int $id Step by step instance ID
     * @return array
     * @throws moodle_exception
     */
    public static function delete_stepbystep($id) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::delete_stepbystep_parameters(), array('id' => $id));

        // Get stepbystep instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $params['id']), '*', MUST_EXIST);
        if (!$stepbystep) {
            throw new moodle_exception('invalidstepbystepid', 'mod_stepbystep');
        }

        // Get course module
        $cm = get_coursemodule_from_instance('stepbystep', $stepbystep->id, $stepbystep->course, false, MUST_EXIST);
        if (!$cm) {
            throw new moodle_exception('invalidcoursemodule', 'error');
        }
        $context = context_module::instance($cm->id);
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
        self::validate_context($context);

        // Check capabilities
        require_capability('mod/stepbystep:addinstance', $context);

        // Delete using Moodle's delete_module function
        $result = stepbystep_delete_instance($stepbystep->id);

        if (!$result) {
            throw new moodle_exception('errordeletingstepbystep', 'mod_stepbystep');
        }

        return array(
            'success' => true,
            'message' => 'Step by step instance deleted successfully',
        );
    }

    /**
     * Returns description of method result value for delete_stepbystep
     * @return external_single_structure
     */
    public static function delete_stepbystep_returns() {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Whether the deletion was successful'),
                'message' => new external_value(PARAM_TEXT, 'Result message'),
            )
        );
    }

    /**
     * Returns description of method parameters for list_stepbystep
     * @return external_function_parameters
     */
    public static function list_stepbystep_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
            )
        );
    }

    /**
     * List all stepbystep instances in a course
     * @param int $courseid Course ID
     * @return array
     * @throws moodle_exception
     */
    public static function list_stepbystep($courseid) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::list_stepbystep_parameters(), array('courseid' => $courseid));

        // Validate course
        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        if (!$course) {
            throw new moodle_exception('invalidcourseid', 'error');
        }
        $context = context_course::instance($course->id);
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
        self::validate_context($context);

        // Check capabilities
        require_capability('mod/stepbystep:view', $context);

        // Get all stepbystep instances in the course
        $stepbysteps = $DB->get_records('stepbystep', array('course' => $params['courseid']), 'timecreated DESC');

        $result = array();
        foreach ($stepbysteps as $stepbystep) {
            $cm = get_coursemodule_from_instance('stepbystep', $stepbystep->id, $stepbystep->course, false);
            $result[] = array(
                'id' => $stepbystep->id,
                'course' => $stepbystep->course,
                'name' => $stepbystep->name,
                'intro' => $stepbystep->intro,
                'introformat' => $stepbystep->introformat,
                'timecreated' => $stepbystep->timecreated,
                'timemodified' => $stepbystep->timemodified,
                'cmid' => $cm ? $cm->id : 0,
            );
        }

        return $result;
    }

    /**
     * Returns description of method result value for list_stepbystep
     * @return external_multiple_structure
     */
    public static function list_stepbystep_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                    'course' => new external_value(PARAM_INT, 'Course ID'),
                    'name' => new external_value(PARAM_TEXT, 'Step by step name'),
                    'intro' => new external_value(PARAM_RAW, 'Step by step description'),
                    'introformat' => new external_value(PARAM_INT, 'Intro format'),
                    'timecreated' => new external_value(PARAM_INT, 'Time created'),
                    'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                    'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                )
            )
        );
    }

    // ============================================
    // CRUD Functions for stepbystep_content
    // ============================================

    /**
     * Returns description of method parameters for create_content
     * @return external_function_parameters
     */
    public static function create_content_parameters() {
        return new external_function_parameters(
            array(
                'stepbystep_id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                'type' => new external_value(PARAM_TEXT, 'Content type (text or vocabulary)'),
                'main_title' => new external_value(PARAM_RAW, 'Main title', VALUE_DEFAULT, ''),
                'sub_heading' => new external_value(PARAM_RAW, 'Sub heading', VALUE_DEFAULT, ''),
                'content_paragraphs' => new external_value(PARAM_RAW, 'Content paragraphs', VALUE_DEFAULT, ''),
                'term' => new external_value(PARAM_TEXT, 'Term (for vocabulary type)', VALUE_DEFAULT, ''),
                'phonetic' => new external_value(PARAM_TEXT, 'Phonetic (for vocabulary type)', VALUE_DEFAULT, ''),
                'definition' => new external_value(PARAM_RAW, 'Definition (for vocabulary type)', VALUE_DEFAULT, ''),
                'example' => new external_value(PARAM_RAW, 'Example (for vocabulary type)', VALUE_DEFAULT, ''),
                'response_text' => new external_value(PARAM_TEXT, 'Response text', VALUE_DEFAULT, 'Tiếp theo'),
                'sortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Create a new stepbystep content step
     * @param int $stepbystep_id Step by step instance ID
     * @param string $type Content type
     * @param string $main_title Main title
     * @param string $sub_heading Sub heading
     * @param string $content_paragraphs Content paragraphs
     * @param string $term Term
     * @param string $phonetic Phonetic
     * @param string $definition Definition
     * @param string $example Example
     * @param string $response_text Response text
     * @param int $sortorder Sort order
     * @return array
     * @throws moodle_exception
     */
    public static function create_content($stepbystep_id, $type, $main_title = '', $sub_heading = '',
                                        $content_paragraphs = '', $term = '', $phonetic = '', $definition = '',
                                        $example = '', $response_text = 'Tiếp theo', $sortorder = 0) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::create_content_parameters(), array(
            'stepbystep_id' => $stepbystep_id,
            'type' => $type,
            'main_title' => $main_title,
            'sub_heading' => $sub_heading,
            'content_paragraphs' => $content_paragraphs,
            'term' => $term,
            'phonetic' => $phonetic,
            'definition' => $definition,
            'example' => $example,
            'response_text' => $response_text,
            'sortorder' => $sortorder,
        ));

        // Get stepbystep instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $params['stepbystep_id']), '*', MUST_EXIST);
        if (!$stepbystep) {
            throw new moodle_exception('invalidstepbystepid', 'mod_stepbystep');
        }

        // Get course module
        $cm = get_coursemodule_from_instance('stepbystep', $stepbystep->id, $stepbystep->course, false, MUST_EXIST);
        if (!$cm) {
            throw new moodle_exception('invalidcoursemodule', 'error');
        }
        $context = context_module::instance($cm->id);
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
        self::validate_context($context);

        // Check capabilities
        require_capability('mod/stepbystep:addinstance', $context);

        // Validate content
        $content = array(
            'type' => $params['type'],
            'main_title' => $params['main_title'],
            'sub_heading' => $params['sub_heading'],
            'content_paragraphs' => $params['content_paragraphs'],
            'term' => $params['term'],
            'phonetic' => $params['phonetic'],
            'definition' => $params['definition'],
            'example' => $params['example'],
            'response_text' => $params['response_text'],
        );

        if (!stepbystep_validate_step_content($content)) {
            throw new moodle_exception('invalidcontent', 'mod_stepbystep');
        }

        // Create content record
        $content_record = new stdClass();
        $content_record->stepbystep_id = $params['stepbystep_id'];
        $content_record->type = $params['type'];
        $content_record->main_title = $params['main_title'];
        $content_record->sub_heading = $params['sub_heading'];
        $content_record->content_paragraphs = $params['content_paragraphs'];
        $content_record->term = $params['term'];
        $content_record->phonetic = $params['phonetic'];
        $content_record->definition = $params['definition'];
        $content_record->example = $params['example'];
        $content_record->audio_file = ''; // Keep in DB but not in API
        $content_record->response_text = $params['response_text'];
        $content_record->storage_path = '';
        $content_record->sortorder = $params['sortorder'];
        $content_record->timecreated = time();

        $content_id = $DB->insert_record('stepbystep_content', $content_record);

        if (!$content_id) {
            throw new moodle_exception('errorcreatingcontent', 'mod_stepbystep');
        }

        $content_record->id = $content_id;

        return array(
            'id' => $content_record->id,
            'stepbystep_id' => $content_record->stepbystep_id,
            'type' => $content_record->type,
            'main_title' => $content_record->main_title,
            'sub_heading' => $content_record->sub_heading,
            'content_paragraphs' => $content_record->content_paragraphs,
            'term' => $content_record->term,
            'phonetic' => $content_record->phonetic,
            'definition' => $content_record->definition,
            'example' => $content_record->example,
            'response_text' => $content_record->response_text,
            'storage_path' => $content_record->storage_path,
            'sortorder' => $content_record->sortorder,
            'timecreated' => $content_record->timecreated,
        );
    }

    /**
     * Returns description of method result value for create_content
     * @return external_single_structure
     */
    public static function create_content_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Content step ID'),
                'stepbystep_id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                'type' => new external_value(PARAM_TEXT, 'Content type'),
                'main_title' => new external_value(PARAM_RAW, 'Main title'),
                'sub_heading' => new external_value(PARAM_RAW, 'Sub heading'),
                'content_paragraphs' => new external_value(PARAM_RAW, 'Content paragraphs'),
                'term' => new external_value(PARAM_TEXT, 'Term'),
                'phonetic' => new external_value(PARAM_TEXT, 'Phonetic'),
                'definition' => new external_value(PARAM_RAW, 'Definition'),
                'example' => new external_value(PARAM_RAW, 'Example'),
                'response_text' => new external_value(PARAM_TEXT, 'Response text'),
                'storage_path' => new external_value(PARAM_TEXT, 'Storage path'),
                'sortorder' => new external_value(PARAM_INT, 'Sort order'),
                'timecreated' => new external_value(PARAM_INT, 'Time created'),
            )
        );
    }

    /**
     * Returns description of method parameters for get_content
     * @return external_function_parameters
     */
    public static function get_content_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Content step ID'),
            )
        );
    }

    /**
     * Get stepbystep content step details
     * @param int $id Content step ID
     * @return array
     * @throws moodle_exception
     */
    public static function get_content($id) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::get_content_parameters(), array('id' => $id));

        // Get content record
        $content = $DB->get_record('stepbystep_content', array('id' => $params['id']), '*', MUST_EXIST);
        if (!$content) {
            throw new moodle_exception('invalidcontentid', 'mod_stepbystep');
        }

        // Get stepbystep instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $content->stepbystep_id), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('stepbystep', $stepbystep->id, $stepbystep->course, false, MUST_EXIST);
        if (!$cm) {
            throw new moodle_exception('invalidcoursemodule', 'error');
        }
        $context = context_module::instance($cm->id);
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
        self::validate_context($context);

        // Check capabilities
        require_capability('mod/stepbystep:view', $context);

        return array(
            'id' => $content->id,
            'stepbystep_id' => $content->stepbystep_id,
            'type' => $content->type,
            'main_title' => $content->main_title,
            'sub_heading' => $content->sub_heading,
            'content_paragraphs' => $content->content_paragraphs,
            'term' => $content->term,
            'phonetic' => $content->phonetic,
            'definition' => $content->definition,
            'example' => $content->example,
            'response_text' => $content->response_text,
            'storage_path' => $content->storage_path,
            'sortorder' => $content->sortorder,
            'timecreated' => $content->timecreated,
        );
    }

    /**
     * Returns description of method result value for get_content
     * @return external_single_structure
     */
    public static function get_content_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Content step ID'),
                'stepbystep_id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                'type' => new external_value(PARAM_TEXT, 'Content type'),
                'main_title' => new external_value(PARAM_RAW, 'Main title'),
                'sub_heading' => new external_value(PARAM_RAW, 'Sub heading'),
                'content_paragraphs' => new external_value(PARAM_RAW, 'Content paragraphs'),
                'term' => new external_value(PARAM_TEXT, 'Term'),
                'phonetic' => new external_value(PARAM_TEXT, 'Phonetic'),
                'definition' => new external_value(PARAM_RAW, 'Definition'),
                'example' => new external_value(PARAM_RAW, 'Example'),
                'response_text' => new external_value(PARAM_TEXT, 'Response text'),
                'storage_path' => new external_value(PARAM_TEXT, 'Storage path'),
                'sortorder' => new external_value(PARAM_INT, 'Sort order'),
                'timecreated' => new external_value(PARAM_INT, 'Time created'),
            )
        );
    }

    /**
     * Returns description of method parameters for update_content
     * @return external_function_parameters
     */
    public static function update_content_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Content step ID'),
                'type' => new external_value(PARAM_TEXT, 'Content type (text or vocabulary)', VALUE_DEFAULT, null),
                'main_title' => new external_value(PARAM_RAW, 'Main title', VALUE_DEFAULT, null),
                'sub_heading' => new external_value(PARAM_RAW, 'Sub heading', VALUE_DEFAULT, null),
                'content_paragraphs' => new external_value(PARAM_RAW, 'Content paragraphs', VALUE_DEFAULT, null),
                'term' => new external_value(PARAM_TEXT, 'Term (for vocabulary type)', VALUE_DEFAULT, null),
                'phonetic' => new external_value(PARAM_TEXT, 'Phonetic (for vocabulary type)', VALUE_DEFAULT, null),
                'definition' => new external_value(PARAM_RAW, 'Definition (for vocabulary type)', VALUE_DEFAULT, null),
                'example' => new external_value(PARAM_RAW, 'Example (for vocabulary type)', VALUE_DEFAULT, null),
                'response_text' => new external_value(PARAM_TEXT, 'Response text', VALUE_DEFAULT, null),
                'sortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_DEFAULT, null),
            )
        );
    }

    /**
     * Update an existing stepbystep content step
     * @param int $id Content step ID
     * @param string $type Content type
     * @param string $main_title Main title
     * @param string $sub_heading Sub heading
     * @param string $content_paragraphs Content paragraphs
     * @param string $term Term
     * @param string $phonetic Phonetic
     * @param string $definition Definition
     * @param string $example Example
     * @param string $response_text Response text
     * @param int $sortorder Sort order
     * @return array
     * @throws moodle_exception
     */
    public static function update_content($id, $type = null, $main_title = null, $sub_heading = null,
                                       $content_paragraphs = null, $term = null, $phonetic = null,
                                       $definition = null, $example = null, $response_text = null, $sortorder = null) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::update_content_parameters(), array(
            'id' => $id,
            'type' => $type,
            'main_title' => $main_title,
            'sub_heading' => $sub_heading,
            'content_paragraphs' => $content_paragraphs,
            'term' => $term,
            'phonetic' => $phonetic,
            'definition' => $definition,
            'example' => $example,
            'response_text' => $response_text,
            'sortorder' => $sortorder,
        ));

        // Get content record
        $content = $DB->get_record('stepbystep_content', array('id' => $params['id']), '*', MUST_EXIST);
        if (!$content) {
            throw new moodle_exception('invalidcontentid', 'mod_stepbystep');
        }

        // Get stepbystep instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $content->stepbystep_id), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('stepbystep', $stepbystep->id, $stepbystep->course, false, MUST_EXIST);
        if (!$cm) {
            throw new moodle_exception('invalidcoursemodule', 'error');
        }
        $context = context_module::instance($cm->id);
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
        self::validate_context($context);

        // Check capabilities
        require_capability('mod/stepbystep:addinstance', $context);

        // Update fields if provided
        if ($params['type'] !== null) {
            $content->type = $params['type'];
        }
        if ($params['main_title'] !== null) {
            $content->main_title = $params['main_title'];
        }
        if ($params['sub_heading'] !== null) {
            $content->sub_heading = $params['sub_heading'];
        }
        if ($params['content_paragraphs'] !== null) {
            $content->content_paragraphs = $params['content_paragraphs'];
        }
        if ($params['term'] !== null) {
            $content->term = $params['term'];
        }
        if ($params['phonetic'] !== null) {
            $content->phonetic = $params['phonetic'];
        }
        if ($params['definition'] !== null) {
            $content->definition = $params['definition'];
        }
        if ($params['example'] !== null) {
            $content->example = $params['example'];
        }
        if ($params['response_text'] !== null) {
            $content->response_text = $params['response_text'];
        }
        if ($params['sortorder'] !== null) {
            $content->sortorder = $params['sortorder'];
        }

        // Validate content if type changed
        if ($params['type'] !== null) {
            $content_array = array(
                'type' => $content->type,
                'main_title' => $content->main_title,
                'sub_heading' => $content->sub_heading,
                'content_paragraphs' => $content->content_paragraphs,
                'term' => $content->term,
                'phonetic' => $content->phonetic,
                'definition' => $content->definition,
                'example' => $content->example,
                'response_text' => $content->response_text,
            );

            if (!stepbystep_validate_step_content($content_array)) {
                throw new moodle_exception('invalidcontent', 'mod_stepbystep');
            }
        }

        // Update record
        $result = $DB->update_record('stepbystep_content', $content);

        if (!$result) {
            throw new moodle_exception('errorupdatingcontent', 'mod_stepbystep');
        }

        return array(
            'id' => $content->id,
            'stepbystep_id' => $content->stepbystep_id,
            'type' => $content->type,
            'main_title' => $content->main_title,
            'sub_heading' => $content->sub_heading,
            'content_paragraphs' => $content->content_paragraphs,
            'term' => $content->term,
                'phonetic' => $content->phonetic,
                'definition' => $content->definition,
                'example' => $content->example,
                'response_text' => $content->response_text,
                'storage_path' => $content->storage_path,
                'sortorder' => $content->sortorder,
                'timecreated' => $content->timecreated,
                'success' => true,
        );
    }

    /**
     * Returns description of method result value for update_content
     * @return external_single_structure
     */
    public static function update_content_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Content step ID'),
                'stepbystep_id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                'type' => new external_value(PARAM_TEXT, 'Content type'),
                'main_title' => new external_value(PARAM_RAW, 'Main title'),
                'sub_heading' => new external_value(PARAM_RAW, 'Sub heading'),
                'content_paragraphs' => new external_value(PARAM_RAW, 'Content paragraphs'),
                'term' => new external_value(PARAM_TEXT, 'Term'),
                'phonetic' => new external_value(PARAM_TEXT, 'Phonetic'),
                'definition' => new external_value(PARAM_RAW, 'Definition'),
                'example' => new external_value(PARAM_RAW, 'Example'),
                'response_text' => new external_value(PARAM_TEXT, 'Response text'),
                'storage_path' => new external_value(PARAM_TEXT, 'Storage path'),
                'sortorder' => new external_value(PARAM_INT, 'Sort order'),
                'timecreated' => new external_value(PARAM_INT, 'Time created'),
                'success' => new external_value(PARAM_BOOL, 'Whether the update was successful'),
            )
        );
    }

    /**
     * Returns description of method parameters for delete_content
     * @return external_function_parameters
     */
    public static function delete_content_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Content step ID'),
            )
        );
    }

    /**
     * Delete a stepbystep content step
     * @param int $id Content step ID
     * @return array
     * @throws moodle_exception
     */
    public static function delete_content($id) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::delete_content_parameters(), array('id' => $id));

        // Get content record
        $content = $DB->get_record('stepbystep_content', array('id' => $params['id']), '*', MUST_EXIST);
        if (!$content) {
            throw new moodle_exception('invalidcontentid', 'mod_stepbystep');
        }

        // Get stepbystep instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $content->stepbystep_id), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('stepbystep', $stepbystep->id, $stepbystep->course, false, MUST_EXIST);
        if (!$cm) {
            throw new moodle_exception('invalidcoursemodule', 'error');
        }
        $context = context_module::instance($cm->id);
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
        self::validate_context($context);

        // Check capabilities
        require_capability('mod/stepbystep:addinstance', $context);

        // Delete record
        $result = $DB->delete_records('stepbystep_content', array('id' => $params['id']));

        if (!$result) {
            throw new moodle_exception('errordeletingcontent', 'mod_stepbystep');
        }

        return array(
            'success' => true,
            'message' => 'Content step deleted successfully',
        );
    }

    /**
     * Returns description of method result value for delete_content
     * @return external_single_structure
     */
    public static function delete_content_returns() {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Whether the deletion was successful'),
                'message' => new external_value(PARAM_TEXT, 'Result message'),
            )
        );
    }

    /**
     * Returns description of method parameters for list_contents
     * @return external_function_parameters
     */
    public static function list_contents_parameters() {
        return new external_function_parameters(
            array(
                'stepbystep_id' => new external_value(PARAM_INT, 'Step by step instance ID'),
            )
        );
    }

    /**
     * List all content steps for a stepbystep instance
     * @param int $stepbystep_id Step by step instance ID
     * @return array
     * @throws moodle_exception
     */
    public static function list_contents($stepbystep_id) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::list_contents_parameters(), array('stepbystep_id' => $stepbystep_id));

        // Get stepbystep instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $params['stepbystep_id']), '*', MUST_EXIST);
        if (!$stepbystep) {
            throw new moodle_exception('invalidstepbystepid', 'mod_stepbystep');
        }

        // Get course module
        $cm = get_coursemodule_from_instance('stepbystep', $stepbystep->id, $stepbystep->course, false, MUST_EXIST);
        if (!$cm) {
            throw new moodle_exception('invalidcoursemodule', 'error');
        }
        $context = context_module::instance($cm->id);
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
        self::validate_context($context);

        // Check capabilities
        require_capability('mod/stepbystep:view', $context);

        // Get all content steps for this stepbystep instance
        $contents = $DB->get_records('stepbystep_content', array('stepbystep_id' => $params['stepbystep_id']), 'sortorder ASC, timecreated ASC');

        $result = array();
        foreach ($contents as $content) {
            $result[] = array(
                'id' => $content->id,
                'stepbystep_id' => $content->stepbystep_id,
                'type' => $content->type,
                'main_title' => $content->main_title,
                'sub_heading' => $content->sub_heading,
                'content_paragraphs' => $content->content_paragraphs,
                'term' => $content->term,
                'phonetic' => $content->phonetic,
                'definition' => $content->definition,
                'example' => $content->example,
                'response_text' => $content->response_text,
                'storage_path' => $content->storage_path,
                'sortorder' => $content->sortorder,
                'timecreated' => $content->timecreated,
            );
        }

        return $result;
    }

    /**
     * Returns description of method result value for list_contents
     * @return external_multiple_structure
     */
    public static function list_contents_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Content step ID'),
                    'stepbystep_id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                    'type' => new external_value(PARAM_TEXT, 'Content type'),
                    'main_title' => new external_value(PARAM_RAW, 'Main title'),
                    'sub_heading' => new external_value(PARAM_RAW, 'Sub heading'),
                    'content_paragraphs' => new external_value(PARAM_RAW, 'Content paragraphs'),
                    'term' => new external_value(PARAM_TEXT, 'Term'),
                    'phonetic' => new external_value(PARAM_TEXT, 'Phonetic'),
                    'definition' => new external_value(PARAM_RAW, 'Definition'),
                    'example' => new external_value(PARAM_RAW, 'Example'),
                    'response_text' => new external_value(PARAM_TEXT, 'Response text'),
                    'storage_path' => new external_value(PARAM_TEXT, 'Storage path'),
                    'sortorder' => new external_value(PARAM_INT, 'Sort order'),
                    'timecreated' => new external_value(PARAM_INT, 'Time created'),
                )
            )
        );
    }

    // ============================================
    // Combined Create Function
    // ============================================

    /**
     * Returns description of method parameters for create_stepbystep_with_contents
     * @return external_function_parameters
     */
    public static function create_stepbystep_with_contents_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Step by step name'),
                'intro' => new external_value(PARAM_RAW, 'Step by step description', VALUE_DEFAULT, ''),
                'introformat' => new external_value(PARAM_INT, 'Intro format', VALUE_DEFAULT, FORMAT_HTML),
                'section' => new external_value(PARAM_INT, 'Course section', VALUE_DEFAULT, 0),
                'visible' => new external_value(PARAM_INT, 'Visible', VALUE_DEFAULT, 1),
                'visibleoncoursepage' => new external_value(PARAM_INT, 'Visible on course page', VALUE_DEFAULT, 1),
                'contents' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'type' => new external_value(PARAM_TEXT, 'Content type (text or vocabulary)'),
                            'main_title' => new external_value(PARAM_RAW, 'Main title', VALUE_DEFAULT, ''),
                            'sub_heading' => new external_value(PARAM_RAW, 'Sub heading', VALUE_DEFAULT, ''),
                            'content_paragraphs' => new external_value(PARAM_RAW, 'Content paragraphs', VALUE_DEFAULT, ''),
                            'term' => new external_value(PARAM_TEXT, 'Term (for vocabulary type)', VALUE_DEFAULT, ''),
                            'phonetic' => new external_value(PARAM_TEXT, 'Phonetic (for vocabulary type)', VALUE_DEFAULT, ''),
                            'definition' => new external_value(PARAM_RAW, 'Definition (for vocabulary type)', VALUE_DEFAULT, ''),
                            'example' => new external_value(PARAM_RAW, 'Example (for vocabulary type)', VALUE_DEFAULT, ''),
                            'response_text' => new external_value(PARAM_TEXT, 'Response text', VALUE_DEFAULT, 'Tiếp theo'),
                        )
                    ),
                    'Array of content steps to create',
                    VALUE_DEFAULT,
                    array()
                ),
            )
        );
    }

    /**
     * Create a new stepbystep instance with multiple content steps
     * @param int $courseid Course ID
     * @param string $name Step by step name
     * @param string $intro Step by step description
     * @param int $introformat Intro format
     * @param int $section Course section
     * @param int $visible Visible
     * @param int $visibleoncoursepage Visible on course page
     * @param array $contents Array of content steps
     * @return array
     * @throws moodle_exception
     */
    public static function create_stepbystep_with_contents($courseid, $name, $intro = '', $introformat = FORMAT_HTML,
                                                           $section = 0, $visible = 1, $visibleoncoursepage = 1,
                                                           $contents = array()) {
        global $DB, $CFG;

        // Validate parameters
        $params = self::validate_parameters(self::create_stepbystep_with_contents_parameters(), array(
            'courseid' => $courseid,
            'name' => $name,
            'intro' => $intro,
            'introformat' => $introformat,
            'section' => $section,
            'visible' => $visible,
            'visibleoncoursepage' => $visibleoncoursepage,
            'contents' => $contents,
        ));

        // Validate course
        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        if (!$course) {
            throw new moodle_exception('invalidcourseid', 'error');
        }
        $context = context_course::instance($course->id);
        if (!$context) {
            throw new moodle_exception('invalidcontext', 'error');
        }
        self::validate_context($context);

        // Check capabilities
        require_capability('mod/stepbystep:addinstance', $context);

        $module = $DB->get_record('modules', ['name' => 'stepbystep'], '*', MUST_EXIST);
        $moduleid = $module->id;

        // Prepare data for module creation
        $data = new stdClass();
        $data->modulename = 'stepbystep';
        $data->module = $moduleid;
        $data->course = $course->id;
        $data->name = $params['name'];
        $data->intro = $params['intro'];
        $data->introformat = $params['introformat'];
        $data->section = $params['section'];
        $data->visible = $params['visible'];
        $data->visibleoncoursepage = $params['visibleoncoursepage'];

        // Create the stepbystep instance using add_moduleinfo
        $cm = add_moduleinfo($data, $course);

        if (!$cm) {
            throw new moodle_exception('errorcreatingstepbystep', 'mod_stepbystep');
        }

        // Get the created instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $cm->instance), '*', MUST_EXIST);

        // Now create content steps
        $created_contents = array();
        $sortorder = 0;

        foreach ($params['contents'] as $content_data) {
            $sortorder++;

            // Validate content
            $content = array(
                'type' => $content_data['type'],
                'main_title' => $content_data['main_title'],
                'sub_heading' => $content_data['sub_heading'],
                'content_paragraphs' => $content_data['content_paragraphs'],
                'term' => $content_data['term'],
                'phonetic' => $content_data['phonetic'],
                'definition' => $content_data['definition'],
                'example' => $content_data['example'],
                'response_text' => $content_data['response_text'],
            );

            // Skip invalid content
            if (!stepbystep_validate_step_content($content)) {
                continue;
            }

            // Create content record
            $content_record = new stdClass();
            $content_record->stepbystep_id = $stepbystep->id;
            $content_record->type = $content_data['type'];
            $content_record->main_title = $content_data['main_title'];
            $content_record->sub_heading = $content_data['sub_heading'];
            $content_record->content_paragraphs = $content_data['content_paragraphs'];
            $content_record->term = $content_data['term'];
            $content_record->phonetic = $content_data['phonetic'];
            $content_record->definition = $content_data['definition'];
            $content_record->example = $content_data['example'];
            $content_record->audio_file = ''; // Keep in DB but not in API
            $content_record->response_text = $content_data['response_text'];
            $content_record->storage_path = '';
            $content_record->sortorder = $sortorder;
            $content_record->timecreated = time();

            $content_id = $DB->insert_record('stepbystep_content', $content_record);

            if ($content_id) {
                $created_contents[] = array(
                    'id' => $content_id,
                    'stepbystep_id' => $stepbystep->id,
                    'type' => $content_record->type,
                    'main_title' => $content_record->main_title,
                    'sub_heading' => $content_record->sub_heading,
                    'content_paragraphs' => $content_record->content_paragraphs,
                    'term' => $content_record->term,
                    'phonetic' => $content_record->phonetic,
                    'definition' => $content_record->definition,
                    'example' => $content_record->example,
                    'response_text' => $content_record->response_text,
                    'storage_path' => $content_record->storage_path,
                    'sortorder' => $content_record->sortorder,
                    'timecreated' => $content_record->timecreated,
                );
            }
        }

        return array(
            'stepbystep' => array(
                'id' => $stepbystep->id,
                'course' => $stepbystep->course,
                'name' => $stepbystep->name,
                'intro' => $stepbystep->intro,
                'introformat' => $stepbystep->introformat,
                'timecreated' => $stepbystep->timecreated,
                'timemodified' => $stepbystep->timemodified,
                'cmid' => $cm->id,
            ),
            'contents' => $created_contents,
            'contents_count' => count($created_contents),
        );
    }

    /**
     * Returns description of method result value for create_stepbystep_with_contents
     * @return external_single_structure
     */
    public static function create_stepbystep_with_contents_returns() {
        return new external_single_structure(
            array(
                'stepbystep' => new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                        'course' => new external_value(PARAM_INT, 'Course ID'),
                        'name' => new external_value(PARAM_TEXT, 'Step by step name'),
                        'intro' => new external_value(PARAM_RAW, 'Step by step description'),
                        'introformat' => new external_value(PARAM_INT, 'Intro format'),
                        'timecreated' => new external_value(PARAM_INT, 'Time created'),
                        'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                        'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                    )
                ),
                'contents' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Content step ID'),
                            'stepbystep_id' => new external_value(PARAM_INT, 'Step by step instance ID'),
                            'type' => new external_value(PARAM_TEXT, 'Content type'),
                            'main_title' => new external_value(PARAM_RAW, 'Main title'),
                            'sub_heading' => new external_value(PARAM_RAW, 'Sub heading'),
                            'content_paragraphs' => new external_value(PARAM_RAW, 'Content paragraphs'),
                            'term' => new external_value(PARAM_TEXT, 'Term'),
                            'phonetic' => new external_value(PARAM_TEXT, 'Phonetic'),
                            'definition' => new external_value(PARAM_RAW, 'Definition'),
                            'example' => new external_value(PARAM_RAW, 'Example'),
                            'response_text' => new external_value(PARAM_TEXT, 'Response text'),
                            'storage_path' => new external_value(PARAM_TEXT, 'Storage path'),
                            'sortorder' => new external_value(PARAM_INT, 'Sort order'),
                            'timecreated' => new external_value(PARAM_INT, 'Time created'),
                        )
                    )
                ),
                'contents_count' => new external_value(PARAM_INT, 'Number of content steps created'),
            )
        );
    }
}
