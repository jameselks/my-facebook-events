# Elks Events
Event manager for Facebook events in Wordpress.

Allows you to:
- Download events that you've RSVP'd to from Facebook (including image)
- Geocode events without a location
- Display todays events on a map using a shortcode
- Display a list of events using a shortcode


## Details

Contributors: James Elks (jamesee)

Requires at least: 4.4

Tested up to: 4.5.3

License: GPLv2 or later

License URI: http://www.gnu.org/licenses/gpl-2.0.html


## Installation

1. Download the [master branch (.zip)](https://github.com/jameselks/elks-events/archive/master.zip) from GitHub.
1. Upload `elks-events` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

## Configuration

... coming ...

## Usage

Two shortcodes:

`[e2_map_today]` to display a map of todays events.

`[e2_list]` to display a list of events for the next time specified in settings. To limit the number of days to fetch events for, use `get_days` variable. For example `[e2_list get_days="7"]` would get todays events and then the next 7 days of events. `[e2_list get_days="0"]` would show only events on today.


To stop an imported event updating when it's Facebook source event is updated:

Add a custom field with name `e2_stop_update` and value `true`. **Note:** if the custom field exists (with any value) the post will not be updated. So if you want the post to be updated again you'll need to delete the custom field from the post.

## Changelog

= 1.0.0 =
* Initial release. Functional.
