<?php
/**
 * @package   Instant Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */

class Instant_Content {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 0.1
	 * @var string
	 */
	protected $version = '0.1';

	/**
	 * Unique identifier
	 *
	 * @since 0.1
	 * @var string
	 */
	protected $slug = 'instantcontent';

	/**
	 * Instance of this class.
	 *
	 * @since 0.1
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since 0.1
	 * @var string
	 */
	protected $search_hook = null;
	protected $library_hook = null;
	protected $settings_hook = null;
	protected $import_hook = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 0.1
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add link from plugins page
		add_filter( 'plugin_action_links', array( $this, 'plugins_page_link' ), 10, 2 );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		//  Displays notices on settings page
		add_action( 'admin_notices', array( $this, 'settings_notices') );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 0.1
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 0.1
	 */
	public function load_plugin_textdomain() {

		$domain = $this->slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since 0.1
	 * @return null Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->search_hook ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == ( $this->search_hook || $this->settings_hook || $this->library_hook || $this->import_hook) ) {
			wp_enqueue_style( $this->slug .'-admin-styles', plugins_url( 'css/styles.css', __FILE__ ), array(), $this->version );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since 0.1
	 * @return null Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->search_hook ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( $screen->id == ( $this->search_hook || $this->settings_hook || $this->library_hook || $this->import_hook ) ) :

			wp_enqueue_script( $this->slug . '-admin-script', plugins_url( 'js/instant-content-scripts.js', __FILE__ ), array( 'jquery' ), $this->version );

			$options = get_option( 'instant_content', false );
			$license = isset( $options['license'] ) ? $options['license'] : '';
			$status = isset( $options['license_status'] ) ? $options['license_status'] : '';
			$terms = isset( $options['terms'] ) ? $options['terms'] : false;

			wp_localize_script( $this->slug . '-admin-script', $this->slug,
				array(
					'loading' => __( 'Loading articles. Please wait...', $this->slug ),
					'purchase' => __( 'Purchase', $this->slug ),
					'import' => __( 'Import', $this->slug ),
					'license' => $license,
					'license_status' => $status,
					'terms' => $terms,
					'referrer' => get_site_url()
				)
			);

		endif;

		if ( $screen->id == $this->search_hook ) {
			add_thickbox();
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since 0.1
	 */
	public function add_plugin_admin_menu() {

		$this->search_hook = add_submenu_page(
			'edit.php',
			__( 'Instant Content', $this->slug ),
			__( 'Instant Content', $this->slug ),
			'edit_posts',
			'instant-content-search',
			array( $this, 'display_search_admin_page' )
		);

		$this->library_hook = add_submenu_page(
			null,
			__( 'Instant Content Library', $this->slug ),
			__( 'Instant Content Library', $this->slug ),
			'edit_posts',
			'instant-content-library',
			array( $this, 'display_library_admin_page' )
		);

		$this->settings_hook = add_submenu_page(
			null,
			__( 'Instant Content Settings', $this->slug ),
			__( 'Instant Content Settings', $this->slug ),
			'edit_posts',
			'instant-content-settings',
			array( $this, 'display_settings_admin_page' )
		);

		register_setting( 'instant_content_settings', 'instant_content', array( $this, 'settings_sanitization') );

		$this->import_hook = add_submenu_page(
			null,
			__( 'Instant Content Import', $this->slug ),
			__( 'Instant Content Import', $this->slug ),
			'edit_posts',
			'instant-content-import',
			array( $this, 'display_import_page' )
		);

	}

	/**
	 * Add a link to Instant Content from the plugins page
	 *
	 * @since 0.1
	 */
	function plugins_page_link( $links, $file ) {
		$plugin = str_replace( 'class-', '', plugin_basename(__FILE__) );
		if ( $plugin == $file ) {
			$settings_link = '<a href="' . 'edit.php?page=instant-content-search' . '">' . __( 'Search', $this->slug ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	/**
	 * Render the search page for this plugin.
	 *
	 * @since 0.1
	 */
	public function display_search_admin_page() {
		include_once( 'views/search.php' );
	}

	/**
	 * Display content purchased through the plugin
	 *
	 * @since 0.1
	 */
	public function display_library_admin_page() {
		include_once( 'views/library.php' );
	}

	/**
	 * Display Instant Content Settings
	 *
	 * @since 0.1
	 */
	public function display_settings_admin_page() {
		include_once( 'views/settings.php' );
	}

	/**
	 * Display Instant Import Page
	 *
	 * @since 0.1
	 */
	public function display_import_page() {
		include_once( 'views/import.php' );
	}

	/**
	 * Make sure options are set correctly
	 *
	 * @since 0.1
	 * @param array Post data from the settings page
	 * @return array Sanitized post form data
	 */
	public function settings_sanitization( $input ) {

		$options = get_option( 'instant_content' );

		// License sanitization and activation
		$license = isset( $options['license'] ) ? $options['license'] : '';
		$status = isset( $options['license_status'] ) ? $options['license_status'] : false;

		if ( $input['license'] != '' ) :
			if ( ( $license != $input['license'] ) || ( $status != 'valid' ) ) {
				if ( strlen( $input['license'] ) != 32 ) {
					add_settings_error(
						'instantcontent_settings',
						esc_attr( 'settings_updated' ),
						__( 'License key should be 32 characters.', $this->slug ),
						'error'
					);
				} else {
					$license_data = $this->activate_license( $input['license'] );
					$output['license_status'] = $license_data;
				}
			}
			$output['license'] = $input['license'];
		endif;

		// Terms
		if ( isset( $input['terms'] ) )
			$output['terms'] = $this->sanitize_checkbox( $input['terms'] );

		// Header
		if ( in_array( $input['header'], array( 'h1', 'h2', 'h3', 'h4' ) ) ) {
			$output['header'] = $input['header'];
		}

		// Resources
		if ( isset( $input['resources'] ) )
			$output['resources'] = $this->sanitize_checkbox( $input['resources'] );

		// Tips
		if ( isset( $input['tips'] ) )
			$output['tips'] = $this->sanitize_checkbox( $input['tips'] );

		// Things needed
		if ( isset( $input['things_needed'] ) )
			$output['things_needed'] = $this->sanitize_checkbox( $input['things_needed'] );

		add_settings_error(
			'instantcontent_settings',
			esc_attr( 'settings_updated' ),
			__( 'Successfully updated', $this->slug ),
			'updated'
		);

		return $output;
	}

	/**
	 * Helper function for sanitizing checkboxes
	 *
	 * @since 0.1
	 * @param string checkbox value
	 * @return string sanitized checkbox value
	 */
	function sanitize_checkbox( $input ) {
        if ( $input ) {
                $output = '1';
        } else {
                $output = false;
        }
        return $output;
	}

	function settings_notices() {
		settings_errors( 'instantcontent_settings' );
	}

	/**
	 * Activate License
	 *
	 * @since 0.1
	 * @param string License key
	 * @return string "Active" or "Inactive"
	 */
	function activate_license( $license ) {

		// Data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( INSTANT_CONTENT_PLUGIN )
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, INSTANT_CONTENT_UPDATE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// Make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// Decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );


		// $license_data->license will be either "active" or "inactive"
		return $license_data->license;
	}

}