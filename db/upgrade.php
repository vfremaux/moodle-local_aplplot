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
 * @copyright   2026 Valery Fremaux (www.activeprolearn.com), Florence Labord (labord.florence@gmail.com)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Standard upgrade handler.
 *
 * Each time we upgrade the libs we will collect information about installed plugins of the mylearnignfactory catalog.
 *
 * @param int $oldversion
 */
function xmldb_local_aplplot_upgrade($oldversion = 0) {
    global $CFG, $THEME, $DB;

    $result = true;

    $dbman = $DB->get_manager();

    return $result;
}
