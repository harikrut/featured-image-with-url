<?php
/**
 * External image fields for the WooCommerce products REST API.
 *
 * @package HARIKRUTFIWU
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds URL-based images to WooCommerce product requests and responses.
 */
class HARIKRUTFIWU_REST {
	/** Featured image URL field. */
	const FEATURED_FIELD = 'featured_image_url';

	/** Gallery image URLs field. */
	const GALLERY_FIELD = 'gallery_image_urls';

	/**
	 * Register the WooCommerce REST hooks.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_fields' ), 5 );
		add_filter( 'woocommerce_rest_prepare_product_object', array( $this, 'prepare_response' ), 10, 3 );
		add_filter( 'woocommerce_rest_pre_insert_product_object', array( $this, 'prepare_product' ), 10, 2 );
	}

	/**
	 * Expose both fields in the WooCommerce product schema.
	 */
	public function register_fields() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		register_rest_field(
			'product',
			self::FEATURED_FIELD,
			array(
				'schema' => array(
					'description' => __( 'External featured image URL. Use an empty string to clear it.', 'featured-image-with-url' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
			)
		);

		register_rest_field(
			'product',
			self::GALLERY_FIELD,
			array(
				'schema' => array(
					'description' => __( 'Ordered external gallery image URLs. Use an empty array to clear them.', 'featured-image-with-url' ),
					'type'        => 'array',
					'items'       => array( 'type' => 'string' ),
					'context'     => array( 'view', 'edit' ),
				),
			)
		);
	}

	/**
	 * Read external URLs using the product ID even if _fields excludes id.
	 *
	 * @param WP_REST_Response $response Product response.
	 * @param WC_Product       $product  Product being read.
	 * @param WP_REST_Request  $request  Product request.
	 * @return WP_REST_Response
	 */
	public function prepare_response( $response, $product, $request ) {
		$data   = $response->get_data();
		$fields = wp_parse_list( $request->get_param( '_fields' ) );

		if ( ! $fields || rest_is_field_included( self::FEATURED_FIELD, $fields ) ) {
			$featured                    = $this->get_featured_meta( $product->get_id() );
			$data[ self::FEATURED_FIELD ] = esc_url_raw( $featured['img_url'] ?? '', array( 'http', 'https' ) );
		}
		if ( ! $fields || rest_is_field_included( self::GALLERY_FIELD, $fields ) ) {
			$urls = array();
			foreach ( $this->get_gallery_meta( $product->get_id() ) as $image ) {
				$url = esc_url_raw( $image['url'], array( 'http', 'https' ) );
				if ( '' !== $url ) {
					$urls[] = $url;
				}
			}
			$data[ self::GALLERY_FIELD ] = $urls;
		}

		$response->set_data( $data );
		return $response;
	}

	/**
	 * Validate and stage changes for WooCommerce to save with the product.
	 *
	 * @param WC_Product|WP_Error $product Product being saved.
	 * @param WP_REST_Request     $request Product request.
	 * @return WC_Product|WP_Error
	 */
	public function prepare_product( $product, $request ) {
		if ( is_wp_error( $product ) ) {
			return $product;
		}

		$featured_url = $request->get_param( self::FEATURED_FIELD );
		$gallery_urls = $request->get_param( self::GALLERY_FIELD );

		if ( $request->has_param( self::FEATURED_FIELD ) && '' !== $featured_url ) {
			$featured_url = $this->sanitize_url( $featured_url );
			if ( '' === $featured_url ) {
				return $this->invalid_field( self::FEATURED_FIELD );
			}
		}
		if ( $request->has_param( self::GALLERY_FIELD ) ) {
			if ( ! is_array( $gallery_urls ) || array_values( $gallery_urls ) !== $gallery_urls ) {
				return $this->invalid_field( self::GALLERY_FIELD );
			}
			foreach ( $gallery_urls as $index => $url ) {
				$gallery_urls[ $index ] = $this->sanitize_url( $url );
				if ( '' === $gallery_urls[ $index ] ) {
					return $this->invalid_field( self::GALLERY_FIELD );
				}
			}
		}

		if ( '' === $featured_url ) {
			$product->delete_meta_data( '_harikrutfiwu_url' );
			$product->delete_meta_data( '_harikrutfiwu_alt' );
			$product->delete_meta_data( '_knawatfibu_url' );
			$product->delete_meta_data( '_knawatfibu_alt' );
		} elseif ( null !== $featured_url ) {
			$old_image = $this->get_featured_meta( $product->get_id() );
			$image     = array( 'img_url' => $featured_url );
			if ( $featured_url === ( $old_image['img_url'] ?? '' ) ) {
				$image = $old_image;
			}
			// Leave dimensions unknown for new URLs so API requests need no remote image lookup.
			$product->update_meta_data( '_harikrutfiwu_url', $image + array( 'width' => 0, 'height' => 0 ) );
		}

		if ( array() === $gallery_urls ) {
			$product->delete_meta_data( HARIKRUTFIWU_WCGALLARY );
			$product->delete_meta_data( '_knawatfibu_wcgallary' );
		} elseif ( null !== $gallery_urls ) {
			$old_images = array_column( $this->get_gallery_meta( $product->get_id() ), null, 'url' );
			$gallery    = array();
			foreach ( $gallery_urls as $url ) {
				$image     = $old_images[ $url ] ?? array( 'url' => $url );
				$gallery[] = $image + array( 'width' => 0, 'height' => 0 );
			}
			$product->update_meta_data( HARIKRUTFIWU_WCGALLARY, $gallery );
		}

		return $product;
	}

	/**
	 * Read featured image metadata without migrating older records.
	 *
	 * @param int $product_id Product ID.
	 * @return array
	 */
	private function get_featured_meta( $product_id ) {
		$image = get_post_meta( $product_id, '_harikrutfiwu_url', true );
		if ( empty( $image ) ) {
			$image = get_post_meta( $product_id, '_knawatfibu_url', true );
		}
		if ( is_array( $image ) ) {
			return isset( $image['img_url'] ) && is_string( $image['img_url'] ) ? $image : array();
		}
		return is_string( $image ) ? array( 'img_url' => $image ) : array();
	}

	/**
	 * Read current, legacy, and comma-separated gallery metadata without migrating it.
	 *
	 * @param int $product_id Product ID.
	 * @return array
	 */
	private function get_gallery_meta( $product_id ) {
		$gallery = get_post_meta( $product_id, HARIKRUTFIWU_WCGALLARY, true );
		if ( empty( $gallery ) ) {
			$gallery = get_post_meta( $product_id, '_knawatfibu_wcgallary', true );
		}
		if ( is_string( $gallery ) && '' !== $gallery ) {
			$gallery = array_map(
				static function ( $url ) {
					return array( 'url' => trim( $url ) );
				},
				explode( ',', $gallery )
			);
		}
		return array_filter(
			(array) $gallery,
			static function ( $image ) {
				return isset( $image['url'] ) && is_string( $image['url'] ) && '' !== $image['url'];
			}
		);
	}

	/**
	 * Validate an absolute HTTP(S) image URL.
	 *
	 * @param mixed $value Submitted value.
	 * @return string Sanitized URL, or an empty string for invalid input.
	 */
	private function sanitize_url( $value ) {
		if ( ! is_string( $value ) || ! preg_match( '#^https?://#i', $value ) ) {
			return '';
		}
		$url = esc_url_raw( $value, array( 'http', 'https' ) );
		return wp_parse_url( $url, PHP_URL_HOST ) ? $url : '';
	}

	/**
	 * Format an invalid parameter error.
	 *
	 * @param string $field Field name.
	 * @return WP_Error
	 */
	private function invalid_field( $field ) {
		return new WP_Error(
			'harikrutfiwu_invalid_image_url',
			sprintf(
				/* translators: %s: REST API field name. */
				__( 'Invalid value for %s. Supply absolute HTTP or HTTPS image URLs.', 'featured-image-with-url' ),
				$field
			),
			array( 'status' => 400 )
		);
	}
}
