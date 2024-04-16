<?php

namespace block_course_activity_time\tasks;

use core\task\scheduled_task;
use block_course_activity_time\local\services\CourseActivityTimeService;
use block_course_activity_time\local\services\CourseActivityTimeStudentService;

defined('MOODLE_INTERNAL') || die();

class RemoveCourseActivityConfigurationTask extends scheduled_task
{

    public function get_name(): string
    {
        return 'Delete content and adjust time for students';
    }

    public function execute(): void
    {
  
        $courseActictivityService = CourseActivityTimeService::getService();
        $courseActictivityStudentService = CourseActivityTimeStudentService::getService();
        $courses = $courseActictivityService->getAllCourses();
        foreach($courses as $course) {
            $mods = get_course_mods($course->id);

            $modulesIds = array_map(function ($module) {
                return $module->id;
            }, $mods); 

            if(empty($modulesIds)) {
                continue;
            }

            $courseActivities = $courseActictivityService->getDeletedModules($modulesIds, (int)$course->id);

            if(empty($courseActivities)) {
                continue;
            }

            $courseActivitiesToDelete = array_map(function ($activity) {
                return $activity->id;
            }, $courseActivities);

            $courseActictivityStudentService->deleteConfig($courseActivitiesToDelete);
            $courseActictivityService->deleteConfig($courseActivitiesToDelete);
        }

    }

}
