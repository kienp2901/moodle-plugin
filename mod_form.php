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
 * Step by Step module form
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Step by Step module form class
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_stepbystep_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('stepbystepname', 'mod_stepbystep'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'stepbystepname', 'mod_stepbystep');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the "content" fieldset for steps.
        $mform->addElement('header', 'content', get_string('content', 'mod_stepbystep'));
        
        // Add help text
        $mform->addElement('static', 'contenthelp', '', get_string('content_help', 'mod_stepbystep'));
        
        // Create repeatable elements for content steps
        $repeatarray = array();
        
        // Step type selector
        $repeatarray[] = $mform->createElement('select', 'type', get_string('steptype', 'mod_stepbystep'), array(
            'vocabulary' => get_string('type_vocabulary', 'mod_stepbystep'),
            'text' => get_string('type_text', 'mod_stepbystep')
        ));
        
        // Main title field (for text type)
        $repeatarray[] = $mform->createElement('text', 'main_title', get_string('main_title', 'mod_stepbystep'), 
            array('size' => 80, 'class' => 'stepbystep-text-field'));
        
        // Sub heading field (for text type)
        $repeatarray[] = $mform->createElement('text', 'sub_heading', get_string('sub_heading', 'mod_stepbystep'), 
            array('size' => 80, 'class' => 'stepbystep-text-field'));
        
        // Content paragraphs field (for text type) - JSON format for multiple paragraphs
        $repeatarray[] = $mform->createElement('editor', 'content_paragraphs', get_string('content_paragraphs', 'mod_stepbystep'), 
            array('rows' => 8, 'cols' => 80, 'class' => 'stepbystep-text-field'), $this->get_editor_options());
        
        // Term field (for vocabulary type)
        $repeatarray[] = $mform->createElement('text', 'term', get_string('term', 'mod_stepbystep'), 
            array('size' => 50, 'class' => 'stepbystep-vocabulary-field'));
        
        // Definition field (for vocabulary type)
        $repeatarray[] = $mform->createElement('textarea', 'definition', get_string('definition', 'mod_stepbystep'), 
            array('rows' => 3, 'cols' => 50, 'class' => 'stepbystep-vocabulary-field'));
        
        // Example field (for vocabulary type)
        $repeatarray[] = $mform->createElement('textarea', 'example', get_string('example', 'mod_stepbystep'), 
            array('rows' => 3, 'cols' => 50, 'class' => 'stepbystep-vocabulary-field'));
        
        // Audio file field (for vocabulary type)
        // $repeatarray[] = $mform->createElement('filemanager', 'audio_file', get_string('audiofile', 'mod_stepbystep'), 
        //     null, $this->get_filemanager_options());
        
        // Response text field (common for both types)
        $repeatarray[] = $mform->createElement('text', 'response_text', get_string('responsetext', 'mod_stepbystep'), 
            array('size' => 50, 'class' => 'stepbystep-common-field'));
        
        // Add remove step button (will be handled by JavaScript)
        $repeatarray[] = $mform->createElement('button', 'remove_step', get_string('removestep', 'mod_stepbystep'), 
            array('class' => 'stepbystep-remove-btn'));
        
        // Always start with 1 step for new activities
        $initialSteps = 1;
        
        // Only load existing steps if editing
        if ($this->current && isset($this->current->id)) {
            global $DB;
            $steps = $DB->get_records('stepbystep_content', 
                array('stepbystep_id' => $this->current->id), 'sortorder ASC');
            if ($steps) {
                $initialSteps = count($steps);
                error_log('Step by Step Form: definition - Found ' . $initialSteps . ' steps, using as initial count');
            }
        }
        
        // Repeat elements with correct initial count
        $this->repeat_elements($repeatarray, $initialSteps, array(), 'steps', 'steps_add', 1, 
            get_string('addstep', 'mod_stepbystep'), true);
            
        // Set the default number of steps
        $mform->setDefault('steps', $initialSteps);
        
        // Add standard elements
        $this->standard_coursemodule_elements();
        
        // Add standard action buttons
        $this->add_action_buttons();
    }

    /**
     * Form definition after data has been set
     */
    public function definition_after_data() {
        parent::definition_after_data();
        
        // Get the number of existing steps to determine how many repeat elements to show
        $stepCount = 1; // Default to 1
        
        if ($this->current && isset($this->current->id)) {
            global $DB;
            $steps = $DB->get_records('stepbystep_content', 
                array('stepbystep_id' => $this->current->id), 'sortorder ASC');
            if ($steps) {
                $stepCount = count($steps);
                error_log('Step by Step Form: definition_after_data - Found ' . $stepCount . ' steps');
                
                // Set the default number of steps to match existing steps
                $this->_form->setDefault('steps', $stepCount);
                error_log('Step by Step Form: Set default steps to ' . $stepCount);
                // Set defaults for all fields
                $formIndex = 0; // Form index starts from 0
                foreach ($steps as $dbIndex => $step) {
                    error_log('Step by Step Form: Processing step DB index ' . $dbIndex . ' -> Form index ' . $formIndex . ' with type ' . $step->type);
                    
                    $this->_form->setDefault('type[' . $formIndex . ']', $step->type);
                    $this->_form->setDefault('response_text[' . $formIndex . ']', $step->response_text);
                    
                    // Set fields based on step type
                    if ($step->type === 'text') {
                        $this->_form->setDefault('main_title[' . $formIndex . ']', $step->main_title);
                        $this->_form->setDefault('sub_heading[' . $formIndex . ']', $step->sub_heading);
                        $this->_form->setDefault('content_paragraphs[' . $formIndex . '][text]', $step->content_paragraphs);
                        $this->_form->setDefault('content_paragraphs[' . $formIndex . '][format]', FORMAT_HTML);
                        // Set empty values for vocabulary fields to avoid conflicts
                        $this->_form->setDefault('term[' . $formIndex . ']', '');
                        $this->_form->setDefault('definition[' . $formIndex . ']', '');
                        $this->_form->setDefault('example[' . $formIndex . ']', '');
                        $this->_form->setDefault('audio_file[' . $formIndex . ']', '');
                    } else if ($step->type === 'vocabulary') {
                        $this->_form->setDefault('term[' . $formIndex . ']', $step->term);
                        $this->_form->setDefault('definition[' . $formIndex . ']', $step->definition);
                        $this->_form->setDefault('example[' . $formIndex . ']', $step->example);
                        $this->_form->setDefault('audio_file[' . $formIndex . ']', $step->audio_file);
                        // Set empty values for text fields to avoid conflicts
                        $this->_form->setDefault('main_title[' . $formIndex . ']', '');
                        $this->_form->setDefault('sub_heading[' . $formIndex . ']', '');
                        $this->_form->setDefault('content_paragraphs[' . $formIndex . '][text]', '');
                        $this->_form->setDefault('content_paragraphs[' . $formIndex . '][format]', FORMAT_HTML);
                    }
                    
                                    error_log('Step by Step Form: Set defaults for step ' . $formIndex . ' with type ' . $step->type);
                $formIndex++;
            }
                
                error_log('Step by Step Form: Successfully set all field defaults');
            } else {
                error_log('Step by Step Form: definition_after_data - No steps found');
            }
        } else {
            error_log('Step by Step Form: definition_after_data - No current instance or ID');
        }
        
        // Add JavaScript for form functionality
        global $PAGE;
        $PAGE->requires->js_call_amd('mod_stepbystep/form', 'init');
    }

    /**
     * Get editor options for step content
     */
    private function get_editor_options() {
        return array(
            'maxfiles' => 0,
            'maxbytes' => 0,
            'trusttext' => false,
            'forcehttps' => false
        );
    }

    /**
     * Get filemanager options for audio files
     */
    private function get_filemanager_options() {
        return array(
            'maxfiles' => 1,
            'maxbytes' => 0,
            'subdirs' => false,
            'context' => $this->context,
            'accepted_types' => array('audio')
        );
    }

    /**
     * Data preprocessing
     *
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        global $DB;
        
        if ($this->current && isset($this->current->id)) {
            // Get existing content steps
            $steps = $DB->get_records('stepbystep_content', 
                array('stepbystep_id' => $this->current->id), 'sortorder ASC');
            
            if ($steps) {
                // Debug: Log the steps found
                error_log('Step by Step Form: Found ' . count($steps) . ' steps in database');
                
                // Initialize arrays for each field
                $defaultvalues['type'] = array();
                $defaultvalues['main_title'] = array();
                $defaultvalues['sub_heading'] = array();
                $defaultvalues['content_paragraphs'] = array();
                $defaultvalues['term'] = array();
                $defaultvalues['definition'] = array();
                $defaultvalues['example'] = array();
                $defaultvalues['audio_file'] = array();
                $defaultvalues['response_text'] = array();
                
                $index = 0;
                foreach ($steps as $step) {
                    $defaultvalues['type'][$index] = $step->type;
                    $defaultvalues['response_text'][$index] = $step->response_text;
                    
                    // Set fields based on step type
                    if ($step->type === 'text') {
                        $defaultvalues['main_title'][$index] = $step->main_title;
                        $defaultvalues['sub_heading'][$index] = $step->sub_heading;
                        $defaultvalues['content_paragraphs'][$index] = array(
                            'text' => $step->content_paragraphs,
                            'format' => FORMAT_HTML
                        );
                        // Set empty values for vocabulary fields to avoid conflicts
                        $defaultvalues['term'][$index] = '';
                        $defaultvalues['definition'][$index] = '';
                        $defaultvalues['example'][$index] = '';
                        $defaultvalues['audio_file'][$index] = '';
                    } else if ($step->type === 'vocabulary') {
                        $defaultvalues['term'][$index] = $step->term;
                        $defaultvalues['definition'][$index] = $step->definition;
                        $defaultvalues['example'][$index] = $step->example;
                        $defaultvalues['audio_file'][$index] = $step->audio_file;
                        // Set empty values for text fields to avoid conflicts
                        $defaultvalues['main_title'][$index] = '';
                        $defaultvalues['sub_heading'][$index] = '';
                        $defaultvalues['content_paragraphs'][$index] = array(
                            'text' => '',
                            'format' => FORMAT_HTML
                        );
                    }
                    
                    error_log('Step by Step Form: Set defaults for step ' . $index . ' with type ' . $step->type);
                    $index++;
                }
                
                // Set the number of repeat elements to match existing steps
                $defaultvalues['steps'] = count($steps);
                
                // Debug: Log the defaultvalues
                error_log('Step by Step Form: Set steps count to ' . count($steps));
                error_log('Step by Step Form: Default values keys: ' . implode(', ', array_keys($defaultvalues)));
                
                // Also set the form defaults directly
                $this->_form->setDefault('steps', count($steps));
                error_log('Step by Step Form: Set form default steps to ' . count($steps));
            } else {
                error_log('Step by Step Form: No steps found in database');
            }
        } else {
            error_log('Step by Step Form: No current instance or ID');
        }
    }

    /**
     * Validation
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        // Check if at least one content step is provided
        if (isset($data['type']) && is_array($data['type'])) {
            $hascontent = false;
            foreach ($data['type'] as $index => $type) {
                $stepcontent = '';
                
                // Check content based on step type
                switch ($type) {
                    case 'text':
                        // if (isset($data['step_content'][$index]) && is_array($data['step_content'][$index]) && isset($data['step_content'][$index]['text'])) {
                        //     $stepcontent = $data['step_content'][$index]['text'];
                        // }
                        if (isset($data['content_paragraphs'][$index]) && is_array($data['content_paragraphs'][$index]) && isset($data['content_paragraphs'][$index]['text'])) {
                            $stepcontent = $data['content_paragraphs'][$index]['text'];
                        }
                        break;
                        
                    case 'vocabulary':
                        if (isset($data['term'][$index]) && trim($data['term'][$index]) !== '') {
                            $stepcontent = $data['term'][$index];
                        }
                        break;
                }
                
                if (trim($stepcontent) !== '') {
                    $hascontent = true;
                    break;
                }
            }
            
            if (!$hascontent) {
                $errors['steps'] = get_string('error_no_content', 'mod_stepbystep');
            }
        }
        
        return $errors;
    }
}
