<?php
/*
 * Plugin Name: Guardian News Headlines
 * Contributors: NealMcConachie
 * Version: 0.2
 * Description: Displays a feed of Guardian news headlines into a widget. Each headline links to the live news article.
 * Author: Neal McConachie for Guardian News Media
 * Author URI: http://www.riveroak.co/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
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
	echo __("Hi there!  I'm just a plugin, not much I can do when called directly.",'guardian_headlines');
	exit;
}

define( 'GUARDIAN_HEADLINES_PATH', plugin_dir_path(__FILE__) );
require_once ( GUARDIAN_HEADLINES_PATH . 'guardian_widget.php' );

global $guardian_headlines;
$guardian_headlines = new Guardian_Headlines();

/** Namespace wrapper */
class Guardian_Headlines {
	private $version = '0.2';

	public function __construct() {
		register_activation_hook(__FILE__, array(&$this, 'install') );
		register_deactivation_hook( __FILE__, array(&$this, 'uninstall') );

		add_action('plugins_loaded', array(&$this, 'update_check') );
		add_action('widgets_init', array(&$this, 'register_widgets') );

	}

	/** Initial database setup for our plugin.
	 * Runs when the plugin is activated or updated to a newer version.
	 */
	public function install() {
		$section_list = json_decode(file_get_contents( GUARDIAN_HEADLINES_PATH . 'headlines_config.json'));

		update_option("guardian_headlines_version", $this->version);
		update_option('guardian_headlines_sections', $section_list);

	}

	/** Clean up behind ourselves.
	 * Runs on plugin deactivation.
	 */
	public function uninstall() {
		delete_option('guardian_headlines_version');
		delete_option('guardian_headlines_sections');
	}

	/**
	 * Check to see if our plugin version has changed from what the user has
	 * installed, and if it has, re-install our list of news sections.
	 *
	 * This function will run after WordPress loads all plugins.
	 */
	public function update_check() {
		if (get_option("guardian_headlines_version") != $this->version) {
			$this->install();
		}
	}

	/** Inform Wordpress of our widget */
	public function register_widgets() {
		register_widget( 'Guardian_Widget' );
	}

	/** Build a query for the Guardian content API
	 * Base our query on the given widget options.
	 * Assumes that the widget options have already been validated.
	 * @see Guardian_Widget::update() for valid widget options
	 */
	public function build_query($widget_options) {
		$query = array (
			'format'	=> 'json',
			'show-fields'	=> 'thumbnail,standfirst,headline',
			'order-by'	=> 'newest',
			);

		if ( $widget_options['type'] == 'simple' ) {
			$base = $widget_options['section'] . '?';
			if ( $widget_options['order'] != 'latest' ) {
				$query['show-most-viewed'] = 'true';
			}
		} else { // advanced query
			$base = 'search?';
			$query['q'] = $widget_options['search'];
			if ( $widget_options['order'] != 'latest' ) {
				$query['order-by'] = 'relevance';
			}
		}

		$query['pageSize'] = $widget_options['quantity'];

		$built_query = http_build_query($query, null, '&');

		return $base . $built_query;
	}

	/** headlines gets the latest feed from The Guardian, for the given query arguments.
	 * Assumes the query is valid for The Guardian content API
	 * (see http://explorer.content.guardianapis.com/)
	 * If an up-to-date cached version of this query is available, we'll use that first. (TODO)
	 *
	 * @param string $query the string to submit to the Guardian content API
	 */
	public function headlines($query) {
		$url = 'http://content.guardianapis.com/';

		$result = json_decode(file_get_contents($url . $query));

		return $result;
	}

}
?>
