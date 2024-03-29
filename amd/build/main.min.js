define([
    "jquery",
    "block_course_activity_time/edit_course_time",
], function ($, EditCourseTime) {

    var initEditCourseTime = (root) => {
        root = $(root);
        EditCourseTime.init(root);
    }
    return {
        initEditCourseTime
    };
});
