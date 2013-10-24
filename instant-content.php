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
 * Version:     0.1
 * Author:      Demand Media
 * Author URI:  http://instantcontent.me
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load the Instant Content Class
require plugin_dir_path( __FILE__ ) . 'class-instant-content.php';
require plugin_dir_path( __FILE__ ) . 'class-instant-content-importer.php';

// Instantiate importer class, so we can add it as a dependency to main plugin class.
$instant_content_importer = new Instant_Content_Importer;

// Initialize the class
$instant_content_importer->init();

// Load the main plugin class
Instant_Content::get_instance();

// Constants used for plugin updates
define( 'INSTANT_CONTENT_UPDATE_URL', 'http://instantcontent.me' );
define( 'INSTANT_CONTENT_PLUGIN', 'Instant Content' );

// Load the updater class
if ( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'class-instant-content-updater.php' );
}

// Retrieve license key from the DB
$options = get_option( 'instant_content', false );
$license_key = isset( $options['license'] ) ? $options['license'] : '';

// Setup the updater
$edd_updater = new EDD_SL_Plugin_Updater( INSTANT_CONTENT_UPDATE_URL, __FILE__, array(
		'version' 	=> '0.1', // current version number
		'license' 	=> $license_key,
		'item_name' => INSTANT_CONTENT_PLUGIN,
		'author' 	=> 'Demand Media'
	)
);