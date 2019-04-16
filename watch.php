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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/filter/opencast/lib.php');

$courseid = required_param('courseid', PARAM_INT);
require_login($courseid);

$videourl = required_param('videourl', PARAM_URL);

//$PAGE->add_body_class('page-ocplayerinternal');
$OUTPUT->body_attributes(['page-ocplayerinternal']);

$OUTPUT->header();

$renderer = $PAGE->get_renderer('filter_opencast');
$mustachedata = new stdClass();
$mustachedata->src = $videourl;
echo $renderer->render_player($mustachedata);

filter_opencast_login();

echo $PAGE->requires->get_head_code($PAGE, $OUTPUT);
echo $PAGE->requires->get_top_of_body_code($OUTPUT);
echo $PAGE->requires->get_end_code();

//echo html_writer::script("document.body.className += ' page-ocplayerinternal';") . "\n";
