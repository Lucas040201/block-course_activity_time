define(["jquery", "core/ajax"], function ($, Ajax) {
    function changeTime(data) {
        return Ajax.call([
            {
                methodname: "block_course_activity_time_change_time",
                args: {
                    ...data
                },
            },
        ])[0];
    }

    return {
        changeTime,
    };
});
