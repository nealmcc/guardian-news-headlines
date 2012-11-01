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

require_once (ABSPATH . WPINC . '/widgets.php');
require_once (GUARDIAN_HEADLINES_PATH . 'gu_headline.php');

/** Guardian_Widget is the base widget for each list of news headlines.
 *
 * must over-ride WP_Widget::widget(), WP_Widget::update() and WP_Widget::form()
 */
class Guardian_Widget extends WP_Widget {

	private $default_config = array(
			'title' 	=> 'Latest from The Guardian',
			'type'		=> 'category',
			'section' 	=> 'news',
			'search'	=> '',							// only relevant for 'search' type widgets
			'quantity'	=> 5,
			'order' 	=> 'latest'
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

		$headlines = $this->get_headlines($instance);

		foreach ( $headlines as &$headline ) {
			$headline->display();
		}

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
	 * 'title'	=> any string, displayed above the widget
	 * 'type'	=> either 'category' or 'search'
	 * 'section'	=> only meaningful for a 'category' widget, and must be the id of one of of the news sections defined in headlines_config.json
	 * 'search'	=> only meaningful for an 'search' widget, and then will be passed directly to the guardian search API.
	 *		   searches can contain the following special characters:
	 * 			,	means AND
	 *			|	means OR
	 *			-	means NOT
	 * 'order'	=> can be 'latest' or 'most-viewed' for category queries
	 *		   can be 'latest' or 'most-relevant' for search queries
	 *		   NOTE: a special restriction exists for a category query for the "index" section.  In this case,
	 *			 only a 'most-viewed' query will return any results, 'latest' will return nothing useful.
	 * 'quantity'	=> can be between 1 and 10
	 *
	 * @return array Updated safe values to be saved.
	 * If "false" is returned, the instance won't be saved/updated.
	 */
	public function update($new, $old) {
		$new = wp_parse_args( (array) $new, $this->default_config );

		$save['title'] = sanitize_text_field($new['title']);

		$field = 'type';
		$allowed = array('category', 'search');
		$save[$field] = ( in_array($new[$field], $allowed) ) ? $new[$field] : $old[$field];

		if ( $save['type'] == 'category' ) {

			$save['search'] = '';

			$field = 'section';
			$allowed = array();
			$sections = get_option('guardian_headlines_sections');
			foreach ($sections as $section) {
				$allowed[] = $section->id;
			}

			$save[$field] = ( in_array($new[$field], $allowed) ) ? $new[$field] : $this->default_config[$field];

			if ( $save['section'] == 'index' ) { //the index section only has data for most-viewed, not latest.
				$save['order'] = 'most-viewed';
			} else {
				$field = 'order';
				$allowed = array('latest','most-viewed');
				$save[$field] = ( in_array($new[$field], $allowed) ) ? $new[$field] : 'latest';
			}

		} else { // type = search

			$save['section'] = '';

			$field = 'search';
			$save[$field] = sanitize_text_field($new[$field]);
			if ( empty($save[$field]) )
				$save[$field] = 'news';

			$field = 'order';
			$allowed = array('latest','relevance');
			$save[$field] = ( in_array($new[$field], $allowed) ) ? $new[$field] : 'latest';
		}

		$field = 'quantity';
		if ( intval($new[$field]) < 1 ) {
			$save[$field] = 1;
		} else if ( intval($new[$field]) > 10 ) {
			$save[$field] = 10;
		} else {
			$save[$field] = intval($new[$field]);
		}

		return $save;
	}

	/** Echo the settings update form
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $instance Current settings
	 */
	public function form($instance) {

		$instance = wp_parse_args( (array) $instance, $this->default_config );

		$field = 'title';
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Title', 'guardian_news').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance[$field] ).'" /><label></p>';

		$field = 'type';
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Feed Type', 'guardian_news').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance[$field] ).'" /><label></p>';

		$field = 'section';
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Section', 'guardian_news').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance[$field] ).'" /><label></p>';

		$field = 'search';
		$field_id = $this->get_field_id($search);
		$field_name = $this->get_field_name($field);
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Search', 'guardian_news').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance[$field] ).'" /><label></p>';

		$field = 'quantity';
		$field_id = $this->get_field_id($search);
		$field_name = $this->get_field_name($field);
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Quantity', 'guardian_news').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance[$field] ).'" /><label></p>';

		$field = 'order';
		$field_id = $this->get_field_id($search);
		$field_name = $this->get_field_name($field);
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Order', 'guardian_news').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance[$field] ).'" /><label></p>';

	}


	/** Get the latest feed from The Guardian, for the given widget options.
	 *
	 * If an up-to-date cached version of this query is available, we'll use that first. (TODO)
	 *
	 * @param string $query the string to submit to the Guardian content API
	 * @returns a (possibly empty) array of Gu_Headline objects
	 */
	private function get_headlines($widget_options) {

		$url = 'http://content.guardianapis.com/';
		$query = $this->build_query($widget_options);
		$feed = json_decode(file_get_contents($url . $query));

		$headlines = array();
		if ( empty($feed) || ($feed->response->status != 'ok' ) ) {
			return array();
		}

		// our data will be in a different part of the feed if we're asking for the most viewed articles.
		if ( $widget_options['order'] == 'most-viewed' ) {
			$quantity = $widget_options['quantity']; // most viewed always returns 10 results for a single news section
			$data = $feed->response->mostViewed;
		} else {
			$quantity = ( $feed->response->total < $widget_options['quantity'] ) ? $feed->response->total : $widget_options['quantity'];
			$data = $feed->response->results;
		}

		$headlines = $this->extract_headlines ($data, $quantity);

		return $headlines;
	}

	/** given an array of Guardian feed data, create an array of Gu_Headline objects
	 *
	 * The provided $data array must have at least $quantity elements. (TODO - check this.)
	 * We specify a quantity, rather than just taking everything from the array, because of how the mostViewed results
	 * come back from the Guardian Content API.  (When asking for most viewed for a given section, we always get 10 results)
	 */
	private function extract_headlines ($data, $quantity) {
		$headlines = array();

		for ($i = 0; $i < $quantity; $i++ ) {
			$headlines[] = new gu_headline(
									$data[$i]->fields->headline,
									$data[$i]->fields->standfirst,
									$data[$i]->fields->thumbnail,
									$data[$i]->webUrl
									);
		}

		return $headlines;
	}

	/** Build a query for the Guardian content API
	 * (see http://explorer.content.guardianapis.com/)
	 * Base our query on the given widget options.
	 * Assumes that the widget options have already been validated.
	 * @see Guardian_Widget::update() for valid widget options
	 */
	private function build_query($widget_options) {
		$query = array (
			'format'      => 'json',
			'show-fields' => 'thumbnail,standfirst,headline',
			'order-by'    => 'newest',
			'pageSize'    => $widget_options['quantity']
			);

		if ( $widget_options['type'] == 'category' ) {
			$base = $widget_options['section'] . '?';
			if ( $widget_options['order'] != 'latest' ) {
				$query['show-most-viewed'] = 'true';
			}
		} else { // advanced query - we're searching for a term
			$base = 'search?';
			$query['q'] = $widget_options['search'];
			if ( $widget_options['order'] != 'latest' ) {
				$query['order-by'] = 'relevance';
			}
		}

		$built_query = http_build_query($query, null, '&');

		return $base . $built_query;
	}


}//class Guardian_Widget
