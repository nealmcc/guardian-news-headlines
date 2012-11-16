<?php
/**
 * Class Name: Gu_Cache
 * Description: Maintains our cache of headlines.
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

/** Gu_Cache Controls the cache for our plugin. */
class Gu_Cache {

	private $table;
	private $cache_time = '0:5:0'; // h:m:s

	public function __construct($table) {
		global $wpdb;
		$this->table = $wpdb->prefix . $table;
	}

	/** Create the table that will hold our cache. */
	public function create() {
		$section_list = get_option('guardian_headlines_sections');
		$section_enum = "'{$section_list[0]->id}'";
		$count = count($section_list);
		for ( $i = 1; $i < $count; $i++ ) {
			$section_enum .= ", '{$section_list[$i]->id}'";
		}

		$table_desc = "CREATE TABLE " . $this->table . " (
				section ENUM(" . $section_enum . ") NOT NULL,
				type ENUM('latest', 'most-viewed') NOT NULL,
				quantity TINYINT UNSIGNED NOT NULL,
				headlines BLOB,
				timestamp TIMESTAMP,
				PRIMARY KEY (section, type, quantity)
				);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($table_desc);
	}

	/** Remove the cache */
	public function remove() {
		global $wpdb;

		$wpdb->query("DROP TABLE {$this->table}");
	}

	/** Get a cached set of headlines

		If the requested headlines are not in the cache, or are stale, we return false.
		Otherwise, we return an array of Gu_Headlines.
	*/
	public function headlines($section, $order, $quantity) {
		global $wpdb;

		$result = $wpdb->get_var(
					$wpdb->prepare(
						"
							SELECT headlines FROM {$this->table}
							WHERE section = %s
							AND type = %s
							AND quantity = %s
							AND timestamp >= SUBTIME(NOW(), %s);
						",
						array(
							$section,
							$order,
							$quantity,
							$this->cache_time
							)
						),
					0,0);

		$headlines = ( empty($result) ) ? false : unserialize(base64_decode($result));

		return $headlines;
	}

	/** Store the given headlines

		If the given combination of $section, $order, and $quantity are already
		cached, we update their headlines and timestamp.
	 */
	public function store($section, $order, $quantity, $headlines) {
		global $wpdb;

		$store_me = base64_encode(serialize($headlines));

		$query = $wpdb->prepare(
			"
					INSERT INTO {$this->table}
					(section, type, quantity, headlines )
					VALUES ( %s, %s, %d, %s )
					ON DUPLICATE KEY UPDATE
					headlines = VALUES(headlines),
					timestamp = NOW();
				",
				array (
					$section,
					$order,
					$quantity,
					$store_me
					)
			);

		$wpdb->query($query);

	}
}

?>
