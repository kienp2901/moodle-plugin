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
            
            // Fix editor sizes on page load
            fixEditorSizes();
            
            // Add content change listeners for dynamic height adjustment
            addContentChangeListeners();
            
            // Check if we need to scroll after adding step
            checkAndScrollAfterAdd();
            
            // Handle step type changes for existing and new steps
            $(document).on('change', 'select[name^="type["]', function() {
                handleStepTypeChange($(this));
            });
            
            // Handle add step button (Moodle's built-in repeatable elements)
            $(document).on('click', 'input[name="steps_add"]', function() {
                // Store scroll flag in sessionStorage for after page reload
                sessionStorage.setItem('stepbystep_scroll_after_add', 'true');
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
                
                // Clear content of new step to avoid showing old content
                clearNewStepContent($select);
                
                handleStepTypeChange($select);
            }
        });
    }
    
    /**
     * Clear content of newly added step
     */
    function clearNewStepContent($select) {
        var stepIndex = getStepIndex($select);
        
        // Clear all text fields for this step
        $('input[name="main_title[' + stepIndex + ']"]').val('');
        $('input[name="sub_heading[' + stepIndex + ']"]').val('');
        $('input[name="term[' + stepIndex + ']"]').val('');
        $('textarea[name="definition[' + stepIndex + ']"]').val('');
        $('textarea[name="example[' + stepIndex + ']"]').val('');
        $('input[name="audio_file[' + stepIndex + ']"]').val('');
        
        // Clear content paragraphs editor - more thorough approach
        var $contentEditor = $('textarea[name="content_paragraphs[' + stepIndex + '][text]"]');
        if ($contentEditor.length > 0) {
            // Clear the textarea value
            $contentEditor.val('');
            
            // Clear any hidden input that might contain the content
            $('input[name="content_paragraphs[' + stepIndex + '][text]"]').val('');
            
            // If it's a TinyMCE editor, clear the editor content
            if (typeof tinymce !== 'undefined') {
                var editorId = $contentEditor.attr('id');
                if (editorId) {
                    // Wait a bit for TinyMCE to initialize if needed
                    setTimeout(function() {
                        var editor = tinymce.get(editorId);
                        if (editor) {
                            editor.setContent('');
                            editor.save(); // Save the empty content
                        }
                    }, 100);
                }
            }
            
            // Also clear any iframe content that might be used by the editor
            $contentEditor.siblings('iframe').each(function() {
                try {
                    var iframeDoc = this.contentDocument || this.contentWindow.document;
                    if (iframeDoc && iframeDoc.body) {
                        iframeDoc.body.innerHTML = '';
                    }
                } catch (e) {
                    // Cross-origin or other iframe access issues - ignore
                }
            });
        }
        
        // Set default response text
        $('select[name="response_text[' + stepIndex + ']"]').val('tiep_theo');
        
        console.log('Cleared content for new step at index:', stepIndex);
    }
    
    /**
     * Check if we need to scroll after adding step (after page reload)
     */
    function checkAndScrollAfterAdd() {
        if (sessionStorage.getItem('stepbystep_scroll_after_add') === 'true') {
            // Clear the flag
            sessionStorage.removeItem('stepbystep_scroll_after_add');
            
            // Check if last step was deleted (special case)
            var lastStepWasDeleted = sessionStorage.getItem('stepbystep_last_step_deleted') === 'true';
            if (lastStepWasDeleted) {
                sessionStorage.removeItem('stepbystep_last_step_deleted');
                console.log('Last step was deleted, using extra aggressive clearing');
            }
            
            // Wait a bit for page to fully load, then scroll and clear only the last step
            setTimeout(function() {
                // Clear only the last step (which should be the newly added one)
                clearLastStepContent();
                
                // Extra aggressive clearing if last step was deleted
                if (lastStepWasDeleted) {
                    clearLastStepContentAggressively();
                }
                
                // Scroll to bottom
                $('html, body').animate({
                    scrollTop: $(document).height()
                }, 500);
                console.log('Scrolled to bottom after adding step');
            }, 1000);
        }
    }
    
    /**
     * Clear content of the last step (newly added step)
     */
    function clearLastStepContent() {
        // Find the last step (highest index)
        var maxIndex = -1;
        $('select[name^="type["]').each(function() {
            var stepIndex = getStepIndex($(this));
            if (stepIndex > maxIndex) {
                maxIndex = stepIndex;
            }
        });
        
        // Clear only the last step
        if (maxIndex >= 0) {
            var $lastSelect = $('select[name="type[' + maxIndex + ']"]');
            if ($lastSelect.length > 0) {
                // Force clear all content for the last step
                clearNewStepContent($lastSelect);
                
                // Additional check: if content_paragraphs still has content, force clear it
                setTimeout(function() {
                    var $contentEditor = $('textarea[name="content_paragraphs[' + maxIndex + '][text]"]');
                    if ($contentEditor.length > 0 && $contentEditor.val().trim() !== '') {
                        $contentEditor.val('');
                        
                        // Force clear TinyMCE if it exists
                        if (typeof tinymce !== 'undefined') {
                            var editorId = $contentEditor.attr('id');
                            if (editorId) {
                                var editor = tinymce.get(editorId);
                                if (editor) {
                                    editor.setContent('');
                                    editor.save();
                                }
                            }
                        }
                        
                        console.log('Force cleared content_paragraphs for step at index:', maxIndex);
                    }
                }, 200);
                
                console.log('Cleared content for newly added step at index:', maxIndex);
            }
        }
    }
    

    /**
     * Extra aggressive clearing for last step when it was deleted before adding new one
     */
    function clearLastStepContentAggressively() {
        var maxIndex = -1;
        $('select[name^="type["]').each(function() {
            var stepIndex = getStepIndex($(this));
            if (stepIndex > maxIndex) {
                maxIndex = stepIndex;
            }
        });
        
        if (maxIndex >= 0) {
            console.log('Extra aggressive clearing for step at index:', maxIndex);
            
            // Clear multiple times with different methods
            for (var i = 0; i < 5; i++) {
                setTimeout(function() {
                    // Clear all fields
                    $('input[name="main_title[' + maxIndex + ']"]').val('');
                    $('input[name="sub_heading[' + maxIndex + ']"]').val('');
                    $('input[name="term[' + maxIndex + ']"]').val('');
                    $('textarea[name="definition[' + maxIndex + ']"]').val('');
                    $('textarea[name="example[' + maxIndex + ']"]').val('');
                    $('input[name="audio_file[' + maxIndex + ']"]').val('');
                    $('select[name="response_text[' + maxIndex + ']"]').val('tiep_theo');
                    
                    // Extra aggressive clearing for content_paragraphs
                    var $contentEditor = $('textarea[name="content_paragraphs[' + maxIndex + '][text]"]');
                    if ($contentEditor.length > 0) {
                        $contentEditor.val('');
                        $('input[name="content_paragraphs[' + maxIndex + '][text]"]').val('');
                        
                        // Clear TinyMCE aggressively
                        if (typeof tinymce !== 'undefined') {
                            var editorId = $contentEditor.attr('id');
                            if (editorId) {
                                var editor = tinymce.get(editorId);
                                if (editor) {
                                    editor.setContent('');
                                    editor.save();
                                    // Also try to clear the iframe content directly
                                    try {
                                        var iframe = editor.getContainer().querySelector('iframe');
                                        if (iframe && iframe.contentDocument) {
                                            iframe.contentDocument.body.innerHTML = '';
                                        }
                                    } catch (e) {
                                        console.log('Could not clear iframe content:', e);
                                    }
                                }
                            }
                        }
                        
                        // Clear any hidden inputs
                        $('input[name*="content_paragraphs[' + maxIndex + ']"]').val('');
                    }
                    
                    console.log('Extra aggressive clearing iteration ' + i + ' for step ' + maxIndex);
                }, i * 100);
            }
        }
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

    /**
     * Fix editor sizes to ensure flexible height with scroll capability
     */
    function fixEditorSizes() {
        console.log('Fixing editor sizes with flexible height...');
        
        // Set flexible height for all content_paragraphs editors
        $('textarea[name*="content_paragraphs"]').each(function() {
            var $textarea = $(this);
            $textarea.css({
                'min-height': '480px',
                'height': 'auto',
                'max-height': '600px',
                'resize': 'vertical',
                'overflow-y': 'auto'
            });
        });
        
        // Set flexible height for all TinyMCE editors
        $('.tox.tox-tinymce').each(function() {
            var $container = $(this);
            $container.css({
                'min-height': '480px',
                'height': 'auto',
                'max-height': '600px'
            });
        });
        
        $('.tox .tox-edit-area').each(function() {
            var $editArea = $(this);
            $editArea.css({
                'min-height': '480px',
                'height': 'auto',
                'max-height': '600px'
            });
        });
        
        $('.tox .tox-edit-area__iframe').each(function() {
            var $iframe = $(this);
            $iframe.css({
                'min-height': '480px',
                'height': 'auto',
                'max-height': '600px',
                'overflow-y': 'auto',
                'overflow-x': 'hidden'
            });
        });
        
        // Auto-adjust height based on content after a short delay
        setTimeout(function() {
            $('.tox.tox-tinymce').each(function() {
                var $editor = $(this);
                try {
                    var iframe = $editor.find('.tox-edit-area__iframe')[0];
                    if (iframe && iframe.contentDocument) {
                        var bodyHeight = iframe.contentDocument.body.scrollHeight;
                        var toolbarHeight = $editor.find('.tox-toolbar').outerHeight() || 0;
                        var totalHeight = Math.max(480, Math.min(bodyHeight + toolbarHeight + 20, 600));
                        
                        $editor.css('height', totalHeight + 'px');
                        $editor.find('.tox-edit-area').css('height', (totalHeight - toolbarHeight) + 'px');
                        $editor.find('.tox-edit-area__iframe').css('height', (totalHeight - toolbarHeight) + 'px');
                    }
                } catch (e) {
                    console.log('Could not auto-adjust editor height:', e);
                }
            });
        }, 600);
        
        console.log('Editor sizes fixed with flexible height');
    }

    /**
     * Add content change listeners for dynamic height adjustment
     */
    function addContentChangeListeners() {
        // Listen for TinyMCE content changes
        if (typeof tinymce !== 'undefined') {
            tinymce.on('AddEditor', function(e) {
                var editor = e.editor;
                if (editor.id && editor.id.indexOf('content_paragraphs') !== -1) {
                    editor.on('input keyup paste', function() {
                        setTimeout(function() {
                            adjustEditorHeight(editor);
                        }, 100);
                    });
                }
            });
        }
        
        // Also listen for direct textarea changes
        $('textarea[name*="content_paragraphs"]').on('input keyup paste', function() {
            var $textarea = $(this);
            setTimeout(function() {
                adjustTextareaHeight($textarea);
            }, 100);
        });
    }

    /**
     * Adjust TinyMCE editor height based on content
     */
    function adjustEditorHeight(editor) {
        try {
            var $editor = $('#' + editor.id).closest('.tox.tox-tinymce');
            var iframe = $editor.find('.tox-edit-area__iframe')[0];
            
            if (iframe && iframe.contentDocument) {
                var bodyHeight = iframe.contentDocument.body.scrollHeight;
                var toolbarHeight = $editor.find('.tox-toolbar').outerHeight() || 0;
                var totalHeight = Math.max(480, Math.min(bodyHeight + toolbarHeight + 20, 600));
                
                $editor.css('height', totalHeight + 'px');
                $editor.find('.tox-edit-area').css('height', (totalHeight - toolbarHeight) + 'px');
                $editor.find('.tox-edit-area__iframe').css('height', (totalHeight - toolbarHeight) + 'px');
            }
        } catch (e) {
            console.log('Could not adjust editor height:', e);
        }
    }

    /**
     * Adjust textarea height based on content
     */
    function adjustTextareaHeight($textarea) {
        var contentHeight = $textarea[0].scrollHeight;
        var newHeight = Math.max(480, Math.min(contentHeight, 600));
        $textarea.css('height', newHeight + 'px');
    }

    return {
        init: init
    };
});
