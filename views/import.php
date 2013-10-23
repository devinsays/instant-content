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
	<h2><?php _e( 'Import Content', $this->slug  ); ?></h2>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url( 'edit.php?page=instant-content-search' ) ?>" class="nav-tab"><?php _e( 'Find Content', $this->slug  ); ?></a>
		<a href="<?php echo admin_url( 'edit.php?page=instant-content-library' ) ?>" class="nav-tab"><?php _e( 'Library', $this->slug  ); ?></a>
		<a href="<?php echo admin_url( 'edit.php?page=instant-content-settings' ) ?>" class="nav-tab"><?php _e( 'Settings', $this->slug  ); ?></a>
	</h2>

	<?php if ( isset( $_POST['instant-content-import-key'] ) ) { ?>
		<div class="updated inline below-h2 instant-content-updated"><p><img class="waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" alt="" width="16"><?php _e( 'Importing content.', $this->slug  ); ?></p></div>
		<input id="instant-content-import-key" type="hidden" value="<?php echo $_POST['instant-content-import-key']; ?>">
	<?php } ?>

	<div class="import-data"></div>

</div>