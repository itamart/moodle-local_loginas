<?php
// This file is part of Moodle - http://moodle.org/.
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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local
 * @subpackage loginas
 * @copyright 2012 Itamar Tzadok
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die;

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $datanode The node to add module settings to
 */
function local_loginas_extends_navigation(global_navigation $navigation) {
    global $DB, $CFG, $PAGE, $USER, $COURSE;

    if (!$settingsnav = $PAGE->__get('settingsnav')) {
        return;
    }
   
    if (is_siteadmin($USER)) {
        $loginas = $settingsnav->add(get_string('loginas'));

        // Admin settings page
        $url = new moodle_url('/admin/settings.php', array('section' => 'localsettingloginas'));
        $loginas->add(get_string('settings'), $url, $settingsnav::TYPE_SETTING);

        // Users list 
        if (!empty($CFG->loginas_loginasusers)) {
            $userids = explode(',', $CFG->loginas_loginasusers);
            $loginasusers = $DB->get_records_list('user', 'id', $userids, '', 'id,firstname,lastname');

            $params = array('id' => $COURSE->id, 'sesskey' => sesskey());
            foreach ($loginasusers as $userid => $lauser) {
                $url = new moodle_url('/course/loginas.php', $params);
                $url->param('user', $userid);
                $loginas->add(fullname($lauser, true), $url, $settingsnav::TYPE_SETTING);
            }
        }
    }
}
