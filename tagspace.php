<?php

/**************************************************************************

Plugin Name: Tagspace
Plugin URI: http://plexav.com/tagspace-plugin-interactive-wordpress
Description: Tagspace is a plugin and widget that lets visitors browse your blog's categories in a dynamic & engaging 3D environment. A single click on any category or tag changes the color of the category or tag and propels the visitor further into Tagspace, revealing additional categories and tags.  Clicking a second time transports the visitor to a view of the selected category or tag's posts. The Tagspace widget automatically links your categories and tags to your associated posts, and any changes you make (deleting or adding a new category or tag, creating or deleting a post, etc.) are automatically and immediately reflected in Tagspace. Tagspace can be configured to match your theme. The widget name, height, width, background color, text color, rollover color, and click color are each configurable via the intuitive Tagspace widget admin panel.
Version: 1.5
Requires at least: 2.3
Tested up to: 3.2.1
Stable tag: 1.5
License: GPLv2
Author: Kenneth Stein
Author URI: http://www.plexav.com

****************************************************************************************************
Copyright (C) 2006-2011 Kenneth L. Stein

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, version 2 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>, or
write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330,
Boston, MA  02111-1307  USA


*****************************************************************************************************

*****INSTRUCTIONS*****

Installation
============
Upload the folder "tagspace" into your "wp-content/plugins" directory.
Log in to Wordpress Administration area, choose "Plugins" from the main menu, find "Tagspace"
and click the "Activate" button.

Configuration
=============
From the main menu choose "Design->Widgets."
Drag the "Tagspace" widget to the preferred position on your sidebar.
Click the "Change" button below the widget to display the configuration menu.
Complete the configuration menu:

	1. Widget Title  	- You can substitute a different title for Tagspace
	2. Widget Height 	- Specify the height in pixels (ex. 250px).
	3. Widget Width  	- Specify the width in pixels (ex. 250px).

You can specify the background color, category color, tag color, and the rollover color using the intuitive color pickers located below eachof the four color option boxes.  [For those of you with versions of Wordpress earlier than 2.8, you'll find a single color picker positioned to the left of the four color option boxes.] Click inside of one of the four option boxes (it will become brighter as a result) rotate the marker on the circle to select a hue, and position the marker in the square to select a brightness/saturation combination.

Alternatively, you can directly type in the hexadecimal codes for your selected colors into each of the color selection boxes.

Click 'Save Changes' located below the configuration menu to save your changes.


Uninstallation
==============
Log in to Wordpress Administration area.
Choose "Plugins" from the main menu
Find the name of the plugin "Tagspace", and click the "Deactivate" button.
If you were using the widget it will no longer appear, so be certain to review your site.

******************************************************************************************************************/

// check WP version to see which version of the plugin to use


global $wp_version;

$legacy = (version_compare($wp_version, "2.8", ">=")) ? false : true;
	if ( $legacy ) {
		include ('ts-pre28.php');
	} else {
		include ('ts-current.php');
	}

	add_action('wp_head', 'wp_head_intercept');
	function wp_head_intercept() {
		echo '<meta name="generator" content="Tagspace Menu 1.5" />';
	}
?>