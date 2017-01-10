<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://jameselks.com
 * @since      1.0.0
 *
 * @package    Elks_Events
 * @subpackage Elks_Events/admin/partials
 */


if (isset($_POST['import'])) {
	do_action('e2_cron_process_events', true);
}

?>


<div id='e2' class='wrap'>

	<h1>Elks Events Settings</h1>
	<h2>Settings</h2>
	<form method='post' action='options.php' id="e2-settings">
		<?php settings_fields( 'e2-group' ); ?>
		<?php do_settings_sections( 'e2-group' ); ?>
		<div class="form-table">
			<div>
				<label for="fb_app_id">Facebook App ID</label>
				<input type='text' name='fb_app_id' id='fb_app_id' value='<?php echo esc_attr( get_option('fb_app_id') ); ?>' />
			</div>
			<div>
				<label for="fb_app_secret">Facebook App Secret</label>
				<input type='text' name='fb_app_secret' id='fb_app_secret' value='<?php echo esc_attr( get_option('fb_app_secret') ); ?>' />
			</div>			
			<div>
				<label for="fb_longtoken">Facebook long-lived token</label>
				<span>Don't edit this field.</span>
				<input type='text' name='fb_longtoken' id='fb_longtoken' value='<?php echo esc_attr( get_option('fb_longtoken') ); ?>' />
			</div>
			<div>
				<label for="api_key_gm">Google Maps API Key</label>
				<input type='text' name='api_key_gm' id='api_key_gm' value='<?php echo esc_attr( get_option('api_key_gm') ); ?>' />
			</div>
			<div>
				<label for="api_key_gp">Google Places API Key</label>
				<input type='text' name='api_key_gp' id='api_key_gp' value='<?php echo esc_attr( get_option('api_key_gp') ); ?>' />
			</div>
			<div>
				<label for="radius_lat">Google Places search centre &mdash; latitude</label>
				<input type='text' name='radius_lat' id='radius_lat' value='<?php echo esc_attr( get_option('radius_lat') ); ?>' />
			</div>
			<div>
				<label for="radius_lng">Google Places search centre &mdash; longitude</label>
				<input type='text' name='radius_lng' id='radius_lng' value='<?php echo esc_attr( get_option('radius_lng') ); ?>' />
			</div>			
			<div>
				<label for="radius">Google Places search radius</label>
				<input type='text' name='radius' id='radius' value='<?php echo esc_attr( get_option('radius') ); ?>' />
			</div>									
			<div>
				<label for="events_get_days">Number of days into the future to display</label>
				<span>Default value. Can be overridden in the shortcode.</span>
				<input type='text' name='events_get_days' id='events_get_days' value='<?php echo esc_attr( get_option('events_get_days') ); ?>' />
			</div>
			<div>
				<label for="fb_get_events">Number of Facebook events to request</label>
				<span>More events leads to a slower request time. Less than 200 recommended. Use the <a href="https://developers.facebook.com/tools/explorer/">Facebook Graph API Explorer</a> to check response time.
				<input type='text' name='fb_get_events' id='fb_get_events' value='<?php echo esc_attr( get_option('fb_get_events') ); ?>' />
			</div>

		</div>
		<?php submit_button( 'Save settings', 'primary', 'save', false ); ?>
	</form>
	<form action="" method="post" id="e2-import">
		<?php submit_button( 'Import Facebook events now', 'secondary', 'import', false ); ?>
	</form>

	<h2>Facebook Connector</h2>
	<div id="status"></div>
	<fb:login-button scope="public_profile,email,user_events" onlogin="checkLoginState();"></fb:login-button>


</div>