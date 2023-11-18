<?php
/**
 * Class Featured_Image_With_URL
 *
 * @package Featured_Image_With_URL
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Featured_Image_With_URL' ) ) :

	/**
	 * Main Featured Image with URL class
	 */
	class Featured_Image_With_URL {

		/** Singleton *************************************************************/
		/**
		 * Featured_Image_With_URL The one true Featured_Image_With_URL.
		 *
		 * @var Featured_Image_With_URL $instance
		 */
		private static $instance;

		/**
		 * Admin Instance.
		 *
		 * @var Featured_Image_With_URL_Admin $admin
		 */
		public $admin;

		/**
		 * Common Instance.
		 *
		 * @var Featured_Image_With_URL_Common $common
		 */
		public $common;

		/**
		 * Main Featured Image with URL Instance.
		 *
		 * Insure that only one instance of Featured_Image_With_URL exists in memory at any one time.
		 * Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 * @static object $instance
		 * @uses Featured_Image_With_URL::setup_constants() Setup the constants needed.
		 * @uses Featured_Image_With_URL::includes() Include the required files.
		 * @uses Featured_Image_With_URL::load_textdomain() load the language files.
		 * @see run_harikrutfiwu()
		 * @return object|Featured_Image_with_URL the one true Featured Image with URL.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Featured_Image_With_URL ) ) {
				self::$instance = new Featured_Image_With_URL();

				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

				self::$instance->includes();
				self::$instance->admin  = new Featured_Image_With_URL_Admin();
				self::$instance->common = new Featured_Image_With_URL_Common();

			}
			return self::$instance;
		}

		/** Magic Methods *********************************************************/

		/**
		 * A dummy constructor to prevent Featured_Image_With_URL from being loaded more than once.
		 *
		 * @since 1.0.0
		 * @see Featured_Image_With_URL::instance()
		 * @see run_harikrutfiwu()
		 */
		private function __construct() {
			/* Do nothing here */
		}

		/**
		 * A dummy magic method to prevent Featured_Image_With_URL from being cloned.
		 *
		 * @since 1.0.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'featured-image-with-url' ), '1.0.0' );
		}

		/**
		 * A dummy magic method to prevent Featured_Image_With_URL from being unserialized.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'featured-image-with-url' ), '1.0.0' );
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function includes() {
			require_once HARIKRUTFIWU_PLUGIN_DIR . 'includes/class-featured-image-with-url-admin.php';
			require_once HARIKRUTFIWU_PLUGIN_DIR . 'includes/class-featured-image-with-url-common.php';
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function load_textdomain() {

			load_plugin_textdomain(
				'featured-image-with-url',
				false,
				basename( dirname( __FILE__ ) ) . '/languages'
			);
		}
	}

endif; // End If class exists check.
