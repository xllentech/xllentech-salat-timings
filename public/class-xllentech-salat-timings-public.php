<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.xllentech.com
 * @since      1.0.0
 *
 * @package    Xllentech_Salat_Timings
 * @subpackage Xllentech_Salat_Timings/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Xllentech_Salat_Timings
 * @subpackage Xllentech_Salat_Timings/public
 * @author     Your Name <email@example.com>
 */
class Xllentech_Salat_Timings_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * The function to get ip and get location data for Xllentech Salat Timings
	 *
	 * @package     Xllentech Salat Timings
	 * @subpackage  Functions
	 * @copyright   Copyright (c) 2018, xllentech
	 * @since       1.1.0
	 */
	function xst_get_salat_location( $json ) {
		
		// Function to get the client ip address
		$ipaddress = '';
		
		//$ipaddress = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);

		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = NULL;
		
		if ( filter_var( $ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE )	!== false ) {
			
			$ip = $ipaddress;
			echo "<p align='center'>Successfully received location data and entered in respective boxes. <strong>NOW, CLICK Make Timetable.</strong></p>";
			
		} else {
			$ip = NULL;
			echo "<p align='center'><strong>Sorry..Couldn't receive location data.</strong></br>To Get Salat Timings for your location, Please Type Latitude, Longitude and Timezone in below boxes, Then Click Make Timetable.</br>(Ignore Country, Region and City fields, They are not required)</p>";
			return NULL;
		}
			$xllentech_salat_timings_options = get_option("xllentech_salat_timings_options");
			
			//$url = 'http://freegeoip.net/json/' . $ip;
			$url = 'http://api.ipstack.com/' . $ip .'?access_key='.$xllentech_salat_timings_options['ipstack_access_key'];  
			$response = wp_remote_get( $url );
						
			$body = wp_remote_retrieve_body( $response );
			
			try {

				// Note that we decode the body's response since it's the actual JSON feed
				$json = json_decode( $body, true );
		 
			} catch ( Exception $ex ) {
				$json = null;
			} // end try/catch

			//print_r( $json );
			//print_r( $json['longitude'] );
			//print_r( $json['time_zone'] );
			
			return $json;
	}
	
	/**
	 * The shortcode function to display monthly salat timings table
	 *
	 * @package     Xllentech Salat Timings
	 * @subpackage  Framework
	 * @copyright   Copyright (c) 2018, xllentech
	 * @since       1.1.0
	 */
	function xllentech_display_salat_monthly( $atts ) {
		
		if( isset( $atts['calc'] ) ) {

			$calc = "manual";
			$ajax_nonce = wp_create_nonce( 'xst-manual-nonce' );
			
		} else {
			$calc = "auto";
		}
		?>
		<div  class='xllentech_salat_timings'>
		<h2> Prayer Timetable </h2>
		<?php
		
		$xllentech_salat_timings_options = get_option("xllentech_salat_timings_options");
		
			if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST["xst_get_salat_location"] ) ) {
				
				$calc = "manual";
				$json = apply_filters( 'xst_get_salat_location', $json );
				//print_r( $json );
				if( $json != NULL ) {
					
					$country = $json["country_name"];
					$region = $json["region_name"];
					$city = $json["city"];
					$zip_code = $json["zip_code"];
					$latitude = $json["latitude"];
					$longitude = $json["longitude"];
					
					$time = time();
					$url = "https://maps.googleapis.com/maps/api/timezone/json?location=$latitude,$longitude&timestamp=$time&key=".$xllentech_salat_timings_options['timezone_api_key'];
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$responseJson = curl_exec($ch);
					curl_close($ch);
					 
					$response = json_decode($responseJson);
					//print_r($response);
					//var_dump($response);
					$timeZone = $response->timeZoneId;

					//echo $timeZone = $json['time_zone'];
					
					$currentdate = new DateTime( 'NOW', new DateTimeZone($timeZone) );
					
					$offset = $currentdate->getOffset()/3600;
									
					$currentmonth = date_format($currentdate,'n');
					$currentmonth_name = date_format($currentdate,'M');
					$currentyear = date_format($currentdate,'Y');
					
					$method = 0;
					
					list( $method, $year, $month, $latitude, $longitude, $offset, $country, $region, $city, $zip_code ) = array( $method, $currentyear, $currentmonth, $latitude, $longitude , $offset, $country, $region, $city, $zip_code );
					
				} else {

					$timeZone = $xllentech_salat_timings_options['timezone'];
			
					$currentdate=new DateTime( 'NOW', new DateTimeZone($timeZone) );
					
					$offset = $currentdate->getOffset()/3600;
					$currentmonth = date_format($currentdate,'n');
					$currentmonth_name = date_format($currentdate,'M');
					$currentyear = date_format($currentdate,'Y');
				
					list( $method, $year, $month, $latitude, $longitude, $offset, $country, $region, $city, $zip_code ) = array( 0, $currentyear, $currentmonth, $xllentech_salat_timings_options['latitude'], $xllentech_salat_timings_options['longitude'] , $offset, 'Canada', 'Alberta', 'Calgary', 'T2L' );
					
				}
					
			} elseif( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST["xst_latitude"] ) ) {
				
				$country = $_POST["xst_country"];
				$region = $_POST["xst_region"];
				$city = $_POST["xst_city"];
				$zip_code = $_POST["xst_zip_code"];
				$latitude = $_POST["xst_latitude"];
				$longitude = $_POST["xst_longitude"];
				$timeZone = $_POST["xst_timeZone"];
				$year = $_POST["xst_year"];
				$month = $_POST["xst_month"];
				
				$date = date_create( "1-" . $month . "-". $year );
				
				$currentdate = new DateTime( "1-" . $month . "-". $year , new DateTimeZone( $timeZone ) );
				
				$offset = $currentdate->getOffset()/3600;
				
				$currentmonth_name = date_format($currentdate,'M');
				
			}	elseif( $_SERVER['REQUEST_METHOD'] == 'POST' ) { // if get salat location not set but POST is set
					
					$country = $_POST["xst_country"];
					$region = $_POST["xst_region"];
					$city = $_POST["xst_city"];
					$zip_code = $_POST["xst_zip_code"];
					$latitude = $_POST["xst_latitude"];
					$longitude = $_POST["xst_longitude"];
					$timeZone = $_POST["xst_timeZone"];
					$year = $_POST["xst_year"];
					$month = $_POST["xst_month"];
					
					$date = date_create( "1-" . $month . "-". $year );
					
					$currentdate = new DateTime( "1-" . $month . "-". $year , new DateTimeZone( $timeZone ) );
					
					$offset = $currentdate->getOffset()/3600;
					
					$currentmonth_name = date_format($currentdate,'M');

			}	else {
				
				
				$countryname = $xllentech_salat_timings_options['countryname'];
				$regionname = $xllentech_salat_timings_options['regionname'];
				$cityname = $xllentech_salat_timings_options['cityname'];
				$zip_code = 'T2L';
				
				$timeZone = $xllentech_salat_timings_options['timezone'];
				
				$currentdate = new DateTime( 'NOW', new DateTimeZone($timeZone) );
				
				$offset = $currentdate->getOffset()/3600;
				$currentmonth=date_format($currentdate,'n');
				$currentmonth_name = date_format($currentdate,'M');
				$currentyear=date_format($currentdate,'Y');
			
				list( $method, $year, $month, $latitude, $longitude, $offset, $country, $region, $city, $zip_code ) = array( 0, $currentyear, $currentmonth, $xllentech_salat_timings_options['latitude'], $xllentech_salat_timings_options['longitude'] , $offset, $countryname, $regionname, $cityname, 'T2L' );
			}
			
		$calc = strtolower( $calc );
		
		if( $calc == "manual" ) {
			
			if( ! isset( $_POST["xst_get_salat_location"] ) ) {
		?>
				<p align='center'><strong>If You are out of Calgary-Canada, Click Get Location First, try Method 1, Allow the webpage to get location.</br>
				If location data is not received, try Method 2.</br>It's by the best Estimate but not guaranteed to be accurate, So, Please Verify Country, Region and City. </br>If The Retrieved location is not correct, Type Latitude, Longitude and Timezone manually.</strong></p>
				<div id="location_row" style="width:100%;display:block;">
					<div class="col-2" style="width:50%;float:left;">
						<p align="lef">Method 1: Coordinates based</p>
				<!--<form name="xst-get-location" id="xst-get-location" method="post" action="">
					<ul class="xst_data_form">
						<li class="xst_data_submit">
							<input type="hidden" name="xst_get_salat_location" value="Y" />-->
						<p align="center" id="location_form" style="background-color: #2c87f0;color:#fff;"><button onclick="getLocation()">Get Location</button></p>
						<!--</li>
					</ul>
				</form>-->
					</div>
					<div class="col-2" style="width:50%;float:left;">
						<p align="left">Method 2: IP based</p>
						<form name="xst-get-location" id="xst-get-location" method="post" action="">
							<input type="hidden" name="xst_get_salat_location" value="Y" >
							<input align="center" type="submit" value="Get Location" style="background-color: #74cff6;">
						</form>
					</div>
				</div>
	<?php 	} 	?>
			<form name="xst-manual-data" id="xst-manual-data" method="post" action="" style="clear:both;">
				<ul class="xst_data_form">
					<li>
						<label for="xst_latitude">Latitude: </label>
						<input type="number" name="xst_latitude" step="any" id="xst_latitude" value="<?php if($latitude) echo $latitude; ?>"> 
					</li>
					
					<li>
						<label for="xst_longitude">Longitude: </label>
						<input type="number" name="xst_longitude" step="any" id="xst_longitude" value="<?php if($longitude) echo $longitude; ?>">
					</li>
					<li>
						<label for="xst_latitude">Country: </label>
						<input type="text" name="xst_country" id="xst_country" size="5" value="<?php if($country) echo $country; ?>">
					</li>
					
					<li>
						<label for="xst_longitude">Region: </label>
						<input type="text" name="xst_region" id="xst_region" size="10" value="<?php if($region) echo $region; ?>">
					</li>
					<li>
						<label for="xst_city">City: </label>
						<input type="text" id="xst_city" name="xst_city" size="10" value="<?php if($city) echo $city; ?>">
					</li>
					
					<li>
						<label for="xst_timeZone">Time Zone: </label>
						<input type="text" name="xst_timeZone" id="xst_timeZone" value="<?php if($timeZone) echo $timeZone; ?>">
						<!-- <select id="xst_timeZone" name="xst_timeZone" style="max-width:60%;">
						<?php
						
						$zones = timezone_identifiers_list();
						foreach ($zones as $zone) {

						?>
							<option value="<?php _e( $zone ); ?>" <?php if( $timeZone == $zone ){?> selected="selected" <?php } ?>><?php _e( $zone ); ?></option>
						<?php } ?>
						</select> -->
					</li>
					
					<li>
						<label for="xst_year">Year: </label>
						<input type="number" value="<?php echo $year ?>" name="xst_year" size="2">
					</li>
					<li>
						<label for="xst_month">Month: </label>
						<select id="xst_month" name="xst_month">
							<option value="1"<?php if( $month == 1 ) _e( ' selected="selected"'); ?>>Jan</option>
							<option value="2"<?php if( $month == 2 ) _e( ' selected="selected"'); ?>>Feb</option>
							<option value="3"<?php if( $month == 3 ) _e( ' selected="selected"'); ?>>Mar</option>
							<option value="4"<?php if( $month == 4 ) _e( ' selected="selected"'); ?>>Apr</option>
							<option value="5"<?php if( $month == 5 ) _e( ' selected="selected"'); ?>>May</option>
							<option value="6"<?php if( $month == 6 ) _e( ' selected="selected"'); ?>>Jun</option>
							<option value="7"<?php if( $month == 7 ) _e( ' selected="selected"'); ?>>Jul</option>
							<option value="8"<?php if( $month == 8 ) _e( ' selected="selected"'); ?>>Aug</option>
							<option value="9"<?php if( $month == 9 ) _e( ' selected="selected"'); ?>>Sep</option>
							<option value="10"<?php if( $month == 10 ) _e( ' selected="selected"'); ?>>Oct</option>
							<option value="11"<?php if( $month == 11 ) _e( ' selected="selected"'); ?>>Nov</option>
							<option value="12"<?php if( $month == 12 ) _e( ' selected="selected"'); ?>>Dec</option>
						</select>
					</li>
					<li class="xst_data_submit">
						<input type="hidden" name="action" value="xst_salat_submit_reload" />
						<input align="center" type="submit" value="Make Timetable">
					</li>
				</ul>
			</form>
			<script async defer src="//cdnjs.cloudflare.com/ajax/libs/jstimezonedetect/1.0.4/jstz.min.js"></script>
			<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAvOW_HTVZgGavh4iiSyoDrRoHaXG_gUfE&callback=initMap"></script>
			
			<script>
			
			var geocoder;
			var map;
			var marker;
			var marker2;
			var x = document.getElementById("xst_latitude");
			var y = document.getElementById("xst_longitude");
			
			function getLocation() {
				
				if (navigator.geolocation) {
					initialize();
					var tz = jstz.determine(); // Determines the time zone of the browser client
					var timezone = tz.name(); //'Asia/Kolhata' for Indian Time.
					
					document.getElementById("xst_timeZone").value = timezone;
					navigator.geolocation.getCurrentPosition(successFunction, errorFunction);
					
										
				} else {
					x.value = "No Geolocation received.";
					y.value = "Location not received.";
				}
				
			}
			// Get the latitude and the longitude;
			function successFunction(position) {
			  var lat = position.coords.latitude;
			  var lng = position.coords.longitude;
			 	x.value = lat;
				y.value = lng;
				codeLatLng(lat, lng);
				document.getElementById("location_row").innerHTML = "<strong>If you see the location fields correct, please click Make Timetable Now.</strong>";
			}

			function errorFunction() {
			  alert("Geocoder failed");
			}
			function initialize() {
			  geocoder = new google.maps.Geocoder();
			}
			function codeLatLng(lat, lng) {
				
			  var latlng = new google.maps.LatLng(lat, lng);
			  geocoder.geocode({latLng: latlng}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					
				  if (results) {
					  
					//console.log(results);
					var arrAddress = results[0].address_components;
						for (ac = 0; ac < arrAddress.length; ac++) {
							//if (arrAddress[ac].types[0] == "street_number") { document.getElementById("tbUnit").value = arrAddress[ac].long_name }
							//if (arrAddress[ac].types[0] == "route") { document.getElementById("tbStreet").value = arrAddress[ac].short_name }
							if (arrAddress[ac].types[0] == "locality") { document.getElementById("xst_city").value = arrAddress[ac].long_name }
							if (arrAddress[ac].types[0] == "administrative_area_level_1") { document.getElementById("xst_region").value = arrAddress[ac].long_name }
							if (arrAddress[ac].types[0] == "country") { document.getElementById("xst_country").value = arrAddress[ac].long_name }
							//if (arrAddress[ac].types[0] == "postal_code") { document.getElementById("tbZip").value = arrAddress[ac].long_name }
						}
					 					 
				  } else {
					document.getElementById("xst_city").value = "No results found.";
				  }
				} else {
				  document.getElementById("xst_city").value = "Geocoder failed due to: " + status;
				}
			  });
			}
			</script>
		<?php
		/**
		<select id="time" name="time" size="1" onChange="loadXMLDoc();">
			<option value="0">24 hours</option>
			<option value="1">12 hours</option>
		</select>	
		*/
		}
			
		$prev_month = clone $currentdate;
		$prev_month->modify( '-1 month' );
		
		$prevmonth = date_format($prev_month,'n');
		$prevyear = date_format($prev_month,'Y');
		
		$next_month = clone $currentdate;
		$next_month->modify( '+1 month' );
		
		$nextmonth = date_format($next_month,'n');
		$nextyear = date_format($next_month,'Y');
		
		$colspan = 6;
		if( $xllentech_salat_timings_options['xst_display_imsak'] == 1 )
			$colspan++;
		if( $xllentech_salat_timings_options['xst_display_asr'] == 1 )
			$colspan++;
		if( $xllentech_salat_timings_options['xst_display_isha'] == 1 )
			$colspan++;
		
		?>
				<div class="xst_instructions">Print on Chrome Browser, use More settings, set Scale/Zoom around 70% for full page printing.</div><div class="xst_print_button"><button onclick="window.print();">PRINT</button></div>
				<table class="xst_monthly_table_Default" id="printarea">
				<thead>
					<tr class="xllentech-salat-header">
						<th colspan=<?php _e( $colspan ); ?>>
						<?php _e( 'Location:  '. $city .',  '. $region ); ?>
						</th>
					</tr>
					<tr class="xllentech-salat-nav">
						<th>
							<form method="post" id="xst-prev-month" name="move-months" action="">
								<input type="hidden" name="xst_year" value="<?php _e( $prevyear ); ?>"/>
								<input type="hidden" name="xst_month" value="<?php _e( $prevmonth ); ?>"/>
								<input type="hidden" name="action" value="xst_salat_submit_reload" />
								<input align="center" type="submit" value="<<">
							</form>
						</th>
						<th colspan=<?php _e( $colspan-2 ); ?>><?php _e( $currentmonth_name .'  '. $currentyear ); ?> </th>
						<th>
							<form method="post" id="xst-next-months" name="move-months" action="">
								<input type="hidden" name="xst_year" value="<?php _e( $nextyear ); ?>"/>
								<input type="hidden" name="xst_month" value="<?php _e( $nextmonth ); ?>"/>
								<input type="hidden" name="action" value="xst_salat_submit_reload" />
								<input align="center" type="submit" value=">>">
							</form>
						</th>
					</tr>
					
					<tr class="xllentech-salat-header">
					 	<th>Day</th>
					<?php if( $xllentech_salat_timings_options['xst_display_imsak'] == 1 ) { ?>
						<th>Imsak</th>
					<?php } ?>
						<th>Fajr</th>
						<th width="10%">Sunrise</th>
						
						<th><?php _e( $xllentech_salat_timings_options['xst_display_zuhr'] ); ?></th>
						
					<?php if( $xllentech_salat_timings_options['xst_display_asr'] == 1 ) { ?>
						<th>Asr</th>
					<?php } ?>
						<th>Sunset</th>
						<th><?php _e( $xllentech_salat_timings_options['xst_display_maghrib'] ); ?></th>
						
					<?php if( $xllentech_salat_timings_options['xst_display_isha'] == 1 ) {?>
						<th>Isha</th>
					<?php } ?>
					</tr>
				</thead>
				<tbody>
			 <?php
				
			//	echo $xllentech_salat_timings_options['Custom'];
			//$dateTimeZone = new DateTimeZone( $timeZone );
				$today_date = new DateTime( 'NOW', new DateTimeZone($timeZone) ); 
				$present_day = date_format($today_date,'d');
				$present_month = date_format($today_date,'n');
				$present_year = date_format($today_date,'Y');
				
				$prayTime = new PrayTimeClass( $xllentech_salat_timings_options['method'], $xllentech_salat_timings_options['Custom'] );
				$prayTime->setAsrMethod( 4/7 ); //4/7 shadow
				$prayTime->setTimeFormat( 0 ); //value 0 = 24 hours , 1 = 12 hours
				$prayTime->setMoonsighting(2); //Ahmar
				$asr="(J)";
				$ish="(J)";
		
				$date = strtotime($year. '-'.$month.'-1');
				
				if($month==12)
					$endDate = strtotime(($year+ 1). '-1-1');
				else
					$endDate = strtotime( $year. '-'. ($month+1) .'-1' );

				$currentdate = new DateTime( $year. '-' .$month. '-1' ." 10:10" , new DateTimeZone( $timeZone ) );
				
					while ($date < $endDate)
					{
					
						$offset = $currentdate->getOffset()/3600;
						
			//			$offset =  date('Z') / 3600;
						$times = $prayTime->getPrayerTimes($date, $latitude, $longitude, $offset);
						
						$day = date('d', $date);
						$month = date('n', $date);
						$year = date('Y', $date);
						if( $day == $present_day && $month == $present_month && $year == $present_year )
							echo '<tr class="xst_present_day">';
						else
							echo '<tr>';
						
							echo '<td width="10%">'.$day . '</td>';
							
							if( $xllentech_salat_timings_options['xst_display_imsak'] == 1 ) {
								$imsak = date( 'H:i', strtotime( date( $times[0] ) ) - ( $xllentech_salat_timings_options['xst_imsak_diff'] * 60 ) );
								echo '<td width="8%">'.$imsak . '</td>';
							}
							
							echo '<td width="12%">'.$times[0] . '</td>';
							echo '<td width="14%">'.$times[1] . '</td>';
							echo '<td width="16%">'.$times[2] . '</td>';
							
							if( $xllentech_salat_timings_options['xst_display_asr'] == 1 )
								echo '<td width="12%">'. $times[3] . '</td>';
							
							echo '<td width="12%">'.$times[4] . '</td>';
							echo '<td width="14%">'.$times[5] . '</td>';
							
							if( $xllentech_salat_timings_options['xst_display_isha'] == 1 )
								echo '<td width="10%">'.$times[6] . '</td>';
							
					//	print $day. "\t". implode("\t", $times). "\n";
						$date += 24* 60* 60;  // next day
						
						$currentdate = $currentdate->modify( '+1 day' );
						
						echo '</tr>';
					}
				?> 
					<tr>
						<td colspan=<?php echo $colspan; ?>><strong>Note: These timings are a general guideline. You are free to follow more precautions for your personal satisfaction.</strong></td>
					</tr>
				</tbody>
				</table>
		</div>
	<?php
	} //end function 
	
	/**
	 * The ajax to refresh monthly salat timings table for new location data OR just month and year
	 *
	 * @package     Xllentech Salat Timings
	 * @subpackage  Framework
	 * @copyright   Copyright (c) 2018, xllentech
	 * @since       1.1.0
	 */
	function xst_salat_submit_reload() {
		
			$xllentech_salat_timings_options = get_option("xllentech_salat_timings_options");
			
			?>
			<div  class='xllentech_salat_timings'>
			<h2> Prayer Timetable </h2>
			<?php
			
			if( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST["xst_get_salat_location"] ) ) {
				
				$json = xst_get_salat_location();
				
				//print_r( $json );
				//print_r( $json['longitude'] );
				//print_r( $json['time_zone'] );
				if( $json != NULL ) {
					$country = $json["country_name"];
					$region = $json["region_name"];
					$city = $json["city"];
					$zip_code = $json["zip_code"];
					$latitude = $json["latitude"];
					$longitude = $json["longitude"];
					$timeZone = $json["time_zone"];
					
					$currentdate=new DateTime( 'NOW', new DateTimeZone($timeZone) );
				
					$offset = $currentdate->getOffset()/3600;
					$month = date_format($currentdate,'n');
					$year = date_format($currentdate,'Y');
					
					$method = 0;
				} else {
					
					$countryname = $xllentech_salat_timings_options['countryname'];
					$regionname = $xllentech_salat_timings_options['regionname'];
					$cityname = $xllentech_salat_timings_options['cityname'];
					$zip_code = 'T2L';
					
					$timeZone = $xllentech_salat_timings_options['timezone'];
					
					$currentdate=new DateTime( 'NOW', new DateTimeZone($timeZone) );
					
					$offset = $currentdate->getOffset()/3600;
					$currentmonth = date_format($currentdate,'n');
					$currentyear = date_format($currentdate,'Y');
				
					list( $method, $year, $month, $latitude, $longitude, $offset, $country, $region, $city, $zip_code ) = array( 0, $currentyear, $currentmonth, $xllentech_salat_timings_options['latitude'], $xllentech_salat_timings_options['longitude'] , $offset, $countryname, $regionname, $cityname, $zip_code );
				}
				
			} elseif( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST["xst_latitude"] ) ) { // if get salat location not set but POST is set
				
				$country = $_POST["xst_country"];
				$region = $_POST["xst_region"];
				$city = $_POST["xst_city"];
				$zip_code = $_POST["xst_zip_code"];
				$latitude = $_POST["xst_latitude"];
				$longitude = $_POST["xst_longitude"];
				$timeZone = $_POST["xst_timeZone"];
				$currentyear = $_POST["xst_year"];
				$currentmonth = $_POST["xst_month"];
				$method = $_POST["xst_method"];
				
				//$date = date_create( "1-" . $currentmonth . "-". $currentyear );
				
				$currentdate = new DateTime( "1-" . $currentmonth . "-". $currentyear , new DateTimeZone( $timeZone ) );
				
				$offset = $currentdate->getOffset()/3600;
				
				list( $method, $year, $month, $latitude, $longitude, $offset, $country, $region, $city, $zip_code ) = array( 0, $currentyear, $currentmonth, $latitude, $longitude , $offset, $country, $region, $city, $zip_code );
				
			} elseif( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST["xst_year"] ) ) {
				
				$countryname = $xllentech_salat_timings_options['countryname'];
				$regionname = $xllentech_salat_timings_options['regionname'];
				$cityname = $xllentech_salat_timings_options['cityname'];
				$zip_code = 'T2L';
				
				$timeZone = $xllentech_salat_timings_options['timezone'];
				
				$currentyear = $_POST["xst_year"];
				$currentmonth = $_POST["xst_month"];
		
				$currentdate = new DateTime( "1-" . $currentmonth . "-". $currentyear , new DateTimeZone( $timeZone ) );
				
				$offset = $currentdate->getOffset()/3600;
						
				list( $method, $year, $month, $latitude, $longitude, $offset, $country, $region, $city, $zip_code ) = array( 0, $currentyear, $currentmonth, $xllentech_salat_timings_options['latitude'], $xllentech_salat_timings_options['longitude'] , $offset, $countryname, $regionname, $cityname, 'T2L' );
					
			} else { //if POST is not set
				
				$countryname = $xllentech_salat_timings_options['countryname'];
				$regionname = $xllentech_salat_timings_options['regionname'];
				$cityname = $xllentech_salat_timings_options['cityname'];
				$zip_code = 'T2L';
					
				$timeZone=$xllentech_salat_timings_options['timezone'];
				
				$currentdate=new DateTime( 'NOW', new DateTimeZone($timeZone) );
				
				$offset = $currentdate->getOffset()/3600;
				$currentmonth=date_format($currentdate,'n');
				$currentyear=date_format($currentdate,'Y');
			
				list( $method, $year, $month, $latitude, $longitude, $offset, $country, $region, $city, $zip_code ) = array( 0, $currentyear, $currentmonth, $xllentech_salat_timings_options['latitude'], $xllentech_salat_timings_options['longitude'] , $offset, $countryname, $regionname, $cityname, 'T2L' );
			}
			
			$currentmonth_name = date_format($currentdate,'M');
			
			$prev_month = clone $currentdate;
			$prev_month->modify( '-1 month' );
			
			$prevmonth = date_format($prev_month,'n');
			$prevyear = date_format($prev_month,'Y');
			
			$next_month = clone $currentdate;
			$next_month->modify( '+1 month' );
			
			$nextmonth = date_format($next_month,'n');
			$nextyear = date_format($next_month,'Y');
			//$ajax_nonce = wp_create_nonce( 'xst-manual-nonce' );
			
			$colspan = 6;
			if( $xllentech_salat_timings_options['xst_display_imsak'] == 1 )
				$colspan++;
			if( $xllentech_salat_timings_options['xst_display_asr'] == 1 )
				$colspan++;
			if( $xllentech_salat_timings_options['xst_display_isha'] == 1 )
				$colspan++;
			
			if( isset( $_POST["xst_latitude"] ) ) {
	?>
					<form name="xst-manual-data" id="xst-manual-data" method="post" action="">
						<ul class="xst_data_form">
							<li>
								<label for="xst_latitude">Latitude: </label>
								<input type="number" value="<?php echo $latitude ?>" name="xst_latitude" size="5">
							</li>
							
							<li>
								<label for="xst_longitude">Longitude: </label>
								<input type="number" value="<?php echo $longitude ?>" name="xst_longitude" size="6">
							</li>
							<li>
								<label for="xst_latitude">Country: </label>
								<input type="text" value="<?php echo $country ?>" name="xst_country" size="5">
							</li>
							
							<li>
								<label for="xst_longitude">Region: </label>
								<input type="text" value="<?php echo $region ?>" name="xst_region" size="6">
							</li>
							<li>
								<label for="xst_latitude">City: </label>
								<input type="text" value="<?php echo $city ?>" name="xst_city" size="5">
							</li>
							
							<li>
								<label for="xst_timeZone">Time Zone: </label>
								<select id="xst_timeZone" name="xst_timeZone" style="max-width:60%;">
								<?php
								
								$zones = timezone_identifiers_list();
								foreach ($zones as $zone) {

								?>
									<option value="<?php _e( $zone ); ?>" <?php if($timeZone==$zone){?> selected="selected" <?php } ?>><?php _e( $zone ); ?></option>
								<?php } ?>
								</select> 
							</li>
							
							<li>
								<label for="xst_year">Year: </label>
								<input type="number" value="<?php echo $year ?>" name="xst_year" size="2">
							</li>
							<li>
								<label for="xst_month">Month: </label>
								<select id="xst_month" name="xst_month">
									<option value="1"<?php if( $month==1 ) echo ' selected="selected"'; ?>>Jan</option>
									<option value="2"<?php if( $month==2 ) echo ' selected="selected"'; ?>>Feb</option>
									<option value="3"<?php if( $month==3 ) echo ' selected="selected"'; ?>>Mar</option>
									<option value="4"<?php if( $month==4 ) echo ' selected="selected"'; ?>>Apr</option>
									<option value="5"<?php if( $month==5 ) echo ' selected="selected"'; ?>>May</option>
									<option value="6"<?php if( $month==6 ) echo ' selected="selected"'; ?>>Jun</option>
									<option value="7"<?php if( $month==7 ) echo ' selected="selected"'; ?>>Jul</option>
									<option value="8"<?php if( $month==8 ) echo ' selected="selected"'; ?>>Aug</option>
									<option value="9"<?php if( $month==9 ) echo ' selected="selected"'; ?>>Sep</option>
									<option value="10"<?php if( $month==10 ) echo ' selected="selected"'; ?>>Oct</option>
									<option value="11"<?php if( $month==11 ) echo ' selected="selected"'; ?>>Nov</option>
									<option value="12"<?php if( $month==12 ) echo ' selected="selected"'; ?>>Dec</option>
								</select>
							</li>
								
						
							<li class="xst_data_submit">
								<input type="hidden" name="action" value="xst_salat_submit_reload" />
								<input type="hidden" name="security" value="<?php echo $ajax_nonce; ?>" /><input align="center" type="submit" value="Make Timetable">
							</li>
						</ul>
					</form>
			<?php } ?>
		<div class="xst_instructions">Print on Chrome Browser, use More settings, set Scale/Zoom around 70% for full page printing.</div><div class="xst_print_button"><button onclick="window.print();">PRINT</button></div>
		<table class='xst_monthly_table_Default' id="printarea">
		<thead>
			<tr class="xllentech-salat-header">
				<th colspan=<?php echo $colspan; ?>>
				<?php _e( 'Location:  '. $city .',  '. $region ); ?>
				</th>
			</tr>
			<tr class="xllentech-salat-nav">
				<th>
				<?php if( ! isset( $_POST["xst_latitude"] ) ) { ?>
					<form method="post" id="xst-prev-month" name="xst-prev-month" action="">
						<input type="hidden" name="xst_year" value="<?php _e( $prevyear ); ?>"/>
						<input type="hidden" name="xst_month" value="<?php _e( $prevmonth ); ?>"/>
						<input type="hidden" name="action" value="xst_salat_submit_reload" />
						<input align="center" type="submit" value="<<">
					</form>
				<?php } ?>
				</th>
				<th colspan=<?php echo $colspan-2; ?>><?php _e( $currentmonth_name .'  '. $currentyear ); ?> </th>
				<th>
				<?php if( ! isset( $_POST["xst_latitude"] ) ) { ?>
					<form method="post" id="xst-next-months" name="xst-next-months" action="">
						<input type="hidden" name="xst_year" value="<?php _e( $nextyear ); ?>"/>
						<input type="hidden" name="xst_month" value="<?php _e( $nextmonth ); ?>"/>
						<input type="hidden" name="action" value="xst_salat_submit_reload" />
						<input align="center" type="submit" value=">>">
					</form>
				<?php } ?>
				</th>
			</tr>
			<tr class="xllentech-salat-header">
				<th>Day</th>
				
				<?php if( $xllentech_salat_timings_options['xst_display_imsak'] == 1 ) { ?>
					<th>Imsak</th>
				<?php } ?>
				
				<th>Fajr</th>
				<th>Sunrise</th>
				
				<th><?php echo $xllentech_salat_timings_options['xst_display_zuhr']; ?></th>
				
				<?php if( $xllentech_salat_timings_options['xst_display_asr'] == 1 )
				echo '<th>Asr</th>'; ?>
			
				<th>Sunset</th>
				<th><?php echo $xllentech_salat_timings_options['xst_display_maghrib']; ?></th>
				
				<?php if( $xllentech_salat_timings_options['xst_display_isha'] == 1 )
				echo '<th>Isha</th>'; ?>

			</tr>
		</thead>
			<tbody>
	 <?php

	//	date_default_timezone_set($timeZone);
	//$dateTimeZone = new DateTimeZone( $timeZone );
		$today_date = new DateTime( 'NOW', new DateTimeZone($timeZone) ); 
		$present_day = date_format($today_date,'d');
		$present_month = date_format($today_date,'n');
		$present_year = date_format($today_date,'Y');
		
		$prayTime = new PrayTimeClass( $xllentech_salat_timings_options['method'], $xllentech_salat_timings_options['Custom'] );
		$prayTime->setAsrMethod( 4/7 ); //4/7 shadow
		$prayTime->setTimeFormat( 0 ); //value 0 = 24 hours , 1 = 12 hours
		$prayTime->setMoonsighting(2); //Ahmar
		$asr="(J)";
		$ish="(J)";
		
		$date = strtotime($year. '-'.$month.'-1');
		
		if($month==12)
			$endDate = strtotime(($year+ 1). '-1-1');
		else
			$endDate = strtotime( $year. '-'. ($month+1) .'-1' );

		$currentdate = new DateTime( $year. '-' .$month. '-1' ." 10:10" , new DateTimeZone( $timeZone ) );
		
			while ($date < $endDate)
			{
			
				$offset = $currentdate->getOffset()/3600;
				
	//			$offset =  date('Z') / 3600;
				$times = $prayTime->getPrayerTimes($date, $latitude, $longitude, $offset);
				$day = date('d', $date);
				$month = date('n', $date);
				$year = date('Y', $date);
				if( $day == $present_day && $month == $present_month && $year == $present_year )
					echo '<tr class="xst_present_day">';
				else
					echo '<tr>';
		
				
					echo '<td>'.$day . '</td>';
					
					if( $xllentech_salat_timings_options['xst_display_imsak'] == 1 ) {
						$imsak = date( 'H:i', strtotime( date( $times[0] ) ) - ( $xllentech_salat_timings_options['xst_imsak_diff'] * 60 ) );
						echo '<td>'.$imsak . '</td>';
					}
					
					echo '<td>'.$times[0] . '</td>';
					echo '<td>'.$times[1] . '</td>';
					echo '<td>'.$times[2] . '</td>';
					
					if( $xllentech_salat_timings_options['xst_display_asr'] == 1 )
						echo '<td>'. $times[3] . '</td>';
					
					echo '<td>'.$times[4] . '</td>';
					echo '<td>'.$times[5] . '</td>';
					
					if( $xllentech_salat_timings_options['xst_display_isha'] == 1 )
						echo '<td>'.$times[6] . '</td>';
					
			//	print $day. "\t". implode("\t", $times). "\n";
				$date += 24* 60* 60;  // next day
				
				$currentdate = $currentdate->modify( '+1 day' );
				
				echo '</tr>';
			}
		?> 		
				<tr>
					<td colspan=<?php _e( $colspan ); ?>><strong>Note: These timings are a general guideline. You are free to follow more precautions for your personal satisfaction.</strong></td>
				</tr>
			</tbody>
		</table> 
		</div>
		<?php
		
		wp_die();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xllentech-salat-timings-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xllentech-salat-timings-public.js', array( 'jquery' ), $this->version, false );
		
		$image_url = plugin_dir_url( __FILE__ ) . 'js/loader-25px.gif';
		$localizations = array( 'imageURL' => $image_url, 'ajax_url' => admin_url( 'admin-ajax.php' ) );
		wp_localize_script( $this->plugin_name, 'xstVars', $localizations );

	}

}
