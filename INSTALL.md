# Installation Guide for Step by Step Module

## Prerequisites

- Moodle 4.0 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher / PostgreSQL 10 or higher
- Web server (Apache/Nginx)

## Installation Steps

### 1. Download and Extract

1. Download the `stepbystep` module
2. Extract the files to your Moodle installation's `mod/` directory
3. Ensure the final path is: `moodle/mod/stepbystep/`

### 2. Set Permissions

Ensure the web server has read access to the module directory:

```bash
# For Linux/Unix systems
chmod -R 755 mod/stepbystep/
chown -R www-data:www-data mod/stepbystep/

# For Windows systems
# Ensure IIS/Apache has read access to the directory
```

### 3. Install via Moodle Admin

1. **Login** to your Moodle site as an administrator
2. **Navigate** to **Site administration > Notifications**
3. **Click** "Continue" when prompted about new plugins
4. **Review** the installation summary
5. **Click** "Upgrade Moodle database now"
6. **Wait** for the installation to complete
7. **Click** "Continue" to finish

### 4. Verify Installation

1. **Check** that the module appears in **Site administration > Plugins > Activity modules**
2. **Verify** that you can add a "Step by Step" activity to a course
3. **Test** creating a simple activity with one or two steps

## Database Tables

The installation will create these tables:

### `stepbystep`
- Main activity information
- Contains course ID, name, description, timestamps

### `stepbystep_content`
- Individual step content
- Links to main activity via `stepbystep_id`
- Contains step type, content, and metadata

## Configuration

### Site Settings

No additional site-wide configuration is required. The module works out of the box.

### Course Settings

1. **Add Activity**: In any course, click "Add an activity or resource"
2. **Select**: Choose "Step by Step" from the activity list
3. **Configure**: Set name, description, and add content steps
4. **Save**: Click "Save and return to course"

## Troubleshooting

### Common Issues

#### 1. Module Not Appearing
- **Check** file permissions
- **Verify** files are in correct directory
- **Clear** Moodle cache (Site administration > Development > Purge all caches)
- **Check** error logs for PHP errors

#### 2. Database Errors
- **Verify** database user has CREATE TABLE permissions
- **Check** database connection settings
- **Review** error logs for SQL errors

#### 3. JavaScript Not Working
- **Check** browser console for errors
- **Verify** AMD modules are built correctly
- **Ensure** browser supports required features

#### 4. Styling Issues
- **Clear** browser cache
- **Check** CSS file is accessible
- **Verify** template files are correct

### Debug Mode

Enable debugging for troubleshooting:

1. **Go to** **Site administration > Development > Debugging**
2. **Set** "Debug messages" to "DEVELOPER"
3. **Set** "Display debug info" to "Yes"
4. **Save changes**

### Log Files

Check these locations for error information:

- **Moodle logs**: Site administration > Reports > Logs
- **PHP error log**: Check your web server configuration
- **Browser console**: Press F12 and check Console tab

## Post-Installation

### Testing

1. **Create** a test course
2. **Add** a Step by Step activity
3. **Add** multiple steps with different types
4. **Test** student view and progression
5. **Verify** completion tracking works

### Training

1. **Document** the module for teachers
2. **Create** example activities
3. **Train** staff on usage
4. **Set** up support procedures

## Security Considerations

### Permissions

The module respects Moodle's permission system:

- **View**: Students can view activities they have access to
- **Add**: Teachers can add activities in their courses
- **Manage**: Teachers can edit activities they created

### Data Privacy

- All data is stored in Moodle's database
- Follows Moodle's data retention policies
- Respects user privacy settings

## Maintenance

### Regular Tasks

1. **Monitor** error logs
2. **Update** module when new versions are available
3. **Backup** custom configurations
4. **Test** after Moodle updates

### Updates

1. **Backup** your current installation
2. **Download** new version
3. **Replace** old files
4. **Run** database updates if needed
5. **Test** functionality

## Support

### Getting Help

1. **Check** this documentation
2. **Review** troubleshooting section
3. **Search** Moodle community forums
4. **Contact** module developer

### Reporting Issues

When reporting problems, include:

- Moodle version
- Module version
- PHP version
- Database type and version
- Error messages
- Steps to reproduce
- Browser information

## Uninstallation

### Removing the Module

1. **Delete** all Step by Step activities from courses
2. **Remove** module files from `mod/stepbystep/`
3. **Visit** Site administration > Notifications
4. **Follow** uninstallation prompts
5. **Verify** database tables are removed

### Data Backup

Before uninstalling:

1. **Export** any important content
2. **Document** custom configurations
3. **Backup** database tables if needed

## License

This module is licensed under the GNU General Public License v3.0.

## Version History

- **1.0.0**: Initial release
- **1.1.0**: Added text-to-speech functionality
- **1.2.0**: Enhanced UI and step progression

---

For additional support, please refer to the main README.md file or contact the development team.
