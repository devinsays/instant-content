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
	 * Hook in the methods for the importer page.
	 *
	 * @since 1.3.0
	 */
	public function init() {
		parent::init();

		// Register ajax actions
		add_action( 'wp_ajax_instant_content_add_to_cart', array( $this, 'instant_content_add_to_cart' ) );

		add_action( 'wp_ajax_instant_content_remove_from_cart', array( $this, 'instant_content_remove_from_cart' ) );

		add_action( 'wp_ajax_instant_content_bulk_remove_from_cart', array( $this, 'instant_content_bulk_remove_from_cart' ) );

		add_action( 'wp_ajax_instant_content_get_checkout_data', array( $this, 'instant_content_get_checkout_data' ) );

	}


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

	/**
	 * Add an article to the cart
	 *
	 * @since 1.0.0
	 */
	public function instant_content_add_to_cart() {

		$cart = get_option( 'instant_content_cart', array() );

		// The $_REQUEST contains all the data sent via ajax
		if ( isset($_REQUEST) ) {
			$title = $_REQUEST['title'];
			$price = $_REQUEST['price'];
			$key = $_REQUEST['key'];
		}

		$cart[] = array(
			'title' => $title,
			'price' => $price,
			'key' => $key,
		);

		$update = update_option( 'instant_content_cart', $cart );

		$ajax['update'] = $update;
		$ajax['title'] = $title;
		$ajax['price'] = $price;
		$ajax['key'] = $key;

		echo json_encode( $ajax );

		// Always die in functions echoing ajax content
	   die();
	}

	/**
	 * Remove an article from the cart
	 *
	 * @since 1.3.0
	 */
	public function instant_content_remove_from_cart() {

		$cart = get_option( 'instant_content_cart', array() );

		// The $_REQUEST contains all the data sent via ajax
		if ( isset($_REQUEST) ) {
			$remove = $_REQUEST['key'];
		}

		foreach( $cart as $key => $article ) {
			if ( $remove == $article['key'] ) {
				$remove_key = $key;
				break;
			}
		}

		unset( $cart[$remove_key] );

		$update = update_option( 'instant_content_cart', $cart );

		$ajax['update'] = $update;

		echo json_encode( $ajax );

		// Always die in functions echoing ajax content
		die();
	}

	/**
	 * Remove multiple items from cart
	 *
	 * @TODO This could probably be combined with instant_content_remove_from_cart
	 * @since 1.3.0
	 */
	public function instant_content_bulk_remove_from_cart() {

		$cart = get_option( 'instant_content_cart', array() );
		$return = array();

		// The $_REQUEST contains all the data sent via ajax
		if ( isset($_REQUEST) ) {
			$remove = $_REQUEST['keys'];
		}

		foreach( $cart as $key => $article ) {
			if ( in_array( $article['key'], $remove ) ) {
				$return[] = $cart[$key];
				unset( $cart[$key] );
			}
		}

		$update = update_option( 'instant_content_cart', $cart );

		$ajax['update'] = $update;
		$ajax['removed'] = $return;
		$ajax['cart'] = $cart;

		echo json_encode( $ajax );

		// Always die in functions echoing ajax content
		die();
	}

	/**
	 * Remove an article from the cart
	 *
	 * @since 1.3.0
	 */
	public function instant_content_get_checkout_data() {

		$cart = get_option( 'instant_content_cart', array() );

		// Exit early there are no items in cart
		if ( !$cart ) {
			die();
		}

		$price = 0;

		foreach( $cart as $key => $article ) {
			$cartkeys[] = $article['key'];
			$titles[] = $article['title'];
			$price = $price + $article['price'];
		}

		$ajax['count'] = sizeof( $cart );
		$ajax['keys'] = $cartkeys;
		$ajax['titles'] = $titles;
		$ajax['total_price'] = $price;

		echo json_encode( $ajax );

		// Always die in functions echoing ajax content
		die();
	}


}