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

defined('MOODLE_INTERNAL') || die();

$observers = [

    [
        'eventname' => '\core\event\user_enrolment_created',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => 'core\event\group_member_added',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => '\core\event\group_member_removed',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => '\core\event\user_updated',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => '\core\event\group_created',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => '\core\event\group_deleted',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => '\core\event\group_updated',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => '\core\event\role_assigned',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => '\core\event\role_unassigned',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => '\core\event\role_deleted',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => '\core\event\course_created',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => '\core\event\course_restored',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ],

    [
        'eventname' => '\totara_core\event\position_updated',
        'callback' => '\local_autogroup\event_handler::create_adhoc_task',
        'includefile' => 'local/autogroup/classes/event_handler.php',
        'internal' => true,
        'priority' => 0,
    ]

];
