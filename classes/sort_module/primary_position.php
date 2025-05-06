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

if (isset($CFG->totara_build) && (int)$CFG->totara_build > 20150302) {
    /**
     * Class course
     *
     * @package local_autogroup\domain
     */
    class primary_position extends sort_module {

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
         * @param stdClass $user
         * @return array $result
         */
        public function eligible_groups_for_user(stdClass $user) {
            global $CFG;
            require_once("{$CFG->dirroot}/totara/hierarchy/prefix/position/lib.php");

            $field = $this->field . 'id';

            // Attempt to load the assignment.
            $primarypos = new \position(
                array(
                    'userid' => $user->id,
                    'type' => POSITION_TYPE_PRIMARY
                )
            );

            if (isset($primarypos->$field) && !empty($primarypos->$field)) {
                $method = 'parse_name_' . $this->field; // Like parse_name_manager() method.

                $group = new stdClass();
                $group->idnumber = $this->field . '_' . $primarypos->$field;
                $group->friendlyname = $this->$method($primarypos->$field);

                return array($group);
            }
            return [];
        }

        /**
         * Returns the options to be displayed on the autgroup_set
         * editing form. These are defined per-module.
         *
         * @return array
         */
        public function get_config_options() {
            $options = [
                'organisation' => get_string('organisation', 'totara_hierarchy'),
                'position' => get_string('position', 'totara_hierarchy'),
                'manager' => get_string('manager', 'totara_hierarchy'),
            ];
            return $options;
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

        /**
         * @param int $id
         * @return string
         */
        private function parse_name_manager($id) {
            $manager = \core_user::get_user($id);
            return 'manager: ' . $manager->firstname . ' ' . $manager->lastname;
        }

        /**
         * @param int $id
         * @return string
         */
        private function parse_name_organisation($id) {
            global $DB; // Until Totara provide a better method to get org data.

            return $DB->get_field('org', 'fullname', array('id' => $id));
        }

        /**
         * @param int $id
         * @return string
         */
        private function parse_name_position($id) {
            global $DB; // Until Totara provide a better method to get pos data.

            return $DB->get_field('pos', 'fullname', array('id' => $id));
        }

    }

}
