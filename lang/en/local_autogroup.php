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
 * autogroup local plugin
 *
 * These strings are used throughout the user interface and can
 * be overridden by the user through the language customisation
 * tool.
 *
 * @package    local_autogroup
 * @copyright  Mark Ward (me@moodlemark.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname']  = 'Auto Group';

// Course Settings.
$string['actions'] = 'Actions';

$string['coursesettings']  = 'Auto Groups';
$string['coursesettingstitle']  = 'Auto Groups: {$a}';

$string['autogroupdescription'] = '"Auto Groups" will automatically assign your users to groups within a course based upon information within their user profile.';
$string['newsettingsintro'] = 'To start grouping your users, simply select a profile field from the "Group by" option below and click "Save Changes".';
$string['updatesettingsintro'] = 'This course is already grouping users with {$a} rule set(s). You can either edit or remove these existing rule sets, or add a new one to the course. ';

$string['dontgroup'] = "Don't group users";
$string['cleanupold'] = 'Clean up old groups?';

$string['set_type'] = 'Group set type';
$string['set_groups'] = 'Number of groups';
$string['set_roles'] = 'Eligible Roles';
$string['set_groupby'] = 'Group by';
$string['set_delimitedby'] = 'Delimited by';

$string['confirmdelete'] = 'Are you sure you wish to remove this auto group set?';

$string['create'] = 'Create new group set:';

// Admin Settings.
$string['addtonewcourses'] = 'Add to new courses';
$string['addtorestoredcourses'] = 'Add to restored courses';
$string['preservemanual'] = 'Manually added Users are not removed when autogroups are updated.';
$string['defaults'] = 'Default Settings';
$string['defaultroles'] = 'Default Eligible Roles';
$string['enabled'] = 'Enabled';
$string['general'] = 'General Configuration';
$string['events'] = 'Event triggers';
$string['events_help'] = 'Customise the events listened for by AutoGroup to improve site performance and tailor behaviour to your site\'s usage';
$string['listenforrolechanges'] = 'Role Assignment';
$string['listenforrolechanges_help'] = 'Listen for new role assignments or changes to role assignments on a course.';
$string['listenforuserprofilechanges'] = 'User Profiles';
$string['listenforuserprofilechanges_help'] = 'Listen for the changes to a user\'s profile which may impact group membership.';
$string['listenforuserpositionchanges'] = 'User Position';
$string['listenforuserpositionchanges_help'] = 'Listen for changes to a learners position, such as a new organisation or position assignment.';
$string['listenforgroupchanges'] = 'Groups';
$string['listenforgroupchanges_help'] = 'Listen for modifications to groups on a course. This can prevent issues caused by manual changes but will also slow down AutoGroup as it double checks its own actions. Previously configured as "Strict Enforcement".';
$string['listenforgroupmembership'] = 'Group Membership';
$string['listenforgroupmembership_help'] = 'Listen for changes to group membership. This can prevent issues caused by manual changes but will also slow down AutoGroup as it double checks its own actions. Previously configured as "Strict Enforcement".';

// Capabilities.
$string['autogroup:managecourse']  = 'Manage autogroup settings on course';

// Sort profile field options.
$string['auth'] = "Authentication Method";
$string['city'] = "City";
$string['department'] = "Department";
$string['institution'] = "Institution";
$string['lang'] = "Preferred Language";

// Sort module names.
$string['sort_module:profile_field'] = 'Profile field';
$string['sort_module:user_info_field'] = 'Custom profile field';
$string['sort_module:user_info_field_multivalue'] = 'Custom profile field multi values';

// Privacy provider
$string['privacy:metadata:local_autogroup'] = 'Data relating users for the local autogroup plugin';
$string['privacy:metadata:local_autogroup_manual'] = 'Data relating users manually assigned to autogroup groups';
$string['privacy:metadata:local_autogroup_manual:id'] = 'Record ID';
$string['privacy:metadata:local_autogroup_manual:userid'] = 'The ID of the user';
$string['privacy:metadata:local_autogroup_manual:groupid'] = 'The ID that relates to the group';
