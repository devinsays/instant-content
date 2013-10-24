<?php
/**
 * Importer methods for Instant Content plugin.
 *
 * @package   Instant Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */

class Instant_Content_Importer {

	/**
	 * Unique identifier
	 *
	 * @since 0.1
	 * @var string
	 */
	protected $slug = 'instantcontent';

	/**
	 * Add the ajax hooks for the importer
	 *
	 * @since 0.1
	 */
	public function init() {

		// Register ajax actions
		add_action( 'wp_ajax_instant_content_import', array( $this, 'instant_content_import') );

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