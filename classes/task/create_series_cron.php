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
 * @package   block_opencast
 * @copyright 2020 Tim Schroeder, RWTH Aachen University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_opencast\task;
use block_opencast\local\apibridge;

defined('MOODLE_INTERNAL') || die();

class create_series_cron extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('createseries', 'block_opencast');
    }

    public function execute() {
        global $DB;
        \core_php_time_limit::raise(HOURSECS);
        raise_memory_limit(MEMORY_EXTRA);

        $sql = 'SELECT c.id
            FROM {course} c
            LEFT JOIN {tool_opencast_series} os
                ON c.id = os.courseid
            WHERE os.courseid IS NULL
                AND c.id <> :siteid';
        $params = ['siteid' => SITEID];
        $courses = $DB->get_records_sql($sql, $params);

        $i = 0;
        $count = count($courses);
        mtrace("Creating $count missing series ...");

        $apibridge = apibridge::get_instance();
        foreach ($courses as $course) {
            $i++;
            mtrace("Creating series for course with ID $course->id ($i/$count)");
            $apibridge->ensure_course_series_exists($course->id);
        }
        mtrace("... done.");
    }
}