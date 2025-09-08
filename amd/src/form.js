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
 * Step by Step module form JavaScript
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    'use strict';

    /**
     * Module initialization
     */
    var init = function() {
        console.log('Step by Step form initialized');
        
        // Initialize after DOM is ready
        $(document).ready(function() {
            // Add helper classes first
            addHelperClasses();
            
            // Initialize existing steps
            initializeExistingSteps();
            
            // Handle step type changes for existing and new steps
            $(document).on('change', 'select[name^="type["]', function() {
                handleStepTypeChange($(this));
            });
            
            // Handle add step button (Moodle's built-in repeatable elements)
            $(document).on('click', 'input[name="steps_add"]', function() {
                // Wait a bit for Moodle to add the new element
                setTimeout(function() {
                    initializeNewSteps();
                    updateRemoveButtons();
                }, 200);
            });
            
            // Handle custom remove step button
            $(document).on('click', 'button[name^="remove_step["]', function(e) {
                e.preventDefault();
                var $button = $(this);
                var stepIndex = getStepIndex($button);
                console.log('Remove step clicked for index:', stepIndex);
                
                // Find all field containers for this specific step
                var stepFieldContainers = [
                    $('input[name="main_title[' + stepIndex + ']"]').closest('.fitem'),
                    $('input[name="sub_heading[' + stepIndex + ']"]').closest('.fitem'),
                    $('textarea[name="content_paragraphs[' + stepIndex + '][text]"]').closest('.fitem'),
                    $('input[name="term[' + stepIndex + ']"]').closest('.fitem'),
                    $('textarea[name="definition[' + stepIndex + ']"]').closest('.fitem'),
                    $('textarea[name="example[' + stepIndex + ']"]').closest('.fitem'),
                    $('input[name="audio_file[' + stepIndex + ']"]').closest('.fitem'),
                    $('select[name="response_text[' + stepIndex + ']"]').closest('.fitem'),
                    $('select[name="type[' + stepIndex + ']"]').closest('.fitem'),
                    $('button[name="remove_step[' + stepIndex + ']"]').closest('.fitem')
                ];
                
                // Remove all field containers for this step
                var removedCount = 0;
                stepFieldContainers.forEach(function($container) {
                    if ($container.length > 0) {
                        $container.remove();
                        removedCount++;
                    }
                });
                
                console.log('Removed', removedCount, 'field containers for step index:', stepIndex);
                
                // Update step indices and remove buttons
                setTimeout(function() {
                    reindexSteps();
                    updateRemoveButtons();
                }, 100);
            });
            
            // Initial update of remove buttons
            updateRemoveButtons();
        });
    };

    /**
     * Handle step type change
     */
    function handleStepTypeChange($select) {
        var selectedType = $select.val();
        var stepIndex = getStepIndex($select);
        
        console.log('Step type changed:', selectedType, 'for step:', stepIndex);
        
        // Find all fields for this specific step only
        var stepFields = {
            text: $('input[name="main_title[' + stepIndex + ']"]').closest('.fitem'),
            subHeading: $('input[name="sub_heading[' + stepIndex + ']"]').closest('.fitem'),
            contentParagraphs: $('textarea[name="content_paragraphs[' + stepIndex + '][text]"]').closest('.fitem'),
            term: $('input[name="term[' + stepIndex + ']"]').closest('.fitem'),
            definition: $('textarea[name="definition[' + stepIndex + ']"]').closest('.fitem'),
            example: $('textarea[name="example[' + stepIndex + ']"]').closest('.fitem'),
            audioFile: $('input[name="audio_file[' + stepIndex + ']"]').closest('.fitem'),
            responseText: $('select[name="response_text[' + stepIndex + ']"]').closest('.fitem')
        };
        
        // Hide all content fields for this step first
        stepFields.text.hide();
        stepFields.subHeading.hide();
        stepFields.contentParagraphs.hide();
        stepFields.term.hide();
        stepFields.definition.hide();
        stepFields.example.hide();
        stepFields.audioFile.hide();
        
        // Show relevant fields based on type
        if (selectedType === 'vocabulary') {
            // Show vocabulary fields: term, definition, example, audio_file
            stepFields.term.show();
            stepFields.definition.show();
            stepFields.example.show();
            stepFields.audioFile.show();
        } else if (selectedType === 'text') {
            // Show text fields: main_title, sub_heading, content_paragraphs
            stepFields.text.show();
            stepFields.subHeading.show();
            stepFields.contentParagraphs.show();
        }
        
        // Always show common fields (response_text)
        stepFields.responseText.show();
    }

    /**
     * Get step index from element name
     */
    function getStepIndex($element) {
        var name = $element.attr('name');
        var match = name.match(/\[(\d+)\]/);
        return match ? parseInt(match[1]) : 0;
    }

    /**
     * Initialize existing steps
     */
    function initializeExistingSteps() {
        $('select[name^="type["]').each(function() {
            handleStepTypeChange($(this));
        });
    }

    /**
     * Initialize new steps that were just added
     */
    function initializeNewSteps() {
        // Find newly added steps (they might not have been initialized yet)
        $('select[name^="type["]').each(function() {
            var $select = $(this);
            if (!$select.data('initialized')) {
                $select.data('initialized', true);
                handleStepTypeChange($select);
            }
        });
    }

    /**
     * Add CSS classes to help with styling and functionality
     */
    function addHelperClasses() {
        console.log('Adding helper classes...');
        
        // Add classes to help identify different field types
        $('select[name^="type["]').each(function() {
            var $select = $(this);
            var $stepContainer = $select.closest('.fitem');
            var stepIndex = getStepIndex($select);
            
            // Add step index as data attribute
            $stepContainer.attr('data-step-index', stepIndex);
            
            console.log('Added step index:', stepIndex);
        });
        
        // Add classes to audio file fields (filemanager doesn't support class in options)
        $('input[name^="audio_file["]').each(function() {
            $(this).closest('.fitem').addClass('stepbystep-vocabulary-field');
        });
        
        // Add classes to filemanager containers
        $('.filemanager').each(function() {
            var $filemanager = $(this);
            var $input = $filemanager.find('input[name^="audio_file["]');
            if ($input.length > 0) {
                $filemanager.closest('.fitem').addClass('stepbystep-vocabulary-field');
            }
        });
        
        console.log('Helper classes added');
    }
    
    /**
     * Reindex steps after removal
     */
    function reindexSteps() {
        console.log('Reindexing steps...');
        
        // Get all remaining type selects and reindex them
        var $typeSelects = $('select[name^="type["]');
        console.log('Found', $typeSelects.length, 'type selects to reindex');
        
        $typeSelects.each(function(newIndex) {
            var $select = $(this);
            var oldName = $select.attr('name');
            var newName = 'type[' + newIndex + ']';
            
            console.log('Reindexing', oldName, 'to', newName);
            
            // Update all field names for this step
            var stepIndex = getStepIndex($select);
            
            // Update all fields with the same step index
            $('input[name="main_title[' + stepIndex + ']"]').attr('name', 'main_title[' + newIndex + ']');
            $('input[name="sub_heading[' + stepIndex + ']"]').attr('name', 'sub_heading[' + newIndex + ']');
            $('textarea[name="content_paragraphs[' + stepIndex + '][text]"]').attr('name', 'content_paragraphs[' + newIndex + '][text]');
            $('input[name="term[' + stepIndex + ']"]').attr('name', 'term[' + newIndex + ']');
            $('textarea[name="definition[' + stepIndex + ']"]').attr('name', 'definition[' + newIndex + ']');
            $('textarea[name="example[' + stepIndex + ']"]').attr('name', 'example[' + newIndex + ']');
            $('input[name="audio_file[' + stepIndex + ']"]').attr('name', 'audio_file[' + newIndex + ']');
            $('select[name="response_text[' + stepIndex + ']"]').attr('name', 'response_text[' + newIndex + ']');
            $('button[name="remove_step[' + stepIndex + ']"]').attr('name', 'remove_step[' + newIndex + ']');
            
            // Update the type select last
            $select.attr('name', newName);
            
            console.log('Updated step', stepIndex, 'to new index', newIndex);
        });
        
        // Update the steps count field
        var newStepCount = $typeSelects.length;
        $('input[name="steps"]').val(newStepCount);
        
        console.log('Reindexed steps, new count:', newStepCount);
    }
    
    /**
     * Update remove buttons visibility based on number of steps
     */
    function updateRemoveButtons() {
        var stepCount = $('select[name^="type["]').length;
        console.log('Current step count:', stepCount);
        
        // Hide all remove buttons if only 1 step
        if (stepCount <= 1) {
            $('button[name^="remove_step["]').hide();
            console.log('Hide remove buttons - only 1 step');
        } else {
            $('button[name^="remove_step["]').show();
            console.log('Show remove buttons - multiple steps');
        }
    }

    return {
        init: init
    };
});
