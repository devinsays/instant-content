<?php
/**
 * Displays the content search page
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

	<?php
	$options = get_option( 'instant_content', false );
	$license = isset( $options['license'] ) ? $options['license'] : '';
	$status  = isset( $options['license_status'] ) ? $options['license_status'] : '';
	$terms   = isset( $options['terms'] ) ? $options['terms'] : false;

	if ( ( 'valid' !== $status ) ) {
		if ( ! $terms ) {
			$notice = __( 'Please enter a valid <a href="%1$s">license key</a> and <a href="%1$s">agree to terms</a> before purchasing content.', 'instant-content' );
		} else {
			$notice = __( 'Please enter a valid <a href="%s">license key</a> before purchasing content.', 'instant-content' );
		}
	} elseif ( ! $terms ) {
		$notice = __( 'Please <a href="%s">agree to terms</a> before purchasing content.', 'instant-content' );
	}

	if ( isset( $notice ) ) {
		?><div class="updated inline below-h2"><p><?php printf( $notice, esc_url( menu_page_url( Instant_Content::SLUG . '-settings', false ) ) ); ?></p></div><?php
	}
	?>

	<form id="search_box" class="search-box">
		<p><?php _e( 'Search for content to purchase:', 'instant-content' ); ?></p>
		<p>
			<label class="screen-reader-text" for="post-search-input"><?php _e( 'Search Posts:', 'instant-content' ); ?></label>
			<input type="search" id="js-post-search-input" name="instant-content-search" value="" />
			<button type="submit" id="js-search-submit" class="button"><?php _e( 'Search for Articles', 'instant-content' ); ?></button>
		</p>
	</form>

	<div class="tablenav">
		<div class="tablenav-pages">
			<span class="displaying-num"></span>
			<span class="pagination-links">
			<button class="button prev-page" title="<?php esc_attr_e( 'Go to the previous page', 'instant-content' ); ?>" disabled="disabled">‹</button>
			<span class="paging-input"><span class="current-page"></span> <?php _e( 'of', 'instant-content' ); ?> <span class="total-pages"></span></span>
			<button class="button next-page" title="<?php esc_attr_e( 'Go to the next page', 'instant-content' ); ?>" disabled="disabled">›</button>
		</div>
	</div>

	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="js-instant-content">
		<input type="hidden" name="cmd" value="_xclick" />
		<input type="hidden" name="business" value="instantcontent@demandmedia.com" />
		<input type="hidden" name="amount" value="" id="js-paypal-item-amount" />
		<input type="hidden" name="item_name" value="" id="js-paypal-item-name" />
		<input type="hidden" name="custom" value="" id="js-paypal-item-custom" />
		<input type="hidden" name="rm" value="1" />
		<input type="hidden" name="cbt" value="<?php esc_attr_e( 'Return to your website and complete import.', 'instant-content' ); ?>" />
		<input type="hidden" name="return" value="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-library' ) ); ?>" />
		<input type="hidden" name="cancel_return" value="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-search' ) ); ?>" />
		<input type="hidden" name="notify_url" value="<?php echo Instant_Content::API_BASE_URL; ?>process/ipn/message" />
		<input type="hidden" name="callback_version" value="1" />
		<input type="hidden" name="purchase-url" id="purchase-url" />
		<?php wp_nonce_field( 'instant-content'); ?>
		<table class="wp-list-table widefat fixed posts" cellspacing="0" />
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column"></th>
					<th scope="col" class="manage-column column-title"><?php _e( 'Title', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-summary"><?php _e( 'Summary', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-word_count" width="70px"><?php _e( 'Words', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-preview" width="70px"><?php _e( 'Preview', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-price" width="60px"><?php _e( 'Price', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-cart" width="100px"><?php _e( 'Cart', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-price" width="120px"><?php _e( 'Purchase', 'instant-content' ); ?></th>
				</tr>
			</thead>
			<tbody class="results-table" id="js-results-table">
				<tr>
					<td class="no-border"></td>
					<td colspan="6" class="no-border"><?php _e( 'Use the search tool above to find articles available for purchase.', 'instant-content' ); ?></td>
				</tr>
			</tbody>
		</table>
	</form>

	<p class="instant-content-terms">
		<?php
		printf(
			'<a href="%s">%s</a>',
			esc_url( 'http://www.instantcontent.me/contact' ),
			__( 'Instant Content Support', 'instant-content' )
		);
		?>
		<span> | </span>
		<?php
		printf(
			'<a href="%s" class="thickbox">%s</a>',
			esc_url( plugins_url( 'service-license.html?width=800', __FILE__ ) ),
			__( 'Instant Content Terms and Conditions', 'instant-content' )
		);
		?>
	</p>

</div>
