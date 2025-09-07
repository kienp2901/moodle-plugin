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
 * Library functions for cmsvideo module
 *
 * @package    mod_cmsvideo
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the information on whether the module supports a feature
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function cmsvideo_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the cmsvideo into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $cmsvideo An object from the form in mod_form.php
 * @return int The id of the newly inserted cmsvideo record
 */
function cmsvideo_add_instance($cmsvideo) {
    global $DB;

    $cmsvideo->timecreated = time();
    $cmsvideo->timemodified = time();

    $cmsvideo->id = $DB->insert_record('cmsvideo', $cmsvideo);

    return $cmsvideo->id;
}

/**
 * Updates an instance of the cmsvideo in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $cmsvideo An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function cmsvideo_update_instance($cmsvideo) {
    global $DB;

    $cmsvideo->timemodified = time();
    $cmsvideo->id = $cmsvideo->instance;

    return $DB->update_record('cmsvideo', $cmsvideo);
}

/**
 * Removes an instance of the cmsvideo from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function cmsvideo_delete_instance($id) {
    global $DB;

    if (!$cmsvideo = $DB->get_record('cmsvideo', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('cmsvideo', array('id' => $cmsvideo->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $cmsvideo
 * @return object|null
 */
function cmsvideo_user_outline($course, $user, $mod, $cmsvideo) {
    return null;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $cmsvideo
 * @return bool
 */
function cmsvideo_user_complete($course, $user, $mod, $cmsvideo) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in cmsvideo activities and print it out.
 *
 * @param object $course
 * @param bool $viewfullnames
 * @param int $timestart
 * @return bool
 */
function cmsvideo_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * @param array $activities
 * @param int $index
 * @param int $timestart
 * @param int $courseid
 * @param int $cmid
 * @param int $userid
 * @param int $groupid
 * @return void
 */
function cmsvideo_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
    // No recent activity for this module
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 */
function cmsvideo_cron() {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @return array
 */
function cmsvideo_get_extra_capabilities() {
    return array();
}

/**
 * Gradebook API
 */

/**
 * Is a given scale used by the instance of cmsvideo?
 *
 * This function returns if a scale is being used by one cmsvideo
 * if it has support for grading and scales. Commented code should be
 * modified accordingly to the implementation.
 *
 * @param int $cmsvideoid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool True if the scale is used by the given cmsvideo instance
 */
function cmsvideo_scale_used($cmsvideoid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of cmsvideo.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any cmsvideo instance
 */
function cmsvideo_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * Creates or updates grade item for the give cmsvideo instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $cmsvideo instance object with extra cmidnumber and modname property
 * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int, 0 if ok, error code otherwise
 */
function cmsvideo_grade_item_update($cmsvideo, $grades = null) {
    return GRADE_UPDATE_OK;
}

/**
 * Delete grade item for given cmsvideo instance
 *
 * @param stdClass $cmsvideo instance object
 * @return grade_item
 */
function cmsvideo_grade_item_delete($cmsvideo) {
    return GRADE_UPDATE_OK;
}

/**
 * Update cmsvideo grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $cmsvideo instance object with extra cmidnumber and modname property
 * @param int $userid Update grade of specific user only, 0 means all participants
 */
function cmsvideo_update_grades($cmsvideo, $userid = 0) {
    // No grades for this module
}

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field.
 * The file area 'content' for the activity content.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function cmsvideo_get_file_areas($course, $cm, $context) {
    return array(
        'intro' => get_string('moduleintro'),
    );
}

/**
 * File browsing support for cmsvideo file areas
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function cmsvideo_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the cmsvideo file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the cmsvideo's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function cmsvideo_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'intro') {
        return false;
    }

    // Make sure the user is logged in and has access to the module.
    require_login($course, true, $cm);

    // No additional restrictions here, so just serve the file.

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_cmsvideo/$filearea/0/$relativepath";
    $file = $fs->get_file_by_hash(sha1($fullpath));

    if (!$file || $file->is_directory()) {
        return false;
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
