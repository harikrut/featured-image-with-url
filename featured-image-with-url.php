<?php
/**
 * Plugin Name:       Featured Image with URL
 * Plugin URI:        https://wordpress.org/plugins/featured-image-with-url/
 * Description:       This plugin allows to use an external URL Images as Featured Image for your post types. Includes support for Product Gallery (WooCommerce).
 * Version:           1.0.0
 * Author:            Harikrut Technolab
 * Author URI:        https://www.harikrut.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       featured-image-with-url
 * Domain Path:       /languages
 *
 * @package     Featured_Image_With_URL
 * @author      Harikrut Technolab <harikruttech@gmail.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set up the plugin constants.
 */
// Plugin version.
if ( ! defined( 'HARIKRUTFIWU_VERSION' ) ) {
	define( 'HARIKRUTFIWU_VERSION', '1.0.0' );
}

// Plugin folder Path.
if ( ! defined( 'HARIKRUTFIWU_PLUGIN_DIR' ) ) {
	define( 'HARIKRUTFIWU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin folder URL.
if ( ! defined( 'HARIKRUTFIWU_PLUGIN_URL' ) ) {
	define( 'HARIKRUTFIWU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin root file.
if ( ! defined( 'HARIKRUTFIWU_PLUGIN_FILE' ) ) {
	define( 'HARIKRUTFIWU_PLUGIN_FILE', __FILE__ );
}

// Options.
if ( ! defined( 'HARIKRUTFIWU_OPTIONS' ) ) {
	define( 'HARIKRUTFIWU_OPTIONS', 'harikrutfiwu_options' );
}

// Gallary meta key.
if ( ! defined( 'HARIKRUTFIWU_WCGALLARY' ) ) {
	define( 'HARIKRUTFIWU_WCGALLARY', '_harikrutfiwu_wcgallary' );
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-featured-image-with-url.php';

/**
 * The main function for that returns Featured_Image_With_URL
 *
 * The main function responsible for returning the one true Featured_Image_With_URL
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $harikrutfiwu = run_harikrutfiwu(); ?>
 *
 * @since 1.0.0
 * @return object|Featured_Image_With_URL The one true Featured_Image_With_URL Instance.
 */
function run_harikrutfiwu() {
	return Featured_Image_With_URL::instance();
}

// Get Featured_Image_With_URL Running.
$GLOBALS['harikrutfiwu'] = run_harikrutfiwu();
