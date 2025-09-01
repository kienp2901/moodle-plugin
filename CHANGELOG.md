# Changelog

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
