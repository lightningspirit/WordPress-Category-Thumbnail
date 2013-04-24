(function($, wp){

var category_thumbnail_frame;

$(document).ready(function(){
	
	$('#image-container').on('click', '#set-category-thumbnail', function( event ){
		event.preventDefault();
		
		// If the media frame already exists, reopen it.
		if ( category_thumbnail_frame ) {
			category_thumbnail_frame.open();
			return;

		}
		
		
		// Create the media frame.
		category_thumbnail_frame = wp.media.frames.category_thumbnail_frame = wp.media({
			title: jQuery( this ).data( 'uploader-title' ),
			button: {
				text: jQuery( this ).data( 'uploader-button-text' ),
			},
			multiple: false
		});
 

		// When an image is selected, run a callback.
		category_thumbnail_frame.on( 'select', function() {
			
			// We set multiple to false so only get one image from the uploader
			attachment = category_thumbnail_frame.state().get( 'selection' ).first().toJSON();
			$('input#image-id').val( attachment.id );
			
			var html = '';
			
			if ( "thumbnail" in attachment.sizes )
				html = _.template( '<img src="<%= url %>" width="<%= width %>" height="<%= height %>">', attachment.sizes.thumbnail );
				
			else if ( attachment.url )
				html = _.template( '<img src="<%= url %>" width="<%= width %>" height="<%= height %>">', attachment );
				
				
			$('#selected-image').html( html );
			
			$('#set-category-thumbnail').hide();
			$('#remove-category-thumbnail').show();
			
			//console.log(attachment);
		});
 
		// Finally, open the modal
		category_thumbnail_frame.open();
		
	});
	
	$(document).on('click', '#remove-category-thumbnail', function( event ){
		event.preventDefault();
		
		$('input#image-id').val('');
		$('#selected-image').html('');
		$('#set-category-thumbnail').show();
		$('#remove-category-thumbnail').hide();
	
	});
	
	$('#submit').click(function( event ){
		event.preventDefault();
		
		$('input#image-id').val('');
		$('#selected-image').html('');
		$('#set-category-thumbnail').show();
		$('#remove-category-thumbnail').hide();
	
	});
	
	
});

})(jQuery, wp);
