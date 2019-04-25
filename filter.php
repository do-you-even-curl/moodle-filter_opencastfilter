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
 *  Opencast filtering
 *
 *  This filter will replace any links to opencast videos with the selected player from opencast.
 *
 * @package    filter
 * @subpackage opencast
 * @copyright  2018 Tamara Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/filter/opencast/lib.php');

use \moodle_url;
use \tool_opencast\local\api;

/**
 * Automatic opencast videos filter class.
 *
 * @package    filter
 * @subpackage opencast
 * @copyright  2018 Tamara Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_opencast extends moodle_text_filter {

    private static $loginrendered = false;

    private function get_thumb_url($src) {

        // Get videoid. Try to get it as a GET parameter. If that doesn't work use the element
        // following /api/ in the path and.
        $thumbsrc = null;
        $videourl = new moodle_url($src);
        $videoid = $videourl->get_param('id');
        if (!$videoid) {
            $path = explode('/', $videourl->get_path(false));
            for ($i = 0; $i + 1 < count($path); $i++) {
                if ($path[$i] == 'api') {
                    $videoid = $path[$i + 1];
                }
            }
            if (!$videoid) {
                return null;
            }
        }

        $api = new api();
        $query = '/api/events/' . $videoid . '/publications/';
        $result = $api->oc_get($query);
        $publications = json_decode($result);

        if (empty($publications)) {
            return null;
        }

        foreach ($publications as $publication) {

            if ($publication->channel == 'api') {
                // Try finding the best preview image:
                // presentation/player+preview > presenter/player+preview > any other preview
                foreach ($publication->attachments as $attachment) {
                    if (!empty($attachment->url) && strpos($attachment->flavor, '+preview') > 0) {
                        if (!isset($thumbsrc) || strpos($attachment->flavor, '/player+preview') > 0) {
                            $thumbsrc = $attachment->url;
                            if ($attachment->flavor === 'presentation/player+preview') {
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $thumbsrc;
    }

    public function filter($text, array $options = array()) {
        global $COURSE, $PAGE;

        // Get baseurl either from engageurl setting or from opencast tool.
        $baseurl = get_config('filter_opencast', 'engageurl');
        if (empty($baseurl)) {
            $baseurl = get_config('tool_opencast', 'apiurl');
        }

        if (stripos($text, $baseurl) === false) {
            // Performance shortcut - if there are no </video> tags, nothing can match.
            return $text;
        }

        // Looking for tags.
        $matches = preg_split('/(<[^>]*>)/i', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        if ($matches) {
            $renderer = $PAGE->get_renderer('filter_opencast');

            $PAGE->requires->js_call_amd('filter_opencast/preview', 'init');

            $video = false;

            foreach ($matches as $match) {
                // Check if the match is a video tag.
                if (substr($match, 0, 6) === "<video") {
                    $video = true;
                } else if ($video) {
                    $video = false;
                    if (substr($match, 0, 7) === "<source") {

                        // Check if video is from opencast.
                        if (strpos($match, $baseurl) === false) {
                            continue;
                        }

                        // Extract url.
                        preg_match_all('/<source[^>]+src=([\'"])(?<src>.+?)\1[^>]*>/i', $match, $result);

                        // Change url for loading the (Paella) Player.
                        $link = $result['src'][0];

                        if (strpos($link, 'http') !== 0) {
                            $link = 'http://' . $link;
                        }

                        // Create source with autoplay enabled.
                        $playerurl = new moodle_url($link);
                        $playerurl->param('autoplay', 'true');
                        $src = $playerurl->out(false);

                        // Collect the needed data being submitted to the template.
                        $mustachedata = new stdClass();
                        $watchpage = new moodle_url('/filter/opencast/watch.php', [
                            'courseid' => $COURSE->id,
                            'videourl' => $src
                        ]);
                        $mustachedata->src = $watchpage->out(false);
                        $mustachedata->link = $link;
                        $mustachedata->thumbsrc = $this->get_thumb_url($src);

                        $newtext = $renderer->render_player_preview($mustachedata);

                        // Replace video tag.
                        $text = preg_replace('/<video(?:(?!<\/video>).)*?' . preg_quote($match, '/') . '.*?<\/video>/', $newtext, $text, 1);
                    }
                }
            }
        }

        // Return the same string except processed by the above.
        return $text;
    }
}
