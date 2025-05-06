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

namespace local_autogroup;

use moodle_exception;

/**
 * Exceptions allow us to handle specific situations which are unique
 * to local_autogroup in a way which we see best.
 *
 * This is particularly useful when we aren't 100% sure something is
 * going to work, as it means we can catch the problem and do something
 * differently to deal with it silently.
 *
 * @package local_autogroup
 */
abstract class exception extends moodle_exception {
    /**
     * @param string $errorcode
     * @param string $link
     * @param null $a
     * @param null $debuginfo
     */
    public function __construct($errorcode, $link = '', $a = null, $debuginfo = null) {
        $module = 'local_autogroup';
        parent::__construct($errorcode, $module, $link, $a, $debuginfo);
    }
}
