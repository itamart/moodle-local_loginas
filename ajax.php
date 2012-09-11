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
 * This file generates AJAX list of login as links of course users
 *
 * @package    local
 * @subpackage loginas
 * @copyright  2012 Itamar Tzadok
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require('../../config.php');
require_once("$CFG->dirroot/local/loginas/lib.php");

$id      = required_param('id', PARAM_INT); // course id
$action  = required_param('action', PARAM_ACTION);

$PAGE->set_url(new moodle_url('/loginas/ajax.php', array('id'=>$id, 'action'=>$action)));

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

if ($course->id == SITEID) {
    throw new moodle_exception('invalidcourse');
}

require_login($course);
require_capability('moodle/user:loginas', $context);
require_sesskey();

echo $OUTPUT->header(); // send headers

$outcome = new stdClass;
$outcome->success = true;
$outcome->response = new stdClass;
$outcome->error = '';

switch ($action) {
    case 'searchusers':
        $search  = optional_param('search', '', PARAM_RAW);
        $page = optional_param('page', 0, PARAM_INT);
        $outcome->response = local_loginas_get_users($context->id, $search, true, $page);
        $extrafields = get_extra_user_fields($context);
        foreach ($outcome->response['users'] as $key => &$user) {
             $user->picture = $OUTPUT->user_picture($user);
            $user->fullname = fullname($user);
            $fieldvalues = array();
            foreach ($extrafields as $field) {
                $fieldvalues[] = s($user->{$field});
                unset($user->{$field});
            }
            $user->extrafields = implode(', ', $fieldvalues);
        }
        $outcome->success = true;
        break;

    default:
        throw new moodle_exception('unknowajaxaction', 'local_loginas');
}

echo json_encode($outcome);