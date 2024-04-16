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

use block_course_activity_time\local\repositories\CourseActivityTimeCourseRepository;
use block_course_activity_time\local\repositories\CourseActivityTimeStudentRepository;
use block_course_activity_time\local\repositories\ActivityRepository;
use RuntimeException;
use stdClass;

class CourseActivityTimeService
{

    /** @var CourseActivityTimeService */
    private static $courseActivityTime;

    /** @var CourseActivityTimeCourseRepository */
    private $courseActivityTimeRepository;

    /** @var ActivityRepository */
    private $activityRepository;

    /** @var CourseActivityTimeStudentRepository */
    private $courseActivityTimeStudentRepository;

    public function __construct()
    {
        $this->courseActivityTimeRepository = new CourseActivityTimeCourseRepository();
        $this->activityRepository = new ActivityRepository();
        $this->courseActivityTimeStudentRepository = new CourseActivityTimeStudentRepository();
        
    }

    public function changeTime(int $courseId, int $activityId, int $hours)
    {
        $courseActivity = $this->courseActivityTimeRepository->findByCourseAndActivity($courseId, $activityId);

        if(empty($courseActivity)) {
            $courseActivity = new stdClass();
        }

        $courseActivity->courseid = $courseId;
        $courseActivity->moduleid = $activityId;
        $courseActivity->estimatedtime = $hours;

        $this->courseActivityTimeRepository->save($courseActivity);
    }


    public function exportData(int $courseId)
    {
        $course = get_course($courseId);
        
        if(empty($course)) {
            throw new RuntimeException('Course Not Found', 404);
        }

        list($formattedActivities, $activitiesId) = $this->getActivitiesIdAndName($course->id);

        $usersEnrolledInCourse = $this->courseActivityTimeStudentRepository->getUsersEnrolledInCourseToExport($course->id);

        $usersEnrolledInCourse = array_map(function($user) use ($activitiesId) {
            $user->activities = $this->courseActivityTimeStudentRepository->getCalculatedUserTime($user->id, $activitiesId);
            sort($user->activities);
            return $user;
        }, $usersEnrolledInCourse);

        return ['activities' => $formattedActivities, 'users' => $usersEnrolledInCourse];
    }

    private function getActivitiesIdAndName(int $courseId)
    {
        $mods = get_course_mods($courseId);
        $activitiesToFormat = [];
        $activitiesId = [];
        foreach($mods as $cm) {
            $modInfo = $this->activityRepository->getCourseActivity($cm->modname, $cm->instance);
            $item = new stdClass();
            $item->id = $cm->id;
            $item->name = $modInfo->name;
            $activitiesId[] = $cm->id;
            $activitiesToFormat[] = $item;
        }

        $activityTime = $this->courseActivityTimeRepository->getConfiguredActivities($activitiesId);

        $activitiesToFormat = array_filter($activitiesToFormat, function ($activity) use ($activityTime) {
            return !!(array_filter($activityTime, function($time) use ($activity) {
                return (int)$activity->id === (int)$time->moduleid;
            }));
        });

        $activitiesId = array_filter($activitiesId, function ($activity) use ($activitiesToFormat) {
            return !!(array_filter($activitiesToFormat, function($toFormat) use ($activity) {
                return (int)$activity === (int)$toFormat->id;
            }));
        });

        sort($activitiesToFormat);
        sort($activitiesId);
        
        return [$activitiesToFormat, $activitiesId];
    }

    public function getDeletedModules(array $modulesIds, int $courseId)
    {
        return $this->courseActivityTimeRepository->getDeletedModules($modulesIds, $courseId);
    }

    public function deleteConfig(array $activitiesId)
    {
        $this->courseActivityTimeRepository->deleteConfig($activitiesId);
    }


    public function getAllCourses()
    {
        return $this->courseActivityTimeRepository->getAllCourses();
    }

    public static function getService(): CourseActivityTimeService
    {
        if (self::$courseActivityTime === null) {
            self::$courseActivityTime = new self();
        }

        return self::$courseActivityTime;
    }
}