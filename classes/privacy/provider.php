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
 * Privacy Subsystem implementation for local_autogroup.
 *
 * @package    local_autogroup
 * @copyright  2023 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autogroup\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\userlist;

/**
 * Privacy Subsystem for local_autogroup.
 *
 * @package    local_autogroup
 * @copyright  2023 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\data_provider {

    /**
     * Returns metadata about this plugin's privacy policy.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table(
            'local_autogroup_manual',
            [
                'id' => 'privacy:metadata:local_autogroup_manual:id',
                'userid' => 'privacy:metadata:local_autogroup_manual:userid',
                'groupid' => 'privacy:metadata:local_autogroup_manual:groupid',
            ],
            'privacy:metadata:local_autogroup_manual'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the given user.
     *
     * @param int $userid the userid to search.
     * @return contextlist the contexts in which data is contained.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $sql = "SELECT ctx.id
                  FROM {local_autogroup_manual} agm
                  JOIN {groups} g ON agm.groupid = g.id
                  JOIN {context} ctx ON g.courseid = ctx.instanceid AND ctx.contextlevel = :contextcourse
                 WHERE agm.userid = :userid";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, ['userid' => $userid, 'contextcourse' => CONTEXT_COURSE]);
        return $contextlist;
    }

    /**
     * Gets the list of users who have data with a context.
     *
     * @param userlist $userlist the userlist containing users who have data in this context.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context instanceof \context_course) {
            $sql = "SELECT agm.userid
                      FROM {local_autogroup_manual} agm
                      JOIN {groups} g ON agm.groupid = g.id
                     WHERE g.courseid = :courseid";
            $userlist->add_from_sql('userid', $sql, ['courseid' => $context->instanceid]);
        }
    }

    /**
     * Exports all data stored in provided contexts for user.
     *
     * @param approved_contextlist $contextlist the list of contexts to export for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist as $context) {
            // If not in course context, exit loop.
            if ($context instanceof \context_course) {

                $parentclass = [];

                // Get records for user ID.
                $rows = $DB->get_records('local_autogroup_manual', array('userid' => $userid));

                if (count($rows) > 0) {
                    $i = 0;
                    foreach ($rows as $row) {
                        $parentclass[$i]['userid'] = $row->userid;
                        $parentclass[$i]['groupid'] = $row->groupid;
                        $i++;
                    }
                }

                writer::with_context($context)->export_data(
                    [get_string('privacy:metadata:local_autogroup_manual', 'local_autogroup_manual')],
                    (object) $parentclass);
            }
        }
    }

    /**
     * Deletes data for all users in context.
     *
     * @param context $context The context to delete for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context instanceof \context_course) {
            if (!$DB->record_exists('groups', ['courseid' => $context->instanceid])) {
                return;
            }

            $select = "groupid IN (SELECT g.id FROM {groups} g WHERE courseid = :courseid)";
            $params = ['courseid' => $context->instanceid];

            $DB->delete_records_select('local_autogroup_manual', $select, $params);
        }
    }

    /**
     * Deletes all data in all provided contexts for user.
     *
     * @param approved_contextlist $contextlist the list of contexts to delete for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist as $context) {
            // If not in course context, skip context.
            if ($context instanceof \context_course) {
                if (!$DB->record_exists('groups', ['courseid' => $context->instanceid])) {
                    return;
                }

                $select = "userid = :userid AND groupid IN (SELECT g.id FROM {groups} g WHERE courseid = :courseid)";
                $params = ['userid' => $userid, 'courseid' => $context->instanceid];

                $DB->delete_records_select('local_autogroup_manual', $select, $params);
            }
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $userids = $userlist->get_userids();

        if ($context instanceof \context_course) {
            if (!$DB->record_exists('groups', ['courseid' => $context->instanceid])) {
                return;
            }

            list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

            $groupselect = "SELECT id FROM {groups} WHERE courseid = :courseid";
            $groupparams = ['courseid' => $context->instanceid];

            $select = "userid {$usersql} AND groupid IN ({$groupselect})";
            $params = $groupparams + $userparams;

            $DB->delete_records_select('local_autogroup_manual', $select, $params);
        }
    }
}
