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
 * grouping logic for autogroups within a course.
 */

namespace local_autogroup;

require_once(__DIR__ . '/../../config.php');

require_login();

require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/renderer.php');

use context_course;
use local_autogroup_renderer;

$courseid = required_param('courseid', PARAM_INT);
$context = context_course::instance($courseid);

require_capability('local/autogroup:managecourse', $context);

global $PAGE, $DB, $SITE;

if ($courseid == $SITE->id || !local_autogroup_plugin_is_enabled()) {
    // Do not allow editing for front page.
    die();
}

$course = $DB->get_record('course', array('id' => $courseid));
$groupsets = $DB->get_records('local_autogroup_set', array('courseid' => $courseid));

foreach ($groupsets as $k => $groupset) {
    $groupsets[$k] = new domain\autogroup_set($DB, $groupset);
}

$heading = \get_string('coursesettingstitle', 'local_autogroup', $course->shortname);

$PAGE->set_context($context);
$PAGE->set_url(local_autogroup_renderer::URL_COURSE_MANAGE, array('courseid' => $courseid));
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->set_pagelayout('incourse');
$PAGE->set_course($course);

$output = $PAGE->get_renderer('local_autogroup');

echo $output->header();

echo $output->intro_text(count($groupsets));

echo $output->groupsets_table($groupsets);

echo $output->add_new_groupset($courseid);

echo $output->footer();
