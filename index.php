<?php

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
 * List of all palestras in course
 *
 * @package mod_palestra
 * @copyright  2021 Interlegis (https://www.interlegis.leg.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

$strpalestra         = get_string('modulename', 'palestra');
$strpalestras        = get_string('modulenameplural', 'palestra');
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/palestra/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strpalestras);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strpalestras);
echo $OUTPUT->header();
echo $OUTPUT->heading($strpalestras);
if (!$palestras = get_all_instances_in_course('palestra', $course)) {
    notice(get_string('thereareno', 'moodle', $strpalestras), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($palestras as $palestra) {
    $cm = $modinfo->cms[$palestra->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($palestra->section !== $currentsection) {
            if ($palestra->section) {
                $printsection = get_section_name($course, $palestra->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $palestra->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($palestra->timemodified)."</span>";
    }

    $class = $palestra->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed

    $table->data[] = array (
        $printsection,
        "<a $class href=\"view.php?id=$cm->id\">".format_string($palestra->name)."</a>",
        format_module_intro('palestra', $palestra, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
