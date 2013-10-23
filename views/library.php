<?php
/**
 * Displays purchased content
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
	<h2><?php _e( 'Instant Content Library', $this->slug  ); ?></h2>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url( 'edit.php?page=instant-content-search' ) ?>" class="nav-tab"><?php _e( 'Find Content', $this->slug  ); ?></a>
		<a href="<?php echo admin_url( 'edit.php?page=instant-content-library' ) ?>" class="nav-tab nav-tab-active"><?php _e( 'Library', $this->slug  ); ?></a>
		<a href="<?php echo admin_url( 'edit.php?page=instant-content-settings' ) ?>" class="nav-tab"><?php _e( 'Settings', $this->slug  ); ?></a>
	</h2>

	<div class="updated inline below-h2 instant-content-updated"><p><img class="waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" alt="" width="16">Loading library.</p></div>
	<?php
	$options = get_option( 'instant_content', false );
	$license = isset( $options['license'] ) ? $options['license'] : '';
	?>
	<input id="instant-content-license" type="hidden" value="<?php echo $license; ?>">

	<form id="instant-content-library" action="edit.php?page=instant-content-import" method="post">
		<input id="instant-content-import-key" name="instant-content-import-key" type="hidden" value="">
		<table class="wp-list-table widefat fixed posts" cellspacing="0">
		<thead>
		<tr>
			<th scope="col" class="manage-column column-cb check-column"></th>
			<th scope="col" class="manage-column column-title">Title</th>
			<th scope="col" class="manage-column column-date">Purchase Date</th>
			<th scope="col" class="manage-column column-import">Import</th>
		</tr>
		</thead>
		<tbody id="results-table">
			<tr class="tr-info"><td class="no-border"></td><td colspan="3" class="no-border"></td></tr>
		</tbody>
		<tfoot>
		<tr id="table-footer">
			<th scope="col" class="manage-column column-cb check-column"></th>
			<th scope="col" class="manage-column column-title">Title</th>
			<th scope="col" class="manage-column column-date">Purchase Date</th>
			<th scope="col" class="manage-column column-import">Import</th>
		</tr>
		</tfoot>
		</table>
	</form>

</div>