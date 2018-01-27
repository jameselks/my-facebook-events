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

		add_menu_page('Facebook Events &mdash; Settings', 'Facebook Events', 'manage_options', $this->plugin_name, array($this, 'e2_admin_page'), 'dashicons-admin-generic');

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

	public function e2_set_custom_columns( $columns ) {
	/*█████████████████████████████████████████████████████
	 * Create custom columns in the 'events' editor.
	 *
	 * @since    1.2.0
	 */ 

		unset($columns['author']);
		unset($columns['date']);
		$columns['e2_start'] = __( 'Start', 'elks-events' );
		$columns['e2_location'] = __( 'Location', 'elks-events' );
		return $columns;
	}

	public function e2_set_custom_columns_data( $column, $post_id ) {
	/*█████████████████████████████████████████████████████
	 * Fill custom columns in the 'events' editor with data.
	 *
	 * @since    1.2.0
	 */
		switch ( $column ) {

			case 'e2_start' :
				echo get_post_meta( $post_id , 'e2_start' , true ); 
				break;

			case 'e2_location' :
				echo get_post_meta( $post_id , 'e2_location' , true ); 
				break;
		}
	}

	public function e2_set_custom_columns_sort( $columns ) {
	/*█████████████████████████████████████████████████████
	 * Enable sorting for the custom 'events' columns
	 *
	 * @since    1.2.0
	 */

		$columns['e2_start'] = 'e2_start';
		$columns['e2_location'] = 'e2_location';
		return $columns;

	}

	public function e2_set_custom_columns_sort_order( $query ) {
	/*█████████████████████████████████████████████████████
	 * Enable sorting for the custom 'events' columns
	 *
	 * @since    1.2.0
	 */

		if( ! is_admin() ) {
        	//Only sort in admin area
			return;
		}
 
		$orderby = $query->get( 'orderby');
	
		switch ( $orderby ) {
			case 'e2_location' :
				$query->set('meta_key','e2_location');
				$query->set('orderby','meta_value');
				break;

			case 'e2_date' :
				$query->set('meta_key','e2_location');
				$query->set('orderby','meta_value_date');
				break;
		}
	}

	public function e2_hourly() {
	/*█████████████████████████████████████████████████████
	 * Actions to be performed every hour.
	 *
	 * @since    1.2.0
	 */

		do_action('e2_process_events');
	}

	public function e2_daily() {
	/*█████████████████████████████████████████████████████
	 * Actions to be performed every day.
	 *
	 * @since    1.2.0
	 */

		do_action('e2_fb_tokenexpiry');

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

	public function e2_mail( $message ) {
	/*█████████████████████████████████████████████████████
	 * Send mail to the site owner.
	 *
	 * @since    1.2.0
	 */

		$message = $message . PHP_EOL . PHP_EOL . PHP_EOL . 'Sent automatically from ' . get_bloginfo('wpurl') . '/wp-admin';
		$to = get_option('admin_email');
		$from = get_bloginfo('name');
		//$headers = 'From: ' . $from . '<' . $to . '>';

		wp_mail($to, 'Error — ' . $from, $message);
		//wp_mail($to, 'Error — ' . $from, $message, $headers);

	}

	public function e2_log( $text, $timestamp = false ) {
	/*█████████████████████████████████████████████████████
	 * Write text to log file.
	 *
	 * @since    1.2.0
	 */	

		// Add timestamp
		if ($timestamp) {
			$text = '[' . current_time('Y-m-d h:i:sa') . '] ' . $text;	
		}

		$text = $text . PHP_EOL;

		if (!file_exists(ABSPATH . 'wp-content/uploads/e2log')) {
			wp_mkdir_p(ABSPATH . 'wp-content/uploads/e2log');
		}	
		file_put_contents(ABSPATH . 'wp-content/uploads/e2log/e2log_'.current_time('Y-m-d').'.txt', $text, FILE_APPEND);
	}

	public function e2_fb_tokenexpiry( $token ) {
	/*█████████████████████████████████████████████████████
	 * Check the expiry date of the token.
	 *
	 * @since    1.2.0
	 */

		$token = get_option('fb_longtoken');
		do_action('e2_log', 'Start e2_fb_tokenexpiry', true);

		// Create the Facebook object.
		$fb = new Facebook\Facebook([
			'app_id' => get_option('fb_app_id'),
			'app_secret' => get_option('fb_app_secret'),
			'default_graph_version' => 'v2.6',
			'default_access_token' => $token,
		]);

		// Create the Facebook Graph request (but don't execute - that happens later).
		$request = $fb->request(
			'GET',
			'/debug_token?input_token=' . $token
		);

		// Set the max script timeout to 20s from now
		set_time_limit(10);

		// Send the request to Graph.
		try {
			$response = $fb->getClient()->sendRequest($request);

		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$fb_error = 'Facebook Graph returned an error: ' . $e->getMessage();
			do_action('e2_log', $fb_error, true);
			exit;

		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$fb_error = 'Facebook SDK returned an error: ' . $e->getMessage();
			do_action('e2_log', $fb_error, true);
			exit;

		}

		// Process the Graph response.
		$token = $response->getGraphObject();
		$notify = new DateTime(date("Y-m-d", strtotime('+10 days')));
		$token_expires = $token['expires_at'];
		
		//If token expires within 10 days - send user an email and log it.
		$expires_days = $notify->diff($token_expires)->format('%a');
		if ($token_expires < $notify ) {
			$message = "Your Facebook authentication expires in " . $expires_days . ' days.';
			do_action('e2_log', $message, true);
			$message = $message . PHP_EOL . PHP_EOL . 'Log in to your website and re-authenticate Facebook Events:';
			$message = $message . PHP_EOL . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=elks-events';
			do_action('e2_mail', $message);
		}

		do_action('e2_log', 'Finish e2_fb_tokenexpiry - ' . $expires_days . ' days before expiry', true);

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
			do_action('e2_log', 'Token exchange - Facebook Graph returned an error: ' . $e->getMessage(), true);
			exit;

		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			do_action('e2_log', 'Token exchange - Facebook SDK returned an error: ' . $e->getMessage(), true);
			exit;

		}

		if (! isset($accessToken)) {
			//If cookie not set
			do_action('e2_log', 'No cookie set or no OAuth data could be obtained from cookie.', true);
			exit;
		}

		// OAuth 2.0 client handler
		$oAuth2Client = $fb->getOAuth2Client();
		 
		// Exchanges a short-lived access token for a long-lived one
		$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);

		// Add the long-lived token to the plugin options
		update_option( 'fb_longtoken', $accessToken );

		//echo $accessToken;
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
			$results = array(
				'lat' => $lat,
				'lng' => $lng,
				);
		}

		return $results;
	}


	public function e2_insert_image($post_id, $cover_url) {
	/*█████████████████████████████████████████████████████
	 * Download and add image as featured image to post.
	 *
	 * @since    1.0.0
	 */

		include_once(ABSPATH . "wp-includes/pluggable.php");
		include_once(ABSPATH . "wp-admin/includes/media.php");
		include_once(ABSPATH . "wp-admin/includes/file.php");
		include_once(ABSPATH . "wp-admin/includes/image.php");

		//Download the image from the specified URL and attach it to the post
		$media = media_sideload_image($cover_url, $post_id);
		
		//If successful, attach it as the cover image
		if ( !empty($media) && !is_wp_error($media) ){
			
			$args = array(
				'post_type' => 'attachment',
				'posts_per_page' => -1,
				'post_status' => 'any',
				'post_parent' => $post_id
				);
			$attachments = get_posts($args);
			
			if ( isset($attachments) && is_array($attachments) ) {

				foreach($attachments as $attachment){

					$cover_url = wp_get_attachment_image_src( $attachment->ID, 'full' );

					if ( strpos($media, $cover_url[0]) !== false ) {
						set_post_thumbnail($post_id, $attachment->ID);
						break;
					}

				}
			}

		}

		update_post_meta($post_id, 'e2_fb_cover', $cover_url);
	}

	public function e2_import_events( $echo_results ) {
	/*█████████████████████████████████████████████████████
	 * Import Facebook events.
	 *
	 * @since    1.0.0
	 */	

		do_action('e2_log', 'Start e2_process_events', true);

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

		// Create the Facebook Graph request (but don't execute - that happens later).
		$request = $fb->request(
			'GET',
			'/me/events',
			array(
				'fields' => 'id,name,description,cover,start_time,place,updated_time',
				//'type' => 'attending',
				'limit' => get_option('fb_get_events')
			)
		);

		// Set the max script timeout to 20s from now
		set_time_limit(20);

		// Send the request to Graph.
		try {
			$response = $fb->getClient()->sendRequest($request);

		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$fb_error = 'Facebook Graph returned an error: ' . $e->getMessage();
			do_action('e2_log', $fb_error, true);
			do_action('e2_mail', $fb_error);
			if($echo_results) {
				echo $fb_error;
			}
			exit;

		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$fb_error = 'Facebook SDK returned an error: ' . $e->getMessage();
			do_action('e2_log', $fb_error, true);
			do_action('e2_mail', $fb_error);
			if($echo_results) {
				echo $fb_error;
			}
			exit;

		}

		// Process the Graph response.
		$events = $response->getGraphEdge();
		
		//Set some vars
		$totalEvents = count($events);
		$today = new DateTime(current_time('Y-m-d'));
		$before_today = false;

		//Cycle through each event
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
			$e_updated = $e['updated_time']->format('Y-m-d g:ia');

			//Location name
			if ( !empty($e['place']['name']) ) {
					$e_location_name = $e['place']['name'];
			}

			//Location address
			if ( !empty($e['place']['location']['street']) && !empty($e['place']['location']['city']) ) {
				$e_location_address = $e['place']['location']['street'] .', ' . $e['place']['location']['city'];
			}

			$e_location = $e_location_name . ' &mdash; ' . $e_location_address;

			//Latitude and longitude
			$e_latitude = $e['place']['location']['latitude'];
			$e_longitude = $e['place']['location']['longitude'];

			// Try and geocode if there is a location name, but no lat/lng
			if (!empty($e_location_name) && empty($e_latitude) && empty($e_longitude)) {
					$log = $log . 'Attempting geocode for address: ' . $e_location_name . PHP_EOL;
					$log = $log . 'Facebook event ID: ' . $e_id . PHP_EOL;
					$geocode = apply_filters('e2_geocode_place', $e_location_name, get_option('radius'), array('lat'=>get_option('radius_lat'),'lng'=>get_option('radius_lng')), get_option('api_key_gp'));
					if (!empty($geocode)) {
						$log = $log . 'Geocode successful: ' . $geocode['lat'] . ', ' . $geocode['lng'] . PHP_EOL;
						$e_latitude = $geocode['lat'];
						$e_longitude = $geocode['lng'];
						$e_location = $e_location_name;
					} else {
						$log = $log . 'Geocode unsuccessful.' . PHP_EOL;
						$e_location = '';
					};
			};

			// The Query
			$args = array (
				'post_type' => 'e2_events',
				'posts_per_page' => -1,
				'meta_key' => 'e2_fb_id',
				'meta_query' => array(
					'key'		=> 'e2_fb_id',
					'value'		=> $e_id,
					),
				);
			$the_query = new WP_Query( $args );

			// The Loop - HAVE POSTS
			if ( $the_query->have_posts() ) {
				
				$do_update_meta = false;

				while ( $the_query->have_posts() ) {
					
					$the_query->the_post();
					$this_id = get_the_ID();
					
					//Don't update if 'stop_update' custom field is true
					if ( ! get_post_meta($this_id, 'e2_stop_update', true) ) {
					
						if ( get_post_meta($this_id, 'e2_fb_updated', true) !=  $e_updated ) {
							$this_event = array(
								'ID'			=> $this_id,
								'post_type'		=> 'e2_events',
								'post_title' 	=> wp_strip_all_tags($e_name),
								'post_content'	=> wp_strip_all_tags($e_description),
								'post_status'	=> 'publish',			
							);
							wp_update_post( $this_event );
							$do_update_meta = true;
						};
					
						$this_img = get_post_meta( $this_id, 'e2_fb_cover', true )[0];

						$e_cover_basename = explode('?', basename($e_cover));
						$e_cover_basename = reset($e_cover_basename);
						$this_img_basename = basename($this_img);				
						$e_cover_basename_noext = pathinfo($e_cover_basename, PATHINFO_FILENAME);
						
						if ( (strpos($this_img_basename, $e_cover_basename_noext) === false)  && !empty($e_cover)) {
							apply_filters('e2_insert_image', $this_id, $e_cover);
						}

					}

				}
			
			} else {

				$this_event = array(
					'post_type' 	=> 'e2_events',
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
			if ($do_update_meta) {
				update_post_meta($this_id, 'e2_source_url', 'https://www.facebook.com/events/' . $e_id);
				update_post_meta($this_id, 'e2_start', $e_start_datetime->format('Y-m-d g:ia'));
				update_post_meta($this_id, 'e2_location', $e_location);
				update_post_meta($this_id, 'e2_lat', $e_latitude);
				update_post_meta($this_id, 'e2_lng', $e_longitude);
				update_post_meta($this_id, 'e2_fb_id', $e_id);
				update_post_meta($this_id, 'e2_fb_updated', $e_updated);
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

		do_action('e2_log', trim($log));
		do_action('e2_log', 'Finish e2_process_events' . PHP_EOL, true);

	}

	public function e2_generate_map_json() {
	/*█████████████████████████████████████████████████████
	 * Generate a JSON file of todays Facebook events for the map.
	 *
	 * @since    1.0.0
	 */
		$args = array (
			'post_type' 		=> 'e2_events',
			'posts_per_page' 	=> -1,
			'orderby' 			=> 'meta_value',
			'meta_key' 			=> 'e2_start',
			'meta_query' 		=> array(
				'key'				=> 'e2_start',
				'value'				=> current_time('Y-m-d'),
				'type'				=> 'DATE'
				),			
		);

		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$this_id = get_the_ID();
				$e_start_datetime = new DateTime(get_post_meta($this_id, 'e2_start', true));

				$events_today[] = array(
					'title'		=> get_the_title(),
					'start'		=> $e_start_datetime->format('g:ia'),
					'url'		=> get_post_meta($this_id, 'e2_source_url', true),
					'location'	=> get_post_meta($this_id, 'e2_location', true),
					'lat' 		=> get_post_meta($this_id, 'e2_lat', true),
					'lng' 		=> get_post_meta($this_id, 'e2_lng', true),
					'cover'		=> wp_get_attachment_url( get_post_thumbnail_id($this_id) )
				);				
			}
		}

		// Write array of todays events to JSON file in uploads folder
		if (empty($events_today)) {
			$response = "";
		} else {
			$response = $events_today;	
		}
		if (!file_exists(ABSPATH . 'wp-content/uploads')) {
		    wp_mkdir_p(ABSPATH . 'wp-content/uploads');
		}

		$fp = fopen(ABSPATH . 'wp-content/uploads/e2-map.json', 'w');
		fwrite($fp, json_encode($response));
		fclose($fp);	

	}

}