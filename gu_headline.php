<?php
/*
 * Class Name: Gu_Headline
 * Description: Object for each Guardian Headline
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

class Gu_Headline {
	private $headline;
	private $thumbnail;
	private $url;

	/** Create a headline from:
	 * @param headline 	- string, containing the article's headline
	 * @param teaser	- string, a short intro to the article
	 * @param thumbnail - string, containing a URL to the article's thumbnail
	 * @param url 		- string, URL to the article itself
	 */
	function __construct ($headline, $thumbnail, $url) {
		$allowed_html = array('a','p','em','strong','b','i');

		$this->headline = wp_kses($headline, $allowed_html);
		$this->thumbnail = wp_kses($thumbnail, $allowed_html);
		$this->url = wp_kses($url, $allowed_html) . '?cmp=wp-plugin';
	}

	/** Display this headline to stdout.
	 * Does not address style issues.
	 */
	public function display() {
		ob_start();
		?>

		<p><a href="<?php echo $this->url; ?>"><?php echo $this->headline; ?></a></p>
		<?php if ( !empty($this->thumbnail) ) : ?>
			<img alt="" src="<?php echo $this->thumbnail; ?>" />
		<?php endif; ?>

		<?php
		ob_end_flush();

	}
}

?>
