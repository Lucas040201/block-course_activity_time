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
    "block_course_activity_time/repository"
], function ($, Templates, Str, Repository) {

    async function sendData(target, root) {
        const value = target.val();
        const container = $(target).parent();
        Repository.changeTime({
            hours: Number(value),
            activityId: Number(container.attr('data-activity')),
            courseId: Number(root.attr('data-courseid'))
        }).then(() => {
            $(target).remove();
            const span = document.createElement('span');
            span.classList.add('add-time');
    
            let hoursLabel = 'hora';
            if(value > 1) {
                hoursLabel = 'horas';
            }
    
            span.innerText = `${value} ${hoursLabel}`;
            container.append(span)
            calculateTotalTime(root);
            addDoubleClick(root);
        }).catch(async (err) => {
            console.log(err);
        });
    }

    async function initInput(target, root) {
        const currentHour = target.find('.time').text();
        const input = document.createElement('input');
        input.classList.add('insert-time');
        input.setAttribute('type', 'number');
        const container = $(target).parent();
        container.append(input);
        $(input).val(currentHour);
        target.remove();
        addInputEvents(root);
    }

    function addInputEvents(root) {
        root.find('.insert-time').off();
        root.find('.insert-time').focusout(async event => {
            await sendData($(event.target), root);
        });

        root.find('.insert-time').off();
        root.find('.insert-time').on('keypress', async event => {
            if(event.which !== 13) return;
            await sendData($(event.target), root);
        });
    }

    function addDoubleClick(root) {
        root.find('.add-time').off();
        root.find('.add-time').dblclick(async event => {
            await initInput($(event.target), root);
        });
    }

    function calculateTotalTime(root) {
        const time = Array.from(document.querySelectorAll('.time'));
        console.log(time);
        if(!time.length) {
            return;
        }

        const totalTime = time.reduce((prev, current) => {
            return prev + Number(current.innerText);
        }, 0);
        root.find('.total-time').text(totalTime);
    }

    function init(root) {
        root = $(root);
        addInputEvents(root);
        addDoubleClick(root);
        calculateTotalTime(root);
    }

    return {
        init: init,
    };
});
