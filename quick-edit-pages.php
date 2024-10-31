<?php
/*
Plugin Name: Quick Edit Page Popup Menu
Plugin URI: http://blog.rowbory.co.uk/sw/wordpress/quick_edit_page
Description: A dropdown menu to jump straight into editing pages
Version: 0.4.5
Author: David Rowbory
Author URI: http://www.rowbory.co.uk
*/
/**
 * Quick Edit Page Popup Menu
 *
 * @category      Wordpress Plugins
 * @package       Plugins
 * @author        David Rowbory
 * @copyright     Yes, Open source
 * @version       v 0.4.5
 *
 *
 * Features:
 * 	Dropdown box on a line under the Pages subitem of the Pages menu in Admin to directly edit page.
 *	Pushes pages up above posts with JS.
 *
 * Feature Changelog:
 * 	0.3 Adds private and draft pages.
 * 	0.4	Adds a marker for draft, pending and private pages
 *
 * Wishlist:
 * 	Settings for turning each action on or off.
 *	Setting for putting dropdown in PLACE of or to right of the Pages subitem.
 *	Add the order number before pages.
 */

/*  
	Copyright 2010-2012 David Rowbory (wordpress@m.rowbory.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
    
    http://www.gnu.org/licenses/gpl.html
*/

if (!defined('ABSPATH')) die("No direct access to this plugin file.");

//add_action('admin_menu', 'rowbory_qep_add_menu');
add_action("admin_footer",array("rowbory_qep","generate_popup"));

class rowbory_qep {
	static $settingPrefix = "rowbory_qep_";
	static $settings = array('pages_top', 'popup_edit');
	static $defaults = array('pages_top' => true, 'popup_edit' => true);
	
	/**
	 * Adds the 'Quick Edit' dropdown menu to the admin sidebar. 
	 * Do this by putting it in the footer then moving it into place with javascript.
	 */
	function generate_popup() {
		echo '<style type="text/css">
		.status_draft { color: red; font-style: italic }
		#rowbory_qep_popup_link select { font-weight: normal; }
		</style>
		<div id="rowbory_qep_popup" style="display: none;"><a id="rowbory_qep_popup_link">';
		$current_page = ""; //$wp_query->get_queried_object_id();
		wp_dropdown_pages(array('exclude_tree' => 0, 
					'selected' => $current_page, 
					'name' => 'rowbory_qep_menu', 
					'show_option_none' => __("&rarr; Quick Edit &rarr;"), 
					'sort_column' => 'menu_order',
					'post_status' => 'publish,draft,private',
					'walker' => new rowbory_qep_Walker()));	// , post_title'
		echo '</a></div>
		<script type="text/javascript">
		<!-- //	
		jQuery("#menu-pages .wp-submenu li.wp-first-item").append(jQuery("#rowbory_qep_popup_link"));
		jQuery("#rowbory_qep_menu").change( function () {
			p = (this.options[this.selectedIndex].value);
			location = "post.php?post="+p+"&action=edit";
		}).css("width","96%");
		jQuery("#menu-posts").before(jQuery("#menu-pages"));
		jQuery("#menu-pages").addClass("menu-top-first");
		jQuery("#menu-posts").removeClass("menu-top-first");
		// -->
		</script>';
	/*
	Above javascript adds a new line to the li#menu-pages div.wp-submenu ul 
	*/
	}
	

	function saveSetting($key, $value)
	{
		$thisKey = self::$settingPrefix . $key;
		if(get_option($thisKey) === false) {
			add_option($thisKey, $value);
		} else {
			update_option($thisKey, $value);
		}
	}
	
	function loadSetting($key)
	{
		$option = get_option(self::$settingPrefix . $key);
		if($option === false)
			$option = ((isset(self::$defaults[$key]))? self::$defaults[$key] : false);
		
		return $option;
	}
	
}

class rowbory_qep_Walker extends Walker_PageDropdown {
	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object.
	 * @param int $depth Depth of page in reference to parent pages. Used for padding.
	 * @param array $args Uses 'selected' argument for selected page to set selected HTML attribute for option element.
	 */
	function start_el(&$output, $page, $depth, $args) {
		$pad = str_repeat('&nbsp;', $depth * 3);
		
		$prefix = ""; $class_extra = "status_".get_post_status( $page->ID);
		switch( get_post_status( $page->ID) ) {
			case "pending":
				$prefix = "(pending) "; break;
			case "draft": case "auto-draft":
				$prefix = "(draft) "; break;
			case "private":
				$prefix = "(private) "; break;
			case "trash":
				$prefix = "DELETED: "; return; break;	// jump out. Don't include deleted posts/pages
			default:
		}

		$output .= "\t<option class=\"level-$depth $class_extra\" value=\"$page->ID\"";
		if ( $page->ID == $args['selected'] )
			$output .= ' selected="selected"';
		$output .= '>';
		$title = apply_filters( 'list_pages', $page->post_title, $page );
		$output .= $pad . $prefix . esc_html( $title );
		$output .= "</option>\n";
	}
}


	


?>