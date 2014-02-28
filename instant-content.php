<?php
/**
 * Instant Content
 *
 * @package   Instant Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantcontent.me
 * @copyright 2013 Demand media
 *
 * @wordpress-plugin
 * Plugin Name: Instant Content
 * Plugin URI:  http://instantcontent.me
 * Description: Purchase Demand Media content to use on your WordPress site.
 * Version:     1.3.0
 * Author:      Demand Media
 * Author URI:  http://instantcontent.me
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: instant-content
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load the Instant Content classes
require plugin_dir_path( __FILE__ ) . 'includes/class-instant-content.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-instant-content-admin.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-instant-content-admin-search.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-instant-content-admin-library.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-instant-content-admin-cart.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-instant-content-admin-importer.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-instant-content-admin-settings.php';

// Instantiate the main plugin class
$instant_content = new Instant_Content;
$instant_content->init();

// Instantiate the search page class
$instant_content_admin_search = new Instant_Content_Admin_Search;
$instant_content_admin_search->init();

// Instantiate the library page class
$instant_content_admin_library = new Instant_Content_Admin_Library;
$instant_content_admin_library->init();

// Instantiate the library page class
$instant_content_admin_cart = new Instant_Content_Admin_Cart;
$instant_content_admin_cart->init();

// Instantiate importer page class
$instant_content_importer = new Instant_Content_Admin_Importer;
$instant_content_importer->init();
// Register ajax actions
add_action( 'wp_ajax_instant_content_import', array( $instant_content_importer, 'instant_content_import') );

// Instantiate the settings page class
$instant_content_admin_settings = new Instant_Content_Admin_Settings;
$instant_content_admin_settings->init();

// Constants used for plugin updates
define( 'INSTANT_CONTENT_UPDATE_URL', 'http://www.instantcontent.me/' );
define( 'INSTANT_CONTENT_PLUGIN', 'Instant Content Plugin' );

// Load the updater class
if ( !class_exists( 'Instant_Content_Plugin_Updater' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'includes/class-instant-content-plugin-updater.php' );
}

// Retrieve license key from the DB
$options = get_option( 'instant_content', false );
$license_key = isset( $options['license'] ) ? $options['license'] : '';

// Setup the updater
$instant_content_plugin_updater = new Instant_Content_Plugin_Updater(
	INSTANT_CONTENT_UPDATE_URL,
	__FILE__,
	array(
		'version' 	=> Instant_Content::VERSION, // current version number
		'license' 	=> $license_key,
		'item_name' => INSTANT_CONTENT_PLUGIN,
		'author' 	=> 'Demand Media',
	)
);
