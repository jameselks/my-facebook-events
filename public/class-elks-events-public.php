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
		wp_register_style( $this->plugin_name . '-boostrap-grid-css', plugin_dir_url( __FILE__ ) . 'css/elks-events-bootstrap-grid.css', array(), $this->version, 'all' );

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
	 * Shortcode for list of events in next 30 days.
	 *
	 * @since    1.1.0
	 */
	public function e2_list( $atts ) {

		//Sort out values that have been passed in
		$atts = shortcode_atts( array(
			'get_days' => intval(get_option('events_get_days')),
			), $atts );
		$atts['get_days'] = $atts['get_days'] - 1;
		if ( $atts['get_days'] < 0 ) {
			$atts['get_days'] = intval(get_option('events_get_days'));
		}

		//Get the generic E2 CSS and JS onboard
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( $this->plugin_name . '-css' );
		wp_enqueue_style( $this->plugin_name . '-boostrap-grid-css' );
		wp_enqueue_script( $this->plugin_name . '-js' );

		$output = "";

		// The Query
		$args = array (
			'post_type' 		=> 'e2_events',
			'posts_per_page'	=> -1,
			'orderby' 			=> 'meta_value',
			'order' 			=> 'ASC',
			'meta_key'			=> 'e2_start',
			'meta_query'		=> array(
					'key'				=> 'e2_start',
					'value'				=> array( current_time('Y-m-d'), date('Y-m-d', strtotime(current_time('Y-m-d') . '+' . intval($atts['get_days']) . ' days')) ),
					'compare'			=> 'BETWEEN',
					'type'				=> 'DATETIME'
			)
		);
		$the_query = new WP_Query( $args );
		
		// The Loop
		if ( $the_query->have_posts() ) {
			$output = $output . '<div id="e2-events">';
			$previous_date = new DateTime( date('Y-m-d', strtotime(current_time('Y-m-d') . "-1 days")) );
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$the_id = get_the_id();
				$the_start = new DateTime(get_post_meta( $the_id, 'e2_start', true ));
				$is_first = false;
				if ($the_start->format('Y-m-d') != $previous_date->format('Y-m-d')) {
					$is_first = true;
					$start_formatted = $the_start->format('Y-m-d');
					if ($start_formatted == current_time('Y-m-d')) {
						$output = $output . '<h2>Today</h2>';
						$output = $output . '<div id="today-map"><p>Want to know where today\'s exhibitions are?</p><a href="/today/" class="btn btn-secondary">Map of today\'s exhibitions</a></div>';
					} elseif ( $start_formatted == date('Y-m-d', strtotime(current_time('Y-m-d') . "+1 days")) ) {
						$output = $output . '<h2>Tomorrow</h2>';
					} else {
						$output = $output . '<h2>' . $the_start->format('l j M') . '</h2>';
					}
				}
				$e2_source_url = get_post_meta( $the_id, 'e2_source_url', true );
				$e2_location = get_post_meta( $the_id, 'e2_location', true );
				if (!empty($e2_location)) {
					$e2_location = ' | ' . $e2_location;
				}
				$previous_date = $the_start;
				if ($is_first) {
					$output = $output . '<div class="event container-fluid first">';
				} else {
					$output = $output . '<div class="event container-fluid">';
				}
				$output = $output . '	<div class="row">';
				$output = $output . '		<div class="col-sm-4 event-image-container">';
				$output = $output . 			get_the_post_thumbnail( $the_id, array(400, 200) );
				$output = $output . '		</div>';
				$output = $output . '		<div class="col-sm-8 event-details">';				
				$output = $output . '			<h3 class="event-name">' . get_the_title() . '</h3>';
				$output = $output . '			<p class="event_start">' . $the_start->format('g:ia') . $e2_location . '</p>';
				$output = $output . '			<div class="event-more accordion">';
				$output = $output . '				<h4 class="accordion-toggle accordion-closed">More details</h4>';
				$output = $output . '				<div class="event-description accordion-content" style="display:none;">';
				$output = $output . '					<p class="event-description">' . str_replace(PHP_EOL, '<br />', get_the_content()) . '</p>';
				if ($e2_source_url) {
					$output = $output . '				<p class="event-link">';
					$output = $output . '					<a href="' . $e2_source_url . '" target="_blank">Source</a>';
					$output = $output . '				</p>';
				}
				$output = $output . '				</div>';
				$output = $output . '			</div>';				
				$output = $output . '		</div>';
				$output = $output . '	</div>';
				$output = $output . '</div>';
			}
			$output = $output . '</div>';
			
			/* Restore original Post Data */
			wp_reset_postdata();
		} else {
			$output = $output . '<p>No posts found.</p>';
		}

		return $output;

	}

	/**
	 * Shortcode for map of today's events.
	 *
	 * @since    1.0.0
	 */
	public function e2_map_today() {
		
		//Get the generic E2 CSS and JS onboard
		wp_enqueue_style( $this->plugin_name . '-css' );
		wp_localize_script( $this->plugin_name . '-js', 'e2js', array( 'uploadsUrl' => set_url_scheme(wp_upload_dir()['baseurl']), 'pluginUrl' => plugins_url('', __FILE__) ));

		// Get the Google Maps script on board
		wp_enqueue_script( 'google-maps-api' );
		wp_enqueue_script( 'geolocation-marker' );


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
			'e2_events',
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

	/**
	 * Shortcode for list of events to be output specifically for the MailChimp RSS email feed.
	 * This REALLY needs to be extracted and put somewhere else at some point, like some kind of 'custom' output.
	 *
	 * @since    1.1.0
	 */
	public function e2_list_email( $atts ) {

		//Sort out values that have been passed in
		$atts = shortcode_atts( array(
			'get_days' => intval(get_option('events_get_days')),
			), $atts );
		$atts['get_days'] = $atts['get_days'] - 1;
		if ( $atts['get_days'] < 0 ) {
			$atts['get_days'] = intval(get_option('events_get_days'));
		}

		$output = "";

		// The Query
		$args = array (
			'post_type' 		=> 'e2_events',
			'posts_per_page'	=> -1,
			'orderby' 			=> 'meta_value',
			'order' 			=> 'ASC',
			'meta_key'			=> 'e2_start',
			'meta_query'		=> array(
					'key'				=> 'e2_start',
					'value'				=> array( current_time('Y-m-d'), date('Y-m-d', strtotime(current_time('Y-m-d') . '+' . intval($atts['get_days']) . ' days')) ),
					'compare'			=> 'BETWEEN',
					'type'				=> 'DATETIME'
			)
		);
		$the_query = new WP_Query( $args );

		// The Loop
		if ( $the_query->have_posts() ) {
			$previous_date = new DateTime( date('Y-m-d', strtotime(current_time('Y-m-d') . "-1 days")) );
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$the_id = get_the_id();
				$the_start = new DateTime(get_post_meta( $the_id, 'e2_start', true ));
				$is_first = false;
				if ($the_start->format('Y-m-d') != $previous_date->format('Y-m-d')) {
					$is_first = true;
					$start_formatted = $the_start->format('Y-m-d');
					$output = $output . '<div><br><h2 style="font-size:18px !important;line-height:125% !important;">';
					if ($start_formatted == current_time('Y-m-d')) {
						//IF Today
						$output = $output . 'Today';
					} elseif ( $start_formatted == date('Y-m-d', strtotime(current_time('Y-m-d') . "+1 days")) ) {
						//IF Tomorrow
						$output = $output . 'Tomorrow';
					} else {
						//Otherwise...
						$output = $output . $the_start->format('l j M');
					}
					$output = $output . '</h2></div>';

				}

				$e2_location = get_post_meta( $the_id, 'e2_location', true );
				if (!empty($e2_location)) {
					$e2_location = ' | ' . $e2_location;
				}
				
				$previous_date = $the_start;
				$output = $output . '<div style="margin-top:5px; padding-top:5px; font-size: 14px !important; line-height: 125% !important; color: #404040 !important;">';
				$output = $output . '	<a class="url" href="https://www.facebook.com/events/319480168399057/" target="_blank" style="word-wrap:break-word;-ms-text-size-adjust: 100%;-webkit-text-size-adjust:100%;color:#be1522;font-weight:bold;text-decoration:none;">' . get_the_title() . '</a>';
				$output = $output . '	<br><span>'. $the_start->format('g:ia') . $e2_location . '</span>';
				$output = $output . '</div>';
			}
			
			/* Restore original Post Data */
			wp_reset_postdata();
		} else {
			$output = $output . '<p>Nothing found :(</p>';
		}

		return $output;

	}

}