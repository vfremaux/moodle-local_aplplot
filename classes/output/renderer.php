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
 * Plugin renderer
 *
 * @package     local_aplplot
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2015 Valery Fremaux (www.activeprolearn.com)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace local_aplplot\output;

// phpcs:disable moodle.Commenting.ValidTags.Invalid
// Abusive PSR12 rule : adds useless spaces in string concatenation.
// phpcs:disable PSR12.Operators.OperatorSpacing.NoSpaceBefore
// phpcs:disable PSR12.Operators.OperatorSpacing.NoSpaceAfter
// phpcs:disable PSR12.Classes.OpeningBraceSpace.Found

use plugin_renderer_base;
use local_aplplot\{chart_bar, chart_line, chart_pie};
use StdClass;

/**
 * Renderer implementation.
 */
class renderer extends plugin_renderer_base {

    /**
     * Properties : max, width, height
     * @param string $name plot name.
     * @param object|array $data
     * @param object $properties
     */
    public function jqw_bargauge_simple($name, $data, $properties = null) {

        $properties = (object) $properties;

        if (empty($properties->max)) {
            $properties->max = 100;
        }
        if (empty($properties->width)) {
            $properties->width = 500;
        }
        if (empty($properties->height)) {
            $properties->height = 500;
        }
        if (empty($properties->cropwidth)) {
            $properties->cropwidth = 300;
        }
        if (empty($properties->cropheight)) {
            $properties->cropheight = 300;
        }
        if (!empty($properties->crop)) {
            $properties->cropheight = 300;
            $properties->cropwidth = $properties->crop;
        }
        if (empty($properties->animationduration)) {
            $properties->animationduration = 500;
        }

        $properties->name = $name;
        $properties->datalist = implode(', ', $data);

        $properties->w = $properties['cropwidth'];
        $properties->h = $properties['cropheight'];
        $properties->l = round(($properties->cropwidth - $properties->width) / 2);
        $properties->t = round(($properties->cropheight - $properties->height) / 2);

        return $this->output->render_from_template('local_aplplot/jqw_simplegauge', $properties);
    }

    /**
     * Prints a progress bar using JQPlot.
     * @param string $name
     * @param string $value
     * @param array $properties
     */
    public function jqw_progress_bar($name, $value, $properties = []) {

        $properties = (object)$properties;

        if (empty($properties->animation)) {
            $properties->animation = 0;
        }
        if (empty($properties->width)) {
            $properties->width = 150;
        }
        if (empty($properties->height)) {
            $properties->height = 24;
        }
        if (empty($properties->template)) {
            $properties->template = 'primary';
        }

        $properties->value = $value;
        $properties->name = $name;

        return $this->output->render_from_template('local_aplplot/jqw_progressbar', $properties);
    }

    /**
     * Prints a bulletchart
     * @param string $name
     * @param array $properties array with ('width', 'height', 'desc', 'barsize', 'tooltip') keys
     * @param array $ranges an array of range objects having ('start', 'end', 'color', 'opacity') keys
     * @param object $pointer an object with ('value', 'label', 'size', 'color') keys
     * @param object $target an object with ('value', 'label', 'size', 'color') keys
     * @param object $ticks an object with ('position', 'interval', 'size') keys
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function jqw_bulletchart($name, $properties, $ranges, $pointer, $target, $ticks = null) {

        $properties = (object)$properties;
        $properties->name = $name;

        if (is_null($ticks)) {
            $ticks = new StdClass();
            $ticks->position = 'both';
            $ticks->interval = 50;
            $ticks->size = 10;
        }

        $properties->barsize = $properties->barsize ?? 20;
        $properties->bgcolor = $properties->bgcolor ?? '#e0e0e0';
        $properties->bgopacity = $properties->bgopacity ?? 1;

        $properties->firstrange = true;
        if (empty($ranges)) {
            $ranges = [];
            $defaultrange = (object) [
                'start' => 0,
                'end' => 100,
                'color' => $properties->bgcolor,
                'opacity' => $properties->bgopacity,
                'firstrange' => $properties->firstrange,
            ];
            $ranges[] = $defaultrange;
            $properties->firstrange = false;
        }

        $rangesarr = [];
        foreach ($ranges as $range) {
            $rangesarr[] = '{startValue: '.$range->start.
                ', endValue: '.$range->end.
                ', color: \''.$range->color.
                '\', opacity: '.$range->opacity.'}';
        }
        $properties->ranges = implode(', ', $rangesarr);

        if (empty($pointer)) {
            if (!isset($pointer->size)) {
                $pointer->size = 80;
            }
            if (!isset($pointer->color)) {
                $pointer->color = '#000000';
            }
            $properties->pointer = $pointer;
        }

        if (empty($target)) {
            if (!isset($target->size)) {
                $target->size = 80;
            }
            if (!isset($target->color)) {
                $target->color = '#000000';
            }
            $properties->target = $target;
        }

        if (empty($properties->tooltip)) {
            $properties->tooltip = 'true';
        }

        return $this->output->render_from_template('local_aplplot/jqw_bulletchart', $properties);
    }

    /**
     * Data is expected as an array of objects, objects have fields mapped to char series.
     * @param string $name the graph title
     * @param array $data an array of source data, as an array of object containing one member per serie
     * @param array $properties a bag with keyed properties to serve graph parametrization
     * @param string $component the component name where strings come from.
     */
    public function jqw_bar_chart($name, $data, $properties, $component) {

        if (empty($data)) {
            return '';
        }

        $properties = (object)$properties;

        $properties->datalist = json_encode($data);
        $properties->name = $name;

        if (empty($properties->direction)) {
            $properties->direction = 'vertical';
        }

        if (empty($properties->xflip)) {
            $properties->xflip = 'false';
        }

        if (empty($properties->yflip)) {
            $properties->yflip = 'false';
        }

        // Guess series from first record.
        $firstarr = (array)$data[0];
        $properties->series = array_keys($firstarr);
        $properties->xaxis = array_shift($series);

        // Get other series and convert to a jsonified string.
        $seriestack = [];
        if (!empty($series)) {
            foreach ($series as $s) {
                $serieobj = new StdClass();
                $serieobj->dataField = $s;
                $serieobj->displayText = get_string($s, $component);
                $seriestack[] = $serieobj;
            }
        }
        $properties->seriesarr = json_encode($seriestack);

        $properties->padding = '{ left: 20, top: 5, right: 20, bottom: 5 }';
        if (!empty($properties->padding)) {
            $properties->padding = json_encode($properties->padding);
        }

        $properties->titlepadding = '{ left: 90, top: 0, right: 0, bottom: 10 }';
        if (!empty($properties->titlepadding)) {
            $properties->titlepadding = json_encode($properties->titlepadding);
        }

        return $this->output->render_from_template('local_aplplot/jqw_barchart', $properties);
    }

    /**
     * Provides a JQWidgets switch button
     * @param text $name
     * @param bool $value
     * @param array $properties an array of propertues with ('width', 'height', 'onchecked', 'onunchecked')
     */
    public function jqw_switchbutton($name, $value, $properties) {

        $properties = (object) $properties;

        $properties->name = $name;
        $properties->value = $value;
        $properties->initial = ($value) ? 'true' : 'false';
        if (empty($properties->width)) {
            $properties->width = 80;
        }

        if (empty($properties->height)) {
            $properties->height = 30;
        }

        return $this->output->render_from_template('local_aplplot/jqw_switchbutton', $properties);
    }

    /**
     * Renders a extended bar chart.
     *
     * @param chart_bar $chart The chart.
     * @return string.
     */
    public function render_chart_bar(chart_bar $chart) {
        return $this->render_chart($chart);
    }

    /**
     * Renders a extended line chart.
     *
     * @param chart_line $chart The chart.
     * @return string.
     */
    public function render_chart_line(chart_line $chart) {
        return $this->render_chart($chart);
    }

    /**
     * Renders a extended pie chart with better color control.
     *
     * @param chart_pie $chart The chart.
     * @return string.
     * @used-by customlabeltype_satisfaction
     */
    public function render_chart_pie(chart_pie $chart) {
        return $this->render_chart($chart);
    }

    /**
     * Renders a chart.
     * Handles it proper uniqid. Fixing possible occurrence of MDL-75379
     *
     * @param \core\chart_base $chart The chart.
     * @param bool $withtable Whether to include a data table with the chart.
     * @return string.
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function render_chart(\core\chart_base $chart, $withtable = true) {

        $localuniqid = uniqid();

        $chartdata = json_encode($chart);

        return $this->output->render_from_template('local_aplplot/chartjsplus_chart', (object) [
            'localuniqid' => $localuniqid,
            'chartdata' => $chartdata,
            'withtable' => $withtable,
            'width' => $chart->get_option('width'),
            'height' => $chart->get_option('height'),
        ]);
    }
}
