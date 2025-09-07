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
 * cmsvideo module index page
 *
 * @package    mod_cmsvideo
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT); // Course ID.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

$params = array(
    'context' => context_course::instance($course->id)
);
$event = \mod_cmsvideo\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strcmsvideo = get_string('modulename', 'cmsvideo');
$strcmsvideos = get_string('modulenameplural', 'cmsvideo');
$strname = get_string('name');
$strintro = get_string('moduleintro');

$PAGE->set_url('/mod/cmsvideo/index.php', array('id' => $id));
$PAGE->set_title($course->shortname.': '.$strcmsvideos);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strcmsvideos);
echo $OUTPUT->header();
echo $OUTPUT->heading($strcmsvideos);

if (!$cmsvideos = get_all_instances_in_course('cmsvideo', $course)) {
    notice(get_string('thereareno', 'moodle', $strcmsvideos), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strname, $strintro);
    $table->align = array ('left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($cmsvideos as $cmsvideo) {
    $cm = $modinfo->cms[$cmsvideo->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($cmsvideo->section !== $currentsection) {
            if ($cmsvideo->section) {
                $printsection = get_section_name($course, $cmsvideo->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $cmsvideo->section;
        }
    } else {
        $printsection = '';
    }

    $class = $cmsvideo->visible ? '' : 'class="dimmed"'; // Hidden modules are dimmed.

    $table->data[] = array (
        $printsection,
        "<a $class href=\"view.php?id=$cm->id\">".format_string($cmsvideo->name)."</a>",
        format_text($cmsvideo->intro, $cmsvideo->introformat));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
