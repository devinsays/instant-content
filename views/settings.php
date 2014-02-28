<?php
/**
 * Configuration page for Instant Content
 *
 * @package   Instant Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */
?>

<div class="wrap">
	<?php screen_icon( 'post' ); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php $this->view( 'navigation' ); ?>

	<?php settings_errors( 'instantcontent_settings', false, true ); ?>

	<div class="wrap">
		<form method="post" action="options.php">

		<?php
		settings_fields( 'instant_content_settings' );
		$options = get_option( 'instant_content', false );

		$terms         = isset( $options['terms'] ) ? $options['terms'] : 0;
		$license       = isset( $options['license'] ) ? $options['license'] : '';
		$status        = isset( $options['license_status'] ) ? $options['license_status'] : '';
		$header        = isset( $options['header'] ) ? $options['header'] : 'h3';
		$resources     = isset( $options['resources'] ) ? $options['resources'] : 0;
		$tips          = isset( $options['tips'] ) ? $options['tips'] : 0;
		$things_needed = isset( $options['things_needed'] ) ? $options['things_needed'] : 0;

		// If options haven't been set, default these checkbox values to true
		if ( !$options ) {
			$resources = 1;
			$tips = 1;
			$things_needed = 1;
		}
		?>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><h3><?php _e( 'Settings', 'instant-content' ); ?></h3></th>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'License Key', 'instant-content' ); ?>
					</th>
					<td>
						<input id="instantcontent-license" name="instant_content[license]" type="text" style="width:240px" value="<?php esc_attr_e( $license ); ?>" />
						<p><label class="description" for="instant_content[license]">
						<?php if ( $status == 'valid' ) {
							_e( 'Your license is valid.', 'instant-content' );
						} elseif ( 'error' === $status ) {
							_e( 'There was an error validating the license.  Try saving again.', 'instant-content' );
						} else {
							printf(
								__( 'Register for a <a href="%s">free license key</a>.', 'instant-content' ),
								esc_url( 'http://instantcontent.me' )
							);
						} ?>
						</label></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<?php _e( 'License Terms', 'instant-content' ); ?>
					</th>
					<td>
						<iframe id="iframe-terms" class="clearfix" src="<?php echo esc_url( plugins_url( 'service-license.html?width=800', __FILE__ ) ); ?>">
						</iframe>
						<label for="instant_content[terms]">
						<input type="checkbox" id="instant_content[terms]" name="instant_content[terms]" value="1"<?php checked( $terms ); ?> />
						<?php printf(
							__( 'I have read and agree to the <a class="thickbox" href="%s">license terms</a>.', 'instant-content' ),
							esc_url( plugins_url( 'service-license.html?width=800', __FILE__ ) )
							);
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><h3><?php _e( 'Import Settings', 'instant-content' ); ?></h3></th>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'Header Markup', 'instant-content' ); ?>
					</th>
					<td>
						<select name="instant_content[header]">
							<option value="h1"<?php selected( $header, 'h1' ); ?>><?php _e( 'Heading 1', 'instant-content' ); ?></option>
							<option value="h2"<?php selected( $header, 'h2' ); ?>><?php _e( 'Heading 2', 'instant-content' ); ?></option>
							<option value="h3"<?php selected( $header, 'h3' ); ?>><?php _e( 'Heading 3', 'instant-content' ); ?></option>
							<option value="h4"<?php selected( $header, 'h4' ); ?>><?php _e( 'Heading 4', 'instant-content' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'References and Resources', 'instant-content' ); ?>
					</th>
					<td>
						<label for="instant_content[resources]">
						<input type="checkbox" id="instant_content[resources]" name="instant_content[resources]" value="1"<?php checked( $resources ); ?> />
						<?php _e( 'Import if present.', 'instant-content' ); ?>
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'Tips and Warnings', 'instant-content'); ?>
					</th>
					<td>
						<label for="instant_content[tips]">
						<input type="checkbox" id="instant_content[tips]" name="instant_content[tips]" value="1"<?php checked( $tips ); ?> />
						<?php _e( 'Import if present.', 'instant-content' ); ?>
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'Things Needed', 'instant-content' ); ?>
					</th>
					<td>
						<label for="instant_content[things_needed]">
						<input type="checkbox" id="instant_content[things_needed]" name="instant_content[things_needed]" value="1"<?php checked( $things_needed ); ?> />
						<?php _e( 'Import if present.', 'instant-content' ); ?>
						</label>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button(); ?>
	</form>

</div>
