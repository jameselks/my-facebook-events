<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://jameselks.com
 * @since      1.0.0
 *
 * @package    Elks_Events
 * @subpackage Elks_Events/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Elks_Events
 * @subpackage Elks_Events/public
 * @author     James Elks <findme@jameselks.com>
 */
class Elks_Events_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function e2_register_styles() {

		wp_register_style( $this->plugin_name . '-css', plugin_dir_url( __FILE__ ) . 'css/elks-events-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function e2_register_scripts() {

		wp_register_script( $this->plugin_name . '-js', plugin_dir_url( __FILE__ ) . 'js/elks-events-public.js', array( 'jquery' ), $this->version, false );
		wp_register_script( 'google-maps-api', 'https://maps.googleapis.com/maps/api/js?key='.get_option('api_key_gm').'&callback=initMap', array($this->plugin_name . '-js'), $this->version, true );
		wp_register_script( 'geolocation-marker', plugin_dir_url( __FILE__ ) . 'libs/geolocation-marker.js', array(), $this->version, true );

	}

	/**
	 * Shortcode for today's events.
	 *
	 * @since    1.0.0
	 */
	public function e2_map_today() {
		// Get the Google Maps script on board
		wp_enqueue_style( $this->plugin_name . '-css' );
		wp_enqueue_script( 'google-maps-api' );
		wp_enqueue_script( 'geolocation-marker' );
		wp_localize_script( $this->plugin_name . '-js', 'e2js', array( 'uploadsUrl' => wp_upload_dir()['baseurl'], 'pluginUrl' => plugins_url('', __FILE__) ));

		// Write the map div. Javascript takes care of the rest.
		return "<div id='map'></div>";
	}

	/**
	 * Creat the custom post type.
	 *
	 * @since    1.0.0
	 */
	public function e2_create_post_type() {
		register_post_type( 
			'events',
			array(
				'labels' => array(
					'name' 			=> __( 'Events' ),
					'singular_name'	=> __( 'Event' )
					),
				'public' 	=> false,
				'show_ui'	=> true,
				'supports' 	=> array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ),
				)
			);
	}

}
