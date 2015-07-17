<?php
/*
Plugin Name: StatsFC Live
Plugin URI: https://statsfc.com/widgets/live-games
Description: StatsFC Live
Version: 1.8
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

define('STATSFC_LIVE_ID',      'StatsFC_Live');
define('STATSFC_LIVE_NAME',    'StatsFC Live');
define('STATSFC_LIVE_VERSION', '1.8');

/**
 * Adds StatsFC widget.
 */
class StatsFC_Live extends WP_Widget
{
    public $isShortcode = false;

    protected static $count = 0;

    private static $defaults = array(
        'title'       => '',
        'key'         => '',
        'competition' => '',
        'team'        => '',
        'highlight'   => '',
        'upcoming'    => false,
        'goals'       => false,
        'show_badges' => false,
        'default_css' => true
    );

    private static $whitelist = array(
        'competition',
        'team',
        'highlight',
        'upcoming',
        'goals',
        'showBadges'
    );

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
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
    public function form($instance)
    {
        $instance    = wp_parse_args((array) $instance, self::$defaults);
        $title       = strip_tags($instance['title']);
        $key         = strip_tags($instance['key']);
        $competition = strip_tags($instance['competition']);
        $team        = strip_tags($instance['team']);
        $highlight   = strip_tags($instance['highlight']);
        $upcoming    = strip_tags($instance['upcoming']);
        $goals       = strip_tags($instance['goals']);
        $show_badges = strip_tags($instance['show_badges']);
        $default_css = strip_tags($instance['default_css']);
        ?>
        <p>
            <label>
                Title
                <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
            </label>
        </p>
        <p>
            <label>
                Key
                <input class="widefat" name="<?php echo $this->get_field_name('key'); ?>" type="text" value="<?php echo esc_attr($key); ?>">
            </label>
        </p>
        <p>
            <label>
                Competition
                <input class="widefat" name="<?php echo $this->get_field_name('team'); ?>" type="text" value="<?php echo esc_attr($team); ?>" placeholder="e.g., EPL, CHP, FAC">
            </label>
        </p>
        <p>
            <label>
                Team
                <input class="widefat" name="<?php echo $this->get_field_name('team'); ?>" type="text" value="<?php echo esc_attr($team); ?>" placeholder="e.g., Liverpool, Manchester City">
            </label>
        </p>
        <p>
            <label>
                Highlight team
                <input class="widefat" name="<?php echo $this->get_field_name('highlight'); ?>" type="text" value="<?php echo esc_attr($highlight); ?>" placeholder="e.g., Liverpool, Manchester City">
            </label>
        </p>
        <p>
            <label>
                Show upcoming fixtures?
                <input type="checkbox" name="<?php echo $this->get_field_name('upcoming'); ?>"<?php echo ($upcoming == 'on' ? ' checked' : ''); ?>>
            </label>
        </p>
        <p>
            <label>
                Show goal scorers?
                <input type="checkbox" name="<?php echo $this->get_field_name('goals'); ?>"<?php echo ($goals == 'on' ? ' checked' : ''); ?>>
            </label>
        </p>
        <p>
            <label>
                Show badges?
                <input type="checkbox" name="<?php echo $this->get_field_name('show_badges'); ?>"<?php echo ($show_badges == 'on' ? ' checked' : ''); ?>>
            </label>
        </p>
        <p>
            <label>
                Use default CSS?
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
    public function update($new_instance, $old_instance)
    {
        $instance                = $old_instance;
        $instance['title']       = strip_tags($new_instance['title']);
        $instance['key']         = strip_tags($new_instance['key']);
        $instance['competition'] = strip_tags($new_instance['competition']);
        $instance['team']        = strip_tags($new_instance['team']);
        $instance['highlight']   = strip_tags($new_instance['highlight']);
        $instance['upcoming']    = strip_tags($new_instance['upcoming']);
        $instance['goals']       = strip_tags($new_instance['goals']);
        $instance['show_badges'] = strip_tags($new_instance['show_badges']);
        $instance['default_css'] = strip_tags($new_instance['default_css']);

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
    public function widget($args, $instance)
    {
        extract($args);

        $title       = apply_filters('widget_title', $instance['title']);
        $unique_id   = ++static::$count;
        $key         = $instance['key'];
        $referer     = (array_key_exists('HTTP_REFERER', $_SERVER) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : '');
        $default_css = filter_var($instance['default_css'], FILTER_VALIDATE_BOOLEAN);

        $options = array(
            'competition' => $instance['competition'],
            'team'        => $instance['team'],
            'highlight'   => $instance['highlight'],
            'upcoming'    => (filter_var($instance['upcoming'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false'),
            'goals'       => (filter_var($instance['goals'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false'),
            'showBadges'  => (filter_var($instance['show_badges'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false'),
            'timezone'    => $instance['timezone']
        );

        $html  = $before_widget;
        $html .= $before_title . $title . $after_title;
        $html .= '<div id="statsfc-live-' . $unique_id . '"></div>' . PHP_EOL;
        $html .= $after_widget;

        // Enqueue CSS
        if ($default_css) {
            wp_register_style(STATSFC_LIVE_ID . '-css', plugins_url('all.css', __FILE__), null, STATSFC_LIVE_VERSION);
            wp_enqueue_style(STATSFC_LIVE_ID . '-css');
        }

        // Enqueue base JS
        wp_register_script(STATSFC_LIVE_ID . '-js', plugins_url('live.js', __FILE__), array('jquery'), STATSFC_LIVE_ID, true);
        wp_enqueue_script(STATSFC_LIVE_ID . '-js');

        // Enqueue widget JS
        $object = 'statsfc_live_' . $unique_id;

        $GLOBALS['statsfc_live_init']  = '<script>' . PHP_EOL;
        $GLOBALS['statsfc_live_init'] .= 'var ' . $object . ' = new StatsFC_Live(' . json_encode($key) . ');' . PHP_EOL;
        $GLOBALS['statsfc_live_init'] .= $object . '.referer = ' . json_encode($referer) . ';' . PHP_EOL;

        foreach (static::$whitelist as $parameter) {
            if (! array_key_exists($parameter, $options)) {
                continue;
            }

            $GLOBALS['statsfc_live_init'] .= $object . '.' . $parameter . ' = ' . json_encode($options[$parameter]) . ';' . PHP_EOL;
        }

        $GLOBALS['statsfc_live_init'] .= $object . '.display("statsfc-live-' . $unique_id . '");' . PHP_EOL;
        $GLOBALS['statsfc_live_init'] .= '</script>';

        add_action('wp_print_footer_scripts', function()
        {
            global $statsfc_live_init;

            echo $statsfc_live_init;
        });

        if ($this->isShortcode) {
            return $html;
        } else {
            echo $html;
        }
    }

    public static function shortcode($atts)
    {
        $args = shortcode_atts(self::$defaults, $atts);

        $widget              = new self;
        $widget->isShortcode = true;

        return $widget->widget(array(), $args);
    }
}

// Register StatsFC widget
add_action('widgets_init', function()
{
    register_widget(STATSFC_LIVE_ID);
});

add_shortcode('statsfc-live', STATSFC_LIVE_ID . '::shortcode');
