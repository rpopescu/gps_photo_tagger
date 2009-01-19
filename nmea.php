<?php
/*
from : http://gpsd.berlios.de/NMEA.txt

RMC - Recommended Minimum Navigation Information
                                                            12
        1         2 3       4 5        6  7   8   9    10 11|  13
        |         | |       | |        |  |   |   |    |  | |   |
 $--RMC,hhmmss.ss,A,llll.ll,a,yyyyy.yy,a,x.x,x.x,xxxx,x.x,a,m,*hh<CR><LF>
                1 2         3 4          5 6    7      8      9 10 11 12 13
$GPRMC,080136.482,A,5222.2484,N,00454.5497,E,0.57,106.95,140109, ,  ,  A*6F

 Field Number: 
  1) UTC Time
  2) Status, V=Navigation receiver warning A=Valid
  3) Latitude
  4) N or S
  5) Longitude
  6) E or W
  7) Speed over ground, knots
  8) Track made good, degrees true
  9) Date, ddmmyy
 10) Magnetic Variation, degrees
 11) E or W
 12) FAA mode indicator (NMEA 2.3 and later)
 13) Checksum

A status of V means the GPS has a valid fix that is below an internal
quality threshold, e.g. because the dilution of precision is too high 
or an elevation mask test failed.
 */
class rmc
{
	public $utc_time;
	public $status;
	public $lat;
	public $lat_ref;
	public $long;
	public $long_ref;
	public $speed_knots;
	public $track_made_good;
	public $date;
	public $mag_var;
	public $mag_var_ew;
	public $faa_mode;
	public $checksum;

	// derived values 
	public $timestamp;
	public $date_stamp;
	public $lat_deg_min_sec;
	public $long_deg_min_sec;

	function __construct($entries)
	{
		$this->utc_time = $entries[1];
		$this->status = $entries[2];
		$this->lat = $entries[3];
		$this->lat_ref = $entries[4];
		$this->long = $entries[5];
		$this->long_ref = $entries[6];
		$this->speed_knots = $entries[7];
		$this->track_made_good = $entries[8];
		$this->date = $entries[9];
		$this->mag_var = $entries[10];
		$this->mag_var_ew = $entries[11];
		$this->faa_mode = substr($entries[12], 0, 1);
		$this->checksum = substr($entries[12], 2, 2);
		$this->make_timestamp();
	}

	function make_timestamp()
	{
		$yy = substr($this->date, 4, 2); $yy += 2000;
		$date = "$yy-".substr($this->date, 2, 2)."-".substr($this->date, 0, 2);
		$time = substr($this->utc_time, 0, 2).":".substr($this->utc_time, 2, 2).":".substr($this->utc_time, 4, 2);
		$this->timestamp = strtotime("$date $time UTC");
		$this->date_stamp = $date;
	}

	/// Returns an array with (degrees, minutes, seconds).
	function calc_lat()
	{
		$this->lat_deg_min_sec = $this->coordinate_parse($this->lat);
		return $this->lat_deg_min_sec;
	}

	/// Returns an array with (degrees, minutes, seconds).
	function calc_long()
	{
		$this->long_deg_min_sec = $this->coordinate_parse($this->long);
		return $this->long_deg_min_sec;
	}

	/// Convert a numeric latitude/longitude from it's representation to its value.
	/// See NMEA spec for details.
	/// Returns an array with (degrees, decimal minutes).
	function coordinate_parse($value)
	{
		$dot = strpos($value, ".");
		if($dot == FALSE) return 0;
		if($dot < 4)
		{
			echo "weird coordinate: $value, not enough space for minutes and degrees to the left of the decimal point\n";
			return FALSE;
		}
		$degs  = substr($value, 0, $dot - 2) + 0;
		$dec_mins = substr($value, $dot - 2) + 0;
		$mins = floor($dec_mins);
		$dec_secs = $dec_mins - $mins;
		$secs = round(6000 * $dec_secs) / 100;
		if($mins > 60 || $secs > 60) { echo "invalid coordinates for $value: $degs $mins $secs\n"; return FALSE; }
		return array($degs, $mins, $secs);
	}

	function time_str()
	{
		return "$this->timestamp - ".date("H:i:s", $this->timestamp);
	}
}

class nmea_log
{
	public $filename;
	public $entries;

	function __construct($filename)
	{
		$this->filename = $filename;
		$f = fopen($this->filename, "r");
		if(!$f)
		{
			echo "file not found\n";
			return;
		}
		while(!feof($f))
		{
			$atoms = fgetcsv($f, 1024, ",");
			if($atoms[0] == "\$GPRMC")
			{
				$this->entries[] = $foo = new rmc($atoms);
			}
		}
		fclose($f);
	}

	function dump()
	{
		print_r($this->entries);
	}
}

//$l = new nmea_log("data/GPS_20090114_080103.log");
//$l->dump();
?>
