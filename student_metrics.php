<?php

use block_course_activity_time\local\services\ActivityService;
use block_course_activity_time\output\student_metrics;

require_once(__DIR__ . '/../../config.php');

if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}

$id = required_param('id', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

$course = get_course($id);

if(empty($course)) {
    throw new RuntimeException(get_string('not_found_course', 'block_course_activity_time'));
}

if((int) $USER->id !== (int) $userid && !has_capability('moodle/site:config', context_system::instance(), $USER->id)) {
    throw new RuntimeException(get_string('unauthorized', 'block_course_activity_time'));
}

$context = context_course::instance($course->id);
if (!is_enrolled($context, $USER->id) && !has_capability('moodle/site:config', context_system::instance(), $USER->id)) {
    throw new RuntimeException(get_string('not_enrolled', 'block_course_activity_time'));
}

$userUrl = '';

if(!empty($userid)) {
    $userUrl = '&userid=' . $userid;
}

list($activities, $totalTime, $withinTime, $totalCourse) = ActivityService::getService()->getActivitiesForUser($userid, $course->id);

$url = new moodle_url('/blocks/course_activity_time/student_metrics.php?id=' . $id . $userUrl);
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_url($url);
$PAGE->set_title(get_string('page_title_your_metrics', 'block_course_activity_time'));
$output = $PAGE->get_renderer('block_course_activity_time');

$page = new student_metrics($activities, $totalTime, $withinTime, $totalCourse, $course);

echo $output->doctype();
echo $output->header();
echo $output->render_student_metrics($page);
echo $output->footer();
