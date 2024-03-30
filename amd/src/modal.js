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

define(['jquery', 'core/str'], function ($, Str) {
    async function open(config = {}) {
        let {body, title, cancel} = config;

        const settings = Object.assign({
            backdrop: true,
            keyboard: true,
            focus: true,
            show: true,
        }, config);


        const modalElement = $('#modal');

        const modalCancel = modalElement.find('.modal-cancel');
        const modalConfirm = modalElement.find('.modal-confirm');

        modalCancel.show();
        modalConfirm.removeData('dismiss');

        title = !!title ? title : await Str.get_string('modal_default_title', 'local_signature');

        modalElement.find('#modal-title').html(title);
        modalElement.find('#modal-text').html(body);

        if (cancel === false) {
            modalCancel.hide();
            modalConfirm.attr('data-dismiss', 'modal');
        }

        return new Promise(function (res) {
            modalElement.modal(settings);

            modalConfirm.off();
            modalConfirm.on('click', function() {
                close();
                res(true);
            });
        });
    }

    function close() {
        $('#modal').modal('hide');
    }

    return {
        open,
        close,
    };
});
