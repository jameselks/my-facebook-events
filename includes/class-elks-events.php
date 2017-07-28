<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://jameselks.com
 * @since      1.0.0
 *
 * @package    Elks_Events
 * @subpackage Elks_Events/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Elks_Events
 * @subpackage Elks_Events/includes
 * @author     James Elks <findme@jameselks.com>
 */
class Elks_Events {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Elks_Events_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'elks-events';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Elks_Events_Loader. Orchestrates the hooks of the plugin.
	 * - Elks_Events_i18n. Defines internationalization functionality.
	 * - Elks_Events_Admin. Defines all hooks for the admin area.
	 * - Elks_Events_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-elks-events-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-elks-events-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-elks-events-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-elks-events-public.php';

		/**
		 * The Facebook SDK.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/libs/facebook-sdk-v4-5.0.0/autoload.php';

		$this->loader = new Elks_Events_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Elks_Events_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Elks_Events_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Elks_Events_Admin( $this->get_plugin_name(), $this->get_version() );

		// Admin styles and scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		// Admin menu and page
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'e2_admin' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'e2_admin_settings' );

		// Primary methods - import events and generate JSON
		$this->loader->add_action( 'e2_import_events', $plugin_admin, 'e2_import_events' );
		$this->loader->add_action( 'e2_generate_map_json', $plugin_admin, 'e2_generate_map_json' );
		$this->loader->add_action( 'e2_process_events', $plugin_admin, 'e2_process_events' );

		// Exchange short-lived FB token for long-lived FB token
		$this->loader->add_action( 'wp_ajax_e2_fb_tokenexchange', $plugin_admin, 'e2_fb_tokenexchange' );

		// Filters for inserting an image and geocoding a place
		$this->loader->add_filter( 'e2_insert_image', $plugin_admin, 'e2_insert_image', 10, 2 );
		$this->loader->add_filter( 'e2_geocode_place', $plugin_admin, 'e2_geocode_place', 10, 4 );

		//Custom columns on the 'edit events' page
		$this->loader->add_filter( 'manage_edit-e2_events_columns', $plugin_admin, 'e2_set_custom_columns' );
		$this->loader->add_action( 'manage_e2_events_posts_custom_column', $plugin_admin, 'e2_set_custom_columns_data', 10, 2 );
		$this->loader->add_filter( 'manage_edit-e2_events_sortable_columns', $plugin_admin, 'e2_set_custom_columns_sort' );
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'e2_set_custom_columns_sort_order' );

		// Write log to log file
		$this->loader->add_action( 'e2_log', $plugin_admin, 'e2_log', 10, 2 );

		// Email notification
		$this->loader->add_action( 'e2_mail', $plugin_admin, 'e2_mail' );

		//Crons
		$this->loader->add_action( 'e2_cron_hourly', $plugin_admin, 'e2_hourly' );
		$this->loader->add_action( 'e2_cron_daily', $plugin_admin, 'e2_daily' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Elks_Events_Public( $this->get_plugin_name(), $this->get_version() );

		//Register styles and scripts
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'e2_register_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'e2_register_scripts' );

		//Event post type
		$this->loader->add_action( 'init', $plugin_public, 'e2_create_post_type' );

		//Shortcodes
		$this->loader->add_shortcode( 'e2_map_today', $plugin_public, 'e2_map_today' );
		$this->loader->add_shortcode( 'e2_list', $plugin_public, 'e2_list' );
		$this->loader->add_shortcode( 'e2_list_email', $plugin_public, 'e2_list_email' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Elks_Events_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
