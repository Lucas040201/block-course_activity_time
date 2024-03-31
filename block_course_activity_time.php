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

use block_course_activity_time\output\main;

class block_course_activity_time extends block_base
{
    /**
     * Initialises the block.
     *
     * @return void
     * @throws coding_exception
     */
    public function init(): void
    {
        $this->title = get_string('block_title', 'block_course_activity_time');
    }

    /**
     * Gets the block contents.
     *
     * @return stdClass The block HTML.
     * @throws moodle_exception
     */
    public function get_content(): stdClass
    {
        $this->buildContent();
        return $this->content;
    }

    private function buildContent(): void
    {
        global $COURSE, $USER;
        $courseContext = context_course::instance($COURSE->id);
        $isStudent = current(get_user_roles($courseContext, $USER->id))->shortname=='student'? true : false;
        $renderer = $this->page->get_renderer('block_course_activity_time');
        $metrics = (new moodle_url("/blocks/course_activity_time/student_metrics.php?id={$COURSE->id}&userid={$USER->id}"))->out(false);
        $usersUrl = (new moodle_url("/blocks/course_activity_time/students_progress.php?id={$COURSE->id}"))->out(false);
        $editCourseUrl = (new moodle_url("/blocks/course_activity_time/edit_course_activities.php?id={$COURSE->id}"))->out(false);
        $renderable = new main($metrics, $usersUrl, $editCourseUrl, $isStudent);
        $this->content->text = $renderer->render($renderable);
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats()
    {
        return [
            'all' => false,
            'course-view' => true,
        ];
    }

}
