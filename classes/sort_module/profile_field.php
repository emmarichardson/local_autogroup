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

namespace local_autogroup\sort_module;

use local_autogroup\sort_module;
use stdClass;

/**
 * Class course
 * @package local_autogroup\domain
 */
class profile_field extends sort_module {
    /**
     * @var string
     */
    private $field = '';

    /**
     * @param stdClass $config
     * @param int $courseid
     */
    public function __construct($config, $courseid) {
        if ($this->config_is_valid($config)) {
            $this->field = $config->field;
        }
        $this->courseid = (int)$courseid;
    }

    /**
     * @param stdClass $config
     * @return bool
     */
    public function config_is_valid(stdClass $config) {
        if (!isset($config->field)) {
            return false;
        }

        // Ensure that the stored option is valid.
        if (array_key_exists($config->field, $this->get_config_options())) {
            return true;
        }

        return false;
    }

    /**
     * Returns the options to be displayed on the autgroup_set
     * editing form. These are defined per-module.
     *
     * @return array
     */
    public function get_config_options() {
        $options = [
            'auth' => get_string('auth', 'local_autogroup'),
            'department' => get_string('department', 'local_autogroup'),
            'institution' => get_string('institution', 'local_autogroup'),
            'lang' => get_string('lang', 'local_autogroup'),
            'city' => get_string('city', 'local_autogroup'),
        ];
        return $options;
    }

    /**
     * @param stdClass $user
     * @return array $result
     */
    public function eligible_groups_for_user(stdClass $user) {
        $field = $this->field;
        if (isset($user->$field) && !empty($user->$field)) {
            return [$user->$field];
        }
        return [];
    }

    /**
     * @return bool|string
     */
    public function grouping_by() {
        if (empty ($this->field)) {
            return false;
        }
        return (string)$this->field;
    }

}
