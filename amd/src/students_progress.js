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
        try {
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
        } catch (error) {
            const html = await Templates.render(COMPONENTS.EMPTY);
            disableSeeMoreButton(root);
            root.find('.course-activity-time-table-body').append(html);
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

    function exportCsv(root) {

        const download = function (data) {
            const blob = new Blob([data], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.setAttribute('href', url)
            const courseName = root.find('.course-name').text();
            a.setAttribute('download', `${courseName}_${Math.floor(Date.now() / 1000)}.csv`);
            a.click()
        }

        const csvmaker = function (data, header = false) {
            csvRows = [];
            const values = Object.values(data);
            csvRows.push(values.join(';'));
            return csvRows.join('\n')
        }

        const get = async function () {
            const params = getParams(root);

            const progress = await Repository.exportData({
                courseId: Number(params.courseid),
            });

            const acitivitiesNames = progress.activities.map(activity => {
                return activity.name;
            });
            const activitiesId = progress.activities.map(activity => {
                return activity.id;
            });
            const csvHeader = ['Nome', 'E-mail', ...acitivitiesNames, 'Total'];

            const formattedCsvHeader = csvHeader.join(';');
            
            const formattedUsers = progress.users.map(user => {
                user.total = user.activities.reduce((past, current) => {
                    return past + Number(current.totaltime)
                }, 0)
                return user;
            })

            const row = [];
            for(const user of formattedUsers) {
                const userInfo = {
                    name: user.fullname,
                    email: user.email,
                }
    
                const defaultLabel = 'activity';
                const userActivities = activitiesId.map(activityId => {
                    const currentUserActivityId = user.activities.find(userActivity => {
                        return Number(userActivity.moduleid) === Number(activityId);
                    });
        
                    if(currentUserActivityId) {
                        return getFormattedTime(currentUserActivityId.totaltime)
                    }

                    return '00:00:00'
                });
                
                user.activities.map(activity => {
                    return getFormattedTime(activity.totaltime)
                });

                let itemsCount = 0;
                const activitiesObject = {}
                while(itemsCount <= acitivitiesNames.length - 1) {
                    activitiesObject[`${defaultLabel}${itemsCount}`] = userActivities[itemsCount];
                    itemsCount++;
                }


                const final = {...userInfo, ...activitiesObject, total: getFormattedTime(user.total)};
                row.push(final);
            }

            let finalRow = '';

            row.forEach((item, index) => {
                finalRow += "\n" + csvmaker(item);
            })

            const finalCsv = formattedCsvHeader + finalRow;

            download(finalCsv);
        }
        root.find('.export-data').on('click', get);
    }
    function getFormattedTime(totaltime) {
        if(!totaltime) {
            return '00:00:00';
        }
        let hours = Math.floor(totaltime / 3600);
        let minutes = Math.floor((totaltime / 60) % 60);
        let seconds = totaltime % 60;

        hours = (hours < 10)? '0' + hours: hours;
        minutes = (minutes < 10)? '0' + minutes: minutes;
        seconds = (seconds < 10)? '0' + seconds: seconds;

        return `${hours}:${minutes}:${seconds}`;
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

        exportCsv(root);
    }

    return {
        init: init,
    };
});
