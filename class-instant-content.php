<?php
/**
 * @package   Instant Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */

class InstantContent {

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

		// Register ajax actions
		add_action( 'wp_ajax_ajax_update_library', array( $this, 'ajax_update_library') );
		add_action( 'wp_ajax_instant_content_import', array( $this, 'instant_content_import') );

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

			wp_enqueue_script( $this->slug . '-admin-script', plugins_url( 'js/scripts.js', __FILE__ ), array( 'jquery' ), $this->version );

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

	/**
	 * Assembles post data from API and inserts the new draft post
	 *
	 * @since 0.1
	 */
	function instant_content_import() {

		if ( isset($_REQUEST) ) {

			$key = $_REQUEST['key'];
			$license = $_REQUEST['license'];

			$data = $this->fetch_content( $key, $license );

			// Exit function early if there is an error
			if ( isset( $data->error ) ) {
				echo $data;
				die();
			}

			// Template type
			$template = $data->template;

			// For post insertion
			$post['post_status'] = 'draft';
			$post['post_type'] = 'post';
			$post['post_title'] = $data->title;
			$post['post_content'] = $this->import_content( $data->content, $template );
			$post_id = wp_insert_post( $post, true );

			// Image import (not present in current version of API)
			if ( isset( $data->image->url ) && isset( $data->image->caption ) ) {
				$file = $data->image->url;
				$caption = $data->image->caption;
				$this->instant_sideload_image( $file, $post_id, $caption );
			}

			// To return to ajax function
			$ajax['id'] = $post_id;
			$ajax['title'] = $data->title;
			$ajax['summary'] = $data->content->summary;

			if ( $post_id ) {
				$ajax['draft_url'] = get_edit_post_link( $post_id, false );
				$ajax['msg'] = __( 'Content import successful.', $this->slug );
			} else {
				$ajax['msg'] = __( 'Unexpected error creating draft post.  Try again.', $this->slug );
			}
		} else {
			$ajax['id'] = 0;
			$ajax['msg'] = __( 'Unexpected error importing content.  Try again.', $this->slug );
		}

		echo json_encode( $ajax );

		// Always die in functions echoing ajax content
	   die();
	}

	/**
	 * Sideloads images into media library
	 *
	 * Images imports have been disabled in the API because of licensing issues
	 * but code has been kept in the hope we'll be able to add images later.
	 *
	 * @since 0.1
	 * @param string File URL
	 * @param number Post ID that image will be attached to
	 */
	function instant_sideload_image( $file, $post_id, $desc = null ) {
		if ( ! empty($file) ) {
			// Download file to temp location
			$tmp = download_url( $file );

			// Set variables for storage
			// Fix file filename for query strings
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
			$file_array['name'] = basename($matches[0]);
			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink($file_array['tmp_name']);
				$file_array['tmp_name'] = '';
			}

			// Do the validation and storage stuff
			$id = media_handle_sideload( $file_array, $post_id, $desc );
			// If error storing permanently, unlink
			if ( is_wp_error($id) ) {
				@unlink($file_array['tmp_name']);
				return $id;
			}

		}

		// Finally check to make sure the file has been saved, then set as featured image
		if ( ! empty($id) ) {
			set_post_thumbnail( $post_id, $id );
		}
	}

	/**
	 * Fetches content from the API
	 *
	 * @since 0.1
	 * @param string Article Key
	 * @param string Instant Content License Key
	 * @returns array New Article Data
	 */
	function fetch_content( $key, $license ) {

		$api = 'http://icstage.demandstudios.com/instant_content/get/article/content?json=';
		$url_args = array();
		$url_args['article_key'] = $key;
		$url_args['license_key'] = $license;
		$url =  $api . json_encode($url_args);

		$args = array(
			'timeout' => '60000',
		);

		$content = false;
		$result = wp_remote_get( $url, $args );

		if ( !is_wp_error( $result ) ) {
			// Translates JSON into a PHP array
			$content = json_decode( $result['body'] );
		} else {
			$content = json_encode( array( 'error' => ( $result->get_error_message() ) ) );
		}

		return $content;
	}

	/**
	 * Loops through $data returned from API to built post body
	 *
	 * @since 0.1
	 * @param array Article data
	 * @param string Type of template (currently not used)
	 */
	function import_content( $data, $template ) {

		$output = '';
		$content = '';
		$things_needed = '';
		$tips = '';
		$warnings = '';
		$references = '';
		$resources = '';

		// Defines header markup to be h1,h2, h3 or h4
		$options = get_option( 'instant_content' );
		$hopen = '<' . $options['header'] . '>';
		$hclose = '</' . $options['header'] . '>';

		if ( isset( $data->summary ) ) {
			$content .= '<p>' . $data->summary . '</p>';
		}

		if ( isset( $data->sections ) && $template ) :

		switch ( $template) {

			default:
				foreach ( $data->sections as $codeblock ) {
					if ( isset( $codeblock->title ) ) {
						$content .= $hopen . $codeblock->title . $hclose;
						if ( isset( $codeblock->content ) ) {
							$content .= '<p>' . $codeblock->content . '</p>';
						}
					}
					if ( isset( $codeblock->steps ) ) {
						foreach ( $codeblock->steps as $step ) {
							if ( isset( $step->title ) ) {
								$content .= $hopen . $step->title . $hclose;
							}
							if ( isset( $step->content ) ) {
								$content .= '<p>' . $step->content . '</p>';
							}
						}
					}
				}
				break;

		}

		endif;

		if ( isset( $options['things_needed'] ) ) {
			if ( isset( $data->things_needed ) && !empty( $data->things_needed ) ) {
				$things_needed = $this->get_content_as_list( $data->things_needed, 'things_needed', 'Things Needed' );
			}
		}

		if ( isset( $options['tips'] ) ) {
			if ( isset( $data->tips ) && !empty( $data->tips ) ) {
				$tips = $this->get_content_as_list( $data->tips, 'tips', 'Tips' );
			}

			if ( isset( $data->warnings ) && !empty( $data->warnings ) ) {
				$warnings = $this->get_content_as_list( $data->warnings, 'warnings', 'Warnings' );
			}
		}

		if ( isset ( $options['resources'] ) ) {

			if ( isset( $data->references ) && !empty( $data->references ) ) {
				$references = $this->get_content_as_list( $data->references, 'references', 'References' );
			}

			if ( isset( $data->resources ) && !empty( $data->resources ) ) {
				$resources = $this->get_content_as_list( $data->resources, 'resources', 'Resources' );
			}

		}

		$output .= $content;
		$output .= $things_needed;
		$output .= $tips;
		$output .= $warnings;
		$output .= $references;
		$output .= $resources;

		return $output;
	}

	/**
	 * Helper function to turn specific data in list formatted markup
	 *
	 * @since 0.1
	 * @param array Article data to be formatted as a list
	 * @param string Class name for the UL
	 * @param string Title for the markup section
	 * @return string Formatted markup
	 */
	function get_content_as_list( $data, $class, $title ) {
		$output = '';
		foreach (  $data as $item ) {
			$link = false;
			$output .= '<li>';
			if ( $item->url ) {
				$output .= '<a href="' . $item->url . '">';
			}
			$output .= $item->content;
			if ( $item->url ) {
				$output .= '</a>';
			}
			$output .= '</li>';
		}
		$output = '<ul class="' . $class . '">' . $output . '</ul>';
		$output = '<h3>' . $title . '</h3>' . $output;
		return $output;
	}

}