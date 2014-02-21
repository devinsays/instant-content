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

<?php $cart = get_option( 'instant_content_cart', false ); ?>

<?php if ( sizeof( $cart ) ) : ?>
	<div class="updated inline below-h2 instant-content-cart-notice">
		<p>
		<span class="dashicons dashicons-cart"></span>
		<?php printf( __( 'You have <span class="cart-count" data-count="%d">%d</span> items in your cart.', 'instant-content'), sizeof( $cart ), sizeof( $cart )); ?>
		<button type="button" class="button"><?php _e( 'Check Out', 'instant-content' ); ?></button>
		</p>
	</div>
<?php endif; ?>