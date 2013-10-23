/**
 * Enables the search page
 * Much of this will be converted to backbone in the future.
 */
jQuery(document).ready(function($) {

	$("#search_box").click(function(event){
	  event.preventDefault();
	});

	$('#search-submit').click(function() {
		if ($('#post-search-input').val() == '') {
			return;
		}
		instantSearch( $('#post-search-input').val(), 0, 20 );
	});

	function instantSearch( terms, offset, max_items ) {

		var license = instantcontent.license_status;
		var terms = instantcontent.terms;

		var args = {};
		args['query_terms'] = terms;
		args['offset'] = offset;
		args['max_items'] = max_items;
		var url = 'http://icstage.demandstudios.com/instant_content/find/article/by_text?json=';
		var url = url + JSON.stringify(args);

		// Debug:
		// console.log('Search: ' + url);

		var preview = 'http://icstage.demandstudios.com/instant_content/get/article/for_preview?json=';

		$('.prev-page').addClass('disabled');
		$('.next-page').addClass('disabled');

		$('#results-table > tr').remove();
		$('#table-footer').hide();
		$('#results-table').append('<tr><td colspan="6">' + instantcontent.loading + '</td></tr>');

		var settings_url = $('#instant-content-settings-tab').attr('href');

		$.ajax({
		    name: 'by_text',
		    url: url,
		    timeout: 60000,
			dataType : "jsonp",
		    success: function(data, textStatus) {

		    	// Loop through the results and to build table markup
		    	$('.tablenav-pages').hide();
				$('#results-table > tr').remove();
				$.each(data.results, function(i, doc) {
					var preview_args = {};
					preview_args['article_key'] = doc['key'];
					preview_args['license_key'] = instantcontent.license;
					preview_args = JSON.stringify(preview_args);
					preview_url = preview + preview_args;
					thickbox = '?TB_iframe=true&width=600&height=550';
					var row = '<tr>';
					row += '<td></td>';
					row += '<td class="title">' + doc['title'] + '</td>';
					row += '<td class="summary">' + doc['summary'] + '</td>';
					row += '<td class="word-count">' + doc['word_count'] + '</td>';
					if ( license == 'valid' && terms ) {
						row += '<td><a class="thickbox" href="' + encodeURI(preview_url) + '&TB_iframe=true&width=800">Preview</a></td>';
					} else {
						row += '<td><a href="' + settings_url + '">Disabled</a></td>';
					}
					row += '<td class="price">$ ' + doc['price'] + '</td>';
					if ( license == 'valid' && terms ) {
						row += '<td><a onclick="javascript:purchaseContent(this);" class="button" data-title="' + doc['title'] + '" data-price="' + doc['price'] + '" data-key="' + doc['key'] + '">' + instantcontent.purchase + '</a></td>';
					} else {
						row += '<td><a onclick="javascript:licenseKey();" class="button" data-key="' + doc['key'] + '">' + instantcontent.purchase + '</a></td>';
					}
					row += '</tr>';
					$('#results-table').append(row);
				});

				// If there are results, append them to page
				if ( data.results.length > 0){
					$('#table-footer').show();

					// Remove previously set pagination bindings (if any)
					$( '.prev-page, .next-page').unbind();
					$( '.prev-page, .next-page').addClass('disabled');

					// Build pagination if needed
					if ( data.count > max_items ) {

						$('.tablenav-pages').show();
						$('.displaying-num').text(data.count + ' items');
						var current_page = Math.ceil( ( (offset / max_items) * 10 ) / 10) + 1;
						$('.current-page').text(current_page);
						var total_pages = Math.ceil( ( (data.count / max_items ) * 10) / 10);
						$('.total-pages').text(total_pages);

						if ( current_page > 1 ) {
							$('.prev-page').removeClass('disabled');
							$('.prev-page').on('click', function(e) {
								e.preventDefault();
								instantSearch( terms, (offset - max_items), max_items);
							});
						}

						if ( current_page < total_pages ) {
							$('.next-page').removeClass('disabled');
							$('.next-page').on('click', function(e) {
								e.preventDefault();
								instantSearch( terms, (offset + max_items), max_items);
							});
						}
					}

				} else {
					$('#results-table').append('<tr><td class="no-border"></td><td colspan="2" class="no-border">No results.</td></tr>');
				}

		    },
		    error: function( objAJAXRequest, strError ) {
		        $('#results-table').append('<tr><td class="no-border"></td><td colspan="2" class="no-border">Failed to connect to server.</td></tr>');
		    }
		});
	}

});

/**
 * Enables the purchase button
 */

function purchaseContent(control) {
	$ = jQuery;
	var title = $(control).attr('data-title');

	if ( confirm( 'You are about to purchase the article "' + title + '".\n\nYou will be taken to a PayPal screen to complete the payment, and then returned to your WordPress library.\n\nClick OK to continue.' ) ) {
		$('#paypal-item-name').val(title);
		$('#paypal-item-amount').val( jQuery(control).attr('data-price') );
		var custom = {};
		custom['article_keys'] = [ jQuery(control).attr('data-key') ];
		custom['license_key'] = instantcontent.license;
		custom['purchaser_domain'] = instantcontent.referrer;
		$('#paypal-item-custom').val( JSON.stringify(custom) );
		$('#instant-content').trigger('submit');
	};
}

function licenseKey() {
	alert( 'Please enter a valid license key and/or agree to plugin terms to purchase content.' );
}

/**
 * Fires off the PHP functions to import content
 */

jQuery(document).ready(function($) {

	// This function only runs on the import page
	if ( pagenow == 'posts_page_instant-content-import' ) {

		var key = $('#instant-content-import-key').attr('value');

		// This does the ajax request
		$.ajax({
			url: ajaxurl,
			data: {
				'action':'instant_content_import',
				'key' : key,
				'license' : instantcontent.license
			},
			dataType: 'JSON',
			success:function(data) {
			  	// console.log(data);
			  	update_import_screen( data );
			},
			error: function(errorThrown){
				console.log('errorThrown');
			    console.log(errorThrown);
			}
		});
	}

	function update_import_screen( data ) {
		// console.log(data);
		if ( data.id == 0 ) {
			$('.instant-content-updated p').html( data.msg );
		} else {
			$('.instant-content-updated p').html( data.msg + '  <a href="' + data.draft_url + '">Edit Post</a>' );
			$('.import-data').append('<h1>' + data.title + '</h1>');
			$('.import-data').append('<p>' + data.summary + '</p>');
			$('.import-data').append('<p><a href="' + data.draft_url + '">View Draft Post</a></p>');
		}
	}

});

/**
 * Displays all the purchased articles in a user library
 * Much of this will be converted to backbone in the future.
 */

jQuery(document).ready(function($) {

	if ( pagenow == 'posts_page_instant-content-library' ) {

		var url = 'http://icstage.demandstudios.com/instant_content/get/article/all_purchased?json={ "license_key":"' + instantcontent.license + '", "offset":0, "max_items":1000}';

		// Debug
		// console.log('Library Query: ' + url);

		$.ajax({
		    name: 'all_purchased',
		    url: url,
		    timeout: 5000,
			dataType : "jsonp",
			statusCode: {
		    	500: function() {
		    		console.log('500');
		        	$('.instant-content-updated p').html('License key is invalid.');
		        }
		    },
		    success: function(data, textStatus) {
				$('#results-table > tr').remove();
				$('#total_count').text('Displaying ' + data.results.length + ' / ' + data.count + ' results.').show();

				if ( data.results.length > 0) {

					var results = '';

					$.each(data.results, function(i, doc) {
						var date = doc['date']['text'];
						date = date.replace('datetime(','');
						date = date.replace(' UTC)','');
						var row = '<tr>';
						row += '<td></td>';
						row += '<td class="title">' + doc['title'] + '</td>';
						row += '<td class="date">' + date + '</td>';
						row += '<td><a class="button import-content" href="#" data-key="' + doc['key'] + '">' + instantcontent.import + '</a></td>';
						row += '</tr>';
						results += row;
					});

					$('#results-table').append(results);
					$('#table-footer').show();
					$('.instant-content-updated p').html('Library loaded.');

					// When Import Button is Clicked
					$('.import-content').on( 'click', function(e) {
						e.preventDefault();
						$('#instant-content-import-key').val( $(this).attr('data-key') );
						$('#instant-content-library').trigger('submit');
					});

				} else {
					$('.instant-content-updated p').html('No purchases.');
				}
		    },
		    error: function( objAJAXRequest, strError ) {
		        $('.instant-content-updated p').html('Failed to connect to the server.');
		    }
		});

	}

});