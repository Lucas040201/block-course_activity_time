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

use \mod_quiz\event\course_module_viewed;
use \mod_forum\event\discussion_viewed;
use \mod_assign\event\submission_form_viewed;
use \core\event\course_module_completion_updated;
use \mod_assign\event\assessable_submitted;
use \mod_forum\event\assessable_uploaded;

$observers = [
    [
        'eventname' => course_module_viewed::class,
        'callback' => '\block_course_activity_time\observer\student_view_monitoring::view_activity',
    ],
    [
        'eventname' => submission_form_viewed::class,
        'callback' => '\block_course_activity_time\observer\student_view_monitoring::view_activity',
    ],
    [
        'eventname' => discussion_viewed::class,
        'callback' => '\block_course_activity_time\observer\student_view_monitoring::view_activity',
    ],
    [
        'eventname' => course_module_completion_updated::class,
        'callback' => '\block_course_activity_time\observer\student_view_monitoring::complete_activity',
    ],
    [
        'eventname' => assessable_submitted::class,
        'callback' => '\block_course_activity_time\observer\student_view_monitoring::complete_activity',
    ],
    [
        'eventname' => assessable_uploaded::class,
        'callback' => '\block_course_activity_time\observer\student_view_monitoring::complete_activity',
    ],
];
