# Changelog

## [v0.1.8] - 2024-12-19

### Added
- **Dynamic Form Field Visibility**: Implemented conditional field display based on step type
  - Vocabulary type: Shows term, definition, example, audio_file fields
  - Text type: Shows main_title, sub_heading, content_paragraphs fields
  - Response text field remains visible for both types
  - Smooth CSS animations for field transitions
  - Enhanced user experience with visual feedback
- **Remove Step Button**: Added remove step functionality for each content step
  - Each step now has a "Remove step" button
  - Button is automatically hidden when only 1 step remains
  - Ensures at least 1 step is always present
  - Dynamic visibility based on step count

### Improved
- **Form User Experience**: Complete redesign with modern UI/UX
  - **Single Step Initialization**: Form now starts with only 1 step for new activities
  - **Step Groups**: Each step wrapped in styled containers with hover effects
  - **Visual Indicators**: Color-coded step types (green for vocabulary, yellow for text)
  - **Step Counters**: Numbered badges showing step order
  - **Responsive Design**: Mobile-friendly layout with touch-optimized controls
  - **Accessibility**: Improved focus states and screen reader support

### Fixed
- **CSS Loading Error**: Fixed "Cannot require a CSS file after <head> has been printed" error
  - Moved CSS inclusion to the correct location in `definition()` method
  - Ensured CSS loads before page rendering begins
  - Proper Moodle CSS loading sequence maintained
- **Form Field Visibility**: Fixed hide/show functionality not working
  - Simplified JavaScript to use jQuery show/hide instead of CSS classes
  - Removed complex CSS animations and styling
  - Ensured proper DOM targeting for field visibility
- **Multi-Step Independence**: Fixed step type changes affecting other steps
  - Each step now has independent field visibility control
  - Step type changes only affect fields of the specific step
  - Proper step index targeting for field selection
- **Form Data Loading**: Fixed data duplication and incorrect field filling when editing
  - Fixed data preprocessing to set fields based on step type
  - Prevented cross-type data contamination
  - Ensured proper field initialization for mixed step types
- **Validation Error**: Fixed trim() error when step has no data
  - Added proper type checking before using trim() function
  - Handled array values from form fields correctly
  - Prevented PHP errors when updating with empty steps

### Technical Changes
- Updated `mod_form.php` with proper initialization logic
- Enhanced `form.js` with simplified hide/show functionality
- Removed custom CSS styling to use Moodle defaults
- Improved JavaScript event handling and error handling
- Added special handling for filemanager elements
- Updated build process with Grunt for minified JavaScript

### Documentation
- Created `FORM_FIELD_VISIBILITY_README.md` with comprehensive implementation guide
- Updated technical documentation with new UI/UX features
- Added troubleshooting section for common issues
- Included responsive design and accessibility guidelines

## [v0.1.1] - 2024-08-30

### Fixed
- **Completion Tracking Error**: Fixed error "Activity does not provide manual completion tracking" when clicking response text button at the last step
  - Added proper completion tracking checks before calling completion API
  - Module now gracefully handles cases where completion tracking is not enabled
  - Shows completion message regardless of completion tracking status

- **Text-to-Speech Button Conflicts**: Fixed issue where multiple text-to-speech buttons could be clicked simultaneously
  - Added global flag to track if audio is currently playing
  - All audio buttons are disabled while any audio is playing
  - Buttons are re-enabled only after audio finishes or is stopped
  - Added visual feedback for playing state with pulse animation
  - Improved button state management and error handling

### Improved
- **Completion Logic**: Enhanced completion tracking logic to support different completion modes
  - Manual completion tracking (COMPLETION_TRACKING_MANUAL)
  - Automatic completion tracking (COMPLETION_TRACKING_AUTOMATIC)
  - No completion tracking (COMPLETION_TRACKING_NONE)

- **Audio Button States**: Better visual feedback for audio buttons
  - Disabled state styling
  - Playing state with animation
  - Hover effects only when not disabled

- **Error Handling**: Improved error handling for completion tracking
  - Graceful fallback when completion tracking is not available
  - Better logging and debugging information
  - No error notifications for expected completion tracking scenarios

### Technical Changes
- Updated `lib.php` to better support completion tracking
- Enhanced JavaScript completion logic in `main.js`
- Added completion tracking information to PHP-JavaScript communication
- Updated CSS for better audio button states
- Bumped version to 2024083021

## [v0.1.0] - 2024-08-30

### Initial Release
- Basic Step by Step module functionality
- Support for text and vocabulary step types
- Text-to-speech functionality
- Basic completion tracking
- Responsive design
