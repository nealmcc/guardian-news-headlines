<?php
/*
 * Class Name: Gu_Widget
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

/** Gu_Widget is the base widget for each list of news headlines.
 *
 * must over-ride WP_Widget::widget(), WP_Widget::update() and WP_Widget::form()
 */
class Gu_Widget extends WP_Widget {

	private $default_config = array();
	private $logos = array();

	public function __construct() {
		parent::__construct(
			'guardian_headlines_widget', // Base ID
			'The Guardian Headlines', // Name
			array( 'description' => __( 'Displays news headlines from The Guardian', 'guardian_headlines' ), ) // Args
		);

		$this->default_config = array(
			'title'     => 'Latest from The Guardian',
			'section'   => 'index',
			'order'     => 'most-viewed',
			'quantity'  => 5,
			'logo'      => 'normal'
			);

		$this->logos = array (
			'normal' => array(	'desc' => __('Normal', 'guardian_headlines'),
								'demo' => '/img/logo-normal.jpg',
								'live' => '/img/poweredbyguardian.png'
							),
			'black' => 	array(	'desc' => __('Black', 'guardian_headlines'),
								'demo' => '/img/logo-black.jpg',
								'live' => '/img/poweredbyguardianBLACK.png'
							),
			'reverse' => array(	'desc' => __('Reverse', 'guardian_headlines'),
								'demo' => '/img/logo-reverse.jpg',
								'live' => '/img/poweredbyguardianREV.png'
							),
			'white' => array(	'desc' => __('White', 'guardian_headlines'),
								'demo' => '/img/logo-white.jpg',
								'live' => '/img/poweredbyguardianWHITE.png'
							)
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

		echo '<div class="gu_headlines_widget_inner">';
		$headlines = $this->get_headlines($instance);

		foreach ( $headlines as &$headline ) {
			echo '<div class="gu_headline">';
			$headline->display();
			echo '</div>';
		}
		$this->show_logo($instance['logo']);
		echo '</div>';

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
	 * 'title'      => any string, displayed above the widget
	 * 'section'    => the id of one of of the news sections defined in headlines_config.json
	 * 'order'      => can be 'latest' or 'most-viewed'
	 *              NOTE: a special restriction exists for a category query for the "index" section.  In this case,
	 *                    only a 'most-viewed' query will return any results, 'latest' will return nothing useful.
	 * 'quantity'   => can be between 1 and 10
	 *
	 * 'logo'		=> one of 'normal', 'black', 'white', or 'reverse'
	 *
	 * @return array Updated safe values to be saved.
	 * If "false" is returned, the instance won't be saved/updated.
	 */
	public function update($new, $old) {
		$new = wp_parse_args( (array) $new, $this->default_config );

		$field = 'title';
		$save[$field] = sanitize_text_field($new[$field]);

		$field = 'section';
		$allowed = array();
		$sections = get_option('guardian_headlines_sections');
		foreach ($sections as $section) {
			$allowed[] = $section->id;
		}
		$save[$field] = ( in_array($new[$field], $allowed) ) ? $new[$field] : $this->default_config[$field];

		$field = 'order';
		if ( $save['section'] == 'index') {
			$save[$field] = 'most-viewed';
		} else {
			$allowed = array('latest','most-viewed');
			$save[$field] = ( in_array($new[$field], $allowed) ) ? $new[$field] : $this->default_config[$field];
		}

		$field = 'quantity';
		if ( ! is_numeric($new[$field]) ) {
			$save[$field] = $this->default_config[$field];
		} else {
			if ( intval($new[$field]) < 1 ) {
				$save[$field] = 1;
			} else if ( intval($new[$field]) > 10 ) {
				$save[$field] = 10;
			} else {
				$save[$field] = intval($new[$field]);
			}
		}

		$field = 'logo';
		$allowed = array();
		foreach ($this->logos as $id => $logo) {
			$allowed[] = $id;
		}
		$save[$field] = ( in_array($new[$field], $allowed) ) ? $new[$field] : $this->default_config[$field];

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
		$label = __('Title:', 'guardian_headlines');
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		$value = esc_attr($instance[$field]);
		echo "<p>" .
				"<label for='{$field_id}'>{$label}</label>" .
				"<input id='{$field_id}' type='text' class='widefat' name='{$field_name}' value='{$value}' />" .
			"</p>";


		$field = 'section';
		$label = __('Category:', 'guardian_headlines');
		$options = get_option('guardian_headlines_sections');
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		$value = esc_attr($instance[$field]);
		echo "<p><label for='{$field_id}'>{$label}</label>" .
				"<select id='{$field_id}' class='widefat' name='{$field_name}' size='1'>";
				foreach ($options as $section) {
					$selected = ( $instance[$field] == $section->id ) ? "selected='selected'" : '';
					echo "<option value='{$section->id}' {$selected}>{$section->webTitle}</option>";
				}
		echo	"</select>" .
			 '</p>';

		$field = 'order';
		$label = __('Order Headlines by:', 'guardian_headlines');
		$options = array (
				'latest'        => __('Latest',      'guardian_headlines'),
				'most-viewed'   => __('Most Viewed', 'guardian_headlines')
				);
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		echo "<p>{$label}<br />";
			foreach ($options as $value => $description) {
				$checked = ( $instance[$field] == $value )? 'checked="true"' : '';
				echo "<input id='{$field_id}_{$value}' type='radio' name='{$field_name}' value='$value' $checked />" .
					 "<label for='{$field_id}_{$value}'> {$description}</label><br />";
			}
		echo '</p>';

		$field = 'quantity';
		$label = __('Quantity:', 'guardian_headlines');
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		$value = esc_attr($instance[$field]);
		echo "<p><label for='{$field_id}'>{$label} </label>" .
				"<select id='{$field_id}' name='{$field_name}' size='1'>";
			for ($i = 1; $i <= 10; $i++) {
				$selected = ( $instance[$field] == $i ) ? "selected='selected'" : '';
				echo "<option value='{$i}' {$selected}>{$i}</option>";
			}
		echo   '</select>' .
			'</p>';

		$field = 'logo';
		$label = __('Logo:', 'guardian_headlines');
		$field_id = $this->get_field_id($field);
		$field_name = $this->get_field_name($field);
		$value = esc_attr($instance[$field]);
		$url_base = plugins_url('', __FILE__ );
		echo $label . '<ul class="gu_widget_admin_logo">';
		foreach ($this->logos as $type => $details) {
			$checked = ( $instance[$field] == $type ) ? 'checked="true"' : '';
			echo "<li>" .
					"<label for='{$field_id}_{$type}'>" .
						"<img alt='{$details['desc']}' src='{$url_base}{$details['demo']}' />" .
					"</label><br />" .
					"<input id='{$field_id}_{$type}' type='radio' name='{$field_name}' value='{$type}' {$checked}/>" .
					"<label for='{$field_id}_{$type}'> {$details['desc']}</label>" .
				"</li>";
		}
		echo '</ul>';

	}


	/** Get the headlines, for the given widget options.

		If a fresh cached version of this query is available, we'll use that first.

		@returns a (possibly empty) array of Gu_Headline objects
	 */
	private function get_headlines($widget_options) {
		global $guardian_headlines;

		$section = $widget_options['section'];
		$order = $widget_options['order'];
		$quantity = $widget_options['quantity'];
		$headlines = $guardian_headlines->cache->headlines($section, $order, $quantity);

		if ( $headlines === false ) {
			$headlines = $this->fresh_headlines($section, $order, $quantity);
			$guardian_headlines->cache->store($section, $order, $quantity, $headlines);
		}

		return $headlines;
	}

	/** Ask the Guardian Content API for some headlines */
	private function fresh_headlines($section, $order, $quantity) {
		$url = 'http://content.guardianapis.com/';
		$query = $this->build_query($section, $order, $quantity);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_URL, $url . $query);

		$feed = curl_exec($ch);

		curl_close($ch);

		$results = json_decode($feed);

		$headlines = array();
		if ( empty($results) || ($results->response->status != 'ok' ) ) {
			return array();
		}

		// our data will be in a different part of the feed if we're asking for the most viewed articles.
		if ( $order == 'most-viewed' ) {
			$data = $results->response->mostViewed;
		} else {
			$quantity = ( $results->response->total < $quantity ) ? $results->response->total : $quantity;
			$data = $results->response->results;
		}

		$headlines = $this->extract_headlines ($data, $quantity);

		return $headlines;
	}

	/** given an array of Guardian feed data, create an array of Gu_Headline objects

		The provided $data array must have at least $quantity elements.
		We specify a quantity, rather than just taking everything from the array, because of how the mostViewed results
		come back from the Guardian Content API.
		(When asking for most viewed for a given section, we always get 10 results, and we might not want them all.)
	**/
	private function extract_headlines ($data, $quantity) {
		for ($i = 0; $i < $quantity; $i++ ) {
			$headlines[] = new gu_headline(
							$data[$i]->fields->headline,
							( !empty ($data[$i]->fields->thumbnail) ) ? $data[$i]->fields->thumbnail : '',
							$data[$i]->webUrl
							);
		}

		return $headlines;
	}

	/** Build a query for the Guardian content API

		(see http://explorer.content.guardianapis.com/)
		Base our query on the given $section, $order and $quantity.
		Assumes that these options have already been validated.
		@see Gu_Widget::update() for valid options
	 */
	private function build_query($section, $order, $quantity) {
		$query = array (
			'format'      => 'json',
			'show-fields' => 'thumbnail,headline',
			'order-by'    => 'newest',
			'pageSize'    => $quantity
			);

		if ( $order == 'most-viewed' )
			$query['show-most-viewed'] = 'true';

		$base = $section . '?';

		$built_query = http_build_query($query, null, '&');

		return $base . $built_query;
	}

	/** Display the powered by The Guardian logo and link */
	private function show_logo($type = 'normal') {
		$link_url = 'http://www.guardian.co.uk/open-platform';
		$img_url = plugins_url($this->logos[$type]['live'], __FILE__ );
		$alt_text = __('Powered by The Guardian', 'guardian_headlines');

		echo "<div class='powered_by'><a href='{$link_url}'>" .
				"<img alt='{$alt_text}' src='{$img_url}' />" .
			 "</a></div>";
	}

}//class Gu_Widget
