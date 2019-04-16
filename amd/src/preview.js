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
     * Make it so video placeholders are replaced by video iframes when clicked.
     */
    var addLoadTriggers = function() {
        $(".ocplayer").each(function() {
            $(this).one('click', function() {
                // Replace the src url of the iframe to load the video.
                var framesrc = $(this).data('framesrc');
                $(this).replaceWith($('<iframe src="' + framesrc + '" class="ocplayer" allowfullscreen="true" allow="autoplay; fullscreen"></iframe>'));
            });
        });
    };

    /*
     * Draw play buttons on video placeholders. Copied from Paella Player.
     */
    var drawPlayButtons = function() {
        $(".playButtonOnScreenIcon").each(function() {
            var width = $(this).innerWidth();
            var height = $(this).innerHeight();
            $(this).attr('width', width);
            $(this).attr('height', height);
            var iconSize = (width < height) ? width / 3 : height / 3;
            var ctx = this.getContext('2d');
            ctx.translate((width - iconSize) / 2, (height - iconSize) / 2);
            ctx.beginPath();
            ctx.arc(iconSize / 2, iconSize / 2, iconSize / 2, 0, 2 * Math.PI, true);
            ctx.closePath();
            ctx.strokeStyle = 'white';
            ctx.lineWidth = 10;
            ctx.stroke();
            ctx.fillStyle = '#8f8f8f';
            ctx.fill();
            ctx.beginPath();
            ctx.moveTo(iconSize / 3, iconSize / 4);
            ctx.lineTo(3 * iconSize / 4, iconSize / 2);
            ctx.lineTo(iconSize / 3, 3 * iconSize / 4);
            ctx.lineTo(iconSize / 3, iconSize / 4);
            ctx.closePath();
            ctx.fillStyle = 'white';
            ctx.fill();
            ctx.stroke();
        });
    };

    /*
     * Initializes the player previews
     */
    var init = function() {
        $(window).resize(drawPlayButtons);
        drawPlayButtons();
        addLoadTriggers();
    };

    return {
        init: init
    };
});
