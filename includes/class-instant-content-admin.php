<?php
/**
 * @package   Instant_Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */

/**
 * Base class for admin pages.
 *
 * @package Instant_Content
 * @author  Demand Media <instantcontent@demandmedia.com>
 */
abstract class Instant_Content_Admin {

	/**
	 * Page hook for the search screen.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $search_hook   = null;

	/**
	 * Page hook for the library screen.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $library_hook  = null;

	/**
	 * Page hook for the cart screen.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $cart_hook  = null;

	/**
	 * Page hook for the settings screen.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $settings_hook = null;

	/**
	 * Page hook for the importer screen.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	protected $importer_hook = null;

	/**
	 * Hook in the methods common to all admin pages.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );

		// Messy use of a global to ensure that each admin page object doesn't re-enqueue when its own class is init()'d.
		global $instant_content_init;

		if ( ! $instant_content_init ) {
			// Load admin style sheet and JavaScript.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			$instant_content_init = true;

		}
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since 1.0.0
	 * @return null Return early if not an admin page registered by this plugin.
	 */
	public function enqueue_admin_styles() {
		$screen = get_current_screen();

		if ( ! in_array( $screen->id, $this->get_page_hooks() ) ) {
		 	return;
		}
		wp_enqueue_style( Instant_Content::SLUG .'-admin-styles', plugins_url( 'css/admin.css', dirname( __FILE__ ) ), array(), Instant_Content::VERSION );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since 1.0.0
	 * @return null Return early if not an admin page registered by this plugin.
	 */
	public function enqueue_admin_scripts() {
		$screen = get_current_screen();

		if ( ! in_array( $screen->id, $this->get_page_hooks() ) ) {
		 	return;
		}

		if ( $screen->id === $this->search_hook ) {
			add_thickbox();
		}

		if ( 'admin_page_instant-content-settings' === $screen->id ) {
			add_thickbox();
		}

		wp_enqueue_script( Instant_Content::SLUG . '-admin-script', plugins_url( 'js/admin.js', dirname( __FILE__ ) ), array( 'jquery' ), Instant_Content::VERSION, true );

		$options = get_option( 'instant_content', false );

		$l10n = array(
			// Common
			'hasValidLicenseAndTerms' => 'valid' === $options['license_status'] && $options['terms'],

			// Search
			'loading'                 => __( 'Loading articles. Please wait...', 'instant-content' ),
			'purchase'                => __( 'Purchase', 'instant-content' ),
			'checkout'                => __( 'Check Out', 'instant-content' ),
			'addtocart'               => __( 'Add to Cart', 'instant-content' ),
			'addedtocart'        	  => __( 'Item added to cart: ', 'instant-content' ),
			'disabled'                => __( 'Disabled', 'instant-content' ),
			'noResults'               => __( 'No results.', 'instant-content' ),
			'aboutToPurchase'         => __( 'You are about to purchase the article', 'instant-content' ),
			'takenToPayPal'           => __( 'You will be taken to a PayPal screen to complete the payment, and then returned to your WordPress library.', 'instant-content' ),
			'clickOk'                 => __( 'Click OK to continue.', 'instant-content' ),
			'enterKeyPurchase'        => __( 'Please enter a valid license key and agree to plugin terms to purchase content.', 'instant-content' ),
			'license'                 => isset( $options['license'] ) ? $options['license'] : '',
			'referrer'                => get_site_url(),
			'settingsUrl'             => menu_page_url( Instant_Content::SLUG . '-settings', false ),

			// Library
			'libraryLoaded'           => __( 'Library loaded.
			New purchases may take a few minutes to appear.', 'instant-content' ),
			'import'                  => __( 'Import', 'instant-content' ),
			'invalidKey'              => __( 'License key is invalid.', 'instant-content' ),
			'enterKeyLibrary'         => __( 'Please enter a valid license key to view purchased content.', 'instant-content' ),
			'noPurchases'             => __( 'No purchases.', 'instant-content' ),

			// Search & Library
			'items'                   => __( 'items', 'instant-content' ), // items is a reserved property name, so in JS, use instantContentL10n['items']
			'failedToConnect'         => __( 'Failed to connect to server.', 'instant-content' ),

			// Import
			'viewDraftPost'    => __( 'View Draft Post', 'instant-content' ),
			'editPost'         => __( 'Edit Post', 'instant-content' ),

			// API
			'apiBaseUrl'       => Instant_Content::API_BASE_URL,
		);

		wp_localize_script( Instant_Content::SLUG . '-admin-script', 'instantContentL10n', $l10n );
	}

	/**
	 * Keep track of the known page hooks in one place, so they are available to all instances of extended classes.
	 *
	 * @since 1.0.0
	 *
	 * @return array Admin page hooks for this plugin.
	 */
	protected function get_page_hooks() {
		return array(
			'posts_page_instant-content-search',
			'admin_page_instant-content-library',
			'admin_page_instant-content-cart',
			'admin_page_instant-content-import',
			'admin_page_instant-content-settings',
		);
	}

	/**
	 * Populate contextual help.
	 *
	 * Only the common sidebar is set here. The help tabs should be set in the page classes.
	 *
	 * @since 1.0.0
	 */
	public function help() {
		$screen = get_current_screen();

		// Add help sidebar
		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'instant-content' ) . '</strong></p>' .
			'<p><a href="http://www.instantcontent.me/contact" target="_blank" title="' . esc_attr__( 'Get Support', 'instant-content' ) . '">' . __( 'Get Support', 'instant-content' ) . '</a></p>'
		);
	}

	/**
	 * Include a view file.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $slug File name slug e.g. 'search'.
	 */
	protected function view( $slug ) {
		include dirname( plugin_dir_path( __FILE__ ) ) . '/views/' . sanitize_file_name( $slug ) . '.php';
	}

	/**
	 * Register this admin page with WordPress.
	 *
	 * @since 1.0.0
	 */
	abstract public function add_admin_page();

	/**
	 * Render the page contents.
	 *
	 * @since 1.0.0
	 */
	abstract public function display();

}
