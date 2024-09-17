<?php
// This file is part of My Certificates block for Moodle - http://moodle.org/
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
 * My Certificates block
 *
 * @package    block_mycertificates
 * @copyright  2020 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_mycertificates\output;

use renderable;
use templatable;
use renderer_base;

/**
 * My Certificates block renderable class.
 *
 * @copyright  2020 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block implements renderable, templatable {

    /**
     * @var int $courseid
     */
    protected $courseid;

    /**
     * Block constructor.
     *
     * @param int $courseid
     */
    public function __construct($courseid = null) {
        $this->courseid = $courseid;
    }

    /**
     * Export the data
     *
     * @param renderer_base $output
     *
     * @return array|\stdClass
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        global $USER;

        $certificates = new \block_mycertificates\util\certificates($USER, $this->courseid);

        $issuedcertificates = $certificates->get_all_certificates();

        return [
            'hascertificates' => (count($issuedcertificates)) ? true : false,
            'coursescertificates' => $issuedcertificates,
        ];
    }
}
