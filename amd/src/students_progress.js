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
    "core/templates",
    "core/str",
    "block_course_activity_time/repository",
    "block_course_activity_time/modal",
], function ($, Templates, Str, Repository) {

    const COMPONENTS = {
        NOT_FOUND: 'block_course_activity_time/components/students_progress/row_not_found_students',
        EMPTY: 'block_course_activity_time/components/students_progress/row_empty_students',
        STUDENTS: 'block_course_activity_time/components/students_progress/row_students',
    };

    async function loadStudents(root, seeMore = false) {
        const params = getParams(root);
        const students = await Repository.getStudents(params);

        let template = COMPONENTS.STUDENTS;

        if (!seeMore && !students.users.length && !params.search) {
            template = COMPONENTS.EMPTY;
        }

        if (!seeMore && !students.users.length && params.search) {
            disableSeeMoreButton(root, 'see_more');
            template = COMPONENTS.NOT_FOUND;
        }

        if (!seeMore) {
            root.find('.course-activity-time-table-body').empty();
        }

        const labelHour = await Str.get_string('hour_label', 'block_course_activity_time');
        const labelHours = await Str.get_string('hours_label', 'block_course_activity_time')

        students.users = students.users.map(user => {
            if(Number(user.totaltime) === 0) {
                user.totaltime = '-';
            } else {
                user.totaltime = (user.totaltime > 1)? `${user.totaltime} ${labelHours}`: `${user.totaltime} ${labelHour}`;
            }

            return user;
        })

        const html = await Templates.render(template, {
            users: students.users,
            courseid: params.courseid
        });

        root.find('.total_students').text(students.total)

        root.find('.course-activity-time-table-body').append(html);

        const studentsLength = root.find('.students-item').length;

        if (students.total === studentsLength) {
            disableSeeMoreButton(root);
        } else if (root.find('.see_more').hasClass('hidden') && students.total > studentsLength) {
            activeSeeMoreButton(root);
        }
    }

    function disableSeeMoreButton(root, findClass = 'see_more') {
        root.find(`.${findClass}`).addClass('hidden');
        root.find(`.${findClass}`).attr('disabled', true);
    }

    function activeSeeMoreButton(root, findClass = 'see_more') {
        root.find(`.${findClass}`).removeClass('hidden');
        root.find(`.${findClass}`).attr('disabled', false);
    }

    function seeMoreStudents(root) {
        root.find('.see_more').on('click', async function () {
            const newPage = Number(root.attr('data-page')) + 1;
            root.attr('data-page', newPage);

            await loadStudents(root, getParams(root), true);
        });
    }

    function getParams(root) {
        return {
            courseid: Number(root.attr('data-courseid')),
            limit: Number(root.attr('data-limit')),
            page: Number(root.attr('data-page')),
            search: root.find('#search').val() || '',
            from: root.find('#from').val() || '',
            to: root.find('#to').val() || ''
        }
    }

    async function init(root) {
        root = $(root);
        await loadStudents(root);
        seeMoreStudents(root);

        let timeout;
        root.find('#search').on('keyup', e => {

            clearTimeout(timeout);

            timeout = setTimeout(() => {
                loadStudents(root);
            }, 2000);
        });

        root.find('[name="from"]').on('change', event => {
            loadStudents(root);
        });

        root.find('[name="to"]').on('change', event => {
            loadStudents(root);
        });
    }

    return {
        init: init,
    };
});
