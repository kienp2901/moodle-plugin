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
 * Reading Flow configuration form
 *
 * @package mod_readingflow
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_readingflow_mod_form extends moodleform_mod {
    
    function definition() {
        global $CFG;

        $mform = $this->_form;

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        
        // Name field
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        
        // Intro field
        $this->standard_intro_elements();

        // Content field (editor like intro)
        $mform->addElement('header', 'contentheader', get_string('content', 'mod_readingflow'));
        
        $mform->addElement('editor', 'content', get_string('content', 'mod_readingflow'), null, 
            array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->context, 
            'subdirs' => true, 'enable_filemanagement' => true));
        $mform->setType('content', PARAM_RAW);
        $mform->addHelpButton('content', 'content', 'mod_readingflow');

        // Lumos fields
        $mform->addElement('header', 'lumosheader', get_string('lumos_reading_id', 'mod_readingflow'));
        
        $mform->addElement('text', 'lumos_reading_id', get_string('lumos_reading_id', 'mod_readingflow'), array('size'=>'10'));
        $mform->setType('lumos_reading_id', PARAM_INT);
        $mform->addHelpButton('lumos_reading_id', 'lumos_reading_id', 'mod_readingflow');
        
        $mform->addElement('text', 'lumos_reading_slug', get_string('lumos_reading_slug', 'mod_readingflow'), array('size'=>'48'));
        $mform->setType('lumos_reading_slug', PARAM_TEXT);
        $mform->addRule('lumos_reading_slug', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('lumos_reading_slug', 'lumos_reading_slug', 'mod_readingflow');

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();
    }

    /**
     * Enforce defaults here.
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues) {
        if (!empty($defaultvalues['id']) || !empty($this->current->instance)) {
            // Prepare content editor field for editing, similar to how intro is handled
            $draftitemid = file_get_submitted_draft_itemid('content');
            $content = isset($defaultvalues['content']) ? $defaultvalues['content'] : '';
            $contentformat = isset($defaultvalues['contentformat']) ? $defaultvalues['contentformat'] : FORMAT_HTML;
            
            $defaultvalues['content'] = array();
            $defaultvalues['content']['format'] = $contentformat;
            $defaultvalues['content']['text'] = file_prepare_draft_area($draftitemid, 
                $this->context->id, 'mod_readingflow', 'content', 0, 
                array('noclean' => true, 'subdirs' => true, 'enable_filemanagement' => true), 
                $content);
            $defaultvalues['content']['itemid'] = $draftitemid;
        }
    }

    /**
     * Return submitted data if properly submitted or returns NULL if validation fails or
     * if there is no submitted data.
     *
     * @return object submitted data; NULL if not valid or not submitted or cancelled
     */
    function get_data() {
        $data = parent::get_data();
        if ($data) {
            // Process content editor field
            if (!empty($data->content) && is_array($data->content)) {
                $data->contentformat = $data->content['format'];
                $data->content = $data->content['text'];
            } else if (!isset($data->content)) {
                $data->content = '';
                $data->contentformat = FORMAT_HTML;
            }
        }
        return $data;
    }
}

