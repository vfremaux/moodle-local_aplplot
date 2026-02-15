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
 * Chart line.
 *
 * @package    core
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @copyright  2022 Valery Fremaux - www.activeprolearn.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_vflibs;
require_once($CFG->dirroot.'/local/vflibs/chartjsplus/chart_base_trait.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Chart line class.
 *
 * @package    core
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @copyright  2022 Valery Fremaux - www.activeprolearn.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chart_line extends \core\chart_line {

    use chart_base_trait;

    /** @var bool Whether the line should be smooth or not. */
    protected $smooth = false;

    /** @var bool $extroptions. Additional options to chart config. */
    protected $options = [];

    /**
     * Add the smooth to the parent and return the serialized data.
     *
     * @return array
     */
    public function jsonSerialize() : array { // @codingStandardsIgnoreLine (CONTRIB-6469).
        $data = parent::jsonSerialize();
        $data['smooth'] = $this->get_smooth();

        $data['options'] = [];
        if (!empty($this->options)) {
            foreach ($this->options as $opt => $value) {
                $data['options'][$opt] = $value;
            }
        }

        return $data;
    }

    /**
     * Get whether a lines should be smooth or not.
     *
     * @return bool
     */
    public function get_smooth() {
        return $this->smooth;
    }

    /**
     * Set Whether the line should be smooth or not.
     *
     * @param bool $smooth True if the line chart should be smooth, false otherwise.
     */
    public function set_smooth($smooth) {
        $this->smooth = $smooth;
    }

    /**
     * Get the chart type. We need override get_type to reprocess the type fixing the apparent class.
     *
     * @return string
     */
    public function get_type() {
        return 'line';
    }
}
