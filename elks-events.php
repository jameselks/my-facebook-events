<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://jameselks.com
 * @since             1.0.0
 * @package           Elks_Events
 *
 * @wordpress-plugin
 * Plugin Name:       My Facebook events
 * Plugin URI:        http://jameselks.com/elks-events
 * Description:       Personal event manager for Facebook events in Wordpress.
 * Version:           1.0.0
 * Author:            James Elks
 * Author URI:        http://jameselks.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       elks-events
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/jameselks/my-facebook-events
 * GitHub Branch:     master 
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-elks-events-activator.php
 */
function activate_elks_events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-elks-events-activator.php';
	Elks_Events_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-elks-events-deactivator.php
 */
function deactivate_elks_events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-elks-events-deactivator.php';
	Elks_Events_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_elks_events' );
register_deactivation_hook( __FILE__, 'deactivate_elks_events' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-elks-events.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_elks_events() {

	$plugin = new Elks_Events();
	$plugin->run();

}
run_elks_events();
