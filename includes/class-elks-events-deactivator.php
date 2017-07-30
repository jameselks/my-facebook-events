<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://jameselks.com
 * @since      1.0.0
 *
 * @package    Elks_Events
 * @subpackage Elks_Events/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Elks_Events
 * @subpackage Elks_Events/includes
 * @author     James Elks <findme@jameselks.com>
 */
class Elks_Events_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		
		$timestamp = wp_next_scheduled( 'e2_cron_process_events' );
		wp_unschedule_event($timestamp, 'e2_cron_process_events' );

		$timestamp = wp_next_scheduled( 'e2_cron_hourly' );
		wp_unschedule_event($timestamp, 'e2_cron_hourly' );

		$timestamp = wp_next_scheduled( 'e2_cron_daily' );
		wp_unschedule_event($timestamp, 'e2_cron_daily' );

	}

}
