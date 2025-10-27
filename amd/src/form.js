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
            
            // Handle generate vocabulary button
            $(document).on('click', '#generate_vocabulary_btn', function(e) {
                e.preventDefault();
                handleGenerateVocabulary();
            });
            
            // Check if we need to populate generated vocabulary after page reload
            populateGeneratedVocabulary();
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

    /**
     * Handle generate vocabulary button click
     */
    function handleGenerateVocabulary() {
        var $button = $('#generate_vocabulary_btn');
        var $countField = $('input[name="vocabulary_count"]');
        var $topicField = $('input[name="vocabulary_topic"]');
        
        // Validate inputs
        var count = parseInt($countField.val());
        var topic = $topicField.val().trim();
        
        if (!topic) {
            alert('Please enter topic before generating vocabulary');
            $topicField.focus();
            return;
        }
        
        if (!count || isNaN(count) || count < 1 || count > 50) {
            alert('Please enter a valid vocabulary count (1-50)');
            $countField.focus();
            return;
        }
        
        // Show loading state
        var originalText = $button.text();
        $button.text('Generating...').prop('disabled', true);
        
        // Get topic_id from excluded vocabulary list (only if exclude checkbox is checked)
        var topicIdString = "";
        var $excludeExistingVocab = $('#id_exclude_existing_vocab');
        if ($excludeExistingVocab.length > 0 && $excludeExistingVocab.is(':checked')) {
            var $excludedVocabList = $('#id_excluded_vocab_list');
            if ($excludedVocabList.length > 0) {
                var selectedValues = $excludedVocabList.val();
                if (selectedValues && selectedValues.length > 0) {
                    // Convert array to comma-separated string
                    topicIdString = selectedValues.join(',');
                }
            }
        }
        
        // Get ems_render_question from quiz generation type
        var emsRenderQuestion = 1; // Default: no quiz
        var $quizGenerationType = $('#id_quiz_generation');
        if ($quizGenerationType.length > 0) {
            var quizTypeValue = $quizGenerationType.val();
            if (quizTypeValue) {
                emsRenderQuestion = parseInt(quizTypeValue);
            }
        }
        
        // Get level from level field
        var level = 1; // Default: vocabulary
        var $levelField = $('#id_level');
        if ($levelField.length > 0) {
            var levelValue = $levelField.val();
            if (levelValue) {
                level = parseInt(levelValue);
            }
        }
        
        // Prepare API request
        var requestData = {
            count: count,
            topic: topic,
            topic_id: topicIdString,
            ems_render_question: emsRenderQuestion,
            level: level
        };
        
        console.log('API Request Data:', requestData);
        console.log('Topic IDs (excluded vocab):', topicIdString);
        console.log('Quiz render type:', emsRenderQuestion);
        console.log('Level:', level);
        
        // Make API call
        $.ajax({
            url: 'https://ai.microgem.io.vn/api/moodle/generate-vocalbulary',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(requestData),
            timeout: 300000, // 5 minutes in milliseconds
            success: function(response) {
                if (response.code === 200 && response.data && response.data.vocabulary) {
                    console.log(response.data.vocabulary);
                    // Generate vocabulary steps
                    generateVocabularySteps(response.data.vocabulary);
                    
                    // Show success message
                    alert('Successfully generated ' + response.data.vocabulary.length + ' vocabulary terms');
                } else {
                    alert('Error: Invalid response from API');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'Error generating vocabulary: ';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += xhr.responseJSON.message;
                } else {
                    errorMessage += error;
                }
                alert(errorMessage);
            },
            complete: function() {
                // Restore button state
                $button.text(originalText).prop('disabled', false);
            }
        });
    }

    /**
     * Generate vocabulary steps from API response
     */
    function generateVocabularySteps(vocabulary) {
        // Get current step count
        var currentSteps = parseInt($('input[name="steps"]').val()) || 0;
        
        // Calculate total steps needed
        var totalSteps = currentSteps + vocabulary.length;
        
        // Update the steps count to add new steps
        $('input[name="steps"]').val(totalSteps);
        
        // Store vocabulary data in sessionStorage for after page reload
        sessionStorage.setItem('stepbystep_generated_vocabulary', JSON.stringify(vocabulary));
        sessionStorage.setItem('stepbystep_current_steps', currentSteps);
        sessionStorage.setItem('stepbystep_auto_add_steps', 'true');
        
        // Automatically trigger the Add step button by simulating a click
        var $addButton = $('input[name="steps_add"]');
        if ($addButton.length > 0) {
            console.log('Automatically triggering Add step button to add', vocabulary.length, 'vocabulary steps');
            
            // Set scroll flag for after page reload
            sessionStorage.setItem('stepbystep_scroll_after_add', 'true');
            
            // Since the button has data-no-submit="1", we need to trigger the form submission manually
            // The button click will trigger the onclick handler but won't submit the form
            // Set skipClientValidation as the button's onclick does
            window.skipClientValidation = true;
            
            // Trigger the button click (this will execute the onclick handler)
            $addButton.trigger('click');
            
            // Since the button has data-no-submit="1", we need to manually submit the form
            // after a short delay to ensure the click handler has executed
            setTimeout(function() {
                // Find the form that contains the button (form ID is dynamic like mform1_v7ETQFeYzxOBr5e)
                var $form = $addButton.closest('form');
                console.log('Manually submitting form after button click, form ID:', $form.attr('id'));
                
                // Debug: Check if steps_add parameter is present
                var stepsAddInput = $form.find('input[name="steps_add"]');
                console.log('steps_add input found:', stepsAddInput.length > 0, 'value:', stepsAddInput.val());
                
                // Ensure steps_add parameter is present in the form
                if (stepsAddInput.length === 0) {
                    console.log('Creating steps_add input since it was not found');
                    var $hiddenInput = $('<input>', {
                        type: 'hidden',
                        name: 'steps_add',
                        value: 'Add step'
                    });
                    $form.append($hiddenInput);
                }
                
                // $form.submit();
            }, 100);
            
        } else {
            console.error('Add step button not found, falling back to direct form submission');
            // Fallback: direct form submission with steps_add parameter
            // Find any form with class 'mform' (Moodle form)
            var $form = $('form.mform');
            if ($form.length > 0) {
                var $hiddenInput = $('<input>', {
                    type: 'hidden',
                    name: 'steps_add',
                    value: 'Add step'
                });
                $form.append($hiddenInput);
                console.log('Fallback: submitting form with ID:', $form.attr('id'));
                // $form.submit();
            } else {
                console.error('No Moodle form found for fallback submission');
            }
        }
    }

    /**
     * Populate generated vocabulary data after page reload
     */
    function populateGeneratedVocabulary() {
        var vocabularyData = sessionStorage.getItem('stepbystep_generated_vocabulary');
        var currentSteps = sessionStorage.getItem('stepbystep_current_steps');
        var autoAddSteps = sessionStorage.getItem('stepbystep_auto_add_steps');
        
        if (vocabularyData && currentSteps) {
            try {
                var vocabulary = JSON.parse(vocabularyData);
                var startIndex = parseInt(currentSteps);
                var isAutoAdd = autoAddSteps === 'true';
                
                console.log('Populating generated vocabulary (auto-add: ' + isAutoAdd + '):', vocabulary);
                console.log('Starting from step index:', startIndex);
                
                // Wait a bit for the form to fully load, especially for auto-add mode
                var initialDelay = isAutoAdd ? 1500 : 500;
                
                setTimeout(function() {
                    // Populate each vocabulary step
                    vocabulary.forEach(function(vocab, index) {
                        var stepIndex = startIndex + index;
                        
                        console.log('Populating step ' + stepIndex + ' with:', vocab.Term);
                        
                        // Set step type to vocabulary and trigger change
                        var $typeSelect = $('select[name="type[' + stepIndex + ']"]');
                        if ($typeSelect.length > 0) {
                            $typeSelect.val('vocabulary').trigger('change');
                            
                            // Wait for type change to take effect, then populate data
                            setTimeout(function() {
                                // Set vocabulary data
                                $('input[name="term[' + stepIndex + ']"]').val(vocab.Term);
                                $('textarea[name="definition[' + stepIndex + ']"]').val(vocab.definition_vi);
                                $('textarea[name="example[' + stepIndex + ']"]').val(vocab.Example_en);
                                
                                console.log('Successfully populated step ' + stepIndex + ':', vocab.Term);
                            }, (isAutoAdd ? 300 : 100) * index); // Longer delay for auto-add mode
                        } else {
                            console.warn('Step ' + stepIndex + ' type select not found');
                        }
                    });
                    
                    // Update remove buttons and scroll after populating
                    setTimeout(function() {
                        updateRemoveButtons();
                        
                        // Scroll to bottom for auto-add mode to show newly added steps
                        if (isAutoAdd) {
                            $('html, body').animate({
                                scrollTop: $(document).height()
                            }, 800);
                            console.log('Auto-scrolled to bottom to show vocabulary steps');
                        }
                        
                        console.log('Population completed for', vocabulary.length, 'vocabulary steps');
                    }, vocabulary.length * (isAutoAdd ? 300 : 100) + 500);
                    
                }, initialDelay);
                
                // Clear session storage after completion
                setTimeout(function() {
                    sessionStorage.removeItem('stepbystep_generated_vocabulary');
                    sessionStorage.removeItem('stepbystep_current_steps');
                    sessionStorage.removeItem('stepbystep_auto_add_steps');
                    console.log('Session storage cleared after vocabulary population');
                }, vocabulary.length * (isAutoAdd ? 300 : 100) + 2000);
                
            } catch (e) {
                console.error('Error parsing vocabulary data:', e);
                sessionStorage.removeItem('stepbystep_generated_vocabulary');
                sessionStorage.removeItem('stepbystep_current_steps');
                sessionStorage.removeItem('stepbystep_auto_add_steps');
            }
        }
    }

    return {
        init: init
    };
});
