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
 * checkmatepdf configuration form
 *
 * @package mod_checkmatepdf
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
// require_once($CFG->dirroot.'/mod/checkmatepdf/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_checkmatepdf_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $config = get_config('checkmatepdf');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        $filemanager_options = array();
        $filemanager_options['accepted_types'] = '.pdf';
        $filemanager_options['maxbytes'] = 0;
        $filemanager_options['maxfiles'] = 1;
        $filemanager_options['mainfile'] = true;

        $mform->addElement('filemanager', 'files', get_string('selectfiles'), null, $filemanager_options);

        // $mform->addElement('text', 'url', get_string('url'), array('size'=>'48'));
        // if (!empty($CFG->formatstringstriptags)) {
        //     $mform->setType('url', PARAM_TEXT);
        // } else {
        //     $mform->setType('url', PARAM_CLEANHTML);
        // }
        // $mform->addRule('url', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        if (!empty($this->current->url)) {
            // Lấy URL từ database.
            $file_url = $DB->get_field('checkmatepdf', 'url', array('id' => $this->_cm->instance), IGNORE_MISSING);
    
            if (!empty($file_url)) {
                $html = '<a href="' . $file_url . '" target="_blank">' . $file_url . '</a>';
                $mform->addElement('static', 'file_display', get_string('url', 'checkmatepdf'), $html);
            }
        }

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();
    }
}

