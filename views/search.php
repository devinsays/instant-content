<?php
/**
 * Displays the content search page
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
	<h2><?php _e( 'Instant Content', $this->slug  ); ?></h2>

	<?php
	$options = get_option( 'instant_content', false );
	$license = isset( $options['license'] ) ? $options['license'] : '';
	$status = isset( $options['license_status'] ) ? $options['license_status'] : '';
	$terms = isset( $options['terms'] ) ? $options['terms'] : 0;
	$settings_page = admin_url( 'edit.php?page=instant-content-settings' );
	?>

	<?php if ( ( $status != 'valid' ) && !($terms) ) { ?>
	<div class="updated inline below-h2"><p>Please enter a valid <a href="<?php echo  $settings_page ?>">license key</a> and <a href="<?php echo  $settings_page ?>">agree to terms</a> before purchasing content.</p></div>
	<?php } elseif ( ( $status == 'valid' ) && !($terms) ) { ?>
		<div class="updated inline below-h2"><p>Please <a href="<?php echo  $settings_page ?>">agree to terms</a> before purchasing content.</p></div>
	<?php } elseif ( ( $status != 'valid' ) && $terms ) { ?>
		<div class="updated inline below-h2"><p>Please enter a valid <a href="<?php echo  $settings_page ?>">license key</a> before purchasing content.</p></div>
	<?php } ?>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url( 'edit.php?page=instant-content-search' ); ?>" class="nav-tab nav-tab-active"><?php _e( 'Find Content', $this->slug  ); ?></a>
		<a href="<?php echo admin_url( 'edit.php?page=instant-content-library' ); ?>" class="nav-tab"><?php _e( 'Library', $this->slug  ); ?></a>
		<a id="instant-content-settings-tab" href="<?php echo admin_url( 'edit.php?page=instant-content-settings' ) ?>" class="nav-tab"><?php _e( 'Settings', $this->slug ); ?></a>
	</h2>

	<div class="tablenav top">

		<form id="search_box">
			<p><?php _e( 'Search for content to purchase:', $this->slug  ); ?></p>
			<p class="search-box">
				<label class="screen-reader-text" for="post-search-input"><?php _e( 'Search Posts:', $this->slug  ); ?></label>
				<input type="search" id="post-search-input" name="s" value="">
				<input type="submit" name="" id="search-submit" class="button" value="Search for Articles">
			</p>
		</form>

		<div class="tablenav-pages">
			<span class="displaying-num"></span>
			<span class="pagination-links">
			<a class="prev-page disabled" title="Go to the previous page" href="">‹</a>
			<span class="paging-input"><span class="current-page"></span> of <span class="total-pages"></span></span>
			<a class="next-page disabled" title="Go to the next page" href="#">›</a>
		</div>
	</div>

	<form id="instant-content" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="instantcontent@demandmedia.com">
		<input type="hidden" id="paypal-item-amount" name="amount" value="">
		<input type="hidden" id="paypal-item-name" name="item_name" value="">
		<input type="hidden" id="paypal-item-custom" name="custom" value="">
		<input type="hidden" name="rm" value="1">
		<input type="hidden" name="cbt" value="Return to your website and complete import.">
		<input type="hidden" name="return" value="<?php echo admin_url( 'edit.php?page=instant-content-library' ); ?>">
		<input type="hidden" name="cancel_return" value="<?php echo admin_url( 'edit.php?page=instant-content-search' ); ?>">
		<input type="hidden" name="notify_url" value="https://icstage.demandstudios.com/instant_content/process/ipn/message">
		<input type="hidden" name="callback_version" value="1">
		<?php wp_nonce_field( $this->slug ); ?>
		<input id="purchase-url" name="purchase-url" type="hidden">
		<table class="wp-list-table widefat fixed posts" cellspacing="0">
		<thead>
		<tr>
			<th scope="col" id="cb" class="manage-column column-cb check-column"></th>
			<th scope="col" id="title" class="manage-column column-title"><?php _e( 'Title', $this->slug  ); ?></th>
			<th scope="col" id="summary" class="manage-column column-summary"><?php _e( 'Summary', $this->slug  ); ?></th>
			<th scope="col" id="word_count" class="manage-column column-word_count" width="100px"><?php _e( 'Word Count', $this->slug  ); ?></th>
			<th scope="col" id="price" class="manage-column column-preview" width="100px"><?php _e( 'Preview', $this->slug  ); ?></th>
			<th scope="col" id="price" class="manage-column column-price" width="100px"><?php _e( 'Price', $this->slug  ); ?></th>
			<th scope="col" id="purchase" class="manage-column column-price" width="100px"><?php _e( 'Purchase', $this->slug  ); ?></th>
		</tr>
		</thead>
		<tbody id="results-table">
			<tr><td class="no-border"></td><td colspan="6" class="no-border">Use the search tool above to find articles available for purchase.</td></tr>
		</tbody>
		<tfoot>
		<tr id="table-footer">
			<th scope="col" id="cb" class="manage-column column-cb check-column"></th>
			<th scope="col" id="title" class="manage-column column-title"><?php _e( 'Title', $this->slug  ); ?></th>
			<th scope="col" id="summary" class="manage-column column-summary"><?php _e( 'Summary', $this->slug  ); ?></th>
			<th scope="col" id="word_count" class="manage-column column-word_count" width="100px"><?php _e( 'Word Count', $this->slug  ); ?></th>
			<th scope="col" id="price" class="manage-column column-preview" width="100px"><?php _e( 'Preview', $this->slug  ); ?></th>
			<th scope="col" id="price" class="manage-column column-price" width="100px"><?php _e( 'Price', $this->slug  ); ?></th>
			<th scope="col" id="purchase" class="manage-column column-price" width="100px"><?php _e( 'Purchase', $this->slug  ); ?></th>
		</tr>
		</tfoot>
		</table>
	</form>

</div>