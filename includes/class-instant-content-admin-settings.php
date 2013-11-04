<?php
/**
 * @package   Instant_Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */

/**
 * Settings admin page.
 *
 * @package Instant_Content
 * @author  Demand Media <instantcontent@demandmedia.com>
 */
class Instant_Content_Admin_Settings extends Instant_Content_Admin {

	/**
	 * Hook in the methods for the settings page.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		parent::init();

		// Displays notices
        add_action( 'admin_notices', array( $this, 'notices') );
	}

	/**
	 * Register this admin page with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_page() {
		$this->settings_hook = add_submenu_page(
			'options.php',
			__( 'Instant Content Settings', 'instant-content' ),
			__( 'Instant Content Settings', 'instant-content' ),
			'edit_posts',
			Instant_Content::SLUG . '-settings',
			array( $this, 'display' )
		);

		// Register contextual help
		add_action( 'load-' . $this->settings_hook, array( $this, 'help' ) );

		register_setting( 'instant_content_settings', 'instant_content', array( $this, 'settings_sanitization') );
	}

	/**
	 * Render the page contents.
	 *
	 * @since 1.0.0
	 */
	public function display() {
		$this->view( 'settings' );
	}

	/**
	 * Populate contextual help.
	 *
	 * @since 1.0.0
	 */
	public function help() {
		$screen = get_current_screen();
		$settings_help =
			'<p>'  . __( 'If you have problems generating the license key from the the Instant Content website, please contact us at instantcontent@demandmedia.com.', 'instant-content' ) . '</p>';

		$screen->add_help_tab(
			array(
				'id'      => $this->settings_hook,
				'title'   => __( 'Instant Content Settings', 'instant-content' ),
				'content' => $settings_help,
			)
		);

		// Add help sidebar
		parent::help();
	}

	/**
	 * Make sure options are set correctly
	 *
	 * @since 1.0.0
	 * @param array Post data from the settings page
	 * @return array Sanitized post form data
	 */
	public function settings_sanitization( $input ) {

		$options = get_option( 'instant_content', false );

		// License sanitization and activation
		$license = isset( $options['license'] ) ? $options['license'] : '';
		$status = isset( $options['license_status'] ) ? $options['license_status'] : '';

		// Manually set as this does not come from form data
		$output['license_status'] = $status;

		if ( isset( $input['license'] ) ) {

			// Sometimes the license doesn't activate on the first save
			// Trying to accomodate for this race condition
			if ( $status !== 'valid' && ( $license === $input['license'] ) ) {
				if ( 32 === strlen( $input['license'] ) ) {
					$output['license_status'] = $this->activate_license( $input['license'] );
				}
			}

			// For new license keys
			if ( $license !== $input['license'] ) {
				// Reset license status
				$output['license_status'] = '';
				// If new license is a valid length, activate it
				if ( 32 === strlen( $input['license'] ) ) {
					$output['license_status'] = $this->activate_license( $input['license'] );
				}

			}

			// Let user know if license key isn't proper length
			if ( 32 !== strlen( $input['license'] ) && !isset( $input['init'] ) ) {
				$output['license_status'] = '';
				add_settings_error(
					'instantcontent_settings',
					esc_attr( 'settings_updated' ),
					__( 'License key should be 32 characters.', 'instant-content' ),
					'error'
				);
			}

			// @todo Sanitization?
			$output['license'] = $input['license'];
		}

		// Terms
		if ( isset( $input['terms'] ) )
			$output['terms'] = $this->one_zero( $input['terms'] );

		// Header
		if ( in_array( $input['header'], array( 'h1', 'h2', 'h3', 'h4' ) ) ) {
			$output['header'] = $input['header'];
		}

		// Resources
		if ( isset( $input['resources'] ) )
			$output['resources'] = $this->one_zero( $input['resources'] );

		// Tips
		if ( isset( $input['tips'] ) )
			$output['tips'] = $this->one_zero( $input['tips'] );

		// Things needed
		if ( isset( $input['things_needed'] ) )
			$output['things_needed'] = $this->one_zero( $input['things_needed'] );

		if ( !isset( $input['init'] ) ) {
			add_settings_error(
				'instantcontent_settings',
				esc_attr( 'settings_updated' ),
				__( 'Settings saved.', 'instant-content' ),
				'updated'
			);
		}

		// Bit of a hack, but prevents setting notices
		// from displaying twice on first run
		if ( !$options ) {
			$output['init'] = true;
		}

		return $output;
	}

	/**
	 * Helper function for sanitizing checkboxes
	 *
	 * @since 1.0.0
	 * @param string checkbox value
	 * @return string sanitized checkbox value
	 */
	function one_zero( $input ) {
		return (int) (bool) $input;
	}

	public function notices() {
		settings_errors( 'instantcontent_settings' );
	}

	/**
	 * Activate License
	 *
	 * @since 1.0.0
	 * @param string License key
	 * @return string "Active" or "Inactive"
	 */
	function activate_license( $license ) {

		// Data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( INSTANT_CONTENT_PLUGIN )
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, INSTANT_CONTENT_UPDATE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// Make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// Decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data ) {
			// $license_data->license will be either "active" or "inactive"
			return $license_data->license;
		} else {
			return 'error';
		}
	}

}
