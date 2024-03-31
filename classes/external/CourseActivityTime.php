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

namespace block_course_activity_time\external;

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use Exception;
use invalid_parameter_exception;
use RuntimeException;
use Throwable;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/externallib.php");

use block_course_activity_time\local\services\CourseActivityTimeService;
use block_course_activity_time\local\services\CourseActivityTimeStudentService;
use external;

class CourseActivityTime extends external_api
{
    /**
     * Describes the parameters for change_time.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_students_parameters(): external_function_parameters
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_REQUIRED),
                'limit' => new external_value(PARAM_INT, 'limit', VALUE_DEFAULT),
                'page' => new external_value(PARAM_INT, 'page', VALUE_DEFAULT),
                'search' => new external_value(PARAM_TEXT, 'search param', VALUE_DEFAULT),
                'from' => new external_value(PARAM_TEXT, 'from date', VALUE_DEFAULT),
                'to' => new external_value(PARAM_TEXT, 'to date', VALUE_DEFAULT),
            )
        );
    }

    /**
     * Describes the change_time return value.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_students_returns(): external_single_structure
    {
        return new external_single_structure(
            array(
                'users' => new external_multiple_structure(new external_single_structure([
                    "id" => new external_value(PARAM_INT),
                    "fullname" => new external_value(PARAM_TEXT),
                    "email" => new external_value(PARAM_TEXT),
                    "totaltime" => new external_value(PARAM_INT),
                    "progress" => new external_value(PARAM_TEXT),
                    "userUrl" => new external_value(PARAM_TEXT),
                ])),
                "total" => new external_value(PARAM_INT),
                "page" => new external_value(PARAM_INT),
            )
        );
    }

    public static function get_students(
        int $courseid,
        int $limit = 30,
        int $page = 1,
        string $search = null,
        string $from = null,
        string $to = null
        )
    {
        try {
            return CourseActivityTimeStudentService::getService()->getStudents(
                $courseid,
                $limit,
                $page,
                $search,
                $from,
                $to
             );
        } catch(Throwable $exception){
            $statusCode = 500;

            if ($exception instanceof RuntimeException || $exception instanceof invalid_parameter_exception) {
                $statusCode = 400;
            }

            http_response_code($statusCode);

            throw new Exception($exception->getMessage() . ':' . $exception->getTraceAsString());
        }
    }


        /**
     * Describes the parameters for change_time.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function change_time_parameters(): external_function_parameters
    {
        return new external_function_parameters(
            array(
                'courseId' => new external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
                'activityId' => new external_value(PARAM_INT, 'Activity id', VALUE_REQUIRED),
                'hours' => new external_value(PARAM_INT, 'Hours', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Describes the change_time return value.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function change_time_returns(): external_single_structure
    {
        return new external_single_structure(
            array(
                'message' => new external_value(PARAM_TEXT, 'Message'),
            )
        );
    }
    public static function change_time(int $courseId, int $activityId, int $hours)
    {
        try {
            CourseActivityTimeService::getService()->changeTime($courseId, $activityId, $hours);

            return [
                'message' => 'Success'
            ];
        } catch(Throwable $exception){
            $statusCode = 500;

            if ($exception instanceof RuntimeException || $exception instanceof invalid_parameter_exception) {
                $statusCode = 400;
            }

            http_response_code($statusCode);

            throw new Exception($exception->getMessage() . ':' . $exception->getTraceAsString());
        }
    }
}