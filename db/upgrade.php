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

/**
 * upgrade this autogroup plugin
 * @param int $oldversion The old version of the assign module
 * @return bool
 */
function xmldb_local_autogroup_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2016062201) {

        // Convert "Strict enforcement" settings to new toggles.
        $pluginconfig = get_config('local_autogroup');
        if ($pluginconfig->strict) {
            set_config('listenforgroupchanges', true, 'local_autogroup');
            set_config('listenforgroupmembership', true, 'local_autogroup');
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2016062201, 'local', 'autogroup');
    }

    if ($oldversion < 2018102300) {
        // Define table local_autogroup_manual to be created.
        $table = new xmldb_table('local_autogroup_manual');

        // Adding fields to table local_autogroup_manual.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_autogroup_manual.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_autogroup_manual.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Autogroup savepoint reached.
        upgrade_plugin_savepoint(true, 2018102300, 'local', 'autogroup');
    }

    if ($oldversion < 2019010300) {
        require_once(__DIR__ . '/../classes/event_handler.php');

        $roleids = array_keys(get_all_roles());
        list($sql, $params) = $DB->get_in_or_equal($roleids, SQL_PARAMS_QM, 'param', false);
        $invalidroleids = $DB->get_fieldset_select('local_autogroup_roles', 'DISTINCT roleid', 'roleid ' . $sql, $params);
        foreach ($invalidroleids as $roleid) {
            $event = \core\event\role_deleted::create(
                [
                    'context' => context_system::instance(),
                    'objectid' => $roleid,
                    'other' => [
                        'shortname' => 'invalidroletoremove'
                    ]
                ]
            );
            local_autogroup\event_handler::role_deleted($event);
        }
        upgrade_plugin_savepoint(true, 2019010300, 'local', 'autogroup');
    }

    if ($oldversion < 2022062500.01) {
        $sql = "DELETE LAM FROM {local_autogroup_manual} LAM JOIN {groups} G ON (G.id = LAM.groupid) WHERE ";
        $sql .= $DB->sql_like('G.idnumber', ':autogroupidnum', true, true, true);

        $DB->execute($sql, ['autogroupidnum' => 'autogroup|%']);

        upgrade_plugin_savepoint(true, 2022062500.01, 'local', 'autogroup');
    }

    return true;
}
