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
	<?php screen_icon(); ?>
	<h2><?php _e( 'Instant Content Settings', $this->slug  ); ?></h2>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url( 'edit.php?page=instant-content-search' ) ?>" class="nav-tab"><?php _e( 'Find Content', $this->slug ); ?></a>
		<a href="<?php echo admin_url( 'edit.php?page=instant-content-library' ) ?>" class="nav-tab"><?php _e( 'Library', $this->slug  ); ?></a>
		<a href="<?php echo admin_url( 'edit.php?page=instant-content-settings' ) ?>" class="nav-tab nav-tab-active"><?php _e( 'Settings', $this->slug  ); ?></a>
	</h2>

	<?php settings_errors( 'instantcontent_settings', false, true ); ?>

	<div class="wrap">
		<form method="post" action="options.php">

		<?php
		settings_fields( 'instant_content_settings' );
		$options = get_option( 'instant_content', false );
		$terms = isset( $options['terms'] ) ? $options['terms'] : '';
		$license = isset( $options['license'] ) ? $options['license'] : '';
		$status = isset( $options['license_status'] ) ? $options['license_status'] : '';
		$header = isset( $options['header'] ) ? $options['header'] : 'h3';
		$resources = isset( $options['resources'] ) ? $options['resources'] : 0;
		$tips = isset( $options['tips'] ) ? $options['tips'] : 0;
		$things_needed = isset( $options['things_needed'] ) ? $options['things_needed'] : 0;
		?>

		<input type="hidden" name="instant_content[license_status]" value="<?php echo $status; ?>">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><h3><?php _e( 'License Settings', $this->slug ); ?></h3></th>
				</tr>
				<th scope="row" valign="top">
						<?php _e( 'License Terms', $this->slug ); ?>
					</th>
					<td>
						<label for="instant_content[terms]">
						<input type="checkbox" name="instant_content[terms]" value="1" <?php checked( 1, $terms ); ?>">
						<?php _e( 'I have read and agree to the license terms.', $this->slug ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'License Key', $this->slug ); ?>
					</th>
					<td>
						<input id="instantcontent-license" name="instant_content[license]" type="text" style="width:240px" value="<?php esc_attr_e( $license ); ?>" />
						<p><label class="description" for="instant_content[license]">
						<?php if ( $status == 'valid' ) {
							_e( 'Your license is valid.', $this->slug );
						} else {
							_e( 'Register for a <a href="http://instantcontent.me">free license key</a>.', $this->slug );
						} ?>
						</label></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><h3><?php _e( 'Import Settings', $this->slug ); ?></h3></th>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'Header Markup', $this->slug ); ?>
					</th>
					<td>
						<select name="instant_content[header]">
							<option value="h1" <?php selected( $header, 'h1' ); ?>>Heading 1</option>
							<option value="h2" <?php selected( $header, 'h2' ); ?>>Heading 2</option>
							<option value="h3" <?php selected( $header, 'h3' ); ?>>Heading 3</option>
							<option value="h4" <?php selected( $header, 'h4' ); ?>>Heading 4</option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e('References and Resources', $this->slug ); ?>
					</th>
					<td>
						<input type="checkbox" name="instant_content[resources]" value="1" <?php checked( 1, $resources ); ?>">
						<p class="description"><?php _e( 'Some articles contain a list of references and resources for the article.  These are generally links.', $this->slug ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'Tips and Warnings', $this->slug); ?>
					</th>
					<td>
						<input type="checkbox" name="instant_content[tips]" value="1" <?php checked( 1, $tips ); ?>">
						<p class="description"><?php _e( 'Some articles contain tips and warnings.', $this->slug ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'Things Needed', $this->slug ); ?>
					</th>
					<td>
						<input type="checkbox" name="instant_content[things_needed]" value="1" <?php checked( 1, $things_needed ); ?>/>
						<p class="description"><?php _e( 'Some how-to articles contain a list of things needed to complete the project.', $this->slug ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button(); ?>
	</form>

</div>