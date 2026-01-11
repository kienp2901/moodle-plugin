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
     * Current step count for form processing
     * @var int
     */
    public $currentStepCount = 0;

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

        // Adding vocabulary generation section after intro
        $mform->addElement('header', 'vocabulary_generation', get_string('vocabulary_generation', 'mod_stepbystep'));
        
        // Topic field
        $mform->addElement('text', 'vocabulary_topic', get_string('vocabulary_topic', 'mod_stepbystep'), 
            array('size' => 50));
        $mform->setType('vocabulary_topic', PARAM_TEXT);
        $mform->addHelpButton('vocabulary_topic', 'vocabulary_topic', 'mod_stepbystep');
        
        // Vocabulary count field
        $mform->addElement('text', 'vocabulary_count', get_string('vocabulary_count', 'mod_stepbystep'), 
            array('size' => 5, 'maxlength' => 2));
        $mform->setType('vocabulary_count', PARAM_INT);
        $mform->addRule('vocabulary_count', get_string('vocabulary_count_help', 'mod_stepbystep'), 'numeric', null, 'client');
        $mform->addRule('vocabulary_count', get_string('vocabulary_count_help', 'mod_stepbystep'), 'minlength', 1, 'client');
        $mform->addRule('vocabulary_count', get_string('vocabulary_count_help', 'mod_stepbystep'), 'maxlength', 2, 'client');
        $mform->addHelpButton('vocabulary_count', 'vocabulary_count', 'mod_stepbystep');
        $mform->setDefault('vocabulary_count', 10);
        
        // Checkbox to exclude existing vocabulary
        $mform->addElement('advcheckbox', 'exclude_existing_vocab', 
            get_string('exclude_existing_vocab', 'mod_stepbystep'), 
            get_string('exclude_existing_vocab_label', 'mod_stepbystep'),
            array('id' => 'id_exclude_existing_vocab'));
        $mform->addHelpButton('exclude_existing_vocab', 'exclude_existing_vocab', 'mod_stepbystep');
        $mform->setDefault('exclude_existing_vocab', 0);
        
        // Autocomplete multiselect for existing vocabulary (loaded from API)
        $existingVocabOptions = $this->get_existing_topics_from_api();
        
        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('select', 'core'),
            'placeholder' => get_string('excluded_vocab_list_placeholder', 'mod_stepbystep')
        );
        
        $mform->addElement('autocomplete', 'excluded_vocab_list', 
            get_string('excluded_vocab_list', 'mod_stepbystep'), 
            $existingVocabOptions,
            $options);
        $mform->addHelpButton('excluded_vocab_list', 'excluded_vocab_list', 'mod_stepbystep');
        $mform->hideIf('excluded_vocab_list', 'exclude_existing_vocab', 'notchecked');
        
        // Quiz component generation type
        $quizOptions = array(
            '1' => get_string('quiz_type_none', 'mod_stepbystep'),
            '2' => get_string('quiz_type_single_choice', 'mod_stepbystep'),
            '3' => get_string('quiz_type_short_answer', 'mod_stepbystep'),
            '4' => get_string('quiz_type_random', 'mod_stepbystep')
        );
        $mform->addElement('select', 'quiz_generation', 
            get_string('quiz_generation', 'mod_stepbystep'), 
            $quizOptions,
            array('id' => 'id_quiz_generation'));
        $mform->addHelpButton('quiz_generation', 'quiz_generation', 'mod_stepbystep');
        $mform->setDefault('quiz_generation', '1'); // Default: no quiz
        $mform->setType('quiz_generation', PARAM_TEXT); // Explicitly set as text to avoid required
        
        // Level field
        $levelOptions = array(
            '1' => 'Vocabulary',
            '2' => 'Collaction'
        );
        $mform->addElement('select', 'level', 
            get_string('level', 'mod_stepbystep'), 
            $levelOptions,
            array('id' => 'id_level'));
        $mform->addHelpButton('level', 'level', 'mod_stepbystep');
        $mform->setDefault('level', '1'); // Default: vocabulary
        $mform->setType('level', PARAM_TEXT);
        
        // Generate vocabulary button
        $mform->addElement('button', 'generate_vocabulary', get_string('generate_vocabulary', 'mod_stepbystep'), 
            array('id' => 'generate_vocabulary_btn'));
        $mform->addHelpButton('generate_vocabulary', 'generate_vocabulary', 'mod_stepbystep');
        
        // Adding the "text generation" section for text type steps
        $mform->addElement('header', 'text_generation', get_string('text_generation', 'mod_stepbystep'));
        
        // Generate question from text button
        $mform->addElement('button', 'generate_question_from_text', get_string('generate_question_from_text', 'mod_stepbystep'), 
            array('id' => 'generate_question_from_text_btn'));
        $mform->addHelpButton('generate_question_from_text', 'generate_question_from_text', 'mod_stepbystep');

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
            array('rows' => 40, 'cols' => 80, 'class' => 'stepbystep-text-field'), $this->get_editor_options());
        
        // Term field (for vocabulary type)
        $repeatarray[] = $mform->createElement('text', 'term', get_string('term', 'mod_stepbystep'), 
            array('size' => 50, 'class' => 'stepbystep-vocabulary-field'));
        
        // Phonetic field (for vocabulary type)
        $repeatarray[] = $mform->createElement('text', 'phonetic', get_string('phonetic', 'mod_stepbystep'), 
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
        
        // Response text field (common for both types) - changed to select dropdown
        $responseElement = $mform->createElement('select', 'response_text', get_string('responsetext', 'mod_stepbystep'), 
            array(
                'tiep_theo' => 'Ti&#7871;p theo',
                'hop_ly' => 'H&#7907;p l&#253;!',
                'duoc_roi' => '&#272;&#432;&#7907;c r&#7891;i!',
                'dong_y' => '&#272;&#7891;ng &#253;!',
                'hieu_roi' => 'Hi&#7875;u r&#7891;i!'
            ), array('class' => 'stepbystep-common-field'));
        $responseElement->setSelected('tiep_theo');
        $repeatarray[] = $responseElement;
        
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
        // Use empty array for defaults to prevent copying data when adding new steps
        $defaults = array();
        
        // Get current step count from form to handle newly added steps
        $currentStepCount = optional_param('steps', $initialSteps, PARAM_INT);
        if ($currentStepCount < $initialSteps) {
            $currentStepCount = $initialSteps;
        }
        
        // Debug: Check if we're adding steps
        $steps_add = optional_param('steps_add', '', PARAM_TEXT);
        if (!empty($steps_add)) {
            error_log('Step by Step Form: steps_add parameter detected = ' . $steps_add);
        }
        
        error_log('Step by Step Form: definition - Initial steps: ' . $initialSteps . ', Current steps: ' . $currentStepCount);
        
        $this->repeat_elements($repeatarray, $currentStepCount, $defaults, 'steps', 'steps_add', 1, 
            get_string('addstep', 'mod_stepbystep'), true);
            
        // Set the default number of steps
        $mform->setDefault('steps', $currentStepCount);
        
        // Store the current step count for later use
        $this->currentStepCount = $currentStepCount;
        
        // Force clear data for newly added steps immediately after repeat_elements
        if ($currentStepCount > $initialSteps) {
            error_log('Step by Step Form: Force clearing newly added steps after repeat_elements');
            
            // Special handling for when last step was deleted and new step added
            $lastStepWasDeleted = false;
            if ($this->current && isset($this->current->id)) {
                global $DB;
                $currentSteps = $DB->get_records('stepbystep_content', 
                    array('stepbystep_id' => $this->current->id), 'sortorder ASC');
                if ($currentSteps) {
                    $currentDbSteps = count($currentSteps);
                    if ($currentDbSteps < $initialSteps) {
                        $lastStepWasDeleted = true;
                        error_log('Step by Step Form: Last step was deleted, current DB steps: ' . $currentDbSteps . ', initial steps: ' . $initialSteps);
                    }
                }
            }
            
            for ($i = $initialSteps; $i < $currentStepCount; $i++) {
                // Force clear all fields for newly added steps
                $mform->setDefault('main_title[' . $i . ']', '');
                $mform->setDefault('sub_heading[' . $i . ']', '');
                $mform->setDefault('content_paragraphs[' . $i . '][text]', '');
                $mform->setDefault('content_paragraphs[' . $i . '][format]', FORMAT_HTML);
                $mform->setDefault('term[' . $i . ']', '');
                $mform->setDefault('phonetic[' . $i . ']', '');
                $mform->setDefault('definition[' . $i . ']', '');
                $mform->setDefault('example[' . $i . ']', '');
                $mform->setDefault('audio_file[' . $i . ']', '');
                $mform->setDefault('response_text[' . $i . ']', 'tiep_theo');
                $mform->setDefault('type[' . $i . ']', 'text');
                
                // Also clear any potential hidden fields
                $mform->setDefault('main_title[' . $i . ']', null);
                $mform->setDefault('sub_heading[' . $i . ']', null);
                $mform->setDefault('content_paragraphs[' . $i . '][text]', null);
                $mform->setDefault('term[' . $i . ']', null);
                $mform->setDefault('phonetic[' . $i . ']', null);
                $mform->setDefault('definition[' . $i . ']', null);
                $mform->setDefault('example[' . $i . ']', null);
                $mform->setDefault('audio_file[' . $i . ']', null);
                
                // Force set empty values again
                $mform->setDefault('main_title[' . $i . ']', '');
                $mform->setDefault('sub_heading[' . $i . ']', '');
                $mform->setDefault('content_paragraphs[' . $i . '][text]', '');
                $mform->setDefault('content_paragraphs[' . $i . '][format]', FORMAT_HTML);
                $mform->setDefault('term[' . $i . ']', '');
                $mform->setDefault('phonetic[' . $i . ']', '');
                $mform->setDefault('definition[' . $i . ']', '');
                $mform->setDefault('example[' . $i . ']', '');
                $mform->setDefault('audio_file[' . $i . ']', '');
                $mform->setDefault('response_text[' . $i . ']', 'tiep_theo');
                $mform->setDefault('type[' . $i . ']', 'text');
                
                // Extra aggressive clearing if last step was deleted
                if ($lastStepWasDeleted) {
                    error_log('Step by Step Form: Extra aggressive clearing for step ' . $i . ' (last step was deleted)');
                    for ($k = 0; $k < 5; $k++) {
                        $mform->setDefault('content_paragraphs[' . $i . '][text]', '');
                        $mform->setDefault('content_paragraphs[' . $i . '][text]', null);
                        $mform->setDefault('content_paragraphs[' . $i . '][text]', '');
                    }
                }
                
                error_log('Step by Step Form: Force cleared newly added step ' . $i);
            }
        }
        
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
        
        // Get the number of existing steps from DB
        $existingStepsCount = 0;
        $steps = array();
        
        if ($this->current && isset($this->current->id)) {
            global $DB;
            $steps = $DB->get_records('stepbystep_content', 
                array('stepbystep_id' => $this->current->id), 'sortorder ASC');
            if ($steps) {
                $existingStepsCount = count($steps);
                error_log('Step by Step Form: definition_after_data - Found ' . $existingStepsCount . ' existing steps');
            }
        }
        
        // Get current step count from form (including newly added steps)
        $currentStepCount = optional_param('steps', $existingStepsCount, PARAM_INT);
        if ($currentStepCount < $existingStepsCount) {
            $currentStepCount = $existingStepsCount;
        }
        
        // Use the stored current step count if available (from definition())
        if (isset($this->currentStepCount) && $this->currentStepCount > $currentStepCount) {
            $currentStepCount = $this->currentStepCount;
            error_log('Step by Step Form: definition_after_data - Using stored current step count: ' . $currentStepCount);
        }
        
        error_log('Step by Step Form: Current step count: ' . $currentStepCount . ', Existing steps: ' . $existingStepsCount);
        
        // Set the default number of steps
        $this->_form->setDefault('steps', $currentStepCount);
        
        // Only set defaults for EXISTING steps (not newly added ones)
        if (!empty($steps)) {
                $formIndex = 0; // Form index starts from 0
                foreach ($steps as $dbIndex => $step) {
                // Only process existing steps, not newly added ones
                if ($formIndex < $existingStepsCount) {
                    error_log('Step by Step Form: Processing EXISTING step DB index ' . $dbIndex . ' -> Form index ' . $formIndex . ' with type ' . $step->type);
                    
                    $this->_form->setDefault('type[' . $formIndex . ']', $step->type);
                    // Set response_text default, use existing value or default to 'tiep_theo'
                    $responseValue = !empty($step->response_text) ? $step->response_text : 'tiep_theo';
                    // Map old values to new keys if needed
                    $responseMapping = array(
                        'Tiếp theo' => 'tiep_theo',
                        'Hợp lý!' => 'hop_ly',
                        'Được rồi!' => 'duoc_roi',
                        'Đồng ý!' => 'dong_y',
                        'Hiểu rồi!' => 'hieu_roi',
                        'Ti&#7871;p theo' => 'tiep_theo',
                        'H&#7907;p l&#253;!' => 'hop_ly',
                        '&#272;&#432;&#7907;c r&#7891;i!' => 'duoc_roi',
                        '&#272;&#7891;ng &#253;!' => 'dong_y',
                        'Hi&#7875;u r&#7891;i!' => 'hieu_roi'
                    );
                    if (isset($responseMapping[$responseValue])) {
                        $responseValue = $responseMapping[$responseValue];
                    }
                    $this->_form->setDefault('response_text[' . $formIndex . ']', $responseValue);
                    
                    // Set fields based on step type
                    if ($step->type === 'text') {
                        $this->_form->setDefault('main_title[' . $formIndex . ']', $step->main_title);
                        $this->_form->setDefault('sub_heading[' . $formIndex . ']', $step->sub_heading);
                        $this->_form->setDefault('content_paragraphs[' . $formIndex . '][text]', $step->content_paragraphs);
                        $this->_form->setDefault('content_paragraphs[' . $formIndex . '][format]', FORMAT_HTML);
                        // Set empty values for vocabulary fields to avoid conflicts
                        $this->_form->setDefault('term[' . $formIndex . ']', '');
                        $this->_form->setDefault('phonetic[' . $formIndex . ']', '');
                        $this->_form->setDefault('definition[' . $formIndex . ']', '');
                        $this->_form->setDefault('example[' . $formIndex . ']', '');
                        $this->_form->setDefault('audio_file[' . $formIndex . ']', '');
                    } else if ($step->type === 'vocabulary') {
                        $this->_form->setDefault('term[' . $formIndex . ']', $step->term);
                        $this->_form->setDefault('phonetic[' . $formIndex . ']', isset($step->phonetic) ? $step->phonetic : '');
                        $this->_form->setDefault('definition[' . $formIndex . ']', $step->definition);
                        $this->_form->setDefault('example[' . $formIndex . ']', $step->example);
                        $this->_form->setDefault('audio_file[' . $formIndex . ']', $step->audio_file);
                        // Set empty values for text fields to avoid conflicts
                        $this->_form->setDefault('main_title[' . $formIndex . ']', '');
                        $this->_form->setDefault('sub_heading[' . $formIndex . ']', '');
                        $this->_form->setDefault('content_paragraphs[' . $formIndex . '][text]', '');
                        $this->_form->setDefault('content_paragraphs[' . $formIndex . '][format]', FORMAT_HTML);
                    }
                    
                    error_log('Step by Step Form: Set defaults for EXISTING step ' . $formIndex . ' with type ' . $step->type);
                }
                $formIndex++;
            }
            
            error_log('Step by Step Form: Successfully set defaults for ' . $existingStepsCount . ' existing steps');
        }
        
        // Explicitly clear data for newly added steps (steps beyond existing count)
        $this->clearNewlyAddedStepsData($existingStepsCount);
        
        // Additional aggressive clearing for newly added steps
        if ($currentStepCount > $existingStepsCount) {
            error_log('Step by Step Form: definition_after_data - Additional aggressive clearing');
            
            // Check if last step was deleted (special case)
            $lastStepWasDeleted = false;
            if ($this->current && isset($this->current->id)) {
                global $DB;
                $currentSteps = $DB->get_records('stepbystep_content', 
                    array('stepbystep_id' => $this->current->id), 'sortorder ASC');
                if ($currentSteps) {
                    $currentDbSteps = count($currentSteps);
                    if ($currentDbSteps < $existingStepsCount) {
                        $lastStepWasDeleted = true;
                        error_log('Step by Step Form: definition_after_data - Last step was deleted, current DB steps: ' . $currentDbSteps . ', existing steps: ' . $existingStepsCount);
                    }
                }
            }
            
            for ($i = $existingStepsCount; $i < $currentStepCount; $i++) {
                // Clear all fields multiple times to ensure they are empty
                $clearIterations = $lastStepWasDeleted ? 5 : 3; // More iterations if last step was deleted
                
                for ($j = 0; $j < $clearIterations; $j++) {
                    $this->_form->setDefault('main_title[' . $i . ']', '');
                    $this->_form->setDefault('sub_heading[' . $i . ']', '');
                    $this->_form->setDefault('content_paragraphs[' . $i . '][text]', '');
                    $this->_form->setDefault('content_paragraphs[' . $i . '][format]', FORMAT_HTML);
                    $this->_form->setDefault('term[' . $i . ']', '');
                    $this->_form->setDefault('phonetic[' . $i . ']', '');
                    $this->_form->setDefault('definition[' . $i . ']', '');
                    $this->_form->setDefault('example[' . $i . ']', '');
                    $this->_form->setDefault('audio_file[' . $i . ']', '');
                    $this->_form->setDefault('response_text[' . $i . ']', 'tiep_theo');
                    $this->_form->setDefault('type[' . $i . ']', 'text');
                    
                    // Also set to null and back to empty
                    $this->_form->setDefault('main_title[' . $i . ']', null);
                    $this->_form->setDefault('sub_heading[' . $i . ']', null);
                    $this->_form->setDefault('content_paragraphs[' . $i . '][text]', null);
                    $this->_form->setDefault('term[' . $i . ']', null);
                    $this->_form->setDefault('phonetic[' . $i . ']', null);
                    $this->_form->setDefault('definition[' . $i . ']', null);
                    $this->_form->setDefault('example[' . $i . ']', null);
                    $this->_form->setDefault('audio_file[' . $i . ']', null);
                }
                
                // Extra special handling for content_paragraphs if last step was deleted
                if ($lastStepWasDeleted) {
                    error_log('Step by Step Form: definition_after_data - Extra special clearing for content_paragraphs[' . $i . ']');
                    for ($k = 0; $k < 10; $k++) {
                        $this->_form->setDefault('content_paragraphs[' . $i . '][text]', '');
                        $this->_form->setDefault('content_paragraphs[' . $i . '][text]', null);
                        $this->_form->setDefault('content_paragraphs[' . $i . '][text]', '');
                        $this->_form->setDefault('content_paragraphs[' . $i . '][format]', FORMAT_HTML);
                    }
                }
                
                error_log('Step by Step Form: definition_after_data - Aggressively cleared step ' . $i . ($lastStepWasDeleted ? ' (last step was deleted)' : ''));
            }
        }
        
        // Add JavaScript for form functionality
        global $PAGE;
        $PAGE->requires->js_call_amd('mod_stepbystep/form', 'init');
    }
    
    /**
     * Clear data for newly added steps to prevent copying old data
     */
    private function clearNewlyAddedStepsData($existingStepsCount) {
        // Get current number of steps from form
        $currentSteps = optional_param('steps', $existingStepsCount, PARAM_INT);
        
        // Use the stored current step count if available (from definition())
        if (isset($this->currentStepCount) && $this->currentStepCount > $currentSteps) {
            $currentSteps = $this->currentStepCount;
            error_log('Step by Step Form: clearNewlyAddedStepsData - Using stored current step count: ' . $currentSteps);
        }
        
        // If we have more steps than existing, clear the new ones
        if ($currentSteps > $existingStepsCount) {
            error_log('Step by Step Form: Clearing data for ' . ($currentSteps - $existingStepsCount) . ' newly added steps');
            
            for ($i = $existingStepsCount; $i < $currentSteps; $i++) {
                // Clear all fields for newly added steps with explicit empty values
                $this->_form->setDefault('main_title[' . $i . ']', '');
                $this->_form->setDefault('sub_heading[' . $i . ']', '');
                $this->_form->setDefault('content_paragraphs[' . $i . '][text]', '');
                $this->_form->setDefault('content_paragraphs[' . $i . '][format]', FORMAT_HTML);
                $this->_form->setDefault('term[' . $i . ']', '');
                $this->_form->setDefault('phonetic[' . $i . ']', '');
                $this->_form->setDefault('definition[' . $i . ']', '');
                $this->_form->setDefault('example[' . $i . ']', '');
                $this->_form->setDefault('audio_file[' . $i . ']', '');
                $this->_form->setDefault('response_text[' . $i . ']', 'tiep_theo');
                $this->_form->setDefault('type[' . $i . ']', 'text'); // Default to text type
                
                // Also clear any potential hidden fields or cached values
                $this->_form->setDefault('main_title[' . $i . ']', null);
                $this->_form->setDefault('sub_heading[' . $i . ']', null);
                $this->_form->setDefault('content_paragraphs[' . $i . '][text]', null);
                $this->_form->setDefault('term[' . $i . ']', null);
                $this->_form->setDefault('phonetic[' . $i . ']', null);
                $this->_form->setDefault('definition[' . $i . ']', null);
                $this->_form->setDefault('example[' . $i . ']', null);
                $this->_form->setDefault('audio_file[' . $i . ']', null);
                
                // Force set empty values again to override any cached values
                $this->_form->setDefault('main_title[' . $i . ']', '');
                $this->_form->setDefault('sub_heading[' . $i . ']', '');
                $this->_form->setDefault('content_paragraphs[' . $i . '][text]', '');
                $this->_form->setDefault('content_paragraphs[' . $i . '][format]', FORMAT_HTML);
                $this->_form->setDefault('term[' . $i . ']', '');
                $this->_form->setDefault('phonetic[' . $i . ']', '');
                $this->_form->setDefault('definition[' . $i . ']', '');
                $this->_form->setDefault('example[' . $i . ']', '');
                $this->_form->setDefault('audio_file[' . $i . ']', '');
                $this->_form->setDefault('response_text[' . $i . ']', 'tiep_theo');
                $this->_form->setDefault('type[' . $i . ']', 'text');
                
                error_log('Step by Step Form: Cleared data for newly added step ' . $i);
            }
            
            error_log('Step by Step Form: Successfully cleared all newly added steps data');
        }
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
        
        // Set default vocabulary count
        if (!isset($defaultvalues['vocabulary_count'])) {
            $defaultvalues['vocabulary_count'] = 10;
        }
        
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
                $defaultvalues['phonetic'] = array();
                $defaultvalues['definition'] = array();
                $defaultvalues['example'] = array();
                $defaultvalues['audio_file'] = array();
                $defaultvalues['response_text'] = array();
                
                // Get current step count from form (including newly added steps)
                $currentStepCount = optional_param('steps', count($steps), PARAM_INT);
                $existingStepsCount = count($steps);
                
                // Use the stored current step count if available (from definition())
                if (isset($this->currentStepCount) && $this->currentStepCount > $currentStepCount) {
                    $currentStepCount = $this->currentStepCount;
                    error_log('Step by Step Form: data_preprocessing - Using stored current step count: ' . $currentStepCount);
                }
                
                error_log('Step by Step Form: data_preprocessing - Existing steps: ' . $existingStepsCount . ', Current steps: ' . $currentStepCount);
                
                $index = 0;
                foreach ($steps as $step) {
                    // Only set data for EXISTING steps, not newly added ones
                    if ($index < $existingStepsCount) {
                    $defaultvalues['type'][$index] = $step->type;
                    // Set response_text default, use existing value or default to 'tiep_theo'
                    $responseValue = !empty($step->response_text) ? $step->response_text : 'tiep_theo';
                    // Map old values to new keys if needed
                    $responseMapping = array(
                        'Tiếp theo' => 'tiep_theo',
                        'Hợp lý!' => 'hop_ly',
                        'Được rồi!' => 'duoc_roi',
                        'Đồng ý!' => 'dong_y',
                        'Hiểu rồi!' => 'hieu_roi',
                        'Ti&#7871;p theo' => 'tiep_theo',
                        'H&#7907;p l&#253;!' => 'hop_ly',
                        '&#272;&#432;&#7907;c r&#7891;i!' => 'duoc_roi',
                        '&#272;&#7891;ng &#253;!' => 'dong_y',
                        'Hi&#7875;u r&#7891;i!' => 'hieu_roi'
                    );
                    if (isset($responseMapping[$responseValue])) {
                        $responseValue = $responseMapping[$responseValue];
                    }
                    $defaultvalues['response_text'][$index] = $responseValue;
                    
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
                        $defaultvalues['phonetic'][$index] = '';
                        $defaultvalues['definition'][$index] = '';
                        $defaultvalues['example'][$index] = '';
                        $defaultvalues['audio_file'][$index] = '';
                    } else if ($step->type === 'vocabulary') {
                        $defaultvalues['term'][$index] = $step->term;
                        $defaultvalues['phonetic'][$index] = isset($step->phonetic) ? $step->phonetic : '';
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
                    
                        error_log('Step by Step Form: Set data for EXISTING step ' . $index . ' with type ' . $step->type);
                    }
                    $index++;
                }
                
                // For newly added steps (beyond existing count), set empty values
                for ($i = $existingStepsCount; $i < $currentStepCount; $i++) {
                    $defaultvalues['type'][$i] = 'text';
                    $defaultvalues['main_title'][$i] = '';
                    $defaultvalues['sub_heading'][$i] = '';
                    $defaultvalues['content_paragraphs'][$i] = array(
                        'text' => '',
                        'format' => FORMAT_HTML
                    );
                    $defaultvalues['term'][$i] = '';
                    $defaultvalues['phonetic'][$i] = '';
                    $defaultvalues['definition'][$i] = '';
                    $defaultvalues['example'][$i] = '';
                    $defaultvalues['audio_file'][$i] = '';
                    $defaultvalues['response_text'][$i] = 'tiep_theo';
                    
                    // Extra special handling if this is after a last step deletion
                    $lastStepWasDeleted = ($currentStepCount > $existingStepsCount) && ($existingStepsCount < count($steps));
                    if ($lastStepWasDeleted) {
                        error_log('Step by Step Form: data_preprocessing - Extra special clearing for step ' . $i . ' (last step was deleted)');
                        // Set multiple times to ensure it's really empty
                        for ($j = 0; $j < 3; $j++) {
                            $defaultvalues['content_paragraphs'][$i] = array(
                                'text' => '',
                                'format' => FORMAT_HTML
                            );
                            $defaultvalues['main_title'][$i] = '';
                            $defaultvalues['sub_heading'][$i] = '';
                        }
                    }
                    
                    error_log('Step by Step Form: Set EMPTY data for NEWLY ADDED step ' . $i . ($lastStepWasDeleted ? ' (last step was deleted)' : ''));
                }
                
                // Set the number of repeat elements to match current step count
                $defaultvalues['steps'] = $currentStepCount;
                
                // Debug: Log the defaultvalues
                error_log('Step by Step Form: Set steps count to ' . $currentStepCount);
                error_log('Step by Step Form: Default values keys: ' . implode(', ', array_keys($defaultvalues)));
                
                // Also set the form defaults directly
                $this->_form->setDefault('steps', $currentStepCount);
                error_log('Step by Step Form: Set form default steps to ' . $currentStepCount);
            } else {
                error_log('Step by Step Form: No steps found in database');
            }
        } else {
            error_log('Step by Step Form: No current instance or ID');
        }
    }

    /**
     * Set form data and ensure newly added steps are empty
     *
     * @param array|object $data
     * @return void
     */
    public function set_data($data) {
        // Get existing steps count before setting data
        $existingStepsCount = 0;
        if ($this->current && isset($this->current->id)) {
            global $DB;
            $steps = $DB->get_records('stepbystep_content', 
                array('stepbystep_id' => $this->current->id), 'sortorder ASC');
            if ($steps) {
                $existingStepsCount = count($steps);
            }
        }
        
        // Call parent set_data first
        parent::set_data($data);
        
        // Force clear data for newly added steps after setting data
        if (is_array($data) && isset($data['steps'])) {
            $currentStepCount = $data['steps'];
            
            // Use the stored current step count if available (from definition())
            if (isset($this->currentStepCount) && $this->currentStepCount > $currentStepCount) {
                $currentStepCount = $this->currentStepCount;
                error_log('Step by Step Form: set_data - Using stored current step count: ' . $currentStepCount);
            }
            
            if ($currentStepCount > $existingStepsCount) {
                error_log('Step by Step Form: set_data - Force clearing newly added steps data');
                
                for ($i = $existingStepsCount; $i < $currentStepCount; $i++) {
                    // Clear all fields for newly added steps
                    $this->_form->setDefault('main_title[' . $i . ']', '');
                    $this->_form->setDefault('sub_heading[' . $i . ']', '');
                    $this->_form->setDefault('content_paragraphs[' . $i . '][text]', '');
                    $this->_form->setDefault('content_paragraphs[' . $i . '][format]', FORMAT_HTML);
                    $this->_form->setDefault('term[' . $i . ']', '');
                    $this->_form->setDefault('phonetic[' . $i . ']', '');
                    $this->_form->setDefault('definition[' . $i . ']', '');
                    $this->_form->setDefault('example[' . $i . ']', '');
                    $this->_form->setDefault('audio_file[' . $i . ']', '');
                    $this->_form->setDefault('response_text[' . $i . ']', 'tiep_theo');
                    $this->_form->setDefault('type[' . $i . ']', 'text');
                    
                    error_log('Step by Step Form: set_data - Cleared data for newly added step ' . $i);
                }
            }
        }
    }

    /**
     * Get form data and ensure newly added steps are empty
     *
     * @return object|false
     */
    public function get_data() {
        $data = parent::get_data();
        
        if ($data) {
            // Get existing steps count
            $existingStepsCount = 0;
            if ($this->current && isset($this->current->id)) {
                global $DB;
                $steps = $DB->get_records('stepbystep_content', 
                    array('stepbystep_id' => $this->current->id), 'sortorder ASC');
                if ($steps) {
                    $existingStepsCount = count($steps);
                }
            }
            
            // Get current step count from form data (this is the actual number of steps user wants)
            $currentStepCount = isset($data->steps) ? $data->steps : $existingStepsCount;
            
            // Only use stored current step count if form data is not available (first load)
            if (!isset($data->steps) && isset($this->currentStepCount) && $this->currentStepCount > $currentStepCount) {
                $currentStepCount = $this->currentStepCount;
                error_log('Step by Step Form: get_data - Using stored current step count (first load): ' . $currentStepCount);
            }
            
            // Debug: Log step data before processing
            error_log('Step by Step Form: get_data - currentStepCount = ' . $currentStepCount);
            error_log('Step by Step Form: get_data - existingStepsCount = ' . $existingStepsCount);
            error_log('Step by Step Form: get_data - data->steps = ' . (isset($data->steps) ? $data->steps : 'not set'));
            error_log('Step by Step Form: get_data - type array count = ' . (isset($data->type) ? count($data->type) : 'not set'));
            error_log('Step by Step Form: get_data - main_title array count = ' . (isset($data->main_title) ? count($data->main_title) : 'not set'));
            
            // Ensure all arrays have the correct length
            if ($currentStepCount > 0) {
                error_log('Step by Step Form: get_data - Ensuring arrays have correct length: ' . $currentStepCount);
                
                // Initialize arrays if they don't exist or are too short
                if (!isset($data->type)) {
                    $data->type = array();
                }
                if (!isset($data->main_title)) {
                    $data->main_title = array();
                }
                if (!isset($data->sub_heading)) {
                    $data->sub_heading = array();
                }
                if (!isset($data->content_paragraphs)) {
                    $data->content_paragraphs = array();
                }
                if (!isset($data->term)) {
                    $data->term = array();
                }
                if (!isset($data->phonetic)) {
                    $data->phonetic = array();
                }
                if (!isset($data->definition)) {
                    $data->definition = array();
                }
                if (!isset($data->example)) {
                    $data->example = array();
                }
                if (!isset($data->audio_file)) {
                    $data->audio_file = array();
                }
                if (!isset($data->response_text)) {
                    $data->response_text = array();
                }
                
                // Only extend arrays if they are too short, don't replace existing data
                $currentTypeCount = count($data->type);
                if ($currentTypeCount < $currentStepCount) {
                    for ($i = $currentTypeCount; $i < $currentStepCount; $i++) {
                        $data->type[$i] = 'text';
                        $data->main_title[$i] = '';
                        $data->sub_heading[$i] = '';
                        $data->content_paragraphs[$i] = array('text' => '', 'format' => FORMAT_HTML);
                        $data->term[$i] = '';
                        $data->phonetic[$i] = '';
                        $data->definition[$i] = '';
                        $data->example[$i] = '';
                        $data->audio_file[$i] = '';
                        $data->response_text[$i] = 'tiep_theo';
                    }
                    error_log('Step by Step Form: get_data - Extended arrays from ' . $currentTypeCount . ' to ' . $currentStepCount);
                }
                
                // Set the correct steps count
                $data->steps = $currentStepCount;
                error_log('Step by Step Form: get_data - Set steps count to: ' . $currentStepCount);
            } else {
                // No steps, ensure arrays are empty or not set
                error_log('Step by Step Form: get_data - No steps to save, clearing arrays');
                unset($data->type);
                unset($data->main_title);
                unset($data->sub_heading);
                unset($data->content_paragraphs);
                unset($data->term);
                unset($data->phonetic);
                unset($data->definition);
                unset($data->example);
                unset($data->audio_file);
                unset($data->response_text);
                $data->steps = 0;
            }
            
            // Debug: Log final step data
            error_log('Step by Step Form: get_data - Final type array count = ' . (isset($data->type) ? count($data->type) : 'not set'));
            error_log('Step by Step Form: get_data - Final main_title array count = ' . (isset($data->main_title) ? count($data->main_title) : 'not set'));
        }
        
        return $data;
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
        
        // Validate vocabulary count if provided
        if (isset($data['vocabulary_count'])) {
            $count = intval($data['vocabulary_count']);
            if ($count < 1 || $count > 50) {
                $errors['vocabulary_count'] = get_string('vocabulary_count_help', 'mod_stepbystep');
            }
        }
        
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

    /**
     * Convert response text keys to display text
     *
     * @param string $key
     * @return string
     */
    private function get_response_display_text($key) {
        $responseMapping = array(
            'tiep_theo' => 'Ti&#7871;p theo',
            'hop_ly' => 'H&#7907;p l&#253;!',
            'duoc_roi' => '&#272;&#432;&#7907;c r&#7891;i!',
            'dong_y' => '&#272;&#7891;ng &#253;!',
            'hieu_roi' => 'Hi&#7875;u r&#7891;i!'
        );
        
        return isset($responseMapping[$key]) ? $responseMapping[$key] : $key;
    }

    /**
     * Get existing topics from API
     *
     * @return array Array of topics for autocomplete
     */
    private function get_existing_topics_from_api() {
        global $CFG;
        
        // Load API configuration
        $configFile = $CFG->dirroot . '/mod/stepbystep/config/config.php';
        if (file_exists($configFile)) {
            include($configFile);
        } else {
            error_log('Step by Step Form: Config file not found');
            return array();
        }
        
        // Use API domain from config
        $apiUrl = isset($apiDoamin) ? $apiDoamin . '/api/moodle/topic' : 'https://ai.ieltscheckmate.edu.vn/api/moodle/topic';
        
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Check for errors
        if ($curlError) {
            error_log('Step by Step Form: cURL error when fetching topics: ' . $curlError);
            return array();
        }
        
        if ($httpCode !== 200) {
            error_log('Step by Step Form: API returned HTTP ' . $httpCode . ' when fetching topics');
            return array();
        }
        
        // Parse JSON response
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['code']) || $result['code'] !== 200 || !isset($result['data'])) {
            error_log('Step by Step Form: Invalid API response when fetching topics');
            return array();
        }
        
        // Build options array for autocomplete
        $options = array();
        foreach ($result['data'] as $topic) {
            if (isset($topic['id']) && isset($topic['name'])) {
                // Use topic code as key and topic name as display value
                $options[$topic['id']] = $topic['name'];
            }
        }
        
        error_log('Step by Step Form: Successfully loaded ' . count($options) . ' topics from API');
        
        return $options;
    }
}
