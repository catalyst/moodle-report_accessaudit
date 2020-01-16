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
 * Access dates report page.
 *
 * @package    report_accessaudit
 * @author     John Yao <johnyao@catalyst-au.net>
 * @copyright  2019 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('report_accessaudit_accessaudit_report');

$download = optional_param('download', '', PARAM_ALPHA);
$page = optional_param('page', 0, PARAM_INT);

$indexurl = new moodle_url('/report/accessaudit/index.php');

$PAGE->set_pagelayout('report');
$PAGE->set_url($indexurl);
$PAGE->set_context(context_system::instance());
$output = $PAGE->get_renderer('report_accessaudit');

$mform = new \report_accessaudit\form\accessaudit_filter_form(null, array());
$filters = array();

if ($data = $mform->get_data()) {
    $filters = (array)$data;
} else {
    $filters = array(
        'firstname' => optional_param('firstname', '', PARAM_TEXT),
        'lastname' => optional_param('lastname', '', PARAM_TEXT),
        'username' => optional_param('username', '', PARAM_TEXT),
    );
}

$mform->set_data($filters);
$table = new \report_accessaudit\table\accessaudit_table('report_table', $indexurl, $filters, $download, $page);

if ($table->is_downloading()) {
    echo $output->render($table);
    die();
}
\core\session\manager::write_close();

$PAGE->navbar->add(get_string('auditreport', 'report_accessaudit'));

echo $output->header();
echo $output->heading(get_string('auditreport', 'report_accessaudit'));
$mform->display();
echo $output->render($table);
echo $output->footer();
