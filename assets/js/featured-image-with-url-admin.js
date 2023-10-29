/**
 * Featured Image with URL admin javascript.
 */
jQuery(document).ready(function($){
	$(document).on("click", ".harikrutfiwu_pvar_preview", function(e){

		e.preventDefault();
		var id = jQuery(this).data('id');
		imgUrl = $('#harikrutfiwu_pvar_url_'+id).val();
		if ( imgUrl != '' ){
			$("<img>", { // Url validation
					    src: imgUrl,
					    error: function() { alert( harikrutfiwujs.invalid_image_url ); },
					    load: function() {
							$('#harikrutfiwu_pvar_img_wrap_'+id).show();
							$('#harikrutfiwu_pvar_img_'+id).attr('src',imgUrl);
							$('#harikrutfiwu_url_wrap_'+id).hide();
					    }
			});
		}
	});

	$(document).on("click", ".harikrutfiwu_pvar_remove", function(e){
		var id2 = jQuery(this).data('id');

		e.preventDefault();
		$('#harikrutfiwu_pvar_url_'+id2).val("").trigger("change");
		$('#harikrutfiwu_pvar_img_'+id2).attr('src',"");
		$('#harikrutfiwu_pvar_img_wrap_'+id2).hide();
		$('#harikrutfiwu_url_wrap_'+id2).show();
	});
});