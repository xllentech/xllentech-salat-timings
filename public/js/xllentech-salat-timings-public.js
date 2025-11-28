(function( t ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	t(document).on('ready', function(){t(document).on("submit", "#xst-manual-data", function(e){
		t("#xst-manual-data").replaceWith("<img src='"+xstVars.imageURL+"'>"),
		e.preventDefault();
		
		// Serialize the data in the form
		var serializedData = t(this).serialize();
		t.ajax({
			 type: 'post',
			 url: xstVars.ajax_url,
			 data: serializedData,
			 success: function(result) {
				 t('.xllentech_salat_timings').html(result);
			}
		});
		return false;
	})}),
	t(document).on('ready', function(){t(document).on("submit", "#xst-next-months", function(e){
		t("#xst-next-months").replaceWith("<img style='padding:0' height='30px' src='"+xstVars.imageURL+"'>"),
		e.preventDefault();
		
		// Serialize the data in the form
		var serializedData = t(this).serialize();
		t.ajax({
			 type: 'post',
			 url: xstVars.ajax_url,
			 data: serializedData,
			 success: function(result) {
				 t('.xllentech_salat_timings').html(result);
			}
		});
		return false;
	})}),
	t(document).on('ready', function(){t(document).on("submit", "#xst-prev-month", function(e){
		t("#xst-prev-month").replaceWith("<img src='"+xstVars.imageURL+"'>"),
		e.preventDefault();
		
		// Serialize the data in the form
		var serializedData = t(this).serialize();
		t.ajax({
			 type: 'post',
			 url: xstVars.ajax_url,
			 data: serializedData,
			 success: function(result) {
				 t('.xllentech_salat_timings').html(result);
			}
		});
		return false;
	})})
	
})( jQuery );
