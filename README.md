# Elks Events
Event manager for Facebook events in Wordpress.

Allows you to:
- Download events that you've RSVP'd to from Facebook (including image)
- Geocode events without a location
- Display the events on a map
- Display a list of events


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

`[e2_list]` to display a list of events for the next time specified in settings.

To stop an imported event updating when it's Facebook source event is updated:

Add a custom field with name `e2_stop_update` and value `true`.

## Changelog

= 1.0.0 =
* Initial release. Functional.
