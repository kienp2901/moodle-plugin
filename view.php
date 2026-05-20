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
 * Test Exam module view
 *
 * @package mod_quizexercise
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/quizexercise/lib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$t       = optional_param('t', 0, PARAM_INT);  // Test Exam instance ID

if ($t) {
    if (!$quizexercise = $DB->get_record('quizexercise', array('id'=>$t))) {
        throw new \moodle_exception('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('quizexercise', $quizexercise->id, $quizexercise->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('quizexercise', $id)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    $quizexercise = $DB->get_record('quizexercise', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/quizexercise:view', $context);

// Completion and trigger events.
quizexercise_view($quizexercise, $course, $cm, $context);

$PAGE->set_url('/mod/quizexercise/view.php', array('id' => $cm->id));

$PAGE->set_title($course->shortname.': '.$quizexercise->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($quizexercise);

echo $OUTPUT->header();

// Display the test exam content
echo $OUTPUT->box_start('generalbox center clearfix');
echo format_module_intro('quizexercise', $quizexercise, $cm->id);
echo $OUTPUT->box_end();

echo $OUTPUT->footer();