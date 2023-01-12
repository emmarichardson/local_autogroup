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

require_once($CFG->dirroot . '/local/autogroup/lib.php');

/**
 * Class verify_group_population
 * @package local_autogroup\usecase
 */
class verify_group_population extends usecase {
    /**
     * @var domain\group
     */
    private $group;
    /**
     * @var bool $redirect
     */
    private $redirect = false;

    /**
     * @param int $groupid
     * @param \moodle_database $db
     */
    public function __construct($groupid, \moodle_database $db, \moodle_page $page) {
        $this->group = new domain\group($groupid, $db);

        // If we are viewing the group members we should redirect to safety.
        if ($page->has_set_url() && strstr($page->url, 'group/members.php?group=' . $groupid)) {
            $this->redirect = true;
        }
    }

    /**
     * @return void
     */
    public function invoke() {
        if (!local_autogroup_plugin_is_enabled()) {
            return;
        }

        $removed = false;
        if ($this->group->membership_count() == 0) {
            $removed = $this->group->remove();
        }

        if ($removed && $this->redirect) {
            $url = new \moodle_url('/group/index.php', array('id' => $this->group->courseid));
            \redirect($url);
        }
    }
}
