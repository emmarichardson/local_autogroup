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
use \html_writer;

/**
 * Class course_settings
 * @package local_autogroup\form
 */
class autogroup_set_delete extends form {
    /**
     * @type domain\autogroup_set
     */
    protected $_customdata;

    /**
     *
     */
    public function definition() {
        $this->groupsetdata = $this->get_submitted_data();

        $this->add_dialogue();

        $this->add_action_buttons(true, get_string('delete'));
    }

    private function add_dialogue() {
        $mform = $this->_form;

        $mform->addElement('header', 'delete', get_string('delete'));

        $html = html_writer::tag('p', get_string('confirmdelete', 'local_autogroup'));
        $mform->addElement('html', $html);

        if ($this->_customdata->exists()) {
            // Offer to preserve existing groups.
            $mform->addElement('selectyesno', 'cleanupold', get_string('cleanupold', 'local_autogroup'));
            $mform->setDefault('cleanupold', 1);
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
