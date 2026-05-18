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
class mod_stepbystep_external extends external_api
{

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function handle_completion_parameters()
    {
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
    public static function handle_completion_returns()
    {
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
    public static function handle_completion($cmid)
    {
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
    private static function mark_manual_completion($cmid, $userid)
    {
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
    private static function mark_automatic_completion($cmid, $userid)
    {
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
    public static function create_stepbystep_parameters()
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Step by step name'),
                'intro' => new external_value(PARAM_RAW, 'Step by step description', VALUE_DEFAULT, ''),
                'introformat' => new external_value(PARAM_INT, 'Intro format', VALUE_DEFAULT, FORMAT_HTML),
                'section' => new external_value(PARAM_INT, 'Course section', VALUE_DEFAULT, 0),
                'visible' => new external_value(PARAM_INT, 'Visible', VALUE_DEFAULT, 1),
                'visibleoncoursepage' => new external_value(PARAM_INT, 'Visible on course page', VALUE_DEFAULT, 1),
                'availabilityconditionsjson' => new external_value(PARAM_RAW, 'Availability conditions JSON', VALUE_DEFAULT, ''),
                'completion' => new external_value(PARAM_INT, 'Completion tracking (0=none,1=manual,2=auto)', VALUE_DEFAULT, 0),
                'completionunlocked' => new external_value(PARAM_INT, 'Completion unlocked', VALUE_DEFAULT, 1),
                'completionview' => new external_value(PARAM_INT, 'Completion view', VALUE_DEFAULT, 0),
                'completionexpected' => new external_value(PARAM_INT, 'Completion expected', VALUE_DEFAULT, 0),
                'tags' => new external_value(PARAM_RAW, 'Tags', VALUE_DEFAULT, ''),
                'showdescription' => new external_value(PARAM_INT, 'Show description', VALUE_DEFAULT, 0),
                'contents' => new external_multiple_structure(
                    new external_single_structure(array(
                        'type' => new external_value(PARAM_TEXT, 'Content type (text or vocabulary)'),
                        'main_title' => new external_value(PARAM_RAW, 'Main title', VALUE_DEFAULT, ''),
                        'sub_heading' => new external_value(PARAM_RAW, 'Sub heading', VALUE_DEFAULT, ''),
                        'content_paragraphs' => new external_value(PARAM_RAW, 'Content paragraphs (HTML)', VALUE_DEFAULT, ''),
                        'term' => new external_value(PARAM_TEXT, 'Term (for vocabulary type)', VALUE_DEFAULT, ''),
                        'phonetic' => new external_value(PARAM_TEXT, 'Phonetic (for vocabulary type)', VALUE_DEFAULT, ''),
                        'definition' => new external_value(PARAM_RAW, 'Definition (for vocabulary type)', VALUE_DEFAULT, ''),
                        'example' => new external_value(PARAM_RAW, 'Example (for vocabulary type)', VALUE_DEFAULT, ''),
                        'response_text' => new external_value(PARAM_TEXT, 'Response text', VALUE_DEFAULT, 'Ti&#7871;p theo'),
                        'sortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_DEFAULT, 0),
                    )),
                    'Step content items',
                    VALUE_DEFAULT,
                    array()
                ),
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
     * @param string $availabilityconditionsjson Availability conditions JSON
     * @param int $completion Completion tracking
     * @param int $completionunlocked Completion unlocked
     * @param int $completionview Completion view
     * @param int $completionexpected Completion expected
     * @param string $tags Tags
     * @param int $showdescription Show description
     * @return array
     * @throws moodle_exception
     */
    public static function create_stepbystep(
        $courseid,
        $name,
        $intro = '',
        $introformat = FORMAT_HTML,
        $section = 0,
        $visible = 1,
        $visibleoncoursepage = 1,
        $availabilityconditionsjson = '',
        $completion = 0,
        $completionunlocked = 1,
        $completionview = 0,
        $completionexpected = 0,
        $tags = '',
        $showdescription = 0,
        $contents = array()
    ) {
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
            'availabilityconditionsjson' => $availabilityconditionsjson,
            'completion' => $completion,
            'completionunlocked' => $completionunlocked,
            'completionview' => $completionview,
            'completionexpected' => $completionexpected,
            'tags' => $tags,
            'showdescription' => $showdescription,
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
        $data->availabilityconditionsjson = $params['availabilityconditionsjson'];
        $data->completion = $params['completion'];
        $data->completionunlocked = $params['completionunlocked'];
        $data->completionview = $params['completionview'];
        $data->completionexpected = $params['completionexpected'];
        $data->showdescription = $params['showdescription'];

        // Create the stepbystep instance using add_moduleinfo
        $cm = add_moduleinfo($data, $course);

        if (!$cm) {
            throw new moodle_exception('errorcreatingstepbystep', 'mod_stepbystep');
        }

        // Get the created instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $cm->instance), '*', MUST_EXIST);

        // Save content steps if provided (mirrors stepbystep_add_instance logic)
        $contents_saved = 0;
        if (!empty($params['contents'])) {
            foreach ($params['contents'] as $i => $content_data) {
                // Build content array for validation (same structure as stepbystep_add_instance)
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

                // Validate step content (same function used in stepbystep_add_instance)
                if (!stepbystep_validate_step_content($content)) {
                    continue;
                }

                $step = new stdClass();
                $step->stepbystep_id = $stepbystep->id;
                $step->type = $content['type'];
                $step->main_title = $content['main_title'];
                $step->sub_heading = $content['sub_heading'];
                // Handle content_paragraphs: may be plain string from API
                $step->content_paragraphs = $content['content_paragraphs'];
                $step->term = $content['term'];
                $step->phonetic = $content['phonetic'];
                $step->definition = $content['definition'];
                $step->example = $content['example'];
                $step->audio_file = ''; // not handled via API
                $step->response_text = $content['response_text'];
                $step->storage_path = '';
                $step->sortorder = isset($content_data['sortorder']) ? $content_data['sortorder'] : $i;
                $step->timecreated = time();

                $DB->insert_record('stepbystep_content', $step);
                $contents_saved++;
            }
        }

        return array(
            'id' => $stepbystep->id,
            'course' => $stepbystep->course,
            'name' => $stepbystep->name,
            'intro' => $stepbystep->intro,
            'introformat' => $stepbystep->introformat,
            'timecreated' => $stepbystep->timecreated,
            'timemodified' => $stepbystep->timemodified,
            'cmid' => $cm->coursemodule,
            'coursemodule' => $cm->coursemodule,
            'contents_count' => $contents_saved,
        );
    }

    /**
     * Returns description of method result value for create_stepbystep
     * @return external_single_structure
     */
    public static function create_stepbystep_returns()
    {
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
                'coursemodule' => new external_value(PARAM_INT, 'Course module ID'),
                'contents_count' => new external_value(PARAM_INT, 'Number of step contents saved'),
            )
        );
    }

    /**
     * Returns description of method parameters for get_stepbystep
     * @return external_function_parameters
     */
    public static function get_stepbystep_parameters()
    {
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
    public static function get_stepbystep($id)
    {
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
    public static function get_stepbystep_returns()
    {
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

    // =========================================================
    // get_stepbystep_with_contents – trả về activity + steps
    // =========================================================

    /**
     * Parameters for get_stepbystep_with_contents
     */
    public static function get_stepbystep_with_contents_parameters()
    {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID of the stepbystep activity'),
        ]);
    }

    /**
     * Get stepbystep activity info + all content steps from stepbystep_content table
     *
     * @param int $cmid Course module ID
     * @return array
     */
    public static function get_stepbystep_with_contents($cmid)
    {
        global $DB;

        $params = self::validate_parameters(
            self::get_stepbystep_with_contents_parameters(),
            ['cmid' => $cmid]
        );

        $cm = get_coursemodule_from_id('stepbystep', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/stepbystep:view', $context);

        $stepbystep = $DB->get_record('stepbystep', ['id' => $cm->instance], '*', MUST_EXIST);

        // Lấy tất cả content steps, sắp xếp theo sortorder
        $contentRows = $DB->get_records(
            'stepbystep_content',
            ['stepbystep_id' => $stepbystep->id],
            'sortorder ASC'
        );

        $contents = [];
        foreach ($contentRows as $row) {
            $contents[] = [
                'id' => (int) $row->id,
                'type' => $row->type ?? 'text',
                'main_title' => $row->main_title ?? '',
                'sub_heading' => $row->sub_heading ?? '',
                'content_paragraphs' => $row->content_paragraphs ?? '',
                'term' => $row->term ?? '',
                'phonetic' => $row->phonetic ?? '',
                'definition' => $row->definition ?? '',
                'example' => $row->example ?? '',
                'response_text' => $row->response_text ?? '',
                'sortorder' => (int) $row->sortorder,
            ];
        }

        return [
            'id' => (int) $stepbystep->id,
            'cmid' => (int) $cm->id,
            'course' => (int) $stepbystep->course,
            'name' => $stepbystep->name,
            'timecreated' => (int) $stepbystep->timecreated,
            'timemodified' => (int) $stepbystep->timemodified,
            'contents' => $contents,
        ];
    }

    /**
     * Returns structure for get_stepbystep_with_contents
     */
    public static function get_stepbystep_with_contents_returns()
    {
        $contentStruct = new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Content record ID'),
            'type' => new external_value(PARAM_TEXT, 'Content type (text|vocabulary)'),
            'main_title' => new external_value(PARAM_RAW, 'Main title', VALUE_DEFAULT, ''),
            'sub_heading' => new external_value(PARAM_RAW, 'Sub heading', VALUE_DEFAULT, ''),
            'content_paragraphs' => new external_value(PARAM_RAW, 'Content paragraphs', VALUE_DEFAULT, ''),
            'term' => new external_value(PARAM_TEXT, 'Vocabulary term', VALUE_DEFAULT, ''),
            'phonetic' => new external_value(PARAM_TEXT, 'Phonetic', VALUE_DEFAULT, ''),
            'definition' => new external_value(PARAM_RAW, 'Definition', VALUE_DEFAULT, ''),
            'example' => new external_value(PARAM_RAW, 'Example', VALUE_DEFAULT, ''),
            'response_text' => new external_value(PARAM_TEXT, 'Response text', VALUE_DEFAULT, ''),
            'sortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_DEFAULT, 0),
        ]);

        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Stepbystep instance ID'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'course' => new external_value(PARAM_INT, 'Course ID'),
            'name' => new external_value(PARAM_TEXT, 'Activity name'),
            'timecreated' => new external_value(PARAM_INT, 'Time created'),
            'timemodified' => new external_value(PARAM_INT, 'Time modified'),
            'contents' => new external_multiple_structure($contentStruct, 'Content steps'),
        ]);
    }

    /**
     * Returns description of method parameters for update_stepbystep
     */
    public static function update_stepbystep_parameters()
    {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID của stepbystep cần cập nhật'),
            'fields' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_TEXT, 'Tên stepbystep', VALUE_OPTIONAL),
                    'intro' => new external_value(PARAM_RAW, 'Mô tả stepbystep', VALUE_OPTIONAL),
                    'introformat' => new external_value(PARAM_INT, 'Định dạng mô tả', VALUE_OPTIONAL),
                    'section' => new external_value(PARAM_INT, 'Section number', VALUE_OPTIONAL),
                    'visible' => new external_value(PARAM_INT, 'Hiển thị', VALUE_OPTIONAL),
                    'visibleoncoursepage' => new external_value(PARAM_INT, 'Visible on course page', VALUE_OPTIONAL),
                    'completion' => new external_value(PARAM_INT, 'Completion tracking', VALUE_OPTIONAL),
                    'completionview' => new external_value(PARAM_INT, 'Completion view', VALUE_OPTIONAL),
                    'completionexpected' => new external_value(PARAM_INT, 'Completion expected', VALUE_OPTIONAL),
                    'showdescription' => new external_value(PARAM_INT, 'Show Description', VALUE_OPTIONAL),
                    'availability' => new external_single_structure([
                        'completioncmid' => new external_multiple_structure(
                            new external_value(PARAM_INT, 'Course module ID for completion'),
                            'Completion course module IDs'
                        ),
                        'timeopen' => new external_value(PARAM_INT, 'Open time', VALUE_OPTIONAL),
                        'timeclose' => new external_value(PARAM_INT, 'Close time', VALUE_OPTIONAL),
                        'gradeitemid' => new external_value(PARAM_INT, 'Grade item ID', VALUE_OPTIONAL),
                        'min' => new external_value(PARAM_FLOAT, 'Minimum grade', VALUE_OPTIONAL),
                        'max' => new external_value(PARAM_FLOAT, 'Maximum grade', VALUE_OPTIONAL),
                    ], 'Availability conditions', VALUE_OPTIONAL),
                    'contents' => new external_multiple_structure(
                        new external_single_structure(array(
                            'type' => new external_value(PARAM_TEXT, 'Content type (text or vocabulary)'),
                            'main_title' => new external_value(PARAM_RAW, 'Main title', VALUE_DEFAULT, ''),
                            'sub_heading' => new external_value(PARAM_RAW, 'Sub heading', VALUE_DEFAULT, ''),
                            'content_paragraphs' => new external_value(PARAM_RAW, 'Content paragraphs (HTML)', VALUE_DEFAULT, ''),
                            'term' => new external_value(PARAM_TEXT, 'Term (for vocabulary type)', VALUE_DEFAULT, ''),
                            'phonetic' => new external_value(PARAM_TEXT, 'Phonetic (for vocabulary type)', VALUE_DEFAULT, ''),
                            'definition' => new external_value(PARAM_RAW, 'Definition (for vocabulary type)', VALUE_DEFAULT, ''),
                            'example' => new external_value(PARAM_RAW, 'Example (for vocabulary type)', VALUE_DEFAULT, ''),
                            'response_text' => new external_value(PARAM_TEXT, 'Response text', VALUE_DEFAULT, 'Ti&#7871;p theo'),
                            'sortorder' => new external_value(PARAM_INT, 'Sort order', VALUE_DEFAULT, 0),
                        )),
                        'Step content items (replaces all existing steps)',
                        VALUE_OPTIONAL
                    ),
                ]),
                'Fields to update'
            ),
        ]);
    }

    /**
     * Update an existing stepbystep instance
     * @param int $cmid Course module ID
     * @param array $fields Fields to update
     * @return array
     * @throws moodle_exception
     */
    public static function update_stepbystep($cmid, $fields)
    {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::update_stepbystep_parameters(), [
            'cmid' => $cmid,
            'fields' => $fields
        ]);

        // Get course module
        $cm = get_coursemodule_from_id('stepbystep', $params['cmid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $context = context_course::instance($course->id);

        // Check capabilities
        require_capability('mod/stepbystep:addinstance', $context);

        // Get the stepbystep instance
        $stepbystep = $DB->get_record('stepbystep', array('id' => $cm->instance), '*', MUST_EXIST);

        // Update fields provided
        foreach ($params['fields'] as $field_data) {
            foreach ($field_data as $field => $value) {
                if (isset($value) && $field !== 'availability' && property_exists($stepbystep, $field)) {
                    $stepbystep->{$field} = $value;
                }
            }
        }
        $stepbystep->timemodified = time();

        // Update stepbystep record
        $result = $DB->update_record('stepbystep', $stepbystep);

        if (!$result) {
            throw new moodle_exception('errorupdatingstepbystep', 'mod_stepbystep');
        }

        // Get course_modules record for availability update
        $cm_record = $DB->get_record('course_modules', ['id' => $cmid], '*', MUST_EXIST);

        // Handle availability if provided
        if (!empty($params['fields'][0]) && !empty($params['fields'][0]['availability'])) {
            $availability_params = $params['fields'][0]['availability'];
            $completioncmids = $availability_params['completioncmid'] ?? [];

            if (!is_array($completioncmids)) {
                $completioncmids = [$completioncmids];
            }

            $availability_json = self::generate_availability_conditions(
                $availability_params['timeopen'] ?? null,
                $availability_params['timeclose'] ?? null,
                $availability_params['gradeitemid'] ?? null,
                $availability_params['min'] ?? null,
                $availability_params['max'] ?? null,
                $completioncmids
            );

            $cm_record->availability = $availability_json;
        } else {
            $cm_record->availability = '';
        }

        // Update course module record
        $DB->update_record('course_modules', $cm_record);

        // Handle section and visible
        $cm1 = $DB->get_record('course_modules', ['id' => $cmid], '*', MUST_EXIST);

        if (!empty($params['fields'][0]) && isset($params['fields'][0]['section'])) {
            $section = $DB->get_record('course_sections', array('course' => $cm1->course, 'section' => $params['fields'][0]['section']));

            if ($section && $section->id != $cm1->section) {
                self::move_activity_to_section($cm1->course, $cmid, $params['fields'][0]['section']);
            }
        }

        if (!empty($params['fields'][0]) && isset($params['fields'][0]['visible'])) {
            $cm1->visible = $params['fields'][0]['visible'];
        }

        $completion = 0;
        $completionview = 0;
        $completionexpected = 0;
        if (!empty($params['fields'][0]) && !empty($params['fields'][0]['completion'])) {
            if ($params['fields'][0]['completion'] == 1) {
                $completion = $params['fields'][0]['completion'];
                $completionexpected = $params['fields'][0]['completionexpected'] ?? 0;
            }

            if ($params['fields'][0]['completion'] == 2) {
                $completion = $params['fields'][0]['completion'];
                $completionview = $params['fields'][0]['completionview'] ?? 0;
                $completionexpected = $params['fields'][0]['completionexpected'] ?? 0;
            }
        }
        $cm1->completion = $completion;
        $cm1->completionview = $completionview;
        $cm1->completionexpected = $completionexpected;

        $cm1->showdescription = (!empty($params['fields'][0]) && isset($params['fields'][0]['showdescription'])) ? $params['fields'][0]['showdescription'] : 0;

        $DB->update_record('course_modules', $cm1);

        rebuild_course_cache($cm1->course, true);

        // Handle contents update (mirrors stepbystep_update_instance logic)
        // Delete all existing content first, then re-insert if contents provided
        $contents_saved = 0;
        if (!empty($params['fields'][0]) && isset($params['fields'][0]['contents'])) {
            // Always delete existing content when contents key is present
            $DB->delete_records('stepbystep_content', array('stepbystep_id' => $stepbystep->id));

            foreach ($params['fields'][0]['contents'] as $i => $content_data) {
                // Build content array for validation
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

                // Validate step content (same function used in stepbystep_update_instance)
                if (!stepbystep_validate_step_content($content)) {
                    continue;
                }

                $step = new stdClass();
                $step->stepbystep_id = $stepbystep->id;
                $step->type = $content['type'];
                $step->main_title = $content['main_title'];
                $step->sub_heading = $content['sub_heading'];
                $step->content_paragraphs = $content['content_paragraphs'];
                $step->term = $content['term'];
                $step->phonetic = $content['phonetic'];
                $step->definition = $content['definition'];
                $step->example = $content['example'];
                $step->audio_file = ''; // not handled via API
                $step->response_text = $content['response_text'];
                $step->storage_path = '';
                $step->sortorder = isset($content_data['sortorder']) ? $content_data['sortorder'] : $i;
                $step->timecreated = time();

                $DB->insert_record('stepbystep_content', $step);
                $contents_saved++;
            }
        }

        return [
            'status' => 'success',
            'message' => 'Step by step updated successfully',
            'stepbystepid' => $stepbystep->id,
            'cmid' => $cmid,
            'contents_count' => $contents_saved,
        ];
    }

    /**
     * Returns description of method result value for update_stepbystep
     * @return external_single_structure
     */
    public static function update_stepbystep_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Kết quả của thao tác'),
            'message' => new external_value(PARAM_TEXT, 'Thông báo kết quả'),
            'stepbystepid' => new external_value(PARAM_INT, 'ID của stepbystep đã cập nhật'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID của stepbystep'),
            'contents_count' => new external_value(PARAM_INT, 'Số step content đã lưu'),
        ]);
    }

    /**
     * Returns description of method parameters for delete_stepbystep
     * @return external_function_parameters
     */
    public static function delete_stepbystep_parameters()
    {
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
    public static function delete_stepbystep($id)
    {
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
    public static function delete_stepbystep_returns()
    {
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
    public static function list_stepbystep_parameters()
    {
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
    public static function list_stepbystep($courseid)
    {
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
    public static function list_stepbystep_returns()
    {
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
    public static function create_content_parameters()
    {
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
    public static function create_content(
        $stepbystep_id,
        $type,
        $main_title = '',
        $sub_heading = '',
        $content_paragraphs = '',
        $term = '',
        $phonetic = '',
        $definition = '',
        $example = '',
        $response_text = 'Tiếp theo',
        $sortorder = 0
    ) {
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
    public static function create_content_returns()
    {
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
    public static function get_content_parameters()
    {
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
    public static function get_content($id)
    {
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
    public static function get_content_returns()
    {
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
    public static function update_content_parameters()
    {
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
    public static function update_content(
        $id,
        $type = null,
        $main_title = null,
        $sub_heading = null,
        $content_paragraphs = null,
        $term = null,
        $phonetic = null,
        $definition = null,
        $example = null,
        $response_text = null,
        $sortorder = null
    ) {
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
    public static function update_content_returns()
    {
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
    public static function delete_content_parameters()
    {
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
    public static function delete_content($id)
    {
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
    public static function delete_content_returns()
    {
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
    public static function list_contents_parameters()
    {
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
    public static function list_contents($stepbystep_id)
    {
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
    public static function list_contents_returns()
    {
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
    public static function create_stepbystep_with_contents_parameters()
    {
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
    public static function create_stepbystep_with_contents(
        $courseid,
        $name,
        $intro = '',
        $introformat = FORMAT_HTML,
        $section = 0,
        $visible = 1,
        $visibleoncoursepage = 1,
        $contents = array()
    ) {
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
    public static function create_stepbystep_with_contents_returns()
    {
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

    /**
     * Generate availability conditions for a Moodle activity.
     *
     * @param int|null $timeopen Timestamp when the activity is available.
     * @param int|null $timeclose Timestamp when the activity is no longer available.
     * @param int|null $gradeitemid Grade item ID for grade condition.
     * @param float|null $min Minimum grade required.
     * @param float|null $max Maximum grade allowed.
     * @param array|null $completioncmids Completion conditions based on activity IDs.
     * @return string JSON string of availability conditions.
     */
    private static function generate_availability_conditions($timeopen = null, $timeclose = null, $gradeitemid = null, $min = null, $max = null, $completioncmids = null)
    {
        $conditions = [];
        $showc = [];

        // Điều kiện Restrict Access theo thời gian
        if ($timeopen !== null && $timeclose !== null) {
            $conditions[] = ["type" => "date", "d" => "<", "t" => $timeopen];
            $showc[] = false;
            $conditions[] = ["type" => "date", "d" => ">=", "t" => $timeclose];
            $showc[] = false;
        } elseif ($timeopen !== null) {
            $conditions[] = ["type" => "date", "d" => "<", "t" => $timeopen];
            $showc[] = false;
        } elseif ($timeclose !== null) {
            $conditions[] = ["type" => "date", "d" => ">=", "t" => $timeclose];
            $showc[] = false;
        }

        // Điều kiện Restrict Access theo điểm số
        if ($gradeitemid !== null && ($min !== null || $max !== null)) {
            if ($min !== null) {
                $conditions[] = ["type" => "grade", "id" => $gradeitemid, "min" => $min];
                $showc[] = true;
            }
            if ($max !== null) {
                $conditions[] = ["type" => "grade", "id" => $gradeitemid, "max" => $max];
                $showc[] = true;
            }
        }

        // Điều kiện Restrict Access theo completion
        if (!empty($completioncmids)) {
            foreach ($completioncmids as $completioncmid) {
                $conditions[] = ["type" => "completion", "cm" => $completioncmid, "e" => 1];
                $showc[] = true;
            }
        }

        $availability = ["op" => "&", "c" => $conditions, "showc" => $showc];
        return json_encode($availability);
    }

    /**
     * Move an activity to a different section.
     *
     * @param int $courseid The ID of the course.
     * @param int $moduleid The ID of the module (activity).
     * @param int $newsection The section number to move the activity to.
     * @return null
     */
    private static function move_activity_to_section($courseid, $moduleid, $newsection)
    {
        global $DB;

        $module = $DB->get_record('course_modules', array('id' => $moduleid));
        if (!$module) {
            return null;
        }

        $section = $DB->get_record('course_sections', ['course' => $courseid, 'section' => $newsection]);
        if (!$section) {
            return null;
        }

        if ($module->section == $section->id) {
            return null;
        }

        moveto_module($module, $section);
        rebuild_course_cache($courseid, true);

        return null;
    }
}
