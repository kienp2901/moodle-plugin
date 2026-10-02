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
 * Quiz Final Test module view
 *
 * @package mod_quizfinaltest
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/quizfinaltest/lib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$t       = optional_param('t', 0, PARAM_INT);  // Quiz Final Test instance ID

if ($t) {
    if (!$quizfinaltest = $DB->get_record('quizfinaltest', array('id'=>$t))) {
        throw new \moodle_exception('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('quizfinaltest', $quizfinaltest->id, $quizfinaltest->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('quizfinaltest', $id)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    $quizfinaltest = $DB->get_record('quizfinaltest', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/quizfinaltest:view', $context);

// Completion and trigger events.
quizfinaltest_view($quizfinaltest, $course, $cm, $context);

$PAGE->set_url('/mod/quizfinaltest/view.php', array('id' => $cm->id));

$PAGE->set_title($course->shortname.': '.$quizfinaltest->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($quizfinaltest);

echo $OUTPUT->header();

// Display the quiz final test content
echo $OUTPUT->box_start('generalbox center clearfix');
echo format_module_intro('quizfinaltest', $quizfinaltest, $cm->id);
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
