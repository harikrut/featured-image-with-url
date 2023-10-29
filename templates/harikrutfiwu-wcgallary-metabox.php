<?php 
// Featured Image with URL metabox Template

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function harikrutfiwu_get_gallary_slot( $image_url = '' ){
	ob_start();
	?>
	<div id="harikrutfiwu_wcgallary__COUNT__" class="harikrutfiwu_wcgallary">
		<div id="harikrutfiwu_url_wrap__COUNT__" <?php if( $image_url != ''){ echo 'style="display: none;"'; } ?>>
			<input id="harikrutfiwu_url__COUNT__" class="harikrutfiwu_url" type="text" name="harikrutfiwu_wcgallary[__COUNT__][url]" placeholder="<?php esc_attr_e('Image URL', 'featured-image-with-url') ?>" data-id="__COUNT__" value="<?php echo esc_url_raw( $image_url ); ?>"/>
			<a id="harikrutfiwu_preview__COUNT__" class="harikrutfiwu_preview button" data-id="__COUNT__">
				<?php esc_html_e( 'Preview', 'featured-image-with-url' ); ?>
			</a>
		</div>
		<div id="harikrutfiwu_img_wrap__COUNT__" class="harikrutfiwu_img_wrap" <?php if( $image_url == ''){ echo 'style="display: none;"'; } ?>>
			<span href="#" class="harikrutfiwu_remove" data-id="__COUNT__"></span>
			<img id="harikrutfiwu_img__COUNT__" class="harikrutfiwu_img" data-id="__COUNT__" src="<?php echo esc_url( $image_url ); ?>" />
		</div>
	</div>
	<?php
	$gallery_image = ob_get_clean();
	return preg_replace('/\s+/', ' ', trim($gallery_image));
}

?>

<div id="harikrutfiwu_wcgallary_metabox_content" >
	<?php
	global $harikrutfiwu;
	$gallary_images = $harikrutfiwu->common->harikrutfiwu_get_wcgallary_meta( $post->ID );
	$count = 1;
	if( !empty( $gallary_images ) ){
		foreach ($gallary_images as $gallary_image ) {
			echo str_replace( '__COUNT__', $count, harikrutfiwu_get_gallary_slot( $gallary_image['url'] ) );
			$count++;
		}
	}
	echo str_replace( '__COUNT__', $count, harikrutfiwu_get_gallary_slot() );
	$count++;
	?>
</div>
<div style="clear:both"></div>
<script>
	jQuery(document).ready(function($){

		var counter = <?php echo $count;?>;
		// Preview
		$(document).on("click", ".harikrutfiwu_preview", function(e){
						
			e.preventDefault();
			counter = counter + 1;
			var new_element_str = '';
			var id = jQuery(this).data('id');
			imgUrl = $('#harikrutfiwu_url'+id).val();
			
			if ( imgUrl != '' ){
				$("<img>", { // Url validation
						    src: imgUrl,
						    error: function() {alert('<?php _e('Error URL Image', 'featured-image-with-url') ?>')},
						    load: function() {
						    	$('#harikrutfiwu_img_wrap'+id).show();
						    	$('#harikrutfiwu_img'+id).attr('src',imgUrl);
						    	$('#harikrutfiwu_remove'+id).show();
						    	$('#harikrutfiwu_url'+id).hide();
						    	$('#harikrutfiwu_preview'+id).hide();
						    	new_element_str = '<?php echo harikrutfiwu_get_gallary_slot(); ?>';
						    	new_element_str = new_element_str.replace(/__COUNT__/g, counter );
						    	$('#harikrutfiwu_wcgallary_metabox_content').append( new_element_str );
						    }
				});
			}
		});

		$(document).on("click", ".harikrutfiwu_remove", function(e){
			var id2 = jQuery(this).data('id');

			e.preventDefault();
			$('#harikrutfiwu_wcgallary'+id2).remove();
		});

	});
</script>