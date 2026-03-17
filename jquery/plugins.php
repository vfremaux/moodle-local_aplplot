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
 * JQuery plugins declaration
 *
 * @package local_aplplot
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2026 Valery Fremaux (www.activeprolearn.com)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die;

$plugins = [
    'sparklines' => ['files' => ['sparklines/sparklines.min.js']],
    'jqplotjquery' => ['files' => ['jqplot/jquery.min.js']],
    'jqplot' => [
        'files' => [
            'jqplot/jquery.jqplot.min.js',
            'jqplot/excanvas.min.js',
            'jqplot/plugins/jqplot.dateAxisRenderer.min.js',
            'jqplot/plugins/jqplot.barRenderer.min.js',
            'jqplot/plugins/jqplot.highlighter.min.js',
            'jqplot/plugins/jqplot.canvasOverlay.min.js',
            'jqplot/plugins/jqplot.cursor.min.js',
            'jqplot/plugins/jqplot.categoryAxisRenderer.min.js',
            'jqplot/plugins/jqplot.pointLabels.min.js',
            'jqplot/plugins/jqplot.logAxisRenderer.min.js',
            'jqplot/plugins/jqplot.canvasTextRenderer.min.js',
            'jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js',
            'jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js',
            'jqplot/plugins/jqplot.enhancedLegendRenderer.min.js',
            'jqplot/plugins/jqplot.pieRenderer.min.js',
            'jqplot/plugins/jqplot.donutRenderer.min.js',
        ],
    ],
    'jqwidgets-core' => [
        'files' => [
            'jqwidgets/jqwidgets/jqxcore.js',
            'jqwidgets/jqwidgets/jqxdraw.js',
            'jqwidgets/jqwidgets/styles/jqx.base.css',
        ],
    ],
    'jqwidgets-bargauge' => [
        'files' => [
            'jqwidgets/jqwidgets/jqxbargauge.js',
        ],
    ],
    'jqwidgets-progressbar' => [
        'files' => [
            'jqwidgets/jqwidgets/jqxprogressbar.js',
        ],
    ],
    'jqwidgets-bulletchart' => [
        'files' => [
            'jqwidgets/jqwidgets/jqxcore.js',
            'jqwidgets/jqwidgets/jqxdata.js',
            'jqwidgets/jqwidgets/jqxtooltip.js',
            'jqwidgets/jqwidgets/jqxbulletchart.js',
            'jqwidgets/jqwidgets/styles/jqx.base.css',
        ],
    ],
    'jqwidgets-barchart' => [
        'files' => [
            'jqwidgets/jqwidgets/jqxcore.js',
            'jqwidgets/jqwidgets/jqxdraw.js',
            'jqwidgets/jqwidgets/jqxchart.core.js',
            'jqwidgets/jqwidgets/jqxdata.js',
            'jqwidgets/jqwidgets/styles/jqx.base.css',
        ],
    ],
    'jqwidgets-switchbutton' => [
        'files' => [
            'jqwidgets/jqwidgets/jqxcore.js',
            'jqwidgets/jqwidgets/jqxswitchbutton.js',
            'jqwidgets/jqwidgets/jqxcheckbox.js',
            'jqwidgets/jqwidgets/styles/jqx.base.css',
        ],
    ],
];
