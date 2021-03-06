=== StatsFC Live ===
Contributors: willjw
Donate link:
Tags: widget, football, soccer, fixtures, scores, live, premier league, fa cup, league cup
Requires at least: 3.3
Tested up to: 4.2.2
Stable tag: 1.8.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This widget will display live football scores on your website, for a chosen competition or team.

== Description ==

Add live football scores to your WordPress website. To request a key sign up for your free trial at [statsfc.com](https://statsfc.com/sign-up).

For a demo, check out [wp.statsfc.com/live/](http://wp.statsfc.com/live/).

== Installation ==

1. Upload the `statsfc-live` folder and all files to the `/wp-content/plugins/` directory
2. Activate the widget through the 'Plugins' menu in WordPress
3. Drag the widget to the relevant sidebar on the 'Widgets' page in WordPress
4. Set the StatsFC key and any other options. If you don't have a key, sign up for free at [statsfc.com](https://statsfc.com)

You can also use the `[statsfc-live]` shortcode, with the following options:

- `key` (required): Your StatsFC key
- `competition` (required*): Competition key, e.g., `EPL`
- `team` (required*): Team name, e.g., `Liverpool`
- `highlight` (optional): The name of the team you want to highlight, e.g., `Liverpool`
- `upcoming` (optional): Show fixtures scheduled to start in the next hour, `true` or `false`
- `goals` (optional): Show goal scorers, `true` or `false`
- `show_badges` (optional): Display team badges, `true` or `false`
- `default_css` (optional): Use the default widget styles, `true` or `false`

*Only one of `competition` or `team` is required.

== Frequently asked questions ==



== Screenshots ==



== Changelog ==

**1.0.1**: Fixed bug: live scores now showing.

**1.1**: Separate control over whether to show goals, red cards and yellow cards.

**1.1.1**: Fixed a bug when selecting a specific team.

**1.2**: Added Community Shield live scores.

**1.2.1**: Use cURL to fetch API data if possible.

**1.2.2**: Fixed possible cURL bug.

**1.2.3**: Added fopen fallback if cURL request fails.

**1.2.4**: Added missing "Missed penalty" icon.

**1.3**: Added an option to show upcoming fixtures, starting within the next hour.

**1.4**: Live match scores and status are automatically updated.

**1.4.1**: Tweaked error message.

**1.5**: Updated to use the new API.

**1.5.3**: Tweaked CSS.

**1.6**: Added `[statsfc-live]` shortcode.

**1.6.2**: Updated team badges.

**1.6.3**: Default `default_css` parameter to `true`

**1.6.4**: Added badge class for each team

**1.6.5**: Use built-in WordPress HTTP API functions

**1.7**: Enabled ad-support

**1.7.1**: Allow more discrete ads for ad-supported accounts

**1.8**: Added `highlight`, `upcoming`, `goals` and `show_badges` options

**1.8.1**: Fixed bug with multiple widgets on one page

**1.8.2**: Fixed bug with boolean options

**1.8.3**: Authenticate requests for the automatic live score updates

**1.8.4**: Fixed bug saving `competition` setting

**1.8.5**: Fixed "Invalid domain" bug caused by referal domain

== Upgrade notice ==

