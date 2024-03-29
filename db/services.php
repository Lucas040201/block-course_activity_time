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

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_course_activity_time_change_time' => [
        'classname' => \block_course_activity_time\external\CourseActivityTime::class,
        'methodname' => 'change_time',
        'description' => 'Create or update activity time',
        'type' => 'write',
        'ajax' => true
    ],
    'block_course_activity_time_get_students' => [
        'classname' => \block_course_activity_time\external\CourseActivityTime::class,
        'methodname' => 'get_students',
        'description' => 'Get Enrolled Students in course',
        'type' => 'read',
        'ajax' => true
    ],
];

$services = array(
    'course_activity_time_web_service' => array(
        'functions' => array(
            'block_course_activity_time_change_time',
            'block_course_activity_time_get_students',
        ),
        'enabled' => 1,
        'restrictedusers' => 0,
        'shortname' => 'service_course_activity_time',
        'downloadfiles'   => 0,
        'uploadfiles'     => 0,
    )
);
