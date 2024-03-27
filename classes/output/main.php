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

use renderable;
use renderer_base;
use stdClass;
use templatable;

class main implements renderable, templatable
{
    /** @var bool */
    private $isStudent;

    /** @var stdClass */
    private $course;
    
    public function __construct(bool $isStudent = false)
    {
        global $COURSE;
        $this->isStudent = $isStudent;
        $this->course = $COURSE;
    }

    public function export_for_template(renderer_base $output)
    {
        
        return [
            'isStudent' => $this->isStudent,
            'course' => $this->course,
        ];
    }

}
