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

namespace block_course_activity_time\output;

defined('MOODLE_INTERNAL') || die();

use block_course_activity_time\local\utils\MoodleUrl;
use plugin_renderer_base;

class renderer extends plugin_renderer_base
{

    public function render_main(main $main)
    {
        return $this->render_from_template('block_course_activity_time/pages/main', $this->getContext($main));
    }

    public function render_edit_course_activities(edit_course_activities $edit)
    {
        return $this->render_from_template('block_course_activity_time/pages/edit_course_activities', $this->getContext($edit));
    }

    public function render_students_progress(students_progress $progress)
    {
        return $this->render_from_template('block_course_activity_time/pages/students_progress',$this->getContext($progress));
    }

    public function render_student_metrics(student_metrics $metrics)
    {
        return $this->render_from_template('block_course_activity_time/pages/student_metrics', $this->getContext($metrics));
    }

    private function getContext(\templatable $templatable)
    {
        $context = $templatable->export_for_template($this);
        $param = [
            'id' => $context['course']->id
        ];
        $siteAdmin = is_siteadmin();
        $isInProgressPage = false;
        $isInEditPage = false;
        $editUrl = MoodleUrl::getMoodleUrl('/blocks/course_activity_time/edit_course_activities.php', $param);
        $progressUrl = MoodleUrl::getMoodleUrl('/blocks/course_activity_time/students_progress.php', $param);
        $courseUrl = MoodleUrl::getMoodleUrl('/course/view.php', $param);

        if($templatable instanceof edit_course_activities) {
            $isInEditPage = true;
        }

        if($templatable instanceof students_progress) {
            $isInProgressPage = true;
        }

        $context['isInEditPage'] = $isInEditPage;
        $context['editUrl'] = $editUrl;
        $context['isInProgressPage'] = $isInProgressPage;
        $context['progressUrl'] = $progressUrl;
        $context['siteAdmin'] = $siteAdmin;
        $context['courseUrl'] = $courseUrl;
        return $context;

    }
}
