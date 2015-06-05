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
 * @package local_loginas
 * @copyright 2015 Itamar Tzadok {@link http://substantialmethods.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('localsettingloginas', get_string('pluginname', 'local_loginas'));
    // Admin userids list.
    $settings->add(new admin_setting_configtext(
        'local_loginas/loginasusers',
        new lang_string('loginasusers', 'local_loginas'),
        new lang_string('configloginasusers', 'local_loginas'),
        ''
    ));
    $settings->add(new admin_setting_configtext(
        'local_loginas/loginasusernames',
        get_string('loginasusernames', 'local_loginas'),
        get_string('configloginasusernames', 'local_loginas'),
        ''
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_loginas/courseusers',
        new lang_string('courseusers', 'local_loginas'),
        new lang_string('configcourseusers', 'local_loginas'),
        '1'
    ));
    $ADMIN->add('localplugins', $settings);
}
