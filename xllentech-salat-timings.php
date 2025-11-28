<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://xllentech.com
 * @since             1.0.0
 * @package           Xllentech_Salat_Timings
 *
 * @wordpress-plugin
 * Plugin Name:       XllenTech Salat Timings
 * Plugin URI:        https://wordpress.org/plugins/xllentech-salat-timings/
 * Description:       Display Salat Timings Daily and Monthly for Shia Ithna-Ashari Muslims Around the World
 * Version:           1.3.0
 * Author:            XllenTech Solutions
 * Author URI:        https://xllentech.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       xllentech-salat-timings
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( "XST_PLUGIN_VERSION" )) 	define( "XST_PLUGIN_VERSION",  "1.3.0");
if ( ! defined( "XST_PLUGIN_DIR" )) define( "XST_PLUGIN_DIR", plugin_dir_path( __FILE__ ));
if ( ! defined( 'XST_PLUGIN_URL' ) ) define( 'XST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	// Plugin Basename aka: "pluginfolder/mainfile.php"
if ( ! defined( 'XST_PLUGIN_BASENAME' ) ) define( 'XST_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-xllentech-salat-timings-activator.php
 */
function activate_xllentech_salat_timings() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xllentech-salat-timings-activator.php';
	Xllentech_Salat_Timings_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-xllentech-salat-timings-deactivator.php
 */
function deactivate_xllentech_salat_timings() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xllentech-salat-timings-deactivator.php';
	Xllentech_Salat_Timings_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_xllentech_salat_timings' );
register_deactivation_hook( __FILE__, 'deactivate_xllentech_salat_timings' );

// Register and load the widget
function salat_load_widget() {
	register_widget( 'Xllentech_Salat_Timings' );
}
add_action( 'widgets_init', 'salat_load_widget' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-xllentech-salat-timings.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_xllentech_salat_timings() {

	$plugin = new Xllentech_Salat_Timings();
	$plugin->run();

}
run_xllentech_salat_timings();