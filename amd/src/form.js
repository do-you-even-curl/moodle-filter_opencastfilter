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
 * Ajax functions for opencast
 *
 * @module     filter/opencast
 * @package    filter_opencast
 * @copyright  2018 Tamara Gunkel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {

    /*
     * Submits lti form
     */
    var init = function() {
        $('#ltiLaunchForm').submit(function(e) {
            e.preventDefault();
            var ocurl = decodeURIComponent($(this).attr("action"));

            $.ajax({
                url: ocurl,
                crossDomain: true,
                type: 'post',
                xhrFields: {withCredentials: true},
                data: $('#ltiLaunchForm').serialize(),
                complete: function () {
                    $(".ocplayerinternal").each(function () {
                        // Replace the src url of the iframes to load the videos.
                        $(this).attr('src', $(this).data('framesrc'));
                    });
                }
            });
        });
        $('#ltiLaunchForm').submit();
    };

    return {
        init: init
    };
});
