<?php
/**
 * @package   Instant_Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */

/**
 * Library admin page.
 *
 * @package Instant_Content
 * @author  Demand Media <instantcontent@demandmedia.com>
 */
class Instant_Content_Admin_Library extends Instant_Content_Admin {

	/**
	 * Register this admin page with WordPress.
	 *
	 * @since 0.1.0
	 */
	public function add_admin_page() {
		$this->library_hook = add_submenu_page(
			'options.php',
			__( 'Instant Content Library', 'instant-content' ),
			__( 'Instant Content Library', 'instant-content' ),
			'edit_posts',
			Instant_Content::SLUG . '-library',
			array( $this, 'display' )
		);

		// Register contextual help
		add_action( 'load-' . $this->library_hook, array( $this, 'help' ) );
	}

	/**
	 * Render the page contents.
	 *
	 * @since 0.1.0
	 */
	public function display() {
		include 'views/library.php';
	}

	/**
	 * Populate contextual help.
	 *
	 * @since 0.1.0
	 */
	public function help() {
		$screen = get_current_screen();
		$library_help =
			'<p>'  . __( 'Some help text here about what is shown on this library page.', 'instant-content' ) . '</p>';

		$screen->add_help_tab(
			array(
				'id'      => $this->library_hook,
				'title'   => __( 'Instant Content Library', 'instant-content' ),
				'content' => $library_help,
			)
		);

		// Add help sidebar
		parent::help();
	}

}
