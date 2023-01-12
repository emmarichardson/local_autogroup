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
 * This plugin automatically assigns users to a group within any course
 * upon which they may be enrolled and which has auto-grouping
 * configured.
 *
 * @package    local_autogroup
 * @copyright  Mark Ward (me@moodlemark.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * This file contains functions which are specific to local_autogroup
 * and do not follow any standard moodle convensions.
 *
 * All functions here exist within the local_autogroup namespace which
 * prevents compatability issues with other plugins.
 */

define('SORT_MODULE_DIR', $CFG->dirroot . '/local/autogroup/classes/sort_module/');

/**
 * Checks the plugin config and returns the current status for
 * the "enabled" option
 *
 * @return bool
 */
function local_autogroup_plugin_is_enabled() {
    $config = get_config('local_autogroup');
    return isset($config->enabled) && $config->enabled;
}

/**
 * generates an array list of sort modules
 *
 * @return array
 */
function local_autogroup_get_sort_module_list() {
    $list = [];

    $files = scandir(SORT_MODULE_DIR);

    foreach ($files as $file) {
        if (strstr($file, '.php')) {
            include_once(SORT_MODULE_DIR . $file);

            $classname = str_replace('.php', '', $file);
            $fullname = 'local_autogroup\\sort_module\\' . $classname;

            if (class_exists($fullname)) {
                $list[$classname] = local_autogroup_sanitise_sort_module_name($classname);
            }
        }
    }

    return $list;
}

/**
 * @param $name
 * @return \lang_string|string
 */
function local_autogroup_sanitise_sort_module_name($name = '') {

    // For when we are passed the full name.
    $name = explode('\\', $name);
    $name = array_pop($name);
    $stringkey = 'sort_module:' . $name;
    if (get_string_manager()->string_exists($stringkey, 'local_autogroup')) {
        return get_string($stringkey, 'local_autogroup');
    }
    $name = str_replace('_', ' ', $name);
    $name = ucfirst($name);
    return $name;
}

/**
 * @param settings_navigation $settingsnav
 * @param context $context
 * @return void
 */
function local_autogroup_amend_settings_structure(settings_navigation $settingsnav, context $context) {
    global $PAGE, $SITE;

    $course = $PAGE->course;

    if ($course->id != $SITE->id && ($course->groupmode || !$course->groupmodeforce)) {

        if (has_capability('local/autogroup:managecourse', $context)) {

            $usersnode = $settingsnav->find('users', navigation_node::TYPE_UNKNOWN);
            $groupparentnode = $settingsnav->find('groups', navigation_node::TYPE_SETTING);

            if ($groupparentnode && $usersnode) {

                $groupnode = navigation_node::create(
                    $groupparentnode->text,
                    $groupparentnode->action,
                    $groupparentnode->type,
                    $groupparentnode->shorttext,
                    $groupparentnode->key,
                    $groupparentnode->icon
                );

                $groupparentnode->type = navigation_node::TYPE_UNKNOWN;
                $groupparentnode->url = null;
                $groupparentnode->action = null;
                $groupparentnode->key = 'groupsparent';

                $groupparentnode->add_node($groupnode);

                // Now add new link for autogroups.
                $url = new moodle_url('/local/autogroup/manage.php', array('courseid' => $course->id));

                $linknode = $groupparentnode->add(
                    get_string('coursesettings', 'local_autogroup'),
                    $url,
                    navigation_node::TYPE_SETTING,
                    null,
                    'autogroups',
                    new pix_icon('i/withsubcat', '')
                );

                // Make the node active if we are viewing its page.
                if ($PAGE->has_set_url() && strstr($PAGE->url, 'local/autogroup/')) {
                    $linknode->make_active();
                }
            }
        }

    }
}
