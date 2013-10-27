<?php
/**
 * Importer methods for Instant Content plugin.
 *
 * @package   Instant_Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */

/**
 * Importer admin page.
 *
 * @package Instant_Content
 * @author  Demand Media <instantcontent@demandmedia.com>
 */
class Instant_Content_Admin_Importer extends Instant_Content_Admin {

	/**
	 * Hook in the methods for the importer page.
	 *
	 * @since 0.1.0
	 */
	public function init() {
		parent::init();

		// Register ajax actions
		add_action( 'wp_ajax_instant_content_import', array( $this, 'instant_content_import') );
	}

	/**
	 * Register this admin page with WordPress.
	 *
	 * @since 0.1.0
	 */
	public function add_admin_page() {
		$this->importer_hook = add_submenu_page(
			'options.php',
			__( 'Instant Content Import', 'instant-content' ),
			__( 'Instant Content Import', 'instant-content' ),
			'edit_posts',
			Instant_Content::SLUG . '-import',
			array( $this, 'display' )
		);

		// Register contextual help
		add_action( 'load-' . $this->importer_hook, array( $this, 'help' ) );
	}

	/**
	 * Render the page contents.
	 *
	 * @since 0.1.0
	 */
	public function display() {
		include 'views/import.php';
	}

	/**
	 * Populate contextual help.
	 *
	 * @since 0.1.0
	 */
	public function help() {
		$screen = get_current_screen();
		$importer_help =
			'<p>'  . __( 'Some help text here about the automatic importing on this page.', 'instant-content' ) . '</p>';

		$screen->add_help_tab(
			array(
				'id'      => $this->importer_hook,
				'title'   => __( 'Instant Content Importer', 'instant-content' ),
				'content' => $importer_help,
			)
		);

		// Add help sidebar
		parent::help();
	}

	/**
	 * Assemble post data from API and inserts the new draft post.
	 *
	 * @since 0.1.0
	 */
	public function instant_content_import() {
		if ( ! isset( $_POST['key'], $_POST['license'] ) ) {
			$ajax['id'] = 0;
			$ajax['msg'] = __( 'Unexpected error importing content. Try again.', 'instant-content' );
			echo json_encode( $ajax );
			die(0);
		}

		$key = $_POST['key'];
		$license = $_POST['license'];

		$data = $this->fetch_content( $key, $license );

		// Exit function early if there is an error
		if ( isset( $data->error ) ) {
			echo $data;
			die(0);
		}

		// For post insertion
		$new_post = array(
			'post_status'  => 'draft', // default
			'post_type'    => 'post', // default
			'post_title'   => $data->title,
			'post_content' => $this->format_content( $data->content, $data->template ),
		);

		// No need to sanitize the above fields, as wp_insert_post() already calls sanitize_post()
		// on all the data merged with the defaults.

		$post_id = wp_insert_post( $new_post );

		// Image import (not present in current version of API)
		if ( isset( $data->image->url ) && isset( $data->image->caption ) ) {
			$file = $data->image->url;
			$caption = $data->image->caption;
			$this->instant_sideload_image( $file, $post_id, $caption );
		}

		// To return to ajax function
		$ajax = array(
			'id'      => $post_id,
			'title'   => $data->title,
			'summary' => $data->content->summary,
		);

		if ( $post_id ) {
			$ajax['draft_url'] = get_edit_post_link( $post_id, false );
			$ajax['msg'] = __( 'Content import successful.', 'instant-content' );
		} else {
			$ajax['msg'] = __( 'Unexpected error creating draft post. Try again.', 'instant-content' );
		}

		echo json_encode( $ajax );

		// Always die in functions echoing ajax content
	   die();
	}

	/**
	 * Fetch content from the API.
	 *
	 * @since 0.1.0
	 *
	 * @param string Article key.
	 * @param string Instant Content license key.
	 *
	 * @returns array New Article data.
	 */
	function fetch_content( $key, $license ) {
		$url = $this->get_content_fetch_url( $key, $license );

		$args = array(
			'timeout' => '60000',
		);

		$content = false;
		$result = wp_remote_get( $url, $args ); // esc_url_raw() does NOT work here - server responds with 500.

		if ( ! is_wp_error( $result ) ) {
			// Translates JSON into a PHP array
			$content = json_decode( $result['body'] );
		} else {
			$content = json_encode( array( 'error' => ( $result->get_error_message() ) ) );
		}

		return $content;
	}

	/**
	 * Get the URL for fetching the content.
	 *
	 * @since 0.1.0
	 *
	 * @param  string $key     Article key.
	 * @param  string $license Instant Content license key.
	 *
	 * @return string          URL
	 */
	function get_content_fetch_url( $key, $license ) {
		$api = 'http://icstage.demandstudios.com/instant_content/get/article/content?json=';
		$url_args = array(
			'article_key' => $key,
			'license_key' => $license
		);
		return $api . json_encode( $url_args );
	}

	/**
	 * Loops through $data returned from API to built post body
	 *
	 * @since 0.1.0
	 *
	 * @param array Article data.
	 * @param string Type of template (currently not used).
	 */
	function format_content( $data, $template ) {
		$content = $this->build_paragraph( $data->summary );

		// if ( isset( $data->sections ) && $template ) :
		if ( isset( $data->sections ) ) {
			// switch ( $template ) {
			// 	default:
					foreach ( $data->sections as $codeblock ) {
						$content .= $this->build_header( $codeblock->title );
						if ( isset( $codeblock->content ) ) {
							$content .= $this->build_paragraph( $codeblock->content );
						}
						if ( isset( $codeblock->steps ) ) {
							foreach ( $codeblock->steps as $step ) {
								$content .= $this->build_header( $step->title );
								if ( isset( $step->content ) ) {
									$content .= $this->build_paragraph( $step->content );
								}
							}
						}
					}
			// 		break;
			// }
		}

		$content .= $this->build_listables( $data );

		return $content;
	}

	/**
	 * Build paragraph.
	 *
	 * @since 0.1.0
	 *
	 * @param  string $summary Content summary.
	 *
	 * @return string          Summary in markup, or empty string if summary is empty.
	 */
	protected function build_paragraph( $summary ) {
		if ( isset( $summary ) ) {
			return '<p>' . $summary . '</p>';
		}
		return '';
	}

	/**
	 * Wrap a title in chosen heading markup.
	 *
	 * @since 0.1.0
	 *
	 * @param  string $title Content title or subtitle.
	 *
	 * @return string Markup.
	 */
	protected function build_header( $title ) {
		if ( ! $title ) {
			return '';
		}

		// Defines header markup to be h1,h2, h3 or h4
		$options = get_option( 'instant_content' );
		$hopen = '<' . $options['header'] . '>';
		$hclose = '</' . $options['header'] . '>';

		return $hopen . $title . $hclose;
	}

	protected function build_listables( $data ) {
		$options = get_option( 'instant_content' );
		// Setup listable extras
		$extras = array(
			'things_needed' => array(
				'things_needed' => __( 'Things Needed', 'instant-content' ),
			),
			'tips' => array(
				'tips'     => __( 'Tips', 'instant-content' ),
				'warnings' => __( 'Warnings', 'instant-content' )
			),
			'resources' => array(
				'references' => __( 'References', 'instant-content' ),
				'resources'  => __( 'Resources', 'instant-content' ),
			),
		);

		$lists = array();
		foreach( $extras as $option_key => $data_properties ) {
			if ( ! isset( $options[$option_key] ) ) {
				continue;
			}
			foreach ( $data_properties as $property => $label ) {
				if ( isset( $data->$property ) ) {
					$lists[] = $this->build_list( $data->$property, $label );
				}
			}
		}

		return implode( '', $lists );
	}

	/**
	 * Return content in list formatted markup.
	 *
	 * @since 0.1.0
	 *
	 * @param array Article data to be formatted as a list.
	 * @param string Title for the markup section.
	 *
	 * @return string Formatted markup.
	 */
	function build_list( $data, $title ) {
		if ( ! $data ) {
			return '';
		}

		foreach ( $data as $item ) {
			$items[] = '<li>' . $this->maybe_wrap_in_link( $item->content, $item->url ) . '</li>';
		}

		$class = strtolower( $title );
		$list = '<ul class="' . esc_attr( sanitize_html_class( $class ) ) . '">' . implode( '', $items ) . '</ul>';

		return '<h3>' . $title . '</h3>' . $list;
	}

	/**
	 * Maybe wrap content in a link, if the link is non-empty.
	 *
	 * @since 0.1.0
	 *
	 * @param  [type] $content [description]
	 * @param  [type] $link    [description]
	 *
	 * @return [type]          [description]
	 */
	protected function maybe_wrap_in_link( $content, $link ) {
		if ( ! $link )
			return $content;
		return '<a href="' . esc_url( $link ) . '">' . esc_html( $content ) . '</a>';
	}

	/**
	 * Sideload images into media library.
	 *
	 * Images imports have been disabled in the API because of licensing issues
	 * but code has been kept in the hope we'll be able to add images later.
	 *
	 * @since 0.1.0
	 *
	 * @param string File URL.
	 * @param number Post ID that image will be attached to.
	 */
	function instant_sideload_image( $file, $post_id, $desc = null ) {
		if ( ! empty( $file ) ) {
			// Download file to temp location
			$tmp = download_url( $file );

			// Set variables for storage
			// Fix file filename for query strings
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
			$file_array['name'] = basename($matches[0]);
			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink( $file_array['tmp_name'] );
				$file_array['tmp_name'] = '';
			}

			// Do the validation and storage stuff
			$id = media_handle_sideload( $file_array, $post_id, $desc );
			// If error storing permanently, unlink
			if ( is_wp_error( $id ) ) {
				@unlink( $file_array['tmp_name'] );
				return $id;
			}

		}

		// Finally check to make sure the file has been saved, then set as featured image
		if ( ! empty( $id ) ) {
			set_post_thumbnail( $post_id, $id );
		}
	}

}
