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

	<h2 class="nav-tab-wrapper">
		<a href="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-search' ) ); ?>" class="nav-tab"><?php _e( 'Find Content', 'instant-content' ); ?></a>
		<a href="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-library' ) ); ?>" class="nav-tab"><?php _e( 'Library', 'instant-content' ); ?></a>
		<a href="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-cart' ) ); ?>" class="nav-tab nav-tab-active"><?php _e( 'Cart', 'instant-content' ); ?></a>
		<a href="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-settings' ) ); ?>" class="nav-tab"><?php _e( 'Settings', 'instant-content' ); ?></a>
	</h2>

	<ul>
		<li>This screen articles saved in cart.</li>
		<li>Allows user select articles they wish to purchase, and bulk purchase.</li>
		<li>Allows user to purchase all items saved in cart.</li>
		<li>Allows users to remove items from cart.</li>
		<li>Availability check should be done before user is taken to PayPal.</li>
		<li>Importer script will need to be updated to handle multiple articles.</li>
	</ul>

</div>
