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

use block_course_activity_time\local\repositories\ActivityRepository;
use block_course_activity_time\local\repositories\CourseActivityTimeCourseRepository;
use block_course_activity_time\local\repositories\CourseActivityTimeStudentRepository;

use stdClass;

class ActivityService
{

    /** @var ActivityService */
    private static $activityService;

    /** @var ActivityRepository */
    private $activityRepository;

    /** @var CourseActivityTimeCourseRepository */
    private $courseActivityTimeRepository;

    /** @var CourseActivityTimeStudentRepository */
    private $courseActivityTimeStudentsRepository;

    public function __construct()
    {
        $this->activityRepository = new ActivityRepository();
        $this->courseActivityTimeRepository = new CourseActivityTimeCourseRepository();
        $this->courseActivityTimeStudentsRepository = new CourseActivityTimeStudentRepository();
    }

    public function getActivities(int $courseId, int $userId)
    {
        $mods = get_course_mods($courseId);
        $activitiesToFormat = [];
        $activitiesId = [];
        foreach($mods as $cm) {
            if(\core_availability\info_module::is_user_visible($cm, $userId)) {
                $modInfo = $this->activityRepository->getCourseActivity($cm->modname, $cm->instance);
                $item = new stdClass();
                $item->id = $cm->id;
                $item->name = $modInfo->name;
                $activitiesId[] = $cm->id;
                $activitiesToFormat[] = $item;
            }
        }
        $coursesTime = $this->courseActivityTimeRepository->getCoursesTime($activitiesId);
        
        $items = array_map(function ($activity) use ($coursesTime) {
            $time = array_values(array_filter($coursesTime, function ($courseTime) use ($activity) {
                return $courseTime->moduleid === $activity->id;
            }));
        
            $finalLabel = '';

            if (!empty($time[0])) {
                $time = $time[0];
                $finalLabel = gmdate('H:i:s', ($time->estimatedtime));
            }

            $activity->time = $finalLabel;

            return $activity;
        }, $activitiesToFormat);

        $total = array_reduce($coursesTime, function($past, $current) {
            return $past + $current->estimatedtime;
        }, 0);

        return [$items, self::getCalculatedTime($total)];
    }

    public static function getCalculatedTime($seconds)
    {
        if(empty($seconds)) {
            return '00:00:00';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        return "$hours:$minutes:$seconds";
    }

    public function getActivitiesForUser(int $userId, int $courseId)
    {
        $mods = get_course_mods($courseId);
        $activitiesToFormat = [];
        $activitiesId = [];
        foreach($mods as $cm) {
            if(\core_availability\info_module::is_user_visible($cm, $userId)) {
                $modInfo = $this->activityRepository->getCourseActivity($cm->modname, $cm->instance);
                $item = new stdClass();
                $item->id = $cm->id;
                $item->name = $modInfo->name;
                $activitiesId[] = $cm->id;
                $activitiesToFormat[] = $item;
            }
        }

        $courseConfig = $this->courseActivityTimeRepository->getConfiguredActivities($activitiesId);

        $userTime = $this->courseActivityTimeStudentsRepository->getUserTime($userId, $activitiesId);

        $formattedUserActivityCompletion = array_map(function ($activity) use ($userTime, $activitiesToFormat) {
            $activityInfo = array_values(array_filter($activitiesToFormat, function ($item) use ($activity) {
                return (int)$item->id === (int)$activity->moduleid;
            }))[0];

            $userActivityInfo = array_values(array_filter($userTime, function ($user) use($activity) {
                return (int)$activity->id === (int)$user->courseactivityid;
            }));

            $data = new stdClass();
            $data->id = $activityInfo->id;
            $data->name = $activityInfo->name;
            $time = 0;
            $formatedTime = '-';
            if(!empty($userActivityInfo) && $userActivityInfo = $userActivityInfo[0]) {
                if(!empty($userActivityInfo->completedat)) {
                    $time = ($userActivityInfo->completedat - $userActivityInfo->firstaccess);
                    $formatedTime = self::getCalculatedTime($time);
                }
            }
            $data->time = $time;
            $data->formatedTime = $formatedTime;
            return $data;
        }, $courseConfig);

        $totalCourse = array_reduce($courseConfig, function($past, $current) {
            return $past + $current->estimatedtime;
        }, 0);

        $totalCourseFormatted = self::getCalculatedTime($totalCourse);

        $totalUser = array_reduce($formattedUserActivityCompletion, function($past, $current) {
            return $past + $current->time;
        }, 0);

        $totalUserFormatted = self::getCalculatedTime($totalUser);
        $bigger = $totalCourse < $totalUser;
        $marginTime = $totalCourse - ($totalCourse * 0.1);
        $smaller = $marginTime < $totalUser && !$bigger;
        $needHelp = $totalUser < $marginTime && !$smaller;

        return [array_values($formattedUserActivityCompletion), $totalUserFormatted, $bigger, $smaller, $needHelp, $totalCourseFormatted];
    }


    public static function getService(): ActivityService
    {
        if (self::$activityService === null) {
            self::$activityService = new self();
        }

        return self::$activityService;
    }
}