<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.xllentech.com
 * @since      1.1.0
 *
 * @package    Xllentech_Salat_Timings
 * @subpackage Xllentech_Salat_Timings/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Xllentech_Salat_Timings
 * @subpackage Xllentech_Salat_Timings/includes
 * @author     Your Name <email@example.com>
 */
class Xllentech_Salat_Timings_Activator {
	
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		$instance = new Xllentech_Salat_Timings_Activator();
		
		$pre = 'xllentech_salat_timings';
		$firstActivate = get_option($pre.'_first_activate');
		if ( empty( $firstActivate ) ) {
			update_option( $pre.'_first_activate', time() );
		}
		
		if ( is_admin() && get_option('xllentech_salat_timings_rd_notice_hidden') != 1 && time() - get_option('xllentech_salat_timings_first_activate') >= (14*86400) ) {
			add_action('admin_notices', array( $instance, 'xllentech_salat_timings_rd_notice' ) );
			add_action('wp_ajax_xllentech_salat_timings_rd_notice_hide', array( $instance, 'xllentech_salat_timings_rd_notice_hide' ) );
		}

		register_setting(
          'xxllentech_salat_timings_options_group',  // settings section
          'xllentech_salat_timings_options' // setting name
		);
		
		$xllentech_salat_timings_options = get_option("xllentech_salat_timings_options");
		
		if ( ! isset( $xllentech_salat_timings_options ) || ! is_array( $xllentech_salat_timings_options ) ) {
			
			$xllentech_salat_timings_options = array(
				"latitude" => 51.044270,
				"longitude" => -114.087033,
				"timezone" => "America/Edmonton",
				"method" => 0,
				"Custom" => "16,0,4,0,14",
				"xst_display_asr" => 1,
				"xst_display_isha" => 1,
				"xst_display_zuhr" => "Zuhrain",
				"xst_display_maghrib" => "Maghribain",
				"xst_display_imsak" => 0,
				"xst_imsak_diff" => 10,
				"cityname"=> "Calgary",
				"regionname" => "Alberta",
				"countryname" => "Canada",
				"row_background" => '#ccc',
				"ipstack_access_key"	=> '',
				"timezone_api_key"	=> ''
			);
			
			update_option( "xllentech_salat_timings_options", $xllentech_salat_timings_options );
		}
		
		if ( ! isset( $xllentech_salat_timings_options['timezone_api_key'] ) ) {
			$xllentech_salat_timings_options = get_option('xllentech_salat_timings_options');
			$new_xllentech_salat_timings_options = array(
				"latitude" => $xllentech_salat_timings_options['latitude'],
				"longitude" => $xllentech_salat_timings_options['longitude'],
				"timezone" => $xllentech_salat_timings_options['timezone'],
				"method" => $xllentech_salat_timings_options['method'],
				"Custom" => $xllentech_salat_timings_options['Custom'],
				"xst_display_asr" => $xllentech_salat_timings_options['xst_display_asr'],
				"xst_display_isha" => $xllentech_salat_timings_options['xst_display_isha'],
				"xst_display_zuhr" => $xllentech_salat_timings_options['xst_display_zuhr'],
				"xst_display_maghrib" => $xllentech_salat_timings_options['xst_display_maghrib'],
				"xst_display_imsak" => $xllentech_salat_timings_options['xst_display_imsak'],
				"xst_imsak_diff" => $xllentech_salat_timings_options['xst_imsak_diff'],
				"cityname"=> "Calgary",
				"regionname" => "Alberta",
				"countryname" => "Canada",
				"row_background" => '#ccc',
				"ipstack_access_key"	=> '',
				"timezone_api_key"	=> ''
			);
			
			update_option( "xllentech_salat_timings_options", $new_xllentech_salat_timings_options );
		}
	}

	
	function xllentech_salat_timings_rd_notice() {
		$pre = 'xllentech_salat_timings';
		$slug = 'xllentech-salat-timings';
		echo('
			<div id="'.$pre.'_rd_notice" class="updated notice is-dismissible"><p>Do you use the <strong>Xllentech Salat Timings</strong> plugin?
			Please support our free plugin by <a href="https://wordpress.org/plugins/xllentech-salat-timings/'.$slug.'" target="_blank">writing a review</a>!
			Thanks!</p></div>
			<script>jQuery(document).ready(function($){$(\'#'.$pre.'_rd_notice\').on(\'click\', \'.notice-dismiss\', function(){jQuery.post(ajaxurl, {action:\'xllentech_salat_timings_rd_notice_hide\'})});});</script>
		');
	}
	
	function xllentech_salat_timings_rd_notice_hide() {
		$pre = 'xllentech_salat_timings';
		update_option($pre.'_rd_notice_hidden', 1);
	}
	
}
