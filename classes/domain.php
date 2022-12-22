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
 * Class domain
 *
 * Domain objects hold the logical behaviour of different elements
 * within the plugin.
 *
 * This base class is extended by specific classes within the domain
 * directory.
 *
 * @package local_autogroup
 */
abstract class domain {
    /**
     * @var array
     */
    protected $attributes = array('id', 'timecreated', 'timemodified');
    /**
     * @var int
     */
    protected $timecreated = 0;
    /**
     * @var int
     */
    protected $timemodified = 0;
    /**
     * @type int
     */
    private $id = 0;

    /**
     * Child classes will probably override this method.
     * @return string
     */
    public function __toString() {
        return get_class($this) . " " . $this->get_id();
    }

    /**
     * @return int
     */
    private function get_id() {
        return $this->id;
    }

    /**
     * @param $attribute
     * @return int|null
     */
    public function __get($attribute) {
        if (!\in_array($attribute, $this->attributes)) {
            return null;
        }

        if ($attribute == 'id') {
            return $this->get_id();
        }
        return $this->$attribute;
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function __set($attribute, $value) {
        if (!in_array($attribute, $this->attributes)) {
            return false;
        }

        if ($attribute == 'id') {
            $this->set_id($value);
        } else {
            $this->$attribute = $value;
        }

        // Timemodified will always reflect the last change.
        $this->timemodified = time();
        return true;
    }

    /**
     * @param int $id
     */
    private function set_id($id) {
        $this->id = (int)$id;
    }

    /**
     * A helper function to set the timestamps on this item correctly.
     */
    protected function update_timestamps() {
        if (!$this->exists()) {
            $this->timecreated = time();
        }
        $this->timemodified = time();
    }

    /**
     * @return bool
     */
    public function exists() {
        return $this->id > 0;
    }
}
