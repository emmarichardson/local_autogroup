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
 * Tests for local_autogroup.
 *
 * @package    local_autogroup
 * @category   test
 * @copyright  2021 My Learning Consultants
 * @author     David Saylor <david@mylearningconsultants.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/profile/definelib.php');

/**
 * Test class for local_autogroup_lib.
 *
 * @package    local_autogroup
 * @author    Oscar Nadjar <oscar.nadjar@moodle.com>
 * @copyright Moodle US
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_autogroup_lib_test extends advanced_testcase {
    /**
     * Test setup.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test that an user is assigned to a group based on a profile field.
     */
    public function test_autogroup_assign() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $fieldid = $this->create_profile_field();

        set_config('enabled', true, 'local_autogroup');
        set_config('addtonewcourses', true, 'local_autogroup');
        set_config('filter', $fieldid, 'local_autogroup');
        set_config('adhoceventhandler', false, 'local_autogroup');

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        profile_save_custom_fields($user->id, ['test' => 'Test 1']);
        user_update_user($user, false, true);

        $groups = groups_get_all_groups($course->id, $user->id);
        $this->assertCount(1, $groups);
        $count = 1;
        foreach ($groups as $group) {
            $this->assertEquals('Test ' . $count, $group->name);
            $count++;
        }
    }

    /**
     * Same as above but with adhoc event handler.
     */
    public function test_autogroup_adhoc_assign() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $fieldid = $this->create_profile_field();

        set_config('enabled', true, 'local_autogroup');
        set_config('addtonewcourses', true, 'local_autogroup');
        set_config('filter', $fieldid, 'local_autogroup');
        set_config('adhoceventhandler', true, 'local_autogroup');

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        profile_save_custom_fields($user->id, ['test' => 'Test 1']);
        user_update_user($user, false, true);

        $this->execute_adhoc();
        $groups = groups_get_all_groups($course->id, $user->id);
        $this->assertCount(1, $groups);
        $count = 1;
        foreach ($groups as $group) {
            $this->assertEquals('Test ' . $count, $group->name);
            $count++;
        }
    }

    /**
     * A queued group deletion must be harmless after its course has gone.
     */
    public function test_group_change_ignores_deleted_course_and_cleans_manual_assignment() {
        global $DB;

        $groupid = 987654;
        $userid = 123456;
        $DB->insert_record('local_autogroup_manual', (object) [
            'groupid' => $groupid,
            'userid' => $userid,
        ]);

        $event = (object) [
            'eventname' => '\core\event\group_deleted',
            'courseid' => 987654,
            'objectid' => $groupid,
            'other' => (object) [],
        ];

        $this->assertFalse(\local_autogroup\event_handler::group_change($event));
        $this->assertFalse($DB->record_exists('local_autogroup_manual', ['groupid' => $groupid]));
    }

    /**
     * A queued membership removal must be harmless after all referenced records have gone.
     */
    public function test_group_member_removed_ignores_deleted_records() {
        global $DB;

        set_config('listenforgroupmembership', true, 'local_autogroup');

        $groupid = 987653;
        $userid = 123455;
        $DB->insert_record('local_autogroup_manual', (object) [
            'groupid' => $groupid,
            'userid' => $userid,
        ]);

        $event = (object) [
            'eventname' => '\core\event\group_member_removed',
            'courseid' => 987653,
            'objectid' => $groupid,
            'relateduserid' => $userid,
            'other' => (object) [],
        ];

        $this->assertTrue(\local_autogroup\event_handler::group_member_removed($event));
        $this->assertFalse($DB->record_exists('local_autogroup_manual', [
            'groupid' => $groupid,
            'userid' => $userid,
        ]));
    }

    /**
     * Course deletion removes the autogroup set and its role records.
     */
    public function test_course_deleted_cleans_plugin_configuration() {
        global $DB;

        $courseid = 987652;
        $setid = $DB->insert_record('local_autogroup_set', (object) [
            'courseid' => $courseid,
            'sortmodule' => 'profile_field',
            'sortconfig' => '{"field":"department"}',
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
        $DB->insert_record('local_autogroup_roles', (object) [
            'setid' => $setid,
            'roleid' => 1,
        ]);

        $event = (object) ['objectid' => $courseid];

        $this->assertTrue(\local_autogroup\event_handler::course_deleted($event));
        $this->assertFalse($DB->record_exists('local_autogroup_set', ['id' => $setid]));
        $this->assertFalse($DB->record_exists('local_autogroup_roles', ['setid' => $setid]));
    }

    /**
     * User deletion removes preserved manual membership records.
     */
    public function test_user_deleted_cleans_manual_assignments() {
        global $DB;

        $userid = 123454;
        $DB->insert_record('local_autogroup_manual', (object) [
            'groupid' => 987651,
            'userid' => $userid,
        ]);

        $event = (object) ['objectid' => $userid];

        $this->assertTrue(\local_autogroup\event_handler::user_deleted($event));
        $this->assertFalse($DB->record_exists('local_autogroup_manual', ['userid' => $userid]));
    }

    /**
     * Deleting a set removes manual rows for its groups, not rows whose group ID equals the set ID.
     */
    public function test_autogroup_set_delete_uses_group_ids_for_manual_cleanup() {
        global $DB;

        set_config('adhoceventhandler', true, 'local_autogroup');

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $placeholder = $this->getDataGenerator()->create_group(['courseid' => $course->id]);

        do {
            $setid = $DB->insert_record('local_autogroup_set', (object) [
                'courseid' => $course->id,
                'sortmodule' => 'profile_field',
                'sortconfig' => '{"field":"department"}',
                'timecreated' => time(),
                'timemodified' => time(),
            ]);
        } while ($setid <= $placeholder->id + 1);

        $group = $this->getDataGenerator()->create_group([
            'courseid' => $course->id,
            'name' => 'Autogroup test',
            'idnumber' => "autogroup|{$setid}|test",
        ]);
        $DB->insert_record('local_autogroup_manual', (object) [
            'groupid' => $group->id,
            'userid' => $user->id,
        ]);
        $DB->insert_record('local_autogroup_manual', (object) [
            'groupid' => $setid,
            'userid' => $user->id,
        ]);

        $record = $DB->get_record('local_autogroup_set', ['id' => $setid], '*', MUST_EXIST);
        $set = new \local_autogroup\domain\autogroup_set($DB, $record);

        $this->assertTrue($set->delete($DB));
        $this->assertFalse($DB->record_exists('local_autogroup_manual', ['groupid' => $group->id]));
        $this->assertTrue($DB->record_exists('local_autogroup_manual', ['groupid' => $setid]));
    }

    /**
     * Create a profile field.
     *
     * @return int
     */
    private function create_profile_field() {
        global $CFG, $DB, $PAGE;

        $PAGE->set_context(context_system::instance());

        $data = (object) [
                'name' => 'Test'
        ];
        $data->sortorder = $DB->count_records('user_info_category') + 1;
        $data->id = $DB->insert_record('user_info_category', $data, true);
        $createdcategory = $DB->get_record('user_info_category', array('id' => $data->id));
        \core\event\user_info_category_created::create_from_category($createdcategory)->trigger();

        $field = (object) [
                'shortname' => 'test',
                'name' => 'Test',
                'datatype' => 'text',
                'description' => 'A test field',
                'descriptionformat' => 1,
                'categoryid' => 6,
                'sortorder' => 2,
                'required' => 0,
                'locked' => 1,
                'visible' => 0,
                'forceunique' => 0,
                'signup' => 1,
                'defaultdata' => '',
                'defaultdataformat' => 0,
                'param1' => '30',
                'param2' => '100',
                'param3' => '0',
                'param4' => '',
                'param5' => '',
        ];

        $field->id = $DB->get_field('user_info_field', 'id', ['shortname' => $field->shortname]);
        require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/define.class.php');
        $newfield = 'profile_define_'.$field->datatype;
        $formfield = new $newfield();
        $field->categoryid = $createdcategory->id;
        $formfield->define_save($field);

        profile_reorder_fields();
        profile_reorder_categories();

        return $field->id;
    }

    /**
     * Execute adhoc tasks.
     */
    public function execute_adhoc() {
        global $DB;
        $tasks = $DB->get_records('task_adhoc', ['component' => 'local_autogroup']);
        foreach ($tasks as $taskinfo) {
            $task = new \local_autogroup\task\process_event();
            $task->set_custom_data(json_decode($taskinfo->customdata));
            $task->execute();
            $DB->delete_records('task_adhoc', ['id' => $taskinfo->id]);
        }
    }
}