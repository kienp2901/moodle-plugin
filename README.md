# Step by Step Module for Moodle

A Moodle activity module that allows teachers to create sequential learning steps that students must complete one by one.

## Features

- **Sequential Learning**: Students view content steps one at a time
- **Multiple Content Types**: Support for text, vocabulary, and custom content
- **Text-to-Speech**: Built-in pronunciation for vocabulary terms
- **Progress Tracking**: Automatic completion tracking
- **Responsive Design**: Works on all devices
- **Modern UI**: Beautiful, intuitive interface

## Installation

1. Copy the `stepbystep` folder to your Moodle installation's `mod/` directory
2. Visit **Site administration > Notifications** to install the plugin
3. The plugin will automatically create necessary database tables

## Building JavaScript

If you need to modify the JavaScript code:

```bash
cd mod/stepbystep/amd
npm install
grunt default
```

## Usage

### For Teachers

1. **Add Activity**: In your course, click "Add an activity or resource" and select "Step by Step"
2. **Configure**: Set the activity name and description
3. **Add Steps**: Use the form to add multiple content steps:
   - **Text/Tip**: General content with text editor
   - **Vocabulary**: Term, definition, example, and audio file
4. **Response Text**: Set custom button text for each step (e.g., "Got it!", "Continue")
5. **Save**: Click "Save and return to course"

### For Students

1. **Access**: Click on the Step by Step activity in your course
2. **View Steps**: Only the first step is visible initially
3. **Interact**: Read content, listen to pronunciation (for vocabulary)
4. **Continue**: Click the response button to proceed to the next step
5. **Complete**: After viewing all steps, the activity is marked as complete

## Step Types

### Text/Tip
- General content with rich text editor
- Perfect for instructions, tips, or explanations

### Vocabulary
- **Term**: The word or concept to learn
- **Definition**: Explanation of the term
- **Example**: Usage example or sentence
- **Audio**: Optional audio file for pronunciation
- **Text-to-Speech**: Built-in pronunciation using browser's speech synthesis

## Technical Details

### Database Tables
- `stepbystep`: Main activity information
- `stepbystep_content`: Individual step content

### Files
- `view.php`: Main display logic
- `mod_form.php`: Teacher configuration form
- `lib.php`: Core functions and database operations
- `templates/view.mustache`: HTML template
- `amd/src/main.js`: JavaScript functionality
- `styles.css`: Styling

### JavaScript Features
- Step-by-step progression
- Text-to-speech integration
- Smooth animations
- Progress tracking
- AJAX completion marking

## Customization

### Styling
Modify `styles.css` to change the appearance:
- Colors and gradients
- Animations and transitions
- Layout and spacing
- Responsive breakpoints

### JavaScript
Modify `amd/src/main.js` to change behavior:
- Step progression logic
- Text-to-speech settings
- Animation timing
- Event handling

### Templates
Modify `templates/view.mustache` to change HTML structure:
- Step layout
- Content display
- Button styling
- Completion message

## Browser Compatibility

- **Modern Browsers**: Chrome, Firefox, Safari, Edge
- **Text-to-Speech**: Requires browser support for Speech Synthesis API
- **Mobile**: Fully responsive design

## Troubleshooting

### Build Issues
```bash
# Install dependencies
npm install

# Install grunt-cli globally
npm install -g grunt-cli

# Build
grunt default
```

### Common Problems
1. **Steps not showing**: Check database content and step order
2. **Audio not working**: Ensure browser supports Speech Synthesis
3. **Styling issues**: Clear browser cache and rebuild CSS
4. **JavaScript errors**: Check browser console for errors

## Development

### Adding New Step Types
1. Update `mod_form.php` to add form fields
2. Modify `lib.php` to handle new data
3. Update `templates/view.mustache` for display
4. Add CSS styling in `styles.css`
5. Extend JavaScript functionality in `main.js`

### Testing
1. Create test content with various step types
2. Test on different devices and browsers
3. Verify completion tracking works correctly
4. Check accessibility features

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review browser console for JavaScript errors
3. Verify database tables are created correctly
4. Test with different content types

## License

This plugin is licensed under the GNU General Public License v3.0.

## Version History

- **1.0.0**: Initial release with basic functionality
- **1.1.0**: Added text-to-speech and improved UI
- **1.2.0**: Enhanced step progression and completion tracking
