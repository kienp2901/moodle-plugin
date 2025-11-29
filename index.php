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
 * Reading Flow module index page
 *
 * @package mod_readingflow
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT);   // Course

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    throw new \moodle_exception('invalidcourseid');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');

$params = array(
    'context' => context_course::instance($course->id)
);
$event = \mod_readingflow\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strreadingflow = get_string('modulename', 'readingflow');
$strreadingflows = get_string('modulenameplural', 'readingflow');
$strname = get_string('name');
$strintro = get_string('moduleintro');

$PAGE->set_url('/mod/readingflow/index.php', array('id' => $id));
$PAGE->set_title($course->shortname.': '.$strreadingflows);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strreadingflows);
echo $OUTPUT->header();
echo $OUTPUT->heading($strreadingflows);

if (!$readingflows = get_all_instances_in_course('readingflow', $course)) {
    notice(get_string('thereareno', 'moodle', $strreadingflows), "$CFG->wwwroot/course/view.php?id=$course->id");
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
foreach ($readingflows as $readingflow) {
    $cm = $modinfo->cms[$readingflow->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($readingflow->section !== $currentsection) {
            if ($readingflow->section) {
                $printsection = get_section_name($course, $readingflow->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $readingflow->section;
        }
    } else {
        $printsection = '';
    }

    $class = $readingflow->visible ? '' : 'class="dimmed"'; // Hidden modules are dimmed

    $extra = empty($cm->extra) ? '' : $cm->extra;
    $icon = '';
    if (!empty($cm->icon)) {
        // each readingflow has an icon in 0/1
        $icon = $OUTPUT->pix_icon($cm->icon, get_string('modulename', 'readingflow'));
    }

    $link = '<a href="view.php?id='.$cm->id.'">'.$readingflow->name.'</a>';
    $intro = format_module_intro('readingflow', $readingflow, $cm->id);

    if ($usesections) {
        $printsection = $printsection;
    }

    if ($usesections) {
        $table->data[] = array ($printsection, $icon.$link, $intro);
    } else {
        $table->data[] = array ($icon.$link, $intro);
    }
}

echo html_writer::table($table);

echo $OUTPUT->footer();

