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
	private $fresh_seconds;

	public function __construct($table, $fresh = 300) {
		global $wpdb;
		$this->table = $wpdb->prefix . $table;
		$this->fresh_seconds = $fresh;
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
		//stub

		return false;
	}

	/** Store the given headlines */
	public function store($section, $order, $quantity, $headlines) {
		//stub
	}
}

?>
