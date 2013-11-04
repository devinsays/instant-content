<?php
/**
 * @package   Instant_Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */

/**
 * Search admin page.
 *
 * @package Instant_Content
 * @author  Demand Media <instantcontent@demandmedia.com>
 */
class Instant_Content_Admin_Search extends Instant_Content_Admin {

	/**
	 * Hook in the methods for the search page.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		parent::init();

		// Add link from plugins page
		add_filter(
			'plugin_action_links_' . Instant_Content::SLUG . '/' . Instant_Content::SLUG . '.php',
			array( $this, 'plugins_page_link' )
		);
	}

	/**
	 * Register this admin page with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_page() {
		$this->search_hook = add_posts_page(
			__( 'Instant Content', 'instant-content' ),
			__( 'Instant Content', 'instant-content' ),
			'edit_posts',
			Instant_Content::SLUG . '-search',
			array( $this, 'display' )
		);

		// Register contextual help
		add_action( 'load-' . $this->search_hook, array( $this, 'help' ) );
	}

	/**
	 * Render the page contents.
	 *
	 * @since 1.0.0
	 */
	public function display() {
		$this->view( 'search' );
	}

	/**
	 * Populate contextual help.
	 *
	 * @since 1.0.0
	 */
	public function help() {
		$screen = get_current_screen();
		$search_help =
			'<p>'  . __( 'To find articles available for purchase in the Instant Content library, enter keywords into the search field and click "Search for Articles".  There are 150,000+ articles available for sale, but not all keywords will return results.', 'instant-content' ) . '</p>' .
			'<p>'  . __( 'Please note, you will not be able to purchase any content or view the article previews until you have entered a valid license key on the settings page.', 'instant-content' ) . '</p>';

		$screen->add_help_tab(
			array(
				'id'      => $this->search_hook,
				'title'   => __( 'Instant Content Search', 'instant-content' ),
				'content' => $search_help,
			)
		);

		// Add help sidebar
		parent::help();
	}

	/**
	 * Add a link to Instant Content from the plugins page
	 *
	 * @since 1.0.0
	 */
	function plugins_page_link( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'edit.php?page=' . Instant_Content::SLUG . '-search' ) . '">' . __( 'Search', 'instant-content' ) . '</a>'
			),
			$links
		);
	}

}
