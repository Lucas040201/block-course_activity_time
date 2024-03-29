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
