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
 * Languages configuration for the block_course_activity_time plugin.
 *
 * @package   block_course_activity_time
 * @copyright 2024, Lucas Mendes {@link https://www.lucasmendesdev.com.br}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    "jquery",
    "block_course_activity_time/edit_course_time",
    "block_course_activity_time/students_progress",
    "block_course_activity_time/your_metrics",
], function ($, EditCourseTime, StudentsProgress, YoutMetrics) {

    var initEditCourseTime = (root) => {
        root = $(root);
        EditCourseTime.init(root);
    }

    var initStudentsProgress = (root) => {
        root = $(root);
        StudentsProgress.init(root);
    }

    var initYourMetrics = (root) =>{
        root = $(root);
        YoutMetrics.init(root);
    }

    return {
        initEditCourseTime,
        initStudentsProgress,
        initYourMetrics,
    };
});
