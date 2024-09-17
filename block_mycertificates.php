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
 * My Certificates block definition
 *
 * @package    block_mycertificates
 * @copyright  2020 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * My Certificates block definition class
 *
 * @copyright  2020 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_mycertificates extends block_base {

    /**
     * Sets the block title
     *
     * @return void
     *
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_mycertificates');
    }

    /**
     * Defines where the block can be added
     *
     * @return array
     */
    public function applicable_formats() {
        return [
            'course-view'    => true,
            'site'           => false,
            'mod'            => false,
            'my'             => true,
        ];
    }

    /**
     * Creates the blocks main content
     *
     * @return stdClass
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new \stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $renderer = $this->page->get_renderer('block_mycertificates');

        $courseid = null;
        if ($this->page->pagelayout == 'course') {
            $courseid = $this->page->course->id;
        }

        $contentrenderable = new \block_mycertificates\output\block($courseid);
        $this->content->text = $renderer->render($contentrenderable);

        return $this->content;
    }
}
