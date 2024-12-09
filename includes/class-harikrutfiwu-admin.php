<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package     HARIKRUTFIWU
 * @subpackage  HARIKRUTFIWU/admin
 * @copyright   Copyright (c) Harikrut Technolab
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @package     HARIKRUTFIWU
 * @subpackage  HARIKRUTFIWU/admin
 */
class HARIKRUTFIWU_Admin {

	/**
	 * Image meta key for saving image URL.
	 *
	 * @var string
	 */
	private $image_meta_url = '_harikrutfiwu_url';

	/**
	 * Image meta key for saving image alt.
	 *
	 * @var string
	 */
	private $image_meta_alt = '_harikrutfiwu_alt';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this, 'harikrutfiwu_add_metabox' ), 10, 2 );
			add_action( 'save_post', array( $this, 'harikrutfiwu_save_image_url_data' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'admin_menu', array( $this, 'harikrutfiwu_add_options_page' ) );
			add_action( 'admin_init', array( $this, 'harikrutfiwu_settings_init' ) );
			// Add & Save Product Variation Featured image by URL.
			add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'harikrutfiwu_add_product_variation_image_selector' ), 10, 3 );
			add_action( 'woocommerce_save_product_variation', array( $this, 'harikrutfiwu_save_product_variation_image' ), 10, 2 );

			// Handle migration from "Featured Image by URL" plugin.
			add_action( 'admin_notices', array( $this, 'maybe_display_migrate_from_fibu_notices' ) );
			add_action( 'admin_post_harikrutfiwu_migrate_from_fibu', array( $this, 'handle_migration_from_fibu' ) );
			add_action( 'admin_post_harikrutfiwu_migration_notice_dismissed', array( $this, 'dismiss_fibu_migration_notice' ) );
			add_filter( 'removable_query_args', array( $this, 'removable_query_args' ) );
		}
	}

	/**
	 * Add Meta box for Featured Image with URL.
	 *
	 * @since 1.0
	 * @param string $post_type Post type.
	 * @param object $post      Post object.
	 * @return void
	 */
	public function harikrutfiwu_add_metabox( $post_type, $post ) {
		$options            = get_option( HARIKRUTFIWU_OPTIONS );
		$disabled_posttypes = isset( $options['harikrutfiwu_disabled_posttypes'] ) ? $options['harikrutfiwu_disabled_posttypes'] : array();

		if ( in_array( $post_type, $disabled_posttypes, true ) ) {
			return;
		}

		add_meta_box(
			'harikrutfiwu_metabox',
			__( 'Featured Image with URL', 'featured-image-with-url' ),
			array( $this, 'harikrutfiwu_render_metabox' ),
			$this->harikrutfiwu_get_posttypes(),
			'side',
			'low'
		);

		add_meta_box(
			'harikrutfiwu_wcgallary_metabox',
			__( 'Product gallery by URLs', 'featured-image-with-url' ),
			array( $this, 'harikrutfiwu_render_wcgallary_metabox' ),
			'product',
			'side',
			'low'
		);
	}

	/**
	 * Render Meta box for Featured Image with URL.
	 *
	 * @since 1.0
	 * @param object $post Post object.
	 * @return void
	 */
	public function harikrutfiwu_render_metabox( $post ) {
		$image_meta = $this->harikrutfiwu_get_image_meta( $post->ID );

		// Include Metabox Template.
		include HARIKRUTFIWU_PLUGIN_DIR . 'templates/harikrutfiwu-metabox.php';
	}

	/**
	 * Render Meta box for Product gallary by URLs
	 *
	 * @since 1.0
	 * @param object $post Post object.
	 * @return void
	 */
	public function harikrutfiwu_render_wcgallary_metabox( $post ) {
		// Include WC Gallary Metabox Template.
		include HARIKRUTFIWU_PLUGIN_DIR . 'templates/harikrutfiwu-wcgallary-metabox.php';
	}

	/**
	 * Load Admin Styles.
	 *
	 * Enqueues the required admin styles.
	 *
	 * @since 1.0
	 * @param string $hook Page hook.
	 * @return void
	 */
	public function enqueue_admin_styles( $hook ) {
		$css_dir = HARIKRUTFIWU_PLUGIN_URL . 'assets/css/';
		wp_enqueue_style( 'harikrutfiwu-admin', $css_dir . 'harikrutfiwu-admin.css', array(), '1.0.3', '' );
	}

	/**
	 * Load Admin Scripts.
	 *
	 * Enqueues the required admin scripts.
	 *
	 * @since 1.0
	 * @param string $hook Page hook.
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook ) {
		$js_dir = HARIKRUTFIWU_PLUGIN_URL . 'assets/js/';
		wp_register_script( 'harikrutfiwu-admin', $js_dir . 'harikrutfiwu-admin.js', array( 'jquery' ), HARIKRUTFIWU_VERSION, true );
		$strings = array(
			'invalid_image_url' => __( 'Error in Image URL', 'featured-image-with-url' ),
		);
		wp_localize_script( 'harikrutfiwu-admin', 'harikrutfiwujs', $strings );
		wp_enqueue_script( 'harikrutfiwu-admin' );
	}

	/**
	 * Add Meta box for Featured Image with URL.
	 *
	 * @since 1.0
	 * @param int    $post_id Post ID.
	 * @param object $post    Post object.
	 * @return void
	 */
	public function harikrutfiwu_save_image_url_data( $post_id, $post ) {
		$cap = 'page' === $post->post_type ? 'edit_page' : 'edit_post';
		if ( ! current_user_can( $cap, $post_id ) || ! post_type_supports( $post->post_type, 'thumbnail' ) || defined( 'DOING_AUTOSAVE' ) ) {
			return;
		}

		if ( isset( $_POST['harikrutfiwu_url'] ) && isset( $_POST['harikrutfiwu_img_url_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['harikrutfiwu_img_url_nonce'] ), 'harikrutfiwu_img_url_nonce_action' ) ) {
			global $harikrutfiwu;
			// Update Featured Image URL.
			$image_url = isset( $_POST['harikrutfiwu_url'] ) ? esc_url_raw( wp_unslash( $_POST['harikrutfiwu_url'] ) ) : '';
			$image_alt = isset( $_POST['harikrutfiwu_alt'] ) ? sanitize_text_field( wp_unslash( $_POST['harikrutfiwu_alt'] ) ) : '';

			if ( ! empty( $image_url ) ) {
				if ( 'product' === get_post_type( $post_id ) ) {
					$img_url = get_post_meta( $post_id, $this->image_meta_url, true );
					if ( is_array( $img_url ) && isset( $img_url['img_url'] ) && $image_url === $img_url['img_url'] ) {
							$image_url = array(
								'img_url' => $image_url,
								'width'   => $img_url['width'],
								'height'  => $img_url['height'],
							);
					} else {
						$imagesize = $harikrutfiwu->common->get_image_sizes( $image_url );
						$image_url = array(
							'img_url' => $image_url,
							'width'   => isset( $imagesize[0] ) ? $imagesize[0] : '',
							'height'  => isset( $imagesize[1] ) ? $imagesize[1] : '',
						);
					}
				}

				update_post_meta( $post_id, $this->image_meta_url, $image_url );
				if ( $image_alt ) {
					update_post_meta( $post_id, $this->image_meta_alt, $image_alt );
				}
			} else {
				delete_post_meta( $post_id, $this->image_meta_url );
				delete_post_meta( $post_id, $this->image_meta_alt );
			}
		}

		if ( isset( $_POST['harikrutfiwu_wcgallary'] ) && isset( $_POST['harikrutfiwu_wcgallary_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['harikrutfiwu_wcgallary_nonce'] ), 'harikrutfiwu_wcgallary_nonce_action' ) ) {
			global $harikrutfiwu;
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Unslash and Sanitization already done in harikrutfiwu_sanitize function.
			$harikrutfiwu_wcgallary = is_array( $_POST['harikrutfiwu_wcgallary'] ) ? $this->harikrutfiwu_sanitize( $_POST['harikrutfiwu_wcgallary'] ) : array();

			if ( empty( $harikrutfiwu_wcgallary ) || 'product' !== $post->post_type ) {
				return;
			}

			$old_images = $harikrutfiwu->common->harikrutfiwu_get_wcgallary_meta( $post_id );
			if ( ! empty( $old_images ) ) {
				foreach ( $old_images as $key => $value ) {
					$old_images[ $value['url'] ] = $value;
				}
			}

			$gallary_images = array();
			if ( ! empty( $harikrutfiwu_wcgallary ) ) {
				foreach ( $harikrutfiwu_wcgallary as $harikrutfiwu_gallary ) {
					if ( isset( $harikrutfiwu_gallary['url'] ) && '' !== $harikrutfiwu_gallary['url'] ) {
						$gallary_image        = array();
						$gallary_image['url'] = $harikrutfiwu_gallary['url'];

						if ( isset( $old_images[ $gallary_image['url'] ]['width'] ) && '' !== $old_images[ $gallary_image['url'] ]['width'] ) {
							$gallary_image['width']  = isset( $old_images[ $gallary_image['url'] ]['width'] ) ? $old_images[ $gallary_image['url'] ]['width'] : '';
							$gallary_image['height'] = isset( $old_images[ $gallary_image['url'] ]['height'] ) ? $old_images[ $gallary_image['url'] ]['height'] : '';

						} else {
							$imagesizes              = $harikrutfiwu->common->get_image_sizes( $harikrutfiwu_gallary['url'] );
							$gallary_image['width']  = isset( $imagesizes[0] ) ? $imagesizes[0] : '';
							$gallary_image['height'] = isset( $imagesizes[1] ) ? $imagesizes[1] : '';
						}

						$gallary_images[] = $gallary_image;
					}
				}
			}

			if ( ! empty( $gallary_images ) ) {
				update_post_meta( $post_id, HARIKRUTFIWU_WCGALLARY, $gallary_images );
			} else {
				delete_post_meta( $post_id, HARIKRUTFIWU_WCGALLARY );
			}
		}
	}

	/**
	 * Get Image metadata by post_id
	 *
	 * @since 1.0
	 * @param int  $post_id        Post ID.
	 * @param bool $is_single_page Is single page? If true then return image size also.
	 * @return array
	 */
	public function harikrutfiwu_get_image_meta( $post_id, $is_single_page = false ) {
		global $harikrutfiwu;
		$image_meta = array();
		$img_url    = get_post_meta( $post_id, $this->image_meta_url, true );
		$img_alt    = get_post_meta( $post_id, $this->image_meta_alt, true );

		// Compatibility with "Featured Image by URL" plugin.
		if ( empty( $img_url ) ) {
			$old_img_url = get_post_meta( $post_id, '_knawatfibu_url', true );
			if ( ! empty( $old_img_url ) ) {
				$img_url     = $old_img_url;
				$old_img_alt = get_post_meta( $post_id, '_knawatfibu_alt', true );
				update_post_meta( $post_id, $this->image_meta_url, $old_img_url );

				if ( ! empty( $old_img_alt ) && empty( $img_alt ) ) {
					$img_alt = $old_img_alt;
					update_post_meta( $post_id, $this->image_meta_alt, $old_img_alt );
				}
			}
		}

		if ( is_array( $img_url ) && isset( $img_url['img_url'] ) ) {
			$image_meta['img_url'] = $img_url['img_url'];
		} else {
			$image_meta['img_url'] = $img_url;
		}
		$image_meta['img_alt'] = $img_alt;
		if ( ( 'product_variation' === get_post_type( $post_id ) || 'product' === get_post_type( $post_id ) ) && $is_single_page ) {
			if ( isset( $img_url['width'] ) ) {
				$image_meta['width']  = $img_url['width'];
				$image_meta['height'] = $img_url['height'];
			} else {
				if ( isset( $image_meta['img_url'] ) && '' !== $image_meta['img_url'] ) {
					$imagesize = $harikrutfiwu->common->get_image_sizes( $image_meta['img_url'] );
					$image_url = array(
						'img_url' => $image_meta['img_url'],
						'width'   => isset( $imagesize[0] ) ? $imagesize[0] : '',
						'height'  => isset( $imagesize[1] ) ? $imagesize[1] : '',
					);
					update_post_meta( $post_id, $this->image_meta_url, $image_url );
					$image_meta = $image_url;
				}
			}
		}
		return $image_meta;
	}

	/**
	 * Adds Settings Page
	 *
	 * @since 1.0
	 * @return void
	 */
	public function harikrutfiwu_add_options_page() {
		add_options_page(
			__( 'Featured Image with URL', 'featured-image-with-url' ),
			__( 'Featured Image with URL', 'featured-image-with-url' ),
			'manage_options',
			'harikrutfiwu',
			array( $this, 'harikrutfiwu_options_page_html' )
		);
	}

	/**
	 * Settings Page HTML
	 *
	 * @since 1.0
	 * @return array|null
	 */
	public function harikrutfiwu_options_page_html() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				// Output security fields for the registered setting "harikrutfiwu".
				settings_fields( 'harikrutfiwu' );

				// Output setting sections and their fields.
				do_settings_sections( 'harikrutfiwu' );

				// Output save settings button.
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register custom settings, Sections & fields
	 *
	 * @since 1.0
	 * @return void
	 */
	public function harikrutfiwu_settings_init() {
		register_setting( 'harikrutfiwu', HARIKRUTFIWU_OPTIONS );

		add_settings_section(
			'harikrutfiwu_section',
			__( 'Settings', 'featured-image-with-url' ),
			array( $this, 'harikrutfiwu_section_callback' ),
			'harikrutfiwu'
		);

		// Register a new field in the "harikrutfiwu_section" section, inside the "harikrutfiwu" page.
		add_settings_field(
			'harikrutfiwu_disabled_posttypes',
			__( 'Disable Post types', 'featured-image-with-url' ),
			array( $this, 'disabled_posttypes_callback' ),
			'harikrutfiwu',
			'harikrutfiwu_section',
			array(
				'label_for' => 'harikrutfiwu_disabled_posttypes',
				'class'     => 'harikrutfiwu_row',
			)
		);

		add_settings_field(
			'harikrutfiwu_resize_images',
			__( 'Display Resized Images', 'featured-image-with-url' ),
			array( $this, 'resize_images_callback' ),
			'harikrutfiwu',
			'harikrutfiwu_section',
			array(
				'label_for' => 'harikrutfiwu_resize_images',
				'class'     => 'harikrutfiwu_row',
			)
		);
	}

	/**
	 * Callback function for harikrutfiwu section.
	 *
	 * @since 1.0
	 * @param array $args Arguments.
	 * @return void
	 */
	public function harikrutfiwu_section_callback( $args ) {
		// Do some HTML here.
	}

	/**
	 * Callback function for disabled_posttypes field.
	 *
	 * @since 1.0
	 * @param array $args Arguments.
	 * @return void
	 */
	public function disabled_posttypes_callback( $args ) {
		// Get the value of the setting we've registered with register_setting().
		global $wp_post_types;

		$options            = get_option( HARIKRUTFIWU_OPTIONS );
		$post_types         = $this->harikrutfiwu_get_posttypes( true );
		$disabled_posttypes = isset( $options['harikrutfiwu_disabled_posttypes'] ) ? $options['harikrutfiwu_disabled_posttypes'] : array();

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $post_type ) {
				?>
				<label for="<?php echo esc_attr( $key ); ?>" style="display: block;">
					<input
						name="<?php echo esc_attr( HARIKRUTFIWU_OPTIONS . '[' . $args['label_for'] . ']' ); ?>[]"
						class="harikrutfiwu_disabled_posttypes"
						id="<?php echo esc_attr( $key ); ?>"
						type="checkbox" value="<?php echo esc_attr( $key ); ?>"
						<?php echo ( in_array( $key, $disabled_posttypes, true ) ) ? 'checked="checked"' : ''; ?>
					/>
					<?php echo isset( $wp_post_types[ $key ]->label ) ? esc_html( $wp_post_types[ $key ]->label ) : esc_html( ucfirst( $key ) ); ?>
				</label>
				<?php
			}
		}
		?>
		<p class="description">
			<?php esc_html_e( 'Please check checkbox for posttypes on which you want to disable Featured image by URL.', 'featured-image-with-url' ); ?>
		</p>

		<?php
	}

	/**
	 * Callback function for resize_images field.
	 *
	 * @since 1.0
	 * @param array $args Arguments.
	 * @return void
	 */
	public function resize_images_callback( $args ) {
		// Get the value of the setting we've registered with register_setting().
		$options       = get_option( HARIKRUTFIWU_OPTIONS );
		$resize_images = isset( $options['harikrutfiwu_resize_images'] ) ? $options['harikrutfiwu_resize_images'] : false;
		?>
		<label for="harikrutfiwu_resize_images">
			<input
				name="<?php echo esc_attr( HARIKRUTFIWU_OPTIONS . '[' . $args['label_for'] . ']' ); ?>"
				type="checkbox"
				value="1"
				id="harikrutfiwu_resize_images"
				<?php echo ( ( ! defined( 'JETPACK__VERSION' ) ) ? 'disabled="disabled"' : ( ( $resize_images ) ? 'checked="checked"' : '' ) ); ?>
			/>
			<?php esc_html_e( 'Enable display resized images for image sizes like thumbnail, medium, large etc..', 'featured-image-with-url' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'You need Jetpack plugin installed & connected  for enable this functionality.', 'featured-image-with-url' ); ?>
		</p>

		<?php
	}

	/**
	 * Get Post Types which supports Featured image with URL.
	 *
	 * @since 1.0.0
	 * @param bool $raw If true then return all post types.
	 * @return array
	 */
	public function harikrutfiwu_get_posttypes( $raw = false ) {
		$post_types = array_diff( get_post_types( array( 'public' => true ), 'names' ), array( 'nav_menu_item', 'attachment', 'revision' ) );
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $post_type ) {
				if ( ! post_type_supports( $post_type, 'thumbnail' ) ) {
					unset( $post_types[ $key ] );
				}
			}
		}
		if ( $raw ) {
			return $post_types;
		} else {
			$options            = get_option( HARIKRUTFIWU_OPTIONS );
			$disabled_posttypes = isset( $options['harikrutfiwu_disabled_posttypes'] ) ? $options['harikrutfiwu_disabled_posttypes'] : array();
			$post_types         = array_diff( $post_types, $disabled_posttypes );
		}

		return $post_types;
	}

	/**
	 * Render Featured image by URL in Product variation
	 *
	 * @since 1.0.0
	 *
	 * @param int    $loop           Loop.
	 * @param array  $variation_data Variation data.
	 * @param object $variation      Variation object.
	 * @return void
	 */
	public function harikrutfiwu_add_product_variation_image_selector( $loop, $variation_data, $variation ) {
		$harikrutfiwu_url = '';
		if ( isset( $variation_data['_harikrutfiwu_url'][0] ) ) {
			$harikrutfiwu_url = $variation_data['_harikrutfiwu_url'][0];
			$harikrutfiwu_url = maybe_unserialize( $harikrutfiwu_url );
			if ( is_array( $harikrutfiwu_url ) ) {
				$harikrutfiwu_url = $harikrutfiwu_url['img_url'];
			}
		}
		?>
		<div id="harikrutfiwu_product_variation_<?php echo esc_attr( $variation->ID ); ?>" class="harikrutfiwu_product_variation form-row form-row-first">
			<label for="harikrutfiwu_pvar_url_<?php echo esc_attr( $variation->ID ); ?>">
				<strong>
					<?php esc_html_e( 'Product Variation Image with URL', 'featured-image-with-url' ); ?>
				</strong>
			</label>

			<div id="harikrutfiwu_pvar_img_wrap_<?php echo esc_attr( $variation->ID ); ?>" class="harikrutfiwu_pvar_img_wrap" style="<?php echo ( ( '' === $harikrutfiwu_url ) ? 'display:none' : '' ); ?>" >
				<span href="#" class="harikrutfiwu_pvar_remove" data-id="<?php echo esc_attr( $variation->ID ); ?>"></span>
				<img id="harikrutfiwu_pvar_img_<?php echo esc_attr( $variation->ID ); ?>" class="harikrutfiwu_pvar_img" data-id="<?php echo esc_attr( $variation->ID ); ?>" src="<?php echo esc_attr( $harikrutfiwu_url ); ?>" />
			</div>
			<div id="harikrutfiwu_url_wrap_<?php echo esc_attr( $variation->ID ); ?>" style="<?php echo ( ( '' !== $harikrutfiwu_url ) ? 'display:none' : '' ); ?>">
				<input id="harikrutfiwu_pvar_url_<?php echo esc_attr( $variation->ID ); ?>" class="harikrutfiwu_pvar_url" type="text" name="harikrutfiwu_pvar_url[<?php echo esc_attr( $variation->ID ); ?>]" placeholder="<?php esc_attr_e( 'Product Variation Image URL', 'featured-image-with-url' ); ?>" value="<?php echo esc_attr( $harikrutfiwu_url ); ?>"/>
				<a id="harikrutfiwu_pvar_preview_<?php echo esc_attr( $variation->ID ); ?>" class="harikrutfiwu_pvar_preview button" data-id="<?php echo esc_attr( $variation->ID ); ?>">
					<?php esc_html_e( 'Preview', 'featured-image-with-url' ); ?>
				</a>
			</div>
			<?php
			$nonce_name  = 'harikrutfiwu_pvar_url_' . $variation->ID . '_nonce';
			$action_name = $nonce_name . '_action';
			wp_nonce_field( $action_name, $nonce_name );
			?>
		</div>
		<?php
	}

	/**
	 * Save Featured image by URL for Product variation
	 *
	 * @since 1.0.0
	 * @param int $variation_id Variation ID.
	 * @param int $i            Loop.
	 * @return void
	 */
	public function harikrutfiwu_save_product_variation_image( $variation_id, $i ) {
		global $harikrutfiwu;
		$nonce_name = 'harikrutfiwu_pvar_url_' . $variation_id . '_nonce';

		if ( isset( $_POST[ $nonce_name ] ) && wp_verify_nonce( sanitize_key( $_POST[ $nonce_name ] ), $nonce_name . '_action' ) ) {
			$image_url = isset( $_POST['harikrutfiwu_pvar_url'][ $variation_id ] ) ? esc_url_raw( wp_unslash( $_POST['harikrutfiwu_pvar_url'][ $variation_id ] ) ) : '';
			if ( ! empty( $image_url ) ) {
				$img_url = get_post_meta( $variation_id, $this->image_meta_url, true );
				if ( is_array( $img_url ) && isset( $img_url['img_url'] ) && $image_url === $img_url['img_url'] ) {
						$image_url = array(
							'img_url' => $image_url,
							'width'   => $img_url['width'],
							'height'  => $img_url['height'],
						);
				} else {
					$imagesize = $harikrutfiwu->common->get_image_sizes( $image_url );
					$image_url = array(
						'img_url' => $image_url,
						'width'   => isset( $imagesize[0] ) ? $imagesize[0] : '',
						'height'  => isset( $imagesize[1] ) ? $imagesize[1] : '',
					);
				}
				update_post_meta( $variation_id, $this->image_meta_url, $image_url );
			} else {
				delete_post_meta( $variation_id, $this->image_meta_url );
			}
		}
	}

	/**
	 * Sanitize variables using sanitize_text_field and wp_unslash.
	 *
	 * @param string|array $var Data to sanitize.
	 * @return string|array
	 */
	public function harikrutfiwu_sanitize( $var ) {
		if ( is_array( $var ) ) {
			return array_map( array( $this, 'harikrutfiwu_sanitize' ), $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( wp_unslash( $var ) ) : $var;
		}
	}

	/**
	 * Display notices for migration from "Featured Image by URL" plugin.
	 *
	 * @return void
	 */
	public function maybe_display_migrate_from_fibu_notices() {
		// Check if the migration was successful.
		if ( isset( $_GET['fibu_migration'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$is_migrated = sanitize_key( $_GET['fibu_migration'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( 'success' === $is_migrated ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php esc_html_e( 'The migration from the Featured Image by URL plugin was successful.', 'featured-image-with-url' ); ?>
					</p>
				</div>
				<?php
			} elseif ( 'dismiss' === $is_migrated ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php esc_html_e( 'The migration notice has been dismissed.', 'featured-image-with-url' ); ?>
					</p>
				</div>
				<?php
				return;
			}
		}

		$is_active = is_plugin_active( 'featured-image-by-url/featured-image-by-url.php' );

		// Check if the plugin is active and not already migrated or dismissed.
		if ( $is_active ) {
			$is_migrated         = get_option( 'harikrutfiwu_migrated_from_fibu', false );
			$is_notice_dismissed = get_option( 'harikrutfiwu_migration_notice_dismissed', false );
			if ( $is_migrated || $is_notice_dismissed ) {
				return;
			}
			?>
			<div class="notice notice-info is-dismissible">
				<p>
					<?php
					printf(
						/* translators: 1: Plugin name, 2: Migration URL */
						esc_html__( 'You are currently using the %1$s plugin, which has been closed and is no longer receiving maintenance. To ensure the uninterrupted functionality of the plugin, please migrate your data from %1$s to %2$s.', 'featured-image-with-url' ),
						'<strong>' . esc_html__( 'Featured Image by URL', 'featured-image-by-url' ) . '</strong>',
						'<strong>' . esc_html__( 'Featured Image with URL', 'featured-image-with-url' ) . '</strong>'
					);
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'harikrutfiwu_migrate_from_fibu', admin_url( 'admin-post.php' ) ), 'harikrutfiwu_migrate_from_fibu_action', 'harikrutfiwu_migrate_from_fibu_nonce' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Migrate Now', 'featured-image-with-url' ); ?>
					</a>
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'harikrutfiwu_migration_notice_dismissed', admin_url( 'admin-post.php' ) ), 'harikrutfiwu_migration_notice_dismissed_action', 'harikrutfiwu_migration_notice_dismissed_nonce' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Dismiss', 'featured-image-with-url' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Handle migration from "Featured Image by URL" plugin.
	 *
	 * @return void
	 */
	public function handle_migration_from_fibu() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_GET['harikrutfiwu_migrate_from_fibu_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['harikrutfiwu_migrate_from_fibu_nonce'] ), 'harikrutfiwu_migrate_from_fibu_action' ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'featured-image-with-url' ) );
		}

		// Migrate data from "Featured Image by URL" plugin.
		$this->migrate_from_fibu();

		// Redirect to the settings page.
		wp_safe_redirect( admin_url( 'options-general.php?page=harikrutfiwu&fibu_migration=success' ) );
		exit;
	}

	/**
	 * Migrate data from "Featured Image by URL" plugin.
	 *
	 * @return void
	 */
	public function migrate_from_fibu() {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct SQL query is required here.

		// Migrate the image url for the featured image.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $wpdb->postmeta SET meta_key = %s WHERE meta_key = %s",
				'_harikrutfiwu_url',
				'_knawatfibu_url'
			)
		);

		// Migrate the image alt for the featured image.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $wpdb->postmeta SET meta_key = %s WHERE meta_key = %s",
				'_harikrutfiwu_alt',
				'_knawatfibu_alt'
			)
		);

		// Migrate the image url for the product gallery.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $wpdb->postmeta SET meta_key = %s WHERE meta_key = %s",
				'_harikrutfiwu_wcgallary',
				'_knawatfibu_wcgallary'
			)
		);

		// phpcs:enable

		// Migrate the settings.
		$settings      = get_option( HARIKRUTFIWU_OPTIONS, array() );
		$fibu_settings = get_option( 'knawatfibu_options', array() );
		if ( empty( $settings ) && ! empty( $fibu_settings ) ) {
			$settings = array(
				'harikrutfiwu_disabled_posttypes' => isset( $fibu_settings['disabled_posttypes'] ) ? $fibu_settings['disabled_posttypes'] : array(),
				'harikrutfiwu_resize_images'      => isset( $fibu_settings['resize_images'] ) ? $fibu_settings['resize_images'] : false,
			);

			// Save the settings.
			update_option( HARIKRUTFIWU_OPTIONS, $settings );
		}

		// Set the "migrated_from_fibu" option to true.
		update_option( 'harikrutfiwu_migrated_from_fibu', true );
	}

	/**
	 * Dismiss the migration notice.
	 *
	 * @return void
	 */
	public function dismiss_fibu_migration_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_GET['harikrutfiwu_migration_notice_dismissed_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['harikrutfiwu_migration_notice_dismissed_nonce'] ), 'harikrutfiwu_migration_notice_dismissed_action' ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'featured-image-with-url' ) );
		}

		// Set the "migration_notice_dismissed" option to true.
		update_option( 'harikrutfiwu_migration_notice_dismissed', true );

		// Redirect to the settings page.
		wp_safe_redirect( admin_url( 'options-general.php?page=harikrutfiwu&fibu_migration=dismiss' ) );
		exit;
	}

	/**
	 * Add "fibu_migration" in list of query variable names to remove.
	 *
	 * @param [] $removable_query_args An array of query variable names to remove from a URL.
	 * @return []
	 */
	public function removable_query_args( array $removable_query_args ): array {
		$removable_query_args[] = 'fibu_migration';
		return $removable_query_args;
	}
}
