<?php

/**
 * Fired during plugin activation
 *
 * @link       http://jameselks.com
 * @since      1.0.0
 *
 * @package    Elks_Events
 * @subpackage Elks_Events/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Elks_Events
 * @subpackage Elks_Events/includes
 * @author     James Elks <findme@jameselks.com>
 */
class Elks_Events_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		$timestamp = wp_next_scheduled( 'e2_cron_process_events' );
		if( $timestamp == false ){
			wp_schedule_event( time(), 'hourly', 'e2_cron_process_events' );
		}

	}

}
