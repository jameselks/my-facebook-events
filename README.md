# My Facebook Events
Import Facebook events from an individual Facebook account to Wordpress posts. Then display them in a list or a map.

Allows you to:
- Download events that you've RSVP'd to from Facebook (including image)
- Automatically update if the Facebook event changes
- Add your own events
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

1. Download the [master branch (.zip)](https://github.com/jameselks/my-facebook-events/archive/master.zip) from GitHub.
1. Upload `elks-events` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

## Configuration

### Create a Facebook app

* Log in to Facebook
* Visit [Facebook for developers](https://developers.facebook.com/)
* Select My `Apps` > `Add a new app`
* Go to `Dashboard`
* Copy your app ID and app secret into the Facebook Events settings page.
* Go to `Settings`
* Add your domain to `App Domains` (e.g. test.com)
* Select `Add platform` (at the bottom)
* Select `Website` and add your domain (e.g. test.com)

### Create Google API keys

* Create a [Google Maps Javascript API key](https://developers.google.com/maps/documentation/javascript/get-api-key)
* Copy the API key to the Facebook Events settings page.

* Create a [Google Places PI key](https://developers.google.com/places/web-service/get-api-key#get_an_api_key)
* Copy the API key to the Facebook Events settings page.

### Configure plugin settings

Using the instructions above, you should now have your Google API keys, Facebook app ID and Facebook app secret in the plugin settings.

Configure the remaining fields using the instructions on the settings page.

## Usage

Two shortcodes:

`[e2_map_today]` to display a map of todays events.

`[e2_list]` to display a list of events for the next time specified in settings. To limit the number of days to fetch events for, use `get_days` variable. For example `[e2_list get_days="7"]` would get todays events and then the next 6 days of events. `[e2_list get_days="1"]` would show only events on today.

To stop an imported event updating when it's Facebook source event is updated:

Add a custom field with name `e2_stop_update` and value `true`. **Note:** if the custom field exists (with any value) the post will not be updated. So if you want the post to be updated again you'll need to delete the custom field from the post.

## Changelog

=1.2.0=
* Enable sorting of events by start date and location in admin
* Email administrator on Facebook import errors
* Check Facebook access token expiry and email administrator 10 days before
* Improve logging

= 1.1.0 =
* Renamed to 'My Facebook Events'
* Simplified manually adding a new custom event (not imported)

= 1.0.0 =
* Initial release. Functional.
