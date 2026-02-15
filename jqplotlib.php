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
 * @package local_aplplot
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2015 Valery Fremaux (www.activeprolearn.com), Florence Labord (labord.florence@gmail.com)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */
namespace local_aplplot;

defined('MOODLE_INTERNAL') || die();

use moodle_exception;
use coding_exception;
use stdClass;

class jqplot {

    /**
     *
     *
     */
    public static function require_jqplot_libs() {
        global $CFG, $PAGE;

        static $jqplotloaded = false;

        if ($jqplotloaded) {
            return;
        }
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
        for ($i = 0; $i < count($data[0]); $i++) {
            $points[] = "['{$data[0][$i]}','{$data[1][$i]}', '{$data[2][$i]}']";
        }
        $str .= '['.implode(',', $points).']';
        $str .= ';';
        return $str;
    }

    /**
     * prints any JQplot graph type given a php descriptor and dataset
     *
     */
    public static function print_graph($htmlid, $graph, &$data, $width, $height, $addstyle = '', $return = false, $ticks = null) {
        global $plotid;
        static $instance = 0;

        $htmlid = $htmlid.'_'.$instance;
        $instance++;

        $str = "<center><div id=\"$htmlid\" style=\"{$addstyle} width:{$width}px; height:{$height}px;\"></div></center>";
        $str .= '<script type="text/javascript">'."\n";

        if (!is_null($ticks)) {
            $ticksvalues = implode("','", $ticks);
            $str .= "var ticks = ['$ticksvalues']; \n";
        }

        $varsetlist = json_encode($data);
        // Fixing data to arrays.
        $varsetlist = str_replace('{', '[', $varsetlist);
        $varsetlist = str_replace('}', ']', $varsetlist);

        $varsetlist = preg_replace('/"(\d+)\"/', "$1", $varsetlist);
        $jsongraph = json_encode($graph);
        $jsongraph = preg_replace('/"\$\$\.(.*?)\"/', "$1", $jsongraph);
        $jsongraph = preg_replace('/"(\$\.jqplot.*?)\"/', "$1", $jsongraph);

        $str .= "
        $.jqplot.config.enablePlugins = true;

        plot{$plotid} = $.jqplot(
            '{$htmlid}',
            $varsetlist,
            {$jsongraph}
        );
        ";
        $str .= "</script>";

        $plotid++;

        if ($return) {
            return $str;
        }
        echo $str;
    }

    /**
     * Prints a bidimensional graph (map)
     * Defaults to a percent graph 100 x 100.
     */
    public static function print_labelled_graph(&$data, $title, $htmlid, $options) {
        global $plotid;
        static $instance = 0;

        // Check option defaults.

        if (empty($options['xmin'])) {
            $options['xmin'] = 0;
        }

        if (empty($options['ymin'])) {
            $options['ymin'] = 0;
        }

        if (empty($options['xmax'])) {
            $options['xmax'] = 100;
        }

        if (empty($options['ymax'])) {
            $options['ymax'] = 100;
        }

        if (empty($options['xunit'])) {
            $options['xunit'] = '\\%';
        }

        if (empty($options['yunit'])) {
            $options['yunit'] = '\\%';
        }

        $htmlid = $htmlid.'_'.$instance;
        $instance++;

        $str = '';

        $str .= '<center>';
        $str .= '<div id="'.$htmlid.'"
                      class="vflibs-jqmap"
                      style="width:'.$options['width'].'px; height:'.$options['height'].'px;"></div>';
        $str .= '</center>';
        $str .= '<script type="text/javascript" language="javascript">';
        $str .= '
            $.jqplot.config.enablePlugins = true;
        ';

        $title = addslashes($title);

        $str .= self::labelled_rawline($data, 'data_'.$htmlid);

        $title = str_replace("'", "\\'", $title);
        $options['xlabel'] = str_replace("'", "\\'", $options['xlabel']);
        $options['ylabel'] = str_replace("'", "\\'", $options['ylabel']);

        $str .= "
            plot{$plotid} = \$.jqplot(
                '{$htmlid}',
                [data_{$htmlid}],
                {
                title:'{$title}',
                seriesDefaults:{
                    renderer:\$.jqplot.LineRenderer,
                      showLine:false,
                      showMarker:true,
                      shadowAngle:135,
                      markerOptions:{size:15, style:'circle'},
                      shadowDepth:2
                },
                axes:{ xaxis:{label:'{$options['xlabel']}', min:{$options['xmin']}, max:{$options['xmax']}, tickOptions:{formatString:'%d {$options['xunit']}'}},
                       yaxis:{label:'{$options['ylabel']}', min:{$options['ymin']}, max:{$options['ymax']}, tickOptions:{formatString:'%d {$options['yunit']}'}}
                },
                cursor:{zoom:true, showTooltip:false}
            });
        ";

        $str .= "</script>";
        $plotid++;

        return $str;
    }

    /**
     * Plots multiple date series, formatting the data array.
     *
     */
    public static function barline($name, &$data) {

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
     * Prints a single data serie for simple graphs.
     *
     */
    public static function simplebarline($name, &$data) {

        $str = "var $name = ";
        $str .= '['.implode(',', array_values($data)).']';
        $str .= ';';
        return $str;
    }

    /**
     * prints a simple bargraph.
     * @param arrayref &$data an associative array with one simple 'label' => value pairs.
     * @param arrayref &$ticks an associative array with one simple 'label' => value pairs.
     * @param string $title a text title.
     * @param string $htmlid an html identifier seed. Will be appended with an automatic instance index.
     * @param array $options some rendering options to inject in jqplot template, to override defaults.
     */
    public static function print_simple_bargraph(&$data, &$ticks, $title, $htmlid, $options = array()) {
        global $plotid;
        static $instance = 0;

        $htmlid = $htmlid.'_'.$instance;
        $instance++;

        if (empty($data)) {
            return '';
        }

        if (empty($options['direction'])) {
            $options['direction'] = 'vertical';
        }

        if (empty($options['xmin'])) {
            $options['xmin'] = 0;
        }

        if (empty($options['xmax'])) {
            $options['xmax'] = 100;
        }

        if (empty($options['width'])) {
            $options['width'] = 680;
        }

        if (empty($options['height'])) {
            $options['height'] = 680;
        }

        if (empty($options['xunit'])) {
            $options['xunit'] = '\\%';
        }

        if (empty($options['xlabel'])) {
            $options['xlabel'] = '';
        }

        if (empty($options['seriename'])) {
            $options['seriename'] = '';
        }

        $str = '';

        $str .= '<center>';
        $str .= '<div id="'.$htmlid.'" class="vflibs-jqhgraph" style="width:'.$options['width'].'px; height:'.$options['height'].'px;"></div>';
        $str .= '</center>';
        $str .= '<script type="text/javascript" language="javascript">';
        $str .= '
            $.jqplot.config.enablePlugins = true;
        ';

        $title = addslashes($title);

        $str .= self::simplebarline('graphdata', $data);
        $str .= "\n";

        if (empty($ticks)) {
            $ticks = [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
        }
        $str .= self::simplebarline('ticks'.$htmlid, $ticks);

        $str .= "
            plot{$plotid} = $.jqplot(
                '$htmlid',
                [graphdata],
                {
                    legend:{
                        show:true,
                        location:'e',
                        placement:'outsideGrid'},
                    title:'$title',
                    seriesDefaults:{ renderer:$.jqplot.BarRenderer,
                                   rendererOptions:{barDirection:'{$options['direction']}',
                                                    barPadding: 6,
                                                    barMargin:15},
                                   shadowAngle:135
                    },
                    series:[
                        {label:'{$options['seriename']}'}
                    ],
                    highlighter: {
                        show: false,
                    },
                    axesDefaults:{useSeriesColor: false, syncTicks:true},
                    axes:{ xaxis:{label:'{$options['xlabel']}', min:{$options['xmin']}, max:{$options['xmax']}, tickOptions:{formatString:'%d {$options['xunit']}'}},
                           yaxis:{renderer:$.jqplot.CategoryAxisRenderer, ticks: ticks{$htmlid}}
                }
            });
        ";

        $str .= "</script>";
        $plotid++;

        return $str;
    }

    /**
     * Prints a timelined curve.
     * Data is expected as an array of data series, each being an array with [date,value] pairs.
     * @param array $data
     * @param string $title the graph title
     * @param string $htmlid
     * @param array $labels an array of object of the series containing fields (color,label,lineWidth,showMarker)
     * @param string $ylabel the label of the value axis
     */
    public static function print_timecurve_bars($data, $title, $htmlid, $labels, $ylabel) {
        global $plotid;
        static $instance = 0;

        $htmlid = $htmlid.'_'.$instance;
        $instance++;

        $str = '<center>';
        $str .= '<div id="timeBars'.$htmlid.'"
                      class="vflibs-jqtimebars"
                      style="width:800px; height:350px;"></div>';
        $str .= '</center>';
        $str .= '<script type="text/javascript">'."\n";

        $title = addslashes($title);

        // Make curves from each x, y pair and print them to Javascript.
        $xserie = $data[0]; // date stamps.
        $varset = [];
        for ($i = 1; $i < count($data) - 1; $i++) {
            // Process a single serie.
            $yserie = $data[$i];
            $curvedata = [];
            $xcount = count($xserie);
            for ($j = 0; $j < $xcount; $j++) {
                $curvedata[$j][0] = $xserie[$j];
                $curvedata[$j][1] = $yserie[$j];
            }
            $str .= self::timeline($curvedata, $i);
            $str .= "\n";
            $varset[] = 'line'.$i;
        }
        $varsetlist = implode(',', $varset);

        $str .= "
        \$.jqplot.config.enablePlugins = true;

        plot{$plotid} = $.jqplot(
            'timeBars{$htmlid}',
            [$varsetlist],
            {
                title: '{$title}',
                seriesDefaults:{
                    renderer:$.jqplot.BarRenderer,
                      rendererOptions:{barDirection:'vertical', barWidth: 10, barPadding: 6, barMargin:15},
                      shadowAngle:135
                },
                cursor: {
                    show: false
                },
                axes: {
                    xaxis: {
                        renderer: $.jqplot.DateAxisRenderer,
                        tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                        tickOptions:{formatString:'%d/%m/%y', angle: -45, fontSize: '8pt'},
                        tickInterval:'1 week'
                    },
                yaxis: {
                    autoscale:true,
                    tickOptions:{formatString:'%d'},
                    label:'$ylabel'
                }
            },
            series:[
        ";
        $labelarr = [];
        foreach ($labels as $label) {
            $labelobj = (object)$label;
            $labelstr = "{label:'{$labelobj->label}', lineWidth:{$labelobj->lineWidth}, ";
            $labelstr .= "color:'{$labelobj->color}', showMarker:{$labelobj->showMarker}}";
            $labelarr[] = $labelstr;
        }
        $str .= implode(',', $labelarr);
        $str .= "
                ]
            }
        );
         ";
        $str .= '</script>';

        $plotid++;

        return $str;
    }

    /**
     * Prints a donut from a simple serie of label => value data.
     */
    public static function simple_donut($data, $htmlid, $class, $attributes = null) {
        global $plotid, $OUTPUT;

        if (is_null($plotid)) {
            $plotid = 1;
        }

        $config = get_config('local_vflibs');

        $template = new StdClass;

        $template->htmlid = $htmlid;
        $template->class = $class;
        $template->plotid = $plotid;
        $template->shadowalpha = (empty($config->jqplotshadows)) ? 0 : 0.2;

        $template->plotattrs = '';
        $template->htmlstyle = '';
        if (array_key_exists('height', $attributes)) {
            $template->plotattrs .= 'height: '.$attributes['height'].',';
            $template->htmlstyle .= ' min-height:'.$attributes['height'].'px; ';
        }

        if (array_key_exists('width', $attributes)) {
            $template->plotattrs .= 'width: '.$attributes['width'].',';
            $template->htmlstyle .= ' min-width:'.$attributes['width'].'px; width:'.$attributes['width'].'px; ';
        }

        if (array_key_exists('diameter', $attributes)) {
            $template->diameter = $attributes['diameter'];
        } else {
            $template->diameter = 100;
        }

        if (array_key_exists('thickness', $attributes)) {
            $template->thickness = $attributes['thickness'];
        } else {
            $template->thickness = 10;
        }

        $template->location = 'w';
        if (array_key_exists('legendlocation', $attributes)) {
            if (!in_array($attributes['legendlocation'], ['w', 's', 'e', 'n'])) {
                throw new coding_exception("Bad legend location value");
            }
            $template->legendlocation = $attributes['legendlocation'];
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
            if (array_key_exists('colors', $attributes) && isset($attributes['colors'][$i])) {
                $colors[] = $attributes['colors'][$i];
            } else if (isset($customcolors[$i])) {
                $colors[] = $customcolors[$i];
            } else {
                $colors[] = self::generate_color();
            }
        }
        $template->customcolors = "'".implode("','", $colors)."'";

        $plotid++;
        return $OUTPUT->render_from_template('local_vflibs/jqplotsimpledonut', $template);
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