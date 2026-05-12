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
 * External API for Test Exam module
 *
 * @package mod_quickquiz
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/quickquiz/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');


use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use moodle_exception;


/**
 * External API class for Test Exam module
 */
class mod_quickquiz_external extends external_api
{

    /**
     * Returns description of method parameters for create_quickquiz
     *
     * @return external_function_parameters
     */
    public static function create_quickquiz_parameters()
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Test exam name'),
                'intro' => new external_value(PARAM_RAW, 'Test exam description', VALUE_DEFAULT, ''),
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
            )
        );
    }

    /**
     * Create a new test exam instance
     *
     * @param int $courseid Course ID
     * @param string $name Test exam name
     * @param string $intro Test exam description
     * @param int $introformat Intro format
     * @param int $section Course section
     * @param int $visible Visible
     * @param int $visibleoncoursepage Visible on course page
     * @param string $availabilityconditionsjson Availability conditions JSON
     * @param int $completionunlocked Completion unlocked
     * @param int $completion Completion tracking
     * @param int $completionview Completion view
     * @param int $completionexpected Completion expected
     * @param string $tags Tags
     * @param int $showdescription Show description
     * @return array
     * @throws moodle_exception
     */
    public static function create_quickquiz(
        $courseid,
        $name,
        $intro = '',
        $introformat = FORMAT_HTML,
        $section = 0,
        $visible = 1,
        $visibleoncoursepage = 1,
        $availabilityconditionsjson = '',
        $completionunlocked = 1,
        $completion = 0,
        $completionview = 0,
        $completionexpected = 0,
        $tags = '',
        $showdescription = 0
    ) {
        global $DB, $CFG;

        // Validate parameters
        $params = self::validate_parameters(self::create_quickquiz_parameters(), array(
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
        ));

        // Validate course
        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        if (!$course) {
            throw new moodle_exception('invalidcourseid', 'error');
        }
        $context = context_course::instance($course->id);

        // Check capabilities
        require_capability('mod/quickquiz:addinstance', $context);

        $module = $DB->get_record('modules', ['name' => 'quickquiz'], '*', MUST_EXIST);
        $moduleid = $module->id;

        // Prepare data for module creation
        $data = new stdClass();
        $data->modulename = 'quickquiz';
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

        // Create the quickquiz instance using add_moduleinfo
        $cm = add_moduleinfo($data, $course);

        if (!$cm) {
            throw new moodle_exception('errorcreatingquickquiz', 'mod_quickquiz');
        }

        // Get the created instance
        $quickquiz = $DB->get_record('quickquiz', array('id' => $cm->instance), '*', MUST_EXIST);

        return array(
            'id' => $quickquiz->id,
            'course' => $quickquiz->course,
            'name' => $quickquiz->name,
            'intro' => $quickquiz->intro,
            'introformat' => $quickquiz->introformat,
            'timecreated' => $quickquiz->timecreated,
            'timemodified' => $quickquiz->timemodified,
            'cmid' => $cm->coursemodule,
            'coursemodule' => $cm->coursemodule,
        );
    }

    /**
     * Returns description of method result value for create_quickquiz
     *
     * @return external_single_structure
     */
    public static function create_quickquiz_returns()
    {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Test exam instance ID'),
                'course' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Test exam name'),
                'intro' => new external_value(PARAM_RAW, 'Test exam description'),
                'introformat' => new external_value(PARAM_INT, 'Intro format'),
                'timecreated' => new external_value(PARAM_INT, 'Time created'),
                'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                'coursemodule' => new external_value(PARAM_INT, 'Course module ID'),
            )
        );
    }

    /**
     * Returns description of method parameters for update_quickquiz
     *
     * @return external_function_parameters
     */
    public static function update_quickquiz_parameters()
    {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID của quickquiz cần cập nhật'),
            'fields' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_TEXT, 'Tên quickquiz', VALUE_OPTIONAL),
                    'intro' => new external_value(PARAM_RAW, 'Mô tả quickquiz', VALUE_OPTIONAL),
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
                ]),
                'Fields to update'
            ),
        ]);
    }

    /**
     * Update an existing test exam instance
     *
     * @param int $cmid Course module ID
     * @param array $fields Fields to update
     * @return array
     * @throws moodle_exception
     */
    public static function update_quickquiz($cmid, $fields)
    {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::update_quickquiz_parameters(), [
            'cmid' => $cmid,
            'fields' => $fields
        ]);

        // Get course module
        $cm = get_coursemodule_from_id('quickquiz', $params['cmid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $context = context_course::instance($course->id);

        // Check capabilities
        require_capability('mod/quickquiz:addinstance', $context);

        // Get the quickquiz instance
        $quickquiz = $DB->get_record('quickquiz', array('id' => $cm->instance), '*', MUST_EXIST);

        // Update fields provided
        foreach ($params['fields'] as $field_data) {
            foreach ($field_data as $field => $value) {
                if (isset($value) && $field !== 'availability' && property_exists($quickquiz, $field)) {
                    $quickquiz->{$field} = $value;
                }
            }
        }

        // Update quickquiz record
        $result = $DB->update_record('quickquiz', $quickquiz);

        if (!$result) {
            throw new moodle_exception('errorupdatingquickquiz', 'mod_quickquiz');
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

            // Update availability in course_modules table
            $cm_record->availability = $availability_json;
        } else {
            $cm_record->availability = '';
        }

        // Update course module record
        $DB->update_record('course_modules', $cm_record);

        // Xử lý section và visible nếu được truyền
        $cm1 = $DB->get_record('course_modules', ['id' => $cmid], '*', MUST_EXIST);

        if (!empty($params['fields'][0]) && isset($params['fields'][0]['section'])) {
            $section = $DB->get_record('course_sections', array('course' => $cm1->course, 'section' => $params['fields'][0]['section']));

            if ($section && $section->id != $cm1->section) {
                // Cập nhật section mới
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

        return [
            'status' => 'success',
            'message' => 'Test exam updated successfully',
            'quickquizid' => $quickquiz->id,
            'cmid' => $cmid
        ];
    }

    /**
     * Returns description of method result value for update_quickquiz
     *
     * @return external_single_structure
     */
    public static function update_quickquiz_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Kết quả của thao tác'),
            'message' => new external_value(PARAM_TEXT, 'Thông báo kết quả'),
            'quickquizid' => new external_value(PARAM_INT, 'ID của quickquiz đã cập nhật'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID của quickquiz')
        ]);
    }

    /**
     * Returns description of method parameters for get_quickquiz
     *
     * @return external_function_parameters
     */
    public static function get_quickquiz_parameters()
    {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Test exam instance ID'),
            )
        );
    }

    /**
     * Get test exam instance details
     *
     * @param int $id Test exam instance ID
     * @return array
     * @throws moodle_exception
     */
    public static function get_quickquiz($id)
    {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::get_quickquiz_parameters(), array(
            'id' => $id,
        ));

        // Get the quickquiz instance
        $quickquiz = $DB->get_record('quickquiz', array('id' => $params['id']), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $quickquiz->course), '*', MUST_EXIST);
        $context = \context_course::instance($course->id);

        // Check capabilities
        require_capability('mod/quickquiz:view', $context);

        // Get course module
        $cm = get_coursemodule_from_instance('quickquiz', $quickquiz->id, $course->id, false, MUST_EXIST);

        return array(
            'id' => $quickquiz->id,
            'course' => $quickquiz->course,
            'name' => $quickquiz->name,
            'intro' => $quickquiz->intro,
            'introformat' => $quickquiz->introformat,
            'timecreated' => $quickquiz->timecreated,
            'timemodified' => $quickquiz->timemodified,
            'cmid' => $cm->id,
            'coursemodule' => $cm->id,
            'section' => $cm->section,
            'visible' => $cm->visible,
            'visibleoncoursepage' => $cm->visibleoncoursepage,
            'availabilityconditionsjson' => $cm->availabilityconditionsjson,
            'completionview' => $cm->completionview,
            'completionexpected' => $cm->completionexpected,
            'showdescription' => $cm->showdescription,
        );
    }

    /**
     * Returns description of method result value for get_quickquiz
     *
     * @return external_single_structure
     */
    public static function get_quickquiz_returns()
    {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Test exam instance ID'),
                'course' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Test exam name'),
                'intro' => new external_value(PARAM_RAW, 'Test exam description'),
                'introformat' => new external_value(PARAM_INT, 'Intro format'),
                'timecreated' => new external_value(PARAM_INT, 'Time created'),
                'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                'coursemodule' => new external_value(PARAM_INT, 'Course module ID'),
                'section' => new external_value(PARAM_INT, 'Course section'),
                'visible' => new external_value(PARAM_INT, 'Visible'),
                'visibleoncoursepage' => new external_value(PARAM_INT, 'Visible on course page'),
                'availabilityconditionsjson' => new external_value(PARAM_RAW, 'Availability conditions JSON'),
                'completionview' => new external_value(PARAM_INT, 'Completion view'),
                'completionexpected' => new external_value(PARAM_INT, 'Completion expected'),
                'showdescription' => new external_value(PARAM_INT, 'Show description'),
            )
        );
    }

    /**
     * Returns description of method parameters for delete_quickquiz
     *
     * @return external_function_parameters
     */
    public static function delete_quickquiz_parameters()
    {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Test exam instance ID'),
            )
        );
    }

    /**
     * Delete test exam instance
     *
     * @param int $id Test exam instance ID
     * @return array
     * @throws moodle_exception
     */
    public static function delete_quickquiz($id)
    {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::delete_quickquiz_parameters(), array(
            'id' => $id,
        ));

        // Get the quickquiz instance
        $quickquiz = $DB->get_record('quickquiz', array('id' => $params['id']), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $quickquiz->course), '*', MUST_EXIST);
        $context = \context_course::instance($course->id);

        // Check capabilities
        require_capability('mod/quickquiz:addinstance', $context);

        // Delete the quickquiz instance
        $result = quickquiz_delete_instance($params['id']);

        if (!$result) {
            throw new moodle_exception('errordeletingquickquiz', 'mod_quickquiz');
        }

        return array(
            'success' => true,
            'message' => 'Test exam deleted successfully'
        );
    }

    /**
     * Returns description of method result value for delete_quickquiz
     *
     * @return external_single_structure
     */
    public static function delete_quickquiz_returns()
    {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Success status'),
                'message' => new external_value(PARAM_TEXT, 'Result message'),
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
            $conditions[] = [
                "type" => "date",
                "d" => "<",
                "t" => $timeopen
            ];
            $showc[] = false;

            $conditions[] = [
                "type" => "date",
                "d" => ">=",
                "t" => $timeclose
            ];
            $showc[] = false;
        } elseif ($timeopen !== null) {
            $conditions[] = [
                "type" => "date",
                "d" => "<",
                "t" => $timeopen
            ];
            $showc[] = false;
        } elseif ($timeclose !== null) {
            $conditions[] = [
                "type" => "date",
                "d" => ">=",
                "t" => $timeclose
            ];
            $showc[] = false;
        }

        // Điều kiện Restrict Access theo điểm số
        if ($gradeitemid !== null && ($min !== null || $max !== null)) {
            if ($min !== null) {
                $conditions[] = [
                    "type" => "grade",
                    "id" => $gradeitemid,
                    "min" => $min
                ];
                $showc[] = true;
            }

            if ($max !== null) {
                $conditions[] = [
                    "type" => "grade",
                    "id" => $gradeitemid,
                    "max" => $max
                ];
                $showc[] = true;
            }
        }

        // Điều kiện Restrict Access theo completion
        if (!empty($completioncmids)) {
            foreach ($completioncmids as $completioncmid) {
                $conditions[] = [
                    "type" => "completion",
                    "cm" => $completioncmid,
                    "e" => 1
                ];
                $showc[] = true;
            }
        }

        // Tạo JSON availability
        $availability = [
            "op" => "&", // Điều kiện 'và' (AND)
            "c" => $conditions,
            "showc" => $showc
        ];

        return json_encode($availability);
    }

    // Function set for move_activity() *********************************************************************************************.

    /**
     * Parameter description for move_activity_to_section().
     *
     * @return external_function_parameters
     */
    public static function move_activity_to_section_parameters()
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'moduleid' => new external_value(PARAM_INT, 'id of the module (activity)'),
                'newsection' => new external_value(PARAM_INT, 'id of the section to move the activity to')
            )
        );
    }

    /**
     * Move an activity to a different section.
     *
     * This function moves an activity from its current section to a new section.
     *
     * @param int $courseid The ID of the course.
     * @param int $moduleid The ID of the module (activity).
     * @param int $newsection The ID of the section to move the activity to.
     * @return null.
     */
    public static function move_activity_to_section($courseid, $moduleid, $newsection)
    {
        global $DB, $USER;

        // Validate parameters passed from web service.
        $params = self::validate_parameters(self::move_activity_to_section_parameters(), array(
            'courseid' => $courseid,
            'moduleid' => $moduleid,
            'newsection' => $newsection
        ));

        // Ensure course exists.
        if (!($course = $DB->get_record('course', array('id' => $params['courseid'])))) {
            throw new moodle_exception('invalidcourseid', 'local_custom_service', '', $courseid);
        }

        // Ensure module exists.
        if (!($module = $DB->get_record('course_modules', array('id' => $params['moduleid'])))) {
            throw new moodle_exception('invalidmoduleid', 'local_custom_service', '', $moduleid);
        }

        // Ensure module belongs to the course.
        if ($module->course != $course->id) {
            throw new moodle_exception('modulenotincourse', 'local_custom_service');
        }

        // Check if section exists.
        $section = $DB->get_record('course_sections', array('course' => $courseid, 'section' => $newsection));
        if (!$section) {
            throw new moodle_exception('invalidsection', 'local_custom_service');
        }

        // Ensure module is not already in the section.
        if ($module->section == $section->id) {
            throw new moodle_exception('modulerealread', 'local_custom_service');
        }

        // Get the section to which we want to move the activity
        $section = $DB->get_record('course_sections', ['course' => $courseid, 'section' => $newsection]);

        // Use the existing moveto_module function to move the module to the new section
        $modvisible = moveto_module($module, $section);

        // Rebuild course cache to reflect changes
        rebuild_course_cache($courseid, true);

        return null;
    }


    /**
     * Return description for move_activity_to_section().
     *
     * @return external_description
     */
    public static function move_activity_to_section_returns()
    {
        return null;
    }
}
