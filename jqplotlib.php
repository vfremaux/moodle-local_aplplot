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
 * JQPlot wrapper library
 *
 * @package     local_aplplot
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2015 Valery Fremaux (www.activeprolearn.com)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace local_aplplot;

// phpcs:disable moodle.Commenting.ValidTags.Invalid
// Abusive PSR12 rule : adds useless spaces in string concatenation.
// phpcs:disable PSR12.Operators.OperatorSpacing.NoSpaceBefore
// phpcs:disable PSR12.Operators.OperatorSpacing.NoSpaceAfter
// phpcs:disable PSR12.Classes.OpeningBraceSpace.Found

use moodle_exception;
use coding_exception;
use stdClass;

/**
 * Main JQPlot library wrapper
 * This wrapper is not intended to cover ALL possibilites of jqplot
 * but is accumulative to all use case APL plugins encountered.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class jqplot {

    /**
     * JS Requires
     */
    public static function require_jqplot_libs() {
        global $PAGE;
        static $jqplotloaded = false;

        if ($jqplotloaded) {
            return;
        }
        $PAGE->requires->js_call_amd('local_aplplot/jqplot', 'init'); // Common setup.
        $PAGE->requires->jquery_plugin('jqplot', 'local_aplplot');
        $jqplotloaded = true;
    }

    /**
     * DATA FORMATER : makes lines with data.
     * Expectation is a multicurve timeline array.
     * subarray 0 contains dates
     * subarrays 1 to n contain curves to plot
     * @param array $data
     * @param int $curveix
     */
    public static function timeline(&$data, $curveix) {
        $str = "line{$curveix} = ";

        foreach ($data as $datum) {
            if (!isset($datum[$curveix])) {
                debugging("missing index");
            }
            $points[] = '[\''.$datum[0].'\','.$datum[$curveix].']';
        }
        $str .= '['.implode(',', $points).']';
        $str .= ';';

        return $str;
    }

    /**
     * DATA FORMATER : formats data as a serie of x,y coordinates.
     * Expectation is a single x => y array.
     * @param array $data
     * @param int $varname the name of the generated JS variable.
     */
    public static function rawline(&$data, $varname) {

        if (empty($data)) {
            return;
        }

        $str = "$varname = ";

        foreach ($data as $x => $y) {
            $points[] = "[$x,$y]";
        }
        $str .= '['.implode(',', $points).']';
        $str .= ';';
        return $str;
    }

    /**
     * DATA FORMATER : formats data as a serie of points associated to a label.
     * @param array $data
     * @param int $varname the name of the generated JS variable.
     */
    public static function labelled_rawline(&$data, $varname) {

        $str = "$varname = ";

        $points = [];
        $num = count($data[0]);
        for ($i = 0; $i < $num; $i++) {
            $points[] = "['{$data[0][$i]}','{$data[1][$i]}', '{$data[2][$i]}']";
        }
        $str .= '['.implode(',', $points).']';
        $str .= ';';
        return $str;
    }

    /**
     * prints any JQplot graph type given a php descriptor and dataset.
     * @param string $htmlid unique HTML id for the graph
     * @param object $graph 
     * @param array $data
     * @param int $width graph's width
     * @param int $height graph's height
     * @param string $addstyle Additional css
     * @param bool $return
     * @param array $ticks additional defs for axis ticks
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public static function print_graph($htmlid, $graph, $data, $width = 800, $height = 600,
            $addstyle = '', $return = false, $ticks = null) {
        global $plotid;
        global $OUTPUT;
        static $instance = 0;

        $template = new StdClass();
        $template->htmlid = $htmlid.'_'.$instance;
        $template->addstyle = $addstyle;
        $instance++;
        $template->width = $width ?? 800;
        $template->height = $height ?? 450;

        if (!is_null($ticks)) {
            $template->hasticks = true;
            $template->ticksvalues = implode("','", $ticks);
        }

        $varsetlist = json_encode($data);
        // Fixing data to arrays.
        $varsetlist = str_replace('{', '[', $varsetlist);
        $varsetlist = str_replace('}', ']', $varsetlist);

        $template->varsetlist = preg_replace('/"(\d+)\"/', "$1", $varsetlist);
        $jsongraph = json_encode($graph);
        $jsongraph = preg_replace('/"\$\$\.(.*?)\"/', "$1", $jsongraph);
        $template->jsongraph = preg_replace('/"(\$\.jqplot.*?)\"/', "$1", $jsongraph);

        $plotid++;

        $str = $OUTPUT->render_from_template('local_aplplot/jqxgraph', $template);

        if ($return) {
            return $str;
        }
        echo $str;
    }

    /**
     * Prints a bidimensional graph (map)
     * Defaults to a percent graph 100 x 100.
     * @param string $htmlid unique HTML id for the graph
     * @param arrayref &$data data to print in graph
     * @param string $title title for the graph
     * @param array $options options
     * @used-by local_advancedperfs
     */
    public static function print_labelled_graph($htmlid, &$data, $title, $options) {
        global $plotid;
        global $OUTPUT;
        static $instance = 0;

        // Check option defaults.

        $options['xmin'] = $options['xmin'] ?? 0;
        $options['ymin'] = $options['ymin'] ?? 0;
        $options['xmax'] = $options['xmax'] ?? 100;
        $options['ymax'] = $options['ymax'] ?? 100;
        $options['xunit'] = $options['xunit'] ?? '\\%';
        $options['yunit'] = $options['yunit'] ?? '\\%';
        $options['xlabel'] = str_replace("'", "\\'", $options['xlabel'] ?? '');
        $options['ylabel'] = str_replace("'", "\\'", $options['ylabel'] ?? '');

        $htmlid = $htmlid.'_'.$instance;
        $instance++;

        $template = (object) $options;
        $template->htmlid = $htmlid;

        $title = addslashes($title);
        $template->title = str_replace("'", "\\'", $title);
        $template->rawline = self::labelled_rawline($data, 'data_'.$htmlid);

        $plotid++;

        return $OUTPUT->render_from_template('local_aplplot/jqxlabelledgraph', $template);
    }

    /**
     * DATA FORMATER : formats data for a bar graph.
     * Plots multiple date series, formatting the data array.
     * @param string $name
     * @param array $data
     */
    public static function barline($name, $data) {

        $str = "$name = ";

        $i = 1;
        foreach ($data as $datum) {
            $points[] = '['.$datum.','.$i.']';
            $i++;
        }
        $str .= '['.implode(',', $points).']';
        $str .= ';';
        return $str;
    }

    /**
     * DATA FORMATER : formats data for a simple line graph.
     * @param string $name
     * @param array $data
     */
    public static function simplebarline($name, $data) {

        $str = "var $name = ";
        $str .= '['.implode(',', array_values($data)).']';
        $str .= ';';
        return $str;
    }

    /**
     * prints a simple bargraph.
     * @param string $htmlid an html identifier seed. Will be appended with an automatic instance index.
     * @param arrayref $data an associative array with one simple 'label' => value pairs.
     * @param arrayref $ticks an associative array with one simple 'label' => value pairs.
     * @param string $title a text title.
     * @param array $options some rendering options to inject in jqplot template, to override defaults.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function print_simple_bargraph($htmlid, $data, $ticks, $title, $options = []) {
        global $plotid, $OUTPUT;

        $htmlid = $htmlid.'_'.$plotid;
        $plotid++;

        if (empty($data)) {
            return '';
        }

        $template = new StdClass();
        $template->direction = $options['direction'] ?? 'vertical';
        $template->xmin = $options['xmin'] ?? 0;
        $template->xmax = $options['xmax'] ?? 100;
        $template->width = $options['width'] ?? 680;
        $template->height = $options['height'] ?? 680;
        $template->xunit = $options['xunit'] ?? '\\%';
        $template->xlabel = $options['xlabel'] ?? '';
        $template->seriename = $options['seriename'] ?? '';

        $template = new StdClass();
        $template->htmlid = $htmlid;
        $template->plotid = $plotid;
        $template->options = $options;

        $template->title = addslashes($title);

        $template->graphdata = self::simplebarline('graphdata', $data);

        if (empty($ticks)) {
            $ticks = [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
        }
        $template->ticksdata = self::simplebarline('ticks'.$htmlid, $ticks);

        return $OUTPUT->render_from_template('local_aplplot/jqxbargraph', $template);
    }

    /**
     * Prints a timelined curve.
     * Data is expected as an array of data series, each being an array with [date,value] pairs.
     * @param string $htmlid Unique id of the graph
     * @param array $data the data to print
     * @param string $title the graph title
     * @param array $labels an array of object of the series containing fields (color,label,lineWidth,showMarker)
     * @param string $ylabel the label of the value axis
     * @param array $options options
     * @used-by local_advancedperfs
     */
    public static function print_timecurve_bars($htmlid, $data, $title, $labels, $ylabel, $options = []) {
        global $plotid;
        global $OUTPUT;

        $htmlid = $htmlid.'_'.$plotid;

        $template = new StdClass();
        $template->htmlid = $htmlid;

        $template->width = $options['width'] ?? 800;
        $template->height = $options['height'] ?? 350;
        $template->title = addslashes($title);

        // Make curves from each x, y pair and print them to Javascript.
        $xserie = $data[0]; // Date stamps.
        $varset = [];
        $num = count($data) - 1;
        $xcount = count($xserie);
        for ($i = 1; $i < $num; $i++) {
            // Process a single serie.
            $yserie = $data[$i];
            $curvedata = [];
            for ($j = 0; $j < $xcount; $j++) {
                $curvedata[$j][0] = $xserie[$j];
                $curvedata[$j][1] = $yserie[$j];
            }
            $str .= self::timeline($curvedata, $i);
            $str .= "\n";
            $varset[] = 'line'.$i;
        }
        $template->varsetlist = implode(',', $varset);
        $template->ylabel = $ylabel;

        $labelarr = [];
        foreach ($labels as $label) {
            $labelobj = (object)$label;
            $labelstr = "{label:'{$labelobj->label}', lineWidth:{$labelobj->lineWidth}, ";
            $labelstr .= "color:'{$labelobj->color}', showMarker:{$labelobj->showMarker}}";
            $labelarr[] = $labelstr;
        }
        // A json array content.
        $template->seriesdata = implode(',', $labelarr);

        $plotid++;

        return $OUTPUT->render_from_template('local_aplplot/jqxtimecurve', $template);
    }

    /**
     * Prints a donut from a simple serie of label => value data.
     * @param $htmlid the unique graph html id
     * @param $data
     * @param string $class
     * @param array $options
     * @used-by local_my
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function simple_donut($htmlid, $data, $class, $options = null) {
        global $plotid, $OUTPUT;

        if (is_null($plotid)) {
            $plotid = 1;
        }

        $config = get_config('local_aplplot');

        $template = new StdClass();

        $template->htmlid = $htmlid;
        $template->class = $class;
        $template->plotid = $plotid;
        $template->shadowalpha = (empty($config->jqplotshadows)) ? 0 : 0.2;

        $template->plotattrs = '';
        $template->htmlstyle = '';
        if (array_key_exists('height', $options)) {
            $template->plotattrs .= 'height: '.$options['height'].',';
            $template->htmlstyle .= ' min-height:'.$options['height'].'px; ';
        }

        if (array_key_exists('width', $options)) {
            $template->plotattrs .= 'width: '.$options['width'].',';
            $template->htmlstyle .= ' min-width:'.$options['width'].'px; width:'.$options['width'].'px; ';
        }

        $template->diameter = $options['diameter'] ?? 100;
        $template->thickness = $options['thickness'] ?? 10;

        $template->location = 'w';
        if (array_key_exists('legendlocation', $options)) {
            if (!in_array($options['legendlocation'], ['w', 's', 'e', 'n'])) {
                throw new coding_exception("Bad legend location value");
            }
            $template->legendlocation = $options['legendlocation'];
        }

        $template->jsondata = self::encode_array($data);
        $colornum = count($data);

        $template->customcolors = false;

        $customcolors = [];
        if (!empty($config->donutrenderercolors)) {
            $customcolors = explode(',', $config->donutrenderercolors);
        }

        // Provide as many colors as input dimensions. Explicitely defined colors will superseede
        // randomly generated colors. Attribute colors will superseed any other.
        for ($i = 0; $i < $colornum; $i++) {
            if (array_key_exists('colors', $options) && isset($options['colors'][$i])) {
                $colors[] = $options['colors'][$i];
            } else if (isset($customcolors[$i])) {
                $colors[] = $customcolors[$i];
            } else {
                $colors[] = self::generate_color();
            }
        }
        $template->customcolors = "'".implode("','", $colors)."'";

        $plotid++;
        return $OUTPUT->render_from_template('local_aplplot/jqplotsimpledonut', $template);
    }

    /**
     * Generates a color in full, dark or light tones.
     */
    public static function generate_color($tone = 'full') {
        if ($tone == 'full') {
            $red = rand(0, 255);
            $green = rand(0, 255);
            $blue = rand(0, 255);
        } else if ($tone = 'dark') {
            $red = rand(0, 127);
            $green = rand(0, 127);
            $blue = rand(0, 127);
        } else {
            $red = rand(128, 255);
            $green = rand(128, 255);
            $blue = rand(127, 255);
        }

        return '#'.dechex($red).dechex($green).dechex($blue);
    }

    /**
     * fix many conversion issues from the standard php json_encode().
     * $data must contain a simple associative array.
     */
    public static function json_encode_array($data) {
        $str = '[';
        $statements = [];
        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $statements[] = "['".$key."', ".$value."]";
            } else {
                $statements[] = "['".$key."', '".$value."']";
            }
        }
        $str .= implode(',', $statements);
        return $str.']';
    }
}
