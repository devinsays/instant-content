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

	<div class="updated inline below-h2 instant-content-updated"><p><?php _e( 'You have (n) items in your cart.', 'instant-content' ); ?>  <span><a href="#"><?php _e( 'Check Out.', 'instant-content' ); ?></a></span></p></div>

	<?php $cart = get_option( 'instant_content_cart' ); ?>

	<?php if ( $cart ) : ?>
	<form action="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-cart' ) ); ?>" method="post" id="js-instant-content-cart" >
		<table class="wp-list-table widefat fixed posts" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-cb check-column"></th>
					<th scope="col" class="manage-column column-title"><?php _e( 'Title', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-price"><?php _e( 'Price', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-remove"><?php _e( 'Cart', 'instant-content' ); ?></th>
				</tr>
			</thead>

			<tbody class="results-table" id="js-results-table">

			<?php foreach( $cart as $article ) { ?>
			<tr><td></td><td class="title"><?php echo $article['title']; ?></td><td class="price">$<?php echo $article['price']; ?></td><td><button type="button" class="button remove" data-key="<?php echo $article['key']; ?>"><?php _e( 'Remove', 'instant-content' ); ?></button></td></tr>
			<?php } ?>

			</tbody>

			<tfoot>
				<tr class="table-footer-row" id="js-table-footer">
					<th scope="col" class="manage-column column-cb check-column"></th>
					<th scope="col" class="manage-column column-title"><?php _e( 'Title', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-price"><?php _e( 'Price', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-remove"><?php _e( 'Cart', 'instant-content' ); ?></th>
				</tr>
			</tfoot>
			<tbody class="results-table" id="js-results-table">
				<tr class="tr-info">
					<td class="no-border"></td>
					<td colspan="3" class="no-border"></td>
				</tr>
			</tbody>
		</table>
	</form>
	<?php endif; ?>

	<ul>
		<li>This screen articles saved in cart.</li>
		<li>Allows user select articles they wish to purchase, and bulk purchase.</li>
		<li>Allows user to purchase all items saved in cart.</li>
		<li>Allows users to remove items from cart.</li>
		<li>Availability check should be done before user is taken to PayPal.</li>
		<li>Importer script will need to be updated to handle multiple articles.</li>
	</ul>

</div>
