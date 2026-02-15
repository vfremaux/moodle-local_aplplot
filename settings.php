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
 * @package     local_aplplot
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2015 Valery Fremaux (www.activeprolearn.com), Florence Labord (labord.florence@gmail.com)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Needs this condition or there is error on login page.
    $settings = new admin_settingpage('local_aplplot', get_string('pluginname', 'local_aplplot'));
    $ADMIN->add('localplugins', $settings);

    $key = 'local_aplplot/donutrenderercolors';
    $label = get_string('configdonutrenderercolors', 'local_aplplot');
    $desc = get_string('configdonutrenderercolors_desc', 'local_aplplot');
    $default = '#00cc00,#66ff66,#ff6666,#ff0000'; // default JQplot colors.
    $settings->add(new admin_setting_configtext($key, $label, $desc, $default));

    $key = 'local_aplplot/jqplotshadows';
    $label = get_string('configjqplotshadows', 'local_aplplot');
    $desc = get_string('configjqplotshadows_desc', 'local_aplplot');
    $default = true; // default JQplot shadows.
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, $default));

    $key = 'local_aplplot/googlemapsapikey';
    $label = get_string('configgooglemapsapikey', 'local_aplplot');
    $desc = get_string('configgooglemapsapikey_desc', 'local_aplplot');
    $default = '';
    $settings->add(new admin_setting_configtext($key, $label, $desc, $default));
}