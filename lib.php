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

/**
 * Functions within this file all conform to Moodle standard function names
 * and are referred to by core Moodle code.
 *
 * Each of these functions are under the global namespace, so each
 * function name should being with "local_autogroup" to prevent conflicts
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

if ($CFG->branch == '27') {
    /**
     * Generates the course settings navigation for Moodle 27
     *
     * @param settings_navigation $settingsnav
     * @param context $context
     * @return bool
     * @throws coding_exception
     */
    function local_autogroup_extends_settings_navigation(settings_navigation $settingsnav, context $context) {
        if (!local_autogroup_plugin_is_enabled()) {
            return false;
        }

        local_autogroup_amend_settings_structure($settingsnav, $context);

        return true;
    }
} else {
    /**
     * Generates the course settings navigation for Moodle 28 and higher
     *
     * @param settings_navigation $settingsnav
     * @param context $context
     * @return bool
     * @throws coding_exception
     */
    function local_autogroup_extend_settings_navigation(settings_navigation $settingsnav, context $context) {
        if (!local_autogroup_plugin_is_enabled()) {
            return false;
        }

        local_autogroup_amend_settings_structure($settingsnav, $context);

        return true;
    }
}
