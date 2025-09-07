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
 * cmsvideo module form
 *
 * @package    mod_cmsvideo
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * cmsvideo module form
 *
 * @package    mod_cmsvideo
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_cmsvideo_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('cmsvideoname', 'cmsvideo'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        if (!empty($CFG->formatstringstriptags)) {
            $mform->addHelpButton('name', 'cmsvideoname', 'cmsvideo');
        }

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding source path field
        $mform->addElement('text', 'source_path', get_string('sourcepath', 'cmsvideo'), array('size' => '64'));
        $mform->setType('source_path', PARAM_TEXT);
        $mform->addRule('source_path', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('source_path', 'sourcepath', 'cmsvideo');

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * Add intro editor
     */
    protected function add_intro_editor($required = false, $customintroheader = false) {
        $required = !empty($required);
        $mform = $this->_form;

        $mform->addElement('editor', 'introeditor', get_string('moduleintro'), null, $this->get_intro_editor_options());
        $mform->setType('introeditor', PARAM_RAW);
        if ($required) {
            $mform->addRule('introeditor', get_string('required'), 'required', null, 'client');
        }

        // If the 'Use course default' checkbox is ticked, disable the intro editor.
        if ($this->_features->intro) {
            $mform->disabledIf('introeditor', 'showdescription');
        }
    }

    /**
     * Get intro editor options
     */
    protected function get_intro_editor_options() {
        $introductionoptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $this->get_course()->maxbytes, 'trusttext' => false, 'noclean' => true);
        return $introductionoptions;
    }


}
