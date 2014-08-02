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
 * @package    local_loginas
 * @copyright  2013 Itamar Tzadok {@link http://substantialmethods.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die;

/**
 * Adds module specific settings to the settings block.
 *
 * @param settings_navigation $settings The settings navigation object
 * @param stdClass $context The node context
 */
function local_loginas_extends_settings_navigation(settings_navigation $settings, $context) {
    global $DB, $CFG, $PAGE, $USER, $OUTPUT;

    if (empty($PAGE->course->id)) {
        return;
    }

    $courseid = $PAGE->course->id;

    // Login as list by admin setting.
    if (is_siteadmin($USER)) {
        $loginas = $settings->add(get_string('loginas'));

        // Admin settings page.
        $url = new moodle_url('/admin/settings.php', array('section' => 'localsettingloginas'));
        $loginas->add(get_string('settings'), $url, $settings::TYPE_SETTING);

        // Users list.
        $loginasusers = array();

        // Since 2.6, use all the required fields (conditionally providing BC).
        $ufields = 'id, firstname, lastname';
        if (function_exists('get_all_user_name_fields')) {
            $ufields = 'id, ' . get_all_user_name_fields(true);
        }

        // Get users by id.
        if (!empty($CFG->loginas_loginasusers)) {
            $userids = explode(',', $CFG->loginas_loginasusers);
            if ($users = $DB->get_records_list('user', 'id', $userids, '', $ufields)) {
                $loginasusers = $users;
            }
        }

        // Get users by username.
        if (!empty($CFG->loginas_loginasusernames)) {
            $usernames = explode(',', $CFG->loginas_loginasusernames);
            if ($users = $DB->get_records_list('user', 'username', $usernames, '', $ufields)) {
                $loginasusers = $loginasusers + $users;
            }
        }

        // Add action links for specified users.
        if ($loginasusers) {
            $params = array('id' => $courseid, 'sesskey' => sesskey());
            foreach ($loginasusers as $userid => $lauser) {
                $url = new moodle_url('/course/loginas.php', $params);
                $url->param('user', $userid);
                $loginas->add(fullname($lauser, true), $url, $settings::TYPE_SETTING);
            }
        }
    }

    // Course login as not on front page.
    if ($courseid == SITEID) {
        return;
    }

    $coursecontext = context_course::instance($courseid);
    $loggedinas = method_exists('\core\session\manager', 'is_loggedinas') ?
            \core\session\manager::is_loggedinas() : session_is_loggedinas();
    if ($CFG->loginas_courseusers and !$loggedinas and has_capability('moodle/user:loginas', $coursecontext)) {
        if (!isset($loginas)) {
            $loginas = $settings->add(get_string('loginas'));
        }
        // Ajax link.
        $node = $loginas->add(get_string('courseusers', 'local_loginas'), 'javascript:void();', $settings::TYPE_SETTING);
        $node->add_class('local_loginas_setting_link');

        local_loginas_require_js($PAGE);
    }
}

/**
 * Sets required javascript.
 */
function local_loginas_require_js($page) {
    $modules = array('moodle-local_loginas-quickloginas', 'moodle-local_loginas-quickloginas-skin');
    $arguments = array(
        'courseid'            => $page->course->id,
        'ajaxurl'             => '/local/loginas/ajax.php',
        'url'                 => $page->url->out(false),
    );

    $function = 'M.local_loginas.quickloginas.init';
    $page->requires->yui_module($modules, $function, array($arguments));
    $page->requires->strings_for_js(array(
        'ajaxoneuserfound',
        'ajaxxusersfound',
        'ajaxnext25',
        'ajaxprev25',
        'loginasuser',
        'errajaxsearch'), 'local_loginas');
    $page->requires->strings_for_js(array('none', 'search'), 'moodle');
}

/**
 * Searches non-admin non-guest users in the context and returns paginated results.
 *
 * @global moodle_database $DB
 * @param string $search
 * @param bool $searchanywhere
 * @param int $page Starting at 0
 * @param int $perpage
 * @return array
 */
function local_loginas_get_users($contextid, $search='', $searchanywhere=false, $page=0, $perpage=25) {
    global $DB, $CFG, $USER, $COURSE;

    // Add some additional sensible conditions.
    $tests = array(
        "u.id <> :guestid",
        "u.id <> :cuserid",
        'u.deleted = 0',
        'u.confirmed = 1'
    );
    $params = array(
        'guestid' => $CFG->siteguest,
        'cuserid' => $USER->id,
    );
    // Add not admin condition.
    list($notinids, $aparams) = $DB->get_in_or_equal(explode(',', $CFG->siteadmins), SQL_PARAMS_NAMED, 'admin', false);
    $tests[] = "u.id $notinids";
    $params = array_merge($params, $aparams);

    // Search condition.
    if (!empty($search)) {
        $conditions = array('u.firstname', 'u.lastname');
        if ($searchanywhere) {
            $searchparam = '%' . $search . '%';
        } else {
            $searchparam = $search . '%';
        }
        $i = 0;
        foreach ($conditions as $key => $condition) {
            $conditions[$key] = $DB->sql_like($condition, ":con{$i}00", false);
            $params["con{$i}00"] = $searchparam;
            $i++;
        }
        $tests[] = '(' . implode(' OR ', $conditions) . ')';
    }

    // Groups condition.
    $joingroupmembers = '';
    $context  = context_course::instance($COURSE->id);
    if (groups_get_course_groupmode($COURSE) != NOGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
        // User course groups.
        $groups = groups_get_user_groups($COURSE->id);
        $groupids = reset($groups);

        // No groups, no users to login as.
        if (empty($groupids)) {
            return array('totalusers' => 0, 'users' => array());
        }

        // Some groups, add condition.
        $joingroupmembers = " JOIN {groups_members} gm ON gm.userid = u.id ";
        list($ingroupids, $gparams) = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED, 'group');
        $tests[] = "gm.groupid $ingroupids";
        $params = array_merge($params, $gparams);
    }

    // Get the users.
    $wherecondition = implode(' AND ', $tests);
    $fields = 'SELECT DISTINCT '. user_picture::fields('u', array('username', 'lastaccess'));
    $countfields = 'SELECT COUNT(u.id)';
    $sql   = "
        FROM
            {user} u
            JOIN {role_assignments} ra ON (ra.userid = u.id AND ra.contextid = :contextid)
            $joingroupmembers
        WHERE
            $wherecondition
    ";
    $order = ' ORDER BY lastname ASC, firstname ASC';

    $params['contextid'] = $contextid;
    $totalusers = $DB->count_records_sql($countfields . $sql, $params);
    $availableusers = $DB->get_records_sql($fields. $sql. $order, $params, $page * $perpage, $perpage);
    return array('totalusers' => $totalusers, 'users' => $availableusers);
}
