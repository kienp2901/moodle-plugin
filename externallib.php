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
 * External API for checkmatepdf module
 *
 * @package mod_checkmatepdf
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/checkmatepdf/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use moodle_exception;

/**
 * External API class for checkmatepdf module
 */
class mod_checkmatepdf_external extends external_api
{

    // =========================================================
    // create_checkmatepdf
    // =========================================================

    public static function create_checkmatepdf_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'name' => new external_value(PARAM_TEXT, 'Activity name'),
            'intro' => new external_value(PARAM_RAW, 'Description', VALUE_DEFAULT, ''),
            'introformat' => new external_value(PARAM_INT, 'Intro format', VALUE_DEFAULT, FORMAT_HTML),
            'section' => new external_value(PARAM_INT, 'Course section', VALUE_DEFAULT, 0),
            'visible' => new external_value(PARAM_INT, 'Visible', VALUE_DEFAULT, 1),
            'visibleoncoursepage' => new external_value(PARAM_INT, 'Visible on course page', VALUE_DEFAULT, 1),
            'availabilityconditionsjson' => new external_value(PARAM_RAW, 'Availability JSON', VALUE_DEFAULT, ''),
            'completionunlocked' => new external_value(PARAM_INT, 'Completion unlocked', VALUE_DEFAULT, 1),
            'completionview' => new external_value(PARAM_INT, 'Completion view', VALUE_DEFAULT, 0),
            'completionexpected' => new external_value(PARAM_INT, 'Completion expected', VALUE_DEFAULT, 0),
            'showdescription' => new external_value(PARAM_INT, 'Show description', VALUE_DEFAULT, 0),
            'url' => new external_value(PARAM_URL, 'PDF URL (from external API)', VALUE_DEFAULT, ''),
            'file_name' => new external_value(PARAM_TEXT, 'File name', VALUE_DEFAULT, ''),
            'file_path' => new external_value(PARAM_TEXT, 'File path', VALUE_DEFAULT, ''),
        ]);
    }

    public static function create_checkmatepdf(
        $courseid,
        $name,
        $intro = '',
        $introformat = FORMAT_HTML,
        $section = 0,
        $visible = 1,
        $visibleoncoursepage = 1,
        $availabilityconditionsjson = '',
        $completionunlocked = 1,
        $completionview = 0,
        $completionexpected = 0,
        $showdescription = 0,
        $url = '',
        $file_name = '',
        $file_path = ''
    ) {
        global $DB;

        $params = self::validate_parameters(self::create_checkmatepdf_parameters(), [
            'courseid' => $courseid,
            'name' => $name,
            'intro' => $intro,
            'introformat' => $introformat,
            'section' => $section,
            'visible' => $visible,
            'visibleoncoursepage' => $visibleoncoursepage,
            'availabilityconditionsjson' => $availabilityconditionsjson,
            'completionunlocked' => $completionunlocked,
            'completionview' => $completionview,
            'completionexpected' => $completionexpected,
            'showdescription' => $showdescription,
            'url' => $url,
            'file_name' => $file_name,
            'file_path' => $file_path,
        ]);

        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        $context = context_course::instance($course->id);
        require_capability('mod/checkmatepdf:addinstance', $context);

        $module = $DB->get_record('modules', ['name' => 'checkmatepdf'], '*', MUST_EXIST);

        $data = new stdClass();
        $data->modulename = 'checkmatepdf';
        $data->module = $module->id;
        $data->course = $course->id;
        $data->name = $params['name'];
        $data->intro = $params['intro'];
        $data->introformat = $params['introformat'];
        $data->section = $params['section'];
        $data->visible = $params['visible'];
        $data->visibleoncoursepage = $params['visibleoncoursepage'];
        $data->availabilityconditionsjson = $params['availabilityconditionsjson'];
        $data->completionunlocked = $params['completionunlocked'];
        $data->completionview = $params['completionview'];
        $data->completionexpected = $params['completionexpected'];
        $data->showdescription = $params['showdescription'];
        $data->url = $params['url'];
        $data->file_name = $params['file_name'];
        $data->file_path = $params['file_path'];
        $data->timemodified = time();

        $cm = add_moduleinfo($data, $course);

        if (!$cm) {
            throw new moodle_exception('errorcreatingcheckmatepdf', 'mod_checkmatepdf');
        }

        $checkmatepdf = $DB->get_record('checkmatepdf', ['id' => $cm->instance], '*', MUST_EXIST);

        return [
            'id' => $checkmatepdf->id,
            'course' => $checkmatepdf->course,
            'name' => $checkmatepdf->name,
            'intro' => $checkmatepdf->intro,
            'introformat' => $checkmatepdf->introformat,
            'url' => $checkmatepdf->url ?? '',
            'file_name' => $checkmatepdf->file_name ?? '',
            'file_path' => $checkmatepdf->file_path ?? '',
            'timemodified' => $checkmatepdf->timemodified,
            'cmid' => $cm->coursemodule,
            'coursemodule' => $cm->coursemodule,
        ];
    }

    public static function create_checkmatepdf_returns()
    {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Instance ID'),
            'course' => new external_value(PARAM_INT, 'Course ID'),
            'name' => new external_value(PARAM_TEXT, 'Activity name'),
            'intro' => new external_value(PARAM_RAW, 'Description'),
            'introformat' => new external_value(PARAM_INT, 'Intro format'),
            'url' => new external_value(PARAM_RAW, 'PDF URL'),
            'file_name' => new external_value(PARAM_TEXT, 'File name'),
            'file_path' => new external_value(PARAM_TEXT, 'File path'),
            'timemodified' => new external_value(PARAM_INT, 'Time modified'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'coursemodule' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    // =========================================================
    // get_checkmatepdf
    // =========================================================

    public static function get_checkmatepdf_parameters()
    {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'checkmatepdf instance ID'),
        ]);
    }

    public static function get_checkmatepdf($id)
    {
        global $DB;

        $params = self::validate_parameters(self::get_checkmatepdf_parameters(), ['id' => $id]);

        $checkmatepdf = $DB->get_record('checkmatepdf', ['id' => $params['id']], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $checkmatepdf->course], '*', MUST_EXIST);
        $context = context_course::instance($course->id);
        require_capability('mod/checkmatepdf:view', $context);

        $cm = get_coursemodule_from_instance('checkmatepdf', $checkmatepdf->id, $course->id, false, MUST_EXIST);

        return [
            'id' => $checkmatepdf->id,
            'course' => $checkmatepdf->course,
            'name' => $checkmatepdf->name,
            'intro' => $checkmatepdf->intro ?? '',
            'introformat' => $checkmatepdf->introformat,
            'url' => $checkmatepdf->url ?? '',
            'file_name' => $checkmatepdf->file_name ?? '',
            'file_path' => $checkmatepdf->file_path ?? '',
            'timemodified' => $checkmatepdf->timemodified,
            'cmid' => $cm->id,
            'coursemodule' => $cm->id,
            'section' => $cm->section,
            'visible' => $cm->visible,
            'visibleoncoursepage' => $cm->visibleoncoursepage,
            'availabilityconditionsjson' => $cm->availability ?? '',
            'completionview' => $cm->completionview,
            'completionexpected' => $cm->completionexpected,
            'showdescription' => $cm->showdescription,
        ];
    }

    public static function get_checkmatepdf_returns()
    {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Instance ID'),
            'course' => new external_value(PARAM_INT, 'Course ID'),
            'name' => new external_value(PARAM_TEXT, 'Activity name'),
            'intro' => new external_value(PARAM_RAW, 'Description'),
            'introformat' => new external_value(PARAM_INT, 'Intro format'),
            'url' => new external_value(PARAM_RAW, 'PDF URL'),
            'file_name' => new external_value(PARAM_TEXT, 'File name'),
            'file_path' => new external_value(PARAM_TEXT, 'File path'),
            'timemodified' => new external_value(PARAM_INT, 'Time modified'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'coursemodule' => new external_value(PARAM_INT, 'Course module ID'),
            'section' => new external_value(PARAM_INT, 'Course section'),
            'visible' => new external_value(PARAM_INT, 'Visible'),
            'visibleoncoursepage' => new external_value(PARAM_INT, 'Visible on course page'),
            'availabilityconditionsjson' => new external_value(PARAM_RAW, 'Availability JSON'),
            'completionview' => new external_value(PARAM_INT, 'Completion view'),
            'completionexpected' => new external_value(PARAM_INT, 'Completion expected'),
            'showdescription' => new external_value(PARAM_INT, 'Show description'),
        ]);
    }

    // =========================================================
    // update_checkmatepdf
    // =========================================================

    public static function update_checkmatepdf_parameters()
    {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'fields' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_TEXT, 'Activity name', VALUE_OPTIONAL),
                    'intro' => new external_value(PARAM_RAW, 'Description', VALUE_OPTIONAL),
                    'introformat' => new external_value(PARAM_INT, 'Intro format', VALUE_OPTIONAL),
                    'url' => new external_value(PARAM_RAW, 'PDF URL', VALUE_OPTIONAL),
                    'file_name' => new external_value(PARAM_TEXT, 'File name', VALUE_OPTIONAL),
                    'file_path' => new external_value(PARAM_TEXT, 'File path', VALUE_OPTIONAL),
                    'section' => new external_value(PARAM_INT, 'Section number', VALUE_OPTIONAL),
                    'visible' => new external_value(PARAM_INT, 'Visible', VALUE_OPTIONAL),
                    'visibleoncoursepage' => new external_value(PARAM_INT, 'Visible on course page', VALUE_OPTIONAL),
                    'completion' => new external_value(PARAM_INT, 'Completion tracking (0=none,1=manual,2=auto)', VALUE_OPTIONAL),
                    'completionview' => new external_value(PARAM_INT, 'Require view to complete (0/1)', VALUE_OPTIONAL),
                    'completiongradeitemnumber' => new external_value(PARAM_INT, 'Grade item number for completion', VALUE_OPTIONAL),
                    'completionexpected' => new external_value(PARAM_INT, 'Completion expected timestamp', VALUE_OPTIONAL),
                    'showdescription' => new external_value(PARAM_INT, 'Show description on course page', VALUE_OPTIONAL),
                    'availability' => new external_single_structure([
                        'completioncmid' => new external_multiple_structure(
                            new external_value(PARAM_INT, 'Completion CM ID'),
                            'Completion conditions'
                        ),
                        'timeopen' => new external_value(PARAM_INT, 'Open time', VALUE_OPTIONAL),
                        'timeclose' => new external_value(PARAM_INT, 'Close time', VALUE_OPTIONAL),
                        'gradeitemid' => new external_value(PARAM_INT, 'Grade item ID', VALUE_OPTIONAL),
                        'min' => new external_value(PARAM_FLOAT, 'Min grade', VALUE_OPTIONAL),
                        'max' => new external_value(PARAM_FLOAT, 'Max grade', VALUE_OPTIONAL),
                    ], 'Availability conditions', VALUE_OPTIONAL),
                ]),
                'Fields to update'
            ),
        ]);
    }

    public static function update_checkmatepdf($cmid, $fields)
    {
        global $DB;

        $params = self::validate_parameters(self::update_checkmatepdf_parameters(), [
            'cmid' => $cmid,
            'fields' => $fields,
        ]);

        $cm = get_coursemodule_from_id('checkmatepdf', $params['cmid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

        // Check capabilities.
        $context = context_module::instance($cm->id);
        require_login($course);
        if (!has_capability('moodle/course:manageactivities', $context)) {
            throw new moodle_exception('nopermissions', 'error', '', 'manage activities');
        }

        $firstField = $params['fields'][0] ?? [];
        $checkmatepdf = $DB->get_record('checkmatepdf', ['id' => $cm->instance], '*', MUST_EXIST);

        // 1. Update checkmatepdf table fields (url, file_name, file_path, etc.).
        $tableFields = ['url', 'file_name', 'file_path'];
        foreach ($tableFields as $field) {
            if (isset($firstField[$field])) {
                $checkmatepdf->$field = $firstField[$field];
            }
        }
        $checkmatepdf->timemodified = time();
        $DB->update_record('checkmatepdf', $checkmatepdf);

        // 2. Build moduleinfo for update_moduleinfo() — handles completion, visibility,
        //    section, intro, availability, cache rebuild automatically.
        $moduleinfo = new stdClass();
        $moduleinfo->id = $cm->instance;
        $moduleinfo->modulename = 'checkmatepdf';
        $moduleinfo->coursemodule = $params['cmid'];
        $moduleinfo->course = $course->id;
        $moduleinfo->name = $firstField['name'] ?? $checkmatepdf->name;
        $moduleinfo->visible = $firstField['visible'] ?? $cm->visible;
        $moduleinfo->visibleoncoursepage = $firstField['visibleoncoursepage'] ?? $cm->visibleoncoursepage;
        $moduleinfo->showdescription = $firstField['showdescription'] ?? $cm->showdescription;
        $moduleinfo->groupmode = $cm->groupmode;
        $moduleinfo->groupingid = $cm->groupingid;

        // Completion fields — passed directly; update_moduleinfo handles the logic.
        $moduleinfo->completion = $firstField['completion'] ?? $cm->completion;
        $moduleinfo->completionview = $firstField['completionview'] ?? $cm->completionview;
        $moduleinfo->completiongradeitemnumber = $firstField['completiongradeitemnumber'] ?? null;
        $moduleinfo->completionexpected = $firstField['completionexpected'] ?? $cm->completionexpected;

        // Intro / description.
        if (plugin_supports('mod', 'checkmatepdf', FEATURE_MOD_INTRO, true)) {
            $draftid = file_get_unused_draft_itemid();
            $moduleinfo->introeditor = [
                'text' => $firstField['intro'] ?? $checkmatepdf->intro,
                'format' => $firstField['introformat'] ?? $checkmatepdf->introformat,
                'itemid' => $draftid,
            ];
        } else {
            $moduleinfo->intro = $firstField['intro'] ?? $checkmatepdf->intro;
            $moduleinfo->introformat = $firstField['introformat'] ?? $checkmatepdf->introformat;
        }

        // Availability JSON.
        if (!empty($firstField['availability'])) {
            $av = $firstField['availability'];
            $moduleinfo->availability = self::generate_availability_conditions(
                $av['timeopen'] ?? null,
                $av['timeclose'] ?? null,
                $av['gradeitemid'] ?? null,
                $av['min'] ?? null,
                $av['max'] ?? null,
                $av['completioncmid'] ?? []
            );
        } else {
            $moduleinfo->availability = null;
        }

        // 3. Call Moodle's update_moduleinfo — the single source of truth for CM updates.
        list($cm, $moduleinfo) = update_moduleinfo($cm, $moduleinfo, $course);

        // 4. Move to different section if requested (update_moduleinfo does not handle this).
        if (isset($firstField['section'])) {
            $targetSection = $DB->get_record('course_sections', [
                'course' => $course->id,
                'section' => $firstField['section'],
            ]);
            if ($targetSection && $targetSection->id != $cm->section) {
                self::move_activity_to_section($course->id, $params['cmid'], $firstField['section']);
            }
        }

        return [
            'status' => 'success',
            'message' => 'checkmatepdf updated successfully',
            'checkmatepdfid' => $checkmatepdf->id,
            'cmid' => $params['cmid'],
        ];
    }

    public static function update_checkmatepdf_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Result status'),
            'message' => new external_value(PARAM_TEXT, 'Result message'),
            'checkmatepdfid' => new external_value(PARAM_INT, 'checkmatepdf instance ID'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    // =========================================================
    // delete_checkmatepdf
    // =========================================================

    public static function delete_checkmatepdf_parameters()
    {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'checkmatepdf instance ID'),
        ]);
    }

    public static function delete_checkmatepdf($id)
    {
        global $DB;

        $params = self::validate_parameters(self::delete_checkmatepdf_parameters(), ['id' => $id]);

        $checkmatepdf = $DB->get_record('checkmatepdf', ['id' => $params['id']], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $checkmatepdf->course], '*', MUST_EXIST);
        $context = context_course::instance($course->id);
        require_capability('mod/checkmatepdf:addinstance', $context);

        $result = checkmatepdf_delete_instance($params['id']);

        if (!$result) {
            throw new moodle_exception('errordeletingcheckmatepdf', 'mod_checkmatepdf');
        }

        return [
            'success' => true,
            'message' => 'checkmatepdf deleted successfully',
        ];
    }

    public static function delete_checkmatepdf_returns()
    {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'message' => new external_value(PARAM_TEXT, 'Result message'),
        ]);
    }

    // =========================================================
    // move_activity_to_section  (internal + web-service)
    // =========================================================

    public static function move_activity_to_section_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'moduleid' => new external_value(PARAM_INT, 'Course module ID'),
            'newsection' => new external_value(PARAM_INT, 'Target section number'),
        ]);
    }

    public static function move_activity_to_section($courseid, $moduleid, $newsection)
    {
        global $DB;

        $params = self::validate_parameters(self::move_activity_to_section_parameters(), [
            'courseid' => $courseid,
            'moduleid' => $moduleid,
            'newsection' => $newsection,
        ]);

        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);
        $module = $DB->get_record('course_modules', ['id' => $params['moduleid']], '*', MUST_EXIST);

        if ($module->course != $course->id) {
            throw new moodle_exception('modulenotincourse', 'mod_checkmatepdf');
        }

        $section = $DB->get_record('course_sections', [
            'course' => $params['courseid'],
            'section' => $params['newsection'],
        ]);

        if (!$section) {
            throw new moodle_exception('invalidsection', 'mod_checkmatepdf');
        }

        if ($module->section == $section->id) {
            return null;
        }

        moveto_module($module, $section);
        rebuild_course_cache($params['courseid'], true);

        return null;
    }

    public static function move_activity_to_section_returns()
    {
        return null;
    }

    // =========================================================
    // Private helper: generate_availability_conditions
    // =========================================================

    private static function generate_availability_conditions(
        $timeopen = null,
        $timeclose = null,
        $gradeitemid = null,
        $min = null,
        $max = null,
        $completioncmids = null
    ) {
        $conditions = [];
        $showc = [];

        if ($timeopen !== null && $timeclose !== null) {
            $conditions[] = ['type' => 'date', 'd' => '<', 't' => $timeopen];
            $showc[] = false;
            $conditions[] = ['type' => 'date', 'd' => '>=', 't' => $timeclose];
            $showc[] = false;
        } elseif ($timeopen !== null) {
            $conditions[] = ['type' => 'date', 'd' => '<', 't' => $timeopen];
            $showc[] = false;
        } elseif ($timeclose !== null) {
            $conditions[] = ['type' => 'date', 'd' => '>=', 't' => $timeclose];
            $showc[] = false;
        }

        if ($gradeitemid !== null && ($min !== null || $max !== null)) {
            if ($min !== null) {
                $conditions[] = ['type' => 'grade', 'id' => $gradeitemid, 'min' => $min];
                $showc[] = true;
            }
            if ($max !== null) {
                $conditions[] = ['type' => 'grade', 'id' => $gradeitemid, 'max' => $max];
                $showc[] = true;
            }
        }

        if (!empty($completioncmids)) {
            foreach ($completioncmids as $cid) {
                $conditions[] = ['type' => 'completion', 'cm' => $cid, 'e' => 1];
                $showc[] = true;
            }
        }

        return json_encode(['op' => '&', 'c' => $conditions, 'showc' => $showc]);
    }
}
