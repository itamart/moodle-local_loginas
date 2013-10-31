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
 * @copyright  2013 Itamar Tzadok {@link http://substantialmethods.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die;

if ($ADMIN->fulltree) {
    $settings = new admin_settingpage('localsettingloginas', get_string('pluginname', 'local_loginas'));
    // Admin userids list
    $settings->add(new admin_setting_configtext(
        'loginas_loginasusers',
        get_string('loginasusers', 'local_loginas'),
        get_string('configloginasusers', 'local_loginas'),
        ''
    ));
    $settings->add(new admin_setting_configtext(
        'loginas_loginasusernames',
        get_string('loginasusernames', 'local_loginas'),
        get_string('configloginasusernames', 'local_loginas'),
        ''
    ));
    $settings->add(new admin_setting_configcheckbox(
        'loginas_courseusers',
        get_string('courseusers', 'local_loginas'),
        get_string('configcourseusers', 'local_loginas'),
        1
    ));
    if ($settings) {
        $ADMIN->add('localplugins', $settings);
    }
}
