

jQuery(document).ready(function($) {

	/*
	 * On selection (change event), add the post to the list in the metabox
	 */

	$('.related-posts-select').change(function() {
		var select = $(this),
				container = $('#related-posts'),
				id = select.val(),
				title = this.options[this.options.selectedIndex].text;

		if (id != "0") {
			if ($('#related-post-' + id).length == 0) {
				container.prepend('<div class="related-post" id="related-post-' +
									id +
									'"><input type="hidden" name="related-posts[]" value="' +
									id +
									'"><span class="related-post-title">' +
									title +
									'</span><a href="#" onClick="related_delete( this ); return false;">Delete</a></div>'
								);
			}
		}
	});

	/* Delete option again on click event */

	$('.related-post a').on('click', function() {
		related_delete( this );
		return false;

	});

	$('#related-posts').sortable();

});

/*
 * related_delete
 * Function te remove the selected post
 */

function related_delete( a_el ) {
	var div = jQuery( a_el ).parent();

	div.css('background-color', '#ff0000').fadeOut('normal', function() {
		div.remove();
	});
	return false;
}


/*
 * Select the right tab on the options page
 *
 */
jQuery(document).ready(function($) {
	jQuery( '.related-nav-tab-wrapper a' ).on('click', function() {

		jQuery( '.related_options' ).removeClass( 'active' );
		jQuery( '.related-nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );

		var rel = jQuery( this ).attr('rel');
		jQuery( '.' + rel ).addClass( 'active' );
		jQuery( this ).addClass( 'nav-tab-active' );

		return false;
	});
});


/*
 * Use Chosen.js to limit the number of shown options in the select-box
 *
 */

jQuery(document).ready(function($) {
	$('select.related-posts-select').chosen({
		no_results_text: "Nothing found...",
		allow_single_deselect: true,
		width: "100%"
	});
});

