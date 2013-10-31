<?php
/**
 * @package   Instant_Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */

class Instant_Content {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 0.1.0
	 * @type string
	 */
	const VERSION = '0.1.0';

	/**
	 * Unique identifier
	 *
	 * @since 0.1.0
	 * @type string
	 */
	const SLUG = 'instant-content';

	/**
	 * Initialize the plugin.
	 *
	 * @since 0.1.0
	 */
	public function init() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 0.1.0
	 */
	public function load_plugin_textdomain() {
		$domain = self::SLUG;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
	}

}
