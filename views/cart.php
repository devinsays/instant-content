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

<div class="wrap">
	<?php screen_icon( 'post' ); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php $this->view( 'navigation' ); ?>

	<?php $this->view( 'cart-notice' ); ?>

	<?php $cart = get_option( 'instant_content_cart', false ); ?>

	<?php if ( $cart ) { ?>
	<form action="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-cart' ) ); ?>" method="post" id="js-instant-content-cart" >
		<table class="wp-list-table widefat fixed posts" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-cb check-column"></th>
					<th scope="col" class="manage-column column-title"><?php _e( 'Title', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-price" width="100px"><?php _e( 'Price', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-remove" width="200px"><?php _e( 'Cart', 'instant-content' ); ?></th>
				</tr>
			</thead>

			<tbody class="results-table" id="js-results-table">

			<?php foreach( $cart as $article ) { ?>
			<tr>
				<td></td>
				<td class="title"><?php echo $article['title']; ?></td>
				<td class="price">$<?php echo $article['price']; ?></td>
				<td><button type="button" class="button remove" data-key="<?php echo $article['key']; ?>"><?php _e( 'Remove', 'instant-content' ); ?></button></td>
			</tr>
			<?php } ?>

			</tbody>

		</table>
	</form>
	<?php } else { ?>
		<div class="updated inline below-h2 instant-content-cart-notice">
		<p>
		<span class="instant-content-cart-icon dashicons dashicons-cart"></span>
		<span class="instant-content-cart-message"><?php _e( 'There are no items in your cart.', 'instant-content' ); ?></span>
		</p>
		</div>
	<?php } ?>

</div>