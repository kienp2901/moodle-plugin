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
 * Library of functions and constants for module stepbystep
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// No need to import these classes - they are global Moodle classes

/**
 * Send vocabulary data to API
 *
 * @param string $term The vocabulary term
 * @param string $definition The definition of the term
 * @param string $example The example usage
 * @param string $apiurl The API endpoint URL
 * @return array API response with status and storage_path
 * @throws moodle_exception
 */
function stepbystep_send_vocabulary_to_api($term, $definition, $example, $apiurl) {
    $curl = new curl();
    
    $postdata = array(
        'term' => $term,
        'definition' => $definition,
        'example' => $example
    );
    
    $response = $curl->post($apiurl, $postdata);
    
    $result = json_decode($response);
    
    if (!isset($result->status) || !$result->status) {
        throw new moodle_exception('apivocabularyerror', 'mod_stepbystep', '', $result->message ?? 'Unknown error');
    }
    
    return [
        'status' => $result->status,
        'storage_path' => $result->storage_path ?? ''
    ];
}

/**
 * List of features supported in Step by Step module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if not
 */
function stepbystep_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_CONTROLS_GRADE_VISIBILITY:
            return false;
        case FEATURE_USES_QUESTIONS:
            return false;
        case FEATURE_COMMENT:
            return false;
        case FEATURE_RATE:
            return false;
        case FEATURE_IDNUMBER:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the stepbystep into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $stepbystep An object from the form in mod_form.php
 * @param mod_stepbystep_mod_form $mform
 * @return int The id of the newly inserted stepbystep record
 */
function stepbystep_add_instance($stepbystep, $mform = null) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/stepbystep/config/config.php');

    $stepbystep->timecreated = time();
    $stepbystep->timemodified = time();

    $stepbystep->id = $DB->insert_record('stepbystep', $stepbystep);

    $cmid = $stepbystep->coursemodule;

    // Save content steps if provided
    if (isset($stepbystep->type) && is_array($stepbystep->type)) {
        $stepcount = count($stepbystep->type);
        
        for ($i = 0; $i < $stepcount; $i++) {
            // Debug: Check content structure
            error_log("Processing step $i");
            
            // Create step data array from form data
            $content = array(
                'type' => $stepbystep->type[$i],
                'main_title' => isset($stepbystep->main_title[$i]) ? $stepbystep->main_title[$i] : '',
                'sub_heading' => isset($stepbystep->sub_heading[$i]) ? $stepbystep->sub_heading[$i] : '',
                'content_paragraphs' => isset($stepbystep->content_paragraphs[$i]) ? $stepbystep->content_paragraphs[$i] : '',
                'term' => isset($stepbystep->term[$i]) ? $stepbystep->term[$i] : '',
                'definition' => isset($stepbystep->definition[$i]) ? $stepbystep->definition[$i] : '',
                'example' => isset($stepbystep->example[$i]) ? $stepbystep->example[$i] : '',
                'audio_file' => isset($stepbystep->audio_file[$i]) ? $stepbystep->audio_file[$i] : '',
                'response_text' => isset($stepbystep->response_text[$i]) ? stepbystep_convert_response_key_to_text($stepbystep->response_text[$i]) : 'Ti&#7871;p theo'
            );
            
            // Check if step has valid content based on type
            if (stepbystep_validate_step_content($content)) {
                $step = new stdClass();
                $step->stepbystep_id = $stepbystep->id;
                $step->type = $content['type'];
                $step->main_title = $content['main_title'];
                $step->sub_heading = $content['sub_heading'];
                
                // Handle content_paragraphs properly - extract text from editor array
                if (is_array($content['content_paragraphs']) && isset($content['content_paragraphs']['text'])) {
                    $step->content_paragraphs = $content['content_paragraphs']['text'];
                } else {
                    $step->content_paragraphs = $content['content_paragraphs'];
                }
                
                $step->term = $content['term'];
                $step->definition = $content['definition'];
                $step->example = $content['example'];
                $step->audio_file = $content['audio_file'];
                $step->response_text = $content['response_text'];
                $step->storage_path = ''; // Initialize storage_path
                $step->sortorder = $i;
                $step->timecreated = time();
                
                // Call API for vocabulary type steps
                // if ($content['type'] === 'vocabulary' && !empty($content['term'])) {
                //     try {
                //         $api_response = stepbystep_send_vocabulary_to_api(
                //             $content['term'],
                //             $content['definition'],
                //             $content['example'],
                //             $apiVocabularyUrl
                //         );
                //         $step->storage_path = $api_response['storage_path'];
                //     } catch (Exception $e) {
                //         // Log error but don't fail the entire operation
                //         error_log('Stepbystep API error: ' . $e->getMessage());
                //         // Continue without storage_path
                //     }
                // }
                
                $result = $DB->insert_record('stepbystep_content', $step);
            }
        }
    }

    $DB->set_field('course_modules', 'instance', $stepbystep->id, array('id'=>$cmid));

    return $stepbystep->id;
}

/**
 * Updates an instance of the stepbystep in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $stepbystep An object from the form in mod_form.php
 * @param mod_stepbystep_mod_form $mform
 * @return boolean Success/Failure
 */
function stepbystep_update_instance($stepbystep, $mform = null) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/stepbystep/config/config.php');

    $stepbystep->timemodified = time();
    $stepbystep->id = $stepbystep->instance;

    $result = $DB->update_record('stepbystep', $stepbystep);

    $cmid = $stepbystep->coursemodule;


    // Update content steps
    if (isset($stepbystep->type) && is_array($stepbystep->type)) {
        // Delete existing content
        $DB->delete_records('stepbystep_content', array('stepbystep_id' => $stepbystep->id));
        
        $stepcount = count($stepbystep->type);
        
        // Insert new content
        for ($i = 0; $i < $stepcount; $i++) {
            // Create step data array from form data
            $content = array(
                'type' => $stepbystep->type[$i],
                'main_title' => isset($stepbystep->main_title[$i]) ? $stepbystep->main_title[$i] : '',
                'sub_heading' => isset($stepbystep->sub_heading[$i]) ? $stepbystep->sub_heading[$i] : '',
                'content_paragraphs' => isset($stepbystep->content_paragraphs[$i]) ? $stepbystep->content_paragraphs[$i] : '',
                'term' => isset($stepbystep->term[$i]) ? $stepbystep->term[$i] : '',
                'definition' => isset($stepbystep->definition[$i]) ? $stepbystep->definition[$i] : '',
                'example' => isset($stepbystep->example[$i]) ? $stepbystep->example[$i] : '',
                'audio_file' => isset($stepbystep->audio_file[$i]) ? $stepbystep->audio_file[$i] : '',
                'response_text' => isset($stepbystep->response_text[$i]) ? stepbystep_convert_response_key_to_text($stepbystep->response_text[$i]) : 'Ti&#7871;p theo'
            );

            // var_dump($content, stepbystep_validate_step_content($content));
            // die();

            // Check if step has valid content based on type
            if (stepbystep_validate_step_content($content)) {
                $step = new stdClass();
                $step->stepbystep_id = $stepbystep->id;
                $step->type = $content['type'];
                $step->main_title = $content['main_title'];
                $step->sub_heading = $content['sub_heading'];
                
                // Handle content_paragraphs properly - extract text from editor array
                if (is_array($content['content_paragraphs']) && isset($content['content_paragraphs']['text'])) {
                    $step->content_paragraphs = $content['content_paragraphs']['text'];
                } else {
                    $step->content_paragraphs = $content['content_paragraphs'];
                }
                
                $step->term = $content['term'];
                $step->definition = $content['definition'];
                $step->example = $content['example'];
                $step->audio_file = $content['audio_file'];
                $step->response_text = $content['response_text'];
                $step->storage_path = ''; // Initialize storage_path
                $step->sortorder = $i;
                $step->timecreated = time();
                
                // Call API for vocabulary type steps
                // if ($content['type'] === 'vocabulary' && !empty($content['term'])) {
                //     try {
                //         $api_response = stepbystep_send_vocabulary_to_api(
                //             $content['term'],
                //             $content['definition'],
                //             $content['example'],
                //             $apiVocabularyUrl
                //         );
                //         $step->storage_path = $api_response['storage_path'];
                //     } catch (Exception $e) {
                //         // Log error but don't fail the entire operation
                //         error_log('Stepbystep API error: ' . $e->getMessage());
                //         // Continue without storage_path
                //     }
                // }
                
                $result = $DB->insert_record('stepbystep_content', $step);
            }
        }
    }

    $DB->set_field('course_modules', 'instance', $stepbystep->id, array('id'=>$cmid));

    return $result;
}

/**
 * Removes an instance of the stepbystep from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function stepbystep_delete_instance($id) {
    global $DB;

    if (!$stepbystep = $DB->get_record('stepbystep', array('id' => $id))) {
        return false;
    }

    $result = true;

    // Delete content steps
    $DB->delete_records('stepbystep_content', array('stepbystep_id' => $stepbystep->id));

    // Delete main record
    $DB->delete_records('stepbystep', array('id' => $stepbystep->id));

    return $result;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function stepbystep_user_outline($course, $user, $mod, $stepbystep) {
    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $stepbystep the module instance record
 * @return void, is supposed to echo directly
 */
function stepbystep_user_complete($course, $user, $mod, $stepbystep) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in stepbystep activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function stepbystep_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the $activity parameter
 * with object based on the $time and $mod parameter
 *
 * @param array $activity sequentially indexed array of objects with the 'cmid' property
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we retrieve the string for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activity and sets $timestart to the last one used
 */
function stepbystep_get_recent_mod_activity(&$activity, &$timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}

/**
 * Prints single activity item prepared by {@see stepbystep_get_recent_mod_activity()}
 *
 * @return void
 */
function stepbystep_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function stepbystep_cron() {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function stepbystep_get_extra_capabilities() {
    return array();
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function stepbystep_get_file_areas($course, $cm, $context) {
    return array(
        'intro' => get_string('intro', 'mod_stepbystep'),
        'audio' => get_string('audio', 'mod_stepbystep'),
    );
}

/**
 * File browsing support for stepbystep file areas
 *
 * @package  mod_stepbystep
 * @category files
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
function stepbystep_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the stepbystep files.
 *
 * @package  mod_stepbystep
 * @category files
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function stepbystep_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);

    if ($filearea !== 'intro' && $filearea !== 'audio') {
        return false;
    }

    $itemid = array_shift($args); // The first item in the $args array.

    if (!$itemid) {
        return false;
    }

    $filename = array_pop($args); // The last item in the $args array.

    if (!$filename) {
        $filename = 'index.html';
    }

    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    if (!$file = get_file_storage()->get_file($context->id, 'mod_stepbystep', $filearea, $itemid, $filepath, $filename)) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding stepbystep nodes if there is a relevant capability
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $stepbystepnode An object representing the navigation tree node of the stepbystep module
 * @param stdClass $course
 * @param stdClass $module
 * @param stdClass $cm
 */
function stepbystep_extend_navigation($navigationnode, $course, $module, $cm) {
}

/**
 * Extends the settings navigation with the stepbystep settings
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param settings_navigation $settingsnav The settings navigation object
 * @param navigation_node $stepbystepnode The stepbystep node object
 */
function stepbystep_extend_settings_navigation($settingsnav, $stepbystepnode) {
}

/**
 * Validates step content based on type
 *
 * @param array $content Step content array
 * @return boolean True if valid, false otherwise
 */
function stepbystep_validate_step_content($content) {
    if (empty($content['type'])) {
        return false;
    }
    
    switch ($content['type']) {
        case 'text':
            // For text type, check if main_title or content_paragraphs is provided
            $mainTitle = is_string($content['main_title']) ? trim($content['main_title']) : '';
            $contentParagraphs = '';
            
            // Handle content_paragraphs properly - extract text from editor array
            if (is_array($content['content_paragraphs']) && isset($content['content_paragraphs']['text'])) {
                $contentParagraphs = trim($content['content_paragraphs']['text']);
            } else if (is_string($content['content_paragraphs'])) {
                $contentParagraphs = trim($content['content_paragraphs']);
            }
            
            if (!empty($mainTitle) || !empty($contentParagraphs)) {
                return true;
            }
            return false;
            
        case 'vocabulary':
            $term = is_string($content['term']) ? trim($content['term']) : '';
            return !empty($term);
            
        default:
            return false;
    }
}

/**
 * Extracts content text from step content array
 *
 * @param array $content Step content array
 * @return string Content text
 */
function stepbystep_extract_content_text($content) {
    switch ($content['type']) {
        case 'text':
            if (isset($content['step_content']) && is_array($content['step_content']) && isset($content['step_content']['text'])) {
                return $content['step_content']['text'];
            } else if (isset($content['step_content']) && is_string($content['step_content'])) {
                return $content['step_content'];
            }
            return '';
            
        case 'vocabulary':
            return $content['term'];
            
        default:
            return '';
    }
}

/* Completion API */

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $stepbystep The stepbystep instance record
 * @return stdClass|null
 */
function stepbystep_get_user_activity_summary($course, $user, $mod, $stepbystep) {
    return null;
}

/**
 * Obtains the automatic completion state for this module based on any conditions
 * in module settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if the course module has been completed
 */
function stepbystep_get_completion_state($course, $cm, $userid, $type) {
    global $DB;
    
    // Get the stepbystep instance
    $stepbystep = $DB->get_record('stepbystep', array('id' => $cm->instance), '*', MUST_EXIST);
    
    // Check if user has viewed the module
    $completion = new completion_info($course);
    $data = $completion->get_data($cm, false, $userid);
    
    // If completion is set to "view" only, return the view status
    if ($stepbystep->completion == COMPLETION_TRACKING_AUTOMATIC) {
        return $data->viewed;
    }
    
    // If completion is set to "manual", return false (user must manually mark as complete)
    if ($stepbystep->completion == COMPLETION_TRACKING_MANUAL) {
        return false;
    }
    
    // Default: no completion tracking
    return $type;
}

/**
 * Convert response text key to display text
 *
 * @param string $key The response key
 * @return string The display text
 */
function stepbystep_convert_response_key_to_text($key) {
    $responseMapping = array(
        'tiep_theo' => 'Ti&#7871;p theo',
        'hop_ly' => 'H&#7907;p l&#253;!',
        'duoc_roi' => '&#272;&#432;&#7907;c r&#7891;i!',
        'dong_y' => '&#272;&#7891;ng &#253;!',
        'hieu_roi' => 'Hi&#7875;u r&#7891;i!'
    );
    
    return isset($responseMapping[$key]) ? $responseMapping[$key] : $key;
}
