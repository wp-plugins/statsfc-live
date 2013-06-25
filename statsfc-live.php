<?php
/*
Plugin Name: StatsFC Live
Plugin URI: https://statsfc.com/docs/wordpress
Description: StatsFC Live
Version: 1.2
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
	private static $_competitions = array(
		'premier-league'	=> 'Premier League',
		'fa-cup'			=> 'FA Cup',
		'league-cup'		=> 'League Cup',
		'community-shield'	=> 'Community Shield'
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
		$defaults = array(
			'title'				=> __('Live Scores', STATSFC_LIVE_ID),
			'api_key'			=> __('', STATSFC_LIVE_ID),
			'competition'		=> __(current(array_keys(self::$_competitions)), STATSFC_LIVE_ID),
			'team'				=> __('', STATSFC_LIVE_ID),
			'goals'				=> __('', STATSFC_LIVE_ID),
			'reds'				=> __('', STATSFC_LIVE_ID),
			'yellows'			=> __('', STATSFC_LIVE_ID),
			'default_css'		=> __('', STATSFC_LIVE_ID)
		);

		$instance		= wp_parse_args((array) $instance, $defaults);
		$title			= strip_tags($instance['title']);
		$api_key		= strip_tags($instance['api_key']);
		$competition	= strip_tags($instance['competition']);
		$team			= strip_tags($instance['team']);
		$goals			= strip_tags($instance['goals']);
		$reds			= strip_tags($instance['reds']);
		$yellows		= strip_tags($instance['yellows']);
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
				<?php _e('API key', STATSFC_LIVE_ID); ?>:
				<input class="widefat" name="<?php echo $this->get_field_name('api_key'); ?>" type="text" value="<?php echo esc_attr($api_key); ?>">
			</label>
		</p>
		<p>
			<label>
				<?php _e('Competition', STATSFC_LIVE_ID); ?>:
				<select name="<?php echo $this->get_field_name('competition'); ?>">
					<?php
					foreach (self::$_competitions as $id => $name) {
						echo '<option value="' . esc_attr($id) . '"' . ($id == $competition ? ' selected' : '') . '>' . esc_attr($name) . '</option>' . PHP_EOL;
					}
					?>
				</select>
			</label>
		</p>
		<p>
			<label>
				<?php _e('Team', STATSFC_LIVE_ID); ?>:
				<?php
				$data = file_get_contents('https://api.statsfc.com/premier-league/teams.json?key=' . (! empty($api_key) ? $api_key : 'free'));

				try {
					if (empty($data)) {
						throw new Exception('There was an error connecting to the StatsFC API');
					}

					$json = json_decode($data);
					if (isset($json->error)) {
						throw new Exception($json->error);
					}
					?>
					<select class="widefat" name="<?php echo $this->get_field_name('team'); ?>">
						<option></option>
						<?php
						foreach ($json as $row) {
							echo '<option value="' . esc_attr($row->path) . '"' . ($row->path == $team ? ' selected' : '') . '>' . esc_attr($row->name) . '</option>' . PHP_EOL;
						}
						?>
					</select>
				<?php
				} catch (Exception $e) {
				?>
					<input class="widefat" name="<?php echo $this->get_field_name('team'); ?>" type="text" value="<?php echo esc_attr($team); ?>">
				<?php
				}
				?>
			</label>
		</p>
		<p>
			<label>
				<?php _e('Show goals?', STATSFC_LIVE_ID); ?>
				<input type="checkbox" name="<?php echo $this->get_field_name('goals'); ?>"<?php echo ($goals == 'on' ? ' checked' : ''); ?>>
			</label>
		</p>
		<p>
			<label>
				<?php _e('Show red cards?', STATSFC_LIVE_ID); ?>
				<input type="checkbox" name="<?php echo $this->get_field_name('reds'); ?>"<?php echo ($reds == 'on' ? ' checked' : ''); ?>>
			</label>
		</p>
		<p>
			<label>
				<?php _e('Show yellow cards?', STATSFC_LIVE_ID); ?>
				<input type="checkbox" name="<?php echo $this->get_field_name('yellows'); ?>"<?php echo ($yellows == 'on' ? ' checked' : ''); ?>>
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
		$instance['api_key']		= strip_tags($new_instance['api_key']);
		$instance['competition']	= strip_tags($new_instance['competition']);
		$instance['team']			= strip_tags($new_instance['team']);
		$instance['goals']			= strip_tags($new_instance['goals']);
		$instance['reds']			= strip_tags($new_instance['reds']);
		$instance['yellows']		= strip_tags($new_instance['yellows']);
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
		$api_key		= $instance['api_key'];
		$competition	= $instance['competition'];
		$team			= $instance['team'];
		$goals			= $instance['goals'];
		$reds			= $instance['reds'];
		$yellows		= $instance['yellows'];
		$default_css	= $instance['default_css'];

		echo $before_widget;
		echo $before_title . $title . $after_title;

		$data = file_get_contents('https://api.statsfc.com/' . esc_attr($competition) . '/live.json?key=' . $api_key . (! empty($team) ? '&team=' . esc_attr($team) : ''));

		try {
			if (empty($data)) {
				throw new Exception('There was an error connecting to the StatsFC API');
			}

			$json = json_decode($data);
			if (isset($json->error)) {
				throw new Exception($json->error);
			}

			if (count($json) == 0) {
				throw new Exception('There are no live matches at the moment');
			}

			if ($default_css) {
				wp_register_style(STATSFC_LIVE_ID . '-css', plugins_url('all.css', __FILE__));
				wp_enqueue_style(STATSFC_LIVE_ID . '-css');
			}
			?>
			<div class="statsfc_live">
				<table>
					<tbody>
						<?php
						foreach ($json as $match) {
						?>
							<tr>
								<td class="statsfc_home<?php echo ($team == $match->home ? ' statsfc_highlight' : ''); ?>">
									<span class="statsfc_status"><?php echo esc_attr($match->statusshort); ?></span>
									<?php echo esc_attr($match->homeshort); ?>
								</td>
								<td class="statsfc_homeScore"><?php echo esc_attr($match->runningscore[0]); ?></td>
								<td class="statsfc_vs">-</td>
								<td class="statsfc_awayScore"><?php echo esc_attr($match->runningscore[1]); ?></td>
								<td class="statsfc_away<?php echo ($team == $match->away ? ' statsfc_highlight' : ''); ?>"><?php echo esc_attr($match->awayshort); ?></td>
							</tr>
							<?php
							if (($goals || $reds || $yellows) && count($match->incidents) > 0) {
								foreach ($match->incidents as $incident) {
									if (! $goals && ($incident->type == 'Goal' || $incident->type == 'Own Goal')) {
										continue;
									}

									if (! $reds && ($incident->type == 'Red' || $incident->type == '2nd Yellow')) {
										continue;
									}

									if (! $yellows && $incident->type == 'Yellow') {
										continue;
									}

									$homeClass	= '';
									$homePlayer	= '';
									$awayClass	= '';
									$awayPlayer	= '';
									$class		= str_replace(' ', '', strtolower($incident->type));

									if ($incident->team_id == $match->home_id) {
										$homeClass	= ' statsfc_' . esc_attr($class);
										$homePlayer	= esc_attr($incident->playershort);
									} elseif ($incident->team_id == $match->away_id) {
										$awayClass	= ' statsfc_' . esc_attr($class);
										$awayPlayer	= esc_attr($incident->playershort);
									}
									?>
									<tr class="statsfc_incident">
										<td class="statsfc_home<?php echo $homeClass; ?>" colspan="2"><?php echo $homePlayer; ?></td>
										<td class="statsfc_vs"><?php echo esc_attr($incident->minute); ?>'</td>
										<td class="statsfc_away<?php echo $awayClass; ?>" colspan="2"><?php echo $awayPlayer; ?></td>
									</tr>
								<?php
								}
							}
						}
						?>
					</tbody>
				</table>

				<p class="statsfc_footer"><small>Powered by StatsFC.com</small></p>
			</div>
		<?php
		} catch (Exception $e) {
			echo '<p class="statsfc_error">' . esc_attr($e->getMessage()) .'</p>' . PHP_EOL;
		}

		echo $after_widget;
	}
}

// register StatsFC widget
add_action('widgets_init', create_function('', 'register_widget("' . STATSFC_LIVE_ID . '");'));
?>