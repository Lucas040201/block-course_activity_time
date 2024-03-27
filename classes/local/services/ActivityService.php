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

use stdClass;

class ActivityService
{

    /** @var ActivityService */
    private static $activityService;

    /** @var ActivityRepository */
    private $activityRepository;

    /** @var CourseActivityTimeCourseRepository */
    private $courseActivityTimeRepository;

    public function __construct()
    {
        $this->activityRepository = new ActivityRepository();
        $this->courseActivityTimeRepository = new CourseActivityTimeCourseRepository();
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
                $item->id = $modInfo->id;
                $item->name = $modInfo->name;
                $activitiesId[] = $modInfo->id;
                $activitiesToFormat[] = $item;
            }
        }
        $coursesTime = $this->courseActivityTimeRepository->getCoursesTime($activitiesId);
        return array_map(function ($activity) use ($coursesTime) {
            $time = array_filter($coursesTime, function ($courseTime) use ($activity) {
                return $courseTime->moduleid === $activity->id;
            });

            $activity->time = $time[0]->estimatedtime ?? null;

            return $activity;
        }, $activitiesToFormat);
    }


    public static function getService(): ActivityService
    {
        if (self::$activityService === null) {
            self::$activityService = new self();
        }

        return self::$activityService;
    }
}