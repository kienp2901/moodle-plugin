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
//

/**
 * Step by Step module main JavaScript with original logic
 *
 * @package    mod_stepbystep
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    'use strict';

    /**
     * Module initialization
     */
    var init = function(cmid, totalsteps) {
        var currentStep = 1;
        var completedSteps = 0;
        var currentUtterance = null;

        console.log('Step by Step module initialized with', totalsteps, 'steps');

        // Initially hide all steps except the first one
        $('.stepbystep-step').not(':first').hide();
        
        // Initially hide all response sections except the first one
        $('.stepbystep-response-section').not(':first').hide();
        
        // Hide completion section initially
        $('.stepbystep-completion').hide();

        // Show first step and its response section
        $('.stepbystep-step:first').show();
        $('.stepbystep-response-section:first').show();

        console.log('First step shown, others hidden');

        // Debug: Log all steps and their attributes
        $('.stepbystep-step').each(function(index) {
            var $step = $(this);
            var stepId = $step.data('step-id');
            var stepNumber = $step.data('step-number');
            var isLast = $step.data('is-last');
            console.log('Step', index + 1, ':', {
                stepId: stepId,
                stepNumber: stepNumber,
                isLast: isLast,
                element: $step[0]
            });
        });

        // Handle continue button clicks
        $(document).on('click', '.stepbystep-continue', function(e) {
            e.preventDefault();
            
            var $currentStep = $(this).closest('.stepbystep-step');
            var stepId = $currentStep.data('step-id');
            var stepNumber = $currentStep.data('step-number');
            var isLast = $currentStep.data('is-last');
            
            console.log('Continue clicked for step', {
                stepId: stepId,
                stepNumber: stepNumber,
                isLast: isLast,
                element: $currentStep[0]
            });
            
            // Hide the entire response section (including avatar and bubble) of current step
            $currentStep.find('.stepbystep-response-section').fadeOut(300);
            
            // Check if this is the last step
            if (isLast) {
                console.log('This is the last step, showing completion');
                // Show completion message
                showCompletion();
                
                // Call custom plugin function to handle completion
                handleStepCompletion(cmid);
            } else {
                console.log('This is not the last step, showing next step');
                // Show next step
                var $nextStep = $currentStep.next('.stepbystep-step');
                if ($nextStep.length) {
                    showNextStep($nextStep);
                    
                    // Scroll to next step smoothly
                    $nextStep[0].scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }
            }
        });

        // Handle audio button clicks for text-to-speech
        $(document).on('click', '.stepbystep-audio-button', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $audioText = $button.find('.audio-text');
            var originalText = $audioText.text();
            var stepId = $button.data('step-id');
            
            // Check if any audio is currently playing
            if (currentUtterance && speechSynthesis.speaking) {
                // If this button is already playing, stop it
                if ($button.hasClass('playing')) {
                    speechSynthesis.cancel();
                    currentUtterance = null;
                    $audioText.text(originalText);
                    $button.removeClass('playing');
                    return;
                }
                
                // If another button is playing, don't allow this one to start
                Notification.alert('Please wait for the current audio to finish playing');
                return;
            }
            
            // Get the term to pronounce
            var term = $button.closest('.stepbystep-vocabulary').find('.stepbystep-term').text();
            
            if (term && speechSynthesis) {
                // Disable all audio buttons while playing
                $('.stepbystep-audio-button').prop('disabled', true);
                
                // Create utterance
                currentUtterance = new SpeechSynthesisUtterance(term);
                currentUtterance.lang = 'en-US';
                currentUtterance.rate = 0.8;
                
                // Update button text to "Playing..."
                $audioText.text('Playing...');
                $button.addClass('playing');
                
                // Handle speech end
                currentUtterance.onend = function() {
                    $audioText.text(originalText);
                    $button.removeClass('playing');
                    currentUtterance = null;
                    
                    // Re-enable all audio buttons
                    $('.stepbystep-audio-button').prop('disabled', false);
                };
                
                // Handle speech error
                currentUtterance.onerror = function() {
                    $audioText.text(originalText);
                    $button.removeClass('playing');
                    currentUtterance = null;
                    
                    // Re-enable all audio buttons
                    $('.stepbystep-audio-button').prop('disabled', false);
                    
                    Notification.exception(new Error('Text-to-speech failed'));
                };
                
                // Start speaking
                speechSynthesis.speak(currentUtterance);
            } else {
                Notification.exception(new Error('Text-to-speech not supported'));
            }
        });

        /**
         * Show the next step
         */
        function showNextStep($nextStep) {
            console.log('Showing next step:', $nextStep[0]);
            
            if ($nextStep.length) {
                $nextStep.show();
                
                // Show the response section for this step
                $nextStep.find('.stepbystep-response-section').show();
                
                // Add animation class
                $nextStep.addClass('fade-in');
                
                // Update progress if needed
                updateProgress();
                
                console.log('Next step shown successfully');
            } else {
                console.error('Next step element not found!');
            }
        }

        /**
         * Show completion message
         */
        function showCompletion() {
            console.log('Showing completion message');
            $('.stepbystep-completion').show();
            
            // Show completion animation
            $('.stepbystep-completion').addClass('show');
            
            console.log('Completion message shown');
        }

        /**
         * Update progress indicator
         */
        function updateProgress() {
            var progress = (completedSteps / totalsteps) * 100;
            
            // You can add a progress bar here if needed
            console.log('Progress: ' + progress + '%');
        }

        /**
         * Handle step completion based on plugin settings
         */
        function handleStepCompletion(cmid) {
            console.log('Handling step completion for cmid:', cmid);
            
            // Only proceed if cmid is provided
            if (!cmid || cmid === 0) {
                console.log('No course module ID provided, skipping completion handling');
                return;
            }
            
            // Call custom plugin function to check completion settings and handle accordingly
            var promises = Ajax.call([
                {
                    methodname: 'mod_stepbystep_handle_completion',
                    args: {
                        cmid: cmid
                    }
                }
            ]);

            promises[0].then(function(response) {
                console.log('Completion handled successfully:', response);
                
                // Check the response to see what was done
                if (response.completion_enabled) {
                    if (response.completion_type === 'manual') {
                        if (response.success) {
                            console.log('Manual completion enabled - activity marked as completed');
                        } else {
                            console.log('Manual completion failed:', response.message);
                        }
                    } else if (response.completion_type === 'automatic') {
                        if (response.success) {
                            console.log('Automatic completion enabled - activity marked as completed');
                        } else {
                            console.log('Automatic completion conditions not met:', response.message);
                        }
                    } else {
                        console.log('Completion enabled but type not specified');
                    }
                } else {
                    console.log('Completion not enabled for this activity:', response.message);
                }
            }).catch(function(error) {
                console.error('Error handling completion:', error);
                
                // Check if it's a capability or configuration error
                if (error.errorcode === 'nopermissions') {
                    console.log('User does not have permission to complete this activity');
                } else if (error.errorcode === 'invalidparameter') {
                    console.log('Invalid course module ID provided');
                } else {
                    console.log('Unexpected error occurred during completion handling');
                }
                
                // Don't show error notification - just log it
                // This prevents user confusion if completion is not configured
            });
        }

        /**
         * Mark activity as completed via AJAX (kept for backward compatibility)
         */
        // function markActivityCompleted(cmid) {
        //     console.log('Marking activity as completed for cmid:', cmid);
        //     // Only try to mark as completed if cmid is provided (not 0)
        //     if (!cmid || cmid === 0) {
        //         console.log('No course module ID provided, skipping completion tracking');
        //         return;
        //     }
        //     
        //     var promises = Ajax.call([
        //         {
        //             methodname: 'core_completion_update_activity_completion_status_manually',
        //             args: {
        //                 cmid: cmid,
        //                 completed: true
        //             }
        //         }
        //     ]);

        //     promises[0].then(function(response) {
        //         console.log('Activity marked as completed successfully');
        //     }).catch(function(error) {
        //         console.error('Error marking activity as completed:', error);
        //         
        //         // Check if the error is due to completion tracking not being enabled
        //         if (error.errorcode === 'cannotmanualctrack') {
        //             console.log('Completion tracking not enabled for this activity - this is normal');
        //             // Don't show error notification for this case
        //         } else {
        //             // Show notification for other types of errors
        //             Notification.exception(error);
        //         }
        //     });
        // }

        // Initialize progress
        updateProgress();
        
        console.log('Step by Step module setup complete');
    };

    return {
        init: init
    };
});
