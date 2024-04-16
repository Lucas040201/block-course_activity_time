<?php

defined('MOODLE_INTERNAL') || die();

use block_course_activity_time\tasks\RemoveCourseActivityConfigurationTask;

$tasks = [
    [
        'classname' => RemoveCourseActivityConfigurationTask::class,
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];
