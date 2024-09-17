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
 * My Certificates block renderer.
 *
 * @package    block_mycertificates
 * @copyright  2020 Willian Mano - http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mycertificates\output;

use plugin_renderer_base;
use renderable;

/**
 * My Certificates block renderer.
 *
 * @copyright  2020 Willian Mano - http://conecti.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Defer the block instance in course to template.
     *
     * @param renderable $page
     *
     * @return bool|string
     *
     * @throws \moodle_exception
     */
    public function render_block(renderable $page) {
        $data = $page->export_for_template($this);

        if ($this->page->pagelayout == 'course') {
            return parent::render_from_template('block_mycertificates/course', $data);
        }

        return parent::render_from_template('block_mycertificates/mydashboard', $data);
    }
}
