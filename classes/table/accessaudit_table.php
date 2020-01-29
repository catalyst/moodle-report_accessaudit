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
 * Table to display Access Audit report.
 *
 * @package    report_accessaudit
 * @copyright  2020 John Yao <johnyao@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_accessaudit\table;


defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/tablelib.php');

class accessaudit_table extends \table_sql implements \renderable {

    /**
     * A list of filters to be applied to the sql query.
     * @var \stdClass
     */
    protected $filters;

    /**
     * A current page number.
     * @var int
     */
    protected $page;

    public function __construct($uniqueid, \moodle_url $url, $filters = array(), $download = '', $page = 0, $perpage = 100) {

        parent::__construct($uniqueid);

        $this->pagesize = $perpage;
        $this->page = $page;
        $this->filters = (object)$filters;

        // Define columns in the table.
        $this->define_table_columns();

        // Define configs.
        $this->define_table_configs($url);

        // Set download status.
        $this->is_downloading($download, 'accessaudit_accessaudit_report');

    }

    /**
     * Setup the headers for the html table.
     */
    protected function define_table_columns() {
        $cols = array(
            'idnumber' => get_string('idnumber'),
            'username' => get_string('username'),
            'email' => get_string('email'),
            'fullname' => get_string('fullname'),
            'role' => get_string('role'),
            'context' => get_string('report_context', 'report_accessaudit'),
            'contextpathraw' => get_string('report_contextpathraw', 'report_accessaudit'),
            'contextpathhuman' => get_string('report_contextpathhuman', 'report_accessaudit')
        );

        $this->define_columns(array_keys($cols));
        $this->define_headers(array_values($cols));
    }

    /**
     * Define table configs.
     *
     * @param \moodle_url $url url of the page where this table would be displayed.
     */
    protected function define_table_configs(\moodle_url $url) {
        $urlparams = (array)$this->filters;

        unset($urlparams['submitbutton']);

        $url->params($urlparams);
        $this->define_baseurl($url);

        // Set table configs.
        $this->collapsible(false);
        $this->sortable(false);
        $this->pageable(false);

        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);
    }

    /**
     * Query the reader. Store results in the object for use by build_table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        $offset = $pagesize * $this->page;
        $limit = $pagesize;

        list($countsql, $countparams) = $this->get_sql_and_params(true);
        list($sql, $params) = $this->get_sql_and_params();

        $total = $DB->count_records_sql($countsql, $countparams);

        if ($this->is_downloading()) {
            $this->rawdata = $DB->get_recordset_sql($sql, $params);
        } else {
            $this->rawdata = $DB->get_recordset_sql($sql, $params, $offset, $limit);
        }

        $this->pagesize($pagesize, $total);

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    /**
     * Builds the complete sql .
     *
     * @param bool $count setting this to true, returns an sql to get count only instead of the complete data records.
     *
     * @return array containing sql to use and an array of params.
     */
    protected function get_sql_and_params($count = false) {
        global $CFG;

        $admin = get_string('administrator', 'core');

        if ($count) {
            $select = "COUNT(*)";
        } else {
            $select = '*';
        }

        $selectuser = ", u.id AS userid, u.idnumber, u.firstname, u.lastname, u.username, u.email";
        $selectrole = "CONCAT(u.id, ra.id) AS id,      ra.contextid,  r.shortname AS role,       ct.contextlevel,   ct.path AS contextpathraw";
        $selectadmin = "      CONCAT(u.id) AS id, null AS contextid,     '$admin' AS role,  null AS contextlevel,      null AS contextpathraw";

        list($where, $params) = $this->get_filters_sql_and_params();
        $sql = "SELECT $select FROM (";
        $sql .= "SELECT $selectrole $selectuser
                   FROM {user} u
              LEFT JOIN {role_assignments} ra ON u.id = ra.userid
              LEFT JOIN {context} ct ON ra.contextid = ct.id
              LEFT JOIN {role} r ON ra.roleid = r.id";

        $sql .= " UNION

                SELECT $selectadmin $selectuser FROM {user} u
                WHERE u.id IN ($CFG->siteadmins)";

        $sql .= ") AS temp";

        $sql .= " WHERE $where";

        // Add order by if needed.
        if (!$count && $sqlsort = $this->get_sql_sort()) {
            $sql .= " ORDER BY " . $sqlsort;
        }

        return array($sql, $params);
    }

    /**
     * Builds the sql and param list needed, based on the user selected filters.
     *
     * @return array containing sql to use and an array of params.
     */
    protected function get_filters_sql_and_params() {
        global $DB;

        $filter = '1 = 1';
        $params = array();

        if (!empty($this->filters->firstname)) {
            $filter .= ' AND (' . $DB->sql_like('firstname', ':firstname', false) . ') ';
            $params['firstname'] = '%' . $DB->sql_like_escape($this->filters->firstname) . '%';
        }

        if (!empty($this->filters->lastname)) {
            $filter .= ' AND (' . $DB->sql_like('lastname', ':lastname', false) . ') ';
            $params['lastname'] = '%' . $DB->sql_like_escape($this->filters->lastname) . '%';
        }

        if (!empty($this->filters->username)) {
            $filter .= ' AND (username = :username) ';
            $params['username'] = $this->filters->username;
        }

        if (!empty($this->filters->role)) {
            $filter .= ' AND (role = :role) ';
            $params['role'] = $this->filters->role;
        }

        return array($filter, $params);
    }

    protected function col_context($data) {
        if ($data->contextid > 0) {
            $context = \context_helper::instance_by_id($data->contextid);
            return $context->get_context_name();
        } else {
            return '-';
        }
    }

    protected function col_contextpathhuman($data) {
        $contextids = explode('/', trim($data->contextpathraw, '/'));

        if ($contextids[0] != null) {
            $contextpath = array();
            foreach ($contextids as $contextid) {
                list($context, $course, $cm) = get_context_info_array($contextid);
                $contextpath[] = $context->get_context_name();
            }
            $contextpathhuman = implode('/', $contextpath);
        }
        return $contextpathhuman;
    }
}
