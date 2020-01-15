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
 * A form for filtering users on accessaudit table.
 *
 * @package    report_accessaudit
 * @copyright  2019 John Yao <johnyao@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_accessaudit\form;


defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

class accessaudit_filter_form extends \moodleform {

    /**
     * Definition of the Mform for filters displayed in the report.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'filters', get_string('filters', 'report_accessaudit'));

        $mform->addElement('text', 'firstname', get_string('firstname'));
        $mform->setType('firstname', PARAM_TEXT);

        $mform->addElement('text', 'lastname', get_string('lastname'));
        $mform->setType('lastname', PARAM_TEXT);

        $mform->addElement('text', 'username', get_string('username'));
        $mform->setType('username', PARAM_TEXT);

        $mform->addElement('submit', 'submitbutton', get_string('filter'));
    }

}
