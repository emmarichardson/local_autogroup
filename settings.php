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
 * A course object relates to a Moodle course and acts as a container
 * for multiple groups. Initialising a course object will automatically
 * load each autogroup group for that course into memory.
 *
 * @package    local_autogroup
 * @copyright  Mark Ward (me@moodlemark.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * This file generates the site admin settings page using Moodles
 * standard admin_settingpage class.
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/lib.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_autogroup',
        get_string('pluginname', 'local_autogroup')
    );

    // General settings.
    $settings->add(
        new admin_setting_heading(
            'local_autogroup/general',
            get_string('general', 'local_autogroup'),
            ''
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'local_autogroup/enabled',
            get_string('enabled', 'local_autogroup'),
            '',
            true
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'local_autogroup/addtonewcourses',
            get_string('addtonewcourses', 'local_autogroup'),
            '',
            false
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'local_autogroup/addtorestoredcourses',
            get_string('addtorestoredcourses', 'local_autogroup'),
            '',
            false
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'local_autogroup/preservemanual',
            get_string('preservemanual', 'local_autogroup'),
            '',
            1
        )
    );

    // Default settings.
    $settings->add(
        new admin_setting_heading(
            'local_autogroup/defaults',
            get_string('defaults', 'local_autogroup'),
            ''
        )
    );
    // TODO: group by sort module using optgroup when MDL-61248 is fixed.
    $choices = [];
    $modules = local_autogroup_get_sort_module_list();

    foreach ($modules as $sortedmodulename => $name) {
        $sortedmodulename = "\\local_autogroup\\sort_module\\$sortedmodulename";
        $module = new $sortedmodulename(new stdClass(), 1);
        $options = $module->get_config_options();
        // FIX array merge doesn't keep array keys.
        $choices += $options;
    }
    $settings->add(
        new admin_setting_configselect(
            'local_autogroup/filter',
            get_string('set_groupby', 'local_autogroup'),
            '',
            'department',
            $choices
        )
    );

    // Default roles.
    $settings->add(
        new admin_setting_heading(
            'local_autogroup/roleconfig',
            get_string('defaultroles', 'local_autogroup'),
            ''
        )
    );

    if ($roles = \get_all_roles()) {
        $roles = \role_fix_names($roles, null, ROLENAME_ORIGINAL);
        $assignableroles = \get_roles_for_contextlevels(CONTEXT_COURSE);
        foreach ($roles as $role) {
            // Default should be true for students.
            $default = ($role->id == 5);

            $settings->add(
                new admin_setting_configcheckbox(
                    'local_autogroup/eligiblerole_' . $role->id,
                    $role->localname,
                    '',
                    $default
                )
            );
        }
    }

    // Event listeners.
    $settings->add(
        new admin_setting_heading(
            'local_autogroup/events',
            get_string('events', 'local_autogroup'),
            get_string('events_help', 'local_autogroup')
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'local_autogroup/listenforrolechanges',
            get_string('listenforrolechanges', 'local_autogroup'),
            get_string('listenforrolechanges_help', 'local_autogroup'),
            true
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'local_autogroup/listenforuserprofilechanges',
            get_string('listenforuserprofilechanges', 'local_autogroup'),
            get_string('listenforuserprofilechanges_help', 'local_autogroup'),
            true
        )
    );
    if (isset($CFG->totara_build)) { // Only for Totara.
        $settings->add(
            new admin_setting_configcheckbox(
                'local_autogroup/listenforuserpositionchanges',
                get_string('listenforuserpositionchanges', 'local_autogroup'),
                get_string('listenforuserpositionchanges_help', 'local_autogroup'),
                true
            )
        );
    }
    $settings->add(
        new admin_setting_configcheckbox(
            'local_autogroup/listenforgroupchanges',
            get_string('listenforgroupchanges', 'local_autogroup'),
            get_string('listenforgroupchanges_help', 'local_autogroup'),
            false
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'local_autogroup/listenforgroupmembership',
            get_string('listenforgroupmembership', 'local_autogroup'),
            get_string('listenforgroupmembership_help', 'local_autogroup'),
            false
        )
    );

    $ADMIN->add('localplugins', $settings);
}
