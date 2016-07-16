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
	<form method='post' action='options.php'>
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
				<label for="radius_lat">Google Places search centre - latitude</label>
				<input type='text' name='radius_lat' id='radius_lat' value='<?php echo esc_attr( get_option('radius_lat') ); ?>' />
			</div>
			<div>
				<label for="radius_lng">Google Places search centre - longitude</label>
				<input type='text' name='radius_lng' id='radius_lng' value='<?php echo esc_attr( get_option('radius_lng') ); ?>' />
			</div>			
			<div>
				<label for="radius">Google Places search radius</label>
				<input type='text' name='radius' id='radius' value='<?php echo esc_attr( get_option('radius') ); ?>' />
			</div>									


		</div>
		<?php submit_button( 'Save settings', 'primary', 'save', false ); ?>
	</form>
	<form action="" method="post">
		<?php submit_button( 'Import Facebook events now', 'secondary', 'import', false ); ?>
	</form>

	<h2>Facebook Connector</h2>
	<div id="status"></div>
	<fb:login-button scope="public_profile,email,user_events" onlogin="checkLoginState();"></fb:login-button>


</div>