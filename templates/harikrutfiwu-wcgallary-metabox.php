<?php
/**
 * Featured Image with URL metabox Template
 *
 * @package HARIKRUTFIWU/Templates
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print Gallary Slot.
 *
 * @param string $image_url Image URL.
 * @param int    $count     Count. Default 1.
 * @return void
 */
function harikrutfiwu_print_gallary_slot( $image_url = '', $count = 1 ) {
	?>
	<div id="harikrutfiwu_wcgallary<?php echo esc_attr( $count ); ?>" class="harikrutfiwu_wcgallary">
		<div id="harikrutfiwu_url_wrap<?php echo esc_attr( $count ); ?>" style="<?php echo ( ! empty( $image_url ) ? 'display:none;' : '' ); ?>">
			<input
				id="harikrutfiwu_url<?php echo esc_attr( $count ); ?>"
				class="harikrutfiwu_url"
				type="text"
				name="harikrutfiwu_wcgallary[<?php echo esc_attr( $count ); ?>][url]"
				placeholder="<?php esc_attr_e( 'Image URL', 'featured-image-with-url' ); ?>"
				data-id="<?php echo esc_attr( $count ); ?>"
				value="<?php echo esc_url( $image_url ); ?>"
			/>
			<a id="harikrutfiwu_preview<?php echo esc_attr( $count ); ?>" class="harikrutfiwu_preview button" data-id="<?php echo esc_attr( $count ); ?>">
				<?php esc_html_e( 'Preview', 'featured-image-with-url' ); ?>
			</a>
		</div>
		<div id="harikrutfiwu_img_wrap<?php echo esc_attr( $count ); ?>" class="harikrutfiwu_img_wrap" style="<?php echo ( empty( $image_url ) ? 'display:none;' : '' ); ?>">
			<span href="#" class="harikrutfiwu_remove" data-id="<?php echo esc_attr( $count ); ?>"></span>
			<img id="harikrutfiwu_img<?php echo esc_attr( $count ); ?>" class="harikrutfiwu_img" data-id="<?php echo esc_attr( $count ); ?>" src="<?php echo esc_url( $image_url ); ?>" />
		</div>
	</div>
	<?php
}
?>

<div id="harikrutfiwu_wcgallary_metabox_content" >
	<?php
	global $harikrutfiwu;
	$count          = 1;
	$gallary_images = $harikrutfiwu->common->harikrutfiwu_get_wcgallary_meta( $post->ID );
	if ( ! empty( $gallary_images ) ) {
		foreach ( $gallary_images as $gallary_image ) {
			harikrutfiwu_print_gallary_slot( $gallary_image['url'], $count );
			$count++;
		}
	}
	harikrutfiwu_print_gallary_slot( '', $count );
	$count++;
	?>
</div>
<template id="harikrutfiwu_wcgallary_template" style="display: none;">
	<?php harikrutfiwu_print_gallary_slot( '', '__COUNT__' ); ?>
</template>
<div style="clear:both"></div>

<?php
wp_nonce_field( 'harikrutfiwu_wcgallary_nonce_action', 'harikrutfiwu_wcgallary_nonce' );
?>
<script>
	function harikrutfiwuGetGallaryTemplate(count = 1){
		const template = document.getElementById('harikrutfiwu_wcgallary_template').content.cloneNode(true);
		template.getElementById('harikrutfiwu_wcgallary__COUNT__').id = "harikrutfiwu_wcgallary" + count;
		template.getElementById('harikrutfiwu_url_wrap__COUNT__').id = "harikrutfiwu_url_wrap" + count;
		template.getElementById('harikrutfiwu_url__COUNT__').setAttribute('data-id', count);
		template.getElementById('harikrutfiwu_url__COUNT__').name = "harikrutfiwu_wcgallary[" + count + "][url]";
		template.getElementById('harikrutfiwu_url__COUNT__').id = "harikrutfiwu_url" + count
		template.getElementById('harikrutfiwu_preview__COUNT__').setAttribute('data-id', count);
		template.getElementById('harikrutfiwu_preview__COUNT__').id = "harikrutfiwu_preview" + count;
		template.getElementById('harikrutfiwu_img_wrap__COUNT__').id = "harikrutfiwu_img_wrap" + count;
		template.querySelector('.harikrutfiwu_remove').setAttribute('data-id', count);
		template.getElementById('harikrutfiwu_img__COUNT__').setAttribute('data-id', count);
		template.getElementById('harikrutfiwu_img__COUNT__').id = "harikrutfiwu_img" + count;
		return template;
	}

	jQuery(document).ready(function($){
		var counter = <?php echo absint( $count ); ?>;
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
					error: function() {$alert( '<?php esc_attr_e( 'Error URL Image', 'featured-image-with-url' ); ?>' ) },
					load: function() {
						$('#harikrutfiwu_img_wrap'+id).show();
						$('#harikrutfiwu_img'+id).attr('src',imgUrl);
						$('#harikrutfiwu_remove'+id).show();
						$('#harikrutfiwu_url'+id).hide();
						$('#harikrutfiwu_preview'+id).hide();
						$('#harikrutfiwu_wcgallary_metabox_content').append( harikrutfiwuGetGallaryTemplate(counter) );
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
