<?php
/**
 * @package   Instant_Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */

/**
 * Cart admin page.
 *
 * @package Instant_Content
 * @author  Demand Media <instantcontent@demandmedia.com>
 */
class Instant_Content_Admin_Cart extends Instant_Content_Admin {

	/**
	 * Register this admin page with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_page() {
		$this->library_hook = add_submenu_page(
			'options.php',
			__( 'Instant Content Cart', 'instant-content' ),
			__( 'Instant Content Cart', 'instant-content' ),
			'edit_posts',
			Instant_Content::SLUG . '-cart',
			array( $this, 'display' )
		);

		// Register contextual help
		add_action( 'load-' . $this->cart_hook, array( $this, 'help' ) );
	}

	/**
	 * Render the page contents.
	 *
	 * @since 1.0.0
	 */
	public function display() {
		$this->view( 'cart' );
	}

	/**
	 * Populate contextual help.
	 *
	 * @since 1.0.0
	 */
	public function help() {
		$screen = get_current_screen();
		$cart_help =
			'<p>'  . __( 'This screen lists all the articles you have saved to your cart.' ) . '</p>';

		$screen->add_help_tab(
			array(
				'id'      => $this->cart_hook,
				'title'   => __( 'Instant Content Cart', 'instant-content' ),
				'content' => $cart_help,
			)
		);

		// Add help sidebar
		parent::help();
	}

}