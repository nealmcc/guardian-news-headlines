<?php
/*
 * Plugin Name: Guardian News
 * Version: 0.1
 * Description: Displays a feed of Guardian news headlines into a widget.
 * Author: Neal McConachie for Guardian News Media
 * Author URI: http://www.riveroak.co/
 * License: GPLv2 or later
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

global $gnews = &$guardian_news = new Guardian_news();

/**
 * The Guardian News class is mainly a namespace wrapper for all Guardian News functions.
 */
class Guardian_news {
	private $version;
	private $table;

	/**
	 * set a few variables,
	 * Tell WordPress about our hooks and filters
	 *
	 * TODO: Load our options.
	 */
	public function __construct() {
		global $wpdb;
		$this->version = "0.1";
		$this->table = $wpdb->prefix . "guardian_news";

		register_activation_hook(__FILE__, array(&$this, 'install'));

		add_action('plugins_loaded', array(&$this, 'update_db_check'));
		add_action('init', array(&$this, 'init'));

	}
}
?>
