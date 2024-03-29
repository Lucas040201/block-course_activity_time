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

class CourseActivityTimeStudentRepository extends RepositoryBase
{
    public function __construct()
    {
        parent::__construct('course_activity_time_student');
    }

    public function findByUserAndActivity(int $userId, int $activityId)
    {
        return $this->db->get_record($this->getTable(), [
            'userid' => $userId,
            'courseactivityid' => $activityId
        ]);
    }

    public function getPaginatedUsers(
        int $courseid,
        int $offset,
        int $limit,
        string $search,
        string $from,
        string $to
    )
    {

    }
}
