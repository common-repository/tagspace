/*
NAME - tagcolor.js
AUTHOR - Kenneth Stein
AUTHOR URL - http://www.plexav.com
COPYRIGHT (c)2009 Kenneth L. Stein

*************************************************************************************************

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

	  jQuery(document).ready(function($) {
	    var f = $.farbtastic('#picker');
	    var p = $('#picker').css('opacity', 0.25);
	    var selected;
	    $('.colorwell')
	      .each(function () { f.linkTo(this); $(this).css('opacity', 0.75); })
	      .focus(function() {
	        if (selected) {
	          $(selected).css('opacity', 0.75).removeClass('colorwell-selected');
	        }
	        f.linkTo(this);
	        p.css('opacity', 1);
	        $(selected = this).css('opacity', 1).addClass('colorwell-selected');
	      });
	  });