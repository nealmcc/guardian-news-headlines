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
	echo __("Hi there!  I'm just a plugin, not much I can do when called directly.",'guardian_news');
	exit;
}

define( 'GUARDIAN_NEWS_PATH', plugin_dir_path(__FILE__) );
require_once ( GUARDIAN_NEWS_PATH . 'guardian_widget.php' );

$guardian_news = new Guardian_News();

/** mainly a namespace wrapper
 */
class Guardian_News {
	private $version;
	private $table;

	public function __construct() {
		global $wpdb;
		$this->version = "0.1";
		$this->table = $wpdb->prefix . "guardian_news";

		register_activation_hook(__FILE__, array(&$this, 'install') );

		add_action('plugins_loaded', array(&$this, 'update_db_check') );
		add_action('widgets_init', array(&$this, 'register_widgets') );

	}

	public function register_widgets() {
		register_widget( 'Guardian_Widget' );
	}


	/** Perform the initial database setup for our plugin.
	 * This function will run when the plugin is activated.
	 */
	public function install() {
		//stub
	}

	/**
	 * Check to see if our plugin version has changed from what the user has
	 * installed, and if it has, re-install the MySQL table.
	 *
	 * This function will run after WordPress loads the plugins.
	 */
	public function update_db_check() {
		if (get_option("guardian_news_version") != $this->version) {
			$this->install();
		}
	}
}
?>
