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
	<?php screen_icon( 'post' ); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php $this->view( 'navigation' ); ?>

	<div class="updated inline below-h2 instant-content-updated"><p><img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" width="16"><?php _e( 'Loading library.', 'instant-content' ); ?></p></div>
	<?php
	$options = get_option( 'instant_content', false );
	$license = isset( $options['license'] ) ? $options['license'] : '';
	?>
	<input id="instant-content-license" type="hidden" value="<?php echo esc_attr( $license ); ?>">

	<div class="tablenav">
		<div class="tablenav-pages">
			<span class="displaying-num"></span>
			<span class="pagination-links">
			<button class="button prev-page" title="<?php esc_attr_e( 'Go to the previous page', 'instant-content' ); ?>" disabled="disabled">‹</button>
			<span class="paging-input"><span class="current-page"></span> <?php _e( 'of', 'instant-content' ); ?> <span class="total-pages"></span></span>
			<button class="button next-page" title="<?php esc_attr_e( 'Go to the next page', 'instant-content' ); ?>" disabled="disabled">›</button>
		</div>
	</div>

	<form action="<?php esc_url( menu_page_url( Instant_Content::SLUG . '-import' ) ); ?>" method="post" id="js-instant-content-library" >
		<input name="instant-content-import-key" type="hidden" value="" id="js-instant-content-import-key" />
		<table class="wp-list-table widefat fixed posts" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-cb check-column"></th>
					<th scope="col" class="manage-column column-title"><?php _e( 'Title', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-date"><?php _e( 'Purchase Date', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-import"><?php _e( 'Import', 'instant-content' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr class="table-footer-row" id="js-table-footer">
					<th scope="col" class="manage-column column-cb check-column"></th>
					<th scope="col" class="manage-column column-title"><?php _e( 'Title', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-date"><?php _e( 'Purchase Date', 'instant-content' ); ?></th>
					<th scope="col" class="manage-column column-import"><?php _e( 'Import', 'instant-content' ); ?></th>
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

</div>
