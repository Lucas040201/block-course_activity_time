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
        $params = [
            'courseid' => $courseid,
            'courseactivityid' => $courseid,
            'coursecompletionid' => $courseid,
        ];
        $joinDate = '';
        if(!empty($from) || !empty($to)) {
            $joinDate = ' inner join {course_completions} mcc on mcc.userid = mu.id and mcc.course = :coursecompletionid ';
        }

        $sql = "
        select 
            mu.id,
            concat(mu.firstname, ' ', mu.lastname) as fullname,
            mu.email,
            (
                select sum((mcats.completedat - mcats.firstaccess)) from {{$this->getTable()}} mcats inner join {course_activity_time_course} mcatc on mcats.courseactivityid = mcatc.id where mcatc.courseid = :courseactivityid and completedat is not null and firstaccess is not null and mu.id = mcats.userid
            ) as totaltime
        from {user} mu 
            inner join {user_enrolments} mue on mu.id = mue.userid 
            inner join {enrol} me on me.id = mue.enrolid and me.courseid = :courseid";

        $sqlCount = "
        select 
            count(*)
        from mdl_user mu 
            inner join {user_enrolments} mue on mu.id = mue.userid 
            inner join {enrol} me on me.id = mue.enrolid and me.courseid = :courseid";

        if(!empty($joinDate)) {
            $sql .= $joinDate;
            $sqlCount .= $joinDate;
        }

        $sql .= " where me.enrol = 'manual'";
        $sqlCount .= " where me.enrol = 'manual'";
        
        if(!empty($search)) {
            $whereSearch = " and ". '(' . $this->db->sql_like("LOWER(concat(mu.firstname, ' ', mu.lastname))", ':user') . ' or ' . $this->db->sql_like('LOWER(mu.email)', ':email') . ') ';
            $lowerValue = strtolower('%' . $this->db->sql_like_escape($search) . '%');
            $params['user'] = $lowerValue;
            $params['email'] = $lowerValue; 
            $sql .= $whereSearch;
            $sqlCount .= $whereSearch;
        }

        if(!empty($from) && !empty($to)) {
            $sql .= " and DATE(FROM_UNIXTIME(mcc.timecompleted)) BETWEEN  '{$this->db->sql_like_escape($from)}' and '{$this->db->sql_like_escape($to)}' ";
            $sqlCount .= " and DATE(FROM_UNIXTIME(mcc.timecompleted)) BETWEEN  '{$this->db->sql_like_escape($from)}' and '{$this->db->sql_like_escape($to)}' ";
        } else if(!empty($from)) {
            $sql .= " and DATE(FROM_UNIXTIME(mcc.timecompleted)) >= '{$this->db->sql_like_escape($from)}' ";
            $sqlCount .= " and DATE(FROM_UNIXTIME(mcc.timecompleted)) >= '{$this->db->sql_like_escape($from)}' ";
        } else if(!empty($to)) {
            $sql .= " and DATE(FROM_UNIXTIME(mcc.timecompleted)) <= '{$this->db->sql_like_escape($to)}' ";
            $sqlCount .= " and DATE(FROM_UNIXTIME(mcc.timecompleted)) <= '{$this->db->sql_like_escape($to)}' ";
        }

        $sql .= "order by mu.firstname, mu.lastname asc";

        return [array_values($this->db->get_records_sql($sql, $params, $offset, $limit)), $this->db->count_records_sql($sqlCount, $params)];
    }

    public function getUserTime(int $userId, array $activitiesIds)
    {
        $activitiesIds = join(', ', $activitiesIds);

        $sql = "
            select 
                mcats.*,
                mcatc.moduleid,
                mcatc.estimatedtime
            from {user} mu
            inner join {{$this->getTable()}} mcats on mcats.userid = mu.id
            inner join {course_activity_time_course} mcatc on mcats.courseactivityid = mcatc.id
            where mcatc.moduleid in ($activitiesIds) and mu.id = :userid
        ";

        return array_values($this->db->get_records_sql($sql, [
            'userid' => $userId
        ]));
    }
}
