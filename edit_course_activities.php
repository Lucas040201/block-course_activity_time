<?php

use block_course_activity_time\output\edit_course_activities;
use block_course_activity_time\local\services\ActivityService;

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

$url = new moodle_url('/blocks/course_activity_time/edit_course_activities.php?id=' . $id);
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_url($url);
$PAGE->set_title(get_string('page_title_edit', 'block_course_activity_time'));
$PAGE->requires->js(new moodle_url('https://unpkg.com/vanilla-masker@1.1.1/build/vanilla-masker.min.js'), true);
$output = $PAGE->get_renderer('block_course_activity_time');
$service = ActivityService::getService();
list($activities, $totalTime) = $service->getActivities($course->id, $USER->id);

$page = new edit_course_activities($course, $totalTime, $activities);

echo $output->doctype();
echo $output->header();
echo $output->render_edit_course_activities($page);
echo $output->footer();
