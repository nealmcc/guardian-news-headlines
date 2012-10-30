<?php
/*
 * Class Name: Guardian Widget
 * Description: Object for each widget on a site.
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

require_once(ABSPATH . WPINC . '/widgets.php');

/** Guardian_Widget is the base widget for each list of news headlines.
 *
 * must over-ride WP_Widget::widget(), WP_Widget::update() and WP_Widget::form()
 */
class Guardian_Widget extends WP_Widget {

	private $defaults = array(
			'category' => 'theguardian',
			'order' => 'recent',
			'quantity' => 5
			);

	public function __construct() {
		parent::__construct(
	 		'guardian_widget', // Base ID
			'The Guardian News', // Name
			array( 'description' => __( 'Displays news headlines from The Guardian', 'guardian_news' ), ) // Args
		);
	}

	/** Echo the widget content.
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	public function widget($args, $instance) {
		echo 'Guardian News Widget (Stub)';

	}

	/** Update a particular instance.
	 *
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * If "false" is returned, the instance won't be saved/updated.
	 */
	public function update($new_instance, $old_instance) {
		//stub - for now, just accept all inputs.

		return $new_instance;
	}

	/** Echo the settings update form
	 *
	 * @param array $instance Current settings
	 */
	public function form($instance) {

		$instance = wp_parse_args( (array) $instance, $defaults );

		$field_id = $this->get_field_id('category');
		$field_name = $this->get_field_name('category');
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Category', 'guardian_news').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance['category'] ).'" /><label></p>';
	}

}//class Guardian_Widget
