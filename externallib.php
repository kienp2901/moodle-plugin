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
 * External API for Reading Flow module
 *
 * @package mod_readingflow
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/readingflow/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');


use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use moodle_exception;


/**
 * External API class for Reading Flow module
 */
class mod_readingflow_external extends external_api {

    /**
     * Returns description of method parameters for create_readingflow
     *
     * @return external_function_parameters
     */
    public static function create_readingflow_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Reading flow name'),
                'intro' => new external_value(PARAM_RAW, 'Reading flow description', VALUE_DEFAULT, ''),
                'introformat' => new external_value(PARAM_INT, 'Intro format', VALUE_DEFAULT, FORMAT_HTML),
                'content' => new external_value(PARAM_RAW, 'Reading flow content', VALUE_DEFAULT, ''),
                'contentformat' => new external_value(PARAM_INT, 'Content format', VALUE_DEFAULT, FORMAT_HTML),
                'section' => new external_value(PARAM_INT, 'Course section', VALUE_DEFAULT, 0),
                'visible' => new external_value(PARAM_INT, 'Visible', VALUE_DEFAULT, 1),
                'visibleoncoursepage' => new external_value(PARAM_INT, 'Visible on course page', VALUE_DEFAULT, 1),
                'availabilityconditionsjson' => new external_value(PARAM_RAW, 'Availability conditions JSON', VALUE_DEFAULT, ''),
                'completionunlocked' => new external_value(PARAM_INT, 'Completion unlocked', VALUE_DEFAULT, 1),
                'completionview' => new external_value(PARAM_INT, 'Completion view', VALUE_DEFAULT, 0),
                'completionexpected' => new external_value(PARAM_INT, 'Completion expected', VALUE_DEFAULT, 0),
                'tags' => new external_value(PARAM_RAW, 'Tags', VALUE_DEFAULT, ''),
                'showdescription' => new external_value(PARAM_INT, 'Show description', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Create a new reading flow instance
     *
     * @param int $courseid Course ID
     * @param string $name Reading flow name
     * @param string $intro Reading flow description
     * @param int $introformat Intro format
     * @param string $content Reading flow content
     * @param int $contentformat Content format
     * @param int $section Course section
     * @param int $visible Visible
     * @param int $visibleoncoursepage Visible on course page
     * @param string $availabilityconditionsjson Availability conditions JSON
     * @param int $completionunlocked Completion unlocked
     * @param int $completionview Completion view
     * @param int $completionexpected Completion expected
     * @param string $tags Tags
     * @param int $showdescription Show description
     * @return array
     * @throws moodle_exception
     */
    public static function create_readingflow($courseid, $name, $intro = '', $introformat = FORMAT_HTML, 
                                         $content = '', $contentformat = FORMAT_HTML,
                                         $section = 0, $visible = 1, $visibleoncoursepage = 1,
                                         $availabilityconditionsjson = '', $completionunlocked = 1,
                                         $completionview = 0, $completionexpected = 0, $tags = '',
                                         $showdescription = 0) {
        global $DB, $CFG;

        // Validate parameters
        $params = self::validate_parameters(self::create_readingflow_parameters(), array(
            'courseid' => $courseid,
            'name' => $name,
            'intro' => $intro,
            'introformat' => $introformat,
            'content' => $content,
            'contentformat' => $contentformat,
            'section' => $section,
            'visible' => $visible,
            'visibleoncoursepage' => $visibleoncoursepage,
            'availabilityconditionsjson' => $availabilityconditionsjson,
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
        require_capability('mod/readingflow:addinstance', $context);

        $module = $DB->get_record('modules', ['name' => 'readingflow'], '*', MUST_EXIST);
        $moduleid = $module->id;

        // Prepare data for module creation
        $data = new stdClass();
        $data->modulename = 'readingflow';
        $data->module = $moduleid;
        $data->course = $course->id;
        $data->name = $params['name'];
        $data->intro = $params['intro'];
        $data->introformat = $params['introformat'];
        $data->content = $params['content'];
        $data->contentformat = $params['contentformat'];
        $data->section = $params['section'];
        $data->visible = $params['visible'];
        $data->visibleoncoursepage = $params['visibleoncoursepage'];
        $data->availabilityconditionsjson = $params['availabilityconditionsjson'];
        $data->completionunlocked = $params['completionunlocked'];
        $data->completionview = $params['completionview'];
        $data->completionexpected = $params['completionexpected'];
        $data->showdescription = $params['showdescription'];

        // Create the readingflow instance using add_moduleinfo
        $cm = add_moduleinfo($data, $course);

        if (!$cm) {
            throw new moodle_exception('errorcreatingreadingflow', 'mod_readingflow');
        }

        // Get the created instance
        $readingflow = $DB->get_record('readingflow', array('id' => $cm->instance), '*', MUST_EXIST);

        return array(
            'id' => $readingflow->id,
            'course' => $readingflow->course,
            'name' => $readingflow->name,
            'intro' => $readingflow->intro,
            'introformat' => $readingflow->introformat,
            'content' => $readingflow->content,
            'contentformat' => $readingflow->contentformat,
            'timecreated' => $readingflow->timecreated,
            'timemodified' => $readingflow->timemodified,
            'cmid' => $cm->coursemodule,
            'coursemodule' => $cm->coursemodule,
        );
    }

    /**
     * Returns description of method result value for create_readingflow
     *
     * @return external_single_structure
     */
    public static function create_readingflow_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Reading flow instance ID'),
                'course' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Reading flow name'),
                'intro' => new external_value(PARAM_RAW, 'Reading flow description'),
                'introformat' => new external_value(PARAM_INT, 'Intro format'),
                'content' => new external_value(PARAM_RAW, 'Reading flow content'),
                'contentformat' => new external_value(PARAM_INT, 'Content format'),
                'timecreated' => new external_value(PARAM_INT, 'Time created'),
                'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                'coursemodule' => new external_value(PARAM_INT, 'Course module ID'),
            )
        );
    }

    /**
     * Returns description of method parameters for update_readingflow
     *
     * @return external_function_parameters
     */
    public static function update_readingflow_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID của readingflow cần cập nhật'),
            'fields' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_TEXT, 'Tên readingflow', VALUE_OPTIONAL),
                    'intro' => new external_value(PARAM_RAW, 'Mô tả readingflow', VALUE_OPTIONAL),
                    'introformat' => new external_value(PARAM_INT, 'Định dạng mô tả', VALUE_OPTIONAL),
                    'content' => new external_value(PARAM_RAW, 'Nội dung readingflow', VALUE_OPTIONAL),
                    'contentformat' => new external_value(PARAM_INT, 'Định dạng nội dung', VALUE_OPTIONAL),
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
     * Update an existing reading flow instance
     *
     * @param int $cmid Course module ID
     * @param array $fields Fields to update
     * @return array
     * @throws moodle_exception
     */
    public static function update_readingflow($cmid, $fields) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::update_readingflow_parameters(), [
            'cmid' => $cmid,
            'fields' => $fields
        ]);

        // Get course module
        $cm = get_coursemodule_from_id('readingflow', $params['cmid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $context = context_course::instance($course->id);

        // Check capabilities
        require_capability('mod/readingflow:addinstance', $context);

        // Get the readingflow instance
        $readingflow = $DB->get_record('readingflow', array('id' => $cm->instance), '*', MUST_EXIST);

        // Update fields provided
        foreach ($params['fields'] as $field_data) {
            foreach ($field_data as $field => $value) {
                if (isset($value) && $field !== 'availability' && property_exists($readingflow, $field)) {
                    $readingflow->{$field} = $value;
                }
            }
        }

        // Update readingflow record
        $result = $DB->update_record('readingflow', $readingflow);
        
        if (!$result) {
            throw new moodle_exception('errorupdatingreadingflow', 'mod_readingflow');
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
            if($params['fields'][0]['completion'] == 1){
                $completion = $params['fields'][0]['completion'];
                $completionexpected = $params['fields'][0]['completionexpected'] ?? 0;
            }

            if($params['fields'][0]['completion'] == 2){
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
            'message' => 'Reading flow updated successfully',
            'readingflowid' => $readingflow->id,
            'cmid' => $cmid
        ];
    }

    /**
     * Returns description of method result value for update_readingflow
     *
     * @return external_single_structure
     */
    public static function update_readingflow_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Kết quả của thao tác'),
            'message' => new external_value(PARAM_TEXT, 'Thông báo kết quả'),
            'readingflowid' => new external_value(PARAM_INT, 'ID của readingflow đã cập nhật'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID của readingflow')
        ]);
    }

    /**
     * Returns description of method parameters for get_readingflow
     *
     * @return external_function_parameters
     */
    public static function get_readingflow_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Reading flow instance ID'),
            )
        );
    }

    /**
     * Get reading flow instance details
     *
     * @param int $id Reading flow instance ID
     * @return array
     * @throws moodle_exception
     */
    public static function get_readingflow($id) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::get_readingflow_parameters(), array(
            'id' => $id,
        ));

        // Get the readingflow instance
        $readingflow = $DB->get_record('readingflow', array('id' => $params['id']), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $readingflow->course), '*', MUST_EXIST);
        $context = \context_course::instance($course->id);

        // Check capabilities
        require_capability('mod/readingflow:view', $context);

        // Get course module
        $cm = get_coursemodule_from_instance('readingflow', $readingflow->id, $course->id, false, MUST_EXIST);

        return array(
            'id' => $readingflow->id,
            'course' => $readingflow->course,
            'name' => $readingflow->name,
            'intro' => $readingflow->intro,
            'introformat' => $readingflow->introformat,
            'content' => $readingflow->content,
            'contentformat' => $readingflow->contentformat,
            'timecreated' => $readingflow->timecreated,
            'timemodified' => $readingflow->timemodified,
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
     * Returns description of method result value for get_readingflow
     *
     * @return external_single_structure
     */
    public static function get_readingflow_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Reading flow instance ID'),
                'course' => new external_value(PARAM_INT, 'Course ID'),
                'name' => new external_value(PARAM_TEXT, 'Reading flow name'),
                'intro' => new external_value(PARAM_RAW, 'Reading flow description'),
                'introformat' => new external_value(PARAM_INT, 'Intro format'),
                'content' => new external_value(PARAM_RAW, 'Reading flow content'),
                'contentformat' => new external_value(PARAM_INT, 'Content format'),
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
     * Returns description of method parameters for delete_readingflow
     *
     * @return external_function_parameters
     */
    public static function delete_readingflow_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'Reading flow instance ID'),
            )
        );
    }

    /**
     * Delete reading flow instance
     *
     * @param int $id Reading flow instance ID
     * @return array
     * @throws moodle_exception
     */
    public static function delete_readingflow($id) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::delete_readingflow_parameters(), array(
            'id' => $id,
        ));

        // Get the readingflow instance
        $readingflow = $DB->get_record('readingflow', array('id' => $params['id']), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $readingflow->course), '*', MUST_EXIST);
        $context = \context_course::instance($course->id);

        // Check capabilities
        require_capability('mod/readingflow:addinstance', $context);

        // Delete the readingflow instance
        $result = readingflow_delete_instance($params['id']);

        if (!$result) {
            throw new moodle_exception('errordeletingreadingflow', 'mod_readingflow');
        }

        return array(
            'success' => true,
            'message' => 'Reading flow deleted successfully'
        );
    }

    /**
     * Returns description of method result value for delete_readingflow
     *
     * @return external_single_structure
     */
    public static function delete_readingflow_returns() {
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
    private static function generate_availability_conditions($timeopen = null, $timeclose = null, $gradeitemid = null, $min = null, $max = null, $completioncmids = null) {
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

    /**
     * Parameter description for move_activity_to_section().
     *
     * @return external_function_parameters
     */
    public static function move_activity_to_section_parameters() {
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
    public static function move_activity_to_section($courseid, $moduleid, $newsection) {
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
    public static function move_activity_to_section_returns() {
        return null;
    }
}

