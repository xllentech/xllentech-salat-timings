<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.xllentech.com
 * @since      1.1.0
 *
 * @package    Xllentech_Salat_Timings
 * @subpackage Xllentech_Salat_Timings/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Xllentech_Salat_Timings
 * @subpackage Xllentech_Salat_Timings/includes
 * @author     Your Name <email@example.com>
 */
class Xllentech_Salat_Timings_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'xllentech-salat-timings',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
