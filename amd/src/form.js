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
        
        // Handle step type changes for existing and new steps
        $(document).on('change', 'select[name^="type["]', function() {
            handleStepTypeChange($(this));
        });
        
        // Handle add step button (Moodle's built-in repeatable elements)
        $(document).on('click', 'input[name="steps_add"]', function() {
            // Wait a bit for Moodle to add the new element
            setTimeout(function() {
                initializeNewSteps();
            }, 100);
        });
        
        // Initialize existing steps
        initializeExistingSteps();
    };

    /**
     * Handle step type change
     */
    function handleStepTypeChange($select) {
        var $stepContainer = $select.closest('.fitem');
        var selectedType = $select.val();
        var stepIndex = getStepIndex($select);
        
        console.log('Step type changed:', selectedType, 'for step:', stepIndex);
        
        // Hide all content fields first
        $stepContainer.find('.step-content-field').hide();
        
        // Show relevant fields based on type
        if (selectedType === 'vocabulary') {
            $stepContainer.find('.vocabulary-fields').show();
            $stepContainer.find('.text-fields').hide();
        } else if (selectedType === 'text') {
            $stepContainer.find('.text-fields').show();
            $stepContainer.find('.vocabulary-fields').hide();
        }
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
        // Add classes to help identify different field types
        $('select[name^="type["]').each(function() {
            var $select = $(this);
            var $stepContainer = $select.closest('.fitem');
            var stepIndex = getStepIndex($select);
            
            // Add step index as data attribute
            $stepContainer.attr('data-step-index', stepIndex);
            
            // Add classes to content fields
            $stepContainer.find('textarea[name^="step_content["]').closest('.fitem').addClass('step-content-field text-fields');
            $stepContainer.find('input[name^="term["]').closest('.fitem').addClass('step-content-field vocabulary-fields');
            $stepContainer.find('textarea[name^="definition["]').closest('.fitem').addClass('step-content-field vocabulary-fields');
            $stepContainer.find('textarea[name^="example["]').closest('.fitem').addClass('step-content-field vocabulary-fields');
            $stepContainer.find('input[name^="audio_file["]').closest('.fitem').addClass('step-content-field vocabulary-fields');
        });
    }

    // Initialize helper classes after a short delay
    setTimeout(function() {
        addHelperClasses();
    }, 500);

    return {
        init: init
    };
});
