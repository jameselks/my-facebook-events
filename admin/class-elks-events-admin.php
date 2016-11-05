<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://jameselks.com
 * @since      1.0.0
 *
 * @package    Elks_Events
 * @subpackage Elks_Events/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Elks_Events
 * @subpackage Elks_Events/admin
 * @author     James Elks <findme@jameselks.com>
 */
class Elks_Events_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function e2_admin() {
	/*█████████████████████████████████████████████████████
	 * Create the admin settings.
	 *
	 * @since    1.0.0
	 */

	add_menu_page('Elks Events &mdash; Settings', 'Elks Events', 'manage_options', $this->plugin_name, array($this, 'e2_admin_page'), 'dashicons-admin-generic');

	}

	public function e2_admin_page() {
	/*█████████████████████████████████████████████████████
	 * Display the admin settings page.
	 *
	 * @since    1.0.0
	 */

	echo require "partials/elks-events-admin-display.php"; 

	}

	public function e2_admin_settings() {
	/*█████████████████████████████████████████████████████
	 * Register the admin settings.
	 *
	 * @since    1.0.0
	 */		
		register_setting( 'e2-group', 'fb_longtoken' );
		register_setting( 'e2-group', 'fb_app_id' );
		register_setting( 'e2-group', 'fb_app_secret' );
		register_setting( 'e2-group', 'api_key_gp' );
		register_setting( 'e2-group', 'api_key_gm' );
		register_setting( 'e2-group', 'radius_lat' );
		register_setting( 'e2-group', 'radius_lng' );
		register_setting( 'e2-group', 'radius' );
		register_setting( 'e2-group', 'events_get_days' );
		register_setting( 'e2-group', 'fb_get_events' );
	}

	public function enqueue_styles() {
	/*█████████████████████████████████████████████████████
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/elks-events-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
	/*█████████████████████████████████████████████████████
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/elks-events-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'e2js', array( 'fbAppId' => get_option('fb_app_id') ) );
	}

	public function e2_process_events($echo_results) {
	/*█████████████████████████████████████████████████████
	 * Download events and process JSON all at once.
	 * $echo_results - true - if is being called and user is expecting echo'd outputs.
	 *
	 * @since    1.0.0
	 */

		do_action('e2_import_events', $echo_results);
		do_action('e2_generate_map_json');

	}

	public function e2_fb_tokenexchange() {
	/*█████████████████████████████████████████████████████
	 * Exchange a short-lived Facebook authentication for a long-lived token.
	 *
	 * @since    1.0.0
	 */
		$fb = new Facebook\Facebook([
			'app_id' => get_option('fb_app_id'),
			'app_secret' => get_option('fb_app_secret'),
			'default_graph_version' => 'v2.6',
		]);

		$helper = $fb->getJavaScriptHelper();

		try {
		  $accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  // When Graph returns an error
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  // When validation fails or other local issues
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}

		if (! isset($accessToken)) {
		  echo 'No cookie set or no OAuth data could be obtained from cookie.';
		  exit;
		}

		// OAuth 2.0 client handler
		$oAuth2Client = $fb->getOAuth2Client();
		 
		// Exchanges a short-lived access token for a long-lived one
		$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);

		update_option( 'fb_longtoken', $accessToken );

		echo $accessToken;
		$_SESSION['fb_access_token'] = (string) $accessToken;

		// User is logged in!

	    wp_die();
	}

	public function e2_geocode_place($place, $radius, $radius_center, $api_key) {
	/*█████████████████████████████████████████████████████
	 * Geocode a place using Google Places API.
	 *
	 * $place to be geocoded.
	 * $radius in metres to search from the $radius_center (lat, lng key/value array).
	 * $api_key for Google Places.
	 *
	 * @since    1.0.0
	 */
		
		$url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json'.
		'?name='.urlencode($place).
		'&location='.$radius_center['lat'].','.$radius_center['lng'].
		'&radius='.$radius.
		'&key='.$api_key;

		$results = get_object_vars(json_decode(file_get_contents($url)));
		$lat = $results['results'][0]->geometry->location->lat;
		$lng = $results['results'][0]->geometry->location->lng;

		if (($lat === NULL) or ($lng === NULL)) {
			$results = NULL;
		} else {
			$latlng = array(
				'lat' => $lat,
				'lng' => $lng
				);
			$results = $latlng;
		}

		return $results;
	}


	public function e2_insert_image($post_id, $cover_url) {
	/*█████████████████████████████████████████████████████
	 * Download and add image as featured image to post.
	 *
	 * @since    1.0.0
	 */
		//echo 'Downloading image from Facebook<br />';
		//flush();

		include_once(ABSPATH . "wp-includes/pluggable.php");
		include_once(ABSPATH . "wp-admin/includes/media.php");
		include_once(ABSPATH . "wp-admin/includes/file.php");
		include_once(ABSPATH . "wp-admin/includes/image.php");

		$media = media_sideload_image($cover_url, $post_id);
		if(!empty($media) && !is_wp_error($media)){
			$args = array(
				'post_type' => 'attachment',
				'posts_per_page' => -1,
				'post_status' => 'any',
				'post_parent' => $post_id
				);
			$attachments = get_posts($args);
			if(isset($attachments) && is_array($attachments)) {

				foreach($attachments as $attachment){
					$cover_url = wp_get_attachment_image_src($attachment->ID, 'full');

					if(strpos($media, $cover_url[0]) !== false){
						set_post_thumbnail($post_id, $attachment->ID);
						break;
					}
				}
			}
		}
		update_post_meta($post_id, 'e2_fb_cover', $cover_url);
	}

	public function e2_import_events($echo_results) {
	/*█████████████████████████████████████████████████████
	 * Import Facebook events.
	 *
	 * @since    1.0.0
	 */	

		$log = 'Started at:  ' . current_time('Y-m-d h:i:sa').' Wordpress time.' . PHP_EOL;

		if ($echo_results) {
			echo '<div id="progress"><p>Connecting to Facebook</p>';
			flush();
		}

		// Create the Facebook object.
		$fb = new Facebook\Facebook([
			'app_id' => get_option('fb_app_id'),
			'app_secret' => get_option('fb_app_secret'),
			'default_graph_version' => 'v2.6',
			'default_access_token' => get_option('fb_longtoken'),
		]);

		// Create the Facebook Graph request (but don't execute - that's below).
		$request = $fb->request(
		  'GET',
		  '/me/events',
		  array(
		    'fields' => 'id,name,description,cover,start_time,end_time,owner,place,updated_time',
		    'type' => 'attending',
		    'limit' => get_option('fb_get_events')
		  )
		);

		// Set the max script timeout to 30s from now
		set_time_limit(30);

		// Send the request to Graph.
		try {
		  $response = $fb->getClient()->sendRequest($request);

		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  // When Graph returns an error
		  $log = $log . 'Graph returned an error: ' . $e->getMessage() . PHP_EOL;
		  exit;

		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  // When validation fails or other local issues
		  $log = $log . 'Facebook SDK returned an error: ' . $e->getMessage() . PHP_EOL;
		  exit;

		}

		// Process the Graph response.
		$events = $response->getGraphEdge();
		
		//Set some vars
		$totalEvents = count($events);
		$today = new DateTime(current_time('Y-m-d'));
		$before_today = false;

		foreach ($events as $index => $e) {

			if ($echo_results) {
				echo '<p>Processing event ' . (intval($index) + 1) . ' of ' . (intval($totalEvents)) . '<br />';
				flush();
			}

			// Set the max script timeout to 15s from now
			set_time_limit(15);

			$e_id = $e['id'];
			$e_name = $e['name'];
			$e_description = wp_strip_all_tags($e['description']);
			$e_cover = $e['cover']['source'];
			
			// Date stuff
			$e_start = $e['start_time']->format('Y-m-d g:ia'); //Format the start date
			$e_start_datetime = new DateTime($e_start); //Convert the start date into a PHP datetime object
			if ((date_diff($today,$e_start_datetime)->format('%r%a')) < -2) { //Set a flag to stop the loop for older events.
				$before_today = true;
				$log = $log . 'Processed ' . (intval($index) + 1) . ' of ' . (intval($totalEvents)) . ' events.' . PHP_EOL;
			}
			$e_start_datetime = $e_start_datetime->format('Y-m-d');

			if (is_a($e['end_time'], 'DateTime')) { 
				$e_end_time = $e['end_time']->format('Y-m-d g:ia'); 
			}

			$e_owner_id = $e['owner']['id'];
			$e_owner_name = $e['owner']['name'];

			if ( !empty($e['place']['location']['street']) && !empty($e['place']['location']['city']) ) {
				$e_location = $e['place']['location']['street'] .', ' . $e['place']['location']['city'];
				$do_geocode = false;
			} elseif ( !empty($e['place']['name']) ) {
				$e_location = $e['place']['name'];
				$do_geocode = true;
			} else {
				$e_location = 'No location';
				$do_geocode = false;
			}
			$e_latitude = $e['place']['location']['latitude'];
			$e_longitude = $e['place']['location']['longitude'];
			$e_timezone = $e['timezone'];
			$e_updated = $e['updated_time']->format('Y-m-d g:ia');

			// Try and geocode if there is a location name, but no lat/lng
			if ($do_geocode && empty($e_latitude) ) {
					$log = $log . 'Attempting geocode for: ' . $e_location . PHP_EOL;
					$geocode = apply_filters('e2_geocode_place', $e_location, get_option('radius'), array('lat'=>get_option('radius_lat'),'lng'=>get_option('radius_lng')), get_option('api_key_gp'));
					if (!empty($geocode)) {
						$log = $log . 'Geocode successful: ' . $geocode['lat'] . ',' . $geocode['lng'] . PHP_EOL;
						$e_latitude = $geocode['lat'];
						$e_longitude = $geocode['lng'];
					} else {
						$log = $log . 'Geocode unsuccessful.' . PHP_EOL;
					};
			};

			// The Query
			$args = array (
				'post_type' => 'events',
				'posts_per_page' => -1,
				'meta_key' => 'e2_fb_id',
				'meta_query' => array(
					'key'		=> 'e2_fb_id',
					'value'		=> $e_id,
					),
				);
			$the_query = new WP_Query( $args );

			// The Loop
			if ( $the_query->have_posts() ) {
				
				$do_update_meta = false;

				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$this_id = get_the_ID();
					if ( get_post_meta($this_id, 'e2_stop_update', true) != true ) {
						if ( get_post_meta($this_id, 'e2_fb_updated', true) !=  $e_updated ) {
							$this_event = array(
								'ID'			=> $this_id,
								'post_type'		=> 'events',
								'post_title' 	=> wp_strip_all_tags($e_name),
								'post_content'	=> wp_strip_all_tags($e_description),
								'post_status'	=> 'publish',			
							);
							wp_update_post( $this_event );
							$do_update_meta = true;
						};
					}
					$this_img = get_post_meta( $this_id, 'e2_fb_cover', true )[0];
					$e_cover_basename = reset(explode('?', basename($e_cover)));
					$this_img_basename = basename($this_img);
					if ($e_cover_basename != $this_img_basename && !empty($e_cover)) {
						apply_filters('e2_insert_image', $this_id, $e_cover);
					}

				}
			
			} else {

				$this_event = array(
					'post_type' 	=> 'events',
					'post_name'		=> $e_id,
					'post_title' 	=> wp_strip_all_tags($e_name),
					'post_content' 	=> wp_strip_all_tags($e_description),
					'post_status' 	=> 'publish',
				);		
				$this_id = wp_insert_post($this_event);
				$do_update_meta = true;

				if ($e_cover != '') {
					apply_filters('e2_insert_image', $this_id, $e_cover);
				}

			} //END - HAVE POSTS

			//Update the post metadata.
			if (do_update_meta) {

				update_post_meta($this_id, 'e2_fb_id', $e_id);
				update_post_meta($this_id, 'e2_fb_start', $e_start);
				update_post_meta($this_id, 'e2_fb_start_date', $e_start_datetime);
				update_post_meta($this_id, 'e2_fb_end', $e_end_time);
				update_post_meta($this_id, 'e2_fb_location', $e_location);
				update_post_meta($this_id, 'e2_fb_lat', $e_latitude);
				update_post_meta($this_id, 'e2_fb_lng', $e_longitude);
				update_post_meta($this_id, 'e2_fb_owner_id', $e_owner_id);
				update_post_meta($this_id, 'e2_fb_owner_name', $e_owner_name);
				update_post_meta($this_id, 'e2_fb_updated', $e_updated);
				update_post_meta($this_id, 'e2_fb_timezone', $e_timezone);
			}

			if ($echo_results) {
				echo '</p>';
				echo '<script type="text/javascript">window.scrollTo(0,document.body.scrollHeight);</script>';
			}
			if ($before_today) {
				break;
			}

		} //END - EACH EVENT

		if ($echo_results) {
			echo '</div>';
		}

		$log = $log . 'Completed at ' . current_time('Y-m-d h:i:sa') . ' Wordpress time.' . PHP_EOL . PHP_EOL;
		if (!file_exists(ABSPATH . 'wp-content/uploads/e2log')) {
		    wp_mkdir_p(ABSPATH . 'wp-content/uploads/e2log');
		}	
		file_put_contents(ABSPATH . 'wp-content/uploads/e2log/e2log_'.current_time('Y-m-d').'.txt', $log, FILE_APPEND);

	}

	public function e2_generate_map_json() {
	/*█████████████████████████████████████████████████████
	 * Generate a JSON file of todays Facebook events for the map.
	 *
	 * @since    1.0.0
	 */
		$args = array (
			'post_type' => 'events',
			'posts_per_page' => -1,
			'meta_key' => 'e2_fb_start_date',
			'meta_query' => array(
				'key'		=> 'e2_fb_start_date',
				'value'		=> current_time('Y-m-d')
				),			
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$this_id = get_the_ID();
				$e_start_datetime = new DateTime(get_post_meta($this_id, 'e2_fb_start', true));
				$events_today[] = array(
					'title'		=> get_the_title(),
					'start'		=> $e_start_datetime->format('g:ia'),
					'url'		=> 'http://facebook.com/events/' . get_post_meta($this_id, 'e2_fb_id', true),
					'location'	=> get_post_meta($this_id, 'e2_fb_location', true),
					'lat' 		=> get_post_meta($this_id, 'e2_fb_lat', true),
					'lng' 		=> get_post_meta($this_id, 'e2_fb_lng', true),
					'cover'		=> wp_get_attachment_url( get_post_thumbnail_id($this_id) )
				);				
			}
		}

		// Write array of todays events to JSON file in uploads folder
		$response = $events_today;
		if (!file_exists(ABSPATH . 'wp-content/uploads')) {
		    wp_mkdir_p(ABSPATH . 'wp-content/uploads');
		}

		$fp = fopen(ABSPATH . 'wp-content/uploads/e2-map.json', 'w');
		fwrite($fp, json_encode($response));
		fclose($fp);	

	}

}