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
 * Version metadata for the block_course_activity_time plugin.
 *
 * @package   block_course_activity_time
 * @copyright 2024 Lucas Mendes {@link https://www.lucasmendesdev.com.br}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_activity_time\local\repositories;

class CourseActivityTimeCourseRepository extends RepositoryBase
{
    public function __construct()
    {
        parent::__construct('course_activity_time_course');
    }

    public function getCoursesTime(array $modulesId)
    {
        $inIds = join(',', $modulesId);
        $sql = <<<SQL
        SELECT * FROM {{$this->getTable()}} WHERE moduleid in ({$inIds})
SQL;

        return $this->db->get_records_sql($sql);
    }

    public function findByCourseAndActivity(int $courseId, int $activityId)
    {
        return $this->db->get_record($this->getTable(), [
            'courseid' => $courseId,
            'moduleid' => $activityId
        ]);
    }

    public function getCourseEstimatedTime(int $courseId)
    {
        $sql = "select sum(estimatedtime) as total from {{$this->getTable()}} where courseid = :courseid";
        return $this->db->get_record_sql($sql, [
            'courseid' => $courseId
        ]);
    }
}
