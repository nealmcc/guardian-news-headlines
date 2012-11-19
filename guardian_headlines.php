<?php
/*
 * Plugin Name: Guardian News Headlines
 * Plugin URI: http://www.guardian.co.uk/open-platform
 * Version: 0.5.2
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
require_once ( GUARDIAN_HEADLINES_PATH . 'gu_widget.php' );
require_once ( GUARDIAN_HEADLINES_PATH . 'gu_cache.php' );

$guardian_headlines = new Guardian_Headlines();

/** Namespace wrapper */
class Guardian_Headlines {
	private $version = '0.5.2';
	public $cache;

	public function __construct() {

		$this->cache = new Gu_Cache('guardian_headlines');

		register_activation_hook(__FILE__, array(&$this, 'install') );
		register_deactivation_hook( __FILE__, array(&$this, 'uninstall') );

		add_action('plugins_loaded', array(&$this, 'update_check') );
		add_action('widgets_init', array(&$this, 'register_widgets') );
		add_action('wp_enqueue_scripts', array(&$this, 'front_scripts') );
		add_action('admin_enqueue_scripts', array(&$this, 'admin_scripts') );

		add_action('admin_notices', array(&$this, 'activate_notify'	));

	}

	/** Initial database setup for our plugin.
	 * Runs when the plugin is activated or updated to a newer version.
	 */
	public function install() {
		$section_list = json_decode(file_get_contents( GUARDIAN_HEADLINES_PATH . 'headlines_config.json'));

		update_option('guardian_headlines_version', $this->version);
		update_option('guardian_headlines_sections', $section_list);

		$this->cache->create();
	}

	/** Clean up behind ourselves.
	 * Runs on plugin deactivation.
	 */
	public function uninstall() {
		delete_option('guardian_headlines_version');
		delete_option('guardian_headlines_sections');
		delete_option('guardian_headlines_notified');

		$this->cache->remove();
	}

	/**
	 * Check to see if our plugin version has changed from what the user has
	 * installed, and if it has, re-install our list of news sections.
	 *
	 * This function will run every time after WordPress loads all plugins.
	 */
	public function update_check() {
		if (get_option("guardian_headlines_version") != $this->version) {
			$this->cache->remove();
			$this->install();
		}
	}

	/** Inform Wordpress of our widget */
	public function register_widgets() {
		register_widget( 'Gu_Widget' );
	}

	/** Enqueue our scripts and styles for the front-facing side. */
	public function front_scripts() {
		wp_register_style('guardian_headlines_style', plugins_url('guardian_headlines.css', __FILE__), $deps = false, $this->version );
		wp_enqueue_style('guardian_headlines_style');
	}

	/** Enqueue our scripts and styles for the admin side. */
	public function admin_scripts() {
		wp_register_style('gu_widget_admin_style',
							plugins_url('gu_widget_admin.css', __FILE__),
							$deps = false,
							$this->version
							);
		wp_enqueue_style('gu_widget_admin_style');
	}

	/** Thank the user for installing the plugin, and point them at the widget page */
	public function activate_notify() {
		$notified = get_option('guardian_headlines_notified', false);
		if ( ( $notified === false) && (current_user_can('edit_theme_options') ) ) {
			echo '<div class="updated">'.
					'<p>'.
					__('Thanks for installing The Guardian Headlines.', 'guardian_headlines') . '<br />'.
					__('To set up your headline feed, add <em>The Guardian Headlines</em> ', 'guardian_headlines') .
					'<a href="'. admin_url('widgets.php') .'">' . __('Widget','guardian_headlines') . '</a> '.
					__('to your sidebar of choice. :)', 'guardian_headlines') . '<br />'.
					'<a href="'. admin_url('widgets.php'). '">&#187; ' . __('Widgets page', 'guardian_headlines') . '</a>'.
					'</p>'.
				'</div>';

			update_option('guardian_headlines_notified', 'true');
		}
	}

}

?>
