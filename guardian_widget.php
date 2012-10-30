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

	private $default_config = array(
			'title' 	=> 'Latest from The Guardian',
			'type'		=> 'simple',
			'section' 	=> 'index',			// All
			'order' 	=> 'latest',
			'quantity'	=> 5
			);

	public function __construct() {
		parent::__construct(
	 		'guardian_widget', // Base ID
			'The Guardian Headlines', // Name
			array( 'description' => __( 'Displays news headlines from The Guardian', 'guardian_news' ), ) // Args
		);
	}

	/** Echo the widget content.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	public function widget($args, $instance) {
		extract($args, EXTR_SKIP);

		echo $before_widget;

		$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		global $guardian_headlines;
		$query = $guardian_headlines->build_query($instance);
		$headlines = $guardian_headlines->headlines($query);

		var_dump($headlines);

		echo $after_widget;
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
	 * widget options:
	 * 'title'	=> a string, displayed above the widget
	 * 'type'	=> either 'simple' or 'advanced'
	 * 'section'	=> only meaningful for a 'simple' widget, and must be the id of one of of the news sections defined in headlines_config.json
	 * 'search'	=> only meaningful for an 'advanced' widget, and then will be passed directly to the guardian search API.
	 *		   searches can contain the following special characters:
	 * 			,	means AND
	 *			|	means OR
	 *			-	means NOT
	 * 'order'	=> can be 'latest' or 'most-viewed' for simple queries
	 *		   can be 'latest' or 'most-relevant' for advanced queries
	 *		   NOTE: a special restriction exists for a simple query for the "index" section.  In this case,
	 *			 only a 'most-viewed' query will return any results.
	 * 'quantity'	=> can be between 1 and 10
	 *
	 * @return array Updated safe values to be saved.
	 * If "false" is returned, the instance won't be saved/updated.
	 */
	public function update($new_instance, $old_instance) {
		//TODO
		//$instance = wp_parse_args( (array) $new_instance, $this->default_config );

		$instance = $default_config;

		return $instance;
	}

	/** Echo the settings update form
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $instance Current settings
	 */
	public function form($instance) {

		$instance = wp_parse_args( (array)$instance, $this->defaults );

		$field_id = $this->get_field_id('title');
		$field_name = $this->get_field_name('title');
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Title', 'guardian_news').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance['title'] ).'" /><label></p>';

		$field_id = $this->get_field_id('section');
		$field_name = $this->get_field_name('section');
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Category', 'guardian_news').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance['section'] ).'" /><label></p>';
	}


}//class Guardian_Widget
