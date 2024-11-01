<?php

/**************************************************************************

File Name: ts-current.php
Component of: TagSpace Widget
Plugin URI: 		http://plexav.com/tagspace-plugin-interactive-wordpress
Authors Live URI: 	http://plexav.com/
Description: This file provides TagSpace operability on wordpress versions 2.8. onwards
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
function get_category_tags($args) {
	global $wpdb;
	$tags = $wpdb->get_results
	("
		SELECT DISTINCT terms2.term_id as tag_id, terms2.name as tag_name, null as tag_link
		FROM
			$wpdb->posts as p1
			LEFT JOIN $wpdb->term_relationships as r1 ON p1.ID = r1.object_ID
			LEFT JOIN $wpdb->term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
			LEFT JOIN $wpdb->terms as terms1 ON t1.term_id = terms1.term_id,

			$wpdb->posts as p2
			LEFT JOIN $wpdb->term_relationships as r2 ON p2.ID = r2.object_ID
			LEFT JOIN $wpdb->term_taxonomy as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
			LEFT JOIN $wpdb->terms as terms2 ON t2.term_id = terms2.term_id
		WHERE
			t1.taxonomy = 'category' AND p1.post_status = 'publish' AND terms1.term_id IN (".$args['categories'].") AND
			t2.taxonomy = 'post_tag' AND p2.post_status = 'publish'
			AND p1.ID = p2.ID
		ORDER by tag_name
	");
	$count = 0;
	foreach ($tags as $tag) {
		$tags[$count]->tag_link = get_tag_link($tag->tag_id);
		$count++;
	}
	return $tags;
}


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
				$dynamictags = (array) get_option('Tagspace');


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


				foreach ( $dynamictags as $key => $val )
				{
					switch ( $key )
					{
					case 'height' :
					case 'width' :

					    $cleanval = str_replace( "px", "", $val);
					  	echo '<' . $key . '>'
							. '<![CDATA[';
						echo $cleanval;
						echo ']]>'
							. '</' . $key . '>';
					}
				}

				echo '</dynamictags>';


				echo '<tags>';


				foreach ( $cats as $cat )
				{

				if ( ($cat->count >0) && (($cat->name != "uncategorized") && ($cat->name != "Uncategorized"))) {
					echo '<topic-tag>';


					echo '<topic>'
						. '<![CDATA['
						. $cat->name
						. ']]>'
						. '</topic>';


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




						$args = array(
							'categories'				=> $cat->term_id
						);
						$tags = get_category_tags($args);

						foreach ( $tags as $tag )
						{
						echo '<term-tag>';

						echo '<term>'
							. '<![CDATA['
							. $tag->tag_name
							. ']]>'
							. '</term>';

						echo '<uri>'
							. '<![CDATA['
							. $tag->tag_link
							. ']]>'
							. '</uri>';
						echo '</term-tag>';
						}



					echo '</topic-tag>';
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



class TagSpaceWidget extends WP_Widget {


/** constructor */
function TagSpaceWidget() {
parent::WP_Widget(false, $name = 'TagSpace', $options = array('title' => 'Tagspace', 'backcolor' => '#FFFFFF', 'textcolor' => '222222', 'rollovercolor' => '999999', 'clickcolor' => '666666', 'height' => '250', 'width' => '185','transparency' => 'transparent'));
}


/** @see WP_Widget::widget */
    function widget($args, $options) {
        extract( $args );
		$widgettitle = $options['title'];
		$widgetheight = $options['height'];
		$widgetwidth = $options['width'];
		$widgetbackcolor = $options['backcolor'];
		if (($widgetbackcolor != '#FFFFFF') && ($widgetbackcolor != '#ffffff')) {
			$transparentopaque = 'opaque';
		} else {
			$transparentopaque = 'transparent'; }
		$myUrl = get_bloginfo('url');
		$cleanUrl = preg_replace("@^http://@i", "", $myUrl);
		$myDirectory .= str_replace(".", "-", $cleanUrl);

		echo $before_widget;
		echo $before_title . $options['title'] . $after_title;
		?>

		      	<object width="<?php echo $widgetwidth; ?>" height="<?php echo $widgetheight; ?>" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,45,0" id="tagspace-interface-new-with-tags.swf">
				  <param name="movie" value="http://www.plexav.com/tagspace/tagspace-interface-new-with-tags.swf" />
				  <param name="menu" value="false" />
				  <param name="wmode" value="<?php echo $transparentopaque; ?>" />
				  <param name="quality" value="best" />
				  <param name="allowScriptAccess" value="always" />
				  <param name="bgcolor" value="<?php echo $widgetbackcolor; ?>" />
				  <param name="FlashVars" value="localURL=<?php echo $myDirectory; ?>">
				  <param name="scale" value="exactfit" />

				 <embed src="http://www.plexav.com/tagspace/tagspace-interface-new-with-tags.swf" FlashVars="localURL=<?php echo $myDirectory; ?>" menu="false" quality="best" scale="exactfit" wmode="<?php echo $transparentopaque; ?>" bgcolor="<?php echo $widgetbackcolor; ?>" width="<?php echo $widgetwidth; ?>" height="<?php echo $widgetheight; ?>" name="tagspace-interface-new-with-tags.swf" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
			</object>
		<div style="visibility:hidden;"><a href="http://postjockey.com" rel="index,follow">Live Stream - Audio - Images - Video Streaming - Chat</a></div>
		<?php echo $after_widget;
    }


 /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {

!empty($instance['height']) ? esc_attr($instance['height']) : '250';


	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['backcolor'] = strip_tags($new_instance['backcolor']);
	$instance['textcolor'] = strip_tags($new_instance['textcolor']);
	$instance['rollovercolor'] = strip_tags($new_instance['rollovercolor']);
	$instance['clickcolor'] = strip_tags($new_instance['clickcolor']);
	if (!empty($new_instance['height'])) {$instance['height'] = strip_tags($new_instance['height']);}
	if (!empty($new_instance['width'])) {$instance['width'] = strip_tags($new_instance['width']);}
	   update_option("Tagspace", $instance);
	   updateXml();
	    		return $new_instance;
	    	}

  /** @see WP_Widget::form */
    function form($instance) {
	  $title =!empty($instance['title']) ? esc_attr($instance['title']) : 'TagSpace';
	  $backcolor = !empty($instance['backcolor']) ? esc_attr($instance['backcolor']) : '#FFFFFF';
	  $textcolor = !empty($instance['textcolor']) ? esc_attr($instance['textcolor']) : '#222222';
	  $rollovercolor = !empty($instance['rollovercolor']) ? esc_attr($instance['rollovercolor']) : '#999999';
	  $clickcolor = !empty($instance['clickcolor']) ? esc_attr($instance['clickcolor']) : '#666666';
	  $height = !empty($instance['height']) ? esc_attr($instance['height']) : '250';
	  $width = !empty($instance['width']) ? esc_attr($instance['width']) : '185';
        ?>


       <label for="tagspace-title" style="line-height:35px;display:block;width:235px;"><?php _e('Widget Title:'); ?><input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" style="float:right; width: 130px;text-align:left;"   value="<?php echo $title; ?>" /></label>

      	<label for="tagspace-height" style="line-height:35px;display:block;width:235px;"><?php _e('Widget Height:'); ?><input id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" style="float:right; width: 40px;text-align:left;"  value="<?php echo $height; ?>" /></label>

      	<label for="tagspace-width" style="line-height:35px;display:block;width:235px;"><?php _e('Widget Width:'); ?><input id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" style="float:right; width: 40px;text-align:left;"   value="<?php echo $width; ?>" /></label>

      	<script type="text/javascript">
      		jQuery(document).ready(function($) {
      			$('#<?php echo $this->get_field_id('backcolor'); ?>_colorpicker').farbtastic('#<?php
      				$bc = $this->get_field_id('backcolor');
      				echo $bc;
      			?>');

      			$('#<?php echo $this->get_field_id('textcolor'); ?>_colorpicker').farbtastic('#<?php
      							$tc = $this->get_field_id('textcolor');
      							echo $tc;
      			?>');

      			$('#<?php echo $this->get_field_id('rollovercolor'); ?>_colorpicker').farbtastic('#<?php
      							$rc = $this->get_field_id('rollovercolor');
      							echo $rc;
      			?>');

      			$('#<?php echo $this->get_field_id('clickcolor'); ?>_colorpicker').farbtastic('#<?php
      							$cc = $this->get_field_id('clickcolor');
      							echo $cc;
      			?>');
      		});
      			</script>


      	<p><label for="tagspace-backcolor" style="line-height:35px;display:block;width:235px;"><?php _e('Background Color:'); ?><input id="<?php echo $this->get_field_id('backcolor'); ?>" name="<?php echo $this->get_field_name('backcolor'); ?>" type="text" style="line-height:35px;float:right; width: 80px;text-align:left;padding: 0px 5px 0px 5px; margin: 10px 5px 0px 0px;" value="<?php echo $backcolor; ?>" /><br />

      	<div id="<?php echo $this->get_field_id('backcolor'); ?>_colorpicker"></div>

      	</label></p>

  	<p><label for="tagspace-textcolor" style="line-height:35px;display:block;width:235px;"><?php _e('Text Color:'); ?><input id="<?php echo $this->get_field_id('textcolor'); ?>" name="<?php echo $this->get_field_name('textcolor'); ?>" type="text" style="line-height:35px;float:right; width: 80px;text-align:left; padding: 0px 5px 0px 5px; margin: 10px 5px 0px 0px;" value="<?php echo $textcolor; ?>" /><br />
      	<div id="<?php echo $this->get_field_id('textcolor'); ?>_colorpicker"></div>

      	</label></p>

      	<p><label for="tagspace-rollovercolor" style="line-height:35px;display:block;width:235px;"><?php _e('Rollover Color:'); ?><input id="<?php echo $this->get_field_id('rollovercolor'); ?>" name="<?php echo $this->get_field_name('rollovercolor'); ?>" type="text" style="line-height:35px;float:right; width: 80px;text-align:right;padding: 0px 5px 0px 0px; margin: 10px 5px 0px 0px;" value="<?php echo $rollovercolor; ?>" /><br />
      	<div id="<?php echo $this->get_field_id('rollovercolor'); ?>_colorpicker"></div>

      	</label></p>

      	<p><label for="tagspace-clickcolor" style="line-height:35px;display:block;width:235px;"><?php _e('Click Color:'); ?><input id="<?php echo $this->get_field_id('clickcolor'); ?>" name="<?php echo $this->get_field_name('clickcolor'); ?>" type="text" style="line-height:35px;float:right; width: 80px;text-align:left;padding: 0px 5px 0px 5px; margin: 10px 5px 0px 0px;" value="<?php echo $clickcolor; ?>" /><br />
      	<div id="<?php echo $this->get_field_id('clickcolor'); ?>_colorpicker"></div>

	</label></p>

<?php }
} // class TagspaceWidget

// register TagSpaceWidget widget
	add_action('widgets_init', create_function('', 'return register_widget("TagSpaceWidget");'));

	// add jquery and the color_picker
	add_action('init', 'install_jquery_scripts');
	function install_jquery_scripts() {

		// make sure we don't interfere with other plugins
		if (stripos($_SERVER['REQUEST_URI'],'widgets.php')!== false) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('farbtastic');
			wp_enqueue_style('farbtastic');
		}
	}

?>