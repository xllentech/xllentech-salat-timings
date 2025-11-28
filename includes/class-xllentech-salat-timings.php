<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.xllentech.com
 * @since      1.0.0
 *
 * @package    Xllentech_Salat_Timings
 * @subpackage Xllentech_Salat_Timings/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Xllentech_Salat_Timings
 * @subpackage Xllentech_Salat_Timings/includes
 * @author     Abbas Momin <contact@xllentech.com>
 */
class Xllentech_Salat_Timings extends WP_Widget {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Xllentech_Salat_Timings_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

		
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		parent::__construct(
			// Base ID of widget
			'Xllentech_Salat_Timings', 

			// Widget name that appear in UI
			__('XllenTech Salat Timings', 'xllentech-salat-timings'), 

			// Widget description
			array( 'description' => __( 'Display Salat Timings Based on City/Location Coordinates, Timezone!', 'xllentech-salat-timings' ),
				'before_widget' => '',
				'after_widget' => '',
				'before_title' => '',
				'after_title' => '' ) 
		);
			
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		
	}

	// Widget Backend 
	public function form( $instance ) {
		
		if ( isset( $instance[ 'title' ] ) )
			$title = $instance[ 'title' ];
		else
			$title = __( 'New title', 'xllentech-salat-timings' );

		if ( isset( $instance[ 'settings' ] ) )
			$settings = $instance[ 'settings' ];
		else
			$settings = __( 'Preset', 'xllentech-salat-timings' );
		
		if ( isset( $instance[ 'page_id' ] ) )
			$page_id = $instance[ 'page_id' ];
		else
			$page_id = __( '', 'xllentech-salat-timings' );
		
		$xllentech_salat_timings_options = get_option( "xllentech_salat_timings_options" );
		
		if( $settings == 'Preset' ):
			$latitude = $xllentech_salat_timings_options['latitude'];
			$longitude = $xllentech_salat_timings_options['longitude'];
			$timezone = $xllentech_salat_timings_options['timezone'];
			$method = $xllentech_salat_timings_options['method'];
		else:
			$latitude = apply_filters( 'Xllentech_Salat_Timings', $instance['latitude'] );
			$longitude = apply_filters( 'Xllentech_Salat_Timings', $instance['longitude'] );
			$timezone = apply_filters( 'Xllentech_Salat_Timings', $instance['timezone'] );
			$method = apply_filters( 'Xllentech_Salat_Timings', $instance['method'] );
		endif;
		$time_format = apply_filters( 'Xllentech_Salat_Timings', $instance['time_format'] );
		
		// Widget admin form
		?>
		<div class="xllentech_salat_widget_form">
		<p>
			<label for="<?php _e( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php _e( $this->get_field_id( 'title' ) ); ?>" name="<?php _e( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php _e( esc_attr( $title ) ); ?>" />
		</p>
		<p>
		<label for="<?php _e( $this->get_field_id( 'settings' ) ); ?>"><?php _e( 'Values:' ); ?></label>
		<select id="<?php _e( $this->get_field_id( 'settings' ) ); ?>" name="<?php _e( $this->get_field_name( 'settings' ) ); ?>" onChange="showData(this)">
			<option value="Preset" <?php if( $settings == "Preset" ):?> selected="selected" <?php endif ?> >Preset</option>
			<option value="Manual" <?php if( $settings == "Manual" ):?> selected="selected" <?php endif ?> >Manual</option></select>
		</p>
		
		<p>Put -(Minus) in Front of the Number for South Latitude & West Longitude!!</p>
		<table id="settings_data" name="settings_data">
		<tr>
			<td><label for="<?php _e( $this->get_field_id( 'latitude' ) ); ?>"><?php _e( 'Latitude:' ); ?></label> </td>
			<td><input id="<?php _e( $this->get_field_id( 'latitude' ) ); ?>" name="<?php _e( $this->get_field_name( 'latitude' ) ); ?>" type="text" <?php if($latitude=='Enter Latitude'): ?> placeholder="Latitude" <?php else: ?> value="<?php _e( esc_attr( $latitude ) ); ?>" <?php endif ?> size="10" /></td>
		</tr>
		<tr>
			<td><label for="<?php _e( $this->get_field_id( 'longitude' ) ); ?>"><?php _e( 'Longitude:' ); ?></label></td>
			<td><input id="longitude" name="<?php _e( $this->get_field_name( 'longitude' ) ); ?>" type="text" <?php if($longitude=='Enter Longitude'): ?> placeholder="Longitude" <?php else: ?> value="<?php _e( esc_attr( $longitude ) ); ?>" <?php endif ?> size="10"/></td>
		</tr>
		<tr>
			<td><label for="<?php _e( $this->get_field_id( 'timezone' ) ); ?>"><?php _e( 'Timezone:' ); ?></label></td>
			<td><select id="<?php _e( $this->get_field_id( 'timezone' ) ); ?>" name="<?php _e( $this->get_field_name( 'timezone' ) ); ?>" style="max-width:60%;">
			<?php

			$zones = timezone_identifiers_list();
			foreach ($zones as $zone) {
			?>
				<option value="<?php _e( $zone ); ?>" <?php if( $timezone == $zone ){?> selected="selected" <?php } ?>><?php _e( $zone ); ?></option>
			<?php } ?>
			</select>
			</td>
		</tr>
		<tr>
			<td><label for="<?php _e( $this->get_field_id( 'method' ) ); ?>"><?php _e( 'Method:' ); ?></label></td>	
			<td>
			<?php
			if ( isset( $instance[ 'method' ] ) )
				$method = $instance[ 'method' ];
			else
				$method = __( 'Select Method', 'xllentech-salat-timings' );
			?>
			<select id="<?php _e( $this->get_field_id( 'method' ) ); ?>" name="<?php _e( $this->get_field_name( 'method' ) ); ?>"  style="max-width:60%;">
				<option value="0" <?php if( $method == 0 ) echo 'selected="selected"'; ?>>Shia Ithna-Ashari</option>
				<option value="6" <?php if( $method == 6 ) echo 'selected="selected"'; ?>>Custom</option>
			</select></td>
		</tr>
		<tr>
			<td><label for="<?php _e( $this->get_field_id( 'time_format' ) ); ?>"><?php _e( 'Time Format:' ); ?></label></td>	
			<td>
			<?php
			if ( isset( $instance[ 'time_format' ] ) )
				$method = $instance[ 'time_format' ];
			else
				$method = __( 'Select Format', 'xllentech-salat-timings' );
			?>
			<select id="<?php _e( $this->get_field_id( 'time_format' ) ); ?>" name="<?php _e( $this->get_field_name( 'time_format' ) ); ?>"  style="max-width:60%;">
				<option value="24" <?php if( $time_format == 24 ):?> selected="selected" <?php endif ?>>24 Hr</option>
				<option value="12" <?php if( $time_format == 12 ):?> selected="selected" <?php endif ?>>12 Hr</option>
			</select></td>
		</tr>
		</table>
		<p>
			<label for="<?php _e( $this->get_field_id('page_id') ); ?>"><?php _e('Salat Monthly Page ID, if any', 'xllentech-salat-timings'); ?>:</label>
			<input id="<?php _e( $this->get_field_id('page_id') ); ?>" name="<?php _e( $this->get_field_name('page_id') ); ?>" type="text" value="<?php _e( $page_id ); ?>" size="3" />
		</p>
		</div>
		<?php
	}
	
// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		
		$new_instance['title'] = filter_var( $new_instance['title'], FILTER_SANITIZE_STRING );
		$new_instance['settings'] = filter_var( $new_instance['settings'], FILTER_SANITIZE_STRING );
		$new_instance['latitude'] = filter_var( $new_instance['latitude'], FILTER_VALIDATE_FLOAT );
		$new_instance['longitude'] = filter_var( $new_instance['longitude'], FILTER_VALIDATE_FLOAT );
		
		//strpos( $new_instance['timezone'] , '/' ) 
		$new_instance['timezone'] = ( strpos( $new_instance['timezone'] , '/' ) ) ? $new_instance['timezone']:'';
		$new_instance['method'] = filter_var( $new_instance['method'], FILTER_VALIDATE_FLOAT );
		$new_instance['page_id'] = filter_var( $new_instance['page_id'], FILTER_VALIDATE_INT );
		$new_instance['time_format'] = filter_var( $new_instance['time_format'], FILTER_VALIDATE_INT );
		
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['settings'] = ( ! empty( $new_instance['settings'] ) ) ? strip_tags( $new_instance['settings'] ) : '';
		$instance['latitude'] = ( ! empty( $new_instance['latitude'] ) ) ? strip_tags( $new_instance['latitude'] ) : '';
		$instance['longitude'] = ( ! empty( $new_instance['longitude'] ) ) ? strip_tags( $new_instance['longitude'] ) : '';
		
		$instance['timezone'] = ( ! empty( $new_instance['timezone'] ) ) ? strip_tags( $new_instance['timezone'] ) : '';
		$instance['method'] = ( ! empty( $new_instance['method'] ) ) ? strip_tags( $new_instance['method'] ) : '';
		$instance['page_id'] = ( ! empty( $new_instance['page_id'] ) ) ? strip_tags( $new_instance['page_id'] ) : '';
		$instance['time_format'] = ( ! empty( $new_instance['time_format'] ) ) ? strip_tags( $new_instance['time_format'] ) : '';
		return $instance;
	}

	// Creating widget front-end
	// This is where the action happens
	function widget( $args, $instance ) {
		extract( $args );
		
		$xllentech_salat_timings_options = get_option("xllentech_salat_timings_options");
		
		if ( isset( $instance[ 'title' ] ) )
			$title = apply_filters( 'Xllentech_Salat_Timings', $instance['title'] );
		else
			$title = __( 'New title', 'xllentech-salat-timings' );
		
		if ( isset( $instance[ 'settings' ] ) )
			$settings = apply_filters( 'Xllentech_Salat_Timings', $instance['settings'] );
		else
			$settings = __( 'Preset', 'xllentech-salat-timings' );
		
		if ( isset( $instance[ 'page_id' ] ) )
			$page_id = apply_filters( 'Xllentech_Salat_Timings', $instance['page_id'] );
		else
			$page_id = __( '', 'xllentech-salat-timings' );
		
		
		if( $settings=='Preset' ) {
			$latitude = $xllentech_salat_timings_options['latitude'];
			$longitude = $xllentech_salat_timings_options['longitude'];
			$timezone = $xllentech_salat_timings_options['timezone'];
			$method = $xllentech_salat_timings_options['method'];
		}
		else {
			$latitude = apply_filters( 'Xllentech_Salat_Timings', $instance['latitude'] );
			$longitude = apply_filters( 'Xllentech_Salat_Timings', $instance['longitude'] );
			$timezone = apply_filters( 'Xllentech_Salat_Timings', $instance['timezone'] );
			$method = apply_filters( 'Xllentech_Salat_Timings', $instance['method'] );
		}
		
		$time_format = apply_filters( 'Xllentech_Salat_Timings', $instance['time_format'] );
		
		if($method==NULL){
			$method=0;
		}
		
		$output='';
		
		// before and after widget arguments are defined by themes
		_e( $args['before_widget'] );

		if ( ! empty( $title ) )
		_e( $args['before_title'] . $title . $args['after_title'] );

		date_default_timezone_set($timezone);

		$my_lat = floatval( $latitude );
		$my_long = floatval( $longitude );
	//	$output .= $timezone;
			
		$my_currentdate = new DateTime( 'NOW', new DateTimeZone($timezone) );
	//	$my_currentdate1=time();
	
		$today = $my_currentdate->format('Y-m-d 10:10:10');
		
		$mt_offset = $my_currentdate->getOffset()/3600;
		
		$my_currentday = date_format( $my_currentdate, 'j' );
		$my_currentmonth = date_format( $my_currentdate, 'n' );
		$my_currentyear = date_format( $my_currentdate, 'Y' );

		$prayTime = new PrayTimeClass( $method, $xllentech_salat_timings_options['Custom'] );
		$prayTime->setAsrMethod( 4/7 ); //4/7 shadow
		if ( $time_format == 12 )
			$prayTime->setTimeFormat( 1 ); //value 0 = 24 hours , 1 = 12 hours
		else 
			$prayTime->setTimeFormat( 0 ); //value 0 = 24 hours , 1 = 12 hours
		$prayTime->setMoonsighting(2); //Ahmar
		$asr="(J)";
		$ish="(J)";

	//	$date = strtotime( '2017-11-05 10:10');
		$date = strtotime( $today );

		$times = $prayTime->getPrayerTimes( $date, $latitude, $longitude, $mt_offset );
		
		$imsak = date( 'H:i', strtotime( date( $times[0] ) ) - ( $xllentech_salat_timings_options['xst_imsak_diff'] * 60) );
		
		// This is where you run the code and display the output
		?>
		<style>
			table.xst_daily_Default tr:nth-child(odd) {
				background-color:<?php echo $xllentech_salat_timings_options['row_background']; ?>;
			}
		</style>
		<div class="xllentech_salat_widget" align="center">
						<table class="xst_daily_Default">
						<tbody>
					<?php if( $xllentech_salat_timings_options['xst_display_imsak'] == 1 ) { ?>
							<tr>
								<td>Imsak</td>
								<td><?php _e( $imsak ); ?></td>
							</tr>
					<?php } ?>
							<tr>
								<td>Fajr</td>
								<td><?php _e( $times[0] ); ?></td>
							</tr>
							<tr>
								<td>Sunrise</td>
								<td><?php _e( $times[1] ); ?></td>
							</tr>
							<tr>
								<td><?php _e( $xllentech_salat_timings_options['xst_display_zuhr'] ); ?></td>
								<td><?php _e( $times[2] ); ?></td>
							</tr>
					<?php if( $xllentech_salat_timings_options['xst_display_asr'] == 1 ) { ?>
							<tr>
								<td>Asr</td>
								<td><?php _e( $times[3]); ?></td>
							</tr>
					<?php } ?>			
							<tr>
								<td>Sunset</td>
								<td><?php _e( $times[4] ); ?></td>
							</tr>
							<tr>
								<td><?php _e( $xllentech_salat_timings_options['xst_display_maghrib'] ); ?></td>
								<td><?php _e( $times[5] ); ?></td>
			
					<?php if( $xllentech_salat_timings_options['xst_display_isha'] == 1 ) { ?>
							<tr>
								<td>Isha</td>
								<td><?php _e( $times[6] ); ?></td>
							</tr>
					<?php } ?>
							</tr>
		<?php
		if( $page_id != NULL ) :
			$monthly_page_path = get_page_link($page_id);
			_e( '<tr><th colspan="2"><span class="xllentech_salat_widget_link"><a href="'. $monthly_page_path .'">VIEW MONTHLY</a></span></th></tr>' );
		endif;
		?>
		</tbody></table></div>
		<?php
		_e( $args['after_widget'] );
		
	}
	
	
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Xllentech_Salat_Timings_Loader. Orchestrates the hooks of the plugin.
	 * - Xllentech_Salat_Timings_i18n. Defines internationalization functionality.
	 * - Xllentech_Salat_Timings_Admin. Defines all hooks for the admin area.
	 * - Xllentech_Salat_Timings_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		
		/**
		 * The class responsible for calculating salat timings.
		 * 
		 */
		require_once XST_PLUGIN_DIR .'includes/PrayTime.php';
		
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xllentech-salat-timings-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xllentech-salat-timings-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-xllentech-salat-timings-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-xllentech-salat-timings-public.php';
	
		$this->loader = new Xllentech_Salat_Timings_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Xllentech_Salat_Timings_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Xllentech_Salat_Timings_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Xllentech_Salat_Timings_Admin( XST_PLUGIN_BASENAME, XST_PLUGIN_VERSION );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		//$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		$this->loader->add_filter( 'plugin_action_links_' . XST_PLUGIN_BASENAME, $plugin_admin, 'xllentech_salat_timings_action_links' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'xllentech_salat_timings_admin_menu' );
		//add_filter( 'plugins_loaded', $plugin_admin, 'xllentech_salat_timings_plugins_loaded' );
		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Xllentech_Salat_Timings_Public( XST_PLUGIN_BASENAME, XST_PLUGIN_VERSION );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		//The ajax to refresh monthly salat timings table for new location data OR just month and year
		$this->loader->add_action('wp_ajax_xst_salat_submit_reload', $plugin_public,'xst_salat_submit_reload');
		$this->loader->add_action('wp_ajax_nopriv_xst_salat_submit_reload', $plugin_public,'xst_salat_submit_reload');
	
		//The function to get ip and get location data for Xllentech Salat Timings
		$this->loader->add_filter( 'xst_get_salat_location', $plugin_public, 'xst_get_salat_location', 10, 1 );
		
		add_shortcode( 'xllentech-salat-timings-monthly', array( $plugin_public, 'xllentech_display_salat_monthly' ) );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Xllentech_Salat_Timings_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

}
