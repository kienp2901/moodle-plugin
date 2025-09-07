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
 * cmsvideo module view
 *
 * @package    mod_cmsvideo
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/cmsvideo/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... cmsvideo instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('cmsvideo', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $cmsvideo  = $DB->get_record('cmsvideo', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $cmsvideo  = $DB->get_record('cmsvideo', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cmsvideo->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('cmsvideo', $cmsvideo->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

// Mark viewed by user (if required).
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Print the page header.

$PAGE->set_url('/mod/cmsvideo/view.php', array('id' => $cm->id));
$PAGE->set_title($course->shortname.': '.$cmsvideo->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($cmsvideo);

// Output starts here.
echo $OUTPUT->header();

// If the intro field is set, display it.
if ($cmsvideo->intro) {
    echo $OUTPUT->box(format_module_intro('cmsvideo', $cmsvideo, $cm->id), 'generalbox mod_introbox', 'cmsvideointro');
}

// Display the activity content.
echo $OUTPUT->box_start('generalbox cmsvideocontent');

if (!empty($cmsvideo->source_path)) {
    echo '<p><strong>' . get_string('sourcepath', 'cmsvideo') . ':</strong> ' . htmlspecialchars($cmsvideo->source_path) . '</p>';
} else {
    echo '<p>' . get_string('nosourcepath', 'cmsvideo') . '</p>';
}

echo $OUTPUT->box_end();

// Finish the page.
echo $OUTPUT->footer();
