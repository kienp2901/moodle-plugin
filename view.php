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
 * Reading Flow module view
 *
 * @package mod_readingflow
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/readingflow/lib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$r       = optional_param('r', 0, PARAM_INT);  // Reading Flow instance ID

if ($r) {
    if (!$readingflow = $DB->get_record('readingflow', array('id'=>$r))) {
        throw new \moodle_exception('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('readingflow', $readingflow->id, $readingflow->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('readingflow', $id)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    $readingflow = $DB->get_record('readingflow', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/readingflow:view', $context);

// Completion and trigger events.
readingflow_view($readingflow, $course, $cm, $context);

$PAGE->set_url('/mod/readingflow/view.php', array('id' => $cm->id));

$PAGE->set_title($course->shortname.': '.$readingflow->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($readingflow);

echo $OUTPUT->header();

// Display the reading flow content
echo $OUTPUT->box_start('generalbox center clearfix');
echo format_module_intro('readingflow', $readingflow, $cm->id);

// Display content field
if (!empty($readingflow->content)) {
    $content = file_rewrite_pluginfile_urls($readingflow->content, 'pluginfile.php', $context->id, 
        'mod_readingflow', 'content', 0);
    $formatoptions = array(
        'noclean' => true,
        'overflowdiv' => true,
        'context' => $context
    );
    echo format_text($content, $readingflow->contentformat, $formatoptions);
}

echo $OUTPUT->box_end();

echo $OUTPUT->footer();

