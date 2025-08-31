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
 * @package mod_checkmatepdf
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in checkmatepdf module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know or string for the module purpose.
 */
function checkmatepdf_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_MOD_PURPOSE:             return MOD_PURPOSE_CONTENT;

        default: return null;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function checkmatepdf_reset_userdata($data) {

    // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
    // See MDL-9367.

    return array();
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function checkmatepdf_get_view_actions() {
    return array('view','view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function checkmatepdf_get_post_actions() {
    return array('update', 'add');
}

function upload_file_to_api($filepath, $filename, $apiurl, $cmid) {
    $curl = new curl();
    $postdata = array(
        'file' => curl_file_create($filepath, mime_content_type($filepath), $filename),
        'activity_id' => $cmid
    );
    $response = $curl->post($apiurl, $postdata);
    
    $result = json_decode($response);
    
    if (!isset($result->success) || !$result->success) {
        throw new moodle_exception('apiuploaderror', 'checkmatepdf', '', $result->message ?? 'Unknown error');
    }
    
    return [
        'urlStorageFile' => $result->data->urlStorageFile,
        'fileName' => $result->data->fileNameOrigin,
        'filePath' => $result->data->filePath,
    ]; // Hoặc giá trị mà API trả về.
}

/**
 * Add checkmatepdf instance.
 * @param stdClass $data
 * @param mod_checkmatepdf_mod_form $mform
 * @return int new checkmatepdf instance id
 */
function checkmatepdf_add_instance($data, $mform = null) {
    global $CFG, $DB, $USER;
    require_once("$CFG->libdir/resourcelib.php");
    require_once($CFG->dirroot . '/mod/checkmatepdf/config/config.php');
    $cmid = $data->coursemodule;

    $draftitemid = $data->files;
    $fs = get_file_storage();
    $context = context_user::instance($USER->id);
    $files = $fs->get_area_files($context->id, 'user', 'draft', $draftitemid, 'id', false);
    if ($files) {
        // Lấy file đầu tiên nếu tồn tại.
        $file = reset($files);
        $filename = $file->get_filename();

        $tempfilepath = $CFG->tempdir . '/' . $filename;
        file_put_contents($tempfilepath, $file->get_content());

        // Tải file lên API.
        $api_url = $apiUploadFile;
        try {
            $uploaded_file_url = upload_file_to_api($tempfilepath, $filename, $api_url, $cmid);
            $data->url = $uploaded_file_url['urlStorageFile']; // Lưu URL từ API.
            $data->file_name = $uploaded_file_url['fileName']; // Lưu fileName từ API.
            $data->file_path = $uploaded_file_url['filePath']; // Lưu filePath từ API.
        } catch (Exception $e) {
            throw new moodle_exception('fileuploadfailed', 'checkmatepdf', '', $e->getMessage());
        }
    }
    $data->timemodified = time();

    $data->id = $DB->insert_record('checkmatepdf', $data);

    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));

    return $data->id;
}

/**
 * Update checkmatepdf instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function checkmatepdf_update_instance($data, $mform) {
    global $CFG, $DB, $USER;
    require_once("$CFG->libdir/resourcelib.php");
    require_once($CFG->dirroot . '/mod/checkmatepdf/config/config.php');
    
    $cmid        = $data->coursemodule;

    $draftitemid = $data->files;
    $fs = get_file_storage();
    $context = context_user::instance($USER->id);
    $files = $fs->get_area_files($context->id, 'user', 'draft', $draftitemid, 'id', false);
    if ($files) {
        // Lấy file đầu tiên nếu tồn tại.
        $file = reset($files);
        $filename = $file->get_filename();

        $tempfilepath = $CFG->tempdir . '/' . $filename;
        file_put_contents($tempfilepath, $file->get_content());

        // Tải file lên API.
        $api_url = $apiUploadFile;
        try {
            $uploaded_file_url = upload_file_to_api($tempfilepath, $filename, $api_url, $cmid);
            $data->url = $uploaded_file_url['urlStorageFile']; // Lưu URL từ API.
            $data->file_name = $uploaded_file_url['fileName']; // Lưu fileName từ API.
            $data->file_path = $uploaded_file_url['filePath']; // Lưu filePath từ API.
        } catch (Exception $e) {
            throw new moodle_exception('fileuploadfailed', 'checkmatepdf', '', $e->getMessage());
        }
    }

    $data->timemodified = time();
    $data->id           = $data->instance;

    $result = $DB->update_record('checkmatepdf', $data);

    $data->cmid = $data->coursemodule;

    return true;
}

/**
 * Delete checkmatepdf instance.
 * @param int $id
 * @return bool true
 */
function checkmatepdf_delete_instance($id) {
    global $DB;

    if (!$checkmatepdf = $DB->get_record('checkmatepdf', array('id'=>$id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('checkmatepdf', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'checkmatepdf', $id, null);

    // note: all context files are deleted automatically

    $DB->delete_records('checkmatepdf', array('id'=>$checkmatepdf->id));

    return true;
}

