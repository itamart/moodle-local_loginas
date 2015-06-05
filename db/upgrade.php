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

defined('MOODLE_INTERNAL') || die();

function xmldb_local_loginas_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    $newversion = 2014111000;
    if ($oldversion < $newversion) {
        // Move config settings from core to plugin.
        $loginasconfigs = array(
            'loginasusers',
            'loginasusernames',
            'courseusers'
        );

        foreach ($loginasconfigs as $config) {
            if ($setting = get_config('core', "loginas_$config")) {
                set_config($config, $setting, 'local_loginas');
                unset_config("loginas_$config");
            }
        }

        upgrade_plugin_savepoint(true, $newversion, 'local', 'loginas');
    }

    return true;
}
