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

use \core\event;
use \local_autogroup\usecase;

/**
 * Class event_handler
 *
 * Functions which are triggered by Moodles events and carry out
 * the necessary logic to maintain membership.
 *
 * These functions almost entirely rely on the usecase classes to
 * carry out work. (see classes/usecase)
 *
 * @package local_autogroup
 */
class event_handler {
    /**
     * @param event\user_enrolment_created $event
     * @return mixed
     */
    public static function user_enrolment_created(event\user_enrolment_created $event) {
        $pluginconfig = get_config('local_autogroup');
        if (!$pluginconfig->listenforrolechanges) {
            return false;
        }

        global $DB;

        $courseid = (int)$event->courseid;
        $userid = (int)$event->relateduserid;

        $usecase = new usecase\verify_user_group_membership($userid, $DB, $courseid);
        return $usecase->invoke();
    }

    /**
     * @param event\group_member_added $event
     * @return bool
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function group_member_added(event\group_member_added $event) {
        if (self::triggered_by_autogroup($event)) {
            return false;
        }

        global $DB;
        $pluginconfig = get_config('local_autogroup');

        // Add to manually assigned list (local_autogroup_manual).
        $userid = (int)$event->relateduserid;
        $groupid = (int)$event->objectid;

        if (!$DB->record_exists('local_autogroup_manual', array('userid' => $userid, 'groupid' => $groupid))) {
            $record = (object)array('userid' => $userid, 'groupid' => $groupid);
            $DB->insert_record('local_autogroup_manual', $record);
        }

        if (!$pluginconfig->listenforgroupmembership) {
            return false;
        }

        $courseid = (int)$event->courseid;
        $userid = (int)$event->relateduserid;

        $usecase = new usecase\verify_user_group_membership($userid, $DB, $courseid);
        return $usecase->invoke();
    }

    /**
     * @param event\group_member_removed $event
     * @return bool
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function group_member_removed(event\group_member_removed $event) {
        if (self::triggered_by_autogroup($event)) {
            return false;
        }

        global $DB, $PAGE;
        $pluginconfig = get_config('local_autogroup');

        // Remove from manually assigned list (local_autogroup_manual).
        $userid = (int)$event->relateduserid;
        $groupid = (int)$event->objectid;

        if ($DB->record_exists('local_autogroup_manual', array('userid' => $userid, 'groupid' => $groupid))) {
            $DB->delete_records('local_autogroup_manual', array('userid' => $userid, 'groupid' => $groupid));
        }

        $groupid = (int)$event->objectid;
        $courseid = (int)$event->courseid;
        $userid = (int)$event->relateduserid;

        if ($pluginconfig->listenforgroupmembership) {
            $usecase1 = new usecase\verify_user_group_membership($userid, $DB, $courseid);
            $usecase1->invoke();
        }

        $usecase2 = new usecase\verify_group_population($groupid, $DB, $PAGE);
        $usecase2->invoke();
        return true;
    }

    /**
     * @param event\user_updated $event
     * @return mixed
     */
    public static function user_updated(event\user_updated $event) {
        $pluginconfig = get_config('local_autogroup');
        if (!$pluginconfig->listenforuserprofilechanges) {
            return false;
        }

        global $DB;

        $userid = (int)$event->relateduserid;

        $usecase = new usecase\verify_user_group_membership($userid, $DB);
        return $usecase->invoke();
    }

    /**
     * @param event\base $event
     * @return bool
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function group_created(event\base $event) {
        if (self::triggered_by_autogroup($event)) {
            return false;
        }

        $pluginconfig = get_config('local_autogroup');
        if (!$pluginconfig->listenforgroupchanges) {
            return false;
        }

        global $DB, $PAGE;

        $groupid = (int)$event->objectid;

        $usecase = new usecase\verify_group_idnumber($groupid, $DB, $PAGE);
        return $usecase->invoke();
    }

    /**
     * @param event\base $event
     * @return bool
     * @throws \Exception
     * @throws \dml_exception
     */
    public static function group_change(event\base $event) {
        if (self::triggered_by_autogroup($event)) {
            return false;
        }

        global $DB, $PAGE;

        $courseid = (int)$event->courseid;
        $groupid = (int)$event->objectid;

        // Remove from manually assigned list (local_autogroup_manual).
        if ($event->eventname === '\core\event\group_deleted') {
            $DB->delete_records('local_autogroup_manual', array('groupid' => $groupid));
        }

        $pluginconfig = get_config('local_autogroup');
        if (!$pluginconfig->listenforgroupchanges) {
            return false;
        }

        if ($DB->record_exists('groups', array('id' => $groupid))) {
            $verifygroupidnumber = new usecase\verify_group_idnumber($groupid, $DB, $PAGE);
            $verifygroupidnumber->invoke();
        }

        $verifycoursegroupmembership = new usecase\verify_course_group_membership($courseid, $DB);
        return $verifycoursegroupmembership->invoke();
    }

    /**
     * @param event\base $event
     * @return mixed
     */
    public static function role_change(event\base $event) {
        $pluginconfig = get_config('local_autogroup');
        if (!$pluginconfig->listenforrolechanges) {
            return false;
        }

        global $DB;

        $userid = (int)$event->relateduserid;

        $usecase = new usecase\verify_user_group_membership($userid, $DB);
        return $usecase->invoke();
    }

    /**
     * @param event\role_deleted $event
     * @return mixed
     */
    public static function role_deleted(event\role_deleted $event) {
        global $DB;

        $DB->delete_records('local_autogroup_roles', ['roleid' => $event->objectid]);
        unset_config('eligiblerole_' . $event->objectid, 'local_autogroup');

        return true;
    }

    /**
     * @param event\course_created $event
     * @return mixed
     */
    public static function course_created(event\course_created $event) {
        $config = get_config('local_autogroup');
        if (!$config->addtonewcourses) {
            return false;
        }

        global $DB;
        $courseid = (int)$event->courseid;

        $usecase = new usecase\add_default_to_course($courseid, $DB);
        return $usecase->invoke();
    }

    /**
     * @param event\course_restored $event
     * @return mixed
     */
    public static function course_restored(event\course_restored $event) {
        $config = get_config('local_autogroup');
        if (!$config->addtorestoredcourses) {
            return false;
        }

        global $DB;
        $courseid = (int)$event->courseid;

        $usecase = new usecase\add_default_to_course($courseid, $DB);
        return $usecase->invoke();
    }

    /**
     * @param \totara_core\event\position_updated $event
     * @return bool
     */
    public static function position_updated(\totara_core\event\position_updated $event) {
        $pluginconfig = get_config('local_autogroup');
        if (!$pluginconfig->listenforuserpositionchanges) {
            return false;
        }

        global $DB;

        $userid = (int)$event->relateduserid;

        $usecase = new usecase\verify_user_group_membership($userid, $DB);
        return $usecase->invoke();
    }

    /**
     * Checks the data of an event to see whether it was initiated
     * by the local_autogroup component
     *
     * @param event\base $event
     * @return bool
     */
    private static function triggered_by_autogroup(\core\event\base $event) {
        $data = $event->get_data();
        if (
            isset($data['other']) &&
            is_array($data['other']) &&
            isset($data['other']['component']) &&
            is_string($data['other']['component']) &&
            strstr($data['other']['component'], 'autogroup')
        ) {
            return true;
        }

        return false;
    }
}
