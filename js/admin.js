/**
 * This file controls the behaviours within the Instant Content plugin.
 *
 * Note that while this version of the file include 'use strict'; at the function level,
 * the Closure Compiler version strips that away. This is fine, as the compiler may
 * well be doing things that are not use strict compatible.
 *
 * @author   Demand Media
 */

// ==ClosureCompiler==
// @compilation_level ADVANCED_OPTIMIZATIONS
// @output_file_name admin.min.js
// @externs_url http://closure-compiler.googlecode.com/svn/trunk/contrib/externs/jquery-1.8.js
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home

/**
 * Holds utility functions in an object to avoid polluting global namespace.
 *
 * @since 1.1.0
 *
 * @constructor
 */
window[ 'instantContent' ] = {

	/**
	 * Create a URL for the API, from a given end point and an arguments object.
	 *
	 * @since  1.1.0
	 *
	 * @function
	 *
	 * @param  {String} endPoint The part after the base API URL.
	 * @param  {Object} args     URL arguments object that will be sent as JSON.
	 *
	 * @return {String}          URL.
	 */
	buildApiUrl: function( endPoint, args ) {
		'use strict';
		return instantContentL10n.apiBaseUrl + endPoint + '?json=' + JSON.stringify( args );
	},

	/**
	 * Fetches data for cart checkout
	 *
	 * @since 1.3.0
	 *
	 * @function
	 *
	 * @param  {jQuery.event} event
	 */
	checkoutStart: function( event ) {
		event.preventDefault();

		if ( !instantContentL10n.hasValidLicenseAndTerms ) {
			alert( instantContentL10n.enterKeyPurchase );
			return;
		}

		jQuery( event.target ).prop( 'disabled', true );
		jQuery('.instant-content-cart-icon');
		jQuery('.instant-content-cart-message').before('<span class="spinner" style="display:block"></span>')
		jQuery('.instant-content-cart-message').html( instantContentL10n.checkingCart );

		instantContent.getCheckoutData();

	},

	/**
	 * Gets Checkout Data
	 *
	 * @since 1.3.0
	 *
	 * @function
	 */
	getCheckoutData: function() {

		jQuery.ajax({
	        url: ajaxurl,
	        data: {
	            'action':'instant_content_get_checkout_data',
	        },
	        success:function(cart) {
	        	instantContent.getArticleStatus( cart );
	        },
	        error: function(errorThrown){
	            console.log(errorThrown);
	        }
	    });

	},

	/**
	 * Gets the status of all articles in the cart from the API
	 *
	 * @since 1.3.0
	 *
	 * @function
	 *
	 * @param json cart - json data from the instant_content_cart option
	 */
	getArticleStatus: function( cart ) {

		var cart = jQuery.parseJSON( cart );
		var obj = { 'article_ids' : cart.keys }
		var ajaxurl = instantContent.buildApiUrl( 'get/article/status', obj );

		jQuery.ajax({
	        url: ajaxurl,
	        dataType : 'jsonp',
	        timeout : 60000,
	        success:function(status) {
	        	instantContent.verifyArticleStatus( status, cart );
	        },
	        error: function(errorThrown){
	            console.log(errorThrown);
	        }
	    });

	},

	/**
	 * Verifies the status of all articles in the cart
	 *
	 * @since 1.3.0
	 *
	 * @function
	 *
	 * @param json status - data with the status of all articles in the cart
	 * @param array cart - data from the instant_content_cart option
	 */
	verifyArticleStatus: function( status, cart ) {

		var remove = [];

		jQuery( status ).each( function( index, article ) {
			if ( article.status != 'available' ) {
				remove.push( article.article_id );
			}
		});

		if ( remove.length > 0 ) {
			instantContent.removeCartArticles( remove, true );
		} else {
			instantContent.checkoutConfirm( cart );
		}

	},

	/**
	 * Removes articles from the cart
	 *
	 * @since 1.3.0
	 *
	 * @function
	 *
	 * @param array articles - articles to be removed
	 */
	removeCartArticles: function( articles, notify ) {

		jQuery.ajax({
	        url: ajaxurl,
	        data: {
	            'action':'instant_content_bulk_remove_from_cart',
	            'keys'  : articles
	        },
	        success:function( data ) {
	        	if ( notify ) {
	        		instantContent.notifyRemovedArticles( jQuery.parseJSON( data ) );
	        	}
	        },
	        error: function(errorThrown){
	            console.log(errorThrown);
	        }
	    });

	},

	/**
	 * Notifies user of removed articles
	 *
	 * @since 1.3.0
	 *
	 * @function
	 *
	 * @param array data with articles removed and cart information
	 */
	notifyRemovedArticles: function( data ) {

		var confirmText;
		var articles = data.removed;

		jQuery( '.instant-content-cart-notice .spinner' ).hide();

		confirmText = 'These articles are no longer available in our content library and have been removed from your cart: \n\n';
		jQuery( articles ).each( function( index, article ) {
			confirmText += '- ' + article.title + '\n';
		});
		confirmText += '\n';

		if ( data.cart.length > 0 ) {
			confirmText += 'The remaining articles are still available.  Please click check out again if you wish to continue.';
		} else {
			confirmText += 'Sorry about the inconvience.';
		}
		var ask = alert( confirmText );
		window.location.reload();
	},

	/**
	 * Confirms checkout, sets form data
	 *
	 * @since 1.3.0
	 *
	 * @function
	 *
	 * @param  {jQuery.event} event
	 */
	checkoutConfirm: function( data ) {

		var custom, confirmText, item;

		confirmText = 'You are about to purchase ' + data.count + ' articles from your cart for $' + data.total_price + '.\n\n';
		confirmText += instantContentL10n.takenToPayPal + '\n\n' + instantContentL10n.clickOk;
		item = 'Instant Content Articles (' + data.count + ')';

		if ( confirm( confirmText ) ) {
			jQuery( '#js-paypal-cart-item' ).val( item );
			jQuery( '#js-paypal-cart-amount' ).val( data.total_price );
			custom = {
				article_keys: data.keys,
				license_key: instantContentL10n.license,
				purchaser_domain: instantContentL10n.referrer
			};
			jQuery( '#js-paypal-cart-custom' ).val( JSON.stringify( custom ) );
			jQuery( '#js-instant-content-cart' ).trigger( 'submit' );
		} else {
			jQuery( '.instant-content-cart-notice .spinner' ).hide();
		}
	}
};

/**
 * Holds search page values in an object to avoid polluting global namespace.
 *
 * @since 1.0.0
 *
 * @constructor
 */
window[ 'instantContentSearch' ] = {

	searchPagination: jQuery( 'button.prev-page, button.next-page' ),
	maxItems: 20,
	offset: 0,

	/**
	 * Check if search input is populated before doing the search.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 */
	searchIfPopulated: function( event ) {
		'use strict';
		event.preventDefault();
		instantContentSearch.queryTerms = jQuery( '#js-post-search-input' ).val();
		if ( '' === instantContentSearch.queryTerms ) {
			return;
		}
		instantContentSearch.instantSearch( 0 );
	},

	/**
	 * Do the search.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {Number} offset Number to start the search results from.
	 */
	instantSearch: function( offset ) {
		'use strict';

		var url, ajaxArgs, jqxhr;

		instantContentSearch.offset = offset;

		url = instantContentSearch.buildSearchUrl( offset );

		instantContentSearch.searchPagination.prop( 'disabled', true );

		jQuery( '#js-results-table > tr' ).remove();
		jQuery( '#js-table-footer' ).hide();
		jQuery( '#js-results-table' ).append( '<tr><td colspan="6">' + instantContentL10n.loading + '</td></tr>' );

		ajaxArgs = {
			name: 'by_text',
			url: url,
			dataType: 'jsonp',
			timeout: 60000
		};

		// Do the ajax call. Need .ajax() and not .get() as timeout property is set.
		jqxhr = jQuery.ajax( ajaxArgs );

		// On ajax success
		jqxhr.done( instantContentSearch.searchSuccess );

		// On ajax error. @todo check if this is actually possible to fire on cross-domain jsonp datatype?
		jqxhr.fail( instantContentSearch.failedToConnect );

	},

	/**
	 * Build a URL for the API to search a document.
	 *
	 * @since 1.1.0
	 *
	 * @function
	 *
	 * @param  {Number} offset Number to start the search results from.
	 *
	 * @return {String}            URL.
	 */
	buildSearchUrl: function( offset ) {
		'use strict';
		var urlArgs = {
			query_terms: instantContentSearch.queryTerms,
			offset: offset,
			max_items: instantContentSearch.maxItems
		};
		return instantContent.buildApiUrl( 'find/article/by_text', urlArgs );
	},

	/**
	 * Handle search results when considered a succesful ajax call.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {Object} data Response object.
	 */
	searchSuccess: function( data ) {
		'use strict';

		var rows = [];

		// Hide pagination, and clear existing result rows
		jQuery( '.tablenav-pages' ).hide();
		jQuery( '.tablenav' ).hide();
		jQuery( '#js-results-table > tr' ).remove();

		// If no results, abandon now.
		if ( data.results.length <= 0 ) {
			instantContentSearch.showMessage( instantContentL10n.noResults );
			return;
		}

		// Loop through the results and build table markup
		jQuery.each( data.results, function() {
			rows.push( instantContentSearch.buildSearchResultRow( this ) );
		});

		// Joining first means there should only be one append of a large string, instead of lots of smaller row strings appends,
		// which is better for performance.
		jQuery( '#js-results-table' ).append( rows.join( '' ) );

		jQuery( '#js-table-footer' ).show();

		// Remove previously set pagination bindings (if any)
		instantContentSearch.searchPagination.off( 'click.instantContent' ).prop( 'disabled', true );

		// @todo Fix the button titles, so it doesn't say "Go to next|previous page" when button is disabled.

		// Build pagination and show if needed
		instantContentSearch.rebuildPagination( data );
	},

	/**
	 * Assemble the markup for a single table row for the search results.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {Object} doc Document.
	 *
	 * @return {String}     Table row markup.
	 */
	buildSearchResultRow: function( doc ) {
		'use strict';
		var settingsUrl = instantContentL10n.settingsUrl,
			previewUrl  = instantContentSearch.buildPreviewUrl( doc.key ),
			row,
			cart = false;

		if ( instantContentL10n.cart.indexOf( doc.key ) > -1 ) {
			cart = true;
			row = '<tr class="item-in-cart">';
		} else {
			row = '<tr>';
		}
		row += '<td></td>';
		row += '<td class="title">' + doc.title + '</td>';
		row += '<td class="summary">' + doc.summary + '</td>';
		row += '<td class="word-count">' + doc.word_count + '</td>';

		if ( instantContentL10n.hasValidLicenseAndTerms ) {
			row += '<td><a class="thickbox" href="' + encodeURI( previewUrl ) + '&TB_iframe=true&width=800">Preview</a></td>';
		} else {
			row += '<td><a href="' + settingsUrl + '">' + instantContentL10n.disabled + '</a></td>';
		}

		row += '<td class="price">$ ' + doc.price + '</td>';
		if (cart) {
			row += '<td><a href="' + instantContentL10n.cartUrl + '">' + instantContentL10n.viewCart + '</a></td>';
		} else {
			row += '<td><button type="button" class="button addtocart" data-title="' + doc.title + '" data-price="' + doc.price + '" data-key="' + doc.key + '">' + instantContentL10n.addtocart + '</button></td>';
		}
		row += '<td><button type="button" class="button purchase" data-title="' + doc.title + '" data-price="' + doc.price + '" data-key="' + doc.key + '">' + instantContentL10n.purchasenow + '</button></td>';
		row += '</tr>';
		return row;
	},

	/**
	 * Build a URL for the API to preview a document.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {Number} articleKey Article unique ID.
	 *
	 * @return {String}            URL.
	 */
	buildPreviewUrl: function( articleKey ) {
		'use strict';
		var urlArgs = {
			article_key: articleKey,
			license_key: instantContentL10n.license
		};
		return instantContent.buildApiUrl( 'get/article/for_preview', urlArgs );
	},

	/**
	 * Callback for search fail.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 */
	failedToConnect: function() {
		'use strict';
		instantContentSearch.showMessage( instantContentL10n.failedToConnect );
	},

	/**
	 * Used to show a message.
	 *
	 * Currently shows the message inside the table, but this could be moved to outside the table if necessary.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {String} message Message to show.
	 */
	showMessage: function( message ) {
		'use strict';
		jQuery( '#js-results-table' )
			.append( '<tr><td class="no-border"></td><td colspan="2" class="no-border">' + message + '</td></tr>' );
	},

	/**
	 * Build or rebuild the behaviour for the pagination feature.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {Object} data Ajax response object.
	 */
	rebuildPagination: function( data ) {
		'use strict';
		if ( data.count < instantContentSearch.maxItems ) {
			return;
		}

		var currentPage, totalPages;

		// Update current page
		currentPage = Math.ceil( ( ( instantContentSearch.offset /  instantContentSearch.maxItems ) * 10  ) / 10 ) + 1;
		jQuery( '.current-page' ).text( currentPage );

		// Update total pages @todo Doesn't need to be done when changing pages.
		totalPages = Math.ceil( ( ( data.count / instantContentSearch.maxItems ) * 10 ) / 10 );
		jQuery( '.total-pages' ).text( totalPages );

		// Update number of results @todo Doesn't need to be done when changing pages.
		jQuery( '.displaying-num' ).text( data.count + ' ' + instantContentL10n[ 'items' ] );

		// Add behaviour for previous page button
		if ( currentPage > 1 ) {
			jQuery( '.prev-page' ).prop( 'disabled', false ).on( 'click.instantContent', function() {
				instantContentSearch.instantSearch( instantContentSearch.offset - instantContentSearch.maxItems );
			});
		}

		// Add behaviour for next page button
		if ( currentPage < totalPages ) {
			jQuery( '.next-page' ).prop( 'disabled', false ).on( 'click.instantContent', function() {
				instantContentSearch.instantSearch( instantContentSearch.offset + instantContentSearch.maxItems );
			});
		}
		jQuery( '.tablenav' ).show();
		jQuery( '.tablenav-pages' ).show();
	},

	/**
	 * Add the article to the cart for checkout
	 *
	 * @since 1.3.0
	 *
	 * @function
	 *
	 * @param  {jQuery.event} event
	 */
	addToCart: function( event ) {
		event.preventDefault();

		jQuery.ajax({
	        url: ajaxurl,
	        data: {
	            'action':'instant_content_add_to_cart',
	            'title' : jQuery( event.target ).data( 'title' ),
	            'price' : jQuery( event.target ).attr( 'data-price' ),
	            'key'   : jQuery( event.target ).data( 'key' ).toString()
	        },
	        success:function(data) {
	            instantContentSearch.cartNotice( data, event );
	        },
	        error: function(errorThrown){
	            console.log(errorThrown);
	        }
	    });
	},

	/**
	 * Displays notice that item was added to cart
	 *
	 * @since 1.3.0
	 *
	 * @function
	 *
	 * @param  {jQuery.event} event
	 */
	cartNotice: function( data, event ) {
		data = JSON.parse( data );

		if ( !data.update ) {
			// If the item was not successfully added to the cart
			noticeText = "There was an error adding this item to the cart.  Please refresh and try again.";
			jQuery('#search_box').after('<div class="updated inline below-h2 instant-content-updated"><p>' + noticeText + '</p></div>').hide().fadeIn();
		} else {
			// If the item was added to the cart
			jQuery('.nav-cart-hidden').removeClass('nav-cart-hidden');
			jQuery('.instant-content-cart-notice').removeClass('hidden');
			noticeText = instantContentL10n.addedtocart + data.title;
			if ( jQuery('.instant-content-updated').length > 0 ) {
				jQuery('.instant-content-updated p').fadeOut().text( noticeText ).fadeIn();
			} else {
				jQuery('#search_box').after('<div class="updated inline below-h2 instant-content-updated"><p>' + noticeText + '</p></div>').hide().fadeIn();
			}
			// Changes cart button to checkout button
			jQuery( event.target ).text( instantContentL10n.checkout ).unbind().on( 'click.instantContent', function( event ) {
				instantContent.checkoutStart( event );
			});
			if ( instantContentL10n.cart ) {
				var cart = JSON.parse( instantContentL10n.cart );
				if ( cart.constructor == Array) {
					cart.push( data.key );
				} else {
					cart = [ data.key ];
				}
				instantContentL10n.cart = JSON.stringify( cart );
			}
			jQuery( event.target ).parents('tr').css({ 'background' : '#fafafa' });
			jQuery('.cart-count').each( function(){
				var count =  jQuery(this).data('count') + 1;
				jQuery(this).data('count',count).text(count);
			});
		}
	},

	/**
	 * Update the purchase form with values before submitting.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {jQuery.event} event
	 */
	purchaseContent: function( event ) {
		'use strict';
		if ( !instantContentL10n.hasValidLicenseAndTerms ) {
			alert( instantContentL10n.enterKeyPurchase );
			return;
		}

		var custom,
			title = jQuery( event.target ).data( 'title' ),
			confirmText = instantContentL10n.aboutToPurchase + ' "' + title + '".\n\n' + instantContentL10n.takenToPayPal + '\n\n' + instantContentL10n.clickOk;

		if ( confirm( confirmText ) ) {
			// @todo Change cancel_return link so that it can return to the search screen with query terms already re-run?
			jQuery( '#js-paypal-item-name' ).val( title );
			jQuery( '#js-paypal-item-amount' ).val( jQuery( event.target ).attr( 'data-price' ) );
			custom = {
				article_keys: [ jQuery( event.target ).data( 'key' ).toString() ],
				license_key: instantContentL10n.license,
				purchaser_domain: instantContentL10n.referrer
			};
			jQuery( '#js-paypal-item-custom' ).val( JSON.stringify( custom ) );
			jQuery( '#js-instant-content' ).trigger( 'submit' );
		}
	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * Generally ordered with stuff that inserts new elements into the DOM first,
	 * then stuff that triggers an event on existing DOM elements when ready,
	 * followed by stuff that triggers an event only on user interaction. This
	 * keeps any screen jumping from occuring later on.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 */
	ready: function() {
		'use strict';

		// Bind search button submission
		jQuery( '#js-search-submit' ).on( 'click.instantContent', instantContentSearch.searchIfPopulated );

		// Bind purchase button click (delegated)
		jQuery( '#js-results-table' ).on( 'click.instantContent', 'button.addtocart', instantContentSearch.addToCart );

		// Bind purchase button click (delegated)
		jQuery( '.instant-content-cart-notice' ).on( 'click.instantContent', 'button.checkout', instantContent.checkoutStart );

		// Bind purchase button click (delegated)
		jQuery( '#js-results-table' ).on( 'click.instantContent', 'button.purchase', instantContentSearch.purchaseContent );


	}
};

/**
 * Holds library page values in an object to avoid polluting global namespace.
 *
 * @since 1.0.0
 *
 * @constructor
 */
window[ 'instantContentLibrary' ] = {

	/**
	 * Load the library contents.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 */
	lookup: function() {
		'use strict';
		var ajaxArgs,
			jqxhr,
			$messageHolder = jQuery( '.instant-content-updated p' );

		// Debug
		// console.log( 'Library Query: ' + url );

		if ( !instantContentL10n.hasValidLicenseAndTerms ) {
			$messageHolder.html( instantContentL10n.enterKeyLibrary );
			return;
		}

		ajaxArgs = {
			name: 'all_purchased',
			url: instantContentLibrary.buildLookupUrl(),
			timeout: 5000,
			dataType: 'jsonp',
			statusCode: { // Swap out this statusCode for a more comprehensive fail() callback?
				500: function() { // 500 is incorrect for unauthorised access. Fix the server to return 403.
					// console.log( '500' );
					$messageHolder.html( instantContentL10n.invalidKey );
				}
			}
		};

		// Do the ajax call. Need .ajax() and not .get() as timeout property is set.
		jqxhr = jQuery.ajax( ajaxArgs );

		// On ajax success
		jqxhr.done( instantContentLibrary.librarySuccess );

		// On ajax error. @todo check if this is actually possible to fire on cross-domain jsonp datatype?
		jqxhr.fail( instantContentLibrary.failedToConnect );

	},

	/**
	 * Build a URL for the API to load the library contents.
	 *
	 * @since 1.1.0
	 *
	 * @function
	 *
	 * @return {String} URL.
	 */
	buildLookupUrl: function() {
		'use strict';
		var urlArgs = {
			license_key: instantContentL10n.license,
			offset: 0,
			max_items: 1000
		};
		return instantContent.buildApiUrl( 'get/article/all_purchased', urlArgs );
	},

	/**
	 * Callback for library look up success.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {Object} data Response object.
	 */
	librarySuccess: function( data ) {
		'use strict';
		var rows = [],
			cart = JSON.parse( instantContentL10n.cart ),
			imported = instantContentL10n.imported,
			remove = [];

		jQuery( '#js-results-table > tr' ).remove();

		if ( data.results.length <= 0) {
			instantContentLibrary.showMessage( instantContentL10n.noPurchases );
			return;
		}

		// Update and show pagination
		jQuery( '.displaying-num' ).text( data.results.length + ' / ' + data.count + ' ' + instantContentL10n[ 'items' ] );
		jQuery( '.pagination-links' ).hide(); // Temporarily hide the pagination buttons, as these aren't working yet
		jQuery( '.tablenav' ).show();
		jQuery( '.tablenav-pages' ).show();

		// Loop through the results
		jQuery.each( data.results, function() {

			// Build table markup
			rows.push( instantContentLibrary.buildLibraryResultRow( this, imported ) );

			// Check if key is in the cart and should be removed
			if ( cart && ( cart.indexOf( this.key ) > -1 ) ) {
				remove.push( this.key );
			}
		});

		// Joining first means there should only be one append of a large string,
		// instead of lots of smaller row strings appends,
		// which is better for performance.
		jQuery( '#js-results-table' ).append( rows.join( '' ) );

		jQuery( '#js-table-footer' ).show();

		instantContentLibrary.showMessage( instantContentL10n.libraryLoaded );

		// If items need to be removed from the cart
		if ( remove.length > 0 ) {
			instantContent.removeCartArticles( remove, false );
		}
	},

	/**
	 * Assemble markup for a single row of the library table.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {Object} doc Document.
	 *
	 * @return {String}     Table row markup.
	 */
	buildLibraryResultRow: function( doc, imported ) {
		'use strict';
		var row,
			date = doc.date.text.replace( 'datetime(', '' ).replace( ' UTC)', '' ),
			isImport = jQuery.inArray( doc.key, imported ),
			btnText = instantContentL10n['import'];

		if ( isImport >= 0 ) {
			btnText = instantContentL10n['reimport'];
		}

		row  = '<tr>';
		row += '<td></td>';
		row += '<td class="title">' + doc.title + '</td>';
		row += '<td class="date">' + date + '</td>';
		row += '<td><button type="button" class="button import-content" data-key="' + doc.key + '">' + btnText + '</button></td>';
		row += '</tr>';
		return row;
	},

	/**
	 * Callback for search fail.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 */
	failedToConnect: function() {
		'use strict';
		instantContentLibrary.showMessage( instantContentL10n.failedToConnect );
	},

	/**
	 * Used to show a message.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {String} message Message to show.
	 */
	showMessage: function( message ) {
		'use strict';
		jQuery( '.instant-content-updated p' ).html( message );
	},

	/**
	 * Update the import form before submitting it.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {jQuery.event} event
	 */
	importContent: function( event ) {
		'use strict';
		jQuery( '#js-instant-content-import-key' ).val( jQuery( event.target ).data( 'key' ) );
		jQuery( '#js-instant-content-library' ).trigger( 'submit' );
	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * Generally ordered with stuff that inserts new elements into the DOM first,
	 * then stuff that triggers an event on existing DOM elements when ready,
	 * followed by stuff that triggers an event only on user interaction. This
	 * keeps any screen jumping from occuring later on.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 */
	ready: function() {
		'use strict';

		// Lookup library contents immediately.
		instantContentLibrary.lookup();

		// Bind import button (delegated)
		jQuery( '#js-results-table' ).on( 'click.instantContent', 'button.import-content', instantContentLibrary.importContent );
	}
};

/**
 * Holds cart page values in an object to avoid polluting global namespace.
 *
 * @since 1.3.0
 *
 * @constructor
 */
window[ 'instantContentCart' ] = {

	/**
	 * Remove an article from the cart
	 *
	 * @since 1.3.0
	 *
	 * @function
	 *
	 * @param  {jQuery.event} event
	 */
	removeFromCart: function( event ) {
		event.preventDefault();

		// Give user indication that action has started
		jQuery( event.target ).parents('tr').css({ 'background' : '#fafafa' });

		jQuery.ajax({
	        url: ajaxurl,
	        data: {
	            'action':'instant_content_remove_from_cart',
	            'key'   : jQuery( event.target ).data( 'key' ).toString()
	        },
	        success:function(data) {
	        	// Remove item from the screen
	        	var total;
	            jQuery( event.target ).parents('tr').fadeOut();
	            jQuery('.cart-count').each( function(){
	            	var count =  jQuery(this).data('count') - 1;
	            	total = count;
					jQuery(this).data('count',count).text(count);
				});
				if ( '0' == total ) {
					jQuery('.instant-content-cart-notice .checkout').fadeOut().remove();
				}
	        },
	        error: function(errorThrown){
	            console.log(errorThrown);
	        }
	    });
	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * Generally ordered with stuff that inserts new elements into the DOM first,
	 * then stuff that triggers an event on existing DOM elements when ready,
	 * followed by stuff that triggers an event only on user interaction. This
	 * keeps any screen jumping from occuring later on.
	 *
	 * @since 1.3.0
	 *
	 * @function
	 */
	ready: function() {
		'use strict';

		// Bind purchase button click (delegated)
		jQuery( '#js-results-table' ).on( 'click.instantContent', 'button.remove', instantContentCart.removeFromCart );

		// Bind purchase button click (delegated)
		jQuery( '.instant-content-cart-notice' ).on( 'click.instantContent', 'button.checkout', instantContent.checkoutStart );

	}
};

/**
 * Holds importer page values in an object to avoid polluting global namespace.
 *
 * @since 1.0.0
 *
 * @constructor
 */
window[ 'instantContentImporter' ] = {

	/**
	 * Initialise the importer on page load to grab the data for a specific document.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 */
	initiateImporter: function() {
		'use strict';
		var jqxhr,
			ajaxArgs = {
				type: 'POST',
				url: ajaxurl,
				dataType: 'JSON',
				data: {
					action: 'instant_content_import',
					key: jQuery( '#js-instant-content-import-key' ).val(),
					license: instantContentL10n.license
				}
			};

		// Do the ajax call.
		jqxhr = jQuery.ajax( ajaxurl, ajaxArgs );

		// On ajax success
		jqxhr.done( instantContentImporter.importContentSuccess );

		// On ajax error.
		jqxhr.fail( instantContentImporter.importContentFail );
	},

	/**
	 * Callback for import success.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 *
	 * @param  {Object} data Response object.
	 */
	importContentSuccess: function( data ) {
		'use strict';
		instantContentImporter.displayFeedback( data );
	},

	/**
	 * What to do if the importing failed.
	 *
	 * @todo This needs some love.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 */
	importContentFail: function( errorThrown ) {
		'use strict';
		console.log( errorThrown );
	},

	/**
	 * If JSON is returned from the import initialise call, update the importer screen.
	 *
	 * @since 1.0.0
	 *
	 * @param  {Object} data Response object.
	 *
	 * @function
	 */
	displayFeedback: function( data ) {
		'use strict';

		if ( 0 === data.id ) {
			jQuery( '.instant-content-updated p' ).html( data.msg );
		} else {
			jQuery( '.instant-content-updated p' ).html( data.msg + '  <a href="' + data.draft_url + '">' + instantContentL10n.editPost + '</a>' );
			jQuery( '.import-data' )
				.append( '<h1>' + data.title + '</h1>' )
				.append( '<p>' + data.summary + '</p>' )
				.append( '<p><a href="' + data.draft_url + '">' + instantContentL10n.viewDraftPost + '</a></p>' );
		}
	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * Generally ordered with stuff that inserts new elements into the DOM first,
	 * then stuff that triggers an event on existing DOM elements when ready,
	 * followed by stuff that triggers an event only on user interaction. This
	 * keeps any screen jumping from occuring later on.
	 *
	 * @since 1.0.0
	 *
	 * @function
	 */
	ready: function() {
		'use strict';

		// Do import immediately.
		instantContentImporter.initiateImporter();
	}
};

if ( pagenow === 'posts_page_instant-content-search' ) {
	jQuery( instantContentSearch.ready );
} else if ( pagenow === 'admin_page_instant-content-library' ) {
	jQuery( instantContentLibrary.ready );
} else if ( pagenow === 'admin_page_instant-content-import' ) {
	jQuery( instantContentImporter.ready );
} else if ( pagenow === 'admin_page_instant-content-cart' ) {
	jQuery( instantContentCart.ready );
}
