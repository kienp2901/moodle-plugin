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
 * @package mod_readingflow
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * List of features supported in Reading Flow module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know or string for the module purpose.
 */
function readingflow_supports($feature) {
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
function readingflow_reset_userdata($data) {
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
function readingflow_get_view_actions() {
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
function readingflow_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add readingflow instance.
 * @param stdClass $data
 * @param mod_readingflow_mod_form $mform
 * @return int new readingflow instance id
 */
function readingflow_add_instance($data, $mform = null) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = time();

    // Handle editor field
    if ($mform && isset($data->content) && is_array($data->content)) {
        $data->contentformat = isset($data->content['format']) ? $data->content['format'] : FORMAT_HTML;
        $data->content = isset($data->content['text']) ? $data->content['text'] : '';
    } else if (!isset($data->contentformat)) {
        $data->contentformat = FORMAT_HTML;
    }

    // Handle lumos fields - set defaults if not set
    if (!isset($data->lumos_reading_id)) {
        $data->lumos_reading_id = 0;
    }
    if (!isset($data->lumos_reading_slug)) {
        $data->lumos_reading_slug = '';
    }

    $cmid = $data->coursemodule;
    $data->id = $DB->insert_record('readingflow', $data);
    
    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
    $context = context_module::instance($cmid);

    // Process files after we have context
    if ($mform && isset($data->content['itemid'])) {
        $draftitemid = $data->content['itemid'];
        $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_readingflow', 'content', 0, 
            array('noclean' => true, 'subdirs' => true, 'enable_filemanagement' => true), $data->content);
        $DB->update_record('readingflow', $data);
    }

    return $data->id;
}

/**
 * Update readingflow instance.
 * @param stdClass $data
 * @param mod_readingflow_mod_form $mform
 * @return bool true
 */
function readingflow_update_instance($data, $mform = null) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;
    $cmid = $data->coursemodule;
    $context = context_module::instance($cmid);

    // Handle editor field
    if ($mform && isset($data->content) && is_array($data->content)) {
        $draftitemid = isset($data->content['itemid']) ? $data->content['itemid'] : null;
        $data->contentformat = isset($data->content['format']) ? $data->content['format'] : FORMAT_HTML;
        $data->content = isset($data->content['text']) ? $data->content['text'] : '';
        
        // Handle lumos fields - ensure they are set
        if (!isset($data->lumos_reading_id)) {
            $data->lumos_reading_id = 0;
        }
        if (!isset($data->lumos_reading_slug)) {
            $data->lumos_reading_slug = '';
        }
        
        // Update record first
        $DB->update_record('readingflow', $data);
        
        // Then process files
        if ($draftitemid) {
            $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_readingflow', 'content', 0, 
                array('noclean' => true, 'subdirs' => true, 'enable_filemanagement' => true), $data->content);
            $DB->update_record('readingflow', $data);
        }
    } else {
        if (!isset($data->contentformat)) {
            $data->contentformat = FORMAT_HTML;
        }
        // Handle lumos fields - ensure they are set
        if (!isset($data->lumos_reading_id)) {
            $data->lumos_reading_id = 0;
        }
        if (!isset($data->lumos_reading_slug)) {
            $data->lumos_reading_slug = '';
        }
        $DB->update_record('readingflow', $data);
    }

    return true;
}

/**
 * Delete readingflow instance.
 * @param int $id
 * @return bool true
 */
function readingflow_delete_instance($id) {
    global $DB;

    if (!$readingflow = $DB->get_record('readingflow', array('id'=>$id))) {
        return false;
    }

    $DB->delete_records('readingflow', array('id'=>$readingflow->id));

    return true;
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $readingflow     readingflow object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function readingflow_view($readingflow, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $readingflow->id
    );

    $event = \mod_readingflow\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('readingflow', $readingflow);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

