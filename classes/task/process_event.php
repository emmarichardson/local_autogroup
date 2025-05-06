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
 * Task that processes an event.
 *
 * @package   local_autogroup   
 * @author    Oscar Nadjar <oscar.nadjar@moodle.com>
 * @copyright Moodle US
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autogroup\task;
use local_autogroup\event_handler;

defined('MOODLE_INTERNAL') || die();

class process_event extends \core\task\adhoc_task {

    /**
     * Get task name
     */
    public function get_name() {
        return get_string('process_event', 'local_autogroup');
    }

    /**
     * Execute task
     */
    public function execute() {
        $event = (object) $this->get_custom_data();

        event_handler::process_event($event);
    }
}