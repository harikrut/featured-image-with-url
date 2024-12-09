<?php
/**
 * Class HARIKRUTFIWU
 *
 * @package HARIKRUTFIWU
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'HARIKRUTFIWU' ) ) :

	/**
	 * Main Featured Image with URL class
	 */
	class HARIKRUTFIWU {

		/** Singleton *************************************************************/
		/**
		 * HARIKRUTFIWU The one true HARIKRUTFIWU.
		 *
		 * @var HARIKRUTFIWU $instance
		 */
		private static $instance;

		/**
		 * Admin Instance.
		 *
		 * @var HARIKRUTFIWU_Admin $admin
		 */
		public $admin;

		/**
		 * Common Instance.
		 *
		 * @var HARIKRUTFIWU_Common $common
		 */
		public $common;

		/**
		 * Main Featured Image with URL Instance.
		 *
		 * Insure that only one instance of HARIKRUTFIWU exists in memory at any one time.
		 * Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 * @static object $instance
		 * @uses HARIKRUTFIWU::setup_constants() Setup the constants needed.
		 * @uses HARIKRUTFIWU::includes() Include the required files.
		 * @uses HARIKRUTFIWU::load_textdomain() load the language files.
		 * @see harikrutfiwu_run()
		 * @return object|Featured_Image_with_URL the one true Featured Image with URL.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof HARIKRUTFIWU ) ) {
				self::$instance = new HARIKRUTFIWU();

				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

				self::$instance->includes();
				self::$instance->admin  = new HARIKRUTFIWU_Admin();
				self::$instance->common = new HARIKRUTFIWU_Common();

			}
			return self::$instance;
		}

		/** Magic Methods *********************************************************/

		/**
		 * A dummy constructor to prevent HARIKRUTFIWU from being loaded more than once.
		 *
		 * @since 1.0.0
		 * @see HARIKRUTFIWU::instance()
		 * @see harikrutfiwu_run()
		 */
		private function __construct() {
			/* Do nothing here */
		}

		/**
		 * A dummy magic method to prevent HARIKRUTFIWU from being cloned.
		 *
		 * @since 1.0.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'featured-image-with-url' ), '1.0.3' );
		}

		/**
		 * A dummy magic method to prevent HARIKRUTFIWU from being unserialized.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'featured-image-with-url' ), '1.0.3' );
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function includes() {
			require_once HARIKRUTFIWU_PLUGIN_DIR . 'includes/class-harikrutfiwu-admin.php';
			require_once HARIKRUTFIWU_PLUGIN_DIR . 'includes/class-harikrutfiwu-common.php';
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
