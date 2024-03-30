<?php

use block_course_activity_time\output\students_progress;

require_once(__DIR__ . '/../../config.php');

if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}

require_admin();

$id = required_param('id', PARAM_INT);

$course = get_course($id);

if(empty($course)) {
    throw new RuntimeException(get_string('not_found_course', 'block_course_activity_time'));
}

$url = new moodle_url('/blocks/course_activity_time/students_progress.php?id=' . $id);
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_url($url);
$PAGE->set_title(get_string('page_title_students_progress', 'block_course_activity_time'));
$output = $PAGE->get_renderer('block_course_activity_time');

$page = new students_progress($course, $activities);

echo $output->doctype();
echo $output->header();
echo $output->render_students_progress($page);
echo $output->footer();
