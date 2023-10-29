<?php 
// Featured Image with URL metabox Template

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
$image_url = '';
$image_alt = '';
if( isset( $image_meta['img_url'] ) && $image_meta['img_url'] != '' ){
	$image_url = esc_url( $image_meta['img_url'] );
}
if( isset( $image_meta['img_alt'] ) && $image_meta['img_alt'] != '' ){
	$image_alt = esc_attr( $image_meta['img_alt'] );
}
?>

<div id="harikrutfiwu_metabox_content" >

	<input id="harikrutfiwu_url" type="text" name="harikrutfiwu_url" placeholder="<?php esc_attr_e('Image URL', 'featured-image-with-url') ?>" value="<?php echo esc_url_raw( $image_url ); ?>" />
	<a id="harikrutfiwu_preview" class="button" >
		<?php _e('Preview', 'featured-image-with-url') ?>
	</a>
	
	<input id="harikrutfiwu_alt" type="text" name="harikrutfiwu_alt" placeholder="<?php esc_attr_e('Alt text (Optional)', 'featured-image-with-url') ?>" value="<?php echo esc_attr( $image_alt ); ?>" />

	<div >
		<span id="harikrutfiwu_noimg"><?php esc_html_e('No image', 'featured-image-with-url'); ?></span>
			<img id="harikrutfiwu_img" src="<?php echo esc_url( $image_url ); ?>" />
	</div>

	<a id="harikrutfiwu_remove" class="button" style="margin-top:4px;"><?php _e('Remove Image', 'featured-image-with-url') ?></a>
</div>

<script>
	jQuery(document).ready(function($){

		<?php if ( ! $image_meta['img_url'] ): ?>
			$('#harikrutfiwu_img').hide().attr('src','');
			$('#harikrutfiwu_noimg').show();
			$('#harikrutfiwu_alt').hide().val('');
			$('#harikrutfiwu_remove').hide();
			$('#harikrutfiwu_url').show().val('');
			$('#harikrutfiwu_preview').show();
		<?php else: ?>
			$('#harikrutfiwu_noimg').hide();
			$('#harikrutfiwu_remove').show();
			$('#harikrutfiwu_url').hide();
			$('#harikrutfiwu_preview').hide();
		<?php endif; ?>

		// Preview Featured Image
		$('#harikrutfiwu_preview').click(function(e){
			
			e.preventDefault();
			imgUrl = $('#harikrutfiwu_url').val();
			
			if ( imgUrl != '' ){
				$("<img>", {
						    src: imgUrl,
						    error: function() {alert('<?php _e('Error URL Image', 'featured-image-with-url') ?>')},
						    load: function() {
						    	$('#harikrutfiwu_img').show().attr('src',imgUrl);
						    	$('#harikrutfiwu_noimg').hide();
						    	$('#harikrutfiwu_alt').show();
						    	$('#harikrutfiwu_remove').show();
						    	$('#harikrutfiwu_url').hide();
						    	$('#harikrutfiwu_preview').hide();
						    }
				});
			}
		});

		// Remove Featured Image
		$('#harikrutfiwu_remove').click(function(e){

			e.preventDefault();
			$('#harikrutfiwu_img').hide().attr('src','');
			$('#harikrutfiwu_noimg').show();
	    	$('#harikrutfiwu_alt').hide().val('');
	    	$('#harikrutfiwu_remove').hide();
	    	$('#harikrutfiwu_url').show().val('');
	    	$('#harikrutfiwu_preview').show();

		});

	});

</script>