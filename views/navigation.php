<?php
/**
 * Displays the navigation tabs at the top of each screen
 *
 * @package   Instant Content
 * @author    Demand Media <instantcontent@demandmedia.com>
 * @license   GPL-2.0+
 * @link      http://instantconent.me
 * @copyright 2013 Demand Media
 */
?>

<?php
$tab_class['search'] = 'nav-tab';
$tab_class['library'] = 'nav-tab';
$tab_class['cart'] = 'nav-tab';
$tab_class['settings'] = 'nav-tab';

$screen = get_current_screen();
echo substr( $screen->base, 27, 100 );

// Substring removes 'admin_page_instant-content-' from $pagenow
$tab_class[ substr( $screen->base, 27, 100 ) ] = 'nav-tab nav-tab-active';
?>

<h2 class="nav-tab-wrapper">
	<a href="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-search' ) ); ?>" class="<?php echo $tab_class['search']; ?>"><?php _e( 'Find Content', 'instant-content' ); ?></a>
	<a href="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-library' ) ); ?>" class="<?php echo $tab_class['library']; ?>"><?php _e( 'Library', 'instant-content' ); ?></a>
	<a href="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-cart' ) ); ?>" class="<?php echo $tab_class['cart']; ?>"><?php _e( 'Cart', 'instant-content' ); ?></a>
	<a href="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-settings' ) ); ?>" class="<?php echo $tab_class['settings']; ?>"><?php _e( 'Settings', 'instant-content' ); ?></a>
</h2>