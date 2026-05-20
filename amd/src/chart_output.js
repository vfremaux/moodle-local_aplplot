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
 * Chart output for APLCore extended chart.js.
 *
 * Proxy to the default output module.
 *
 * @copyright  2022 Valery Fremaux - www.activeprolearn.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @module     local_aplplot/chart_output
 */
define(['local_aplplot/chart_output_chartjs'], function(Output) {

    /**
     * @exports module:local_aplplot/chart_output
     * @extends {module:local_aplplot/chart_output_chartjs}
     */
    var defaultModule = Output;

    return defaultModule;

});
