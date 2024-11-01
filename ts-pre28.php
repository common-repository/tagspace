<?php


/**************************************************************************

File Name: ts-pre28.php
Component of: TagSpace Widget
Plugin URI: 		http://plexav.com/tagspace-plugin-interactive-wordpress
Authors Live URI: 	http://plexav.com/
Description: This file provides TagSpace operability on wordpress versions 2.3. to 2.7.1
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
****************************************************************************************************/





function tags2xml()   {

			global $wpdb;

				$cats = (array) $wpdb->get_results("
				SELECT	*
				FROM	$wpdb->term_taxonomy
				JOIN $wpdb->terms
				ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
				WHERE	$wpdb->term_taxonomy.taxonomy = 'category'
				");

				$presets = array('textcolor' => '222222', 'rollovercolor' => '999999', 'clickcolor' => '666666', 'height' => '250', 'width' => '185');
				$dynamictags = (array) get_option('dynamictags');


				foreach ( $presets as $key => $value ) {
					if  ((!isset($dynamictags[$key])) || ($dynamictags[$key]=="")){
						$dynamictags[$key] = $presets[$key];}
						}

			if ( $cats )
			{
				ob_start();  // Start Output Buffering
				echo '<?xml version="1.0" encoding="utf-8"?>';
				echo '<site_tags>';

				echo '<site>';

				echo '<name>'
					. '<![CDATA[';
						bloginfo('sitename');
		                echo ']]>'
					. '</name>';

				echo '<tagline>'
					. '<![CDATA[';
						bloginfo('description');
				echo ']]>'
					. '</tagline>';

				echo '<url>'
					. '<![CDATA[';
						bloginfo('url');
				echo ']]>'
					. '</url>';

				echo '</site>';


				echo '<dynamictags>';


				foreach ( $dynamictags as $key => $val )
				{
					switch ( $key )
					{
					case 'textcolor':
					case 'rollovercolor':
					case 'clickcolor':

					    $tval = str_replace( "#", "", $val);
						echo '<' . $key . '>'
							. '<![CDATA[';
						echo '0x' . $tval;
						echo ']]>'
							. '</' . $key . '>';
					}
				}

				echo '</dynamictags>';


				echo '<tags>';


				foreach ( $cats as $cat )
				{

				if ( $cat->count >0 ) {
					echo '<tag>';


					echo '<name>'
						. '<![CDATA['
						. $cat->name
						. ']]>'
						. '</name>';


					echo '<description>'
						. '<![CDATA['
						. $cat->description
						. ']]>'
						. '</description>';


					echo '<uri>'
						. '<![CDATA['
						. get_category_link($cat->term_id)
						. ']]>'
						. '</uri>';


					echo '<count>'
						. $cat->count
						. '</count>';

					echo '</tag>';

				} }

				echo '</tags>';


				echo '</site_tags>';

			}
			$updatedXml = ob_get_contents();
    			ob_end_clean();

			return $updatedXml;
	} 								// end tags2xml()


 function post_it($datastream, $url) {
	$url = preg_replace("@^http://@i", "", $url);   /* Replace 'http://' with the empty string */
	$host = substr($url, 0, strpos($url, "/"));	/* Get the Host address substring */
	$uri = strstr($url, "/");					/* Get the URI of the desired Resource */

	 $reqbody = "";
	    foreach($datastream as $key=>$val) {
	        if (!(empty($reqbody))) $reqbody.= "&";
		$reqbody.= $key."=".urlencode($val);
	}

	$contentlength = strlen($reqbody);
	$reqheader = 	"POST $uri HTTP/1.1\r\n".
				"Host: $host\n". "User-Agent: PostIt\r\n".
				"Content-Type: application/x-www-form-urlencoded\r\n".
				"Content-Length: $contentlength\r\n\r\n".
				"$reqbody\r\n";
	$socket = fsockopen($host, 80, $errno, $errstr);
		if (!$socket) {
			$result["errno"] = $errno;
			$result["errstr"] = $errstr;
			return $result;
			}
		fputs($socket, $reqheader);
		while (!feof($socket)) {
		$result[] = fgets($socket, 4096);
		}
		fclose($socket);
	return $result;
	  }

/* Update the XML when... */
  add_action ( 'publish_post', 'updateXml', 12 );
  add_action ( 'delete_post', 'updateXml', 12 );
  add_action ( 'create_$taxonomy', 'updateXml' );
  add_action ( 'delete_$taxonomy', 'updateXml' );
  add_action ( 'edit_$taxonomy', 'updateXml' );


function updateXml()	 {
		$data["xml"] = tags2xml();
		$data["url"] = get_bloginfo('url');
		$kenny = post_it($data, "http://www.plexav.com/tagspace/ts-samedomain-solution.php");
		if (isset($result["errno"])) {
			$errno = $result["errno"];
			$errstr = $result["errstr"];
			echo "<B>Error $errno</B> $errstr";
			exit;
		}
		return;
	} 						// End of updateXml



function dynamictags_init() {
	// Check to see required Widget API functions are defined...
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
				return;

function tagspace_css() {
	echo "<link rel=\"stylesheet\" href=\"";
	echo plugins_url('/tagspace/colorpick/farbtastic.css');
	echo "\" type=\"text/css\" />";
	}

add_action('admin_print_scripts', 'tagspace_css', 17);


function tagspace_scripts() {
echo "<script type=\"text/javascript\" src=\"";
	echo plugins_url('/tagspace/colorpick/farbtastic.js');
	echo "\"></script>";

	echo "<script type=\"text/javascript\" src=\"";
	echo plugins_url('/tagspace/colorpick/tagcolors.js');
	echo "\"></script>";
}
add_action('admin_footer', 'tagspace_scripts');


function dynamictags_control() {
	$options = get_option('dynamictags');
	$newoptions = get_option('dynamictags');
	if ($_POST['dynamictags-submit'] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST['dynamictags-title']));
		$newoptions['height'] = strip_tags(stripslashes($_POST['dynamictags-height']));
		$newoptions['width'] = strip_tags(stripslashes($_POST['dynamictags-width']));
		$newoptions['backcolor'] = strip_tags(stripslashes($_POST['dynamictags-backcolor']));
		$newoptions['textcolor'] = strip_tags(stripslashes($_POST['dynamictags-textcolor']));
		$newoptions['rollovercolor'] = strip_tags(stripslashes($_POST['dynamictags-rollovercolor']));
		$newoptions['clickcolor'] = strip_tags(stripslashes($_POST['dynamictags-clickcolor']));
		}

	if ( $options != $newoptions ) {
		$options = $newoptions;


		update_option('dynamictags', $options);
		updateXml();
	}
?>
			<div style="text-align:right">

			<label for="dynamictags-title" style="line-height:35px;display:block;">Widget Title: <input type="text" id="dynamictags-title" name="dynamictags-title" value="<?php echo htmlspecialchars($options['title'], true); ?>" /></label>

			<label for="dynamictags-height" style="line-height:35px;display:block;">Widget Height:<input type="text" id="dynamictags-height" name="dynamictags-height" value="<?php echo htmlspecialchars($options['height'], true); ?>" /></label>

			<label for="dynamictags-width" style="line-height:35px;display:block;">Widget Width: <input type="text" id="dynamictags-width" name="dynamictags-width" value="<?php echo htmlspecialchars($options['width'], true); ?>" /></label>

			<form action="" style="width: 500px;">
			  <div id="picker"></div>
			  <div id="tagspace-colors">

			 	<div class="form-item"><label for="dynamictags-backcolor">Background Color:</label><input type="text"   	id="dynamictags-backcolor" name="dynamictags-backcolor" class="colorwell" value="<?php echo htmlspecialchars($options['backcolor'], true); ?>" /></div>

				<div class="form-item"><label for="dynamictags-textcolor">Text Color:</label><input type="text" id="dynamictags-textcolor" name="dynamictags-textcolor" class="colorwell" value="<?php echo htmlspecialchars($options['textcolor'], true); ?>" /></div>

			 	<div class="form-item"><label for="dynamictags-rollovercolor">Rollover Color:</label><input type="text" id="dynamictags-rollovercolor" name="dynamictags-rollovercolor" class="colorwell" value="<?php echo htmlspecialchars($options['rollovercolor'], true); ?>" /></div>

			  	<div class="form-item"><label for="dynamictags-clickcolor">Click Color:</label><input type="text" id="dynamictags-clickcolor" name="dynamictags-clickcolor" class="colorwell" value="<?php echo htmlspecialchars($options['clickcolor'], true); ?>" /></div>

			  </div>
			</form>

			<input type="hidden" name="dynamictags-submit" id="dynamictags-submit" value="1" />
			</div>
		<?php }

	// This function prints the sidebar widget
	function dynamictags($args) {
			extract($args);
			$defaults = array('title' => 'Tagspace', 'height' => '250px', 'width' => '185px', 'backcolor' => 'FFFFFF', 'transparency' => 'transparent');    //changed default width and height to fit more common sidebar configurations out of the box
			$options = (array) get_option('dynamictags');
			foreach ( $defaults as $key => $value ) {

				if  ((!isset($options[$key])) || ($options[$key]=="")){
				$options[$key] = $defaults[$key]; }}


		$title = $options['title'];
        $widgetheight = $options['height'];
		$widgetwidth = $options['width'];
		$backgroundcolor = $options['backcolor'];
		if ($backgroundcolor != $defaults['backcolor']) {
		$transparentopaque = 'opaque';
		} else { $transparentopaque = 'transparent'; }
		$myUrl = get_bloginfo('url');
		$cleanUrl = preg_replace("@^http://@i", "", $myUrl);
		$myDirectory .= str_replace(".", "-", $cleanUrl);

		echo $before_widget;
        	echo $before_title . $options['title'] . $after_title;
?>

      	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,45,0" width="<?php echo $widgetwidth; ?>" height="<?php echo $widgetheight; ?>" id="tagspace-interface-new.swf">
		  <param name="movie" value="http://www.plexav.com/tagspace/tagspace-interface-new.swf" />
		  <param name="menu" value="false" />
		  <param name="wmode" value="<?php echo $transparentopaque; ?>" />
		  <param name="quality" value="best" />
		  <param name="allowScriptAccess" value="always" />
		  <param name="bgcolor" value="<?php echo $backgroundcolor; ?>" />
		  <param name="FlashVars" value="localURL=<?php echo $myDirectory; ?>">
		  <param name="scale" value="exactfit" />

		 <embed src="http://www.plexav.com/tagspace/tagspace-interface-new.swf" FlashVars="localURL=<?php echo $myDirectory; ?>" menu="false" quality="best" scale="exactfit" wmode="<?php echo $transparentopaque; ?>" bgcolor="<?php echo $backgroundcolor; ?>" width="<?php echo $widgetwidth; ?>" height="<?php echo $widgetheight; ?>" name="tagspace-interface-new.swf" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
	</object>

<?php echo $after_widget; ?>
<?php /* Before you remove this link, message me and (subject to certain restrictions - I won't link to certain types of sites) I'll provide a link to your site for as long as it includes the Tagspace widget and this link! */?>.
		<div style="visibility:hidden;"><a href="http://postjockey.com" rel="index,follow">Live Stream - Audio - Images - Video Streaming - Chat</a></div>
		<?php }

	// Register The Widget Already
	register_sidebar_widget('Tagspace', 'dynamictags');
	register_widget_control('Tagspace', 'dynamictags_control' , 450, 450);
}

// Delays plugin execution until Dynamic Sidebar has loaded first.
add_action('plugins_loaded', 'dynamictags_init');
?>