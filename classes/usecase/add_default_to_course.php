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

namespace local_autogroup\usecase;

defined('MOODLE_INTERNAL') || die();

use local_autogroup\domain;
use local_autogroup\usecase;
use moodle_database;
use stdClass;

require_once($CFG->dirroot . '/local/autogroup/lib.php');

/**
 * Class add_default_to_course
 * @package local_autogroup\usecase
 */
class add_default_to_course extends usecase {
    /**
     * @var bool
     */
    private $addtonewcourse = false;
    /**
     * @var domain\group
     */
    private $courseid;
    /**
     * @var moodle_database
     */
    private $db;
    /**
     * @var stdClass
     */
    private $pluginconfig;

    /**
     * @param int $courseid
     * @param moodle_database $db
     */
    public function __construct($courseid, moodle_database $db) {
        $this->courseid = (int)$courseid;
        $this->db = $db;

        $this->pluginconfig = get_config('local_autogroup');

        $this->addtonewcourse = true;

        if ($db->record_exists('local_autogroup_set', array('courseid' => $courseid))) {
            // This shouldn't happen, but we want to ensure we avoid duplicates.
            $this->addtonewcourse = false;
        }
    }

    /**
     * @return void
     */
    public function invoke() {
        if ($this->addtonewcourse) {

            // First generate a new autogroup_set object.
            $autogroupset = new domain\autogroup_set($this->db);
            $autogroupset->set_course($this->courseid);

            // Set the sorting options to global default.
            $options = new stdClass();
            $options->field = $this->pluginconfig->filter;
            if (is_numeric($this->pluginconfig->filter)) {
                $autogroupset->set_sort_module('user_info_field');
            }

            $autogroupset->set_options($options);

            // Now we can set the eligible roles to global default.
            if ($roles = \get_all_roles()) {
                $roles = \role_fix_names($roles, null, ROLENAME_ORIGINAL);
                $newroles = array();
                foreach ($roles as $role) {
                    $attributename = 'eligiblerole_' . $role->id;

                    if (isset($this->pluginconfig->$attributename) &&
                        $this->pluginconfig->$attributename) {

                        $newroles[] = $role->id;

                    }
                }

                $autogroupset->set_eligible_roles($newroles);
            }

            // Save all that to db.
            $autogroupset->save($this->db);

            $usecase = new usecase\verify_course_group_membership($this->courseid, $this->db);
            $usecase->invoke();
        }
    }
}
