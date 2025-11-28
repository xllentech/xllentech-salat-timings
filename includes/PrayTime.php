<?php
/**
 * The calculating formula from praytime.org to calculate salat timings
 *
 * @package     Xllentech Salat Timings
 * @subpackage  Framework
 * @copyright   Copyright (c) 2018, xllentech
 * @since       1.1.0
 */
 
// Exit if accessed directly
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//--------------------- Copyright Block ----------------------
/*

praytime.php: Prayer Times Calculator (ver 1.2.2)
Copyright (C) 2007-2010 PrayTimes.org

Developer: Hamid Zarrabi-Zadeh
License: GNU General Public License, ver 3

TERMS OF USE:
    Permission is granted to use this code, with or
    without modification, in any website or application
    provided that credit is given to the original work
    with a link back to PrayTimes.org.

This program is distributed in the hope that it will
be useful, but WITHOUT ANY WARRANTY.

PLEASE DO NOT REMOVE THIS COPYRIGHT BLOCK.

*/


//--------------------- Help and Manual ----------------------
/*

User's Manual:
http://praytimes.org/manual

Calculating Formulas:
http://praytimes.org/calculation



//------------------------ User Interface -------------------------


    getPrayerTimes (timestamp, latitude, longitude, timeZone)
    getDatePrayerTimes (year, month, day, latitude, longitude, timeZone)

    setCalcMethod (methodID)
    setAsrMethod (methodID)

    setFajrAngle (angle)
    setMaghribAngle (angle)
    setIshaAngle (angle)
    setDhuhrMinutes (minutes)    // minutes after mid-day
    setMaghribMinutes (minutes)  // minutes after sunset
    setIshaMinutes (minutes)     // minutes after maghrib

    setHighLatsMethod (methodID) // adjust method for higher latitudes

    setTimeFormat (timeFormat)
    floatToTime24 (time)
    floatToTime12 (time)
    floatToTime12NS (time)


//------------------------- Sample Usage --------------------------


    $praytime->setCalcMethod($praytime->ISNA);
    $times = $praytime->getPrayerTimes(time(), 43, -80, -5);
    print('Sunrise = '. $times[1]);


*/


//--------------------- praytime Class -----------------------

if ( !class_exists( 'PrayTimeClass' ) ) {
	class PrayTimeClass
	{

    //------------------------ Constants --------------------------


    // Calculation Methods
    var $Jafari     = 0;    // Ithna Ashari
    var $Karachi    = 1;    // University of Islamic Sciences, Karachi
    var $ISNA       = 2;    // Islamic Society of North America (ISNA)
    var $MWL        = 3;    // Muslim World League (MWL)
    var $Makkah     = 4;    // Umm al-Qura, Makkah
    var $Egypt      = 5;    // Egyptian General Authority of Survey
    var $Custom     = 6;    // Custom Setting
    var $Tehran     = 7;    // Institute of Geophysics, University of Tehran

    // Juristic Methods
    var $Shafii     = 0;    // Shafii (standard)
    var $Hanafi     = 1;    // Hanafi

    // Adjusting Methods for Higher Latitudes
    var $None       = 0;    // No adjustment
    var $MidNight   = 1;    // middle of night
    var $OneSeventh = 2;    // 1/7th of night
    var $AngleBased = 3;    // angle/60th of night


    // Time Formats
    var $Time24     = 0;    // 24-hour format
    var $Time12     = 1;    // 12-hour format
    var $Time12NS   = 2;    // 12-hour format with no suffix
    var $Float      = 3;    // floating point number

    // Time Names
    var $timeNames = array(
        'Fajr',
        'Sunrise',
        'Dhuhr',
        'Asr',
        'Sunset',
        'Maghrib',
        'Isha'
    );

    var $InvalidTime = '-----';     // The string used for invalid times


    //---------------------- Global Variables --------------------


    var $calcMethod   = 0;        // caculation method
    var $asrJuristic  = 0;        // Juristic method for Asr
    var $dhuhrMinutes = 0;        // minutes after mid-day for Dhuhr
    var $adjustHighLats = 1;    // adjusting method for higher latitudes
    var $moonsighting = 1; //=0 no, 1= Shafaq General , 2=Shafaq Ahmar, 3=Shafaq Abyad 
    var $timeFormat   = 0;        // time format

    var $lat;        // latitude
    var $lng;        // longitude
    var $timeZone;   // time-zone
    var $JDate;      // Julian date
    var $cDay,$cMonth,$cYear;

    //--------------------- Technical Settings --------------------


    var $numIterations = 1;        // number of iterations needed to compute times


    //------------------- Calc Method Parameters --------------------


    var $methodParams = array();

    /*  var $methodParams[methodNum] = array(fa, ms, mv, is, iv);

            fa : fajr angle
            ms : maghrib selector (0 = angle; 1 = minutes after sunset)
            mv : maghrib parameter value (in angle or minutes)
            is : isha selector (0 = angle; 1 = minutes after maghrib)
            iv : isha parameter value (in angle or minutes)
    */


    //----------------------- Constructors -------------------------


    function praytime( $methodID = 0, $values = NULL )
    {
			
		if( $methodID == 6 && $values != NULL ):
			$this->methodParams[$this->Custom]    = explode(",", $values);
		else:
			$this->methodParams[$this->Custom]    = array(18, 1, 0, 0, 17);
		endif;
		
		//echo $this->methodParams[$this->Custom][0];
		
	// You can adjust the maghrib time for shia method by changing the third value
        $this->methodParams[$this->Jafari]    = array(16, 1, 14, 0, 14);
        $this->methodParams[$this->Karachi]   = array(18, 1, 0, 0, 18);
        $this->methodParams[$this->ISNA]      = array(15, 1, 0, 0, 15);
        $this->methodParams[$this->MWL]       = array(18, 1, 0, 0, 17);
        $this->methodParams[$this->Makkah]    = array(18.5, 1, 0, 1, 90);
        $this->methodParams[$this->Egypt]     = array(19.5, 1, 0, 0, 17.5);
        $this->methodParams[$this->Tehran]    = array(17.7, 0, 4.5, 0, 14);
        //$this->methodParams[$this->Custom]    = array(18, 1, 0, 0, 17);

        $this->setCalcMethod($methodID);
    }

    function __construct($methodID = 0 , $values = NULL )
    {
        $this->praytime( $methodID, $values );
    }



    //-------------------- Interface Functions --------------------


    // return prayer times for a given date
    function getDatePrayerTimes($year, $month, $day, $latitude, $longitude, $timeZone)
    {
        $this->lat = $latitude;
        $this->lng = $longitude;
        $this->cDay=$day;
		$this->cMonth=$month;
		$this->cYear=$year;
		$this->timeZone = $timeZone;
        $this->JDate = $this->julianDate($year, $month, $day)- $longitude/ (15* 24);
        return $this->computeDayTimes();
    }

    // return prayer times for a given timestamp
    function getPrayerTimes($timestamp, $latitude, $longitude, $timeZone)
    {
        $date = @getdate($timestamp);
        return $this->getDatePrayerTimes($date['year'], $date['mon'], $date['mday'],
                    $latitude, $longitude, $timeZone);
    }

    // set the calculation method
    function setCalcMethod($methodID)
    {
        $this->calcMethod = $methodID;
    }

    // set the juristic method for Asr
    function setAsrMethod($methodID)
    {
        if ($methodID < 0 || $methodID > 1)
            return;
        $this->asrJuristic = $methodID;
    }
    
    function setMoonsighting($moon)
    {
        if ($moon < 0 || $moon > 3)
            return;
        $this->moonsighting=$moon;
    }

    // set the angle for calculating Fajr
    function setFajrAngle($angle)
    {
        $this->setCustomParams(array($angle, null, null, null, null));
    }

    // set the angle for calculating Maghrib
    function setMaghribAngle($angle)
    {
        $this->setCustomParams(array(null, 0, $angle, null, null));
    }

    // set the angle for calculating Isha
    function setIshaAngle($angle)
    {
        $this->setCustomParams(array(null, null, null, 0, $angle));
    }

    // set the minutes after mid-day for calculating Dhuhr
    function setDhuhrMinutes($minutes)
    {
        $this->dhuhrMinutes = $minutes;
    }

    // set the minutes after Sunset for calculating Maghrib
    function setMaghribMinutes($minutes)
    {
        $this->setCustomParams(array(null, 1, $minutes, null, null));
    }

    // set the minutes after Maghrib for calculating Isha
    function setIshaMinutes($minutes)
    {
        $this->setCustomParams(array(null, null, null, 1, $minutes));
    }

    // set custom values for calculation parameters
    function setCustomParams($params)
    {
        for ($i=0; $i<5; $i++)
        {
            if ($params[$i] == null)
                $this->methodParams[$this->Custom][$i] = $this->methodParams[$this->calcMethod][$i];
            else
                $this->methodParams[$this->Custom][$i] = $params[$i];
        }
        $this->calcMethod = $this->Custom;
    }

    // set adjusting method for higher latitudes
    function setHighLatsMethod($methodID)
    {
        $this->adjustHighLats = $methodID;
    }

    // set the time format
    function setTimeFormat($timeFormat)
    {
        $this->timeFormat = $timeFormat;
    }

    // convert float hours to 24h format
    function floatToTime24($time)
    {
        if (is_nan($time))
            return $this->InvalidTime;
        $time = $this->fixhour($time+ 0.5/ 60);  // add 0.5 minutes to round
        $hours = floor($time);
        $minutes = floor(($time- $hours)* 60);
        return $this->twoDigitsFormat($hours). ':'. $this->twoDigitsFormat($minutes) ."   ";
    }

    // convert float hours to 12h format
    function floatToTime12($time, $noSuffix = false)
    {
        if (is_nan($time))
            return $this->InvalidTime;
        $time = $this->fixhour($time+ 0.5/ 60);  // add 0.5 minutes to round
        $hours = floor($time);
        $minutes = floor(($time- $hours)* 60);
        $suffix = $hours >= 12 ? ' pm' : ' am';
        $hours = ($hours+ 12- 1)% 12+ 1;
        return $this->twoDigitsFormat($hours). ':'. $this->twoDigitsFormat($minutes). ($noSuffix ? '' : $suffix);
    }

    // convert float hours to 12h format with no suffix
    function floatToTime12NS($time)
    {
        return $this->floatToTime12($time, true);
    }



    //---------------------- Calculation Functions -----------------------

    // References:
    // http://www.ummah.net/astronomy/saltime
    // http://aa.usno.navy.mil/faq/docs/SunApprox.html


    // compute declination angle of sun and equation of time
    function sunPosition($jd)
    {
        $D = $jd - 2451545.0;
        $g = $this->fixangle(357.529 + 0.98560028* $D);
        $q = $this->fixangle(280.459 + 0.98564736* $D);
        $L = $this->fixangle($q + 1.915* $this->dsin($g) + 0.020* $this->dsin(2*$g));

        $R = 1.00014 - 0.01671* $this->dcos($g) - 0.00014* $this->dcos(2*$g);
        $e = 23.439 - 0.00000036* $D;

        $d = $this->darcsin($this->dsin($e)* $this->dsin($L));
        $RA = $this->darctan2($this->dcos($e)* $this->dsin($L), $this->dcos($L))/ 15;
        $RA = $this->fixhour($RA);
        $EqT = $q/15 - $RA;

        return array($d, $EqT);
    }

    // compute equation of time
    function equationOfTime($jd)
    {
        $sp = $this->sunPosition($jd);
        return $sp[1];
    }

    // compute declination angle of sun
    function sunDeclination($jd)
    {
        $sp = $this->sunPosition($jd);
        return $sp[0];
    }

    // compute mid-day (Dhuhr, Zawal) time
    function computeMidDay($t)
    {
        $T = $this->equationOfTime($this->JDate+ $t);
        $Z = $this->fixhour(12- $T);
        return $Z;
    }

    // compute time for a given angle G
    function computeTime($G, $t)
    {
        $D = $this->sunDeclination($this->JDate+ $t);
        $Z = $this->computeMidDay($t);
        $V = 1/15* $this->darccos((-$this->dsin($G)- $this->dsin($D)* $this->dsin($this->lat))/
                ($this->dcos($D)* $this->dcos($this->lat)));
        return $Z+ ($G>90 ? -$V : $V);
    }

    // compute the time of Asr
    function computeAsr($step, $t)  // Shafii: step=1, Hanafi: step=2, step=4/7 
    {
        $D = $this->sunDeclination($this->JDate+ $t);
        $G = -$this->darccot($step+ $this->dtan(abs($this->lat- $D)));
        return $this->computeTime($G, $t);
    }


    //---------------------- Compute Prayer Times -----------------------

    function MoonsightingCalculateAForFajr( $LT ,$iday ,$imonth , $iyear)
    {
        $A = 75 + 28.65 / 55.0 * abs($LT);
        $B = 75 + 19.44 / 55.0 * abs($LT);
        $C = 75+ 32.74 / 55.0 * abs($LT); 
        $D = 75 + 48.1 / 55.0 * abs($LT);
        
        $DYY=$this->dayNumberFromDec21($iday,$imonth,$iyear,$LT);
        
        
        if ( $DYY < 91)
            $A = $A + ( $B - $A )/ 91.0 * $DYY; // RETURN: '91 DAYS SPAN 
        else if ( $DYY < 137) 
            $A = $B + ( $C - $B ) / 46.0 * ( $DYY - 91 ); // RETURN: '46 DAYS SPAN
        else if ( $DYY< 183 )
            $A = $C + ( $D - $C ) / 46.0 * ( $DYY - 137 ); //RETURN: '46 DAYS SPAN
        else if ( $DYY < 229 )
            $A = $D + ( $C - $D ) / 46.0 * ( $DYY - 183 ); // RETURN: '46 DAYS SPAN
        else if ( $DYY < 275 )
            $A = $C + ( $B - $C ) / 46.0 * ( $DYY - 229 ); //RETURN: '46 DAYS SPAN
       else if ( $DYY >= 275 )
            $A = $B + ( $A - $B ) / 91.0 * ( $DYY - 275 ); // RETURN: ' 91 DAYS SPAN
 
        return $A;
    }
    
    function MoonsightingCalculateAForIsha($LT,$iday,$imonth,$iyear)
    {
        if ($this->moonsighting == 1 )//general, Ahmed removed 3
        {
            $A = 75  + 25.6 / 55.0 * abs($LT);
            $B = 75  + 2.05 / 55.0 * abs($LT); 
            $C = 75  - 9.21 / 55.0 * abs($LT); 
            $D = 75  + 6.14 / 55.0 * abs($LT);
        }
        else if ($this->moonsighting == 2 ) //ahmar
        {
            $A = 62 + 17.4 / 55.0 * abs($LT); 
            $B = 62 - 7.16 / 55.0 * abs($LT); 
            $C = 62 + 5.12 / 55.0 * abs($LT); 
            $D = 62 + 19.44 / 55.0 * abs($LT);
        }
        else if ($this->moonsighting == 3) //abyad
        {
            $A = 75 + 25.6 / 55.0 * abs($LT);
            $B = 75 + 7.16 / 55.0 * abs($LT);
            $C = 75 + 36.84 / 55.0 * abs($LT);
            $D = 75 + 81.84/ 55.0 * abs($LT);
        }
        
        
        $DYY=$this->dayNumberFromDec21($iday,$imonth,$iyear,$LT);
        
        
        if ( $DYY < 91)
            $A = $A + ( $B - $A )/ 91.0 * $DYY; // RETURN: '91 DAYS SPAN 
        else if ( $DYY < 137) 
            $A = $B + ( $C - $B ) / 46.0 * ( $DYY - 91 ); // RETURN: '46 DAYS SPAN
        else if ( $DYY< 183 )
            $A = $C + ( $D - $C ) / 46.0 * ( $DYY - 137 ); //RETURN: '46 DAYS SPAN
        else if ( $DYY < 229 )
            $A = $D + ( $C - $D ) / 46.0 * ( $DYY - 183 ); // RETURN: '46 DAYS SPAN
        else if ( $DYY < 275 )
            $A = $C + ( $B - $C ) / 46.0 * ( $DYY - 229 ); //RETURN: '46 DAYS SPAN
       else if ( $DYY >= 275 )
            $A = $B + ( $A - $B ) / 91.0 * ( $DYY - 275 ); // RETURN: ' 91 DAYS SPAN 
        return $A;
    }
    
    function isTime($T1,$T2)
    {
        if (abs($T1 - $T2) > 5 ) {
            if ($T1 - $T2 > 0) 
                return 0;
            else
                return 1;
        }
        else
            return ($T1 > $T2);
    }
    
    function MoonsightingFajr($TSR,$TSS,$LT,$A,$FJR)
    {
        
        //   TSS=TSS+0.05;
        // Added by Ahmed to fix nigative time
        
        if ($FJR < 0.0)
        {
            $FJR = $FJR + 24.0; 
        } 
        /////
        
        $FJ7=$TSR-(24-$TSS+$TSR)/7.0;
        
        if ($FJ7 < 0) {
            $FJ7=24+$FJ7;
        }
        
         $FJRT=$TSR-$A/60.0;
        
        if ($FJRT < 0) {
            $FJRT= 24+$FJRT;
        }
        //if( isnan (FJR) || FJR<FJRT )
        if( is_nan($FJR) || $this->isTime($FJRT,$FJR))
            $FJR=$FJRT;
        // if (FJR < FJ7 && ABS(LT) >= 55 )
        if ($this->isTime($FJ7,$FJR) && abs($LT) >= 55)
            $FJR=$FJ7;
        return $FJR;
    }
    
     function MoonsightingIsha($TSR, $TSS,$LT,$A,$ISHA)
    {
        // TSS=TSS+0.05;
        // Added by Ahmed to fix nigative time
        
        if ($ISHA < 0)
            $ISHA = $ISHA + 24.0;
        ////
        
        
        // Calculate 1/7th of Night AFTER SUNSET, call it IS7
         $IS7 = $TSS + ( 24 - $TSS + $TSR ) / 7.0;
        
        if ($IS7 < 0) {
            $IS7= 24+IS7;
        }
        
        if ($TSR > $TSS) {
            $IS7=24-$IS7;
        }
        
         $ISHAT = $TSS + $A / 60.0;
        if ($ISHAT <0) {
            $ISHAT=24+$ISHAT;
        }
        
        // if( isnan(ISHA)  || ISHA > ISHAT )
        if( is_nan($ISHA)  || $this->isTime($ISHA,$ISHAT))
            $ISHA = $ISHAT;
        // if( ISHA > IS7 && ABS(LT)>= 55 )
        if( $this->isTime($ISHA,$IS7) && abs($LT)>= 55 )
            $ISHA=$IS7;
        
        return $ISHA;        
    }

    // compute prayer times at given julian date
    function computeTimes($times)
    {
        $t = $this->dayPortion($times);
        $idk=0; 
       
        if($this->moonsighting)
            $idk=18;
        else
            $idk=$this->methodParams[$this->calcMethod][0];

        $Fajr    = $this->computeTime(180- $idk, $t[0]);
        $Sunrise = $this->computeTime(180- 0.833, $t[1]);
        $Dhuhr   = $this->computeMidDay($t[2]);
	if($this->asrJuristic == 0)
	{
        	$Asr     = $this->computeAsr(1+ $this->asrJuristic, $t[3]);
		$Asr2	 = $this->computeAsr(2+ $this->asrJuristic, $t[3]);
	}
	else if($this->asrJuristic == 1)
	{
		$Asr     = $this->computeAsr(1+ $this->asrJuristic, $t[3]);
                $Asr2    = $this->computeAsr($this->asrJuristic, $t[3]);
	}
	else
	{
		$Asr     = $this->computeAsr($this->asrJuristic, $t[3]);
        	$Asr2	 =0;
	}
	$Sunset  = $this->computeTime(0.833, $t[4]);;
        $Maghrib = $this->computeTime($this->methodParams[$this->calcMethod][2], $t[5]);
        
        if($this->calcMethod !=4 && $this->moonsighting)
            $Isha=$this->computeTime(18, $t[6]);
        else
            $Isha=$this->computeTime($this->methodParams[$this->calcMethod][4], $t[6]);

        return array($Fajr, $Sunrise, $Dhuhr, $Asr, $Sunset, $Maghrib, $Isha, $Asr2);
    }


    // compute prayer times at given julian date
    function computeDayTimes()
    {
        $times = array(5, 6, 12, 13, 18, 18, 18,13); //default times

        for ($i=1; $i<=$this->numIterations; $i++)
            $times = $this->computeTimes($times);

        $times = $this->adjustTimes($times);
        
        
        if($this->moonsighting)
        {
            /// Moonsighting
             $A=$this->MoonsightingCalculateAForFajr($this->lat,$this->cDay,$this->cMonth,$this->cYear);  
             $fajr18=$times[0];
             $sunrise=$times[1];
             $sunset=$times[4];
             $isha18=$times[6];
             $maghrib=$times[5];
             $maghrib=$maghrib+0.05;
          
            $dhohr=$times[2];
            //$dhohr = $dhohr + 0.083; // this line adds 5 mins to the zuhr times
            
            
            $fajr= $this->MoonsightingFajr($sunrise,$sunset,$this->lat,$A,$fajr18);
            
            $A=$this->MoonsightingCalculateAForIsha($this->lat,$this->cDay,$this->cMonth,$this->cYear);
            
            $isha = $this->MoonsightingIsha($sunrise, $sunset,$this->lat,$A,$isha18);
            
            $times[0]=$fajr;
            $times[5]=$maghrib;    
            $times[2]=$dhohr; 
            
            if ($this->calcMethod!=4) 
                $times[6]=$isha;    
            
        }

        return $this->adjustTimesFormat($times);
    }


    // adjust times in a prayer time array
    function adjustTimes($times)
    {
        for ($i=0; $i<8; $i++)
            $times[$i] += $this->timeZone- $this->lng/ 15;
        $times[2] += $this->dhuhrMinutes/ 60; //Dhuhr
        if ($this->methodParams[$this->calcMethod][1] == 1) // Maghrib
            $times[5] = $times[4]+ $this->methodParams[$this->calcMethod][2]/ 60;
        if ($this->methodParams[$this->calcMethod][3] == 1) // Isha
            $times[6] = $times[5]+ $this->methodParams[$this->calcMethod][4]/ 60;

        if ($this->adjustHighLats != $this->None)
            $times = $this->adjustHighLatTimes($times);
        return $times;
    }


    // convert times array to given time format
    function adjustTimesFormat($times)
    {
        if ($this->timeFormat == $this->Float)
            return $times;
        for ($i=0; $i<8; $i++)
            if ($this->timeFormat == $this->Time12)
                $times[$i] = $this->floatToTime12($times[$i]);
            else if ($this->timeFormat == $this->Time12NS)
                $times[$i] = $this->floatToTime12($times[$i], true);
            else
                $times[$i] = $this->floatToTime24($times[$i]);
        return $times;
    }


    // adjust Fajr, Isha and Maghrib for locations in higher latitudes
    function adjustHighLatTimes($times)
    {
        $nightTime = $this->timeDiff($times[4], $times[1]); // sunset to sunrise

        // Adjust Fajr
        $FajrDiff = $this->nightPortion($this->methodParams[$this->calcMethod][0])* $nightTime;
        if (is_nan($times[0]) || $this->timeDiff($times[0], $times[1]) > $FajrDiff)
            $times[0] = $times[1]- $FajrDiff;

        // Adjust Isha
        $IshaAngle = ($this->methodParams[$this->calcMethod][3] == 0) ? $this->methodParams[$this->calcMethod][4] : 18;
        $IshaDiff = $this->nightPortion($IshaAngle)* $nightTime;
        if (is_nan($times[6]) || $this->timeDiff($times[4], $times[6]) > $IshaDiff)
            $times[6] = $times[4]+ $IshaDiff;

        // Adjust Maghrib
        $MaghribAngle = ($this->methodParams[$this->calcMethod][1] == 0) ? $this->methodParams[$this->calcMethod][2] : 4;
        $MaghribDiff = $this->nightPortion($MaghribAngle)* $nightTime;
        if (is_nan($times[5]) || $this->timeDiff($times[4], $times[5]) > $MaghribDiff)
            $times[5] = $times[4]+ $MaghribDiff;

        return $times;
    }


    // the night portion used for adjusting times in higher latitudes
    function nightPortion($angle)
    {
        if ($this->adjustHighLats == $this->AngleBased)
            return 1/60* $angle;
        if ($this->adjustHighLats == $this->MidNight)
            return 1/2;
        if ($this->adjustHighLats == $this->OneSeventh)
            return 1/7;
    }


    // convert hours to day portions
    function dayPortion($times)
    {
        for ($i=0; $i<7; $i++)
            $times[$i] /= 24;
        return $times;
    }



    //---------------------- Misc Functions -----------------------


    // compute the difference between two times
    function timeDiff($time1, $time2)
    {
        return $this->fixhour($time2- $time1);
    }


    // add a leading 0 if necessary
    function twoDigitsFormat($num)
    {
        return ($num <10) ? '0'. $num : "".$num;
    }

    function dayNumberFromDec21($iday,$imonth,$iyear,$LT)
    {   
        if( $LT >=0 )
        {
            
            if( $imonth == 12 && $iday >= 22)
            {
                $dc=strtotime($iyear. "-12-21");
            }
            else
            {
                $dc=strtotime(($iyear-1). "-12-21");
            }
        }
        else
        {

            if( $imonth == 6 && $iday >= 22 || $imonth > 6)
            {
                $dc=strtotime($iyear. "-6-21");
            }
            else
            {
                $dc=strtotime(($iyear-1). "-6-21");
            }
        }
        
        $dc2=strtotime($iyear. "-". $imonth. "-". $iday);
        
        
        $numberOfDays=($dc2 - $dc)/ (60 * 60 * 24);
        

        return $numberOfDays;
    }


    //---------------------- Julian Date Functions -----------------------


    // calculate julian date from a calendar date
    function julianDate($year, $month, $day)
    {
        if ($month <= 2)
        {
            $year -= 1;
            $month += 12;
        }
        $A = floor($year/ 100);
        $B = 2- $A+ floor($A/ 4);

        $JD = floor(365.25* ($year+ 4716))+ floor(30.6001* ($month+ 1))+ $day+ $B- 1524.5;
        return $JD;
    }


    // convert a calendar date to julian date (second method)
    function calcJD($year, $month, $day)
    {
        $J1970 = 2440588.0;
        $date = $year. '-'. $month. '-'. $day;
        $ms = strtotime($date);   // # of milliseconds since midnight Jan 1, 1970
        $days = floor($ms/ (1000 * 60 * 60* 24));
        return $J1970+ $days- 0.5;
    }


    //---------------------- Trigonometric Functions -----------------------

    // degree sin
    function dsin($d)
    {
        return sin($this->dtr($d));
    }

    // degree cos
    function dcos($d)
    {
        return cos($this->dtr($d));
    }

    // degree tan
    function dtan($d)
    {
        return tan($this->dtr($d));
    }

    // degree arcsin
    function darcsin($x)
    {
        return $this->rtd(asin($x));
    }

    // degree arccos
    function darccos($x)
    {
        return $this->rtd(acos($x));
    }

    // degree arctan
    function darctan($x)
    {
        return $this->rtd(atan($x));
    }

    // degree arctan2
    function darctan2($y, $x)
    {
        return $this->rtd(atan2($y, $x));
    }

    // degree arccot
    function darccot($x)
    {
        return $this->rtd(atan(1/$x));
    }

    // degree to radian
    function dtr($d)
    {
        return ($d * M_PI) / 180.0;
    }

    // radian to degree
    function rtd($r)
    {
        return ($r * 180.0) / M_PI;
    }

    // range reduce angle in degrees.
    function fixangle($a)
    {
        $a = $a - 360.0 * floor($a / 360.0);
        $a = $a < 0 ? $a + 360.0 : $a;
        return $a;
    }

    // range reduce hours to 0..23
    function fixhour($a)
    {
        $a = $a - 24.0 * floor($a / 24.0);
        $a = $a < 0 ? $a + 24.0 : $a;
        return $a;
    }

	}
} //end if

//---------------------- praytime Object -----------------------

//$praytime = new praytime();