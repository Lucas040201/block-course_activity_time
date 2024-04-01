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

namespace block_course_activity_time\local\services;

use block_course_activity_time\local\repositories\CourseActivityTimeStudentRepository;
use block_course_activity_time\local\repositories\CourseActivityTimeCourseRepository;
use block_course_activity_time\local\enums\ActivityCompletionStatusEnum;
use stdClass;
use context_course;
use moodle_url;

class CourseActivityTimeStudentService
{

    /** @var CourseActivityTimeStudentService */
    private static $courseActivityTimeStudent;

    /** @var CourseActivityTimeStudentRepository */
    private $courseActivityTimeStudentRepository;

    /** @var CourseActivityTimeCourseRepository */
    private $courseActivityTimeCourseRepository;

    public function __construct()
    {
        $this->courseActivityTimeStudentRepository = new CourseActivityTimeStudentRepository();
        $this->courseActivityTimeCourseRepository = new CourseActivityTimeCourseRepository();
    }

    public function viewActivity(array $data)
    {
        $courseActivity = $this->courseActivityTimeCourseRepository->findByCourseAndActivity($data['courseid'], $data['contextinstanceid']);
        if(empty($courseActivity)) {
            return;
        }

        $context = context_course::instance($data['courseid']);
        if (!is_enrolled($context, $data['userid'])) {
            return;
        }

        $userActivity = $this->courseActivityTimeStudentRepository->findByUserAndActivity($data['userid'], $courseActivity->id);

        if(!empty($userActivity->completedat)) {
            return;
        }

        if(empty($userActivity)) {
            $userActivity = new stdClass();
        }

        $userActivity->userid = $data['userid'];
        $userActivity->courseactivityid = $courseActivity->id;
        $userActivity->firstaccess = time();
        $userActivity->status = ActivityCompletionStatusEnum::VIEWED;

        $this->courseActivityTimeStudentRepository->save($userActivity);
    }

    public function completeActivity(array $data)
    {
        $courseActivity = $this->courseActivityTimeCourseRepository->findByCourseAndActivity($data['courseid'], $data['contextinstanceid']);
        if(empty($courseActivity)) {
            return;
        }

        $context = context_course::instance($data['courseid']);
        if (!is_enrolled($context, $data['userid'])) {
            return;
        }

        $userActivity = $this->courseActivityTimeStudentRepository->findByUserAndActivity($data['userid'], $courseActivity->id);
        if(empty($userActivity) || !empty($userActivity->completedat)) {
            return;
        }

        $userActivity->completedat = time();
        $userActivity->status = ActivityCompletionStatusEnum::COMPLETED;

        $this->courseActivityTimeStudentRepository->save($userActivity);
    }

    public function getStudents(
        int $courseid,
        int $limit = 30,
        int $page = 1,
        string $search = null,
        string $from = null,
        string $to = null
    )
    {
        $offset = ($page - 1) * $limit;
        list($users, $total) = $this->courseActivityTimeStudentRepository->getPaginatedUsers(
            $courseid,
            $offset,
            $limit,
            $search,
            $from,
            $to
        );

        $course = get_course($courseid);


        return [
            'page' => $page,
            'total' => $total,
            'users' => array_map(function ($user) use ($course) {
                $user->totaltime = gmdate('H:i:s', ($user->totaltime));
                $user->progress = (int) \core_completion\progress::get_course_progress_percentage($course, $user->id) . '%';
                $user->userUrl = (new moodle_url("/blocks/course_activity_time/student_metrics.php?id={$course->id}&userid={$user->id}"))->out(false);
                return $user;
            }, $users),
        ];
    }

    public static function getService(): CourseActivityTimeStudentService
    {
        if (self::$courseActivityTimeStudent === null) {
            self::$courseActivityTimeStudent = new self();
        }

        return self::$courseActivityTimeStudent;
    }
}