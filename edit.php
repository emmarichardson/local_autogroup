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
 * A course object relates to a Moodle course and acts as a container
 * for multiple groups. Initialising a course object will automatically
 * load each autogroup group for that course into memory.
 *
 * @package    local_autogroup
 * @copyright  Mark Ward (me@moodlemark.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This file allows users with the correct capability to manage
 * settings for autogroup within a course.
 *
 * The code instantiates a form which is autoloaded from the file
 * classes/form/autogroup_set_settings.php
 */

namespace local_autogroup;

use context_course;
use local_autogroup_renderer;
use moodle_url;
use stdClass;

require_once(__DIR__ . '/../../config.php');

require_login();

require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/renderer.php');

if (!local_autogroup_plugin_is_enabled()) {
    // Do not allow editing for front page.
    die();
}

$courseid = optional_param('courseid', -1, PARAM_INT);
$groupsetid = optional_param('gsid', -1, PARAM_INT);
$action = optional_param('action', 'add', PARAM_TEXT);
$sortmodule = optional_param('sortmodule', null, PARAM_TEXT);

global $PAGE, $DB, $SITE;

switch ($action) {
    case 'edit':
    case 'delete':
        if ($groupsetid < 1) {
            throw new exception\invalid_autogroup_set_argument($groupsetid);
        }
        $data = $DB->get_record('local_autogroup_set', array('id' => $groupsetid));
        $courseid = (int)$data->courseid;
        $groupset = new domain\autogroup_set($DB, $data);

    case 'add':
        if ($courseid < 1 || $courseid == $SITE->id) {
            throw new exception\invalid_course_argument($courseid);
        }
        if (!isset($groupset)) {
            $groupset = new domain\autogroup_set($DB);
            $groupset->set_course($courseid);
        }
        break;
    default:
        // Do nothing with incorrect actions.
        die();
}

// Set the sort module if it doesn't match.
if ($sortmodule && $groupset->sortmoduleshortname != $sortmodule) {
    $groupset->set_sort_module($sortmodule);
}

$context = context_course::instance($courseid);

require_capability('local/autogroup:managecourse', $context);

$course = $DB->get_record('course', array('id' => $courseid));

$heading = \get_string('coursesettingstitle', 'local_autogroup', $course->shortname);

$PAGE->set_context($context);
$PAGE->set_url(local_autogroup_renderer::URL_COURSE_SETTINGS, array('courseid' => $courseid));
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->set_pagelayout('incourse');
$PAGE->set_course($course);

$output = $PAGE->get_renderer('local_autogroup');

$returnparams = array('action' => $action, 'sortmodule' => $sortmodule);
if ($groupset->exists()) {
    $returnparams['gsid'] = $groupset->id;
} else {
    $returnparams['courseid'] = $courseid;
}

$returnurl = new moodle_url(local_autogroup_renderer::URL_COURSE_SETTINGS, $returnparams);
$aborturl = new moodle_url(local_autogroup_renderer::URL_COURSE_MANAGE, array('courseid' => $courseid));

if ($action == 'delete') {
    $form = new form\autogroup_set_delete($returnurl, $groupset);
} else {
    $form = new form\autogroup_set_settings($returnurl, $groupset);
}

if ($form->is_cancelled()) {
    redirect($aborturl);
}
if ($data = $form->get_data()) {

    // Data relevant to both form types.
    $updategroupmembership = false;
    $cleanupold = isset($data->cleanupold) ? (bool)$data->cleanupold : true;

    if ($action == 'delete') {
        // User has selected "dont group".
        $groupset->delete($DB, $cleanupold);

        $groupset = new domain\autogroup_set($DB);
        $groupset->set_course($courseid);

        $updategroupmembership = true;
    } else {

        $options = new stdClass();
        $options->field = $data->groupby;

        if ($options->field != $groupset->grouping_by()) {
            // User has selected another option.
            $groupset->set_options($options);
            $groupset->save($DB, $cleanupold);

            $updategroupmembership = true;
        }

        // Check for role settings.
        if ($groupset->exists() && $roles = \get_all_roles()) {
            $roles = \role_fix_names($roles, null, ROLENAME_ORIGINAL);
            $newroles = array();
            foreach ($roles as $role) {
                $attributename = 'role_' . $role->id;
                if (isset($data->$attributename)) {
                    $newroles[] = $role->id;
                }
            }

            if ($groupset->set_eligible_roles($newroles, $DB)) {
                $groupset->save($DB, $cleanupold);

                $updategroupmembership = true;
            }
        }

    }

    if ($updategroupmembership) {
        $usecase = new usecase\verify_course_group_membership($courseid, $DB);
        $usecase->invoke();
    }

    redirect($aborturl);
}

echo $output->header();

$form->display();

echo $output->footer();
