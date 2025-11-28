<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.xllentech.com
 * @since      1.1.0
 *
 * @package    Xllentech_Salat_Timings
 * @subpackage Xllentech_Salat_Timings/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Xllentech_Salat_Timings
 * @subpackage Xllentech_Salat_Timings/admin
 * @author     Your Name <email@example.com>
 */
class Xllentech_Salat_Timings_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	// Add settings link in plugins list
	
	function xllentech_salat_timings_action_links($links) {

		$xc_settings_link = array(
			'<a href="' . admin_url( 'options-general.php?page=xllentech_salat_timings_options' ) . '">Settings</a>',	);
		
		return array_merge( $links, $xc_settings_link );

	}

	// Get default plugin options
	function xllentech_salat_timings_default_options() {
		
		//global $xllentech_salat_timings_options;
		
		if ( ! isset( $xllentech_salat_timings_options ) ) {
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
				"cityname"=> $xllentech_salat_timings_options['cityname'],
				"regionname" => $xllentech_salat_timings_options['regionname'],
				"countryname" => $xllentech_salat_timings_options['countryname'],
				"row_background" => $xllentech_salat_timings_options['row_background'],
				"ipstack_access_key"	=> '',
				"timezone_api_key"	=> ''
			);
			
			update_option( "xllentech_salat_timings_options", $new_xllentech_salat_timings_options );
		}
				
		return $xllentech_salat_timings_options;
	}

	// Retrieve plugin options
	function xllentech_salat_timings_get_option( $option ) {
		
		$xllentech_salat_timings_options = get_option("xllentech_salat_timings_options");
		
		if ( ! isset( $xllentech_salat_timings_options[$option] ) ) {
			$xllentech_salat_timings_options = array_merge( $this->xllentech_salat_timings_default_options(), get_option( 'xllentech_salat_timings_options', array() ) );
		}
		
		return ( isset( $xllentech_salat_timings_options[$option] ) ? $xllentech_salat_timings_options[$option] : null );
	}

	// Save plugin options
	function xllentech_salat_timings_set_options( $options ) {
		
		//$defaultOptions = $this->xllentech_salat_timings_default_options();
		//$xllentech_salat_timings_options = get_option("xllentech_salat_timings_options");
		//$xllentech_salat_timings_options['']=0;
		
		return update_option( 'xllentech_salat_timings_options', $options );
	}
	
	// Add admin page
	function xllentech_salat_timings_admin_menu() {
		
		$xst_settings_page=add_submenu_page(
			  'options-general.php',          // admin page slug
			  __( 'Xllentech Salat Settings', 'xllentech-salat-timings' ), // page title
			  __( 'Xllentech Salat Settings', 'xllentech-salat-timings' ), // menu title
			  'manage_options',               // capability required to see the page
			  'xllentech_salat_timings_options',                // admin page slug, e.g. options-general.php?page=xllentech_salat_options
			  array( $this, 'xllentech_salat_timings_options_page' )           // callback function to display the options page
		 );
	}

	function xllentech_salat_timings_validate_options( $options ) {
		
		$xllentech_salat_timings_options = get_option("xllentech_salat_timings_options");
		
		$validated_options = [];
		
		$checkboxFields = array( 'xst_display_imsak', 'xst_display_asr', 'xst_display_isha' );
			
		foreach ( $checkboxFields as $cbField ) {
			$validated_options[$cbField] = ( empty( $options[$cbField] ) ? 0 : 1 );
		}
				
		if( empty($options['xst_countryname']) )
			$validated_options['countryname'] = 'Canada';
		else
			$validated_options['countryname'] = filter_var( $options['xst_countryname'], FILTER_SANITIZE_STRING );
				
		if( empty($options['cityname']) )
			$options['cityname'] = 'Calgary';
		else
			$validated_options['cityname'] = filter_var( $options['cityname'], FILTER_SANITIZE_STRING );
		
		if( empty($options['xst_regionname']) )
			$validated_options['regionname'] = 'Alberta';
		else 
			$validated_options['regionname'] = filter_var( $options['xst_regionname'], FILTER_SANITIZE_STRING );
		
		if( is_numeric($options["latitude"]) )
			$validated_options['latitude'] = floatval( $options['latitude'] );
		else
			return 'latitude';
		
		if( ! empty($options["longitude"]) )
			$validated_options['longitude'] = filter_var( $options['longitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		else
			return 'longitude';
		
		$validated_options['timezone'] = ( strpos( $options['timezone'] , '/' ) ) ? $options['timezone']:'';
		
		if( is_numeric($options["method"]) )
			$validated_options['method'] = $options['method'];
		else
			return 'method';
		
		if( is_numeric($options["xst_imsak_diff"]) )
			$validated_options['xst_imsak_diff'] = intval( $options['xst_imsak_diff'] );
		else
			$validated_options['xst_imsak_diff'] = 10;
		
		if( empty($options['xst_display_zuhr']) )
			$options['xst_display_zuhr'] = 'Zuhr';
		else
			$validated_options['xst_display_zuhr'] = filter_var( $options['xst_display_zuhr'], FILTER_SANITIZE_STRING );
		
		if( empty($options['xst_display_maghrib']) )
			$options['xst_display_maghrib'] = 'Maghrib';
		else
			$validated_options['xst_display_maghrib'] = filter_var( $options['xst_display_maghrib'], FILTER_SANITIZE_STRING );
		
				
		if( is_numeric( $options["fajr_angle"] ) )		
			$options['fajr_angle'] = floatval( $options['fajr_angle'] );
		else
			return 'fajr_angle';
		
		if( is_numeric($options["maghrib_select"]) )
			$options['maghrib_select'] = intval( $options['maghrib_select'] );
		else
			return 'maghrib_select';
		
		if( is_numeric($options["maghrib_value"]) ) 
			$options['maghrib_value'] = floatval( $options['maghrib_value'] );
		else
			return 'maghrib_value';
		
		if( is_numeric($options["isha_select"]) )
			$options['isha_select'] = intval( $options['isha_select'] );
		else
			return 'isha_select';
		
		if( is_numeric($options["isha_value"]) )
			$options['isha_value'] = floatval( $options['isha_value'] );
		else
			return 'isha_value';
		
		$validated_options['Custom'] = $options['fajr_angle'] . ',' . $options['maghrib_select'] . ',' . $options['maghrib_value'] . ',' . $options['isha_select'] . ',' . $options['isha_value'];
		
		$validated_options['row_background'] = filter_var( $options['row_background'], FILTER_SANITIZE_STRING );
		
		$validated_options['ipstack_access_key'] = filter_var( $options['ipstack_access_key'], FILTER_SANITIZE_STRING );
		$validated_options['timezone_api_key'] = filter_var( $options['timezone_api_key'], FILTER_SANITIZE_STRING );
		//$validated_options = array_merge( $options, $validated_options );
		
		return $validated_options;
		
	}
	/**
	 * Build the options page
	 */
	function xllentech_salat_timings_options_page() {
				
		?>
		<div class="wrap">
		<h2>Xllentech Salat Settings</h2>
		
		<?php
		
		if ( ! empty( $_POST ) && check_admin_referer( 'xlst-settings-options-action','xlst-settings-options-nonce' ) && current_user_can( 'manage_options' ) ) {
			
			$validated_options = $this->xllentech_salat_timings_validate_options( $_POST );
			//print_r($validated_options);
			if( $validated_options == NULL || ! is_array($validated_options) ) {
				echo '<div class="error notice is-dismissible"><p>' . __( 'Values validation failed, The settings not saved. Invalid: ', 'xllentech-salat-timings' ) . $validated_options . '</p></div>';
			} else {
				
				$result = $this->xllentech_salat_timings_set_options( $validated_options );
						
				if( $result == NULL )
					echo '<div class="error notice is-dismissible"><p>' . __( 'Invalid values or no changes, settings not saved.', 'xllentech-salat-timings' ) . '</p></div>';
				else
					echo '<div class="notice notice-success"><p>' . __( 'The settings have been saved.', 'xllentech-salat-timings' ) . '</p></div>';
			
			}
				
		}
		
		//$xst_options = get_option("xst_options");

		wp_create_nonce( 'xlst-settings-options-nonce' );
		
		/**
		if( isset( $_POST["update_settings"] ) && check_admin_referer( 'xlst-settings-options-action','xlst-settings-options-nonce' ) && current_user_can( 'manage_options' ) ) {
			
			if( ! empty( $_POST["latitude"] ) 
					&& ! empty($_POST["longitude"]) 
						&& is_numeric($_POST["latitude"]) 
							&& is_numeric($_POST["longitude"]) 
								&& is_numeric($_POST["method"]) ):
			
				$xst_options['latitude'] = $_POST["latitude"];
				$xst_options['longitude'] = $_POST['longitude'];
				$xst_options['timezone'] = $_POST['timezone'];
				$xst_options['method'] = $_POST['method'];
				
				if( !empty($_POST['xst_display_imsak']) )
					$xst_options['xst_display_imsak'] ='Yes';
				else
					$xst_options['xst_display_imsak'] = 'No';
				
				if( !empty($_POST['xst_display_asr']) )
					$xst_options['xst_display_asr'] ='Yes';
				else
					$xst_options['xst_display_asr'] = 'No';
				
				if( !empty($_POST['xst_display_isha']) )
					$xst_options['xst_display_isha'] ='Yes';
				else
					$xst_options['xst_display_isha'] = 'No';
				
				$xst_options['xst_imsak_diff'] = $_POST['xst_imsak_diff'];
				$xst_options['xst_display_zuhr'] = $_POST['xst_display_zuhr'];
				$xst_options['xst_display_maghrib'] = $_POST['xst_display_maghrib'];
				
				update_option("xst_options",$xst_options);
				?>
				<div class="updated notice is-dismissible"><p><strong><?php _e( 'Location and Preferences Updated!', 'xllentech-salat-timings' ); ?></strong></p></div> <?php
			else: ?>
				<div class="error notice is-dismissible"><p><strong><?php _e( 'Oops, Looks like You did not enter correct data, Please try again, Make sure latitude, longitude are only digits!', 'xllentech-salat-timings' ); ?></strong></p></div> <?php
			endif;
			
		} elseif( isset( $_POST["method_settings"] ) && check_admin_referer( 'xlst-settings-options-action','xlst-settings-options-nonce' ) && current_user_can( 'manage_options' ) ) {
			
			if( is_numeric( $_POST["fajr_angle"] ) 
					&& is_numeric($_POST["maghrib_select"]) 
						&& is_numeric($_POST["maghrib_value"]) 
							&& is_numeric($_POST["isha_select"]) 
								&& is_numeric($_POST["isha_value"]) ):
								
				$xst_options['Custom'] = trim( $_POST['fajr_angle'] ). ',' . trim( $_POST['maghrib_select'] ). ',' . trim( $_POST['maghrib_value'] ). ',' . trim( $_POST['isha_select'] ). ',' . trim( $_POST['isha_value'] );
			
				update_option("xst_options",$xst_options);
				?>
				<div class="updated notice is-dismissible"><p><strong><?php _e( 'Custom Calculation Method Values Saved!', 'xllentech-salat-timings' ); ?></strong></p></div> <?php
			else: ?>
				<div class="error notice is-dismissible"><p><strong><?php _e( 'Oops, Looks like You did not enter correct data, Please Make sure all custom values are numbers only!', 'xllentech-salat-timings' ); ?></strong></p></div> <?php
			endif;
		} else { }
		*/
		
		$timezone = $this->xllentech_salat_timings_get_option( 'timezone' );
		date_default_timezone_set( $timezone );
		$offset =  date('Z') / 3600;
		list( $latitude, $longitude, $method, $offset) = array( $this->xllentech_salat_timings_get_option('latitude'), $this->xllentech_salat_timings_get_option('longitude'), $this->xllentech_salat_timings_get_option('method'), $offset);
	//	$xst_options['Custom']    = "16, 0, 4, 0, 14";  //changed maghrib to 4 degrees
			
		$method_custom = explode( ",", $this->xllentech_salat_timings_get_option('Custom') );
		include_once XST_PLUGIN_DIR . 'includes/country-state.php';
		//echo $country_state_list[0][0];
		
		?>
		
		<div id="poststuff" class="xllentech_salat_options">
			
			<div id="postbox-container" class="postbox-container">
		
				<div class="meta-box-sortables ui-sortable" id="normal-sortables">
					<div class="postbox " id="section1">
						<div title="Click to toggle" class="handlediv icon32"><br/></div>
						<h3 class="hndle"><span>Location and Preferences</span></h3>
						<form name="location_data" method="post" action="#">
						<div class="inside">
			
							<ul class="xst_options_ul">
								<li>
									<label for="xst_countryname">Country:</label>
									<select name="xst_countryname" id="xst_countryname">
									<?php
										foreach ($country_state_list as $countries) {
											echo ($this->xllentech_salat_timings_get_option( 'countryname' ) === $countries[0]) ? '<option value="'.$countries[0].'" selected="selected">'.$countries[1].'</option>':'<option value="'.$countries[0].'">'.$countries[1].'</option>';
										}
									?>
									</select>
								</li>
								<li>
									<label for="xst_regionname">State/Province/Region:<p class="description">Save settings to reload.</p></label>
									<?php
										foreach ($country_state_list as $countries) {
											$region_list[$countries[0]] = explode( '|', $countries[2] );
										}
										//print_r($region_list['TN']);
										//echo $this->xllentech_salat_timings_get_option( 'countryname' );
									?>
									<div id="xst_region_div" name="xst_region_div">
									<select name="xst_regionname" id="xst_regionname">
									<?php
										foreach ($region_list[$this->xllentech_salat_timings_get_option( 'countryname' )] as $region) {
											echo ($this->xllentech_salat_timings_get_option( 'regionname' ) === $region) ? '<option value="'.$region.'" selected="selected">'.$region.'</option>':'<option value="'.$region.'">'.$region.'</option>';
										}
									?>
									</select>
									</div>
								</li>
								<li>
									<label for="cityname">City:</label>
									<input type="text" name="cityname" required value="<?php _e( $this->xllentech_salat_timings_get_option( 'cityname' ) ); ?>">
									
								</li>
								<li>
									<label for="timezone">Time Zone:</label>
									<select id="timezone" name="timezone" style="max-width:60%;">
										<?php
										$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
										$xstp_timezone = $this->xllentech_salat_timings_get_option( 'timezone' );
										foreach($tzlist as $timezone_list) {
									?>
										<option value="<?php _e( $timezone_list ); ?>" <?php if( $xstp_timezone == $timezone_list ) { ?> selected="selected" <?php } ?>><?php _e( $timezone_list ); ?></option>
										<?php } ?>
									</select>
								</li>
								<li>
									<label for="latitude">Latitude:<p class="description">Use Minus(-) for South.</p></label>
									<input type="text" value="<?php echo $this->xllentech_salat_timings_get_option( 'latitude' ); ?>" name="latitude">
								</li>
								
								<li>
									<label for="longitude">Longitude:<p class="description">Use Minus(-) for West.</p></label>
									<input type="text" value="<?php echo $this->xllentech_salat_timings_get_option( 'longitude' ); ?>" name="longitude">
								</li>
								
								<li>
									<label for="method">Method:</label>
									<select id="method" name="method" size="1" style="max-width:40%;">
										<option value="0"<?php if( $this->xllentech_salat_timings_get_option( 'method' ) == 0 )echo ' selected="selected"'; ?>>Shia Ithna-Ashari</option>
										<option value="6"<?php if( $this->xllentech_salat_timings_get_option( 'method' ) == 6 ) echo ' selected="selected"'; ?>>Custom</option>
									</select>
								</li>
								
								<li>
									<label for="xst_display_asr">Optional Timings:</label>
									<input type="checkbox" name="xst_display_imsak" class="display_select" <?php echo $this->xllentech_salat_timings_get_option( 'xst_display_imsak' ) ? 'checked ="checked"' : ''; ?>><?php esc_attr_e( 'Imsak', 'xllentech-salat-timings' ); ?><span style="margin-right:20px"></span>
									
									<input type="checkbox" name="xst_display_asr" class="display_select" <?php echo $this->xllentech_salat_timings_get_option( 'xst_display_asr' ) ? 'checked ="checked"' : ''; ?>><?php esc_attr_e( 'Asr', 'xllentech-salat-timings' ); ?>
									<span style="margin-right:20px"></span>
									
									<input type="checkbox" name="xst_display_isha" class="display_select" <?php echo $this->xllentech_salat_timings_get_option( 'xst_display_isha' ) ? 'checked ="checked"' : ''; ?>><?php esc_attr_e( 'Isha', 'xllentech-salat-timings' ); ?>
								</li>
								
								<li>
									<label for="xst_imsak_diff">Imsak Difference:</label>
									<input type="number" style="width:50px" value="<?php  echo $this->xllentech_salat_timings_get_option( 'xst_imsak_diff' ); ?>" name="xst_imsak_diff"> Minutes
								</li>
								
								<li><label for="xst_display_zuhr">Choose Name:</label>
									<input type="radio" name="xst_display_zuhr" value="Zuhr" class="display_select" <?php echo ( $this->xllentech_salat_timings_get_option( 'xst_display_zuhr' ) == 'Zuhr' ) ? 'checked="checked"' : ''; ?>><?php esc_attr_e( 'Zuhr', 'xllentech-salat-timings' ); ?>
									<span style="margin-right:20px"></span>				
									<input type="radio" name="xst_display_zuhr" value="Zuhrain" class="display_select" <?php echo ( $this->xllentech_salat_timings_get_option( 'xst_display_zuhr' ) == 'Zuhrain' ) ? 'checked="checked"' : ''; ?>><?php esc_attr_e( 'Zuhrain', 'xllentech-salat-timings' ); ?>
								</li>
								
								<li>
									<label for="xst_display_maghrib">Choose Name:</label>
									<input type="radio" name="xst_display_maghrib" value="Maghrib" class="display_select" <?php if( $this->xllentech_salat_timings_get_option( 'xst_display_maghrib' ) == 'Maghrib' ) echo 'checked="checked"'; ?>><?php esc_attr_e( 'Maghrib', 'xllentech-salat-timings' ); ?>
									<span style="margin-right:20px"></span>
									<input type="radio" name="xst_display_maghrib" value="Maghribain" class="display_select" <?php if( $this->xllentech_salat_timings_get_option( 'xst_display_maghrib' ) == 'Maghribain' ) echo 'checked="checked"'; ?>><?php esc_attr_e( 'Maghribain', 'xllentech-salat-timings' ); ?>
								</li>
								<li>
									<label for="row_background">Widget Row Background:<p class="description">e.g. #abcdef.</p></label>
									<input type="text"  name="row_background" value="<?php echo $this->xllentech_salat_timings_get_option( 'row_background' ); ?>">
								</li>
								<li>
									<label for="timezone_api_key">IPStack Access Key:<p class="description">For location, visit <a href="http://api.ipstack.com/" target="_blank" >here</a>.</p></label>
									<input type="text"  name="ipstack_access_key" value="<?php echo $this->xllentech_salat_timings_get_option( 'ipstack_access_key' ); ?>">
								</li>
								<li>
									<label for="timezone_api_key">Timezone API Key:<p class="description">For Timezone, visit <a href="https://console.cloud.google.com/apis/credentials" target="_blank">here</a>.</p></label>
									<input type="text"  name="timezone_api_key" value="<?php echo $this->xllentech_salat_timings_get_option( 'timezone_api_key' ); ?>">
								</li>
							</ul>
							
						
							<h3>To customize method, Select Custom in Method under Preferences and change values under Custom Column below.</h3>
							
							<ul class="xst_options_ul xst_methodlist">
								<li style="height:25px">
									<label class="xst_method_column1">Method</label>
									<label class="xst_method_column2">Jafari</label>
									<label class="xst_method_column3">Custom</label>
								<li>
									<label class="xst_method_column1">FA: Fajr Angle</label>
									<label class="xst_method_column2">16</label>
									<input class="xst_method_column3" type="number" style="width:60px;" name="fajr_angle" value="<?php echo $method_custom[0]; ?>">
								</li>
								<li>
									<label class="xst_method_column1">MS: Maghrib selector (0 = angle; 1 = minutes after sunset)</label>
									<label class="xst_method_column2">0</label>
									<input class="xst_method_column3" type="number" name="maghrib_select"  style="width:60px;" value="<?php echo $method_custom[1]; ?>">
								</li>
								<li>
									<label class="xst_method_column1">MV: Maghrib Parameter Value (in angle or minutes)</label>
									<label class="xst_method_column2">4</label>
									<input class="xst_method_column3" type="number" name="maghrib_value" step="0.01"  style="width:60px;" value="<?php echo $method_custom[2]; ?>">	
								</li>
								<li>
									<label class="xst_method_column1">IS: Isha Selector (0 = angle; 1 = minutes after maghrib)</label>
									<label class="xst_method_column2">0</label>
									<input class="xst_method_column3" type="number" name="isha_select"  style="width:60px;" value="<?php echo $method_custom[3]; ?>">
								</li>
								<li>
									<label class="xst_method_column1">IV: Isha Parameter Value (in angle or minutes)</label>
									<label class="xst_method_column2">14</label>
									<input class="xst_method_column3" type="number" name="isha_value"  style="width:60px;" value="<?php echo $method_custom[4]; ?>">
								</li>
								
							</ul>
							
							<button type="submit" class="button-primary"><?php _e( 'Save Settings', 'xllentech-salat-timings' ); ?></button>
							<?php wp_nonce_field( 'xlst-settings-options-action','xlst-settings-options-nonce' ); ?>
												
						</form>
					</div>
					
				</div>
			</div>
		</div>
		</div>
		<?php
	}
	
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Xllentech_Salat_Timings_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Xllentech_Salat_Timings_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xllentech-salat-timings-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Xllentech_Salat_Timings_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Xllentech_Salat_Timings_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xllentech-salat-timings-admin.js', array( 'jquery' ), $this->version, false );
		//$image_url = XC_PRO_URL . 'public/assets/small_loader11.gif';
		//$localizations = array( 'imageURL' => $image_url, 'ajax_url' => admin_url( 'admin-ajax.php' ) );
		$localizations = array( 'ajax_url' => admin_url( 'admin-ajax.php' ) );
		wp_localize_script( $this->plugin_name, 'xstVars', $localizations );
	}

}
