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
 * checkmatepdf module version information
 *
 * @package mod_checkmatepdf
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/checkmatepdf/lib.php');
// require_once($CFG->dirroot.'/mod/checkmatepdf/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$p       = optional_param('p', 0, PARAM_INT);  // checkmatepdf instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($p) {
    if (!$checkmatepdf = $DB->get_record('checkmatepdf', array('id'=>$p))) {
        throw new \moodle_exception('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('checkmatepdf', $checkmatepdf->id, $checkmatepdf->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('checkmatepdf', $id)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    $checkmatepdf = $DB->get_record('checkmatepdf', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/checkmatepdf:view', $context);


$PAGE->set_url('/mod/checkmatepdf/view.php', array('id' => $cm->id));

$activityheader = ['hidecompletion' => false];
if (empty($options['printintro'])) {
    $activityheader['description'] = '';
}

$PAGE->set_title($course->shortname.': '.$checkmatepdf->name);
$PAGE->set_heading($course->fullname);
$PAGE->activityheader->set_attrs($activityheader);
echo $OUTPUT->header();

if (isset($checkmatepdf->url)) {
    echo 'Click <a href="' . htmlspecialchars($checkmatepdf->url) . '" target="_blank">' . htmlspecialchars($checkmatepdf->file_name) . '</a> link to view the file.';
}

?>

<?php

echo $OUTPUT->footer();
