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

/**
 * Class usecase
 *
 * Usecases offer a mechanism through which the plugin can execute
 * commonly repeated processes. This class is extended by the files
 * in classes/usecase.
 *
 * All usecases are triggered through the invoke magic method, which
 * effectively means instantiating the class and then using it like
 * a function:
 *
 * $usecase = new usecase($a, $b, $c);
 * $usecase();
 *
 * @package local_autogroup
 */
abstract class usecase {

    /**
     * @return mixed
     */
    abstract public function invoke();

    /**
     * Child classes will probably override this method.
     * @return string
     */
    public function __toString() {
        return get_class($this);
    }

    /**
     * @param $attribute
     * @return null
     */
    public function __get($attribute) {
        return null;
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function __set($attribute, $value) {
        return false;
    }
}
