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
 * Chart bar.
 *
 * @package    core
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_vflibs;
require_once($CFG->dirroot.'/local/vflibs/chartjsplus/chart_base_trait.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Chart bar class.
 *
 * @package    core
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chart_bar extends \core\chart_bar {

    use chart_base_trait;

    /** @var bool Whether the bars should be displayed horizontally or not. */
    protected $horizontal = false;
    /** @var bool Whether the chart should be stacked or not. */
    protected $stacked = null;

    /** @var bool $extroptions. Additional options to chart config. */
    protected $options = [];

    /**
     * Add the horizontal to the parent and return the serialized data.
     *
     * @return array
     */
    public function jsonSerialize() : array { // @codingStandardsIgnoreLine (CONTRIB-6469).
        $data = parent::jsonSerialize();
        $data['horizontal'] = $this->get_horizontal();
        $data['stacked'] = $this->get_stacked();

        $data['options'] = [];
        if (!empty($this->options)) {
            foreach ($this->options as $opt => $value) {
                $data['options'][$opt] = $value;
            }
        }

        return $data;
    }

    /**
     * Set the defaults.
     */
    protected function set_defaults() {
        parent::set_defaults();
        $yaxis = $this->get_yaxis(0, true);
        $yaxis->set_min(0);
    }

    /**
     * Get whether the bars should be displayed horizontally or not.
     *
     * @return bool
     */
    public function get_horizontal() {
        return $this->horizontal;
    }

    /**
     * Get whether the bars should be stacked or not.
     *
     * @return bool
     */
    public function get_stacked() {
        return $this->stacked;
    }

    /**
     * Set whether the bars should be displayed horizontally or not.
     *
     * @param bool $horizontal True if the bars should be displayed horizontally, false otherwise.
     */
    public function set_horizontal($horizontal) {
        $this->horizontal = $horizontal;
    }

    /**
     * Set whether the bars should be stacked or not.
     *
     * @param bool $stacked True if the chart should be stacked or false otherwise.
     */
    public function set_stacked($stacked) {
        $this->stacked = $stacked;
    }

    /**
     * Get the chart type. We need override get_type to reprocess the type fixing the apparent class.
     *
     * @return string
     */
    public function get_type() {
        return 'bar';
    }
}
