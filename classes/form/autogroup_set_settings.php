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

namespace local_autogroup\form;

use local_autogroup\domain;
use local_autogroup\form;

/**
 * Class course_settings
 * @package local_autogroup\form
 */
class autogroup_set_settings extends form {
    /**
     * @type domain\autogroup_set
     */
    protected $_customdata;

    /**
     *
     */
    public function definition() {
        $this->autogroup_set = $this->get_submitted_data();

        $this->add_group_by_options();

        $this->add_role_options();

        $this->add_action_buttons();
    }

    /**
     * @throws \coding_exception
     */
    private function add_group_by_options() {
        $mform = &$this->_form;

        $options = $this->_customdata->get_group_by_options();

        $mform->addElement('select', 'groupby', get_string('set_groupby', 'local_autogroup'), $options);
        $mform->setDefault('groupby', $this->_customdata->grouping_by());

        if ($this->_customdata->exists()) {
            // Offer to preserve existing groups.
            $mform->addElement('selectyesno', 'cleanupold', get_string('cleanupold', 'local_autogroup'));
            $mform->setDefault('cleanupold', 1);
        }
    }

    /**
     * @throws \coding_exception
     */
    private function add_role_options() {
        $mform = &$this->_form;

        $currentroles = $this->_customdata->get_eligible_roles();

        $mform->addElement('header', 'roles', get_string('set_roles', 'local_autogroup'));

        if ($roles = \get_all_roles()) {
            $roles = \role_fix_names($roles, null, ROLENAME_ORIGINAL);
            $assignableroles = \get_roles_for_contextlevels(CONTEXT_COURSE);
            foreach ($roles as $role) {
                $mform->addElement('checkbox', 'role_' . $role->id, $role->localname);
                if (in_array($role->id, $currentroles)) {
                    $mform->setDefault('role_' . $role->id, 1);
                }
            }
        }
    }

    /**
     *
     */
    public function extract_data() {
        $data = array();
        $this->set_data($data);
    }

}
