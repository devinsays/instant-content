<?php
/**
 * Imports purchased content
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
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<h2 class="nav-tab-wrapper">
		<a href="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-search' ) ); ?>" class="nav-tab"><?php _e( 'Find Content', 'instant-content' ); ?></a>
		<a href="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-library' ) ); ?>" class="nav-tab"><?php _e( 'Library', 'instant-content' ); ?></a>
		<a href="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-settings' ) ); ?>" class="nav-tab"><?php _e( 'Settings', 'instant-content' ); ?></a>
	</h2>

	<div class="updated inline below-h2 instant-content-updated"><p><img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" width="16"><?php _e( 'Importing content.', 'instant-content' ); ?></p></div>
	<?php if ( isset( $_POST['instant-content-import-key'] ) ) { ?>
		<input type="hidden" value="<?php echo esc_attr( $_POST['instant-content-import-key'] ); ?>" id="js-instant-content-import-key" />
	<?php } ?>

	<div class="import-data"></div>

</div>
