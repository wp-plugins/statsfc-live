<?php
/*
Plugin Name: StatsFC Live
Plugin URI: https://statsfc.com/docs/wordpress
Description: StatsFC Live
Version: 1.6.4
Author: Will Woodward
Author URI: http://willjw.co.uk
License: GPL2
*/

/*  Copyright 2013  Will Woodward  (email : will@willjw.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('STATSFC_LIVE_ID',	'StatsFC_Live');
define('STATSFC_LIVE_NAME',	'StatsFC Live');

/**
 * Adds StatsFC widget.
 */
class StatsFC_Live extends WP_Widget {
	public $isShortcode = false;

	private static $defaults = array(
		'title'			=> '',
		'key'			=> '',
		'competition'	=> '',
		'team'			=> '',
		'default_css'	=> true
	);

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(STATSFC_LIVE_ID, STATSFC_LIVE_NAME, array('description' => 'StatsFC Live'));
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @todo Option to show match incidents.
	 */
	public function form($instance) {
		$instance		= wp_parse_args((array) $instance, self::$defaults);
		$title			= strip_tags($instance['title']);
		$key			= strip_tags($instance['key']);
		$competition	= strip_tags($instance['competition']);
		$team			= strip_tags($instance['team']);
		$default_css	= strip_tags($instance['default_css']);
		?>
		<p>
			<label>
				<?php _e('Title', STATSFC_LIVE_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Key', STATSFC_LIVE_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('key'); ?>" type="text" value="<?php echo esc_attr($key); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Competition', STATSFC_LIVE_ID); ?>:
				<?php
				try {
					$data = $this->_fetchData('https://api.statsfc.com/crowdscores/competitions.php');

					if (empty($data)) {
						throw new Exception;
					}

					$json = json_decode($data);

					if (isset($json->error)) {
						throw new Exception;
					}
					?>
					<select class="widefat" name="<?php echo $this->get_field_name('competition'); ?>">
						<option></option>
						<?php
						foreach ($json as $comp) {
							echo '<option value="' . esc_attr($comp->key) . '"' . ($comp->key == $competition ? ' selected' : '') . '>' . esc_attr($comp->name) . '</option>' . PHP_EOL;
						}
						?>
					</select>
				<?php
				} catch (Exception $e) {
				?>
					<input class="widefat" name="<?php echo $this->get_field_name('competition'); ?>" type="text" value="<?php echo esc_attr($competition); ?>">
				<?php
				}
				?>
			</label>
		</p>
		<p>
			<label>
				<?php _e('Team', STATSFC_LIVE_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('team'); ?>" type="text" value="<?php echo esc_attr($team); ?>" placeholder="e.g., Liverpool, Manchester City">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Use default CSS?', STATSFC_LIVE_ID); ?>
				<input type="checkbox" name="<?php echo $this->get_field_name('default_css'); ?>"<?php echo ($default_css == 'on' ? ' checked' : ''); ?>>
			</label>
		</p>
	<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance) {
		$instance					= $old_instance;
		$instance['title']			= strip_tags($new_instance['title']);
		$instance['key']			= strip_tags($new_instance['key']);
		$instance['competition']	= strip_tags($new_instance['competition']);
		$instance['team']			= strip_tags($new_instance['team']);
		$instance['default_css']	= strip_tags($new_instance['default_css']);

		return $instance;
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance) {
		extract($args);

		$title			= apply_filters('widget_title', $instance['title']);
		$key			= $instance['key'];
		$competition	= $instance['competition'];
		$team			= $instance['team'];
		$default_css	= filter_var($instance['default_css'], FILTER_VALIDATE_BOOLEAN);

		$html  = $before_widget;
		$html .= $before_title . $title . $after_title;

		try {
			$data = $this->_fetchData('https://api.statsfc.com/crowdscores/live.php?key=' . urlencode($key) . '&competition=' . urlencode($competition) . '&team=' . urlencode($team));

			if (empty($data)) {
				throw new Exception('There was an error connecting to the StatsFC API');
			}

			$json = json_decode($data);

			if (isset($json->error)) {
				throw new Exception($json->error);
			}

			$matches	= $json->matches;
			$customer	= $json->customer;

			if ($default_css) {
				wp_register_style(STATSFC_LIVE_ID . '-css', plugins_url('all.css', __FILE__));
				wp_enqueue_style(STATSFC_LIVE_ID . '-css');
			}

			wp_register_script(STATSFC_LIVE_ID . '-js', plugins_url('script.js', __FILE__), array('jquery'));
			wp_enqueue_script(STATSFC_LIVE_ID . '-js');

			$html .= <<< HTML
			<div class="statsfc_live">
				<div>
					<table>
						<tbody>
HTML;

			foreach ($matches as $match) {
				$homePath		= esc_attr($match->homepath);
				$awayPath		= esc_attr($match->awaypath);
				$homeBadge		= '';
				$awayBadge		= '';
				$status			= esc_attr($match->status);
				$home			= esc_attr($match->home);
				$away			= esc_attr($match->away);
				$homeScore		= esc_attr($match->score[0]);
				$awayScore		= esc_attr($match->score[1]);
				$competitionKey	= '';

				if ($default_css) {
					$homeBadge	= ' style="background-image: url(//api.statsfc.com/kit/' . esc_attr($match->homepath) . '.svg);"';
					$awayBadge	= ' style="background-image: url(//api.statsfc.com/kit/' . esc_attr($match->awaypath) . '.svg);"';
				}

				if (strlen($competition) == 0) {
					$competitionKey = '<span class="statsfc_competition"><abbr title="' . esc_attr($match->competition) . '">' . esc_attr($match->competitionkey) . '</abbr></span>';
				}

				$html .= <<< HTML
				<tr id="statsfc_{$match->id}">
					<td class="statsfc_team statsfc_home statsfc_badge statsfc_badge_{$homePath}"{$homeBadge}>
						<span class="statsfc_status">{$status}</span>
						{$home}
					</td>
					<td class="statsfc_homeScore">{$homeScore}</td>
					<td class="statsfc_vs">-</td>
					<td class="statsfc_awayScore">{$awayScore}</td>
					<td class="statsfc_team statsfc_away statsfc_badge statsfc_badge_{$awayPath}"{$awayBadge}>
						{$away}
						{$competitionKey}
					</td>
				</tr>
HTML;
			}

			$html .= <<< HTML
						</tbody>
					</table>
				</div>
HTML;

			if ($customer->advert) {
				$html .= <<< HTML
				<p class="statsfc_footer"><small>Powered by StatsFC.com. Fan data via CrowdScores.com</small></p>
HTML;
			}

			$html .= <<< HTML
			</div>
HTML;
		} catch (Exception $e) {
			$html .= '<p style="text-align: center;">StatsFC.com – ' . esc_attr($e->getMessage()) . '</p>' . PHP_EOL;
		}

		$html .= $after_widget;

		if ($this->isShortcode) {
			return $html;
		} else {
			echo $html;
		}
	}

	private function _fetchData($url) {
		if (function_exists('curl_exec')) {
			return $this->_curlRequest($url);
		} else {
			return $this->_fopenRequest($url);
		}
	}

	private function _curlRequest($url) {
		$ch = curl_init();

		curl_setopt_array($ch, array(
			CURLOPT_AUTOREFERER		=> true,
			CURLOPT_HEADER			=> false,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_TIMEOUT			=> 5,
			CURLOPT_URL				=> $url
		));

		$data = curl_exec($ch);
		if (empty($data)) {
			$data = $this->_fopenRequest($url);
		}

		curl_close($ch);

		return $data;
	}

	private function _fopenRequest($url) {
		return file_get_contents($url);
	}

	public static function shortcode($atts) {
		$args = shortcode_atts(self::$defaults, $atts);

		$widget					= new self;
		$widget->isShortcode	= true;

		return $widget->widget(array(), $args);
	}
}

// register StatsFC widget
add_action('widgets_init', create_function('', 'register_widget("' . STATSFC_LIVE_ID . '");'));
add_shortcode('statsfc-live', STATSFC_LIVE_ID . '::shortcode');
