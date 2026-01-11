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
 * Language strings for mod_stepbystep
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['modulename'] = 'Step by Step';
$string['modulenameplural'] = 'Step by Steps';
$string['modulename_help'] = 'The Step by Step module allows teachers to create a series of content cards that students view sequentially. Each card contains information, vocabulary, or tips that students must acknowledge before proceeding to the next step.';
$string['modulename_link'] = 'mod/stepbystep/view';
$string['stepbystep:addinstance'] = 'Add a new Step by Step activity';
$string['stepbystep:view'] = 'View Step by Step activity';
$string['stepbystep:manage'] = 'Manage Step by Step activity';
$string['pluginname'] = 'Step by Step';
$string['pluginadministration'] = 'Step by Step administration';

// Form fields
$string['name'] = 'Name';
$string['stepbystepname'] = 'Activity name';
$string['intro'] = 'Description';
$string['content'] = 'Content';
$string['step'] = 'Step';
$string['steps'] = 'Steps';
$string['addstep'] = 'Add step';
$string['removestep'] = 'Remove step';
$string['content_help'] = 'Add content steps for your lesson. Each step will be shown to students one at a time. You can add text, images, videos, or any HTML content.';

// Step types
$string['steptype'] = 'Step type';
$string['responsetext'] = 'Response text';
$string['type_text'] = 'Text/Tip';
$string['type_vocabulary'] = 'Vocabulary';
$string['type_definition'] = 'Definition';
$string['type_example'] = 'Example';
$string['type_audio'] = 'Audio';
$string['response'] = 'Response';

// Step fields
$string['title'] = 'Title';
$string['main_title'] = 'Main title';
$string['sub_heading'] = 'Sub heading';
$string['content_paragraphs'] = 'Content paragraphs';
$string['term'] = 'Term';
$string['phonetic'] = 'Phonetic';
$string['definition'] = 'Definition';
$string['example'] = 'Example';
$string['audiofile'] = 'Audio file';
$string['step_title'] = 'Step title';
$string['step_title_help'] = 'Optional title for this step';
$string['main_title_help'] = 'Main title for this text step (e.g., lesson topic)';
$string['sub_heading_help'] = 'Sub heading or specific topic for this step';
$string['content_paragraphs_help'] = 'Enter content paragraphs. Each line will be treated as a separate paragraph. You can also use JSON format for more complex content.';
$string['term_help'] = 'Enter the vocabulary term or concept';
$string['phonetic_help'] = 'Enter the phonetic transcription (e.g., /ˈæpl/ for "apple")';
$string['definition_help'] = 'Enter the definition or explanation';
$string['example_help'] = 'Enter an example sentence or usage';
$string['audiofile_help'] = 'Upload an audio file for pronunciation';
$string['responsetext_help'] = 'Text to display on the continue button (e.g., "Got it!", "Continue", "Next")';

// Button texts
$string['continue'] = 'Continue';
$string['gotit'] = 'Got it!';
$string['reasonable'] = 'Reasonable!';
$string['understand'] = 'I understand!';
$string['complete'] = 'Complete!';

// Completion messages
$string['lessoncomplete'] = 'Lesson completed!';
$string['excellent'] = 'Excellent! You have completed this lesson.';
$string['returntocourse'] = '← Return to course';

// Content types
$string['definition_label'] = 'Definition:';
$string['example_label'] = 'Example:';
$string['listenpronunciation'] = 'Listen to pronunciation';
$string['playing'] = 'Playing...';

// Completion tracking
$string['completion'] = 'Completion tracking';
$string['completion_help'] = 'If enabled, activity completion is tracked, either manually or automatically, based on certain conditions. Multiple conditions may be set if desired. If so, the activity will only be considered complete when ALL conditions are met.';
$string['completionview'] = 'Student must view all steps to complete this activity';
$string['completionview_desc'] = 'Student must view all steps to complete this activity';

// Vocabulary generation
$string['vocabulary_generation'] = 'Vocabulary Generation';
$string['vocabulary_topic'] = 'Topic';
$string['vocabulary_topic_help'] = 'Enter the topic or theme for vocabulary generation (e.g., "IELTS Reading", "Business English")';
$string['vocabulary_count'] = 'Number of vocabulary terms';
$string['vocabulary_count_help'] = 'Enter the number of vocabulary terms to generate (1-50)';
$string['exclude_existing_vocab'] = 'Exclude existing vocabulary';
$string['exclude_existing_vocab_label'] = 'Skip already generated vocabulary';
$string['exclude_existing_vocab_help'] = 'Check this option to exclude existing vocabulary terms from generation to avoid duplicates';
$string['excluded_vocab_list'] = 'Select vocabulary to exclude';
$string['excluded_vocab_list_help'] = 'Search and select existing vocabulary terms that should be excluded from the generation process. You can select multiple items.';
$string['excluded_vocab_list_placeholder'] = 'Type to search vocabulary...';
$string['quiz_generation'] = 'Quiz component type';
$string['quiz_generation_help'] = 'Select the type of quiz component to generate for testing vocabulary. Choose "No quiz" if you only want vocabulary steps without testing.';
$string['quiz_type_none'] = 'No quiz component';
$string['quiz_type_single_choice'] = 'Single-choice questions';
$string['quiz_type_short_answer'] = 'Short-answer questions';
$string['quiz_type_random'] = 'Random mix of question types';
$string['level'] = 'Level';
$string['level_help'] = 'Select the level type for vocabulary generation: Vocabulary for individual terms, or Collection for grouped vocabulary sets';
$string['generate_vocabulary'] = 'Generate Vocabulary';
$string['generate_vocabulary_help'] = 'Click to automatically generate vocabulary terms based on the activity name';

// Text generation
$string['text_generation'] = 'Text Generation';
$string['generate_question_from_text'] = 'Generate Questions from Text';
$string['generate_question_from_text_help'] = 'Click to generate questions from existing text content in the steps';
$string['generating'] = 'Generating...';
$string['generation_success'] = 'Successfully generated {$a} vocabulary terms';
$string['generation_error'] = 'Error generating vocabulary: {$a}';
$string['validation_error'] = 'Please enter both activity name and vocabulary count before generating';

// Error messages
$string['error_no_content'] = 'You must provide at least one content step.';
$string['error_invalid_type'] = 'Invalid step type selected.';
$string['error_missing_fields'] = 'Required fields are missing for this step type.';
$string['apivocabularyerror'] = 'API error when processing vocabulary: {$a}';
