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
 * Plugin renderer.
 *
 * @package    report_accessaudit
 * @copyright  2020 John Yao <johnyao@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use report_accessaudit\table\accessaudit_table;

defined('MOODLE_INTERNAL') || die;

class report_accessaudit_renderer extends plugin_renderer_base {

    /**
     * Render history table.
     *
     * @param \report_accessaudit\table\accessaudit_table $table
     *
     * @return string
     */
    public function render_accessaudit_table(accessaudit_table $table) {
        ob_start();
        $table->out($table->pagesize, false);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

}
