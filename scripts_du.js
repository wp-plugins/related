

jQuery(document).ready(function($) {

	/*
	 * On selection (change event), add the post to the list in the metabox
	 */

	$('.related_du-posts-select').change(function() {
		var select = $(this),
				container = $('#related_du-posts'),
				id = select.val(),
				title = this.options[this.options.selectedIndex].text;

		if (id != "0") {
			if ($('#related_du-post-' + id).length == 0) {
				container.prepend('<div class="related_du-post" id="related-post-' +
									id +
									'"><input type="hidden" name="related_du-posts[]" value="' +
									id +
									'"><span class="related_du-post-title">' +
									title +
									'</span><a href="#" onClick="related_delete( this ); return false;">Delete</a></div>'
								);
			}
		}
	});

	/* Delete option again on click event */

	$('.related_du-post a').on('click', function() {
		related_delete( this );
		return false;

	});

	$('#related_du-posts').sortable();

});


/*
 * Use Chosen.js to limit the number of shown options in the select-box
 *
 */

jQuery(document).ready(function($) {
	$('select.related_du-posts-select').chosen({
		no_results_text: "Nothing found...",
		allow_single_deselect: true,
		width: "100%"
	});
});

