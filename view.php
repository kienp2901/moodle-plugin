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
 * Step by Step module view page
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(__DIR__.'/lib.php');

// No need to import these classes - they are global Moodle classes

$id = optional_param('id', 0, PARAM_INT); // Course Module ID
$s = optional_param('s', 0, PARAM_INT);  // Step by Step instance ID

if ($id) {
    $cm = get_coursemodule_from_id('stepbystep', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $stepbystep = $DB->get_record('stepbystep', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($s) {
    $stepbystep = $DB->get_record('stepbystep', array('id' => $s), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $stepbystep->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('stepbystep', $stepbystep->id, $course->id, false, MUST_EXIST);
} else {
    print_error('missingparameter');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/stepbystep:view', $context);

// Trigger module viewed event.
$event = \mod_stepbystep\event\course_module_viewed::create(array(
    'objectid' => $stepbystep->id,
    'context' => $context,
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('stepbystep', $stepbystep);
$event->trigger();

// Mark as viewed for completion tracking.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Get content steps
$steps = $DB->get_records('stepbystep_content', 
    array('stepbystep_id' => $stepbystep->id), 'sortorder ASC');

// Debug: Check if steps exist
if (empty($steps)) {
    // Create demo steps if none exist - ONLY FOR TESTING
    $steps = array(
        (object)array(
            'id' => 1,
            'type' => 'vocabulary',
            'content' => '',
            'term' => 'dense forests',
            'definition' => 'những cánh rừng rậm rạp',
            'example' => 'We hiked through dense forests during our vacation.',
            'audio_file' => '',
            'response_text' => 'Got it!',
            'sortorder' => 0,
            'timecreated' => time()
        ),
        (object)array(
            'id' => 2,
            'type' => 'text',
            'content' => 'When reading IELTS passages, always read the questions first to understand what you need to look for.',
            'term' => '',
            'definition' => '',
            'example' => '',
            'audio_file' => '',
            'response_text' => 'I understand!',
            'sortorder' => 1,
            'timecreated' => time()
        )
    );
    
    // Debug: Log that we're using demo data
    error_log('Step by Step: Using demo data - no real steps found in database');
} else {
    // Debug: Log real database data
    error_log('Step by Step: Using real database data - found ' . count($steps) . ' steps');
    foreach ($steps as $step) {
        error_log('Step ID: ' . $step->id . ', Sort Order: ' . $step->sortorder);
    }
}

// Prepare data for template
$data = array(
    'name' => $stepbystep->name,
    'intro' => format_module_intro('stepbystep', $stepbystep, $cm->id),
    'steps' => array(),
    'totalsteps' => count($steps),
    'courseurl' => new moodle_url('/course/view.php', array('id' => $course->id))
);

// foreach ($steps as $index => $step) {
//     $stepData = array(
//         'id' => $step->id,
//         'type' => $step->type,
//         'content' => format_text($step->content, FORMAT_HTML, array('context' => $context)),
//         'term' => $step->term,
//         'definition' => $step->definition,
//         'example' => $step->example,
//         'audio_file' => $step->audio_file,
//         'response_text' => $step->response_text ?: get_string('continue', 'mod_stepbystep'),
//         'stepnumber' => $index + 1,
//         'islast' => ($index == count($steps) - 1),
//         'buttontext' => ($index == count($steps) - 1) ? get_string('complete', 'mod_stepbystep') : ($step->response_text ?: get_string('continue', 'mod_stepbystep')),
//         'totalsteps' => count($steps)
//     );

//     // Add type-specific variables for template
//     $stepData['type_' . $step->type] = true;
    
//     // Process audio file if exists
//     if (!empty($step->audio_file)) {
//         $fs = get_file_storage();
//         $files = $fs->get_area_files($context->id, 'mod_stepbystep', 'audio', $step->id);
//         foreach ($files as $file) {
//             if (!$file->is_directory()) {
//                 $stepData['audio_url'] = moodle_url::make_pluginfile_url(
//                     $file->get_contextid(),
//                     $file->get_component(),
//                     $file->get_filearea(),
//                     $file->get_itemid(),
//                     $file->get_filepath(),
//                     $file->get_filename()
//                 );
//                 break;
//             }
//         }
//     }
    
//     $data['steps'][] = $stepData;
// }


$stepnumber_counter = 1;

foreach ($steps as $step) {
    $stepData = array(
        'id' => $step->id,
        'type' => $step->type,
        'main_title' => isset($step->main_title) ? format_text($step->main_title, FORMAT_HTML, array('context' => $context)) : '',
        'sub_heading' => isset($step->sub_heading) ? format_text($step->sub_heading, FORMAT_HTML, array('context' => $context)) : '',
        'content_paragraphs' => isset($step->content_paragraphs) ? $step->content_paragraphs : '',
        'term' => $step->term,
        'phonetic' => isset($step->phonetic) ? $step->phonetic : '',
        'definition' => $step->definition,
        'example' => $step->example,
        'audio_file' => $step->audio_file,
        'response_text' => $step->response_text ?: get_string('continue', 'mod_stepbystep'),
        // Correctly calculate the step number using a counter
        'stepnumber' => $stepnumber_counter,
        'islast' => ($stepnumber_counter == count($steps)),
        'buttontext' => ($stepnumber_counter == count($steps)) ? get_string('complete', 'mod_stepbystep') : ($step->response_text ?: get_string('continue', 'mod_stepbystep')),
        'totalsteps' => count($steps)
    );

    // Add type-specific variables for template
    $stepData['type_' . $step->type] = true;
    
    // For text type, process content paragraphs if they exist
    if ($step->type === 'text' && !empty($step->content_paragraphs)) {
        // Try to decode JSON content paragraphs
        $paragraphs = json_decode($step->content_paragraphs, true);
        if (is_array($paragraphs)) {
            $stepData['paragraphs'] = array();
            foreach ($paragraphs as $paragraph) {
                $stepData['paragraphs'][] = format_text($paragraph, FORMAT_HTML, array('context' => $context));
            }
        } else {
            // Fallback: treat as plain text with line breaks
            $paragraphs = explode("\n", $step->content_paragraphs);
            $stepData['paragraphs'] = array();
            foreach ($paragraphs as $paragraph) {
                $paragraph = trim($paragraph);
                if (!empty($paragraph)) {
                    $stepData['paragraphs'][] = format_text($paragraph, FORMAT_HTML, array('context' => $context));
                }
            }
        }
    }
    
    // Process audio file if exists
    if (!empty($step->audio_file)) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_stepbystep', 'audio', $step->id);
        foreach ($files as $file) {
            if (!$file->is_directory()) {
                $stepData['audio_url'] = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
                break;
            }
        }
    }
    
    $data['steps'][] = $stepData;
    // Increment the counter for the next step
    $stepnumber_counter++;
}

// Debug: Log data structure
error_log('Step by Step data: ' . print_r($data, true));

// Debug: Log each step data being sent to template
foreach ($data['steps'] as $index => $stepData) {
    error_log('Template Step ' . ($index + 1) . ': ' . print_r($stepData, true));
}

// Set up page
$PAGE->set_url('/mod/stepbystep/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($stepbystep->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Add CSS and JS
$PAGE->requires->css(new moodle_url('/mod/stepbystep/styles.css'));
$PAGE->requires->js_call_amd('mod_stepbystep/main', 'init', array(
    'cmid' => $cm->id,
    'totalsteps' => count($steps)
));

// Output starts here
echo $OUTPUT->header();

// Render template
echo $OUTPUT->render_from_template('mod_stepbystep/view', $data);

echo $OUTPUT->footer();
