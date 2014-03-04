<?php
/**
 * Displays content saved to cart
 *
 * @package   Instant Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */
?>

<?php

$cart = get_option( 'instant_content_cart', false );

$cart_class = 'instant-content-cart-visible';

if ( ! $cart ) {
	$cart_class = 'hidden';
}
?>

<div class="updated inline below-h2 instant-content-cart-notice <?php echo $cart_class; ?>">
	<p>
	<span class="instant-content-cart-icon dashicons dashicons-cart"></span>
	<span class="instant-content-cart-message"><?php printf( __( 'You have <span class="cart-count" data-count="%d">%d</span> items in your cart.', 'instant-content'), sizeof( $cart ), sizeof( $cart ) ); ?></span>
	<button type="button" class="button checkout"><?php _e( 'Check Out', 'instant-content' ); ?></button>
	</p>
</div>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="js-instant-content-cart">
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="instantcontent@demandmedia.com" />
	<input type="hidden" name="amount" value="" id="js-paypal-cart-amount" />
	<input type="hidden" name="item_name" value="" id="js-paypal-cart-item" />
	<input type="hidden" name="custom" value="" id="js-paypal-cart-custom" />
	<input type="hidden" name="rm" value="1" />
	<input type="hidden" name="cbt" value="<?php esc_attr_e( 'Return to your website and complete import.', 'instant-content' ); ?>" />
	<input type="hidden" name="return" value="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-library' ) ); ?>" />
	<input type="hidden" name="cancel_return" value="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-search' ) ); ?>" />
	<input type="hidden" name="notify_url" value="<?php echo Instant_Content::API_BASE_URL; ?>process/ipn/message" />
	<input type="hidden" name="callback_version" value="1" />
	<input type="hidden" name="purchase-url" id="purchase-url" />
</form>